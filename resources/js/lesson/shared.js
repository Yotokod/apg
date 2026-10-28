export const prefersReducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/** Calls cb(isVisible) whenever el enters/leaves the viewport; returns a disconnect function. */
export function onVisible(el, cb, threshold = 0.25) {
    const io = new IntersectionObserver(([entry]) => cb(entry.isIntersecting), { threshold });
    io.observe(el);
    return () => io.disconnect();
}
