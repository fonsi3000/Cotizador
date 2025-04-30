{{-- resources/views/Cotizacion/CotizacionTirilla.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cotización Tirilla</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            width: 80mm;
        }
        .seccion { margin-bottom: 8px; }
        .titulo { font-weight: bold; text-align: center; margin-bottom: 6px; }
        .linea { border-bottom: 1px dashed #000; margin: 4px 0; }
    </style>
</head>
<body onload="window.print();">
    <div class="titulo">COMODISIMOS</div>
    <div class="seccion">
        <div>COTIZACIÓN: {{ $cotizacion->id }}</div>
        <div>Fecha: {{ $cotizacion->created_at->format('Y-m-d H:i') }}</div>
    </div>
    <div class="seccion">
        <div><strong>Cliente:</strong> {{ $cotizacion->nombre_cliente }}</div>
        <div><strong>Doc:</strong> {{ $cotizacion->documento_cliente }}</div>
        <div><strong>Tel:</strong> {{ $cotizacion->numero_celular_cliente }}</div>
    </div>
    <div class="linea"></div>
    @foreach ($cotizacion->items as $item)
        <div>
            {{ $item->producto->descripcion }}<br>
            ${{ number_format($item->precio_unitario, 0, ',', '.') }} x {{ $item->cantidad }} = ${{ number_format($item->subtotal, 0, ',', '.') }}
        </div>
        <div class="linea"></div>
    @endforeach
    <div class="seccion">
        <strong>Total: </strong> ${{ number_format($cotizacion->total_cotizacion, 0, ',', '.') }}
    </div>
</body>
</html>
