// Story-style branching quiz (x-data="apgQuiz(config)") driven by the #[Locked] Livewire Quiz component.
// The client never knows the right answer before picking: $wire.answer() returns correctness,
// explanation and the next question; transitions/feedback animations run client-side.
import gsap from 'gsap';
import { prefersReducedMotion } from './shared';

const LETTER_ICONS = ['fa-a', 'fa-b', 'fa-c', 'fa-d', 'fa-e', 'fa-f'];
const CONFETTI_COLORS = ['#D9480F', '#FFB347', '#4FA65F', '#0B0B2B', '#FFD166'];

export default function apgQuiz(config) {
    return {
        t: config.t,
        total: Math.max(1, config.total),
        passPercent: config.passPercent,
        scenes: config.scenes,
        node: config.node,
        good: config.good,
        answered: config.answered,
        percent: config.percent,
        canBack: config.canBack,
        phase: config.finished ? 'result' : 'ask',
        shownPercent: config.percent,
        streak: 0,
        streaks: [],
        selected: null,
        feedback: null,
        error: null,
        busy: false,
        focusIndex: 0,
        announce: '',

        get passed() { return this.percent >= this.passPercent; },
        get ringPercent() { return this.phase === 'result' ? 100 : Math.min(100, Math.round(this.answered / this.total * 100)); },
        get sceneIndex() { return this.answered % this.scenes; },
        get rightLabel() {
            const ids = this.feedback?.correctIds || [];
            return (this.node?.options || []).filter((o) => ids.includes(o.id)).map((o) => o.label).join(' / ');
        },

        fmt(template, values) {
            return Object.entries(values).reduce((s, [k, v]) => s.replace(`:${k}`, v), template);
        },

        optionIcon(id, idx) {
            if (this.phase === 'feedback' && this.feedback.correctIds.includes(id)) return 'fa-check';
            if (this.phase === 'feedback' && id === this.selected) return 'fa-xmark';
            if (this.phase === 'checking' && id === this.selected) return 'fa-spinner fa-spin';
            return LETTER_ICONS[idx] || 'fa-circle';
        },

        optionClass(id) {
            if (this.phase === 'checking') return id === this.selected ? 'is-selected' : 'is-dim';
            if (this.phase !== 'feedback') return '';
            if (this.feedback.correctIds.includes(id)) return 'is-correct';
            return id === this.selected ? 'is-wrong' : 'is-dim';
        },

        onKey(event, idx) {
            const count = this.node?.options.length || 0;
            const moves = { ArrowDown: 1, ArrowRight: 1, ArrowUp: -1, ArrowLeft: -1 };
            if (!(event.key in moves) || !count) return;
            event.preventDefault();
            this.focusIndex = (idx + moves[event.key] + count) % count;
            this.$el.querySelectorAll('[data-option]')[this.focusIndex]?.focus();
        },

        stats(res) {
            this.good = res.good;
            this.answered = res.answered;
            this.percent = res.percent;
            this.canBack = res.canBack;
        },

        async pick(id, card) {
            if (this.phase !== 'ask' || this.busy) return;
            this.selected = id;
            this.phase = 'checking';
            this.error = null;
            this.busy = true;
            try {
                const res = await this.$wire.answer(id);
                if (res.stale) throw new Error('stale');
                this.showFeedback(res, card);
            } catch {
                this.phase = 'ask';
                this.selected = null;
                this.error = this.t.error;
            } finally {
                this.busy = false;
            }
        },

        showFeedback(res, card) {
            this.feedback = res;
            this.streaks.push(this.streak);
            this.streak = res.correct ? this.streak + 1 : 0;
            this.stats(res);
            this.phase = 'feedback';
            this.$nextTick(() => {
                this.announce = this.$el.querySelector('[data-feedback]')?.innerText || '';
                res.correct ? this.celebrate(card) : this.shake(card);
                this.$refs.continue?.focus({ preventScroll: true });
            });
        },

        proceed() {
            const res = this.feedback;
            if (!res) return;
            this.feedback = null;
            this.selected = null;
            if (res.finished) return this.showResult();
            this.node = res.node;
            this.phase = 'ask';
            this.enterQuestion();
        },

        async back() {
            if (!this.canBack || this.busy) return;
            this.busy = true;
            try {
                const res = await this.$wire.back();
                this.node = res.node;
                this.stats(res);
                this.streak = this.streaks.pop() ?? 0;
                this.feedback = null;
                this.selected = null;
                this.phase = 'ask';
                this.enterQuestion(-1);
            } catch {
                this.error = this.t.error;
            } finally {
                this.busy = false;
            }
        },

        async restart() {
            if (this.busy) return;
            this.busy = true;
            try {
                const res = await this.$wire.restart();
                Object.assign(this, { node: res.node, streak: 0, streaks: [], feedback: null, selected: null, error: null, phase: 'ask' });
                this.stats(res);
                this.enterQuestion();
            } catch {
                this.error = this.t.error;
            } finally {
                this.busy = false;
            }
        },

        enterQuestion(direction = 1) {
            this.focusIndex = 0;
            this.$nextTick(() => {
                const first = this.$el.querySelector('[data-option]');
                first?.focus({ preventScroll: true });
                const options = this.$el.querySelectorAll('[data-option]');
                if (prefersReducedMotion() || !options.length) return;
                gsap.fromTo(this.$el.querySelector('[data-question-card]'), { opacity: 0, x: 40 * direction }, { opacity: 1, x: 0, duration: 0.4, ease: 'power2.out', clearProps: 'all' });
                gsap.fromTo(options,{ opacity: 0, y: 14 }, { opacity: 1, y: 0, duration: 0.35, stagger: 0.06, delay: 0.1, ease: 'power2.out', clearProps: 'all' });
            });
        },

        showResult() {
            this.phase = 'result';
            this.shownPercent = this.percent;
            this.$nextTick(() => {
                this.$el.querySelector('[data-result]')?.scrollIntoView({ block: 'nearest', behavior: prefersReducedMotion() ? 'auto' : 'smooth' });
                if (prefersReducedMotion()) return;
                const counter = { v: 0 };
                gsap.to(counter, { v: this.percent, duration: 1, ease: 'power1.out', onUpdate: () => { this.shownPercent = Math.round(counter.v); } });
                gsap.fromTo(this.$el.querySelector('[data-result-badge]'), { scale: 0.4, rotate: -20, opacity: 0 }, { scale: 1, rotate: 0, opacity: 1, duration: 0.7, ease: 'back.out(2)' });
                if (this.passed) this.confetti(this.$el.querySelector('[data-result-badge]'));
            });
        },

        shake(card) {
            if (!card || prefersReducedMotion()) return;
            gsap.fromTo(card, { x: 0 }, { keyframes: { x: [0, -10, 10, -7, 7, -3, 0] }, duration: 0.45, ease: 'power1.inOut', clearProps: 'transform' });
        },

        celebrate(card) {
            if (!card || prefersReducedMotion()) return;
            gsap.fromTo(card, { scale: 1 }, { scale: 1.035, duration: 0.16, yoyo: true, repeat: 1, ease: 'power1.out', clearProps: 'transform' });
            this.confetti(card);
        },

        confetti(origin) {
            if (!origin || prefersReducedMotion()) return;
            const host = this.$el;
            const box = host.getBoundingClientRect();
            const from = origin.getBoundingClientRect();
            for (let n = 0; n < 16; n++) {
                const bit = document.createElement('span');
                bit.className = 'lsn-confetti';
                bit.style.background = CONFETTI_COLORS[n % CONFETTI_COLORS.length];
                bit.style.left = `${from.left - box.left + from.width / 2}px`;
                bit.style.top = `${from.top - box.top + from.height / 2}px`;
                host.appendChild(bit);
                gsap.to(bit, {
                    x: gsap.utils.random(-120, 120), y: gsap.utils.random(-110, 20), rotate: gsap.utils.random(-200, 200),
                    opacity: 0, duration: gsap.utils.random(0.7, 1.1), ease: 'power2.out', onComplete: () => bit.remove(),
                });
            }
        },
    };
}
