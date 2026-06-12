<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Termo de Responsabilidade</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 0; padding: 20px; background: #525659; display: flex; justify-content: center; }
        .page { background: white; width: 210mm; min-height: 297mm; padding: 15mm; box-sizing: border-box; box-shadow: 0 0 10px rgba(0,0,0,0.5); }
        .warning-box { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px; text-align: center; margin-bottom: 15px; font-size: 11px; font-weight: bold;}
        .warning-box p { margin: 5px 0; font-weight: normal; text-align: justify;}
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
        .sign-box { border: 1px solid #ccc; height: 80px; width: 80%; margin: 10px auto; }
        .footer { border-top: 1px solid #ccc; margin-top: 40px; padding-top: 10px; font-size: 10px; display: table; width: 100%;}
        
        @media print {
            body { background: white; padding: 0; display: block; }
            .page { width: 100%; min-height: auto; padding: 0; box-shadow: none; border: none; }
        }
    </style>
</head>
<body>
    @php
        $assinaturaEletronica = $assinaturaEletronica ?? ((int) ($arquivoDigital->situacao ?? 0) === 1);
        $usuarioDestinatario = $usuarioDestinatario ?? $usuarioEmitente ?? null;
        $cargoDestinatario = $cargoDestinatario ?? $cargoEmitente ?? null;
        $cpfDestinatario = $cpfDestinatario ?? $cpfEmitente ?? null;
        $dataEmissao = $dataEmissao ?? date('d/m/Y', strtotime($termo->date_time ?? now()));
        $dataAssinatura = $dataAssinatura ?? $dataEmissao;
    @endphp
    <div class="page">
        @if(! $assinaturaEletronica)
        <div class="warning-box">
            IMPORTANTE!
            <p>Para concluir a transferência, solicite <b>assinatura eletrônica</b> ao servidor recebedor dos bens (setor destinatário), caso contrário, eles permanecerão na relação de bens do setor remetente e a transferência ficará pendente no sistema.</p>
            <p>Se a retirada ocorrer pela equipe de logística, imprima este Termo e solicite o preenchimento dos campos no final do documento (identificação, assinatura e data de embarque). Em seguida, digitalize e envie <b>o PDF, por meio do botão "anexar termo" constante no menu "Patrimônio>Movimentação de Materiais"</b>, objetivando comprovar a retirada no setor.</p>
        </div>
        @endif

        <table class="header-table">
            <tr>
                <td style="width: 80px; text-align: center;">
                    @if(file_exists(public_path('images/brasao.png')))
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/brasao.png'))) }}" style="width: 60px;">
                    @endif
                </td>
                <td style="vertical-align: top;">
                    <b>TRIBUNAL DE JUSTIÇA DO ESTADO ES</b><br><br><br>
                    Termo de Responsabilidade - Bens Patrimoniais
                </td>
                <td style="text-align: right; vertical-align: bottom;">
                    <b>Seção de Patrimônio</b>
                </td>
            </tr>
        </table>

        <div class="title">TERMO DE RESPONSABILIDADE - No. {{ $termo->num_termo }}/{{ $termo->ano_termo }}</div>

        <div class="declaration">
            Declaro pelo presente documento de responsabilidade que os materiais especificados abaixo estão devidamente localizados nesta unidade. Comprometo-me a zelar pelo bom uso e guarda dos mesmos, bem como informar a Seção de Patrimônio eventual modificação de sua localização e/ou qualquer defeito/avaria, sob pena de responsabilização.
        </div>

        <table class="info-table">
            <tr><td style="width: 120px; font-weight: bold;">UNIDADE</td><td>{{ mb_strtoupper($unidade ?? 'TRIBUNAL DE JUSTIÇA DO ESPÍRITO SANTO') }}</td></tr>
            <tr><td style="font-weight: bold;">SETOR</td><td>{{ mb_strtoupper($setor ?? 'NÃO INFORMADO') }}</td></tr>
            <tr><td style="font-weight: bold;">COMPLEMENTO</td><td>{{ mb_strtoupper($complemento ?? 'NÃO INFORMADO') }}</td></tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 40px;">ITEM</th>
                    <th style="width: 80px;">PATRIMÔNIO</th>
                    <th style="text-align: left;">DESCRIÇÃO DO BEM/MARCA/MODELO</th>
                    <th style="width: 80px;">SITUAÇÃO</th>
                    <th style="width: 60px;">VALOR</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bens as $index => $bem)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $bem->NumPatrimonio }}</td>
                    <td class="desc">
                        {{ $bem->Descricao }}
                        
                        @if($bem->marca_desc ?? ($bem->marca_re->descricao ?? null))
                            , Marca: {{ $bem->marca_desc ?? $bem->marca_re->descricao }}
                        @endif
                        
                        @if($bem->modelo_desc ?? ($bem->modelo_re->descricao ?? null))
                            , Modelo: {{ $bem->modelo_desc ?? $bem->modelo_re->descricao }}
                        @endif
                    </td>
                    <td>{{ mb_strtoupper($bem->EstadodeConservacao ?? 'BOM') }}</td>
                    
                    @php
                        $valor = $bem->ValorCalculado ?? 0;
                        if (!isset($bem->ValorCalculado)) {
                            $dataInc = $bem->DatadeIncorporacao ?? date('Y-m-d');
                            $valor = (strtotime($dataInc) < strtotime('2015-01-01')) ? ($bem->ValordaReavaliacao ?? 0) : ($bem->ValorAquisicao ?? 0);
                        }
                    @endphp
                    <td>{{ number_format((float) $valor, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table class="signatures">
            <tr>
                <td>
                    @if($assinaturaEletronica)
                        <div class="sign-line" style="width: 60%; margin: 0 auto 2px;">Emitente (Gerado por)</div>
                        <div>{{ mb_strtoupper($usuarioEmitente ?? 'USUÁRIO') }}</div>
                        <div>{{ mb_strtoupper($cargoEmitente ?? 'SERVIDOR') }}</div>
                        <div>{{ $dataEmissao }}</div>
                    @else
                        <div>{{ mb_strtoupper($usuarioEmitente ?? 'USUÁRIO') }} ({{ mb_strtoupper($cargoEmitente ?? 'SERVIDOR') }})</div>
                        <div class="sign-line" style="margin-top: 2px; width: 60%; margin: 2px auto;">Setor (Emitente)</div>
                        <div>Carimbo e Assinatura</div>
                        <div class="sign-box"></div>
                    @endif
                </td>
                <td>
                    @if($assinaturaEletronica)
                        <div class="sign-line" style="width: 60%; margin: 0 auto 2px;">Destinatário (Recebido por)</div>
                        <div>{{ mb_strtoupper($usuarioDestinatario ?? 'USUÁRIO') }}</div>
                        <div>{{ mb_strtoupper($cargoDestinatario ?? 'SERVIDOR') }}</div>
                        <div>{{ $dataAssinatura }}</div>
                    @else
                        <div style="margin-top: 14px;"></div>
                        <div class="sign-line" style="width: 60%; margin: 0 auto 2px;">Setor (Destinatário)</div>
                        <div>Carimbo e Assinatura</div>
                        <div class="sign-box"></div>
                        <div style="text-align: right; margin-top: 5px;">Recebido em ____/____/______.</div>
                    @endif
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
                @if($assinaturaEletronica)
                    Documento assinado eletronicamente por <b>{{ mb_strtoupper($usuarioDestinatario ?? 'USUÁRIO') }}</b>, CPF: <b>{{ $cpfDestinatario ?: 'NÃO INFORMADO' }}</b>, {{ mb_strtoupper($cargoDestinatario ?? 'SERVIDOR') }}, em {{ $dataAssinatura }}, conforme art. 1º do Ato Normativo TJES Nº 75/2018. Código de Validação {{ $termo->num_termo }}/{{ $termo->ano_termo }}.
                @else
                    Documento gerado em {{ date('d/m/Y', strtotime($termo->date_time ?? now())) }}<br>
                    Os itens constantes neste termo foram devidamente conferidos e embarcados, sendo sua guarda de responsabilidade do transportador até a efetivação da entrega.
                @endif
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
