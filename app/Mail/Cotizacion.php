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
        $empresa = $this->cotizacion->usuario->empresa ?? 'Espumas Medellín S.A';

        $empresaNombreCorto = $empresa === 'Espumados del Litoral S.A'
            ? 'Espumados del Litoral'
            : 'Espumas Medellín';

        $pdf = Pdf::loadView('Cotizacion.CotizacionPDF', [
            'cotizacion' => $this->cotizacion,
        ])->output();

        return $this->subject("Gracias por cotizar con $empresaNombreCorto")
            ->view('mails.cotizacion')
            ->with(['cotizacion' => $this->cotizacion])
            ->attachData($pdf, "cotizacion-{$this->cotizacion->id}.pdf", [
                'mime' => 'application/pdf',
            ]);
    }
}
