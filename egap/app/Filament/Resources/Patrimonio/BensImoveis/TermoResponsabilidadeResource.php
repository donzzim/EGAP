<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis;

use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use App\Filament\Resources\Patrimonio\BensImoveis\TermoResponsabilidadeResource\Pages\ListTermoResponsabilidades;
use App\Filament\Resources\Patrimonio\BensImoveis\TermoResponsabilidadeResource\Pages\CreateTermoResponsabilidade;
use App\Filament\Resources\Patrimonio\BensImoveis\TermoResponsabilidadeResource\Pages\EditTermoResponsabilidade;
use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensImoveis\TermoResponsabilidadeResource\Pages;
use App\Filament\Support\LegacyFileUrl;
use App\Filament\Support\TableDefaults;
use App\Filament\Support\TableColumns;
use App\Models\Patrimonio\BensImoveis\TermoResponsabilidade;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TermoResponsabilidadeResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

    protected static ?\Filament\Pages\Enums\SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = TermoResponsabilidade::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationLabel = 'Termos de Responsabilidade';
    protected static ?string $modelLabel = 'Termo de Responsabilidade';
    protected static ?string $pluralModelLabel = 'Termos de Responsabilidade';
    protected static string | \UnitEnum | null $navigationGroup = 'Bens Imóveis';
    protected static ?int $navigationSort = 7;
    protected static ?string $slug = 'bens-imoveis/termo-responsabilidade-imoveis';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('num_termo')
                            ->label('Termo Nº')
                            ->disabled(fn (string $operation): ?int => $operation === 'create' ? true : false)
                            ->default(fn (string $operation): ?int => $operation === 'create' ? self::getNextNumTermo() : null)
                            ->numeric(),

                        TextInput::make('ano_termo')
                            ->label('Ano')
                            ->numeric(),

                        FileUpload::make('arquivo')
                            ->label('Arquivo')
                            ->disk('public')
                            ->directory('files/imoveis/termo-responsabilidade')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    private static function getNextNumTermo(): int
    {
        $ultimoNumTermo = TermoResponsabilidade::query()
            ->whereNotNull('num_termo')
            ->where('num_termo', '<>', '')
            ->orderByRaw('CAST(num_termo AS UNSIGNED) DESC')
            ->value('num_termo');

        return ((int) $ultimoNumTermo) + 1;
    }

    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('num_termo', 'Termo Nº', isFirstColumn: true),
                TableColumns::text('ano_termo', 'Ano'),
                TableColumns::text('arquivo', 'Arquivo')
                    ->url(fn ($record): ?string => LegacyFileUrl::resolve($record->arquivo, config('app.egap')))
                    ->openUrlInNewTab()
                    ->formatStateUsing(fn ($state) => $state ? 'Abrir' : '-')
                    ->color('primary')
                    ->weight('bold'),
                TableColumns::updatedBy('atualizadoPorRef.name'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTermoResponsabilidades::route('/'),
            'create' => CreateTermoResponsabilidade::route('/create'),
            'edit' => EditTermoResponsabilidade::route('/{record}/edit'),
        ];
    }
}
