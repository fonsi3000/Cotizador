<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Referido - Paso 1</title>
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
            max-width: 500px;
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .form-title {
            text-align: center;
            background-color: #FF6B24;
            color: white;
            padding: 1rem;
            border-radius: 12px 12px 0 0;
            margin: -2rem -2rem 2rem -2rem;
            font-size: 1.2rem;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: none; /* evita que se ponga en mayúsculas */
        }

        .form-label {
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border-radius: 50px;
            padding: 0.5rem 1rem;
            height: 46px;
            border: 1px solid #ced4da;
            font-size: 0.95rem;
            margin-bottom: 1rem;
        }

        .error {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: -0.75rem;
            margin-bottom: 1rem;
        }

        .submit-button {
            background-color: #FF6B24;
            border: none;
            border-radius: 50px;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            padding: 0.75rem;
            width: 100%;
            font-size: 1rem;
        }

        .submit-button:hover {
            background-color: #e45e1f;
        }

        .footer-logos {
            text-align: center;
            margin-top: 1.5rem;
        }

        .footer-logos img {
            max-width: 80%;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-title">
            Refiera a una persona, recuerde que debe ser empleado para obtener este beneficio
        </div>

        <form method="POST" action="{{ route('referido.public.send-code') }}">
            @csrf

            <label for="correo_referidor" class="form-label">Correo corporativo del referidor:</label>
            <input type="email" name="correo_referidor" id="correo_referidor" class="form-control" value="{{ old('correo_referidor') }}" required>

            @error('correo_referidor')
                <div class="error">{{ $message }}</div>
            @enderror

            <button type="submit" class="submit-button">Enviar código</button>
        </form>

        <div class="footer-logos">
            <img src="{{ asset('images/logo.png') }}" alt="Logos corporativos">
        </div>
    </div>
</body>
</html>
