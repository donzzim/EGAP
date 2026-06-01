<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Relatório de Baixa - Bens Patrimoniais</title>
    <style>
        /* Estilização da página centralizada (Padrão e-GAPes) */
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 0; padding: 20px; background: #525659; display: flex; justify-content: center; }
        .page { background: white; width: 210mm; min-height: 297mm; padding: 15mm; box-sizing: border-box; box-shadow: 0 0 10px rgba(0,0,0,0.5); }

        /* Tabelas e Bordas */
        .header-table { width: 100%; border-collapse: collapse; border: 1px solid #000; margin-bottom: 20px; }
        .header-table td { padding: 5px; }
        .title { text-align: center; font-size: 16px; font-weight: bold; margin-bottom: 15px; text-transform: uppercase; }
        .info-table, .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td, .info-table th, .items-table td, .items-table th { border: 1px solid #000; padding: 5px; text-align: left; }

        /* Grid de Materiais */
        .items-table th { text-align: center; font-weight: bold; text-transform: uppercase; background-color: #f2f2f2; }
        .items-table td { text-align: center; }
        .items-table td.desc { text-align: left; }

        /* Auxiliares */
        .text-right { text-align: right; }
        .total-row td { font-weight: bold; background-color: #fafafa; font-size: 11px; }
        .signatures { width: 100%; margin-top: 50px; text-align: center; }
        .signatures td { width: 100%; vertical-align: top; padding: 0 20px;}
        .sign-line { border-top: 1px solid #000; margin: 40px auto 2px; width: 40%; }
        .footer { border-top: 1px solid #ccc; margin-top: 40px; padding-top: 10px; font-size: 10px; display: table; width: 100%;}

        @media print {
            body { background: white; padding: 0; display: block; }
            .page { width: 100%; min-height: auto; padding: 0; box-shadow: none; border: none; }
        }
    </style>
</head>
<body>
    <div class="page">
        <table class="header-table">
            <tr>
                <td style="width: 80px; text-align: center;">
                    @php
                        $pathBrasao = public_path('images/brasao-tjes.png');
                        $brasaoData = file_exists($pathBrasao) ? base64_encode(file_get_contents($pathBrasao)) : null;
                    @endphp

                    @if($brasaoData)
                        <img src="data:image/png;base64,{{ $brasaoData }}" style="width: 60px;">
                    @else
                        <div style="color:red; font-size:8px;">BRASÃO NÃO ENCONTRADO</div>
                    @endif
                </td>
                <td style="vertical-align: top;">
                    <b>TRIBUNAL DE JUSTIÇA DO ESTADO ES</b><br><br><br>
                    Relatório de Baixa - Bens Patrimoniais
                </td>
                <td style="text-align: right; vertical-align: bottom; width: 150px;">
                    <b>Setor de Patrimônio</b>
                </td>
            </tr>
        </table>

        <div class="title">Relatório de Baixa dos Bens Patrimoniais</div>

        <table class="info-table">
            <tr>
                <td style="width: 120px; font-weight: bold;">PROCESSO Nº</td>
                <td style="font-weight: bold; font-size: 12px;">{{ $numeroProcesso }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">REQUISITANTE</td>
                <td>{{ mb_strtoupper($baixa->Requisitante ?? 'NÃO INFORMADO') }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">CNPJ</td>
                <td>{{ $baixa->RequisitanteCnpj ?? 'NÃO INFORMADO' }}</td>
            </tr>
            @if(!empty($baixa->Endereco))
            <tr>
                <td style="font-weight: bold;">ENDEREÇO</td>
                <td>{{ mb_strtoupper($baixa->Endereco) }}</td>
            </tr>
            @endif
        </table>

        <!-- Grid de Itens Baixados -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 40px;">ITEM</th>
                    <th style="width: 90px;">PAT. C/ CÓD. BARRAS</th>
                    <th style="text-align: left;">DESCRIÇÃO DO BEM/MARCA/MODELO</th>
                    <th style="width: 90px;" class="text-right">VALOR AQUISIÇÃO</th>
                    <th style="width: 90px;" class="text-right">VALOR REAVALIADO</th>
                    <th style="width: 80px;">NRO SÉRIE</th>
                    <th style="width: 100px;">PROCESSO BAIXA</th>
                </tr>
            </thead>
            <tbody>
                @foreach($itens as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><b>{{ $item->NumPatrimonio }}</b></td>
                    <td class="desc">{{ $item->MaterialDescricao }}</td>
                    <td class="text-right">R$ {{ number_format((float) ($item->ValorAquisicao ?? 0), 2, ',', '.') }}</td>
                    <td class="text-right">R$ {{ number_format((float) ($item->ValordaReavaliacao ?? 0), 2, ',', '.') }}</td>
                    <td>{{ $item->NumerodeSerie ?? '-' }}</td>
                    <td>{{ $numeroProcesso }}</td>
                </tr>
                @endforeach

                <tr class="total-row">
                    <td colspan="3" class="text-right">TOTAL</td>
                    <td class="text-right">R$ {{ number_format((float) $totalAquisicao, 2, ',', '.') }}</td>
                    <td class="text-right">R$ {{ number_format((float) $totalReavaliado, 2, ',', '.') }}</td>
                    <td colspan="2"></td>
                </tr>
            </tbody>
        </table>

        <table class="signatures">
            <tr>
                <td>
                    <div class="sign-line"></div>
                    <div><b>COMISSÃO DE INVENTÁRIO E CONTROLE PATRIMONIAL</b></div>
                    <div style="font-size: 9px; color: #555;">Seção de Patrimônio - TJES</div>
                </td>
            </tr>
        </table>

        <div class="footer">
            <div style="display: table-cell; width: 60px; vertical-align: middle;">
                @if($brasaoData)
                    <img src="data:image/png;base64,{{ $brasaoData }}" style="width: 40px;">
                @endif
            </div>
            <div style="display: table-cell; vertical-align: middle;">
                Documento gerado em {{ date('d/m/Y \à\s H:i:s') }} via e-GAPes Patrimônio.
            </div>
        </div>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() { window.print(); }, 500);
        }
    </script>
</body>
</html>
