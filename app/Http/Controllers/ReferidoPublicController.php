<?php

namespace App\Http\Controllers;

use App\Mail\CodigoReferidoMail;
use App\Mail\CodigoReferidorMail;
use App\Models\Referido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class ReferidoPublicController extends Controller
{
    /**
     * Paso 1: Mostrar formulario para ingresar el correo del referidor.
     */
    public function showEmailForm()
    {
        return view('public.referido-ingresar-correo');
    }

    /**
     * Paso 1: Enviar código al correo del referidor.
     */
    public function sendReferidorCode(Request $request)
    {
        $validated = $request->validate([
            'correo_referidor' => ['required', 'email', function ($attribute, $value, $fail) {
                $dominiosPermitidos = ['@espumasmedellin.com.co', '@espumadosdellitoral.com.co', '@stn.com.co'];
                $correosProhibidos = [
                    'Saladeventasguatapuri@espumadosdellitoral.com.co',
                    'lider.saladeventas@espumadosdellitoral.com.co',
                    'sala.ventasguacari@espumadosdellitoral.com.co',
                    'sala.ventasvalledupar@espumadosdellitoral.com.co',
                    'saladeventas.portaldelprado@espumadosdellitoral.com.co',
                    'celagranmanzana@espumadosdellitoral.com.co',
                    'sala.ventastradicional@espumadosdellitoral.com.co',
                    'cecaribeplaza@espumadosdellitoral.com.co',
                    'Centrodeexperienciaalegra@espumadosdellitoral.com.co',
                    'centrodeexperiencias@espumadosdellitoral.com.co',
                    'sala.santafe@espumasmedellin.com.co',
                    'sala.losmolinos@espumasmedellin.com.co',
                    'sala.florida@espumasmedellin.com.co',
                    'sala.rionegro@espumasmedellin.com.co',
                    'sala.callecolombia@espumasmedellin.com.co',
                    'sala.ventasmakro@espumasmedellin.com.co',
                    'sala.itaguiparque@espumasmedellin.com.co',
                    'sala.mayorca@espumasmedellin.com.co',
                    'sala.parquefabricato@espumasmedellin.com.co',
                    'sala.sandiego@espumasmedellin.com.co',
                    'sala.autopista@espumasmedellin.com.co',
                    'sala.laestrella@espumasmedellin.com.co',
                ];

                if (!Str::endsWith($value, $dominiosPermitidos)) {
                    $fail('El correo debe ser corporativo.');
                }

                if (in_array(strtolower($value), array_map('strtolower', $correosProhibidos))) {
                    $fail('Este correo no está autorizado para registrar referidos.');
                }
            }],
        ]);

        $correoReferidor = $validated['correo_referidor'];

        // Verifica si ya existe un registro pendiente
        $referidoExistente = Referido::where('correo_referidor', $correoReferidor)
            ->where('estado', 'pendiente')
            ->where('referidor_validado', false)
            ->first();

        if ($referidoExistente) {
            return redirect()->route('referido.validar-codigo', ['id' => $referidoExistente->id])
                ->with('mensaje', 'Ya se envió un código a este correo. Por favor verifica tu correo y completa la validación.');
        }

        $codigo = random_int(100000, 999999);

        $referido = Referido::create([
            'correo_referidor' => $correoReferidor,
            'codigo_referidor' => $codigo,
            'estado' => 'pendiente',
            'vigencia' => Carbon::now()->addMonth(),
        ]);

        try {
            Mail::to($correoReferidor)->send(new CodigoReferidorMail($codigo));
            \Log::info("✅ Código enviado correctamente a {$correoReferidor}");
        } catch (\Exception $e) {
            \Log::error("❌ Error al enviar correo al referidor: " . $e->getMessage());
        }


        return redirect()->route('referido.validar-codigo', ['id' => $referido->id]);
    }

    /**
     * Paso 2: Mostrar formulario para validar el código recibido.
     */
    public function showCodeForm($id)
    {
        $referido = Referido::findOrFail($id);
        return view('public.referido-validar-codigo', compact('referido'));
    }

    /**
     * Paso 2: Validar el código ingresado por el referidor.
     */
    public function validateReferidorCode(Request $request, $id)
    {
        $referido = Referido::findOrFail($id);

        $request->validate([
            'codigo_referidor' => 'required|numeric|digits:6',
        ]);

        if ($request->codigo_referidor != $referido->codigo_referidor) {
            return back()->withErrors(['codigo_referidor' => 'Código incorrecto.'])->withInput();
        }

        $referido->update(['referidor_validado' => true]);

        return view('public.referido-form', compact('referido'));
    }

    /**
     * Paso 3: Guardar datos del referido y enviar código al referido.
     */
    public function storeReferido(Request $request, $id)
    {
        $referido = Referido::findOrFail($id);

        $validated = $request->validate([
            'nombre_referidor' => 'required|string|max:255',
            'documento_referidor' => 'required|string|max:50',
            'nombre_referido' => 'required|string|max:255',
            'documento_referido' => 'required|string|max:50',
            'correo_referido' => 'required|email|max:255',
        ]);

        $codigo = random_int(100000, 999999);

        // Evitar sobrescribir correo_referidor
        $referido->update([
            'nombre_referidor' => $validated['nombre_referidor'],
            'documento_referidor' => $validated['documento_referidor'],
            'nombre_referido' => $validated['nombre_referido'],
            'documento_referido' => $validated['documento_referido'],
            'correo_referido' => $validated['correo_referido'],
            'codigo_referido' => $codigo,
            'referido_validado' => false,
            'estado' => 'activo',
        ]);

        \Log::info('Enviando código al referido: ' . $referido->correo_referido);
        Mail::to($referido->correo_referido)->send(new CodigoReferidoMail($codigo));

        return redirect()->route('referido.public.success');
    }

    /**
     * Vista final de éxito
     */
    public function success()
    {
        return view('public.referido-success');
    }
}
