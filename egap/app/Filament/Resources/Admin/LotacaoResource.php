<?php

namespace App\Filament\Resources\Admin;

use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Admin\LotacaoResource\Pages\ListLotacaos;
use App\Filament\Resources\Admin\LotacaoResource\Pages\CreateLotacao;
use App\Filament\Resources\Admin\LotacaoResource\Pages\EditLotacao;
use App\Filament\Clusters\AdminEgapCluster;
use App\Filament\Resources\Admin\LotacaoResource\Pages;
use App\Filament\Support\TableColumns;
use App\Models\Admin\Lotacao;
use App\Models\Cadastro\Setores;
use App\Models\UserEgap;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LotacaoResource extends Resource
{
    protected static ?string $model = Lotacao::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Lotação';
    protected static ?string $cluster = AdminEgapCluster::class;
    protected static ?\Filament\Pages\Enums\SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
    protected static ?string $modelLabel = 'Lotação';
    protected static ?int $navigationSort = 2;
    protected static ?string $pluralModelLabel = 'Lotações';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Vínculo')
                    ->description('Defina o usuário e a estrutura organizacional da lotação.')
                    ->icon('heroicon-o-link')
                    ->schema([
                        Grid::make()
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
                                    ->label('Unidade Judiciária')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->native(false)
                                    ->prefixIcon('heroicon-m-building-office-2')
                                    ->placeholder('Selecione a unidade')
                                    ->columnSpan(1)
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
                                    ->columnSpan(1)
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
            ->emptyStateHeading('Nenhum registro encontrado')
            ->defaultPaginationPageOption(25)
            ->defaultSort('date_time', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Lotacao $record): ?string => $record->user?->username)
                    ->wrap(),
                TextColumn::make('unidadeJudiciaria.Setor')
                    ->label('Unidade Judiciaria')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('setorRef.Setor')
                    ->label('Setor')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('setorRef.UnidadeOrganizacional')
                    ->label('Unidade Organizacional')
                    ->searchable()
                    ->wrap(),
                TableColumns::updatedBy('usuarioRef.name'),
            ])
            ->filters([
                SelectFilter::make('unidade_judiciaria')
                    ->label('Unidade Judiciária')
                    ->searchable()
                    ->preload()
                    ->options(fn () => Setores::query()
                        ->whereColumn('id', 'CodigodaUO')
                        ->orderBy('UnidadeOrganizacional')
                        ->pluck('UnidadeOrganizacional', 'CodigoPai')
                        ->toArray()
                    ),
                SelectFilter::make('setor')
                    ->label('Setor')
                    ->searchable()
                    ->preload()
                    ->options(fn () => Setores::query()
                        ->orderBy('Setor')
                        ->pluck('Setor', 'id')
                        ->toArray()
                    ),
            ])
            ->recordActions([
                EditAction::make()
                    ->tooltip('Editar')
                    ->hiddenLabel(),
                ViewAction::make()
                    ->tooltip('Visualizar')
                    ->hiddenLabel(),
                DeleteAction::make()
                    ->tooltip('Excluir')
                    ->modalHeading('Excluir registro')
                    ->hiddenLabel(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLotacaos::route('/'),
            'create' => CreateLotacao::route('/create'),
            'edit' => EditLotacao::route('/{record}/edit'),
        ];
    }

    protected static function makeSetorSelect(string $name, string $label): Select
    {
        return Select::make($name)
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
