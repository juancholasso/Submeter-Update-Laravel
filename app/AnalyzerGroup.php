<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AnalyzerGroup extends Model
{
    public function analyzers()
    {
        return $this->hasMany("App\AnalyzerGroupDetails", "analyzer_group_id", "id");
    }
}
