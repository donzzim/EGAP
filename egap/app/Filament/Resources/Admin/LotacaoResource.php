<?php

namespace App\Filament\Egap\Resources\Admin;

use App\Filament\Egap\Clusters\AdminEgapCluster;
use App\Filament\Egap\Resources\Admin\LotacaoResource\Pages;
use App\Models\Egap\Admin\Lotacao;
use App\Models\Egap\Cadastro\Setores;
use App\Models\UserEgap;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LotacaoResource extends Resource
{
    protected static ?string $model = Lotacao::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Lotação';
    protected static ?string $cluster = AdminEgapCluster::class;
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
    protected static ?string $modelLabel = 'Lotação';
    protected static ?int $navigationSort = 2;
    protected static ?string $pluralModelLabel = 'Lotações';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Vínculo')
                    ->description('Defina o usuário e a estrutura organizacional da lotação.')
                    ->icon('heroicon-o-link')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 6,
                        ])
                            ->schema([
                                Select::make('id_user')
                                    ->label('Usuário')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->native(false)
                                    ->prefixIcon('heroicon-m-user')
                                    ->placeholder('Selecione o usuário')
                                    ->columnSpanFull()
                                    ->options(fn (): array => UserEgap::query()
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                        ->toArray()),

                                Select::make('UnidadeJudiciaria')
                                    ->label('Unidade judiciária')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->native(false)
                                    ->prefixIcon('heroicon-m-building-office-2')
                                    ->placeholder('Selecione a unidade')
                                    ->columnSpan([
                                        'default' => 1,
                                        'md' => 3,
                                    ])
                                    ->options(fn (): array => Setores::query()
                                        ->whereColumn('id', 'CodigodaUO')
                                        ->orderBy('UnidadeOrganizacional')
                                        ->pluck('UnidadeOrganizacional', 'CodigoPai')
                                        ->toArray())
                                    ->afterStateUpdated(fn (Set $set) => $set('Setor', null)),

                                Select::make('Setor')
                                    ->label('Setor')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->prefixIcon('heroicon-m-squares-2x2')
                                    ->placeholder('Selecione o setor')
                                    ->columnSpan([
                                        'default' => 1,
                                        'md' => 3,
                                    ])
                                    ->options(fn (Get $get): array => Setores::query()
                                        ->when(
                                            $get('UnidadeJudiciaria'),
                                            fn ($query, $codigoPai) => $query->where('CodigoPai', $codigoPai)
                                        )
                                        ->orderBy('Setor')
                                        ->pluck('Setor', 'id')
                                        ->toArray())
                                    ->disabled(fn (Get $get): bool => blank($get('UnidadeJudiciaria'))),
                            ]),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date_time', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Lotacao $record): ?string => $record->user?->username)
                    ->wrap(),
                Tables\Columns\TextColumn::make('unidadeJudiciaria.Setor')
                    ->label('Unidade Judiciaria')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('setorRef.Setor')
                    ->label('Setor')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('setorRef.UnidadeOrganizacional')
                    ->label('Unidade Organizacional')
                    ->searchable()
                    ->toggleable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('usuarioRef.name')
                    ->label('Atualizado por')
                    ->toggleable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('date_time')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('unidade_judiciaria')
                    ->label('Unidade Judiciaria')
                    ->relationship('unidadeJudiciaria', 'Setor')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('setor')
                    ->label('Setor')
                    ->relationship('setorRef', 'Setor')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('unidade_organizacional')
                    ->label('Unidade Organizacional')
                    ->options(static::getUnidadeOrganizacionalFilterOptions())
                    ->searchable()
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if (blank($value)) {
                            return $query;
                        }

                        return $query->whereHas(
                            'setorRef',
                            fn (Builder $setorQuery): Builder => $setorQuery->where('UnidadeOrganizacional', $value)
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLotacaos::route('/'),
            'create' => Pages\CreateLotacao::route('/create'),
            'edit' => Pages\EditLotacao::route('/{record}/edit'),
        ];
    }

    protected static function makeSetorSelect(string $name, string $label): Forms\Components\Select
    {
        return Forms\Components\Select::make($name)
            ->label($label)
            ->searchable()
            ->getSearchResultsUsing(fn (string $search): array => Setores::query()
                ->where('Setor', 'like', "%{$search}%")
                ->orWhere('UnidadeOrganizacional', 'like', "%{$search}%")
                ->orderBy('Setor')
                ->limit(50)
                ->pluck('Setor', 'id')
                ->all())
            ->getOptionLabelUsing(fn ($value): ?string => Setores::query()->whereKey($value)->value('Setor'))
            ->required();
    }

    protected static function getUnidadeOrganizacionalFilterOptions(): array
    {
        return Setores::query()
            ->whereNotNull('UnidadeOrganizacional')
            ->where('UnidadeOrganizacional', '!=', '')
            ->orderBy('UnidadeOrganizacional')
            ->pluck('UnidadeOrganizacional', 'UnidadeOrganizacional')
            ->all();
    }
}
