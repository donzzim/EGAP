<?php

namespace App\Filament\Clusters\ExternoCluster\Agendamento;

use App\Filament\Clusters\ExternoCluster;
use App\Filament\Clusters\ExternoCluster\Almoxarifado\RequisicaoDeMateriaisPage;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class SolicitacaoVeiculos extends Page
{
    protected static ?int $navigationSort = 1;

    protected static ?string $cluster = ExternoCluster::class;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::ArchiveBox;

    protected static string | \UnitEnum | null $navigationGroup = 'Agendamento de Veículos';

    protected static ?string $slug = 'agendamento/solicitacao-veiculos';

    protected static ?string $navigationLabel = 'Solicitação';

    protected static ?string $title = 'Solicitação de Veículo';

    protected string $view = 'filament.pages.externo.agendamento.solicitacao-veiculos';


}
