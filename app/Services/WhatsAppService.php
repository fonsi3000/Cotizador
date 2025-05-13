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

        // Verificar archivo original
        $originalPath = storage_path("app/public/cotizaciones/cotizacion-{$cotizacion->id}.pdf");
        if (!file_exists($originalPath)) {
            Log::error("Archivo PDF no encontrado para cotización ID {$cotizacion->id}");
            return;
        }

        // Crear archivo temporal
        $random = Str::random(20);
        $tempFileName = "cotizacion-{$cotizacion->id}-{$random}.pdf";
        $tempPath = public_path("tmp-cotizaciones/{$tempFileName}");

        // Crear carpeta si no existe
        if (!File::exists(public_path("tmp-cotizaciones"))) {
            File::makeDirectory(public_path("tmp-cotizaciones"), 0755, true);
            Log::info('Carpeta tmp-cotizaciones creada');
        }

        // Copiar archivo temporalmente
        File::copy($originalPath, $tempPath);
        Log::info("Archivo temporal creado: {$tempPath}");

        // Construir URL pública
        $publicUrl = config('services.whatsapp.public_url') . "/tmp-cotizaciones/{$tempFileName}";

        // Construir payload
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => '57' . $telefono,
            'type' => 'template',
            'template' => [
                'name' => config('services.whatsapp.template'),
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

        $url = "https://graph.facebook.com/v22.0/" . config('services.whatsapp.phone_id') . "/messages";
        $token = config('services.whatsapp.token');

        // Logs de depuración
        Log::info('WhatsApp Phone ID:', [config('services.whatsapp.phone_id')]);
        Log::info('WhatsApp URL:', [$url]);
        Log::info('Payload WhatsApp:', $payload);
        Log::info('Token parcial:', [substr($token, 0, 20) . '...']);

        // Enviar solicitud a la API
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ])->post($url, $payload);

        if ($response->failed()) {
            Log::error('Error al enviar WhatsApp', [
                'cotizacion_id' => $cotizacion->id,
                'response' => $response->json(),
                'url_pdf' => $publicUrl,
            ]);
        } else {
            Log::info("WhatsApp enviado correctamente a 57{$telefono}");
        }

        // Eliminar archivo después de 5 minutos
        self::eliminarTemporal($tempPath, 300);
    }

    protected static function eliminarTemporal(string $filePath, int $delaySeconds = 300): void
    {
        dispatch(function () use ($filePath) {
            sleep(300);
            if (file_exists($filePath)) {
                unlink($filePath);
                Log::info("Archivo temporal eliminado: {$filePath}");
            }
        })->delay(now()->addSeconds($delaySeconds));
    }
}
