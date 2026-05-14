<?php

namespace App\Filament\Egap\Widgets\EgapDashboard;

use App\Services\PatrimonioDashboardService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Number;

class PatrimonioTopMateriaisValorTable extends TableWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 5;

    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $service = app(PatrimonioDashboardService::class);

        return $table
            ->heading('Top 10 materiais por valor')
            ->description('Bens móveis ativos agrupados por descrição resumida e detalhada')
            ->query($service->topMovableMaterialsQuery($this->filters))
            ->striped()
            ->paginated(false)
            ->defaultSort('valor_atual', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('descricao_resumida')
                    ->label('Material')
                    ->searchable(false)
                    ->wrap(),

                Tables\Columns\TextColumn::make('descricao_detalhada')
                    ->label('Descrição detalhada')
                    ->searchable(false)
                    ->wrap(),

                Tables\Columns\TextColumn::make('quantidade')
                    ->label('Qtde')
                    ->alignEnd()
                    ->formatStateUsing(fn ($state): string => Number::format((int) $state, locale: 'pt_BR')),

                Tables\Columns\TextColumn::make('valor_aquisicao')
                    ->label('Valor aquisição')
                    ->money('BRL', locale: 'pt_BR')
                    ->alignEnd()
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('valor_atual')
                    ->label('Valor atual')
                    ->money('BRL', locale: 'pt_BR')
                    ->alignEnd()
                    ->badge()
                    ->color('success'),
            ])
            ->actions([])
            ->bulkActions([])
            ->selectable(false);
    }
}
