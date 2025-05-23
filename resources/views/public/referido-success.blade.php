<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Referido registrado</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('{{ asset("images/fondo.png") }}');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Helvetica', 'Arial', sans-serif;
        }

        .form-container {
            width: 100%;
            max-width: 600px;
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            text-align: center;
        }

        h1 {
            color: #FF6B24;
            font-size: 1.75rem;
            margin-bottom: 1rem;
        }

        p {
            font-size: 1rem;
            color: #333;
            margin-bottom: 1.5rem;
        }

        .alert-info {
            background-color: #e9f7ff;
            border-left: 4px solid #0dcaf0;
            padding: 1rem;
            border-radius: 8px;
            font-size: 0.95rem;
            color: #055160;
            margin-bottom: 1.5rem;
        }

        .action-button {
            background-color: #FF6B24;
            color: white;
            border: none;
            border-radius: 50px;
            font-weight: bold;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            text-transform: uppercase;
            text-decoration: none;
            display: inline-block;
        }

        .action-button:hover {
            background-color: #e45e1f;
        }

        .footer-logos {
            margin-top: 2rem;
        }

        .footer-logos img {
            max-width: 70%;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>¡Gracias por registrar el referido!</h1>
        <p>
            Hemos enviado un código de verificación al correo del referido.  
            <strong>Es importante que el referido lleve ese código al momento de acercarse a la sala de ventas.</strong>
        </p>

        <div class="alert-info">
            Recuerda que solo puedes tener <strong>10 referidos activos</strong> al mismo tiempo.
        </div>

        <a href="{{ route('referido.public.email-form') }}" class="action-button">Referir otra persona</a>

        <div class="footer-logos">
            <img src="{{ asset('images/logo.png') }}" alt="Logo corporativo">
        </div>
    </div>
</body>
</html>
