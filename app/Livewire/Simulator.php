<?php

namespace App\Livewire;

use App\Models\SimChoice;
use App\Models\SimStep;
use Livewire\Component;

class Simulator extends Component
{
    public const START_CASH = 1000000;
    public const PRICE_PER_BIRD = 4500; // FCFA, adjustable later via settings

    public int $index = 0;
    public array $state = ['cash' => self::START_CASH, 'birds' => 0, 'mortality' => 5, 'revenue_pct' => 0];
    public array $history = [];   // stack of [index, state] for the "back" button
    public ?int $warnChoice = null; // bad choice awaiting confirmation (popup)

    public function choose(int $choiceId): void
    {
        $choice = SimChoice::findOrFail($choiceId);
        if ($choice->is_bad && $this->warnChoice !== $choiceId) {
            $this->warnChoice = $choiceId; // show explanation popup first

            return;
        }
        $this->apply($choice);
    }

    public function confirmBad(): void
    {
        if ($this->warnChoice) {
            $this->apply(SimChoice::findOrFail($this->warnChoice));
        }
    }

    public function dismissWarning(): void
    {
        $this->warnChoice = null; // stay on the step to pick differently
    }

    public function back(): void
    {
        if ($last = array_pop($this->history)) {
            [$this->index, $this->state] = $last;
        }
        $this->warnChoice = null;
    }

    public function restart(): void
    {
        $this->reset('index', 'history', 'warnChoice');
        $this->state = ['cash' => self::START_CASH, 'birds' => 0, 'mortality' => 5, 'revenue_pct' => 0];
    }

    private function apply(SimChoice $c): void
    {
        $this->history[] = [$this->index, $this->state];
        $this->state['cash'] += $c->cash;
        $this->state['birds'] += $c->birds;
        $this->state['mortality'] = max(0, $this->state['mortality'] + $c->mortality);
        $this->state['revenue_pct'] += $c->revenue_pct;
        $this->index++;
        $this->warnChoice = null;
    }

    public function render()
    {
        $steps = SimStep::with('choices')->orderBy('position')->get();
        $step = $steps[$this->index] ?? null;
        $result = null;
        if (! $step && $steps->isNotEmpty()) {
            $alive = (int) round($this->state['birds'] * (1 - min(100, $this->state['mortality']) / 100));
            $sales = (int) round($alive * self::PRICE_PER_BIRD * (1 + $this->state['revenue_pct'] / 100));
            $profit = $this->state['cash'] + $sales - self::START_CASH;
            $result = compact('alive', 'sales', 'profit') + ['roi' => round($profit / self::START_CASH * 100)];
        }

        return view('livewire.simulator', [
            'steps' => $steps, 'step' => $step, 'result' => $result,
            'warn' => $this->warnChoice ? SimChoice::find($this->warnChoice) : null,
        ])->layout('components.layouts.app', ['title' => __('nav.simulator').' | '.site('name')]);
    }
}
