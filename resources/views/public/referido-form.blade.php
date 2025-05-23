<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Referido</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('{{ asset("images/fondo.png") }}');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-family: 'Helvetica', 'Arial', sans-serif;
        }

        .form-container {
            width: 100%;
            max-width: 750px;
            transform: scale(0.98);
        }

        .card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            background-color: white;
            overflow: hidden;
        }

        .card-header {
            background-color: #FF6B24;
            color: white;
            padding: 1.2rem 1.5rem;
            text-align: center;
            border: none;
        }

        .card-header h3 {
            font-size: 1.8rem;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .card-body {
            padding: 1.5rem;
        }

        .section-title {
            font-weight: bold;
            font-size: 1.1rem;
            margin-bottom: 1rem;
            color: #212529;
        }

        .form-control {
            border-radius: 50px;
            padding: 0.5rem 1rem;
            height: 46px;
            border: 1px solid #ced4da;
            font-size: 0.95rem;
        }

        .form-control::placeholder {
            color: #6c757d;
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
            margin-top: 0.5rem;
        }

        .submit-button:hover {
            background-color: #e45e1f;
        }

        .divider {
            height: 1px;
            background-color: #e9ecef;
            margin: 1rem 0;
            width: 100%;
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
        <div class="card">
            <div class="card-header">
                <h3>Registro de Referido</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('referido.public.store', ['id' => $referido->id]) }}">
                    @csrf

                    @if ($errors->any())
                        <div class="alert alert-danger mb-3">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Datos del referidor -->
                    <div class="form-section">
                        <h5 class="section-title">Datos de quien refiere</h5>
                        <div class="mb-3">
                            <input type="text" class="form-control" name="nombre_referidor" placeholder="Nombre completo" required>
                        </div>
                        <div class="mb-3">
                            <input type="text" class="form-control" name="documento_referidor" placeholder="Documento" required>
                        </div>
                    </div>

                    <div class="divider"></div>

                    <!-- Datos del referido -->
                    <div class="form-section">
                        <h5 class="section-title">Datos del referido</h5>
                        <div class="mb-3">
                            <input type="text" class="form-control" name="nombre_referido" placeholder="Nombre del referido" required>
                        </div>
                        <div class="mb-3">
                            <input type="text" class="form-control" name="documento_referido" placeholder="Documento del referido" required>
                        </div>
                        <div class="mb-3">
                            <input type="email" class="form-control" name="correo_referido" placeholder="Correo del referido" required>
                        </div>
                    </div>

                    <button type="submit" class="submit-button">Enviar Referido</button>
                </form>

                <div class="footer-logos">
                    <img src="{{ asset('images/logo.png') }}" alt="Logos corporativos">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
