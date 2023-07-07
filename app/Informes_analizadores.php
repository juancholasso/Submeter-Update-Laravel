<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Informes_analizadores extends Model
{
    protected $table= 'informes_analizadores';

    protected $fillable = [
        'check', 
        'emails',   
        'user_id',        
        'contador',
    ];

    public function _user(){
      return $this->belongsTo(User::class);
    }
}
