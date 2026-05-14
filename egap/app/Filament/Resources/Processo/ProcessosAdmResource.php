<?php

namespace App\Filament\Egap\Resources\Processo;

use App\Filament\Egap\Resources\Processo\ProcessosAdmResource\Pages;
use App\Models\Egap\Patrimonio\BensImoveis\Processo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class ProcessosAdmResource extends Resource
{
    protected static ?string $model = Processo::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-folder-open'; 
    protected static ?string $navigationGroup = 'Processos'; 
    protected static ?string $navigationLabel = 'Processos Administrativos';
    protected static ?string $modelLabel = 'Processo Administrativo';
    protected static ?string $pluralModelLabel = 'Processos Administrativos';
    protected static ?string $slug = 'processos/processos-adm';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Processos Administrativos')
                            ->schema([
                                Forms\Components\TextInput::make('num_processo')
                                    ->label('Nº Processo TJES')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->mask('9999.99.999.999')
                                    ->placeholder('0000.00.000.000'),
                                    
                                Forms\Components\TextInput::make('no_processo_sei')
                                    ->label('No Processo SEI')
                                    ->unique(ignoreRecord: true)
                                    ->mask('9999999-99.9999.9.99.9999')
                                    ->placeholder('0000000-00.0000.0.00.0000'),
                                    
                                Forms\Components\Select::make('id_tipo_processo')
                                    ->label('Tipo de Processo')
                                    ->relationship('tipoProcessoRelacaoRef', 'descricao')
                                    ->searchable()
                                    ->preload(),
                                    
                                Forms\Components\DatePicker::make('data_abertura')
                                    ->label('Data de Abertura')
                                    ->displayFormat('d/m/Y')
                                    ->native(false),
                                    
                                Forms\Components\Select::make('unidade_demandante')
                                    ->label('Unidade Requisitante')
                                    ->relationship('unidadeRequisitanteRelacaoRef', 'Setor')
                                    ->searchable()
                                    ->optionsLimit(50),
                                    
                                Forms\Components\DatePicker::make('data_vigencia')
                                    ->label('Data de encerramento da vigência')
                                    ->displayFormat('d/m/Y')
                                    ->native(false),
                                    
                                Forms\Components\Textarea::make('descricao')
                                    ->label('Descrição')
                                    ->columnSpan(1)
                                    ->rows(4),
                                    
                                Forms\Components\Select::make('id_processo_pai')
                                    ->label('Relacionado ao Processo')
                                    ->relationship('processoPaiRelacaoRef', 'num_processo')
                                    ->searchable()
                                    ->optionsLimit(20)
                                    ->columnSpan(1),
                                    
                                Forms\Components\Select::make('id_fornecedor')
                                    ->label('Fornecedor')
                                    ->relationship('fornecedorRelacaoRef', 'NomeFornecedor')
                                    ->searchable()
                                    ->optionsLimit(50),
                                    
                                Forms\Components\Select::make('situacao_atual')
                                    ->label('Situação Atual')
                                    ->options([
                                        'Aguardando validação' => 'Aguardando validação',
                                        'Enviado para Empenho' => 'Enviado para Empenho',
                                        'Empenhado' => 'Empenhado',
                                        'Ordem de Entrega Emitida' => 'Ordem de Entrega Emitida',
                                        'Material Recebido' => 'Material Recebido',
                                    ])
                                    ->searchable(),
                                    
                                Forms\Components\Select::make('projeto_atividade')
                                    ->label('Projeto/Atividade')
                                    ->relationship('projetoAtividadeRelacaoRef', 'descricao')
                                    ->searchable()
                                    ->columnSpan(1),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('Gestores')
                            ->schema([
                                Forms\Components\Select::make('gestor_titular')
                                    ->label('Gestor Titular')
                                    ->relationship('gestorTitularRelacaoRef', 'name')
                                    ->searchable()
                                    ->columnSpanFull(),
                                    
                                Forms\Components\Select::make('gestor_substituto')
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
            ->columns([
                Tables\Columns\TextColumn::make('num_processo')
                    ->label(new HtmlString('Nº Processo<br>TJES'))
                    ->sortable()
                    ->searchable()
                    ->width('80px'),
                    
                Tables\Columns\TextColumn::make('no_processo_sei')
                    ->label('No Processo SEI')
                    ->sortable()
                    ->searchable()
                    ->wrap()
                    ->width('60px'),
                    
                    
                Tables\Columns\TextColumn::make('tipoProcessoRelacaoRef.descricao')
                    ->label('Tipo de Processo')
                    ->sortable()
                    ->width('100px')
                    ->wrap(),
                    
                    
                Tables\Columns\TextColumn::make('data_abertura')
                    ->label(new HtmlString('Data de<br>Abertura'))
                    ->date('d/m/Y')
                    ->sortable()
                    ->alignCenter()
                    ->width('120px')
                    ->extraCellAttributes(['style' => 'padding-right: 16px;']),
                    
                Tables\Columns\TextColumn::make('unidadeRequisitanteRelacaoRef.Setor')
                    ->label(new HtmlString('Unidade<br>Requisitante'))
                    ->sortable()
                    ->width('150px')
                    ->wrap(),
                    
                Tables\Columns\TextColumn::make('data_vigencia')
                    ->label(new HtmlString('Data de encerramento<br>da vigência'))
                    ->date('d/m/Y')
                    ->sortable()
                    ->alignCenter()
                    ->width('90px')
                    ->extraCellAttributes(['style' => 'padding-right:24px;']),
                    
                Tables\Columns\TextColumn::make('descricao')
                    ->label('Descrição')
                    ->sortable()
                    ->searchable()
                    ->width('300px')
                    ->wrap(),
                    
                Tables\Columns\TextColumn::make('situacao_atual')
                    ->label('Situação Atual')
                    ->sortable()
                    ->searchable()
                    ->width('180px'),
                    
                    
                Tables\Columns\TextColumn::make('gestorTitularRelacaoRef.name')
                    ->label('Gestor Titular')
                    ->sortable()
                    ->width('200px')
                    ->wrap(),
                    
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ActionGroup::make([

                    Tables\Actions\EditAction::make()
                        ->label('Editar')
                        ->color('warning')
                        ->icon('heroicon-o-pencil-square'),      
                                      
                    Tables\Actions\ViewAction::make()
                        ->label('Visualizar')
                        ->icon('heroicon-o-eye'),
                        
                    Tables\Actions\DeleteAction::make()
                        ->label('Excluir')
                        ->color('danger')
                        ->icon('heroicon-o-trash'),

                    Tables\Actions\EditAction::make('materiais')
                        ->label('Materiais')
                        ->icon('heroicon-o-cube')
                        ->color('info')
                        ->modalHeading(fn (Processo $record) => "Processos Administrativos - Materiais - " . ($record->no_processo_sei ?? $record->num_processo))
                        ->modalSubmitActionLabel('Salvar alterações')
                        ->modalWidth('7xl')
                        ->form([
                            Forms\Components\Repeater::make('materiais')
                                ->relationship('materiaisRelacaoRef')
                                ->label('')
                                ->collapsible()
                                ->collapsed()
                                ->itemLabel(fn (array $state) => $state['material'] ? \Illuminate\Support\Facades\DB::connection('egap')->table('mat_descricaodetalhada')->where('id', $state['material'])->value('descricao_detalhada') : 'Novo Material')
                                ->schema([
                                    Forms\Components\Select::make('processo')
                                        ->label('Processo')
                                        ->options(fn () => \Illuminate\Support\Facades\DB::connection('egap')
                                            ->table('mat_processos')
                                            ->selectRaw("id, IFNULL(no_processo_sei, num_processo) as proc_label")
                                            ->pluck('proc_label', 'id')
                                        )
                                        ->searchable()
                                        ->default(fn ($livewire) => $livewire->mountedTableActionRecord)
                                        ->columnSpanFull(),

                                    Forms\Components\Select::make('material')
                                        ->label('Material')
                                        ->options(fn () => \Illuminate\Support\Facades\DB::connection('egap')
                                            ->table('mat_descricaodetalhada as dd')
                                            ->leftJoin('mat_descricaoresumida as dr', 'dr.id', '=', 'dd.descricao_resumida')
                                            ->leftJoin('mat_produtos as el', 'el.id', '=', 'dr.id_produto')
                                            ->selectRaw("dd.id, CONCAT(IFNULL(el.CodigodaClasse, ''), ' - ', IFNULL(dr.Descricao, ''), ' - ', IFNULL(dd.descricao_detalhada, '')) as full_name")
                                            ->pluck('full_name', 'id')
                                        )
                                        ->searchable()
                                        ->columnSpanFull(),

                                    Forms\Components\TextInput::make('qtde_min')
                                        ->label('Qtde Min')
                                        ->numeric(),

                                    Forms\Components\TextInput::make('qtde_max')
                                        ->label('Qtde Máx')
                                        ->numeric(),

                                    Forms\Components\TextInput::make('preco')
                                        ->label('Preço')
                                        ->numeric()
                                        ->prefix('R$'),

                                    Forms\Components\TextInput::make('saldo_atual')
                                        ->label('Saldo Atual')
                                        ->numeric(),

                                    Forms\Components\TextInput::make('lote')
                                        ->label('Lote'),

                                    Forms\Components\Select::make('atualizado_por')
                                        ->label('Atualizado por')
                                        ->relationship('atualizadoPorRelacaoRef', 'name')
                                        ->searchable()
                                        ->default(fn () => auth()->id())
                                        ->columnSpanFull(),

                                    Forms\Components\Hidden::make('date_time')
                                        ->default(now()),
                                ])
                                ->columns(5)
                                ->defaultItems(0)
                                ->addActionLabel('Adicionar novo material')
                                ->columnSpanFull(),
                        ]),

                    Tables\Actions\EditAction::make('documentos')
                        ->label('Documentos')
                        ->icon('heroicon-o-paper-clip')
                        ->color('info')
                        ->modalHeading(fn (Processo $record) => "Anexos do Processo - " . ($record->no_processo_sei ?? $record->num_processo))
                        ->modalSubmitActionLabel('Salvar alterações')
                        ->modalWidth('7xl')
                        ->form([
                            Forms\Components\Repeater::make('documentacoes')
                                ->relationship('documentacoesRelacaoRef')
                                ->label('')
                                ->collapsible()
                                ->collapsed()
                                ->itemLabel(fn (array $state) => ($state['num_documento'] ?? 'Novo Documento') . ($state['data'] ?? false ? ' - ' . date('d/m/Y', strtotime($state['data'])) : ''))
                                ->schema([
                                    Forms\Components\Select::make('tipo_documento')
                                        ->label('Tipo do Documento')
                                        ->options(fn () => \App\Models\Egap\Processo\MatTipoDocumento::pluck('descricao', 'id'))
                                        ->searchable(),
                                        
                                    Forms\Components\Select::make('material')
                                        ->label('Material')
                                        ->options(fn () => \Illuminate\Support\Facades\DB::connection('egap')->table('mat_descricaoresumida')->pluck('Descricao', 'id'))
                                        ->searchable(),
                                        
                                    Forms\Components\DatePicker::make('data')
                                        ->label('Data')
                                        ->displayFormat('d/m/Y')
                                        ->native(false),
                                        
                                    Forms\Components\TextInput::make('num_documento')
                                        ->label('Documento Nº'),

                                    Forms\Components\Placeholder::make('link_anexo')
                                        ->label('Anexo')
                                        ->content(function ($get) {
                                            $file = $get('anexo_documento');
                                            if (!$file) return 'Nenhum arquivo vinculado';
                                            
                                            $fileName = basename($file);
                                            
                                            return new HtmlString("<a href='https://sistemas.tjes.jus.br/patrimonio/images/processos/{$fileName}' target='_blank' style='color: #3b82f6; text-decoration: underline; word-break: break-all; max-width: 100%; display: inline-block;'>{$fileName}</a>");
                                        }),
                                        
                                    Forms\Components\Hidden::make('date_time')
                                        ->default(now()),
                                ])
                                ->columns(3)
                                ->defaultItems(0)
                                ->addActionLabel('Adicionar nova documentação')
                                ->columnSpanFull(),
                        ]),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->tooltip('Opções')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Excluir Selecionados'),
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
            'index' => Pages\ListProcessosAdms::route('/'),
        ];
    }
}