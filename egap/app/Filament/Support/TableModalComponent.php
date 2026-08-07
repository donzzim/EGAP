<?php

namespace App\Filament\Support;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Base para os modais que exibem apenas uma tabela, abertos a partir de uma
 * Action criada com {@see TableModalAction::make()}. Cada subclasse só
 * precisa implementar mount() e table(); o envelope do modal (wrapper com
 * scroll interno) é sempre o mesmo, definido em
 * resources/views/livewire/support/table-modal.blade.php.
 */
abstract class TableModalComponent extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public function render(): View
    {
        return view(TableModalAction::VIEW);
    }
}
