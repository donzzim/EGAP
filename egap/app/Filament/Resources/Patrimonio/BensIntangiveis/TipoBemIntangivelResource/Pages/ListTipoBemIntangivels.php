<?php

namespace App\Filament\Resources\Patrimonio\BensIntangiveis\TipoBemIntangivelResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Patrimonio\BensIntangiveis\TipoBemIntangivelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTipoBemIntangivels extends ListRecords
{
    protected static string $resource = TipoBemIntangivelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo')
        ];
    }
}
