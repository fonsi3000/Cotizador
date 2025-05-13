<?php

namespace App\Services;

use App\Models\Cotizacion;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Barryvdh\DomPDF\Facade\Pdf;

class WhatsAppService
{
    public static function enviarCotizacion(Cotizacion $cotizacion): void
    {
        $telefono = preg_replace('/[^0-9]/', '', $cotizacion->numero_celular_cliente);
        if (!$telefono || strlen($telefono) < 10) {
            Log::warning("Número no válido para WhatsApp en cotización ID {$cotizacion->id}");
            return;
        }

        // Ruta del archivo PDF original (siempre se sobrescribe)
        $originalPath = storage_path("app/public/cotizaciones/cotizacion-{$cotizacion->id}.pdf");

        Log::info("Generando PDF para cotización ID {$cotizacion->id}");

        try {
            $cotizacion->load('items.producto');
            $pdf = Pdf::loadView('Cotizacion.CotizacionPDF', compact('cotizacion'));
            $pdf->save($originalPath);
            Log::info("PDF generado exitosamente para cotización ID {$cotizacion->id}");
        } catch (\Throwable $e) {
            Log::error("Error generando PDF para cotización ID {$cotizacion->id}", [
                'error' => $e->getMessage()
            ]);
            return;
        }

        // Crear nombre y ruta del archivo temporal
        $random = Str::random(20);
        $tempFileName = "cotizacion-{$cotizacion->id}-{$random}.pdf";
        $tempPath = public_path("tmp-cotizaciones/{$tempFileName}");

        // Crear carpeta si no existe
        if (!File::exists(public_path("tmp-cotizaciones"))) {
            File::makeDirectory(public_path("tmp-cotizaciones"), 0755, true);
            Log::info('Carpeta tmp-cotizaciones creada');
        }

        // Copiar el archivo al directorio temporal
        File::copy($originalPath, $tempPath);
        Log::info("Archivo temporal creado: {$tempPath}");

        // URL pública temporal para WhatsApp
        $publicUrl = config('services.whatsapp.public_url') . "/tmp-cotizaciones/{$tempFileName}";

        // Payload para plantilla WhatsApp
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

        // URL de la API de WhatsApp con versión 22.2
        $url = "https://graph.facebook.com/v22.2/" . config('services.whatsapp.phone_id') . "/messages";
        $token = config('services.whatsapp.token');

        // Logs de depuración
        Log::info('WhatsApp Phone ID:', [config('services.whatsapp.phone_id')]);
        Log::info('WhatsApp URL:', [$url]);
        Log::info('Payload WhatsApp:', $payload);
        Log::info('Token parcial:', [substr($token, 0, 20) . '...']);

        // Enviar la solicitud
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

        // Eliminar el archivo temporal después de 5 minutos
        self::eliminarTemporal($tempPath, 300);
    }

    protected static function eliminarTemporal(string $filePath, int $delaySeconds = 300): void
    {
        dispatch(function () use ($filePath, $delaySeconds) {
            sleep($delaySeconds);
            if (file_exists($filePath)) {
                unlink($filePath);
                Log::info("Archivo temporal eliminado: {$filePath}");
            }
        })->delay(now()->addSeconds($delaySeconds));
    }
}
