import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import {
    SimError, dailyFeedKg, feedForDays, bagsNeeded, breederRationG, surfaceM2, incubate, eggsLaid,
    checkRate, checkAllocation, checkSplit, breakEven, simulate, compareScenarios, feedPeriods, progressAt, endingFor,
} from '../../resources/js/sim/engine.js';
import { createRun, choose, back, resolveNext, applyEffects, choicesFor, auditGraph, isEnding } from '../../resources/js/sim/story.js';

const seed = JSON.parse(readFileSync(new URL('../../database/seeders/data/simulator.json', import.meta.url), 'utf8'));
const PARAMS = Object.fromEntries(seed.params.map((p) => [p.key, p.value]));
const STORY = {
    start: seed.nodes[0].code,
    nodes: Object.fromEntries(seed.nodes.map((n) => [n.code, { ...n, choices: n.choices.map((c) => ({ ...c, bad: !!c.bad })) }])),
};

const throwsCode = (fn, code) => assert.throws(fn, (e) => e instanceof SimError && e.code === code);

test('Annexe A – base feed: 500 birds × 25 g = 12.5 kg/day, 87.5 kg over 7 days', () => {
    assert.equal(dailyFeedKg(500, 25), 12.5);
    assert.equal(feedForDays(500, 25, 7), 87.5);
});

test('Annexe A – progressive ration: week 20 = 120 g (140 g does not follow), held after the cap', () => {
    assert.equal(breederRationG(1), 25);
    assert.equal(breederRationG(20), 120);
    assert.notEqual(breederRationG(20), 140);
    assert.equal(breederRationG(30), 120);
});

test('Annexe A – bags: 87.5 kg in 50 kg bags = 2 bags, 12.5 kg theoretical leftover', () => {
    assert.deepEqual(bagsNeeded(87.5, 50), { bags: 2, leftoverKg: 12.5 });
    assert.equal(bagsNeeded(100, 50).bags, 2);
    assert.equal(bagsNeeded(87.5, 25).bags, 4);
});

test('Annexe A – incubation: fertile base 1000 → 900 fertile → 765 chicks; loaded base → 850 (fertility not applied twice)', () => {
    assert.deepEqual(incubate(1000, { fertilityPct: 90, hatchPct: 85, base: 'fertile' }), { loaded: 1000, fertile: 900, chicks: 765, denominator: 'fertile' });
    const loaded = incubate(1000, { fertilityPct: 90, hatchPct: 85, base: 'loaded' });
    assert.equal(loaded.chicks, 850);
    assert.equal(loaded.fertile, null);
    assert.equal(loaded.denominator, 'loaded');
});

test('Annexe A – laying: 400 hens × 80 % × 1 day = 320 eggs', () => {
    assert.equal(eggsLaid(400, 80, 1), 320);
});

test('Annexe A – surface: 500 birds at 4/m² = 125 m²; brooder 500 at 25/m² = 20 m²', () => {
    assert.equal(surfaceM2(500, 4), 125);
    assert.equal(surfaceM2(500, 25), 20);
    assert.equal(surfaceM2(10000, 4), 2500);
});

test('Annexe A – guards: rate 120 %, bag 0 kg, males+females > flock, allocation > 100, negatives', () => {
    throwsCode(() => checkRate(120), 'rate_range');
    throwsCode(() => eggsLaid(400, 120), 'rate_range');
    throwsCode(() => bagsNeeded(87.5, 0), 'bag_zero');
    throwsCode(() => checkSplit(300, 800, 1000), 'split_over');
    throwsCode(() => checkAllocation({ table: 50, incubate: 60, sell: 0 }), 'alloc_over');
    throwsCode(() => checkAllocation({ table: 0, incubate: 60, sell: 50 }), 'alloc_over');
    throwsCode(() => dailyFeedKg(-5, 25), 'negative_count');
    throwsCode(() => surfaceM2(500, 0), 'density_zero');
    throwsCode(() => simulate(PARAMS, { goal: 'chair', flock: 400 }), 'flock_min');
    throwsCode(() => simulate(PARAMS, { goal: 'chair', flock: 1000, bagKg: 0 }), 'bag_zero');
    throwsCode(() => simulate({ ...PARAMS, feed_price_kg: -1 }, { goal: 'chair', flock: 1000 }), 'negative_param');
    throwsCode(() => simulate(PARAMS, { goal: 'repro', flock: 1000, alloc: { table: 60, incubate: 50, sell: 0 } }), 'alloc_over');
    const { feed_price_kg: _omit, ...missing } = PARAMS;
    throwsCode(() => simulate(missing, { goal: 'chair', flock: 1000 }), 'missing_param');
});

test('break-even: fixed / contribution rate, null when not reached', () => {
    assert.equal(breakEven(100, 1000, 500), 200);
    assert.equal(breakEven(100, 1000, 1200), null);
    assert.equal(breakEven(100, 0, 0), null);
});

test('simulate chair: feed, bags, costs and revenue are consistent', () => {
    const r = simulate(PARAMS, { goal: 'chair', flock: 1000, ration: 'r25', saleDay: 45, bagKg: 50 });
    assert.equal(r.days, 45);
    assert.ok(r.feedKg < feedForDays(1000, 25, 45) && r.feedKg > feedForDays(940, 25, 45), 'dead birds stop eating');
    assert.equal(r.bags, Math.ceil(r.feedWithWaste / 50));
    assert.equal(r.lines.chicks, 900000);
    assert.equal(r.surfaceAdult, 250);
    assert.equal(r.surfaceBrooder, 40);
    assert.equal(r.aliveEnd, 940);
    assert.ok(Math.abs(r.revenue - 940 * 1.5 * 0.75 * 3000) < 1e-6);
    assert.ok(Math.abs(r.result - (r.revenue - r.opex - r.amortization)) < 1e-6);
});

test('zone price changes the chick cost (APG shop prices)', () => {
    assert.equal(simulate(PARAMS, { goal: 'chair', flock: 500, zone: 'togo' }).lines.chicks, 550000);
    assert.equal(simulate(PARAMS, { goal: 'chair', flock: 500, zone: 'world' }).lines.chicks, 650000);
});

test('simulate eggs/breeding: hens only for table eggs, split + incubation for breeding, no double counting', () => {
    const eggs = simulate(PARAMS, { goal: 'oeufs', flock: 1000, ration: 'prog' });
    assert.equal(eggs.females, 1000);
    assert.equal(eggs.males, 0);
    assert.ok(eggs.eggsLaidTotal > 0 && eggs.layDays > 0);
    const repro = simulate(PARAMS, { goal: 'repro', flock: 1100, ration: 'prog', alloc: { table: 60, incubate: 30, sell: 10 } });
    assert.equal(repro.males + repro.females, 1100);
    assert.equal(repro.males, 100);
    assert.ok(repro.eggsTable + repro.eggsIncubated + repro.eggsSold <= repro.eggsNet + 2);
    assert.equal(repro.hatch.denominator, 'fertile');
    assert.equal(repro.hatch.fertile, Math.round(repro.eggsIncubated * 0.9));
});

test('scenarios: favourable beats middle beats cautious', () => {
    const cmp = compareScenarios(PARAMS, { goal: 'chair', flock: 2000 });
    assert.ok(cmp.favorable.result > cmp.inter.result && cmp.inter.result > cmp.prudent.result);
});

test('feed periods: weekly for short cycles, cumulative equals total', () => {
    const r = simulate(PARAMS, { goal: 'chair', flock: 500 });
    const periods = feedPeriods(r.weeks);
    assert.equal(periods.length, 7);
    assert.ok(Math.abs(periods.at(-1).cumul - r.feedKg) < 1e-6);
    assert.ok(feedPeriods(simulate(PARAMS, { goal: 'oeufs', flock: 500, ration: 'prog' }).weeks).length <= 30);
});

test('HUD progress never exceeds totals', () => {
    const r = simulate(PARAMS, { goal: 'chair', flock: 1000 });
    assert.equal(progressAt(r, 1).spent, 0);
    assert.equal(progressAt(r, 8).feedKg, r.feedKg);
    assert.ok(progressAt(r, 4).alive <= 1000);
});

test('story helpers: goal-map next, effects with @param references, visibility by goal', () => {
    assert.equal(resolveNext('chair=growth;oeufs=laying', { goal: 'oeufs' }), 'laying');
    assert.equal(resolveNext('sale', { goal: 'chair' }), 'sale');
    const d = applyEffects({ mortalityExtra: 1, extraPerBird: 0, weightFactor: 1, flags: [], alloc: {} },
        { 'add.mortalityExtra': '6', 'add.extraPerBird': '@vet_plan_per_bird', 'mul.weightFactor': '0.7', 'set.ration': 'prog', flag: 'x' }, PARAMS);
    assert.deepEqual([d.mortalityExtra, d.extraPerBird, d.weightFactor, d.ration, d.flags], [7, 200, 0.7, 'prog', ['x']]);
    assert.equal(choicesFor(STORY.nodes.feed, { goal: 'chair' }).some((c) => c.code === 'feed_adlib'), false);
    assert.equal(choicesFor(STORY.nodes.feed, { goal: 'repro' }).some((c) => c.code === 'feed_25'), false);
});

test('story graph: every goal reaches the ending, no dead ends', () => {
    assert.deepEqual(auditGraph(STORY), []);
});

function play(codes) {
    let run = createRun(STORY);
    for (const code of codes) {
        const choice = STORY.nodes[run.node].choices.find((c) => c.code === code);
        assert.ok(choice, `choice ${code} available on ${run.node}`);
        run = choose(run, STORY, choice, PARAMS);
    }
    return run;
}

const GOOD_CHAIR = ['goal_chair', 'order', 'build_new', 'site_good', 'arrival_good', 'heat_gas', 'cold_ok', 'hot_ok', 'feed_25', 'bio_vet', 'routine_log', 'sell_45', 'sale_direct'];

test('path: good broiler decisions → success ending', () => {
    const run = play(GOOD_CHAIR);
    assert.ok(isEnding(STORY, run.node));
    const r = simulate(PARAMS, run.decisions);
    assert.equal(r.ending, 'success');
});

test('path: early sale + rushed middleman → loss ending', () => {
    const run = play(GOOD_CHAIR.slice(0, -2).concat(['sell_35', 'sale_rush']));
    assert.equal(simulate(PARAMS, run.decisions).ending, 'loss');
});

test('path: no biosecurity branches to the outbreak; ignoring it → disaster', () => {
    let run = play(['goal_chair', 'order', 'build_new', 'site_good', 'arrival_good', 'heat_gas', 'cold_ok', 'hot_ok', 'feed_25', 'bio_none']);
    assert.equal(run.node, 'outbreak');
    run = play(['goal_chair', 'order', 'build_new', 'site_good', 'arrival_good', 'heat_gas', 'cold_ok', 'hot_ok', 'feed_25', 'bio_none', 'out_wait', 'sell_45', 'sale_direct']);
    assert.equal(simulate(PARAMS, run.decisions).ending, 'disaster');
});

test('path: breeding goes through laying then incubation; back() restores the previous snapshot', () => {
    let run = play(['goal_repro', 'order', 'build_new', 'site_good', 'arrival_good', 'heat_elec', 'cold_ok', 'hot_ok', 'feed_prog', 'bio_vet', 'routine_log']);
    assert.equal(run.node, 'laying');
    const before = run;
    run = choose(run, STORY, STORY.nodes.laying.choices.find((c) => c.code === 'reform_24'), PARAMS);
    assert.equal(run.node, 'incubation');
    assert.equal(run.decisions.reformMonths, 24);
    const rewound = back(run);
    assert.equal(rewound.node, 'laying');
    assert.deepEqual(rewound.decisions, before.decisions);
});

test('ending thresholds', () => {
    assert.equal(endingFor({ mortalityPct: 30, result: 1, revenue: 2 }), 'disaster');
    assert.equal(endingFor({ mortalityPct: 5, result: -1, revenue: 2 }), 'loss');
    assert.equal(endingFor({ mortalityPct: 5, result: 5, revenue: 100 }), 'tight');
    assert.equal(endingFor({ mortalityPct: 5, result: 50, revenue: 100 }), 'success');
});
