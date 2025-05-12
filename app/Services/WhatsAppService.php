<?php

namespace App\Services;

use App\Models\Cotizacion;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class WhatsAppService
{
    public static function enviarCotizacion(Cotizacion $cotizacion): void
    {
        // Validar número de celular
        $telefono = preg_replace('/[^0-9]/', '', $cotizacion->numero_celular_cliente);
        if (!$telefono || strlen($telefono) < 10) {
            Log::warning("Número no válido para WhatsApp en cotización ID {$cotizacion->id}");
            return;
        }

        // Generar PDF y guardarlo en el disco público (si no existe ya)
        $pdfPath = "cotizaciones/cotizacion-{$cotizacion->id}.pdf";
        if (!Storage::disk('public')->exists($pdfPath)) {
            $pdf = Pdf::loadView('Cotizacion.CotizacionPDF', [
                'cotizacion' => $cotizacion->load(['items.producto', 'items.listaPrecio', 'usuario']),
                'isPdfDownload' => true,
            ])->output();

            Storage::disk('public')->put($pdfPath, $pdf);
        }

        // Obtener la URL pública del archivo servida por Laravel (no por Nginx)
        $publicUrl = route('cotizacion.pdf', ['cotizacion' => $cotizacion->id]);

        // Construcción del payload con header tipo DOCUMENT
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => '57' . $telefono,
            'type' => 'template',
            'template' => [
                'name' => env('WHATSAPP_TEMPLATE_NAME', 'cotizacion'),
                'language' => [
                    'code' => 'es_CO',
                ],
                'components' => [
                    [
                        'type' => 'header',
                        'parameters' => [
                            [
                                'type' => 'document',
                                'document' => [
                                    'link' => $publicUrl,
                                    'filename' => "cotizacion-{$cotizacion->id}.pdf"
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ];

        // Enviar la solicitud a la API de WhatsApp
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('WHATSAPP_TOKEN'),
            'Content-Type' => 'application/json',
        ])->post("https://graph.facebook.com/v22.0/" . env('WHATSAPP_PHONE_ID') . "/messages", $payload);

        // Log de errores si falla el envío
        if ($response->failed()) {
            Log::error('Error al enviar WhatsApp', [
                'response' => $response->json(),
                'cotizacion_id' => $cotizacion->id,
            ]);
        }
    }
}
