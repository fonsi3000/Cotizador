<?php

namespace App\Services;

use App\Models\Cotizacion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class WhatsAppService
{
    public static function enviarCotizacion(Cotizacion $cotizacion): void
    {
        $telefono = preg_replace('/[^0-9]/', '', $cotizacion->numero_celular_cliente);
        if (!$telefono || strlen($telefono) < 10) {
            Log::warning("Número no válido para WhatsApp en cotización ID {$cotizacion->id}");
            return;
        }

        // Cargar relaciones necesarias
        $cotizacion->load(['items.producto', 'items.listaPrecio', 'usuario']);

        // Crear archivo temporal directo desde la vista PDF
        $random = Str::random(20);
        $tempFileName = "cotizacion-{$cotizacion->id}-{$random}.pdf";
        $tempPath = public_path("tmp-cotizaciones/{$tempFileName}");

        // Crear carpeta si no existe
        if (!File::exists(public_path("tmp-cotizaciones"))) {
            File::makeDirectory(public_path("tmp-cotizaciones"), 0755, true);
            Log::info('Carpeta tmp-cotizaciones creada');
        }

        // Generar PDF dinámicamente y guardarlo
        try {
            $pdf = Pdf::loadView('Cotizacion.CotizacionPDF', [
                'cotizacion' => $cotizacion,
                'isPdfDownload' => true,
            ])->output();

            File::put($tempPath, $pdf);
            Log::info("Archivo PDF generado y guardado temporalmente: {$tempPath}");
        } catch (\Throwable $e) {
            Log::error("Error al generar PDF para cotización ID {$cotizacion->id}", [
                'exception' => $e->getMessage(),
            ]);
            return;
        }

        // Construir URL pública
        $publicUrl = config('services.whatsapp.public_url') . "/tmp-cotizaciones/{$tempFileName}";

        // Obtener el nombre del cliente para la plantilla
        $clienteNombre = $cotizacion->nombre_cliente;

        // Construir payload incluyendo el header (documento) y el body (nombre)
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => '57' . $telefono,
            'type' => 'template',
            'template' => [
                'name' => config('services.whatsapp.template'),
                'language' => ['code' => 'es_CO'],
                'components' => [
                    // 1) Encabezado: el documento PDF
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
                    // 2) Cuerpo: variable nombrada {{name}}, se requiere 'parameter_name'
                    [
                        'type' => 'body',
                        'parameters' => [
                            [
                                'type' => 'text',
                                'text' => $cotizacion->nombre_cliente,
                                'parameter_name' => 'name',
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
