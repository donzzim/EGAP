<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensImoveis\TermoResponsabilidadeResource\Pages;
use App\Filament\Support\TableDefaults;
use App\Filament\Support\TableColumns;
use App\Models\Patrimonio\BensImoveis\TermoResponsabilidade;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;

class TermoResponsabilidadeResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = TermoResponsabilidade::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationLabel = 'Termos de Responsabilidade';
    protected static ?string $modelLabel = 'Termo de Responsabilidade';
    protected static ?string $pluralModelLabel = 'Termos de Responsabilidade';
    protected static ?string $navigationGroup = 'Bens Imóveis';
    protected static ?int $navigationSort = 7;
    protected static ?string $slug = 'bens-imoveis/termo-responsabilidade-imoveis';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('num_termo')
                            ->label('Termo Nº')
                            ->numeric(),

                        Forms\Components\TextInput::make('ano_termo')
                            ->label('Ano')
                            ->numeric(),

                        Forms\Components\FileUpload::make('arquivo')
                            ->label('Arquivo')
                            ->columnSpanFull(),

                        Forms\Components\DatePicker::make('atualizado_em')
                            ->label('Atualizado em')
                            ->displayFormat('d/m/Y'),

                        Forms\Components\Select::make('atualizado_por')
                            ->label('Atualizado por')
                            ->relationship('atualizadoPorRelacaoref', 'name')
                            ->searchable()
                            ->preload(),
                    ])->columns(2),

                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Repeater::make('termosImoveis')
                            ->relationship('termosImoveis')
                            ->label('')
                            ->schema([
                                Forms\Components\DateTimePicker::make('date_time')
                                    ->label('date time')
                                    ->displayFormat('Y-m-d H:i:s'),

                                Forms\Components\Select::make('termo')
                                    ->label('Termo')
                                    ->relationship('termoRelacaoref', 'num_termo')
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Select::make('imovel')
                                    ->label('Imóvel')
                                    ->relationship('imovelRelacaoref', 'descricao')
                                    ->searchable()
                                    ->preload(),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->addable(false)
                            ->deletable(false)
                    ])
            ]);
    }
    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('num_termo', 'Termo Nº', isFirstColumn: true),
                TableColumns::text('ano_termo', 'Ano'),
                TableColumns::text('arquivo', 'Arquivo')
                    ->url(fn ($record) => $record->arquivo ? "https://sistemas.tjes.jus.br/patrimonio{$record->arquivo}" : null)
                    ->openUrlInNewTab()
                    ->extraCellAttributes(['style' => 'color: #3b82f6; text-decoration: underline; cursor: pointer;']),
                TableColumns::dateTime('date_time', 'Atualizado em', 'd/m/Y'),
                TableColumns::text('atualizadoPorRelacaoref.name', 'Atualizado por'),
                TableColumns::text('termosImoveis.imovelRelacaoref.descricao', 'Imóvel')
                    ->listWithLineBreaks()
                    ->limitList(3),
            ])
            ->filters([
                //
            ])
            ->searchPlaceholder('Entre com a palavra-chave');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTermoResponsabilidades::route('/'),
            'create' => Pages\CreateTermoResponsabilidade::route('/create'),
            'edit' => Pages\EditTermoResponsabilidade::route('/{record}/edit'),
        ];
    }
}
