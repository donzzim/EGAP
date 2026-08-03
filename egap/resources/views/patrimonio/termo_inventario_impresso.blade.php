<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Termo de Responsabilidade - Inventário</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 0; padding: 20px; background: #525659; display: flex; justify-content: center; }
        .page { background: white; width: 210mm; min-height: 297mm; padding: 15mm; box-sizing: border-box; box-shadow: 0 0 10px rgba(0,0,0,0.5); }
        .header-table { width: 100%; border-collapse: collapse; border: 1px solid #000; margin-bottom: 20px; }
        .header-table td { padding: 5px; }
        .title { text-align: center; font-size: 16px; font-weight: bold; margin-bottom: 15px; text-transform: uppercase; }
        .declaration { text-align: justify; margin-bottom: 15px; font-size: 12px;}
        .info-table, .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td, .info-table th, .items-table td, .items-table th { border: 1px solid #000; padding: 5px; text-align: left; }
        .items-table th { text-align: center; font-weight: bold; }
        .items-table td { text-align: center; }
        .items-table td.desc { text-align: left; }
        .signatures { width: 100%; margin-top: 50px; text-align: center; }
        .signatures td { width: 50%; vertical-align: top; padding: 0 20px;}
        .sign-line { border-top: 1px solid #000; margin-bottom: 2px; }
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
                    @if(file_exists(public_path('images/brasao.png')))
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/brasao.png'))) }}" style="width: 60px;">
                    @endif
                </td>
                <td style="vertical-align: top;">
                    <b>TRIBUNAL DE JUSTIÇA DO ESTADO ES</b><br><br><br>
                    Relatório de Inventário - Bens Patrimoniais
                </td>
                <td style="text-align: right; vertical-align: bottom;">
                    <b>Comissão de Inventário</b>
                </td>
            </tr>
        </table>

        <div class="title">TERMO DE RESPONSABILIDADE - No. {{ $termo->num_termo }}/{{ $termo->ano_termo }}</div>

        <div class="declaration">
            Declaro pelo presente documento que os materiais especificados abaixo estão devidamente localizados nesta unidade. Comprometo-me a zelar pelo bom uso e guarda dos mesmos, bem como informar a Seção de Patrimônio eventual modificação de sua localização e/ou qualquer defeito/avaria, sob pena de responsabilização.
        </div>

        <table class="info-table">
            <tr><td style="width: 120px; font-weight: bold;">UNIDADE</td><td>{{ mb_strtoupper($unidade) }}</td></tr>
            <tr><td style="font-weight: bold;">SETOR</td><td>{{ mb_strtoupper($setor) }}</td></tr>
            <tr><td style="font-weight: bold;">COMPLEMENTO</td><td>{{ mb_strtoupper($complemento) }}</td></tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 40px;">ITEM</th>
                    <th style="width: 90px;">PATRIMÔNIO</th>
                    <th style="text-align: left;">DESCRIÇÃO DO BEM / MARCA / MODELO</th>
                    <th style="width: 90px;">SITUAÇÃO</th>
                </tr>
            </thead>
            <tbody>
                @foreach($itens as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->num_patrimonio }}</td>
                    <td class="desc">
                        {{ $item->descricao_detalhada }}
                        @if($item->marca)
                            - Marca: {{ $item->marca }}
                        @endif
                        @if($item->modelo)
                            - Modelo: {{ $item->modelo }}
                        @endif
                    </td>
                    <td>{{ mb_strtoupper($item->estado_conservacao ?: '-') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table class="signatures">
            <tr>
                <td>
                    <small>Comissão (Membro)</small>
                    <div class="sign-line" style="width: 60%; margin: 10px auto 2px;"></div>
                    <div>{{ mb_strtoupper($responsavel) }}</div>
                    <div>{{ mb_strtoupper($cargo) }}</div>
                </td>
                <td>
                    <small>Setor (Responsável)</small>
                    <div class="sign-line" style="width: 60%; margin: 10px auto 2px;"></div>
                    <div>{{ mb_strtoupper($responsavel) }}</div>
                    <div>{{ mb_strtoupper($cargo) }}</div>
                </td>
            </tr>
        </table>

        <div class="footer">
            <div style="display: table-cell; width: 60px;">
                @if(file_exists(public_path('images/brasao.png')))
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/brasao.png'))) }}" style="width: 40px;">
                @endif
            </div>
            <div style="display: table-cell; vertical-align: middle;">
                Documento assinado eletronicamente por <b>{{ mb_strtoupper($responsavel) }} - {{ $cpf ?: 'NÃO INFORMADO' }}</b>, <b>{{ mb_strtoupper($cargo) }}</b>, em <b>{{ $dataAssinatura }}</b>, conforme art. 1º do Ato Normativo TJES Nº 75/2018. Código de Validação <b>{{ $termo->num_termo }}/{{ $termo->ano_termo }}</b>.
            </div>
        </div>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>
