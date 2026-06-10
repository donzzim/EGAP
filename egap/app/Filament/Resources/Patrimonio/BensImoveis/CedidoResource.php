<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensImoveis\CedidoResource\Pages;
use App\Filament\Support\TableDefaults;
use App\Filament\Support\TableColumns;
use App\Models\Patrimonio\BensImoveis\Cedido;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;

class CedidoResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = Cedido::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Imóveis Ocupados por Terceiros';
    protected static ?string $modelLabel = 'Imóvel Ocupado por Terceiro';
    protected static ?string $pluralModelLabel = 'Imóveis Ocupados por Terceiros';
    protected static ?string $navigationGroup = 'Bens Imóveis';
    protected static ?int $navigationSort = 5;
    protected static ?string $slug = 'bens-imoveis/ocupados-por-terceiros';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Imóvel')
                            ->icon('heroicon-o-building-office')
                            ->schema([
                                Forms\Components\Section::make('Identificação')
                                    ->icon('heroicon-o-building-office')
                                    ->schema([
                                        Forms\Components\Select::make('id_imovel')
                                            ->label('Imóvel')
                                            ->relationship('imovelRelacaoref', 'descricao')
                                            ->searchable()
                                            ->preload()
                                            ->placeholder('Selecione o imóvel')
                                            ->columnSpanFull(),

                                        Forms\Components\TextInput::make('num_processo')
                                            ->label('Nº do Processo')
                                            ->placeholder('Informe o número do processo'),

                                        Forms\Components\FileUpload::make('termo_digital')
                                            ->label('Termo Digital')
                                            ->helperText('Anexe o termo ou documento relacionado à ocupação.'),
                                    ])
                                    ->columns(2),

                                Forms\Components\Section::make('Dados da Ocupação')
                                    ->icon('heroicon-o-document-text')
                                    ->schema([
                                        Forms\Components\Textarea::make('resumo')
                                            ->label('Partes/Terceiros')
                                            ->placeholder('Informe as partes envolvidas ou terceiros ocupantes')
                                            ->rows(4),

                                        Forms\Components\Textarea::make('proprietario_responsavel')
                                            ->label('Proprietário/Responsável')
                                            ->placeholder('Informe o proprietário ou responsável')
                                            ->rows(4),

                                        Forms\Components\Textarea::make('condicao_uso')
                                            ->label('Condição de Uso')
                                            ->placeholder('Descreva a condição de uso do espaço')
                                            ->rows(4),

                                        Forms\Components\Textarea::make('objeto')
                                            ->label('Objeto')
                                            ->placeholder('Descreva o objeto da cessão ou ocupação')
                                            ->rows(4),

                                        Forms\Components\Textarea::make('fiscais')
                                            ->label('Fiscais')
                                            ->placeholder('Informe os fiscais responsáveis')
                                            ->columnSpanFull()
                                            ->rows(3),
                                    ])
                                    ->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Situação')
                            ->icon('heroicon-o-check-circle')
                            ->schema([
                                Forms\Components\Section::make('Status e Controle')
                                    ->icon('heroicon-o-check-circle')
                                    ->schema([
                                        Forms\Components\Select::make('situacao')
                                            ->label('Situação')
                                            ->options([
                                                'Vigente' => 'Vigente',
                                                'Encerrado' => 'Encerrado',
                                            ])
                                            ->placeholder('Selecione a situação'),

                                        Forms\Components\Select::make('atualizado_por')
                                            ->label('Atualizado por')
                                            ->relationship('atualizadoPorRelacaoref', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->placeholder('Selecione o usuário'),

                                        Forms\Components\Textarea::make('observacao')
                                            ->label('Observação')
                                            ->placeholder('Registre informações complementares sobre a situação')
                                            ->columnSpanFull()
                                            ->rows(4),
                                    ])
                                    ->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Publicações')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->schema([
                                Forms\Components\Section::make('Assinatura e Publicação')
                                    ->icon('heroicon-o-clipboard-document-list')
                                    ->schema([
                                        Forms\Components\DatePicker::make('data_assinatura')
                                            ->label('Data de Assinatura')
                                            ->displayFormat('d/m/Y')
                                            ->native(false),

                                        Forms\Components\TextInput::make('ato_diario')
                                            ->label('Ato Diário')
                                            ->placeholder('Informe o ato diário'),

                                        Forms\Components\DatePicker::make('data_publicacao')
                                            ->label('Data de Publicação')
                                            ->displayFormat('d/m/Y')
                                            ->native(false),
                                    ])
                                    ->columns(3),
                            ]),

                        Forms\Components\Tabs\Tab::make('Vigência')
                            ->icon('heroicon-o-calendar-days')
                            ->schema([
                                Forms\Components\Section::make('Prazos')
                                    ->icon('heroicon-o-calendar-days')
                                    ->schema([
                                        Forms\Components\DatePicker::make('vencimento')
                                            ->label('Vencimento')
                                            ->displayFormat('d/m/Y')
                                            ->native(false),

                                        Forms\Components\TextInput::make('vigencia')
                                            ->label('Vigência')
                                            ->placeholder('Ex: 12 meses'),

                                        Forms\Components\DatePicker::make('aditivo_vigencia')
                                            ->label('Aditivo de Vigência')
                                            ->displayFormat('d/m/Y')
                                            ->native(false),
                                    ])
                                    ->columns(3),
                            ]),

                        Forms\Components\Tabs\Tab::make('Ocupação do Espaço')
                            ->icon('heroicon-o-currency-dollar')
                            ->schema([
                                Forms\Components\Section::make('Condições Financeiras e Despesas')
                                    ->icon('heroicon-o-currency-dollar')
                                    ->schema([
                                        Forms\Components\Radio::make('retribuicao')
                                            ->label('Retribuição')
                                            ->options([
                                                'Oneroso' => 'Oneroso',
                                                'Gratuito' => 'Gratuito',
                                            ])
                                            ->columnSpan(1),

                                        Forms\Components\CheckboxList::make('despesas')
                                            ->label('Despesas')
                                            ->options([
                                                'Energia' => 'Energia',
                                                'Água' => 'Água',
                                                'Tributos' => 'Tributos',
                                                'taxa de coleta de lixo' => 'taxa de coleta de lixo',
                                            ])
                                            ->columns(2)
                                            ->columnSpan(1),
                                    ])->columns(2)
                            ]),

                        Forms\Components\Tabs\Tab::make('Gestores/Fiscais')
                            ->icon('heroicon-o-user-group')
                            ->schema([
                                Forms\Components\Section::make('Equipe Responsável')
                                    ->icon('heroicon-o-user-group')
                                    ->schema([
                                        Forms\Components\Repeater::make('gestores')
                                            ->relationship('gestores')
                                            ->schema([
                                                Forms\Components\Select::make('gestor_fiscal')
                                                    ->label('Perfil')
                                                    ->options([
                                                        'Gestor' => 'Gestor',
                                                        'Fiscal' => 'Fiscal',
                                                    ])
                                                    ->placeholder('Selecione'),

                                                Forms\Components\Select::make('nome')
                                                    ->label('Nome')
                                                    ->relationship('nomeRelacaoref', 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->placeholder('Selecione o responsável'),

                                                Forms\Components\TextInput::make('ato_diario')
                                                    ->label('Ato Diário')
                                                    ->placeholder('Informe o ato diário'),

                                                Forms\Components\DatePicker::make('data_publicacao')
                                                    ->label('Data de Publicação')
                                                    ->displayFormat('d/m/Y')
                                                    ->native(false),

                                                Forms\Components\DatePicker::make('data_encerramento')
                                                    ->label('Data de Encerramento')
                                                    ->displayFormat('d/m/Y')
                                                    ->native(false),

                                                Forms\Components\Select::make('atualizado_por')
                                                    ->label('Atualizado por')
                                                    ->relationship('atualizadoPorRelacaoref', 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->hidden(),
                                            ])
                                            ->columns(3)
                                            ->defaultItems(0)
                                            ->hiddenLabel()
                                            ->addActionLabel('Adicionar gestor/fiscal'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull()
            ]);
    }
    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('imovelRelacaoref.descricao', 'Imóvel', isFirstColumn: true)
                    ->limit(45)
                    ->tooltip(fn ($record): ?string => $record->imovelRelacaoref?->descricao),
                TableColumns::text('resumo', 'Partes/Terceiros')
                    ->limit(40)
                    ->tooltip(fn ($record): ?string => $record->resumo),
                TableColumns::text('proprietario_responsavel', 'Proprietário/Responsável')
                    ->limit(35)
                    ->tooltip(fn ($record): ?string => $record->proprietario_responsavel),
                TableColumns::text('condicao_uso', 'Condição de Uso')
                    ->limit(35)
                    ->tooltip(fn ($record): ?string => $record->condicao_uso),
                TableColumns::text('num_processo', 'Nº Processo')
                    ->badge(),
                TableColumns::text('situacao', 'Situação')
                    ->badge(),
                TableColumns::date('data_assinatura', 'Assinatura'),
                TableColumns::date('vencimento', 'Vencimento'),
                TableColumns::text('vigencia', 'Vigência')
                    ->badge(),
                TableColumns::text('retribuicao', 'Retribuição')
                    ->badge(),
            ])
            ->filters([

            ], layout: Tables\Enums\FiltersLayout::AboveContent);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCedidos::route('/'),
            'create' => Pages\CreateCedido::route('/create'),
            'edit' => Pages\EditCedido::route('/{record}/edit'),
        ];
    }
}
