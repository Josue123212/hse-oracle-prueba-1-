<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Program;
use Illuminate\Support\Facades\Session;

class ProgramSwitcher extends Component
{
    public $selectedProgramId;
    public $programs = [];

    public function mount()
    {
        $this->programs = Program::orderBy('nombre')->pluck('nombre', 'id')->toArray();
        $this->selectedProgramId = Session::get('hse_program_id');
    }

    public function updatedSelectedProgramId($value)
    {
        if ($value) {
            Session::put('hse_program_id', $value);
        } else {
            Session::forget('hse_program_id');
        }
        
        $this->js('window.location.reload()');
    }

    public function render()
    {
        return view('livewire.program-switcher');
    }
}
