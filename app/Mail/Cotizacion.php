<?php

namespace App\Mail;

use App\Models\Cotizacion as CotizacionModel;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class Cotizacion extends Mailable
{
    use Queueable, SerializesModels;

    public CotizacionModel $cotizacion;

    public function __construct(CotizacionModel $cotizacion)
    {
        $this->cotizacion = $cotizacion;
    }

    public function build()
    {
        $pdf = Pdf::loadView('Cotizacion.CotizacionPDF', [
            'cotizacion' => $this->cotizacion,
        ])->output();

        return $this->subject('Gracias por cotizar con Espumas Medellín')
            ->view('mails.cotizacion')
            ->with(['cotizacion' => $this->cotizacion])
            ->attachData($pdf, "cotizacion-{$this->cotizacion->id}.pdf", [
                'mime' => 'application/pdf',
            ]);
    }
}
