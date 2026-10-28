// Pure simulation engine (no DOM). Formulas from docs/SIMULATOR-SPEC.md; every number comes from `params`
// (DB table sim_params) or from the player's decisions. Errors are SimError codes, translated by the UI (sim.errors.*).

export const GOALS = ['chair', 'oeufs', 'repro'];
export const SCENARIOS = ['prudent', 'inter', 'favorable'];
export const ENDINGS = ['success', 'tight', 'loss', 'disaster'];
const DAYS_PER_MONTH = 30.4;
const TIGHT_MARGIN_RATE = 0.1;
const DISASTER_MORTALITY_PCT = 25;

export class SimError extends Error {
    constructor(code, data = {}) {
        super(code);
        this.code = code;
        this.data = data;
    }
}

export const round1 = (n) => Math.round(n * 10) / 10;
const clamp = (n, min, max) => Math.min(max, Math.max(min, n));

function assertNonNegative(value, code) {
    if (!Number.isFinite(value) || value < 0) throw new SimError(code, { value });
}

export function checkRate(pct, code = 'rate_range') {
    if (!Number.isFinite(pct) || pct < 0 || pct > 100) throw new SimError(code, { value: pct });
    return pct;
}

export function dailyFeedKg(alive, gramsPerBird) {
    assertNonNegative(alive, 'negative_count');
    assertNonNegative(gramsPerBird, 'negative_value');
    return (alive * gramsPerBird) / 1000;
}

export function feedForDays(alive, gramsPerBird, days) {
    assertNonNegative(days, 'negative_value');
    return dailyFeedKg(alive, gramsPerBird) * days;
}

export function bagsNeeded(kg, bagKg) {
    if (!Number.isFinite(bagKg) || bagKg <= 0) throw new SimError('bag_zero');
    assertNonNegative(kg, 'negative_value');
    const bags = Math.ceil(kg / bagKg - 1e-9);
    return { bags, leftoverKg: round1(bags * bagKg - kg) };
}

/** Breeder rule (APG): start g in week 1, +step g each week, held at capWeek (never extrapolated). */
export function breederRationG(week, start = 25, step = 5, capWeek = 20) {
    if (!Number.isFinite(week) || week < 1) throw new SimError('negative_value', { value: week });
    return start + (Math.min(week, capWeek) - 1) * step;
}

export function surfaceM2(birds, density) {
    if (!Number.isFinite(density) || density <= 0) throw new SimError('density_zero');
    assertNonNegative(birds, 'negative_count');
    return birds / density;
}

/** base 'fertile': hatch % applies to fertile eggs; base 'loaded': hatch % applies to loaded eggs (fertility NOT applied again). */
export function incubate(eggs, { fertilityPct, hatchPct, base = 'fertile' }) {
    assertNonNegative(eggs, 'negative_count');
    checkRate(hatchPct);
    if (base === 'loaded') return { loaded: eggs, fertile: null, chicks: Math.round((eggs * hatchPct) / 100), denominator: 'loaded' };
    checkRate(fertilityPct);
    const fertile = Math.round((eggs * fertilityPct) / 100);
    return { loaded: eggs, fertile, chicks: Math.round((fertile * hatchPct) / 100), denominator: 'fertile' };
}

export function eggsLaid(femalesAlive, ratePct, days = 1) {
    assertNonNegative(femalesAlive, 'negative_count');
    checkRate(ratePct);
    return (femalesAlive * ratePct * days) / 100;
}

export function checkAllocation(alloc) {
    const parts = [alloc.table, alloc.incubate, alloc.sell].map(Number);
    parts.forEach((p) => assertNonNegative(p, 'negative_value'));
    const total = parts.reduce((a, b) => a + b, 0);
    if (total > 100) throw new SimError('alloc_over', { total });
    return total;
}

export function checkSplit(males, females, total) {
    assertNonNegative(males, 'negative_count');
    assertNonNegative(females, 'negative_count');
    if (males + females > total) throw new SimError('split_over', { total });
}

/** Break-even revenue = fixed costs / contribution-margin rate; null when the margin on variable costs is not positive. */
export function breakEven(fixedCosts, revenue, variableCosts) {
    if (revenue <= 0) return null;
    const rate = (revenue - variableCosts) / revenue;
    return rate > 0 ? fixedCosts / rate : null;
}

export const DEFAULT_DECISIONS = Object.freeze({
    goal: 'chair', flock: 1000, zone: 'benin', scenario: 'inter', bagKg: 50, ration: 'r25',
    buildMode: 'new', density: null, saleDay: 45, weightKg: null, reformMonths: 18, hatchBase: 'fertile',
    alloc: { table: 50, incubate: 40, sell: 10 },
    mortalityExtra: 0, weightFactor: 1, layingFactor: 1, hatchExtra: 0, priceFactor: 1, feedPriceFactor: 1,
    extraPerBird: 0, extraFixed: 0, eggLossExtra: 0, flags: [],
});

const num = (params, key) => {
    const v = Number(params[key]);
    if (!Number.isFinite(v)) throw new SimError('missing_param', { key });
    return v;
};

export function validate(params, d) {
    if (!GOALS.includes(d.goal)) throw new SimError('goal_unknown');
    if (!Number.isInteger(d.flock) || d.flock < 0) throw new SimError('negative_count', { value: d.flock });
    if (d.flock < num(params, 'min_flock')) throw new SimError('flock_min', { min: num(params, 'min_flock') });
    if (!Number.isFinite(d.bagKg) || d.bagKg <= 0) throw new SimError('bag_zero');
    Object.entries(params).forEach(([key, v]) => { if (Number(v) < 0) throw new SimError('negative_param', { key }); });
    if (d.goal === 'repro') checkAllocation(d.alloc);
}

export function flockSplit(params, d) {
    if (d.goal !== 'repro') return { males: 0, females: d.goal === 'oeufs' ? d.flock : 0 };
    const males = Math.round(d.flock / (num(params, 'male_ratio') + 1));
    const females = d.flock - males;
    checkSplit(males, females, d.flock);
    return { males, females };
}

export function rationG(params, ration, week) {
    const prog = () => breederRationG(week, num(params, 'breeder_start_g'), num(params, 'breeder_step_g'), num(params, 'breeder_cap_week'));
    switch (ration) {
        case 'r30': return num(params, 'ration_high_g');
        case 'prog': return prog();
        case 'adlib': return prog() * (1 + num(params, 'adlib_extra_pct') / 100);
        default: return num(params, 'ration_low_g');
    }
}

export function timeline(params, d) {
    if (d.goal === 'chair') return { days: d.saleDay, rearDays: d.saleDay, layDays: 0 };
    const days = Math.round(d.reformMonths * DAYS_PER_MONTH);
    const rearDays = Math.min(days, num(params, 'lay_start_week') * 7);
    return { days, rearDays, layDays: days - rearDays };
}

/** Week-by-week flock: deaths are spread linearly over the cycle; dead birds stop eating and laying. */
export function weeklySeries(params, d, mortalityPct, layRatePct, femalesShare) {
    const { days, rearDays } = timeline(params, d);
    const weeks = [];
    for (let start = 0, w = 1; start < days; start += 7, w++) {
        const len = Math.min(7, days - start);
        const alive = d.flock * (1 - (mortalityPct / 100) * (start / days));
        const grams = rationG(params, d.ration, w);
        const layingDays = Math.max(0, Math.min(len, start + len - rearDays));
        weeks.push({
            week: w, days: len, alive, grams,
            feedKg: feedForDays(alive, grams, len),
            eggs: layingDays ? eggsLaid(alive * femalesShare, layRatePct, layingDays) : 0,
        });
    }
    return weeks;
}

function scenarioRates(params, d, scenario) {
    return {
        mortalityPct: clamp(num(params, `mort_${scenario}`) + d.mortalityExtra, 0, 100),
        layRatePct: clamp(num(params, `lay_${scenario}`) * d.layingFactor, 0, 100),
        feedFactor: num(params, `feedf_${scenario}`) / 100,
        priceFactor: (num(params, `pricef_${scenario}`) / 100) * d.priceFactor,
    };
}

function revenueFor(params, d, ctx) {
    const { aliveEnd, eggsLaidTotal, priceFactor, split } = ctx;
    if (d.goal === 'chair') {
        const weightKg = (d.weightKg ?? num(params, 'weight_45d_kg')) * d.weightFactor;
        const kgSold = aliveEnd * weightKg * (num(params, 'carcass_yield_pct') / 100);
        return { revenue: kgSold * num(params, 'frozen_price_kg') * priceFactor, kgSold, weightKg, mainUnits: kgSold };
    }
    const lossPct = clamp(num(params, 'egg_loss_pct') + d.eggLossExtra, 0, 100);
    const eggsNet = eggsLaidTotal * (1 - lossPct / 100);
    const reformRevenue = aliveEnd * num(params, 'reform_price_bird') * priceFactor;
    if (d.goal === 'oeufs') {
        return { revenue: eggsNet * num(params, 'egg_price') * priceFactor + reformRevenue, eggsNet, eggsTable: eggsNet, reformRevenue, lossPct, mainUnits: eggsNet };
    }
    const share = (k) => Math.round((eggsNet * d.alloc[k]) / 100);
    const [eggsTable, eggsIncubated, eggsSold] = [share('table'), share('incubate'), share('sell')];
    const hatch = incubate(eggsIncubated, {
        fertilityPct: num(params, 'fertility_pct'), hatchPct: clamp(num(params, 'hatch_pct') + d.hatchExtra, 0, 100), base: d.hatchBase,
    });
    const revenue = (eggsTable * num(params, 'egg_price') + eggsSold * num(params, 'fertile_egg_price') + hatch.chicks * num(params, 'chick_sale_price')) * priceFactor + reformRevenue;
    return { revenue, eggsNet, eggsTable, eggsIncubated, eggsSold, hatch, reformRevenue, lossPct, females: split.females, mainUnits: eggsNet };
}

function costsFor(params, d, ctx) {
    const { feedKg, days, feedFactor } = ctx;
    const density = d.density ?? num(params, 'density_adult');
    const surfaceAdult = surfaceM2(d.flock, density);
    const surfaceBrooder = surfaceM2(d.flock, num(params, 'density_brooder'));
    const feedWithWaste = feedKg * (1 + num(params, 'feed_waste_pct') / 100);
    const { bags, leftoverKg } = bagsNeeded(feedWithWaste, d.bagKg);
    const feedPriceKg = num(params, 'feed_price_kg') * feedFactor * d.feedPriceFactor;
    const invest = d.buildMode === 'new' ? surfaceAdult * num(params, 'build_cost_m2') : 0;
    const lines = {
        chicks: d.flock * num(params, `chick_price_${d.zone}`),
        feed: bags * d.bagKg * feedPriceKg,
        perBird: d.flock * (num(params, 'litter_per_bird') + num(params, 'misc_per_bird') + d.extraPerBird),
        cleaning: d.buildMode === 'new' ? 0 : surfaceAdult * num(params, 'clean_cost_m2'),
        incubation: (ctx.eggsIncubated ?? 0) * num(params, 'incubation_cost_egg'),
        labour: num(params, 'labour_month') * (days / DAYS_PER_MONTH),
        extraFixed: d.extraFixed,
    };
    const amortization = invest * (days / 365) / num(params, 'building_life_years');
    return { density, surfaceAdult, surfaceBrooder, feedWithWaste, bags, leftoverKg, feedPriceKg, invest, lines, amortization };
}

export function endingFor(r, flags = []) {
    if (r.mortalityPct >= DISASTER_MORTALITY_PCT || flags.includes('outbreak_ignored')) return 'disaster';
    if (r.result < 0) return 'loss';
    if (r.revenue <= 0 || r.result / r.revenue < TIGHT_MARGIN_RATE) return 'tight';
    return 'success';
}

export function simulate(params, decisions, scenarioOverride = null) {
    const d = { ...DEFAULT_DECISIONS, ...decisions, alloc: { ...DEFAULT_DECISIONS.alloc, ...(decisions.alloc || {}) } };
    validate(params, d);
    const scenario = scenarioOverride ?? d.scenario;
    if (!SCENARIOS.includes(scenario)) throw new SimError('scenario_unknown');
    const rates = scenarioRates(params, d, scenario);
    const split = flockSplit(params, d);
    const { days, rearDays, layDays } = timeline(params, d);
    const weeks = weeklySeries(params, d, rates.mortalityPct, rates.layRatePct, d.flock ? split.females / d.flock : 0);
    const feedKg = weeks.reduce((s, w) => s + w.feedKg, 0);
    const eggsLaidTotal = weeks.reduce((s, w) => s + w.eggs, 0);
    const aliveEnd = Math.round(d.flock * (1 - rates.mortalityPct / 100));
    const sales = revenueFor(params, d, { aliveEnd, eggsLaidTotal, priceFactor: rates.priceFactor, split });
    const costs = costsFor(params, d, { feedKg, days, feedFactor: rates.feedFactor, eggsIncubated: sales.eggsIncubated });
    const opex = Object.values(costs.lines).reduce((a, b) => a + b, 0);
    const variable = opex - costs.lines.labour - costs.lines.extraFixed;
    const fixed = costs.lines.labour + costs.lines.extraFixed + costs.amortization;
    const margin = sales.revenue - opex;
    const result = margin - costs.amortization;
    const r = {
        scenario, goal: d.goal, flock: d.flock, ...split, ...rates, days, rearDays, layDays, weeks, feedKg, eggsLaidTotal, aliveEnd,
        dead: d.flock - aliveEnd, ...sales, ...costs, opex, variable, fixed, margin, result,
        breakEvenRevenue: breakEven(fixed, sales.revenue, variable),
        unitCost: sales.mainUnits > 0 ? (opex + costs.amortization) / sales.mainUnits : null,
        costPerBird: aliveEnd > 0 ? (opex + costs.amortization) / aliveEnd : null,
        eggsPerFemale: split.females > 0 ? eggsLaidTotal / split.females : null,
    };
    r.ending = endingFor(r, d.flags);
    return r;
}

export function compareScenarios(params, decisions) {
    return Object.fromEntries(SCENARIOS.map((s) => [s, simulate(params, decisions, s)]));
}

/** Group weekly rows into periods (chair: weeks; long cycles: 4-week blocks) with a running cumulative. */
export function feedPeriods(weeks, size = weeks.length > 12 ? 4 : 1) {
    const out = [];
    let cumul = 0;
    for (let i = 0; i < weeks.length; i += size) {
        const block = weeks.slice(i, i + size);
        const kg = block.reduce((s, w) => s + w.feedKg, 0);
        cumul += kg;
        out.push({ from: block[0].week, to: block[block.length - 1].week, kg, cumul, grams: block[block.length - 1].grams, eggs: block.reduce((s, w) => s + w.eggs, 0) });
    }
    return out;
}

/** HUD values "so far" at a chapter: cycle fraction elapsed per chapter (1..8). */
const CHAPTER_PROGRESS = [0, 0, 0, 0.1, 0.25, 0.4, 0.7, 1, 1];
export function progressAt(r, chapter) {
    const f = CHAPTER_PROGRESS[clamp(chapter, 0, 8)] ?? 1;
    const ordered = chapter >= 2;
    const built = chapter >= 3 ? r.invest : 0;
    const running = (r.opex - r.lines.chicks) * f;
    return {
        spent: (ordered ? r.lines.chicks : 0) + built + running,
        alive: ordered ? Math.round(r.flock * (1 - (r.mortalityPct / 100) * f)) : 0,
        mortalityPct: r.mortalityPct * f,
        feedKg: r.feedKg * f,
        day: Math.round(r.days * f),
        fraction: f,
    };
}
