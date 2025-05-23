<div class="space-y-4">
    <h2 class="text-lg font-semibold">Referidor</h2>
    <ul>
        <li><strong>Nombre:</strong> {{ $referido->nombre_referidor }}</li>
        <li><strong>Documento:</strong> {{ $referido->documento_referidor }}</li>
        <li><strong>Correo:</strong> {{ $referido->correo_referidor }}</li>
    </ul>

    <h2 class="text-lg font-semibold">Referido</h2>
    <ul>
        <li><strong>Nombre:</strong> {{ $referido->nombre_referido }}</li>
        <li><strong>Documento:</strong> {{ $referido->documento_referido }}</li>
        <li><strong>Correo:</strong> {{ $referido->correo_referido }}</li>
    </ul>

    <h2 class="text-lg font-semibold">Proceso</h2>
    <ul>
        <li><strong>Estado:</strong> {{ ucfirst($referido->estado) }}</li>
        <li><strong>Vigencia:</strong> {{ $referido->vigencia?->format('d/m/Y') }}</li>
        <li><strong>Código de venta:</strong> {{ $referido->codigo_venta }}</li>
        <li><strong>Sala de ventas:</strong> {{ $referido->salaVenta?->nombre }}</li>
        <li><strong>Modificado por:</strong> {{ $referido->modificadoPor?->name }}</li>
    </ul>
</div>
