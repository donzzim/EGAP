<?php

namespace App\Filament\Resources\Processo;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Repeater;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Hidden;
use App\Models\Processo\MatTipoDocumento;
use Filament\Forms\Components\Placeholder;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Processo\ProcessosAdmResource\Pages\ListProcessosAdms;
use App\Filament\Resources\Processo\ProcessosAdmResource\Pages;
use App\Models\Patrimonio\BensImoveis\Processo;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class ProcessosAdmResource extends Resource
{
    protected static ?string $model = Processo::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-folder-open';
    protected static string | \UnitEnum | null $navigationGroup = 'Processos';
    protected static ?string $navigationLabel = 'Processos Administrativos';
    protected static ?string $modelLabel = 'Processo Administrativo';
    protected static ?string $pluralModelLabel = 'Processos Administrativos';
    protected static ?string $slug = 'processos/processos-adm';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                        Tab::make('Processos Administrativos')
                            ->schema([
                                TextInput::make('num_processo')
                                    ->label('Nº Processo TJES')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->mask('9999.99.999.999')
                                    ->placeholder('0000.00.000.000'),

                                TextInput::make('no_processo_sei')
                                    ->label('No Processo SEI')
                                    ->unique(ignoreRecord: true)
                                    ->mask('9999999-99.9999.9.99.9999')
                                    ->placeholder('0000000-00.0000.0.00.0000'),

                                Select::make('id_tipo_processo')
                                    ->label('Tipo de Processo')
                                    ->relationship('tipoProcessoRelacaoRef', 'descricao')
                                    ->searchable()
                                    ->preload(),

                                DatePicker::make('data_abertura')
                                    ->label('Data de Abertura')
                                    ->displayFormat('d/m/Y')
                                    ->native(false),

                                Select::make('unidade_demandante')
                                    ->label('Unidade Requisitante')
                                    ->relationship('unidadeRequisitanteRelacaoRef', 'Setor')
                                    ->searchable()
                                    ->optionsLimit(50),

                                DatePicker::make('data_vigencia')
                                    ->label('Data de encerramento da vigência')
                                    ->displayFormat('d/m/Y')
                                    ->native(false),

                                Textarea::make('descricao')
                                    ->label('Descrição')
                                    ->columnSpan(1)
                                    ->rows(4),

                                Select::make('id_processo_pai')
                                    ->label('Relacionado ao Processo')
                                    ->relationship('processoPaiRelacaoRef', 'num_processo')
                                    ->searchable()
                                    ->optionsLimit(20)
                                    ->columnSpan(1),

                                Select::make('id_fornecedor')
                                    ->label('Fornecedor')
                                    ->relationship('fornecedorRelacaoRef', 'NomeFornecedor')
                                    ->searchable()
                                    ->optionsLimit(50),

                                Select::make('situacao_atual')
                                    ->label('Situação Atual')
                                    ->options([
                                        'Aguardando validação' => 'Aguardando validação',
                                        'Enviado para Empenho' => 'Enviado para Empenho',
                                        'Empenhado' => 'Empenhado',
                                        'Ordem de Entrega Emitida' => 'Ordem de Entrega Emitida',
                                        'Material Recebido' => 'Material Recebido',
                                    ])
                                    ->searchable(),

                                Select::make('projeto_atividade')
                                    ->label('Projeto/Atividade')
                                    ->relationship('projetoAtividadeRelacaoRef', 'descricao')
                                    ->searchable()
                                    ->columnSpan(1),
                            ])->columns(2),

                        Tab::make('Gestores')
                            ->schema([
                                Select::make('gestor_titular')
                                    ->label('Gestor Titular')
                                    ->relationship('gestorTitularRelacaoRef', 'name')
                                    ->searchable()
                                    ->columnSpanFull(),

                                Select::make('gestor_substituto')
                                    ->label('Gestor Substituto')
                                    ->relationship('gestorSubstitutoRelacaoRef', 'name')
                                    ->searchable()
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Nenhum registro encontrado')
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('num_processo')
                    ->label(new HtmlString('Nº Processo<br>TJES'))
                    ->sortable()
                    ->searchable()
                    ->width('80px'),

                TextColumn::make('no_processo_sei')
                    ->label('No Processo SEI')
                    ->sortable()
                    ->default('-')
                    ->alignCenter()
                    ->searchable()
                    ->wrap()
                    ->width('60px'),


                TextColumn::make('tipoProcessoRelacaoRef.descricao')
                    ->label('Tipo de Processo')
                    ->sortable()
                    ->default('-')
                    ->alignCenter()
                    ->width('100px')
                    ->wrap(),


                TextColumn::make('data_abertura')
                    ->label(new HtmlString('Data de<br>Abertura'))
                    ->date('d/m/Y')
                    ->sortable()
                    ->alignCenter()
                    ->width('120px')
                    ->extraCellAttributes(['style' => 'padding-right: 16px;']),

                TextColumn::make('unidadeRequisitanteRelacaoRef.Setor')
                    ->label(new HtmlString('Unidade<br>Requisitante'))
                    ->sortable()
                    ->width('150px')
                    ->wrap(),

                TextColumn::make('data_vigencia')
                    ->label(new HtmlString('Data de encerramento<br>da vigência'))
                    ->date('d/m/Y')
                    ->sortable()
                    ->alignCenter()
                    ->width('90px')
                    ->extraCellAttributes(['style' => 'padding-right:24px;']),

                TextColumn::make('descricao')
                    ->label('Descrição')
                    ->sortable()
                    ->searchable()
                    ->width('300px')
                    ->wrap(),

                TextColumn::make('situacao_atual')
                    ->label('Situação Atual')
                    ->sortable()
                    ->searchable()
                    ->width('180px'),


                TextColumn::make('gestorTitularRelacaoRef.name')
                    ->label('Gestor Titular')
                    ->sortable()
                    ->width('200px')
                    ->wrap(),

            ])
            ->filters([])
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

                EditAction::make('materiais')
                    ->hiddenLabel()
                    ->tooltip('Materiais')
                    ->icon('heroicon-o-cube')
                    ->color('info')
                    ->modalHeading(fn (Processo $record) => "Processos Administrativos - Materiais - " . ($record->no_processo_sei ?? $record->num_processo))
                    ->modalSubmitActionLabel('Salvar alterações')
                    ->modalWidth('7xl')
                    ->schema([
                        Repeater::make('materiais')
                            ->relationship('materiaisRelacaoRef')
                            ->label('')
                            ->collapsible()
                            ->collapsed()
                            ->itemLabel(fn (array $state) => $state['material'] ? DB::connection('egap')->table('mat_descricaodetalhada')->where('id', $state['material'])->value('descricao_detalhada') : 'Novo Material')
                            ->schema([
                                Select::make('processo')
                                    ->label('Processo')
                                    ->options(fn () => DB::connection('egap')
                                        ->table('mat_processos')
                                        ->selectRaw("id, IFNULL(no_processo_sei, num_processo) as proc_label")
                                        ->pluck('proc_label', 'id')
                                    )
                                    ->searchable()
                                    ->default(fn ($livewire) => $livewire->mountedTableActionRecord)
                                    ->columnSpanFull(),

                                Select::make('material')
                                    ->label('Material')
                                    ->options(fn () => DB::connection('egap')
                                        ->table('mat_descricaodetalhada as dd')
                                        ->leftJoin('mat_descricaoresumida as dr', 'dr.id', '=', 'dd.descricao_resumida')
                                        ->leftJoin('mat_produtos as el', 'el.id', '=', 'dr.id_produto')
                                        ->selectRaw("dd.id, CONCAT(IFNULL(el.CodigodaClasse, ''), ' - ', IFNULL(dr.Descricao, ''), ' - ', IFNULL(dd.descricao_detalhada, '')) as full_name")
                                        ->pluck('full_name', 'id')
                                    )
                                    ->searchable()
                                    ->columnSpanFull(),

                                TextInput::make('qtde_min')
                                    ->label('Qtde Min')
                                    ->numeric(),

                                TextInput::make('qtde_max')
                                    ->label('Qtde Máx')
                                    ->numeric(),

                                TextInput::make('preco')
                                    ->label('Preço')
                                    ->numeric()
                                    ->prefix('R$'),

                                TextInput::make('saldo_atual')
                                    ->label('Saldo Atual')
                                    ->numeric(),

                                TextInput::make('lote')
                                    ->label('Lote'),

                                Select::make('atualizado_por')
                                    ->label('Atualizado por')
                                    ->relationship('atualizadoPorRelacaoRef', 'name')
                                    ->searchable()
                                    ->default(fn () => auth()->id())
                                    ->columnSpanFull(),

                                Hidden::make('date_time')
                                    ->default(now()),
                            ])
                            ->columns(5)
                            ->defaultItems(0)
                            ->addActionLabel('Adicionar novo material')
                            ->columnSpanFull(),
                    ]),

                EditAction::make('documentos')
                    ->hiddenLabel()
                    ->tooltip('Documentos')
                    ->icon('heroicon-o-paper-clip')
                    ->color('info')
                    ->modalHeading(fn (Processo $record) => "Anexos do Processo - " . ($record->no_processo_sei ?? $record->num_processo))
                    ->modalSubmitActionLabel('Salvar alterações')
                    ->modalWidth('7xl')
                    ->schema([
                        Repeater::make('documentacoes')
                            ->relationship('documentacoesRelacaoRef')
                            ->label('')
                            ->collapsible()
                            ->collapsed()
                            ->itemLabel(fn (array $state) => ($state['num_documento'] ?? 'Novo Documento') . ($state['data'] ?? false ? ' - ' . date('d/m/Y', strtotime($state['data'])) : ''))
                            ->schema([
                                Select::make('tipo_documento')
                                    ->label('Tipo do Documento')
                                    ->options(fn () => MatTipoDocumento::pluck('descricao', 'id'))
                                    ->searchable(),

                                Select::make('material')
                                    ->label('Material')
                                    ->options(fn () => DB::connection('egap')->table('mat_descricaoresumida')->pluck('Descricao', 'id'))
                                    ->searchable(),

                                DatePicker::make('data')
                                    ->label('Data')
                                    ->displayFormat('d/m/Y')
                                    ->native(false),

                                TextInput::make('num_documento')
                                    ->label('Documento Nº'),

                                Placeholder::make('link_anexo')
                                    ->label('Anexo')
                                    ->content(function ($get) {
                                        $file = $get('anexo_documento');
                                        if (!$file) return 'Nenhum arquivo vinculado';

                                        $fileName = basename($file);

                                        return new HtmlString("<a href='https://sistemas.tjes.jus.br/patrimonio/images/processos/{$fileName}' target='_blank' style='color: #3b82f6; text-decoration: underline; word-break: break-all; max-width: 100%; display: inline-block;'>{$fileName}</a>");
                                    }),

                                Hidden::make('date_time')
                                    ->default(now()),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->addActionLabel('Adicionar nova documentação')
                            ->columnSpanFull(),
                    ]),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Excluir Selecionados'),
                ]),
            ])
            ->searchPlaceholder('Entre com a palavra-chave')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(10)
            ->striped()
            ->deferLoading()
            ->emptyStateHeading('Nenhum Processo Administrativo encontrado');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProcessosAdms::route('/'),
        ];
    }
}
