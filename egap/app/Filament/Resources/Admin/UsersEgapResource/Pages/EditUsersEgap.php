<?php

namespace App\Filament\Egap\Resources\Admin\UsersEgapResource\Pages;

use App\Filament\Egap\Resources\Admin\UsersEgapResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Arr;

class EditUsersEgap extends EditRecord
{
    protected static string $resource = UsersEgapResource::class;

    protected array $infoUserData = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['cpf'] = $this->record->infoUser?->cpf;
        $data['matricula'] = $this->record->infoUser?->matricula;
        $data['cargo'] = $this->record->infoUser?->cargo;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->infoUserData = Arr::only($data, ['cpf', 'matricula', 'cargo']);

        unset($data['cpf'], $data['matricula'], $data['cargo']);

        return $data;
    }

    protected function afterSave(): void
    {
        $payload = collect($this->infoUserData)
            ->map(fn ($value) => filled($value) ? trim((string) $value) : null)
            ->all();

        if (blank($payload['cpf']) && blank($payload['matricula']) && blank($payload['cargo'])) {
            $this->record->infoUser()->delete();
            $this->record->unsetRelation('infoUser');

            return;
        }

        $infoUser = $this->record->infoUser()->updateOrCreate(
            ['usuario_id' => $this->record->id],
            $payload,
        );

        $this->record->setRelation('infoUser', $infoUser);
    }
}
