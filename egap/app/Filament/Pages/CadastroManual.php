<?php

namespace App\Filament\Pages;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensMoveis\BemMovelResource;
use Filament\Pages\Page;

class CadastroManual extends Page
{
    protected static ?string $cluster = PatrimonioCluster::class;

    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';

    protected static ?string $navigationLabel = 'Cadastro manual do bem';

    protected static ?string $navigationGroup = 'Bens Móveis';

    protected static ?int $navigationSort = 2;

    public function mount(): void
    {
        $this->redirect(BemMovelResource::getUrl('create'));
    }
}
