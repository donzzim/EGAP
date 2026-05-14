<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Depreciação - Patrimônio {{ $record->NumPatrimonio }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            background: #fff;
            color: #000;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 10mm;
            background: #fff;
        }

        /* ===== WRAPPER EXTERNO (Borda Preta conforme e-GAPES.pdf) ===== */
        .doc-border {
            border: 2px solid #000;
        }

        /* ===== CABEÇALHO ===== */
        .header {
            display: flex;
            align-items: stretch;
            border-bottom: 2px solid #000;
        }
        .header-brasao {
            display: flex;
            align-items: center;
            justify-content: center;
            border-right: 2px solid #000;
            padding: 8px;
            width: 90px;
        }
        .header-brasao img {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }
        .header-central {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 0 12px;
        }
        .header-central h1 {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header-central p {
            font-size: 10px;
            margin-top: 2px;
        }
        .header-right {
            width: 150px;
            border-left: 2px solid #000;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 8px;
            text-align: right;
        }

        /* ===== TÍTULO DA SEÇÃO ===== */
        .section-title {
            background: #f2f2f2;
            text-align: center;
            padding: 4px 0;
            border-bottom: 2px solid #000;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* ===== INFO GRID ===== */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            text-transform: uppercase;
            border-bottom: 2px solid #000;
        }
        .info-left {
            border-right: 2px solid #000;
            padding: 6px;
        }
        .info-right {
            padding: 6px;
        }
        .info-row {
            margin-bottom: 4px;
        }
        .info-label {
            font-weight: bold;
        }

        /* ===== TABELA DE DADOS ===== */
        table {
            width: 100%;
            border-collapse: collapse;
            text-transform: uppercase;
            font-size: 9px;
        }
        th {
            background: #f2f2f2;
            border-bottom: 2px solid #000;
            border-right: 1px solid #000;
            padding: 4px;
            font-weight: bold;
            text-align: center;
        }
        td {
            border-bottom: 1px solid #000;
            border-right: 1px solid #000;
            padding: 4px;
        }
        th:last-child, td:last-child { border-right: none; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }

        /* ===== RODAPÉ ===== */
        .footer {
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
            font-size: 8px;
            font-style: italic;
            color: #555;
        }

        /* ===== PRINT CONFIG ===== */
        @media print {
            body { background: none; }
            .page { margin: 0; padding: 0; width: 100%; }
            @page { margin: 10mm; }
        }
    </style>
</head>
<body onload="window.print()">

<div class="page">
    <div class="doc-border">
        
        <div class="header">
            <div class="header-brasao">
                <img src="{{ asset('images/brasao-tjes.png') }}" alt="Brasão TJES">
            </div>
            <div class="header-central">
                <h1>Tribunal de Justiça do Estado ES</h1>
                <p>Cálculo de Depreciação Mensal - Bens Patrimoniais</p>
            </div>
            <div class="header-right">
                <p>{{ now()->format('d/m/Y') }}</p>
                <p class="font-bold">Setor de Patrimônio</p>
            </div>
        </div>

        <div class="section-title">
            Cálculo de Depreciação Mensal
        </div>

        <div class="info-grid">
            <div class="info-left">
                <div class="info-row"><span class="info-label">Patrim.:</span> {{ $record->NumPatrimonio }}</div>
                <div class="info-row"><span class="info-label">Conta Contábil:</span> {{ $record->contaContabilRef->titulo ?? '1.2.3.1.1.01.42' }}</div>
                <div class="info-row"><span class="info-label">Data Aquisição:</span> {{ \Carbon\Carbon::parse($record->DataCadastro)->format('d/m/Y') }}</div>
                <div class="info-row"><span class="info-label">Valor Aquisição:</span> R$ {{ number_format($record->ValorAquisicao, 2, ',', '.') }}</div>
            </div>
            <div class="info-right">
                <div class="info-row"><span class="info-label">Descrição:</span> {{ $record->Descricao }}</div>
                <div class="info-row"><span class="info-label">Elemento Despesa:</span> {{ $record->elementoDespesaRef->DescricaodaClasse ?? 'Mobiliário em Geral' }}</div>
                <div class="info-row">
                    <span class="info-label">Vida Útil:</span> {{ $vidaUtil }} Meses
                    <span class="info-label" style="margin-left: 20px;">Valor Residual:</span> R$ {{ number_format($valorResidual, 2, ',', '.') }}
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Data Cálculo</th>
                    <th>Valor (R$)</th>
                    <th>Vida Útil (Meses)</th>
                    <th>Valor Residual</th>
                    <th>Depreciação Mensal</th>
                    <th>Depreciação Acumulada</th>
                    <th>Valor Líquido Contábil</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dados as $index => $linha)
                <tr>
                    <td class="text-center">{{ count($dados) - $index }}</td>
                    <td class="text-center">{{ $linha['data'] }}</td>
                    <td class="text-right">{{ number_format($record->ValorAquisicao, 2, ',', '.') }}</td>
                    <td class="text-center">{{ $vidaUtil }}</td>
                    <td class="text-right">{{ number_format($valorResidual, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($linha['mensal'], 4, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($linha['acumulada'], 4, ',', '.') }}</td>
                    <td class="text-right font-bold">{{ number_format($linha['liquido'], 4, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

    </div>{{-- /.doc-border --}}

    <div class="footer">
        <span>Documento gerado eletronicamente em {{ now()->format('d/m/Y H:i:s') }}</span>
        <span>Página 1/1</span>
    </div>
</div>

</body>
</html>