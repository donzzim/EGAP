<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\EgapDashboard\PatrimonioImoveisPorContaChart;
use App\Filament\Widgets\EgapDashboard\PatrimonioMoveisPorAnoChart;
use App\Filament\Widgets\EgapDashboard\PatrimonioMoveisPorSituacaoChart;
use App\Filament\Widgets\EgapDashboard\PatrimonioOverviewStats;
use App\Filament\Widgets\EgapDashboard\PatrimonioTopMateriaisValorTable;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class EgapDashboard extends Dashboard
{
    use HasFiltersForm;

    protected static string $routePath = '/';

    protected static ?int $navigationSort = -2;

    protected static ?string $title = 'Painel Administrativo do E-Gap';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filtros')
                    ->description('Refina os indicadores por periodo de incorporação de bens moveis e imóveis.')
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('Data inicial')
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        DatePicker::make('end_date')
                            ->label('Data final')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('clearFilters')
                ->label('Limpar filtros')
                ->icon('heroicon-m-x-mark')
                ->color('gray')
                ->visible(fn (): bool => filled($this->filters))
                ->action(function (): void {
                    $this->filters = [];
                    $this->getFiltersForm()->fill();
                }),
        ];
    }

    public function getWidgets(): array
    {
        return [
            PatrimonioOverviewStats::class,
            PatrimonioMoveisPorSituacaoChart::class,
            PatrimonioMoveisPorAnoChart::class,
            PatrimonioImoveisPorContaChart::class,
            PatrimonioTopMateriaisValorTable::class,
        ];
    }

    public static function getColors(array $color, int $limit = 10): array
    {
        $palette = [900, 800, 700, 600, 500, 400, 300, 200, 100, 50];
        $colors = [];

        while (count($colors) < max($limit, 1)) {
            foreach ($palette as $tone) {
                $colors[] = 'rgb(' . $color[$tone] . ')';

                if (count($colors) >= max($limit, 1)) {
                    break;
                }
            }
        }

        return $colors;
    }

    public function getColumns(): int | string | array
    {
        return [
            'md' => 4,
            'xl' => 4,
        ];
    }

    public function mount(): void
    {
        if (session()->has('egap_boas_vindas')) {
            $firstName = str(auth()->user()->name)->trim()->before(' ')->toString();

            Notification::make()
                ->title('Bem-vindo ao EGAP, '. $firstName .'!')
                ->body('Login realizado com sucesso.')
                ->success()
                ->send();

            session()->forget('egap_boas_vindas');
        }
    }
}
