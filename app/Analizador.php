<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Analizador extends Model
{
    protected $fillable = [
        'label', 'count_id', 'host', 'port', 'database', 'username', 'password', 'principal',
    ];

    protected $table= 'analizadors';

    public function _count(){
      return $this->belongsTo(Count::class);
    }
    
    public function meter()
    {
        return $this->hasOne("App\EnergyMeter", "id", "count_id");
    }
    
    public function analyzer_meters()
    {
        return $this->hasMany("App\AnalyzerMeter", "analyzer_id", "id");
    }
	
	public function meters(){
        return $this->belongsToMany(EnergyMeter::class, "analyzer_meters", "analyzer_id", "meter_id");
    }
}
