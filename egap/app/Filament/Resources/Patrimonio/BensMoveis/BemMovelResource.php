<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensMoveis\BemMovelResource\Pages;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Patrimonio\BensMoveis\BemMovel;
use App\Services\Patrimonio\RecalcularDepreciacaoService;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Throwable;

class BemMovelResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = BemMovel::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'Bens Móveis';

    protected static ?string $navigationLabel = 'Administração';

    protected static ?string $modelLabel = 'Bem Móvel';

    protected static ?string $pluralModelLabel = 'Administração dos Bens Móveis';

    protected static ?string $recordTitleAttribute = 'NumPatrimonio';

    protected static ?string $slug = 'bens-moveis/adm-bens-moveis';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([

        ]);
    }

    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('ultimaTransferencia'))
            ->columns([
                TableColumns::text('NumPatrimonio', 'Patrimônio'),
                TableColumns::text('NumerodeSerie', 'Número de Série'),
                TableColumns::text('Descricao', 'Descrição Detalhada'),
                TableColumns::text('modeloRef.descricao', 'Modelo'),
                TableColumns::text('unidadeJudiciariaRef.UnidadeOrganizacional', 'Unidade Judiciária'),
                TableColumns::text('setorRef.Setor', 'Setor'),
                TableColumns::text('complementoSetorRef.descricao', 'Complemento do Setor'),
                TableColumns::money('Valor', 'Valor de Aquisição'),
                TableColumns::date('DataDisponibilizacao', 'Data da Disponibilização'),
                TableColumns::date('DatadaReavaliacao', 'Data da Reavaliação'),
            ])
            ->actions([
                ...TableDefaults::actions(),
                ActionGroup::make([
                    self::imprimirTermoTableAction(),
                    self::transferirBensTableAction(),
                    self::historicoMovimentacoesTableAction(),
                    self::vincularBensTableAction(),
                    self::atualizarDadosTableAction(),
                    self::calculoDepreciacaoMensalTableAction(),
                    self::gerarNovoCalculoTableAction(),
                    self::conciliarBemTableAction(),
                    self::termoDigitalizadoTableAction(),
                    self::reavaliarBensTableAction(),
                    self::corrigirInformacoesTableAction(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('Imprimir Bens'),
                ...TableDefaults::bulkActions(),
            ]);
    }

    private static function imprimirTermoTableAction(): Action
    {
        return Action::make('imprimir_termo')
            ->label('Imprimir Termo')
            ->icon('heroicon-o-printer')
            ->color('gray')
            ->visible(fn (BemMovel $record): bool => filled($record->ultimaTransferencia?->Termo))
            ->url(fn (BemMovel $record): string => route('termo.imprimir', ['id' => $record->ultimaTransferencia->Termo]))
            ->openUrlInNewTab();
    }

    private static function transferirBensTableAction(): Action
    {
        return Action::make('transferir_bens');
    }

    private static function historicoMovimentacoesTableAction(): Action
    {
        return Action::make('historico_movimentacoes')
            ->label('Histórico de Movimentações')
            ->icon('heroicon-o-arrows-right-left')
            ->color('gray')
            ->modalHeading(fn (BemMovel $record): string => "Histórico de Movimentações - {$record->NumPatrimonio}")
            ->modalWidth('full')
            ->extraModalWindowAttributes([
                'class' => 'egap-modal-window',
                'style' => 'width: calc(100vw - 2rem); max-width: 96rem; height: min(82dvh, 860px); overflow: hidden;',
            ])
            ->stickyModalHeader()
            ->stickyModalFooter()
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Fechar')
            ->modalContent(fn (BemMovel $record): HtmlString => new HtmlString(
                Livewire::mount(
                    'patrimonio.historico-movimentacoes-modal',
                    ['numPatrimonio' => (int) $record->NumPatrimonio],
                    "historico-movimentacoes-{$record->getKey()}",
                )
            ));
    }

    private static function vincularBensTableAction(): Action
    {
        return Action::make('vincular_bens');
    }

    private static function atualizarDadosTableAction(): Action
    {
        return Action::make('atualizar_dados');
    }

    private static function calculoDepreciacaoMensalTableAction(): Action
    {
        return Action::make('calculo_depreciacao_mensal');
    }

    private static function gerarNovoCalculoTableAction(): Action
    {
        return Action::make('gerar_novo_calculo')
            ->label('Gerar Novo Cálculo')
            ->icon('heroicon-o-calculator')
            ->color('gray')
            ->requiresConfirmation()
            ->modalHeading('Gerar novo cálculo de depreciação')
            ->modalDescription('O histórico de depreciação deste bem será apagado e recalculado do zero, e os valores atuais do patrimônio serão atualizados de acordo com o mês corrente.')
            ->modalSubmitActionLabel('Gerar cálculo')
            ->action(function (BemMovel $record): void {
                try {
                    $resultado = app(RecalcularDepreciacaoService::class)->recalcular($record);
                } catch (ValidationException $exception) {
                    Notification::make()
                        ->title('Não foi possível gerar o cálculo.')
                        ->body(collect($exception->errors())->flatten()->implode(' '))
                        ->danger()
                        ->send();

                    return;
                } catch (Throwable $exception) {
                    Notification::make()
                        ->title('Não foi possível gerar o cálculo.')
                        ->body($exception->getMessage())
                        ->danger()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title('Cálculo de depreciação gerado com sucesso.')
                    ->body("{$resultado->parcelasGeradas} parcela(s) geradas. Depreciação mensal: R$ ".number_format($resultado->depreciacaoMensal, 2, ',', '.'))
                    ->success()
                    ->send();
            });
    }

    private static function conciliarBemTableAction(): Action
    {
        return Action::make('conciliar_bem');
    }

    private static function termoDigitalizadoTableAction(): Action
    {
        return Action::make('termo_digitalizado');
    }

    private static function reavaliarBensTableAction(): Action
    {
        return Action::make('reavaliar_bens');
    }

    private static function corrigirInformacoesTableAction(): Action
    {
        return Action::make('reavaliar_bens');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBemMovels::route('/'),
            'create' => Pages\CreateBemMovel::route('/create'),
            'edit' => Pages\EditBemMovel::route('/{record}/edit'),
        ];
    }
}
