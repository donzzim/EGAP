<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis;

use App\Filament\Resources\Patrimonio\BensMoveis\ValidarTermoResource\Pages;
use App\Models\Patrimonio\BensMoveis\Termo;
use App\Filament\Clusters\PatrimonioCluster;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Pages\SubNavigationPosition;
use Filament\Forms\Components\{TextInput, Textarea, Grid, Section, Placeholder};
use Illuminate\Support\Facades\Auth;

class ValidarTermoResource extends Resource
{
    protected static ?string $model = Termo::class;
    protected static ?string $navigationIcon = 'heroicon-o-check-badge';
    protected static ?string $cluster = PatrimonioCluster::class;
    protected static ?string $slug = 'validar-termos';
    protected static ?string $navigationGroup = 'Bens Móveis';
    protected static ?string $navigationLabel = 'Validar Termos';
    protected static ?int $navigationSort = 4;
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function canCreate(): bool { return false; }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Anexo do Termo')
                    ->schema([
                        Grid::make(2)->schema([
                            Placeholder::make('arquivo_digital_display')
                                ->label('Documento do Termo')
                                ->content(fn ($record) => $record->situacao_entrega === 'Validado'
                                    ? new \Illuminate\Support\HtmlString("<a href='".route('termo.imprimir.dinamico', ['id' => $record->id])."' target='_blank' class='text-primary-600 underline font-bold'>Visualizar Termo Dinâmico (ID: {$record->id})</a>")
                                    : 'Aguardando Validação'),

                            TextInput::make('situacao_entrega')->label('Situação Atual')->readOnly(),
                            TextInput::make('id')->label('ID do Registro')->readOnly(),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('termo_completo')->label('Termo')->searchable(['num_termo', 'ano_termo'])->weight('bold'),

                Tables\Columns\TextColumn::make('status_virtual')
                    ->label('Link do Arquivo')
                    ->getStateUsing(fn ($record) => $record->situacao_entrega === 'Validado' ? "Abrir Documento" : "Pendente")
                    ->color(fn ($state) => $state === 'Abrir Documento' ? 'primary' : 'gray')
                    ->weight('bold')
                    ->url(fn ($record) => $record->situacao_entrega === 'Validado' ? route('termo.imprimir.dinamico', ['id' => $record->id]) : null)
                    ->openUrlInNewTab(),

                Tables\Columns\TextColumn::make('situacao_entrega')
                    ->label('Situação')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Validado' => 'success',
                        'Em rota' => 'info',
                        'Cancelado' => 'danger',
                        default => 'warning',
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()->label('Visualizar Detalhes'),
                    Action::make('imprimir')
                        ->label('Imprimir termo')
                        ->icon('heroicon-o-printer')
                        ->url(fn ($record) => route('termo.imprimir.dinamico', ['id' => $record->id]))
                        ->openUrlInNewTab(),
                    Action::make('validar')
                        ->label('Validar Termo')
                        ->icon('heroicon-o-hand-thumb-up')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Termo $record) {
                            $record->update(['situacao_entrega' => 'Validado']);
                            Notification::make()->title('Termo Validado!')->success()->send();
                        })->visible(fn ($record) => $record->situacao_entrega !== 'Validado'),
                    Action::make('cancelar')
                        ->label('Invalidar/Cancelar Termo')
                        ->icon('heroicon-o-hand-thumb-down')
                        ->color('danger')
                        ->action(function (Termo $record) {
                            $record->update(['situacao_entrega' => 'Cancelado']);
                            Notification::make()->title('Termo Invalidado')->danger()->send();
                        })->visible(fn ($record) => $record->situacao_entrega !== 'Cancelado'),
                ])->label('Opções')->button(),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array { return ['index' => Pages\ListValidarTermos::route('/')]; }
}
