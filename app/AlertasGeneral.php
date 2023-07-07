<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AlertasGeneral extends Model
{
    protected $table= 'alertas_general';

    protected $fillable = [
        'contador',
    	'conexion',
        'frecuencia_mes',
        'frecuencia_dia',
        'avisos',
        'nombre_alerta',
        'destinatarios', 
        'activado', 
        'user_id', 
        'control_avisos_fecha', 
        'control_avisos_num'
    ];

    public function _user(){
      return $this->belongsTo(User::class);
    }
}
