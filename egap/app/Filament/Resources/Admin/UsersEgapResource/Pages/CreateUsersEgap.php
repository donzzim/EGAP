<?php

namespace App\Filament\Egap\Resources\Admin\UsersEgapResource\Pages;

use App\Filament\Egap\Resources\Admin\UsersEgapResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class CreateUsersEgap extends CreateRecord
{
    protected static string $resource = UsersEgapResource::class;

    protected array $infoUserData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->infoUserData = Arr::only($data, ['cpf', 'matricula', 'cargo']);

        unset($data['cpf'], $data['matricula'], $data['cargo']);

        $now = now();

        $data['password'] = Hash::make($data['password']);
        $data['registerDate'] = $now;
        $data['lastvisitDate'] = $now;
        $data['activation'] = '';
        $data['params'] = '';
        $data['lastResetTime'] = $now;
        $data['resetCount'] = 0;
        $data['otpKey'] = '';
        $data['otep'] = '';
        $data['sendEmail'] = (bool) ($data['sendEmail'] ?? false);
        $data['block'] = (bool) ($data['block'] ?? false);
        $data['requireReset'] = (bool) ($data['requireReset'] ?? false);

        return $data;
    }

    protected function afterCreate(): void
    {
        $payload = collect($this->infoUserData)
            ->map(fn ($value) => filled($value) ? trim((string) $value) : null)
            ->all();

        if (blank($payload['cpf']) && blank($payload['matricula']) && blank($payload['cargo'])) {
            return;
        }

        $this->record->infoUser()->create($payload);
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Usuário EGAP criado com sucesso.');
    }
}
