<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensIntangiveis\TipoBemIntangivelResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensIntangiveis\TipoBemIntangivelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTipoBemIntangivels extends ListRecords
{
    protected static string $resource = TipoBemIntangivelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
