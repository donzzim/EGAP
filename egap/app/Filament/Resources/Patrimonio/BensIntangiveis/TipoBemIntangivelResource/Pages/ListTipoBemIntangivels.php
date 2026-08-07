<?php

namespace App\Filament\Resources\Patrimonio\BensIntangiveis\TipoBemIntangivelResource\Pages;

use App\Filament\Resources\Patrimonio\BensIntangiveis\TipoBemIntangivelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTipoBemIntangivels extends ListRecords
{
    protected static string $resource = TipoBemIntangivelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo'),
        ];
    }
}
