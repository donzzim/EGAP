<?php

namespace App\Filament\Resources\Agendamento;

use App\Filament\Resources\Agendamento\TransporteResource\Pages;
use App\Models\Agendamento\Solicitacao;
use App\Models\Cadastro\Setores;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TransporteResource extends Resource
{
    protected static ?string $model = Solicitacao::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Agendamento';
    protected static ?string $modelLabel = 'Transporte de Carga';
    protected static ?string $pluralModelLabel = 'Transporte de Carga';
    protected static ?string $navigationLabel = 'Transporte de Carga';
    protected static ?int $navigationSort = 5;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('tipo', 2);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('Transporte')
                ->tabs([
                    Forms\Components\Tabs\Tab::make('Transporte de Carga')
                        ->schema([
                            Forms\Components\Section::make()
                                ->schema([
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\Select::make('tipo')
                                                ->label('Tipo de Agendamento')
                                                ->options([
                                                    1 => 'Agendamento de Veículos',
                                                    2 => 'Transporte de Carga',
                                                ])
                                                ->default(2)
                                                ->disabled()
                                                ->dehydrated(true)
                                                ->native(false)
                                                ->required(),

                                            Forms\Components\Select::make('id_situacao')
                                                ->label('Situação')
                                                ->relationship('idSituacaoRef', 'Descricao')
                                                ->searchable()
                                                ->preload()
                                                ->placeholder('Por favor selecione')
                                                ->native(false)
                                                ->required(),

                                            Forms\Components\Select::make('id_solicitante')
                                                ->label('Solicitante')
                                                ->relationship('idSolicitanteRef', 'name')
                                                ->searchable()
                                                ->preload()
                                                ->placeholder('Por favor selecione')
                                                ->native(false)
                                                ->required(),

                                            Forms\Components\Select::make('regiao')
                                                ->label('Região')
                                                ->relationship('regiaoRef', 'regiao')
                                                ->searchable()
                                                ->preload()
                                                ->placeholder('Por favor selecione')
                                                ->native(false)
                                                ->required(),

                                            Forms\Components\Select::make('unidade_solicitante')
                                                ->label('Unidade Solicitante')
                                                ->required()
                                                ->searchable()
                                                ->preload()
                                                ->live()
                                                ->placeholder('Por favor selecione')
                                                ->native(false)
                                                ->options(fn () => Setores::query()
                                                    ->whereColumn('id', 'CodigodaUO')
                                                    ->orderBy('UnidadeOrganizacional')
                                                    ->pluck('UnidadeOrganizacional', 'CodigoPai')
                                                    ->toArray()
                                                )
                                                ->afterStateUpdated(fn (Set $set) => $set('setor_solicitante', null)),

                                            Forms\Components\Select::make('setor_solicitante')
                                                ->label('Setor Solicitante')
                                                ->required()
                                                ->searchable()
                                                ->preload()
                                                ->placeholder('Por favor selecione')
                                                ->native(false)
                                                ->options(fn (Get $get) => Setores::query()
                                                    ->when(
                                                        $get('unidade_solicitante'),
                                                        fn ($query, $codigoPai) => $query->where('CodigoPai', $codigoPai)
                                                    )
                                                    ->orderBy('Setor')
                                                    ->pluck('Setor', 'id')
                                                    ->toArray()
                                                )
                                                ->disabled(fn (Get $get) => blank($get('unidade_solicitante'))),

                                            Forms\Components\TextInput::make('local_saida')
                                                ->label('Local Saída')
                                                ->maxLength(255)
                                                ->required(),

                                            Forms\Components\TextInput::make('local_destino')
                                                ->label('Local Destino')
                                                ->maxLength(255)
                                                ->required(),
                                        ]),

                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\Textarea::make('justificativa')
                                                ->label('Detalhamento')
                                                ->rows(6)
                                                ->autosize()
                                                ->required(),

                                            Forms\Components\Textarea::make('motivo_cancelamento')
                                                ->label('Motivo Cancelamento')
                                                ->rows(6)
                                                ->autosize(),
                                        ]),

                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\DateTimePicker::make('data_alteracao')
                                                ->label('Atualizado em')
                                                ->seconds(false)
                                                ->native(false),

                                            Forms\Components\Select::make('id_user')
                                                ->label('Atualizado por')
                                                ->relationship('idUserRef', 'name')
                                                ->searchable()
                                                ->preload()
                                                ->placeholder('Por favor selecione')
                                                ->native(false),
                                        ]),
                                ]),
                        ]),

                    Forms\Components\Tabs\Tab::make('Agendar')
                        ->schema([
                            Forms\Components\Section::make()
                                ->schema([
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\DatePicker::make('data_inicio')
                                                ->label('Data início')
                                                ->displayFormat('d/m/Y')
                                                ->native(false)
                                                ->required(),

                                            Forms\Components\TimePicker::make('hora_inicio')
                                                ->label('Hora Início')
                                                ->seconds(false)
                                                ->required(),

                                            Forms\Components\DatePicker::make('data_termino')
                                                ->label('Data Término')
                                                ->displayFormat('d/m/Y')
                                                ->native(false)
                                                ->required(),

                                            Forms\Components\TimePicker::make('hora_termino')
                                                ->label('Hora Término')
                                                ->seconds(false)
                                                ->required(),
                                        ]),

                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\Textarea::make('finalizar')
                                                ->label('Observação')
                                                ->rows(6)
                                                ->autosize(),

                                            Forms\Components\Textarea::make('motivo_edicao')
                                                ->label('Motivo da Edição')
                                                ->rows(6)
                                                ->autosize(),
                                        ]),

                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\TextInput::make('agendamento_pai')
                                                ->label('Agendamento Pai'),

                                            Forms\Components\DateTimePicker::make('date_time')
                                                ->label('Data Alteração')
                                                ->seconds(false)
                                                ->native(false),
                                        ]),

                                    Forms\Components\TextInput::make('anexo')
                                        ->label('Anexo')
                                        ->maxLength(255),
                                ]),
                        ]),

                    Forms\Components\Tabs\Tab::make('Requisição/Termos')
                        ->schema([
                            Forms\Components\Section::make()
                                ->schema([
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\DateTimePicker::make('data_alteracao')
                                                ->label('Atualizado em')
                                                ->seconds(false)
                                                ->native(false),

                                            Forms\Components\Select::make('id_user')
                                                ->label('Atualizado por')
                                                ->relationship('idUserRef', 'name')
                                                ->searchable()
                                                ->preload()
                                                ->placeholder('Por favor selecione')
                                                ->native(false),

                                            Forms\Components\TextInput::make('requisicao')
                                                ->label('Requisição')
                                                ->placeholder('Por favor selecione'),

                                            Forms\Components\TextInput::make('termo')
                                                ->label('Termo')
                                                ->placeholder('Por favor selecione'),
                                        ]),
                                ]),
                        ]),

                    Forms\Components\Tabs\Tab::make('age_recursos')
                        ->schema([
                            Forms\Components\Section::make()
                                ->schema([
                                    Forms\Components\Placeholder::make('age_recursos_info')
                                        ->label('')
                                        ->content('Campos relacionados a age_recursos devem ser vinculados ao model/tabela correspondente.'),

                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\TextInput::make('age_recursos_id_user')
                                                ->label('id user')
                                                ->dehydrated(false),

                                            Forms\Components\TextInput::make('age_recursos_condutor')
                                                ->label('condutor')
                                                ->dehydrated(false),

                                            Forms\Components\TextInput::make('age_recursos_veiculo')
                                                ->label('veiculo')
                                                ->dehydrated(false),

                                            Forms\Components\TextInput::make('age_recursos_id_solicitacao')
                                                ->label('id solicitacao')
                                                ->dehydrated(false),

                                            Forms\Components\Textarea::make('age_recursos_observacao')
                                                ->label('observacao')
                                                ->rows(6)
                                                ->dehydrated(false)
                                                ->columnSpanFull(),
                                        ]),
                                ]),
                        ]),
                ])
                ->persistTabInQueryString()
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(25)
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Nº')
                    ->sortable()
                    ->alignCenter()
                    ->weight('bold')
                    ->copyable(),

                Tables\Columns\TextColumn::make('idSituacaoRef.Descricao')
                    ->label('Situação')
                    ->badge()
                    ->sortable()
                    ->searchable()
                    ->color(fn (?string $state) => match (mb_strtolower($state ?? '')) {
                        'pendente' => 'warning',
                        'aprovado', 'deferido', 'ativo' => 'success',
                        'cancelado', 'indeferido' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('idSolicitanteRef.name')
                    ->label('Solicitante')
                    ->sortable()
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('local_saida')
                    ->label('Local de saída')
                    ->sortable()
                    ->alignCenter()
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn ($state) => $state)
                    ->wrap()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('local_destino')
                    ->label('Local de destino')
                    ->sortable()
                    ->default(' - ')
                    ->alignCenter()
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn ($state) => $state)
                    ->wrap()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('justificativa_lista')
                    ->label('Detalhamento')
                    ->formatStateUsing(fn ($state) => filled($state) ? nl2br(e($state)) : '-')
                    ->html()
                    ->wrap()
                    ->limit(120)
                    ->tooltip(fn ($state) => $state)
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('data_inicio')
                    ->label('Data de início')
                    ->sortable()
                    ->searchable()
                    ->date('d/m/Y')
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('requisicao')
                    ->label('Requisição')
                    ->alignCenter()
                    ->default(' / ')
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('termo')
                    ->label('Termo')
                    ->alignCenter()
                    ->default(' / ')
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                Tables\Filters\Filter::make('periodo')
                    ->label('Período')
                    ->form([
                        Forms\Components\DatePicker::make('data_inicio')
                            ->label('De')
                            ->native(false),

                        Forms\Components\DatePicker::make('data_termino')
                            ->label('Até')
                            ->native(false),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['data_inicio'] ?? null,
                                fn ($q, $date) => $q->whereDate('data_inicio', '>=', $date)
                            )
                            ->when(
                                $data['data_termino'] ?? null,
                                fn ($q, $date) => $q->whereDate('data_termino', '<=', $date)
                            );
                    }),

                Tables\Filters\SelectFilter::make('id_situacao')
                    ->label('Situação')
                    ->relationship('idSituacaoRef', 'Descricao')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('regiao')
                    ->label('Região')
                    ->relationship('regiaoRef', 'sigla')
                    ->searchable()
                    ->preload(),
            ])
            ->filtersFormColumns(3)
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
            ->defaultSort('id', 'desc')
            ->emptyStateHeading('Nenhuma solicitação encontrada')
            ->emptyStateDescription('Não há registros cadastrados para os filtros atuais.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransportes::route('/'),
            'create' => Pages\CreateTransporte::route('/create'),
            'edit' => Pages\EditTransporte::route('/{record}/edit'),
        ];
    }
}
