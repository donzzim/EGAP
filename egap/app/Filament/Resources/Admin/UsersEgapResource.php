<?php

namespace App\Filament\Resources\Admin;

use App\Filament\Clusters\AdminEgapCluster;
use App\Filament\Resources\Admin\UsersEgapResource\Pages;
use App\Models\Admin\InfoUser;
use App\Models\Admin\Lotacao;
use App\Models\Cadastro\Setores;
use App\Models\User;
use App\Models\UserEgap;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class UsersEgapResource extends Resource
{
    protected const EMAIL_DOMAIN = '@tjes.jus.br';

    protected static ?string $model = UserEgap::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Usuários EGAP';
    protected static ?string $cluster = AdminEgapCluster::class;
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
    protected static ?string $slug = 'users-egap';
    protected static ?string $modelLabel = 'Usuário EGAP';
    protected static ?string $pluralModelLabel = 'Usuários EGAP';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Conta')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(400),
                        Forms\Components\TextInput::make('username')
                            ->label('Login')
                            ->required()
                            ->maxLength(150),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->required()
                            ->suffix(static::EMAIL_DOMAIN, true)
                            ->helperText('Informe apenas a parte antes de @tjes.jus.br.')
                            ->formatStateUsing(fn (?string $state): ?string => static::extractEmailLocalPart($state))
                            ->dehydrateStateUsing(fn (?string $state): ?string => static::buildInstitutionalEmail($state))
                            ->rule(fn (): \Closure => function (string $attribute, mixed $value, \Closure $fail): void {
                                if (! static::isValidInstitutionalEmailInput($value)) {
                                    $fail('Informe apenas o usuário do e-mail institucional.');
                                }
                            })
                            ->maxLength(100 - strlen(static::EMAIL_DOMAIN)),
                        Forms\Components\TextInput::make('password')
                            ->label('Senha')
                            ->password()
                            ->revealable()
                            ->required()
                            ->visibleOn('create')
                            ->maxLength(100),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Acesso')
                    ->schema([
                        Forms\Components\Toggle::make('block')
                            ->label('Bloqueado?'),
                        Forms\Components\Toggle::make('sendEmail')
                            ->label('Recebe email?'),
                        Forms\Components\Toggle::make('requireReset')
                            ->label('Exigir reset de senha?'),
                    ])
                    ->columns(3),
                Forms\Components\Section::make('Dados Pessoais')
                    ->schema([
                        Forms\Components\TextInput::make('cpf')
                            ->label('CPF')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('matricula')
                            ->label('Matricula')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('cargo')
                            ->label('Cargo')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(25)
            ->defaultSort('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('username')
                    ->label('Login')
                    ->searchable()
                    ->badge()
                    ->alignCenter()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->alignCenter(),
                Tables\Columns\IconColumn::make('block')
                    ->label('Acesso')
                    ->alignCenter()
                    ->icon(fn (bool $state): string => $state ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (bool $state): string => $state ? 'danger' : 'success')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('lotacao')
                    ->color('gray')
                    ->label('Lotação')
                    ->icon('heroicon-o-building-office-2')
                    ->modalHeading(fn (UserEgap $record): string => "Lotação de {$record->name}")
                    ->modalWidth('5xl')
                    ->modalSubmitAction(false)
                    ->extraModalFooterActions([
                        Tables\Actions\Action::make('visualizar_lotacao')
                            ->label('Visualizar')
                            ->icon('heroicon-o-eye')
                            ->color('gray')
                            ->visible(fn (UserEgap $record): bool => $record->lotacoes()->exists())
                            ->fillForm(fn (UserEgap $record): array => static::getLotacaoViewData($record))
                            ->modalHeading('Visualizar lotação')
                            ->modalSubmitAction(false)
                            ->form(static::getLotacaoViewFormSchema()),
                        Tables\Actions\Action::make('editar_lotacao')
                            ->label('Editar')
                            ->icon('heroicon-o-pencil-square')
                            ->color('warning')
                            ->visible(fn (UserEgap $record): bool => $record->lotacoes()->exists())
                            ->fillForm(fn (UserEgap $record): array => static::getLotacaoEditData($record))
                            ->modalHeading('Editar lotação')
                            ->modalSubmitActionLabel('Salvar')
                            ->form(static::getLotacaoEditFormSchema())
                            ->action(function (array $data, UserEgap $record): void {
                                $lotacao = $record->lotacoes()->find($data['lotacao_id'] ?? null);

                                if (! $lotacao) {
                                    Notification::make()
                                        ->title('Lotação não encontrada.')
                                        ->danger()
                                        ->send();
                                    return;
                                }

                                $lotacao->update([
                                    'unidade_judiciaria' => $data['unidade_judiciaria'],
                                    'setor' => $data['setor'],
                                ]);

                                static::reloadLotacoes($record);

                                Notification::make()
                                    ->title('Lotação atualizada com sucesso.')
                                    ->success()
                                    ->send();
                            }),
                        Tables\Actions\Action::make('excluir_lotacao')
                            ->label('Excluir')
                            ->icon('heroicon-o-trash')
                            ->color('danger')
                            ->visible(fn (UserEgap $record): bool => $record->lotacoes()->exists())
                            ->fillForm(fn (UserEgap $record): array => static::getLotacaoDeleteData($record))
                            ->modalHeading('Excluir lotação')
                            ->modalDescription('Selecione a lotação que deve ser removida.')
                            ->requiresConfirmation()
                            ->form([
                                Forms\Components\Select::make('lotacao_id')
                                    ->label('Lotação')
                                    ->options(fn (UserEgap $record): array => static::getLotacaoOptions($record))
                                    ->required(),
                            ])
                            ->action(function (array $data, UserEgap $record): void {
                                $lotacao = $record->lotacoes()->find($data['lotacao_id'] ?? null);

                                if (! $lotacao) {
                                    Notification::make()
                                        ->title('Lotação não encontrada.')
                                        ->danger()
                                        ->send();
                                    return;
                                }

                                $lotacao->delete();
                                static::reloadLotacoes($record);

                                Notification::make()
                                    ->title('Lotação excluída com sucesso.')
                                    ->success()
                                    ->send();
                            }),
                    ])
                    ->infolist([
                        Section::make('Lotações')
                            ->schema([
                                TextEntry::make('lotacoes_vazias')
                                    ->hiddenLabel()
                                    ->state(fn (UserEgap $record): string => static::buildLotacaoRows($record) === [] ? 'Nenhuma lotação encontrada para este usuário.' : '')
                                    ->visible(fn (UserEgap $record): bool => static::buildLotacaoRows($record) === []),
                                RepeatableEntry::make('lotacoes')
                                    ->hiddenLabel()
                                    ->contained(false)
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextEntry::make('user.username')
                                                    ->label('Login')
                                                    ->placeholder('-'),
                                                TextEntry::make('unidadeJudiciaria.Setor')
                                                    ->label('Unidade Judiciaria')
                                                    ->placeholder('-'),
                                                TextEntry::make('setorRef.Setor')
                                                    ->label('Setor')
                                                    ->placeholder('-'),
                                                TextEntry::make('setorRef.UnidadeOrganizacional')
                                                    ->label('Unidade Organizacional')
                                                    ->placeholder('-')
                                                    ->columnSpanFull(),
                                            ]),
                                    ])
                                    ->visible(fn (UserEgap $record): bool => $record->lotacoes()->exists()),
                            ]),
                    ]),
                Tables\Actions\Action::make('dados_pessoais')
                    ->color('gray')
                    ->label('Dados Pessoais')
                    ->icon('heroicon-o-identification')
                    ->modalHeading(fn (UserEgap $record): string => "Dados pessoais de {$record->name}")
                    ->modalWidth('4xl')
                    ->modalSubmitAction(false)
                    ->extraModalFooterActions([
                        Tables\Actions\Action::make('visualizar_dados_pessoais')
                            ->label('Visualizar')
                            ->icon('heroicon-o-eye')
                            ->color('gray')
                            ->fillForm(fn (UserEgap $record): array => static::getInfoUserDisplayData($record))
                            ->modalHeading('Visualizar dados pessoais')
                            ->modalSubmitAction(false)
                            ->form(static::getInfoUserViewFormSchema()),
                        Tables\Actions\Action::make('editar_dados_pessoais')
                            ->label('Editar')
                            ->icon('heroicon-o-pencil-square')
                            ->color('warning')
                            ->fillForm(fn (UserEgap $record): array => static::getInfoUserEditData($record))
                            ->modalHeading('Editar dados pessoais')
                            ->modalSubmitActionLabel('Salvar')
                            ->form(static::getInfoUserEditFormSchema())
                            ->action(function (array $data, UserEgap $record): void {
                                $infoUser = $record->infoUser()->updateOrCreate(
                                    ['usuario_id' => $record->id],
                                    [
                                        'cpf' => filled($data['cpf'] ?? null) ? $data['cpf'] : null,
                                        'matricula' => filled($data['matricula'] ?? null) ? $data['matricula'] : null,
                                        'cargo' => filled($data['cargo'] ?? null) ? $data['cargo'] : null,
                                    ],
                                );

                                $record->setRelation('infoUser', $infoUser);

                                Notification::make()
                                    ->title('Dados pessoais atualizados com sucesso.')
                                    ->success()
                                    ->send();
                            }),
                        Tables\Actions\Action::make('excluir_dados_pessoais')
                            ->label('Excluir')
                            ->icon('heroicon-o-trash')
                            ->color('danger')
                            ->visible(fn (UserEgap $record): bool => $record->infoUser()->exists())
                            ->requiresConfirmation()
                            ->modalHeading('Excluir dados pessoais')
                            ->modalDescription('Os dados pessoais vinculados a este usuário serão removidos.')
                            ->action(function (UserEgap $record): void {
                                $record->infoUser()->delete();
                                $record->setRelation('infoUser', null);

                                Notification::make()
                                    ->title('Dados pessoais excluídos com sucesso.')
                                    ->success()
                                    ->send();
                            }),
                    ])
                    ->infolist([
                        Section::make('Conta')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('name')
                                            ->label('Nome')
                                            ->placeholder('-'),
                                        TextEntry::make('username')
                                            ->label('Login')
                                            ->placeholder('-'),
                                        TextEntry::make('email')
                                            ->label('Email')
                                            ->placeholder('-'),
                                        TextEntry::make('block')
                                            ->label('Bloqueado?')
                                            ->badge()
                                            ->formatStateUsing(fn (bool $state): string => $state ? 'Sim' : 'Não'),
                                    ]),
                            ]),
                        Section::make('Dados Pessoais')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('infoUser.cpf')
                                            ->label('CPF')
                                            ->placeholder('-'),
                                        TextEntry::make('infoUser.matricula')
                                            ->label('Matrícula')
                                            ->placeholder('-'),
                                        TextEntry::make('infoUser.cargo')
                                            ->label('Cargo')
                                            ->placeholder('-')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ]),
                Tables\Actions\EditAction::make()
                    ->tooltip('Editar')
                    ->hiddenLabel(),
                Tables\Actions\ViewAction::make()
                    ->tooltip('Visualizar')
                    ->hiddenLabel(),
                Tables\Actions\DeleteAction::make()
                    ->tooltip('Excluir')
                    ->modalHeading('Excluir registro')
                    ->hiddenLabel(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('infoUser');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsersEgaps::route('/'),
            'create' => Pages\CreateUsersEgap::route('/create'),
            'edit' => Pages\EditUsersEgap::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return true;
    }

    protected static function buildLotacaoRows(UserEgap $record): array
    {
        return $record->lotacoes()->limit(1)->pluck('id')->all();
    }

    protected static function reloadLotacoes(UserEgap $record): void
    {
        $record->load([
            'lotacoes' => fn ($query) => $query->with([
                'user:id,username',
                'unidadeJudiciaria:id,Setor',
                'setorRef:id,Setor,UnidadeOrganizacional',
            ]),
        ]);
    }

    protected static function getLotacaoOptions(UserEgap $record): array
    {
        static::reloadLotacoes($record);

        return $record->lotacoes
            ->mapWithKeys(fn (Lotacao $lotacao): array => [
                $lotacao->id => sprintf(
                    'ID %d - %s / %s',
                    $lotacao->id,
                    $lotacao->unidadeJudiciaria?->Setor ?? 'Sem unidade',
                    $lotacao->setorRef?->Setor ?? 'Sem setor'
                ),
            ])
            ->all();
    }

    protected static function getLotacaoViewData(UserEgap $record): array
    {
        $lotacao = $record->lotacoes()
            ->with(['unidadeJudiciaria:id,Setor', 'setorRef:id,Setor'])
            ->first();

        return [
            'lotacao_id' => $lotacao?->id,
            'login_label' => $record->username ?? '-',
            'unidade_judiciaria_label' => $lotacao?->unidadeJudiciaria?->Setor ?? '-',
            'setor_label' => $lotacao?->setorRef?->Setor ?? '-',
        ];
    }

    protected static function getLotacaoEditData(UserEgap $record): array
    {
        $lotacao = $record->lotacoes()->first();

        return [
            'lotacao_id' => $lotacao?->id,
            'unidade_judiciaria' => $lotacao?->unidade_judiciaria,
            'setor' => $lotacao?->setor,
        ];
    }

    protected static function getLotacaoDeleteData(UserEgap $record): array
    {
        return [
            'lotacao_id' => $record->lotacoes()->value('id'),
        ];
    }

    protected static function getLotacaoViewFormSchema(): array
    {
        return [
            Forms\Components\Select::make('lotacao_id')
                ->label('Lotacao')
                ->options(fn (UserEgap $record): array => static::getLotacaoOptions($record))
                ->live()
                ->afterStateUpdated(function ($state, Set $set): void {
                    static::fillLotacaoDisplayFields($state, $set);
                })
                ->required(),
            Forms\Components\TextInput::make('login_label')
                ->label('Login')
                ->disabled()
                ->dehydrated(false),
            Forms\Components\TextInput::make('unidade_judiciaria_label')
                ->label('Unidade Judiciaria')
                ->disabled()
                ->dehydrated(false),
            Forms\Components\TextInput::make('setor_label')
                ->label('Setor')
                ->disabled()
                ->dehydrated(false),
        ];
    }

    protected static function getLotacaoEditFormSchema(): array
    {
        return [
            Forms\Components\Select::make('lotacao_id')
                ->label('Lotacao')
                ->options(fn (UserEgap $record): array => static::getLotacaoOptions($record))
                ->live()
                ->afterStateUpdated(function ($state, Set $set): void {
                    static::fillLotacaoEditFields($state, $set);
                })
                ->required(),
            static::makeSetorSelect('unidade_judiciaria', 'Unidade Judiciaria'),
            static::makeSetorSelect('setor', 'Setor'),
        ];
    }

    protected static function makeSetorSelect(string $name, string $label): Forms\Components\Select
    {
        return Forms\Components\Select::make($name)
            ->label($label)
            ->searchable()
            ->getSearchResultsUsing(fn (string $search): array => static::searchSetores($search))
            ->getOptionLabelUsing(fn ($value): ?string => static::getSetorLabel($value))
            ->required();
    }

    protected static function searchSetores(string $search): array
    {
        return Setores::query()
            ->where('Setor', 'like', "%{$search}%")
            ->orWhere('UnidadeOrganizacional', 'like', "%{$search}%")
            ->orderBy('Setor')
            ->limit(50)
            ->pluck('Setor', 'id')
            ->all();
    }

    protected static function getSetorLabel($value): ?string
    {
        if (blank($value)) {
            return null;
        }

        return Setores::query()->whereKey($value)->value('Setor');
    }

    protected static function fillLotacaoDisplayFields($lotacaoId, Set $set): void
    {
        $lotacao = Lotacao::query()
            ->with(['user:id,username', 'unidadeJudiciaria:id,Setor', 'setorRef:id,Setor'])
            ->find($lotacaoId);

        $set('login_label', $lotacao?->user?->username ?? '-');
        $set('unidade_judiciaria_label', $lotacao?->unidadeJudiciaria?->Setor ?? '-');
        $set('setor_label', $lotacao?->setorRef?->Setor ?? '-');
    }

    protected static function fillLotacaoEditFields($lotacaoId, Set $set): void
    {
        $lotacao = Lotacao::query()->find($lotacaoId);

        $set('unidade_judiciaria', $lotacao?->unidade_judiciaria);
        $set('setor', $lotacao?->setor);
    }

    protected static function getInfoUserDisplayData(UserEgap $record): array
    {
        return [
            'cpf' => $record->infoUser?->cpf ?? '-',
            'matricula' => $record->infoUser?->matricula ?? '-',
            'cargo' => $record->infoUser?->cargo ?? '-',
        ];
    }

    protected static function getInfoUserEditData(UserEgap $record): array
    {
        return [
            'cpf' => $record->infoUser?->cpf,
            'matricula' => $record->infoUser?->matricula,
            'cargo' => $record->infoUser?->cargo,
        ];
    }

    protected static function getInfoUserViewFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('cpf')
                ->label('CPF')
                ->disabled()
                ->dehydrated(false),
            Forms\Components\TextInput::make('matricula')
                ->label('Matricula')
                ->disabled()
                ->dehydrated(false),
            Forms\Components\TextInput::make('cargo')
                ->label('Cargo')
                ->disabled()
                ->dehydrated(false),
        ];
    }

    protected static function getInfoUserEditFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('cpf')
                ->label('CPF')
                ->maxLength(255),
            Forms\Components\TextInput::make('matricula')
                ->label('Matricula')
                ->maxLength(255),
            Forms\Components\TextInput::make('cargo')
                ->label('Cargo')
                ->maxLength(255),
        ];
    }

    protected static function extractEmailLocalPart(?string $email): ?string
    {
        if (blank($email)) {
            return null;
        }

        return trim(Str::before(trim($email), '@'));
    }

    protected static function buildInstitutionalEmail(?string $value): ?string
    {
        $localPart = static::extractEmailLocalPart($value);

        if (blank($localPart)) {
            return null;
        }

        return $localPart . static::EMAIL_DOMAIN;
    }

    protected static function isValidInstitutionalEmailInput(mixed $value): bool
    {
        $localPart = static::extractEmailLocalPart(is_string($value) ? $value : null);

        if (blank($localPart)) {
            return false;
        }

        if (! preg_match('/^[A-Za-z0-9._%+-]+$/', $localPart)) {
            return false;
        }

        return filter_var(static::buildInstitutionalEmail($localPart), FILTER_VALIDATE_EMAIL) !== false;
    }

}
