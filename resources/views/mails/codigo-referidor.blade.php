<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificación de Correo</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            background-color: #f4f4f4;
            padding: 2rem;
            color: #333;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        h2 {
            font-size: 28px;
            color: #FF6B24;
            font-weight: bold;
            text-align: center;
            margin: 2rem 0;
        }

        p {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .footer {
            font-size: 14px;
            color: #777;
            margin-top: 2rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <p>Hola,</p>

        <p>Has iniciado el proceso para registrar un referido.</p>
        <p>Tu código de verificación es:</p>

        <h2>{{ $codigo }}</h2>

        <p>Este código es necesario para continuar con el registro del referido.</p>

        <p>Gracias,</p>

        <div class="footer">
            Este mensaje fue enviado automáticamente. No respondas a este correo.
        </div>
    </div>
</body>
</html>
