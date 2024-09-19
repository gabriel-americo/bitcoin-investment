<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmação de Depósito</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background-color: #f0f0f0;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #007BFF;
            margin-bottom: 20px;
            text-align: center;
        }

        p {
            font-size: 16px;
            line-height: 1.3;
            color: #555;
        }

        .highlight {
            font-weight: bold;
            color: #000;
        }

        .footer {
            margin-top: 30px;
            font-size: 14px;
            color: #888;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Confirmação de Depósito</h2>
        <p>Olá, <span class="highlight">{{ $user->name }}</span>,</p>
        <p>Este e-mail é para confirmar que o valor de <span class="highlight">R$ {{ $value }}</span> foi
            depositado com sucesso na sua conta no dia <span class="highlight">{{ date('d/m/Y') }}</span>.</p>
        <p>Se você tiver qualquer dúvida ou precisar de mais informações, por favor, entre em contato conosco.</p>
        <p>Atenciosamente,</p>
        <p><span class="highlight">Equipe Seu Projeto</span></p>

        <div class="footer">
            <p>Este é um e-mail automático, por favor, não responda.</p>
        </div>
    </div>
</body>

</html>
