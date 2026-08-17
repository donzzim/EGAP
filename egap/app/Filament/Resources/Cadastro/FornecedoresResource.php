<?php

namespace App\Filament\Resources\Cadastro;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Enums\FiltersLayout;
use App\Filament\Resources\Cadastro\FornecedoresResource\Pages\ListFornecedores;
use App\Filament\Resources\Cadastro\FornecedoresResource\Pages\CreateFornecedores;
use App\Filament\Resources\Cadastro\FornecedoresResource\Pages\EditFornecedores;
use App\Filament\Resources\Cadastro\FornecedoresResource\Pages;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Cadastro\Fornecedores;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FornecedoresResource extends Resource
{
    protected static ?string $model = Fornecedores::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel = 'Fornecedores';
    protected static string | \UnitEnum | null $navigationGroup = 'Cadastro';
    protected static ?string $modelLabel = 'Fornecedor';
    protected static ?string $pluralModelLabel = 'Fornecedores';
    protected static ?int $navigationSort = 8;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(12)
                    ->schema([
                        TextInput::make('NomeFornecedor')
                            ->label('Nome do Fornecedor')
                            ->placeholder('Ex.: Empresa Modelo Ltda')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(8),

                        Select::make('Pessoa')
                            ->label('Tipo de Pessoa')
                            ->placeholder('Selecione o tipo')
                            ->native(false)
                            ->searchable(false)
                            ->options([
                                'Jurídica' => 'Jurídica',
                                'Física' => 'Física',
                            ])
                            ->required()
                            ->columnSpan(4),

                        TextInput::make('CNPJ')
                            ->label('CNPJ')
                            ->placeholder('00.000.000/0000-00')
                            ->mask('99.999.999/9999-99')
                            ->stripCharacters(['.', '/', '-'])
                            ->rule('digits:14')
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('NomeFornecedor', 'Fornecedor', isFirstColumn: true)
                    ->wrap(),

                TableColumns::text('Pessoa', 'Tipo')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'Jurídica' => 'info',
                        'Física' => 'success',
                        default => 'gray',
                    }),

                TableColumns::text('CNPJ', 'CNPJ')
                    ->formatStateUsing(fn (?string $state): string => static::formatCnpj($state))
                    ->copyable()
                    ->copyMessage('CNPJ copiado'),

                TableColumns::updatedBy('atualizado_por.name'),
            ])
            ->filters([
                SelectFilter::make('Pessoa')
                    ->columnSpan(2)
                    ->label('Tipo de Pessoa')
                    ->options([
                        'Jurídica' => 'Jurídica',
                        'Física' => 'Física',
                    ]),
            ], FiltersLayout::AboveContent)
            ->defaultSort('NomeFornecedor');
    }

    public static function formatCnpj(?string $value): string
    {
        $digits = preg_replace('/\D/', '', $value ?? '');

        if (strlen($digits) !== 14) {
            return $value ?: '-';
        }

        return preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/', '$1.$2.$3/$4-$5', $digits);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFornecedores::route('/'),
        ];
    }
}
