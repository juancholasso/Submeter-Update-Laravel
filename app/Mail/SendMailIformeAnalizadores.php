<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendMailIformeAnalizadores extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
  public $group  = array();
	public $data_analizadores = array();
  public $data_analizadores_total = array();
	public $informes = '';
	public $contador_name = '';
	public $label_intervalo = '';



    public function __construct($group,$data_analizadores,$informes,$contador_name,$label_intervalo,$data_analizadores_total)
    {
      $this->group = $group;
      $this->data_analizadores = $data_analizadores;
      $this->data_analizadores_total = $data_analizadores_total;
  		$this->informes = $informes;
  		$this->contador_name = $contador_name;
  		$this->label_intervalo = $label_intervalo;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.informesAnalizadores')->from('submeter@submeter.email', 'Plataforma Submeter 4.0')->subject('Informe Analizadores '.$this->informes.' Submeter 4.0 - Contador: '.$this->contador_name.' ('.$this->label_intervalo.')');
        //	.$this->contador->count_label.'( '.$this->label_intervalo.' )'
    }
}
