<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Bens Patrimoniais</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; margin: 0; padding: 10mm; }
        .doc-border { border: 2px solid #000; width: 100%; }
        .header { display: flex; border-bottom: 2px solid #000; }
        .header-brasao { padding: 5px; border-right: 2px solid #000; width: 70px; text-align: center; }
        .header-brasao img { width: 50px; height: 50px; }
        .header-title { flex: 1; padding: 5px 10px; display: flex; flex-direction: column; justify-content: center; }
        .header-title h1 { font-size: 13px; margin: 0; text-transform: uppercase; }
        .header-right { padding: 5px 10px; border-left: 2px solid #000; text-align: right; width: 150px; }
        .section-title { background: #fff; border-bottom: 2px solid #000; text-align: center; padding: 4px; font-weight: bold; text-transform: uppercase; font-size: 12px; }
        
        table { width: 100%; border-collapse: collapse; text-transform: uppercase; font-size: 8px; }
        th { border-bottom: 2px solid #000; border-right: 1px solid #000; padding: 3px; font-weight: bold; background: #fff; }
        td { border-bottom: 1px solid #000; border-right: 1px solid #000; padding: 5px 3px; vertical-align: top; }
        th:last-child, td:last-child { border-right: none; }
        
        @media print { 
            .no-print { display: none; }
            @page { size: landscape; margin: 5mm; } /* Paisagem para caber todas as colunas */
        }
    </style>
</head>
<body onload="window.print()">

<div class="doc-border">
    <div class="header">
        <div class="header-brasao">
            <img src="{{ asset('images/brasao-tjes.png') }}" alt="Brasão">
        </div>
        <div class="header-title">
            <h1>TRIBUNAL DE JUSTIÇA DO ESTADO ES</h1>
            <p>Relatório de Bens Patrimoniais</p>
        </div>
        <div class="header-right">
            <p>{{ now()->format('d/m/Y') }}</p>
            <p style="margin-top: 15px; font-weight: bold;">Setor de Patrimônio</p>
        </div>
    </div>

    <div class="section-title">Relatório de Bens Patrimoniais</div>

    <table>
        <thead>
            <tr>
                <th>PATRIMÔNIO</th>
                <th>NÚMERO DE SÉRIE</th>
                <th>DESCRIÇÃO DETALHADA</th>
                <th>MARCA</th>
                <th>MODELO</th>
                <th>UNIDADE JUDICIÁRIA</th>
                <th>SETOR</th>
                <th>COMPLEMENTO DO SETOR</th>
                <th>VALOR AQUISIÇÃO</th>
                <th>SITUAÇÃO</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bens as $bem)
            <tr>
                <td style="text-align: center;">{{ $bem->NumPatrimonio }}</td>
                <td style="text-align: center;">{{ $bem->NumerodeSerie ?? 'NULL' }}</td>
                <td>{{ $bem->Descricao }}</td>
                <td style="text-align: center;">{{ $bem->marcaRef->descricao ?? 'NULL' }}</td>
                <td style="text-align: center;">{{ $bem->modeloRef->descricao ?? 'NULL' }}</td>
                <td>{{ $bem->unidadeJudiciariaRef->Setor ?? 'NULL' }}</td>
                <td>{{ $bem->setorRef->Setor ?? 'NULL' }}</td>
                <td>{{ $bem->ComplementoSetor ?? 'NULL' }}</td>
                <td style="text-align: right;">{{ number_format($bem->ValorAquisicao, 2, ',', '.') }}</td>
                <td style="text-align: center;">{{ $bem->situacaoBemRef->descricao_completa ?? 'NULL' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

</body>
</html>
