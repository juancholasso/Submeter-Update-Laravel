<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AlertaGeneral extends Mailable
{
    use Queueable, SerializesModels;

    public $dataMail;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($dataMail)
    {
        $this->dataMail = $dataMail;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.alertas.alerta_general')->from('submeter@submeter.email', 'Plataforma Submeter 4.0')->subject("Alertas Submeter 4.0");
    }
}
