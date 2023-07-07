<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Alertas extends Model
{
    protected $table= 'alertas';

    protected $fillable = [
    	'alert_type',
        'alert_value',
        'emails',
        'user_id',
        'contador',
    ];

    public function _user(){
      return $this->belongsTo(User::class);
    }
}
