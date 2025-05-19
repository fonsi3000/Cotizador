<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class WhatsAppTestService
{
    public static function enviarHelloWorldViaCurl(string $telefono): void
    {
        Log::info("🧪 Enviando mensaje hello_world vía cURL a {$telefono}");

        $numero = preg_replace('/[^0-9]/', '', $telefono);
        if (strlen($numero) < 10) {
            Log::warning("❌ Número de teléfono no válido: {$telefono}");
            return;
        }

        $token = 'EAATkE0qSd7wBO6OutqP0qdKnJrOMzZA0ZAOVkSx2GZBM4aNmhBlXaGmvlOvdcgc35mDzZCOoZABFbC30kg4m9tGxgvCHv0pKYu9QVVXd4abCJPuy3jSC7EwEFf2OWrXxZA0jnCUIqFB4ufNZC00ei0wZBgGV2QkbF84DgcxZBEt2whlbv69nHnMLeqStlkrD8pIgJBQZDZD';
        $phoneId = '607948232410753';

        $url = "https://graph.facebook.com/v22.0/{$phoneId}/messages";

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => '57' . $numero,
            'type' => 'template',
            'template' => [
                'name' => 'hello_world',
                'language' => [
                    'code' => 'en_US'
                ]
            ]
        ];

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
            ],
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            Log::error("❌ cURL Error:", ['message' => $error]);
        } else {
            Log::info("✅ Respuesta de Meta:", ['response' => json_decode($response, true)]);
        }
    }
}
