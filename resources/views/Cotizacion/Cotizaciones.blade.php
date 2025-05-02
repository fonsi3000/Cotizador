{{-- resources/views/Cotizacion/Cotizaciones.blade.php --}}
<div class="bg-white w-full rounded shadow-sm print:shadow-none text-sm text-gray-800 leading-relaxed">

    <div class="flex items-center justify-between w-full border-b pb-4 mb-6 px-4">
        {{-- Bloque 1: Logo Espumas Medellín --}}
        <div class="flex items-center justify-start space-x-2">
            <img src="{{ asset('images/logo.png') }}" alt="Espumas Medellín" style="height: 70px; width: auto;">

        </div>
    
        {{-- Bloque 2: Título centrado --}}
        <div class="text-center">
            <h1 class="text-3xl font-bold text-gray-900">COTIZACIÓN #{{ $cotizacion->id }}</h1>
        </div>
    
        {{-- Bloque 3: Logo Espumados del Litoral + Fechas --}}
        <div class="flex flex-col items-end text-right space-y-1">
            <p class="font-semibold text-sm">Fecha: {{ $cotizacion->created_at->format('d/m/Y') }}</p>
            <p class="font-semibold text-sm">Validez: {{ $cotizacion->created_at->addDays(30)->format('d/m/Y') }}</p>
        </div>
    </div>

    {{-- Información del cliente --}}
    <div class="mb-6 w-full px-4">
        <h2 class="text-lg font-bold text-gray-700 mb-2 border-b pb-1">INFORMACIÓN DEL CLIENTE</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full">
            <div class="space-y-1">
                <p><span class="font-semibold">Nombre:</span> {{ $cotizacion->nombre_cliente }}</p>
                <p><span class="font-semibold">Documento:</span> {{ $cotizacion->documento_cliente }}</p>
                <p><span class="font-semibold">Asesor:</span> {{ $cotizacion->usuario->name ?? 'No asignado' }}</p>
            </div>
            <div class="space-y-1">
                <p><span class="font-semibold">Teléfono:</span> {{ $cotizacion->numero_celular_cliente }}</p>
                <p><span class="font-semibold">Email:</span> {{ $cotizacion->correo_electronico_cliente ?? 'No especificado' }}</p>
            </div>
        </div>
    </div>

    {{-- Tabla de productos --}}
    <div class="mb-8 overflow-x-auto px-4 w-full">
        <h2 class="text-lg font-bold text-gray-700 mb-2 border-b pb-1">PRODUCTOS COTIZADOS</h2>
        <table class="table-auto w-full border border-gray-300 text-sm">
            <thead>
                <tr class="bg-gray-100 text-gray-800">
                    <th class="py-2 px-4 border-b text-left">#</th>
                    <th class="py-2 px-4 border-b text-left">Código</th>
                    <th class="py-2 px-4 border-b text-left">Descripción</th>
                    <th class="py-2 px-4 border-b text-right">Lista de Precio</th>
                    <th class="py-2 px-4 border-b text-right">Precio Unitario</th>
                    <th class="py-2 px-4 border-b text-center">Cantidad</th>
                    <th class="py-2 px-4 border-b text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @php $counter = 1; @endphp
                @foreach($cotizacion->items as $item)
                <tr class="{{ $counter % 2 == 0 ? 'bg-gray-50' : '' }}">
                    <td class="py-2 px-4 border-b">{{ $counter++ }}</td>
                    <td class="py-2 px-4 border-b">{{ $item->producto->codigo ?? 'N/A' }}</td>
                    <td class="py-2 px-4 border-b">{{ $item->producto->descripcion ?? 'Producto no disponible' }}</td>
                    <td class="py-2 px-4 border-b text-right">{{ $item->listaPrecio->nombre ?? 'N/A' }}</td>
                    <td class="py-2 px-4 border-b text-right">{{ number_format($item->precio_unitario, 2, ',', '.') }}</td>
                    <td class="py-2 px-4 border-b text-center">{{ $item->cantidad }}</td>
                    <td class="py-2 px-4 border-b text-right">{{ number_format($item->subtotal, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                @php
                $subtotal = $cotizacion->items->sum('subtotal');
                $iva = round($subtotal * 0.19, 2);
                $total = $subtotal + $iva;
                @endphp
                <tfoot>
                    <tr class="bg-gray-100 font-bold">
                        <td colspan="6" class="py-2 px-4 border-b text-right">SUBTOTAL:</td>
                        <td class="py-2 px-4 border-b text-right">{{ number_format($subtotal, 2, ',', '.') }}</td>
                    </tr>
                    <tr class="bg-gray-100 font-bold">
                        <td colspan="6" class="py-2 px-4 border-b text-right">IVA (19%):</td>
                        <td class="py-2 px-4 border-b text-right">{{ number_format($iva, 2, ',', '.') }}</td>
                    </tr>
                    <tr class="bg-gray-100 font-bold text-base">
                        <td colspan="6" class="py-2 px-4 border-b text-right">TOTAL:</td>
                        <td class="py-2 px-4 border-b text-right">{{ number_format($total, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
                
            </tfoot>
        </table>
    </div>
</div>
