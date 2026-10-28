// Lottie JSON player (x-data="apgLottie({ src })"): lazy load when visible, skeleton, error + retry,
// play/pause/replay, 1x/1.5x speed, scrub bar; reduced motion shows the last frame as a static poster.
import lottie from 'lottie-web/build/player/lottie_light';
import { prefersReducedMotion, onVisible } from './shared';

const LOAD_TIMEOUT_MS = 15000;

export default function apgLottie({ src }) {
    let anim = null;
    let timer = null;
    let stopObserving = () => {};

    return {
        status: 'idle',
        playing: false,
        speed: 1,
        frame: 0,
        total: 0,
        transcript: false,
        reduced: prefersReducedMotion(),

        init() {
            stopObserving = onVisible(this.$el, (isVisible) => {
                if (isVisible && this.status === 'idle') this.load();
                if (!isVisible && this.playing) anim?.pause();
                if (isVisible && this.playing) anim?.play();
            }, 0.1);
        },

        destroy() {
            stopObserving();
            this.unload();
        },

        load() {
            this.status = 'loading';
            timer = setTimeout(() => this.fail(), LOAD_TIMEOUT_MS);
            anim = lottie.loadAnimation({ container: this.$refs.stage, renderer: 'svg', loop: true, autoplay: false, path: src });
            anim.addEventListener('DOMLoaded', () => this.ready());
            anim.addEventListener('data_failed', () => this.fail());
            anim.addEventListener('error', () => this.fail());
            anim.addEventListener('enterFrame', () => { this.frame = Math.round(anim.currentFrame); });
        },

        ready() {
            clearTimeout(timer);
            this.status = 'ready';
            this.total = Math.max(0, Math.floor(anim.totalFrames) - 1);
            if (this.reduced) {
                anim.goToAndStop(this.total, true);
                this.frame = this.total;
                return;
            }
            anim.play();
            this.playing = true;
        },

        fail() {
            clearTimeout(timer);
            if (this.status === 'ready') return;
            this.unload();
            this.status = 'error';
        },

        unload() {
            clearTimeout(timer);
            anim?.destroy();
            anim = null;
            this.playing = false;
        },

        retry() {
            this.unload();
            this.load();
        },

        toggle() {
            if (!anim) return;
            this.playing ? anim.pause() : anim.play();
            this.playing = !this.playing;
        },

        replay() {
            if (!anim) return;
            anim.goToAndPlay(0, true);
            this.playing = true;
        },

        setSpeed(value) {
            this.speed = value;
            anim?.setSpeed(value);
        },

        scrub(value) {
            if (!anim) return;
            anim.goToAndStop(Number(value), true);
            this.playing = false;
        },
    };
}
