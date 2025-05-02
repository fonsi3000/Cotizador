<?php

namespace App\Console\Commands;

use App\Mail\Cotizacion;
use App\Models\User;
use App\Models\Cotizacion as CotizacionModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía una cotización de prueba por correo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = User::find(1);

        if (! $user) {
            $this->error('Usuario no encontrado.');
            return;
        }

        // Obtén una cotización de prueba (ID 1 en este ejemplo)
        $cotizacion = CotizacionModel::with(['items.producto', 'items.listaPrecio', 'usuario'])->find(1);

        if (! $cotizacion) {
            $this->error('Cotización no encontrada.');
            return;
        }

        // Envío del correo
        Mail::to($user->email)->send(new Cotizacion($cotizacion));

        $this->info('Correo enviado correctamente a ' . $user->email);
    }
}
