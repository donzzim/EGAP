<?php

namespace App\Filament\Clusters\ExternoCluster\Patrimonio;

use App\Filament\Clusters\ExternoCluster\RequisicaoPage;

class RequisicaoDeMateriais extends RequisicaoPage
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cube';

    protected static string | \UnitEnum | null $navigationGroup = 'Patrimônio';

    protected static ?string $navigationLabel = 'Requisição de Materiais Permanentes';

    protected static ?string $title = 'Requisição de Materiais Permanentes';

    protected static ?string $slug = 'patrimonio/requisicao-de-materiais-permanentes';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.externo.patrimonio.requisicao-de-materiais-permanentes';
}
