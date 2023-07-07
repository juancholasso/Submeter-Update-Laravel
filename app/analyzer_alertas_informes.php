<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Analyzer_alertas_informes extends Model
{
    protected $table= 'analyzer_alertas_informes';

    protected $fillable = [
        'analyzer_id', 
        'informes',   
        'alertas',        
    ];

    public function _user(){
      return $this->belongsTo(User::class);
    }
}
