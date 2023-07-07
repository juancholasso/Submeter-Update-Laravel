<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendAlertPotenciaConsumo extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $user;
    public $contador;
    public $fecha;
    public $alert;
    public $periodo;
    public $date_antier;
    public $hora;
    public $poten;
    public $CUPS;
    public $dataMail;

    public function __construct($user,$contador,$fecha,$alert,$periodos,$date_antier,$hora,$poten,$CUPS, $dataMail)
    {
        $this->user = $user;
        $this->contador = $contador;
        $this->fecha = $fecha;
        $this->alert = $alert;
        $this->periodo = $periodos;
        $this->date_antier = $date_antier;
        $this->hora = $hora;
        $this->poten = $poten;
        $this->CUPS = $CUPS;
        $this->dataMail = $dataMail;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.alerta_potencia')->from('submeter@submeter.email', 'Plataforma Submeter 4.0')->subject("Alertas Submeter 4.0");
    }
}
