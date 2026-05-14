<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensMoveis\BemMovelResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensMoveis\BemMovelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBemMovels extends ListRecords
{
    protected static string $resource = BemMovelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
