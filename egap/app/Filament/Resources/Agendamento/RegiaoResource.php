<?php

namespace App\Filament\Resources\Agendamento;

use App\Filament\Resources\Agendamento\RegiaoResource\Pages;
use App\Models\Agendamento\Regiao;
use App\Models\Cadastro\Setores;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RegiaoResource extends Resource
{
    protected static ?string $model = Regiao::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';
    protected static ?string $navigationGroup = 'Agendamento';
    protected static ?string $modelLabel = 'Região';
    protected static ?string $pluralModelLabel = 'Regiões';
    protected static ?string $navigationLabel = 'Regiões';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Dados da região')
                ->description('Informe a descrição da região, sua sigla e a unidade vinculada.')
                ->icon('heroicon-o-map')
                ->schema([
                    Forms\Components\Grid::make(12)
                        ->schema([
                            Forms\Components\Select::make('regiao')
                                ->label('Região')
                                ->options([
                                    '1' => 'Região 1',
                                    '2' => 'Região 2',
                                    '3' => 'Região 3',
                                    '4' => 'Região 4',
                                    '5' => 'Região 5',
                                    '6' => 'Região 6',
                                    '7' => 'Região 7',
                                    '8' => 'Região 8',
                                    '9' => 'Região 9',
                                    '10' => 'Região 10',
                                ])
                                ->required()
                                ->placeholder('Selecione a região atendida')
                                ->columnSpan(12),

                            Forms\Components\TextInput::make('sigla')
                                ->label('Sigla')
                                ->required()
                                ->maxLength(255)
                                ->placeholder('Ex: GV, SUL, NORTE')
                                ->columnSpan(4),

                            Forms\Components\Select::make('unidade')
                                ->label('Unidade Judiciária')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->live()
                                ->options(fn () => Setores::query()
                                    ->whereColumn('id', 'CodigodaUO')
                                    ->orderBy('UnidadeOrganizacional')
                                    ->pluck('UnidadeOrganizacional', 'CodigoPai')
                                    ->toArray()
                                )
                                ->placeholder('Selecione a unidade')
                                ->columnSpan(8),
                        ]),
                ])
                ->columns(1)
                ->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Nenhum registro encontrado')
            ->defaultPaginationPageOption(25)
            ->columns([
                Tables\Columns\TextColumn::make('regiao')
                    ->label('Região')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->weight('medium')
                    ->description(fn ($record) => $record->sigla ? 'Sigla: ' . $record->sigla : null),

                Tables\Columns\TextColumn::make('unidadeRef.UnidadeOrganizacional')
                    ->label('Unidade')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('sigla')
                    ->label('Sigla')
                    ->searchable()
                    ->alignCenter()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('atualizadoPorRef.name')
                    ->label('Atualizado por')
                    ->alignCenter()
                    ->sortable()
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('date_time')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y')
                    ->alignCenter()
                    ->sortable()
                    ->sinceTooltip()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('unidade')
                    ->label('Unidade')
                    ->relationship('unidadeRef', 'Setor'),
            ])
            ->actions([
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
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('regiao', 'asc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRegiaos::route('/'),
            'create' => Pages\CreateRegiao::route('/create'),
            'edit' => Pages\EditRegiao::route('/{record}/edit'),
        ];
    }
}
