<?php

namespace App\Services;

use App\Models\Cotizacion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Illuminate\Support\Str as StrSupport;

class WhatsAppService
{
    public static function enviarCotizacion(Cotizacion $cotizacion): void
    {
        Log::info("📤 Iniciando envío de WhatsApp para cotización ID {$cotizacion->id}");

        $telefono = preg_replace('/[^0-9]/', '', $cotizacion->numero_celular_cliente);
        Log::info("📱 Teléfono procesado: {$telefono}");

        if (!$telefono || strlen($telefono) < 10) {
            Log::warning("❌ Teléfono no válido para WhatsApp en cotización ID {$cotizacion->id}");
            return;
        }

        // Cargar relaciones necesarias
        $cotizacion->load(['items.producto', 'items.listaPrecio', 'usuario']);
        $empresa = $cotizacion->usuario->empresa ?? 'NO DEFINIDA';
        Log::info("🏢 Empresa del usuario: {$empresa}");

        // Crear archivo temporal directo desde la vista PDF
        $random = Str::random(20);
        $tempFileName = "cotizacion-{$cotizacion->id}-{$random}.pdf";
        $tempPath = public_path("tmp-cotizaciones/{$tempFileName}");

        // Crear carpeta si no existe
        if (!File::exists(public_path("tmp-cotizaciones"))) {
            File::makeDirectory(public_path("tmp-cotizaciones"), 0755, true);
            Log::info('📂 Carpeta tmp-cotizaciones creada');
        }

        // Generar PDF dinámicamente y guardarlo
        try {
            $pdf = Pdf::loadView('Cotizacion.CotizacionPDF', [
                'cotizacion' => $cotizacion,
                'isPdfDownload' => true,
            ])->output();

            File::put($tempPath, $pdf);
            Log::info("🧾 Archivo PDF generado y guardado: {$tempPath}");
        } catch (\Throwable $e) {
            Log::error("❌ Error al generar PDF para cotización ID {$cotizacion->id}", [
                'exception' => $e->getMessage(),
            ]);
            return;
        }

        // Construir URL pública
        $publicUrl = config('services.whatsapp.public_url') . "/tmp-cotizaciones/{$tempFileName}";
        $clienteNombre = $cotizacion->nombre_cliente;

        // Seleccionar configuración según empresa
        $empresaStr = StrSupport::of($empresa)->lower();

        if ($empresaStr->contains('litoral')) {
            $phoneId = config('services.whatsapp_litoral.phone_id');
            $token = config('services.whatsapp_litoral.token');
            $template = config('services.whatsapp_litoral.template');
            Log::info("⚙️ Usando configuración de Litoral");
        } else {
            $phoneId = config('services.whatsapp.phone_id');
            $token = config('services.whatsapp.token');
            $template = config('services.whatsapp.template');
            Log::info("⚙️ Usando configuración de Medellín");
        }

        Log::info("📨 Template: {$template}");
        Log::info("📞 Phone ID: {$phoneId}");

        // Construir payload
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => '57' . $telefono,
            'type' => 'template',
            'template' => [
                'name' => $template,
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
                    [
                        'type' => 'body',
                        'parameters' => [
                            [
                                'type' => 'text',
                                'text' => $clienteNombre,
                                'parameter_name' => 'name',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $url = "https://graph.facebook.com/v22.0/{$phoneId}/messages";

        // Logs de depuración
        Log::info('🌐 URL destino:', [$url]);
        Log::info('📦 Payload:', $payload);
        Log::info('🔐 Token parcial:', [substr($token, 0, 20) . '...']);

        // Enviar solicitud
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            if ($response->failed()) {
                Log::error('❌ Error al enviar WhatsApp', [
                    'cotizacion_id' => $cotizacion->id,
                    'response' => $response->json(),
                    'url_pdf' => $publicUrl,
                ]);
            } else {
                Log::info("✅ WhatsApp enviado correctamente a 57{$telefono}");
                Log::info("📬 Respuesta:", [$response->body()]);
            }
        } catch (\Throwable $e) {
            Log::error("❌ Excepción al enviar solicitud HTTP", [
                'message' => $e->getMessage(),
            ]);
        }

        // Eliminar archivo temporal
        self::eliminarTemporal($tempPath, 300);
    }

    protected static function eliminarTemporal(string $filePath, int $delaySeconds = 300): void
    {
        dispatch(function () use ($filePath) {
            sleep(300);
            if (file_exists($filePath)) {
                unlink($filePath);
                Log::info("🗑️ Archivo temporal eliminado: {$filePath}");
            }
        })->delay(now()->addSeconds($delaySeconds));
    }
}
