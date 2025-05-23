<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Código de Verificación</title>
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

        <p>
            Has sido registrado como referido. Para continuar con el proceso, por favor presenta el siguiente código de verificación cuando te acerques a la sala de ventas:
        </p>

        <h2>{{ $codigo }}</h2>

        <p>Este código es válido por <strong>30 días</strong>. Recuerda llevarlo contigo.</p>

        <p>Gracias,</p>

        <div class="footer">
            Este correo fue generado automáticamente. Por favor, no respondas a este mensaje.
        </div>
    </div>
</body>
</html>
