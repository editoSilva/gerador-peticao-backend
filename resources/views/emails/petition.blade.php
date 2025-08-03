<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Petição Gerada</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 40px 0;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
        <tr>
            <td style="padding: 30px; text-align: center; background-color: #001a49; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                <h2 style="color: #ffffff; margin: 0;">Petição Gerada com Sucesso Nº ({{ $number }})</h2>
            </td>
        </tr>
        <tr>
            <td style="padding: 30px; color: #333333;">
                <p style="font-size: 16px;">Olá, <b>{{ $name }}</b></p>

                <p style="font-size: 16px;">
                    Esperamos que você esteja bem. Conforme solicitado, estamos enviando em anexo a petição gerada com base nas informações fornecidas.
                </p>

                <p style="font-size: 16px;">
                    Caso haja qualquer dúvida ou necessidade de ajustes, fique à vontade para entrar em contato com nossa equipe jurídica.
                </p>

                <h3>Segue aqui o passo a passo do que fazer:</h3>

                {!! nl2br(e(limparMarkdown($description))) !!}
                {{-- {!! nl2br(e($description) !! } --}}

                <p style="font-size: 16px;">Atenciosamente,</p>
                <p style="font-size: 16px; font-weight: bold; color: #001a49;">Equipe Jurídica<br>Direito Cidadão</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 20px; text-align: center; background-color: #f0f0f0; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px; color: #777;">
                <small>Este é um e-mail automático. Por favor, não responda diretamente a esta mensagem.</small>
            </td>
        </tr>
    </table>
</body>
</html>