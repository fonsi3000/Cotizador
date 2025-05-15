<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>.</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            width: 80mm;
            margin: 0;
            padding: 0;
        }

        .tirilla {
            padding: 10px;
        }

        .center {
            text-align: center;
        }

        .titulo {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .subtitulo {
            font-weight: bold;
            margin-bottom: 2px;
        }

        .linea {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .info p,
        .productos p,
        .totales p {
            margin: 2px 0;
        }

        .productos .item {
            margin-bottom: 4px;
        }

        .totales {
            margin-top: 8px;
        }

        .footer {
            text-align: justify;
            font-size: 10px;
            margin-top: 12px;
        }

        .qr {
            text-align: center;
            margin-top: 10px;
        }

        .logo {
            height: 40px;
            margin: 0 auto 4px;
            display: block;
        }

        .text-center {
            text-align: center;
        }

        @media print {
            body {
                margin: 0;
            }
        }
    </style>
</head>
<body onload="window.print(); setTimeout(() => window.close(), 100);">
    <div class="tirilla">
        <div class="center">
            <img src="{{ asset('images/logo.png') }}" class="logo" alt="Logo">
            @php
                $empresa = $cotizacion->usuario->empresa ?? 'Espumas Medellin S.A';
                $empresaNombre = $empresa === 'Espumados del Litoral S.A'
                    ? 'ESPUMADOS DEL LITORAL S.A'
                    : 'ESPUMAS MEDELLÍN S.A';

                $empresaNit = $empresa === 'Espumados del Litoral S.A'
                    ? '800177119-1'
                    : '890921665-9';

                $paginaWeb = $empresa === 'Espumados del Litoral S.A'
                    ? 'www.espumadosdellitoral.com.co'
                    : 'www.espumasmedellin.com';

                $correoContacto = $empresa === 'Espumados del Litoral S.A'
                    ? 'lider.servicioalcliente@espumadosdellitoral.com.co'
                    : 'experienciacliente@espumasmedellin.com.co';

                $direccionEmpresa = $empresa === 'Espumados del Litoral S.A'
                    ? 'Calle 110 # 9G-520 AV Circunvalar, Barranquilla'
                    : 'CR 48 98 SUR 05 LA ESTRELLA, ANTIOQUIA';

                $direccionSala = $cotizacion->salaVenta->direccion ?? $direccionEmpresa;
            @endphp
            <div class="titulo">{{ $empresaNombre }}</div>
            <div>N.I.T: {{ $empresaNit }}</div>

            @if ($cotizacion->salaVenta)
                <div><strong>{{ strtoupper($cotizacion->salaVenta->nombre) }}</strong></div>
                <div>{{ $cotizacion->salaVenta->direccion }}</div>
                <div>Tel: {{ $cotizacion->salaVenta->telefono }}</div>
            @endif
        </div>

        <div class="linea"></div>

        <div class="info">
            <p><strong>Cotización:</strong> {{ $cotizacion->id }}</p>
            <p><strong>Fecha de creación:</strong> {{ $cotizacion->created_at->format('d/m/Y') }}</p>
            <p><strong>Válida hasta:</strong> {{ $cotizacion->created_at->addDays(30)->format('d/m/Y') }}</p>
            <p><strong>Asesor:</strong> {{ $cotizacion->usuario->name ?? 'No asignado' }}</p>
            <p><strong>Teléfono:</strong> {{ $cotizacion->usuario->numero_telefono ?? 'N/A' }}</p>
        </div>

        <div class="linea"></div>

        <div class="info">
            <p><strong>Cliente:</strong> {{ $cotizacion->documento_cliente }} – {{ $cotizacion->nombre_cliente }}</p>
            <p><strong>Correo:</strong> {{ $cotizacion->correo_electronico_cliente ?? 'No especificado' }}</p>
            <p><strong>Teléfono:</strong> {{ $cotizacion->numero_celular_cliente }}</p>
        </div>

        <div class="linea"></div>

        <div class="productos">
            <div class="subtitulo">PRODUCTOS</div>
            @foreach ($cotizacion->items as $item)
                <div class="item">
                    <p>{{ $item->producto->descripcion ?? 'Producto' }}</p>
                    <p>${{ number_format($item->precio_unitario, 0, ',', '.') }} x {{ $item->cantidad }} = ${{ number_format($item->subtotal, 0, ',', '.') }}</p>
                </div>
            @endforeach
        </div>

        <div class="linea"></div>

        @php
            $subtotal = $cotizacion->items->sum('subtotal');
            $iva = round($subtotal * 0.19, 2);
            $total = $subtotal + $iva;
        @endphp

        <div class="totales">
            <p><strong>Subtotal:</strong> ${{ number_format($subtotal, 0, ',', '.') }}</p>
            <p><strong>IVA (19%):</strong> ${{ number_format($iva, 0, ',', '.') }}</p>
            <p><strong>TOTAL:</strong> ${{ number_format($total, 0, ',', '.') }}</p>
        </div>

        <div class="linea"></div>

        <div class="footer">
            <p class="text-center"><strong>DOCUMENTO NO VÁLIDO COMO RECIBO DE CAJA, ESTE NO ES UN DOCUMENTO COMERCIAL. EXIJA EL RECIBO DE CAJA O FACTURA ORIGINAL PARA EFECTOS DE RECLAMO.</strong></p>
            <p><strong>PROTECCIÓN DE DATOS PERSONALES:</strong> De acuerdo con la Ley Estatutaria 1581 de 2012 de protección de datos y con el Decreto 1377 de 2013, la información suministrada por usted para la realización de este documento será incorporada en una base de datos responsabilidad de {{ $empresaNombre }}, para su tratamiento y la transferencia de datos a terceros. Siendo tratados con la finalidad de: gestión de clientes, gestión administrativa, prospección comercial, fidelización de clientes, mercadeo, publicidad propia, el envío de comunicaciones comerciales sobre nuestros productos y campañas de actualización de datos e información de cambios en el tratamiento de datos personales. La política de tratamiento de datos se podrá consultar en la página {{ $paginaWeb }}.</p>
            <p>Usted puede ejercer su derecho de acceso, corrección, suspensión, revocación o reclamo por infracción sobre sus datos con un correo electrónico a <strong>{{ $correoContacto }}</strong> o por medio físico enviado a la dirección <strong>{{ $direccionSala }}</strong>.</p>
            <p class="text-center">Para más información visite nuestra página web: <strong>{{ $paginaWeb }}</strong></p>
        </div>

        <div class="qr">
            <img src="{{ asset('images/qr-medellin.png') }}" alt="QR Espumas Medellín" width="100">
        </div>

        <div style="height: 20px;"></div>
    </div>
</body>
</html>
