// Lesson workspace Alpine components (Alpine is bundled with Livewire).
import apgExplainer from './explainer';
import apgLottie from './lottie';
import apgQuiz from './quiz';

const components = { apgExplainer, apgLottie, apgQuiz };

function register(Alpine) {
    Object.entries(components).forEach(([name, factory]) => Alpine.data(name, factory));
}

if (window.Alpine) register(window.Alpine);
document.addEventListener('alpine:init', () => register(window.Alpine));
