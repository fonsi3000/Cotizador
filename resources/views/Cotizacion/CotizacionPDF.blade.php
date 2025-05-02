<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotización #{{ $cotizacion->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 700px;
            margin: 0 auto;
            background-color: #fff;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #ccc;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header img {
            height: 60px;
        }

        .header .titulo {
            font-size: 20px;
            font-weight: bold;
            margin-top: 10px;
        }

        .info, .cliente, .productos, .totales, .footer {
            margin-bottom: 20px;
        }

        .info p, .cliente p, .totales p {
            margin: 4px 0;
        }

        .productos table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        .productos th, .productos td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: left;
        }

        .productos img {
            height: 50px;
        }

        .productos th {
            background-color: #f0f0f0;
        }

        .totales {
            text-align: right;
        }

        .totales p strong {
            display: inline-block;
            width: 120px;
        }

        .footer {
            font-size: 10px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ public_path('images/logo.png') }}" alt="Espumas Medellín">
            <div class="titulo">ESPUMAS MEDELLÍN S.A</div>
            <div>N.I.T: 890921665-9</div>
            <div>Dirección: CARRERA 48 98 SUR 05 LA ESTRELLA</div>
            <div>Tel: 4441423 Ext:4021</div>
        </div>

        <div class="info">
            <p><strong>Cotización:</strong> {{ $cotizacion->id }}</p>
            <p><strong>Fecha:</strong> {{ $cotizacion->created_at->format('d/m/Y') }}</p>
            <p><strong>Válida hasta:</strong> {{ $cotizacion->created_at->addDays(30)->format('d/m/Y') }}</p>
            <p><strong>Asesor:</strong> {{ $cotizacion->usuario->name ?? 'No asignado' }}</p>
            <p><strong>Teléfono:</strong> {{ $cotizacion->usuario->numero_telefono ?? 'N/A' }}</p>
        </div>

        <div class="cliente">
            <p><strong>Cliente:</strong> {{ $cotizacion->documento_cliente }} – {{ $cotizacion->nombre_cliente }}</p>
            <p><strong>Correo:</strong> {{ $cotizacion->correo_electronico_cliente ?? 'No especificado' }}</p>
            <p><strong>Teléfono:</strong> {{ $cotizacion->numero_celular_cliente }}</p>
        </div>

        <div class="productos">
            <h4>PRODUCTOS COTIZADOS</h4>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th>Precio Unitario</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cotizacion->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->producto->codigo ?? 'N/A' }}</td>
                            <td>{{ $item->producto->descripcion ?? 'N/A' }}</td>
                            <td>${{ number_format($item->precio_unitario, 0, ',', '.') }}</td>
                            <td>{{ $item->cantidad }}</td>
                            <td>${{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @php
            $subtotal = $cotizacion->items->sum('subtotal');
            $iva = round($subtotal * 0.19, 2);
            $total = $subtotal + $iva;
        @endphp

        <div class="totales">
            <p><strong>Subtotal: ${{ number_format($subtotal, 0, ',', '.') }}</p></strong>
            <p><strong>IVA (19%): ${{ number_format($iva, 0, ',', '.') }}</p></strong>
            <p><strong>TOTAL: ${{ number_format($total, 0, ',', '.') }}</p></strong>
        </div>

        <div class="footer">
            <p><strong>DOCUMENTO NO VÁLIDO COMO RECIBO DE CAJA.</strong> Este no es un documento comercial. Exija el recibo de caja o factura original para efectos de reclamo.</p>
            <p><strong>PROTECCIÓN DE DATOS PERSONALES:</strong> De acuerdo con la Ley Estatutaria 1581 de 2012 de protección de datos y con el Decreto 1377 de 2013, la información suministrada por usted para la realización de este documento será incorporada en una base de datos responsabilidad de ESPUMAS MEDELLÍN S.A, para su tratamiento y la transferencia de datos a terceros. Siendo tratados con la finalidad de: gestión de clientes, gestión administrativa, prospección comercial, fidelización de clientes, mercadeo, publicidad propia, el envío de comunicaciones comerciales sobre nuestros productos y campañas de actualización de datos e información de cambios en el tratamiento de datos personales. La política de tratamiento de datos se podrá consultar en la página www.espumasmedellin.com.</p>
            <p>Usted puede ejercer su derecho de acceso, corrección, suspensión, revocación o reclamo por infracción sobre sus datos con un correo electrónico a <strong>experienciacliente@espumasmedellin.com.co</strong> o por medio físico enviado a la dirección <strong>CR 48 98 SUR 05 LA ESTRELLA, ANTIOQUIA</strong>.</p>
        </div>
    </div>
</body>
</html>
