<?php

namespace App\Services;

use App\Models\Cotizacion;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class WhatsAppService
{
    public static function enviarCotizacion(Cotizacion $cotizacion): void
    {
        $telefono = preg_replace('/[^0-9]/', '', $cotizacion->numero_celular_cliente);
        if (!$telefono || strlen($telefono) < 10) {
            Log::warning("Número no válido para WhatsApp en cotización ID {$cotizacion->id}");
            return;
        }

        // Ruta original del PDF
        $originalPath = storage_path("app/public/cotizaciones/cotizacion-{$cotizacion->id}.pdf");
        if (!file_exists($originalPath)) {
            Log::error("Archivo PDF no encontrado para cotización ID {$cotizacion->id}");
            return;
        }

        // Crear copia temporal en public/tmp-cotizaciones/
        $random = Str::random(20);
        $tempFileName = "cotizacion-{$cotizacion->id}-{$random}.pdf";
        $tempPath = public_path("tmp-cotizaciones/{$tempFileName}");

        // Crear carpeta si no existe
        if (!File::exists(public_path("tmp-cotizaciones"))) {
            File::makeDirectory(public_path("tmp-cotizaciones"), 0755, true);
        }

        // Copiar el archivo temporalmente
        File::copy($originalPath, $tempPath);

        // URL pública temporal
        $publicUrl = env('WHATSAPP_PUBLIC_BASE_URL', 'https://cotizador.espumasmedellin.com') . "/tmp-cotizaciones/{$tempFileName}";

        // Payload WhatsApp
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => '57' . $telefono,
            'type' => 'template',
            'template' => [
                'name' => env('WHATSAPP_TEMPLATE_NAME', 'cotizacion'),
                'language' => ['code' => 'es_CO'],
                'components' => [
                    [
                        'type' => 'header',
                        'parameters' => [
                            [
                                'type' => 'document',
                                'document' => [
                                    'link' => $publicUrl,
                                    'filename' => "cotizacion-{$cotizacion->id}.pdf",
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $url = "https://graph.facebook.com/v22.0/" . env('WHATSAPP_PHONE_ID') . "/messages";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('WHATSAPP_TOKEN'),
            'Content-Type' => 'application/json',
        ])->post($url, $payload);

        if ($response->failed()) {
            Log::error('Error al enviar WhatsApp', [
                'cotizacion_id' => $cotizacion->id,
                'response' => $response->json(),
                'url_pdf' => $publicUrl,
            ]);
        }

        // Programar eliminación del archivo en 5 minutos
        self::eliminarTemporal($tempPath, 300); // 300 segundos
    }

    protected static function eliminarTemporal(string $filePath, int $delaySeconds = 300): void
    {
        // Ejecutar después de un retraso con un job o un sleep simple (si estás fuera de cola)
        dispatch(function () use ($filePath) {
            sleep(300); // 5 minutos
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        })->delay(now()->addSeconds($delaySeconds));
    }
}
