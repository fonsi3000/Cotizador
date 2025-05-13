<?php

namespace App\Services;

use App\Models\Cotizacion;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public static function enviarCotizacion(Cotizacion $cotizacion): void
    {
        // Limpiar el número de celular y validar longitud
        $telefono = preg_replace('/[^0-9]/', '', $cotizacion->numero_celular_cliente);
        if (!$telefono || strlen($telefono) < 10) {
            Log::warning("Número no válido para WhatsApp en cotización ID {$cotizacion->id}");
            return;
        }

        // Construir la URL pública del archivo PDF (ajusta el dominio en producción)
        $archivo = "cotizacion-{$cotizacion->id}.pdf";
        $publicUrl = env('WHATSAPP_PUBLIC_BASE_URL', 'https://cotizador.espumasmedellin.com') . "/storage/cotizaciones/{$archivo}";

        // Armar el payload para la plantilla con header tipo DOCUMENT
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
                                    'filename' => $archivo,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        // Enviar la solicitud a la API de WhatsApp Cloud
        $url = "https://graph.facebook.com/v22.0/" . env('WHATSAPP_PHONE_ID') . "/messages";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('WHATSAPP_TOKEN'),
            'Content-Type' => 'application/json',
        ])->post($url, $payload);

        // Verificar si hubo error
        if ($response->failed()) {
            Log::error('Error al enviar WhatsApp', [
                'cotizacion_id' => $cotizacion->id,
                'response' => $response->json(),
                'url_pdf' => $publicUrl,
            ]);
        }
    }
}
