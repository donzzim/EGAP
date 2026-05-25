<?php

namespace App\Filament\Resources\Patrimonio\BensIntangiveis\BemIntangivelResource\Pages;

use App\Filament\Resources\Patrimonio\BensIntangiveis\BemIntangivelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBemIntangivels extends ListRecords
{
    protected static string $resource = BemIntangivelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Novo')
        ];
    }
}
