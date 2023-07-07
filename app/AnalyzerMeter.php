<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AnalyzerMeter extends Model
{
    public function meter()
    {
        return $this->hasOne("App\EnergyMeter", "id", "meter_id");
    }
    
    public function analyzer()
    {
        return $this->hasOne("App\Analizador", "id", "analyzer_id");
    }
}
