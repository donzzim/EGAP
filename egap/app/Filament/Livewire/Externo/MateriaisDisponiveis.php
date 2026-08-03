<?php

namespace App\Filament\Livewire\Externo;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Livewire\Component;

abstract class MateriaisDisponiveis extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;


}
