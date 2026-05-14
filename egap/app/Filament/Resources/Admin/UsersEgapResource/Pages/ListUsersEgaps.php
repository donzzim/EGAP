<?php

namespace App\Filament\Resources\Admin\UsersEgapResource\Pages;

use App\Filament\Resources\Admin\UsersEgapResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsersEgaps extends ListRecords
{
    protected static string $resource = UsersEgapResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn (): bool => in_array(auth()->user()?->login, ['admin', 'admin2'], true)),
        ];
    }
}
