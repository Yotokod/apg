// Step-by-step animated explainer (x-data="apgExplainer({ total, durations })").
// Autoplays when visible, manual prev/next/dots, keyboard arrows, reduced-motion = manual only.
import gsap from 'gsap';
import { prefersReducedMotion, onVisible } from './shared';

export default function apgExplainer({ total, durations }) {
    let raf = null;
    let last = 0;
    let stopObserving = () => {};

    return {
        i: 0,
        total,
        progress: 0,
        playing: false,
        ended: false,
        userPaused: false,

        init() {
            stopObserving = onVisible(this.$el, (isVisible) => {
                if (isVisible && !this.ended && !this.userPaused && !prefersReducedMotion()) this.play();
                if (!isVisible) this.halt();
            });
        },

        destroy() {
            this.halt();
            stopObserving();
        },

        fill(step) {
            if (step < this.i) return 1;
            return step === this.i ? this.progress : 0;
        },

        play() {
            if (this.playing) return;
            this.playing = true;
            last = performance.now();
            raf = requestAnimationFrame((t) => this.tick(t));
        },

        halt() {
            this.playing = false;
            cancelAnimationFrame(raf);
        },

        toggle() {
            if (this.playing) {
                this.userPaused = true;
                return this.halt();
            }
            if (this.ended) this.go(0);
            this.userPaused = false;
            this.play();
        },

        tick(now) {
            if (!this.playing) return;
            this.progress = Math.min(1, this.progress + (now - last) / ((durations[this.i] || 8) * 1000));
            last = now;
            if (this.progress >= 1) {
                if (this.i >= this.total - 1) {
                    this.halt();
                    this.ended = true;
                    return;
                }
                this.go(this.i + 1, true);
            }
            raf = requestAnimationFrame((t) => this.tick(t));
        },

        prev() { if (this.i > 0) this.go(this.i - 1); },
        next() { if (this.i < this.total - 1) this.go(this.i + 1); },

        go(step, auto = false) {
            const direction = step >= this.i ? 1 : -1;
            this.i = Math.max(0, Math.min(this.total - 1, step));
            this.progress = 0;
            this.ended = false;
            if (!auto) {
                this.userPaused = true;
                this.halt();
            }
            this.animateIn(direction);
        },

        animateIn(direction) {
            if (prefersReducedMotion()) return;
            this.$nextTick(() => {
                const scene = this.$el.querySelectorAll('[data-step]')[this.i];
                const text = this.$el.querySelectorAll('[data-narration]')[this.i];
                gsap.fromTo(scene, { opacity: 0, x: 28 * direction, scale: 0.97 }, { opacity: 1, x: 0, scale: 1, duration: 0.5, ease: 'power2.out', clearProps: 'transform,opacity' });
                gsap.fromTo(text, { opacity: 0, y: 10 }, { opacity: 1, y: 0, duration: 0.4, delay: 0.1, ease: 'power2.out', clearProps: 'transform,opacity' });
            });
        },
    };
}
