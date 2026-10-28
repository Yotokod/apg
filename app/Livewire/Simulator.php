<?php

namespace App\Livewire;

use App\Support\SimulatorConfig;
use Livewire\Component;

/** Boots the interactive-story simulator; every state transition runs client-side (resources/js/sim). */
class Simulator extends Component
{
    public function render()
    {
        return view('livewire.simulator', ['config' => SimulatorConfig::build()])
            ->layout('components.layouts.app', ['title' => __('nav.simulator').' | '.site('name'), 'description' => __('sim.ui.meta')]);
    }
}
