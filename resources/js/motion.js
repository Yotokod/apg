// Motion system: GSAP (reveals, counters) + Lottie (lesson illustrations, JSON animations).
// Markup API (all optional, all respect prefers-reduced-motion):
//   data-reveal                 fade/slide in when scrolled into view
//   data-stagger                children of this element reveal one after another
//   data-count="1200"           number counts up when visible
//   data-lottie="/path.json"    Lottie animation, lazy-loaded when visible (data-loop="false" to play once)
import gsap from 'gsap';
import lottie from 'lottie-web/build/player/lottie_light';

const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
document.documentElement.classList.add(reduced ? 'no-motion' : 'motion-ready');

const io = new IntersectionObserver((entries) => {
    entries.forEach((e) => {
        if (!e.isIntersecting) return;
        io.unobserve(e.target);
        play(e.target);
    });
}, { threshold: 0.15 });

function play(el) {
    if (el.dataset.lottie) return startLottie(el);
    if (el.dataset.count) return count(el);
    if (el.hasAttribute('data-stagger')) {
        gsap.fromTo(el.children, { opacity: 0, y: 18 }, { opacity: 1, y: 0, duration: 0.55, stagger: 0.08, ease: 'power2.out', clearProps: 'all' });
        return;
    }
    el.classList.add('is-in');
}

function count(el) {
    const end = Number(el.dataset.count) || 0;
    if (reduced) { el.textContent = end.toLocaleString(); return; }
    const o = { v: 0 };
    gsap.to(o, { v: end, duration: 1.2, ease: 'power1.out', onUpdate: () => { el.textContent = Math.round(o.v).toLocaleString(); } });
}

function startLottie(el) {
    if (el._anim) return;
    el._anim = lottie.loadAnimation({
        container: el,
        renderer: 'svg',
        loop: el.dataset.loop !== 'false',
        autoplay: !reduced,
        path: el.dataset.lottie,
    });
    if (reduced) el._anim.goToAndStop(el._anim.totalFrames ? el._anim.totalFrames - 1 : 0, true);
}

// Controls used by <x-motion>: toggle play/pause and replay
window.apgMotion = {
    toggle(btn) { const a = btn.closest('[data-motion]').querySelector('[data-lottie]')._anim; if (a) (a.isPaused ? a.play() : a.pause()); },
    replay(btn) { const a = btn.closest('[data-motion]').querySelector('[data-lottie]')._anim; if (a) a.goToAndPlay(0, true); },
};

function scan() {
    document.querySelectorAll('[data-reveal],[data-stagger],[data-count],[data-lottie]').forEach((el) => {
        if (el.dataset.seen) return;
        el.dataset.seen = '1';
        if (el.hasAttribute('data-stagger') && !reduced) gsap.set(el.children, { opacity: 0 });
        io.observe(el);
    });
}

let queued = false;
new MutationObserver(() => {
    if (queued) return;
    queued = true;
    requestAnimationFrame(() => { queued = false; scan(); });
}).observe(document.documentElement, { childList: true, subtree: true });

document.addEventListener('DOMContentLoaded', scan);
document.addEventListener('livewire:navigated', scan);
scan();
