<?php

namespace App\Filament\Egap\Resources\Admin\LotacaoResource\Pages;

use App\Filament\Egap\Resources\Admin\LotacaoResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateLotacao extends CreateRecord
{
    protected static string $resource = LotacaoResource::class;

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Lotacao criada com sucesso.');
    }
}
