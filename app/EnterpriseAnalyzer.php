<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EnterpriseAnalyzer extends Model
{
    public function analyzer()
    {
        return $this->hasOne("App\Analizador", "id", "analyzer_id");
    }
}
