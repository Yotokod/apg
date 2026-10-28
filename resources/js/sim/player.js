// Alpine component `simulator(config)`: interactive-story player. State lives client-side and is saved in localStorage.
import gsap from 'gsap';
import { simulate, compareScenarios, feedPeriods, progressAt, rationG, incubate, dailyFeedKg, SCENARIOS, SimError } from './engine.js';
import { createRun, choose, back, choicesFor, isEnding } from './story.js';

const STORAGE_KEY = 'apg.sim.v2';
const FLOCK_SNAPS = [500, 1000, 2000, 3000, 5000, 10000];
const FLOCK_STEP = 100;
const FLOCK_MAX = 100000;
const CHAPTERS = 8;
const RATIONS_BY_GOAL = { chair: ['r25', 'r30', 'prog'], oeufs: ['prog', 'adlib'], repro: ['prog', 'adlib'] };
const ENDING_ICONS = { success: 'fa-trophy', tight: 'fa-scale-balanced', loss: 'fa-arrow-trend-down', disaster: 'fa-biohazard' };
const reducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function readStorage() {
    try { return JSON.parse(localStorage.getItem(STORAGE_KEY) || 'null'); } catch { return null; }
}

function writeStorage(value) {
    try { localStorage.setItem(STORAGE_KEY, JSON.stringify(value)); } catch { /* private mode / quota: run stays in memory */ }
}

export function simulator(config) {
    const story = config.story;
    const memo = new Map();
    const hudTween = {};
    let hudTimer = null;

    return {
        cfg: config,
        run: null,
        overrides: {},
        pending: null,
        sheet: null,
        resumed: false,
        hud: { spent: 0, alive: 0, mortalityPct: 0, feedKg: 0, day: 0 },
        snaps: FLOCK_SNAPS,
        scenarios: SCENARIOS,
        chapterCount: CHAPTERS,

        init() {
            const saved = readStorage();
            const usable = saved && saved.rev === config.revision && story.nodes[saved.run?.node];
            this.run = usable ? saved.run : createRun(story);
            this.overrides = usable ? saved.overrides || {} : {};
            this.resumed = Boolean(usable && this.run.history.length);
            this.$watch('run', () => this.onChange());
            this.$watch('overrides', () => this.onChange());
            this.updateHud(true);
            this.$nextTick(() => this.animateScene());
        },

        // ---------- i18n & formatting ----------
        t(key, repl = {}) {
            const raw = key.split('.').reduce((o, k) => (o == null ? o : o[k]), config.i18n);
            if (typeof raw !== 'string') return key;
            return Object.entries(repl).reduce((s, [k, v]) => s.replaceAll(`:${k}`, v), raw);
        },
        fmt(n, digits = 0) {
            if (n == null || !Number.isFinite(n)) return '—';
            return new Intl.NumberFormat(config.locale === 'en' ? 'en-US' : 'fr-FR', { maximumFractionDigits: digits }).format(n);
        },
        money(n) { return n == null || !Number.isFinite(n) ? '—' : `${this.fmt(Math.round(n))} ${config.currency}`; },
        kg(n) { return `${this.fmt(n, 1)} kg`; },
        pct(n) { return `${this.fmt(n, 1)} %`; },
        unit(u) { return u ? this.t(`units.${u}`) : ''; },

        // ---------- derived state ----------
        get params() { return { ...config.params, ...this.overrides }; },
        get d() { return this.run.decisions; },
        get node() { return story.nodes[this.run.node]; },
        get chapter() { return this.node?.chapter ?? 1; },
        get choices() { return choicesFor(this.node, this.d); },
        get isEnd() { return isEnding(story, this.run.node); },
        get canBack() { return this.run.history.length > 0; },
        get outcome() { return this.calc(this.d); },
        get r() { return this.outcome.r; },
        get error() { return this.outcome.error; },
        get errorText() {
            const e = this.error;
            if (!e) return '';
            return e instanceof SimError ? this.t(`errors.${e.code}`, e.data) : this.t('errors.generic');
        },
        get compare() {
            if (!this.r) return null;
            const out = {};
            SCENARIOS.forEach((s) => { out[s] = this.calc({ ...this.d, scenario: s }).r; });
            return Object.values(out).every(Boolean) ? out : null;
        },
        get compareMax() {
            const c = this.compare;
            return c ? Math.max(1, ...Object.values(c).map((x) => Math.abs(x.result))) : 1;
        },

        calc(decisions, overridesKey = JSON.stringify(this.overrides)) {
            const key = overridesKey + JSON.stringify(decisions);
            if (!memo.has(key)) {
                if (memo.size > 200) memo.clear();
                try { memo.set(key, { r: simulate(this.params, JSON.parse(JSON.stringify(decisions))), error: null }); } catch (error) { memo.set(key, { r: null, error }); }
            }
            return memo.get(key);
        },

        // ---------- story actions ----------
        pick(choice) {
            if (this.error) return;
            if (choice.bad) { this.pending = choice; this.$nextTick(() => this.$refs.badFirst?.focus()); return; }
            this.commit(choice);
        },
        commit(choice) {
            this.pending = null;
            this.run = choose(JSON.parse(JSON.stringify(this.run)), story, choice, this.params);
            this.afterMove();
        },
        goBack() {
            this.pending = null;
            this.run = back(JSON.parse(JSON.stringify(this.run)));
            this.afterMove();
        },
        restart() {
            if (this.run.history.length && !window.confirm(this.t('ui.restart_confirm'))) return;
            this.pending = null;
            this.sheet = null;
            this.run = createRun(story);
            this.afterMove();
        },
        afterMove() {
            this.resumed = false;
            this.$nextTick(() => {
                this.animateScene();
                this.$refs.title?.focus({ preventScroll: true });
                this.$refs.stage?.scrollIntoView({ behavior: reducedMotion() ? 'auto' : 'smooth', block: 'start' });
            });
        },
        onChange() {
            writeStorage({ rev: config.revision, run: this.run, overrides: this.overrides });
            this.updateHud();
        },
        chooseKey(event, index) {
            const keys = { ArrowDown: 1, ArrowRight: 1, ArrowUp: -1, ArrowLeft: -1 };
            if (!(event.key in keys)) return;
            event.preventDefault();
            const cards = [...this.$refs.choices.querySelectorAll('[role="radio"]')];
            cards[(index + keys[event.key] + cards.length) % cards.length]?.focus();
        },

        // ---------- interactive controls ----------
        get snapIndex() {
            const i = FLOCK_SNAPS.findIndex((s) => s >= this.d.flock);
            return i === -1 ? FLOCK_SNAPS.length - 1 : i;
        },
        setSnap(i) { this.d.flock = FLOCK_SNAPS[Number(i)]; },
        stepFlock(dir) {
            const min = this.params.min_flock;
            this.d.flock = Math.min(FLOCK_MAX, Math.max(min, Math.round((this.d.flock + dir * FLOCK_STEP) / FLOCK_STEP) * FLOCK_STEP));
        },
        typeFlock(value) {
            const n = Math.round(Number(value));
            this.d.flock = Number.isFinite(n) ? Math.min(FLOCK_MAX, Math.max(0, n)) : this.params.min_flock;
        },
        chickPrice(zone) { return this.params[`chick_price_${zone}`]; },
        setAlloc(part, value) { this.d.alloc = { ...this.d.alloc, [part]: Math.max(0, Math.min(100, Math.round(Number(value) || 0))) }; },
        get allocTotal() { return ['table', 'incubate', 'sell'].reduce((s, k) => s + Number(this.d.alloc[k] || 0), 0); },
        get rationOptions() {
            return (RATIONS_BY_GOAL[this.d.goal] || []).map((ration) => {
                const res = this.calc({ ...this.d, ration }).r;
                const g1 = rationG(this.params, ration, 1);
                return { ration, res, g1, day1: dailyFeedKg(this.d.flock, g1), week1: dailyFeedKg(this.d.flock, g1) * 7 };
            });
        },
        get bagFill() {
            const max = Math.max(1, ...this.rationOptions.map((o) => o.res?.feedWithWaste ?? 0));
            return Math.min(1, (this.r?.feedWithWaste ?? 0) / max);
        },
        get tempWeeks() {
            return [1, 2, 3, 4].map((w) => ({ w, min: this.params[`temp_w${w}`], max: this.params[`temp_w${w}`] + this.params.temp_span }));
        },
        get eggsPerDay() {
            if (!this.r) return 0;
            return Math.round((this.r.females * this.r.layRatePct) / 100);
        },
        get hatchExample() {
            const eggs = this.r?.eggsIncubated ?? 0;
            try {
                return incubate(eggs, { fertilityPct: this.params.fertility_pct, hatchPct: Math.min(100, Math.max(0, this.params.hatch_pct + (this.d.hatchExtra || 0))), base: this.d.hatchBase });
            } catch { return null; }
        },
        get hatchFormula() {
            const h = this.hatchExample;
            if (!h) return '';
            const hatchPct = this.fmt(this.params.hatch_pct + (this.d.hatchExtra || 0));
            return h.denominator === 'fertile'
                ? this.t('controls.formula_fertile', { e: this.fmt(h.loaded), f: this.fmt(this.params.fertility_pct), fe: this.fmt(h.fertile), h: hatchPct, c: this.fmt(h.chicks) })
                : this.t('controls.formula_loaded', { e: this.fmt(h.loaded), h: hatchPct, c: this.fmt(h.chicks) });
        },

        // ---------- scenes ----------
        get sceneKey() { return this.node?.scene || 'farm'; },
        get chapterName() { return this.t(`chapters.${this.chapter}`); },
        get sceneChip() {
            const r = this.r;
            if (!r) return '';
            const temps = this.tempWeeks;
            const chips = {
                chicks: () => `${this.fmt(this.d.flock)} ${this.unit('birds')}`,
                house: () => `${this.fmt(r.surfaceAdult, 1)} m²`,
                site: () => `${this.fmt(r.surfaceAdult, 1)} m²`,
                thermo: () => this.t('controls.temp_range', { min: temps[0].min, max: temps[0].max }),
                huddle: () => this.t('controls.temp_range', { min: temps[0].min, max: temps[0].max }),
                panting: () => this.t('controls.temp_range', { min: temps[1].min, max: temps[1].max }),
                feed: () => `${this.fmt(r.bags)} × ${this.d.bagKg} kg`,
                growth: () => `${this.fmt(r.weightKg, 1)} kg`,
                eggs: () => `${this.fmt(this.eggsPerDay)} ${this.unit('eggs_day')}`,
                incubator: () => `${this.fmt(this.hatchExample?.chicks ?? 0)} ${this.unit('chicks')}`,
                truck: () => this.money(r.revenue),
                report: () => this.money(r.result),
            };
            return chips[this.sceneKey]?.() ?? '';
        },
        get chicksShown() { return Math.max(3, Math.min(12, Math.round(Math.log2(Math.max(500, this.d.flock) / 250) * 2.4))); },
        get thermoLevel() { return Math.min(1, Math.max(0.15, (this.params.temp_w1 + this.params.temp_span - 15) / 25)); },
        get eggsShown() { return Math.round(((this.r?.layRatePct ?? 60) / 100) * 12); },
        animateScene() {
            if (reducedMotion() || !this.$refs.scene) return;
            gsap.fromTo(this.$refs.scene, { opacity: 0, scale: 1.04 }, { opacity: 1, scale: 1, duration: 0.6, ease: 'power2.out', clearProps: 'transform' });
            const cards = this.$refs.choices?.querySelectorAll('[data-choice]');
            if (cards?.length) gsap.fromTo(cards, { opacity: 0, y: 14 }, { opacity: 1, y: 0, duration: 0.45, stagger: 0.07, delay: 0.2, ease: 'power2.out', clearProps: 'all' });
        },

        // ---------- HUD ----------
        updateHud(instant = false) {
            const target = this.r ? progressAt(this.r, this.isEnd ? CHAPTERS : this.chapter) : null;
            if (!target) return;
            const values = { spent: target.spent, alive: target.alive, mortalityPct: target.mortalityPct, feedKg: target.feedKg, day: target.day };
            if (instant || reducedMotion()) { Object.assign(this.hud, values); return; }
            const from = { ...this.hud };
            gsap.killTweensOf(hudTween);
            Object.assign(hudTween, from);
            gsap.to(hudTween, { ...values, duration: 0.8, ease: 'power1.out', onUpdate: () => Object.assign(this.hud, hudTween) });
            clearTimeout(hudTimer);
            hudTimer = setTimeout(() => Object.assign(this.hud, values), 900); // settle even when rAF is throttled (background tab)
        },
        get totalDays() { return this.r?.days ?? 0; },

        // ---------- settings & hypotheses ----------
        get groups() {
            const order = [];
            config.meta.forEach((m) => { if (!order.includes(m.group)) order.push(m.group); });
            return order.map((g) => ({ key: g, items: config.meta.filter((m) => m.group === g) }));
        },
        setParam(key, value) {
            const n = Number(String(value).replace(',', '.'));
            if (value === '' || !Number.isFinite(n)) return;
            if (n === config.params[key]) { const { [key]: _drop, ...rest } = this.overrides; this.overrides = rest; return; }
            this.overrides = { ...this.overrides, [key]: n };
        },
        resetParams() { this.overrides = {}; },
        isModified(key) { return key in this.overrides; },
        openSheet(name) {
            this.sheet = name;
            this.$nextTick(() => this.$refs.sheetClose?.focus());
        },
        closeSheet() { this.sheet = null; },

        // ---------- ending & report ----------
        get ending() { return this.r?.ending ?? 'loss'; },
        get endingIcon() { return ENDING_ICONS[this.ending]; },
        get marginRate() { return this.r && this.r.revenue > 0 ? (this.r.result / this.r.revenue) * 100 : null; },
        get periods() { return this.r ? feedPeriods(this.r.weeks) : []; },
        get costLines() {
            if (!this.r) return [];
            return Object.entries(this.r.lines).filter(([, v]) => v > 0).map(([k, v]) => ({ key: k, label: this.t(`lines.${k}`), value: v }));
        },
        get pathLabels() {
            return this.run.path.map((p) => {
                const node = story.nodes[p.node];
                const choice = node?.choices.find((c) => c.code === p.choice);
                return { title: node?.title ?? p.node, label: choice?.label ?? p.choice, bad: Boolean(choice?.bad) };
            });
        },
        get risks() {
            const flags = (this.d.flags || []).filter((f) => this.t(`risks.${f}`) !== `risks.${f}`);
            const list = flags.map((f) => this.t(`risks.${f}`));
            if (this.d.goal === 'repro') list.push(this.t('risks.market'));
            list.push(this.t('risks.generic'));
            return list;
        },
        periodLabel(p) { return p.from === p.to ? this.t('report.week', { a: p.from }) : this.t('report.weeks', { a: p.from, b: p.to }); },
        paramValue(m) { return `${this.fmt(this.params[m.key], 2)} ${this.unit(m.unit)}`; },
        print() { window.print(); },
    };
}

function register() { window.Alpine.data('simulator', simulator); }
if (window.Alpine) register();
else document.addEventListener('alpine:init', register);
