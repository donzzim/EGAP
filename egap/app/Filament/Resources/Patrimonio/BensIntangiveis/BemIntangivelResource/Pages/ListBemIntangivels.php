<?php

namespace App\Filament\Resources\Patrimonio\BensIntangiveis\BemIntangivelResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Patrimonio\BensIntangiveis\BemIntangivelResource;
use App\Filament\Resources\Patrimonio\BensIntangiveis\BemIntangivelResource\Widgets\BensIntangiveisCountStats;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBemIntangivels extends ListRecords
{
    protected static string $resource = BemIntangivelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BensIntangiveisCountStats::class,
        ];
    }
}
