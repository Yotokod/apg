// Story graph (data from DB: sim_steps = nodes, sim_choices = edges). Pure functions, no DOM.
// Choice effects are flat key/values editable in the admin:
//   "set.density": "8" | "add.mortalityExtra": "6" | "mul.weightFactor": "0.7" | "flag": "co_risk"
//   a value "@param_key" reads that parameter (e.g. "add.extraPerBird": "@vet_plan_per_bird").
// "next" is a node code, or a goal map "chair=growth;oeufs=laying;repro=laying".
import { DEFAULT_DECISIONS } from './engine.js';

export function resolveNext(spec, decisions) {
    if (!spec) return null;
    if (!spec.includes('=')) return spec.trim();
    const map = Object.fromEntries(spec.split(';').map((p) => p.split('=').map((s) => s.trim())));
    return map[decisions.goal] ?? map['*'] ?? null;
}

export function parseValue(raw, params = {}) {
    if (typeof raw !== 'string') return raw;
    const v = raw.trim();
    if (v.startsWith('@')) return Number(params[v.slice(1)] ?? 0);
    if (v !== '' && !Number.isNaN(Number(v))) return Number(v);
    return v;
}

export function applyEffects(decisions, effects = {}, params = {}) {
    const next = { ...decisions, alloc: { ...decisions.alloc }, flags: [...(decisions.flags || [])] };
    for (const [key, raw] of Object.entries(effects || {})) {
        const value = parseValue(raw, params);
        const [op, field] = key.split('.');
        if (op === 'flag') { if (!next.flags.includes(value)) next.flags.push(value); continue; }
        if (op === 'set') next[field] = value;
        else if (op === 'add') next[field] = (Number(next[field]) || 0) + Number(value);
        else if (op === 'mul') next[field] = (Number(next[field] ?? 1)) * Number(value);
    }
    return next;
}

export function isVisible(choice, decisions) {
    return !choice.goals || choice.goals.length === 0 || choice.goals.includes(decisions.goal);
}

export function choicesFor(node, decisions) {
    return (node?.choices || []).filter((c) => isVisible(c, decisions));
}

export function createRun(story, decisions = {}) {
    return { node: story.start, decisions: { ...structuredClone(DEFAULT_DECISIONS), ...decisions }, history: [], path: [] };
}

/** Returns a new run after taking `choice` on the current node (pure: history keeps the previous snapshot). */
export function choose(run, story, choice, params) {
    const node = story.nodes[run.node];
    const decisions = applyEffects(run.decisions, choice.effects, params);
    const target = resolveNext(choice.next || node.next, decisions);
    if (target && !story.nodes[target]) throw new Error(`Unknown node: ${target}`);
    return {
        node: target ?? run.node,
        decisions,
        history: [...run.history, { node: run.node, decisions: run.decisions, path: run.path }],
        path: [...run.path, { node: run.node, choice: choice.code }],
    };
}

export function back(run) {
    if (!run.history.length) return run;
    const prev = run.history[run.history.length - 1];
    return { node: prev.node, decisions: prev.decisions, history: run.history.slice(0, -1), path: prev.path };
}

export function isEnding(story, code) {
    return story.nodes[code]?.kind === 'ending';
}

/** Every path from start must reach an ending; returns codes of unreachable or dead-end nodes (admin sanity check). */
export function auditGraph(story, goals = ['chair', 'oeufs', 'repro']) {
    const problems = new Set();
    for (const goal of goals) {
        const seen = new Set();
        const walk = (code) => {
            if (seen.has(code)) return;
            seen.add(code);
            const node = story.nodes[code];
            if (!node) { problems.add(code); return; }
            if (node.kind === 'ending') return;
            const visible = choicesFor(node, { goal });
            if (!visible.length) problems.add(code);
            visible.forEach((c) => {
                const t = resolveNext(c.next || node.next, { goal });
                t ? walk(t) : problems.add(code);
            });
        };
        walk(story.start);
    }
    return [...problems];
}
