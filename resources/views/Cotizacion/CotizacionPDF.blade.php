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

        .productos th {
            background-color: #f0f0f0;
        }

        .totales {
            text-align: right;
        }

        .totales p strong {
            display: inline-block;
            width: 140px;
        }

        .footer {
            font-size: 10px;
            color: #555;
            text-align: justify;
        }
    </style>
</head>
<body>
    <div class="container">
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

        <div class="header">
            <img src="{{ public_path('images/logo.png') }}" alt="Logo">
            <div class="titulo">{{ $empresaNombre }}</div>
            <div>N.I.T: {{ $empresaNit }}</div>

            @if ($cotizacion->salaVenta)
                <div><strong>{{ strtoupper($cotizacion->salaVenta->nombre) }}</strong></div>
                <div>{{ $cotizacion->salaVenta->direccion }}</div>
                <div>Tel: {{ $cotizacion->salaVenta->telefono }}</div>
            @else
                <div>Dirección: {{ $direccionEmpresa }}</div>
                <div>Tel: {{ $empresa === 'Espumados del Litoral S.A' ? 'N/A' : '4441423 Ext:4021' }}</div>
            @endif
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
                        <th>IVA (19%)</th>
                        <th>Total x Unidad</th>
                        <th>Cantidad</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cotizacion->items as $index => $item)
                        @php
                            $ivaUnidad = round($item->precio_unitario * 0.19, 2);
                            $totalUnidad = $item->precio_unitario + $ivaUnidad;
                            $totalProducto = $totalUnidad * $item->cantidad;
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->producto->codigo ?? 'N/A' }}</td>
                            <td>{{ $item->producto->descripcion ?? 'N/A' }}</td>
                            <td>${{ number_format($item->precio_unitario, 0, ',', '.') }}</td>
                            <td>${{ number_format($ivaUnidad, 0, ',', '.') }}</td>
                            <td>${{ number_format($totalUnidad, 0, ',', '.') }}</td>
                            <td>{{ $item->cantidad }}</td>
                            <td>${{ number_format($totalProducto, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @php
            $subtotal = $cotizacion->items->sum(fn($item) => $item->precio_unitario * $item->cantidad);
            $iva = round($subtotal * 0.19, 2);
            $total = $subtotal + $iva;
        @endphp

        <div class="totales">
            <p><strong>Subtotal:</strong> ${{ number_format($subtotal, 0, ',', '.') }}</p>
            <p><strong>IVA (19%):</strong> ${{ number_format($iva, 0, ',', '.') }}</p>
            <p><strong>TOTAL:</strong> ${{ number_format($total, 0, ',', '.') }}</p>
        </div>

        <div class="footer">
            <p><strong>DOCUMENTO NO VÁLIDO COMO RECIBO DE CAJA.</strong> Este no es un documento comercial. Exija el recibo de caja o factura original para efectos de reclamo.</p>
            <p><strong>PROTECCIÓN DE DATOS PERSONALES:</strong> Según la Ley 1581 de 2012 y el Decreto 1377 de 2013, la información suministrada será tratada por {{ $empresaNombre }} para fines administrativos, comerciales y de fidelización. Consulte nuestra política en <strong>{{ $paginaWeb }}</strong>.</p>
            <p>Puede ejercer sus derechos contactando a <strong>{{ $correoContacto }}</strong> o en la dirección <strong>{{ $direccionSala }}</strong>.</p>
        </div>
    </div>
</body>
</html>
