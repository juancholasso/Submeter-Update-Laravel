<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\User;
use App\Count;
use Auth;
use Session;
use App\EnergyMeter;

class SeguimientoObjetivosController extends Controller
{
    //
    public function Seguimiento(Request $request, $id)
    {
        $user = User::find($id);

        $interval = "";
        $flash_current_count = null;
        $session = Session::get('_flash');
        if(array_key_exists('intervalos', $session))
        {
            $interval = $session['intervalos'];
            if(array_key_exists("current_count", $session))
            {
                $flash_current_count = $session['current_count'];
            }
        }

        $dataRequest = [];
        $dataRequest["user"] = $user;
        $dataRequest["contador"] = "";
        $dataRequest["interval"] = $interval;
        $dataRequest["flash_current_count"] = $flash_current_count;

        $contador2 = ContadorController::getCurrrentController($dataRequest);

        return $this->getViewSeguimiento(array($user, $contador2, $request));
    }

    public function SeguimientoCounter(Request $request, $id, $counter_id)
    {
        $user = User::find($id);
        $count = EnergyMeter::find($counter_id);

        $interval = "";
        $flash_current_count = null;
        $session = Session::get('_flash');
        if(array_key_exists('intervalos', $session))
        {
            $interval = $session['intervalos'];
            if(array_key_exists("current_count", $session))
            {
                $flash_current_count = $session['current_count'];
            }
        }

        $dataRequest = [];
        $dataRequest["user"] = $user;
        $dataRequest["contador"] = $count->label;
        $dataRequest["interval"] = $interval;
        $dataRequest["flash_current_count"] = $flash_current_count;

        $contador2 = ContadorController::getCurrrentController($dataRequest);

        return $this->getViewSeguimiento(array($user, $contador2, $request));
    }

    public function getViewSeguimiento($dataCount)
    {
        $user = $dataCount[0];
        $current_count = $dataCount[1];
        $request = $dataCount[2];
        $tipo_count = $current_count->tipo;
        $tipo_tarifa = $current_count->tarifa;

        $dates = $this->getDatesSeguimiento($request);
        $datePeriod1 = $dates[0];
        $datePeriod2 = $dates[1];
        $period_type = $dates[2];

        config(['database.connections.mysql2.host' => $current_count->host]);
        config(['database.connections.mysql2.port' => $current_count->port]);
        config(['database.connections.mysql2.database' => $current_count->database]);
        config(['database.connections.mysql2.username' => $current_count->username]);
        config(['database.connections.mysql2.password' => $current_count->password]);
        env('MYSQL2_HOST',$current_count->host);
        env('MYSQL2_DATABASE',$current_count->database);
        env('MYSQL2_USERNAME', $current_count->username);
        env('MYSQL2_PASSWORD',$current_count->password);
        try {
            \DB::connection('mysql2')->getPdo();
        } catch (\Exception $e) {
            Session::flash('message-error', "La base de datos a la que se desea conectar está mal configurada.
                    Por favor, edite los parámetros de configuración de conexión.");
            return \Redirect::back();
        }
        $db = \DB::connection('mysql2');

        $domicilio = $db->table('Area_Cliente')->select(\DB::raw('`DENOMINACIÓN SOCIAL` denominacion_social,
                `SOCIAL DOMICILIO` social_domicilio, `SUMINISTRO DEL  DOMICILIO` suministro_del_domicilio,
                 CUPS, CIF, `DISTRIBUIDORA EMPRESA` distribuidora_empresa,
                `COMERCIALIZADORA EMPRESA` comercializadora_empresa, `CONTACTO DE  PERSONA` contacto_persona,
                `TELÉFONO`, `CONTACTO O  AYUDA` contacto_ayuda, `TARIFA`'))->first();

        if(!is_null($db->table('Area_Cliente')->select(\DB::raw('`LOGOTIPO` logo'))->first()))
        {
            $dir_image_count = $db->table('Area_Cliente')->select(\DB::raw('`LOGOTIPO` logo'))->first()->logo;
        }
        else
        {
            $dir_image_count = $db->table('Area_Cliente')->select(\DB::raw('`LOGOTIPO` logo'))->first();
        }


        $potencia_contratada = $db->table('Potencia_Contratada')
        ->select(\DB::raw("Periodo as periodo, MAX(`Potencia_contratada`) as potencia_contratada,
                RIGHT(Periodo,1) as periodo_int"))
                ->groupBy('Periodo')->get();

        $arreglo_potencia = array();
        foreach($potencia_contratada as $potencia){
            $arreglo_potencia[] = array("periodo"=>$potencia->periodo, "potencia"=>$potencia->potencia_contratada);
        }

        $data_calculos = array();
        $data_calculos["data_period_1"] = $datePeriod1;
        $data_calculos["data_period_2"] = $datePeriod2;
        $data_calculos["period_type"] = $period_type;

        $data_consumo_1 = $db->table('Consumo_Energia_Activa_Linea_Base')->select(\DB::raw('`date`,
                `EAct imp(kWh)` as consumo, `Linea_Base(kWh)` as linea_base,
                 RIGHT(Periodo,1) as periodo'))
             ->where("date",">=",$datePeriod1["date_from"])
             ->where("date","<=",$datePeriod1["date_to"])
             ->get()->toArray();

         $data_consumo_2 = $db->table('Consumo_Energia_Activa_Linea_Base')->select(\DB::raw('`date`,
                `EAct imp(kWh)` as consumo, `Linea_Base(kWh)` as linea_base,
                 RIGHT(Periodo,1) as periodo'))
             ->where("date",">=",$datePeriod2["date_from"])
             ->where("date","<=",$datePeriod2["date_to"])
             ->get()->toArray();


         $data_linea_base = $db->table('Linea_Base')->select(DB::raw('`EAct imp(kWh)` as linea_base, DiaDeSemana as dia'))->get();
         $vector_linea_base = array();

         foreach($data_linea_base as $data) {
             $idx = ($data->dia + 6) % 8;
             $vector_linea_base[$idx] = $data->linea_base;
         }

         ksort($vector_linea_base);
         $vector_linea_base = array_values($vector_linea_base);
         $date_labels = $this->getLabelsPlot($data_calculos);
         $datePeriod1["label_period"] = $date_labels["data_label_period"][0];
         $datePeriod2["label_period"] = $date_labels["data_label_period"][1];

         $datesInfo = array($datePeriod1, $datePeriod2);
         $label_interval = $date_labels["label_interval"];


         $data_calculos["data_consumo_1"] = $data_consumo_1;
         $data_calculos["data_consumo_2"] = $data_consumo_2;
         $data_calculos["data_labels"] = $date_labels;
         $data_calculos["vector_linea_base"] = $vector_linea_base;
         $data_calculos["arreglo_potencia"] = $arreglo_potencia;

         $dataPlotting = $this->getPlotSeguimiento($data_calculos,$date_labels);

         $dataComparison = $this->getDataComparison($data_calculos);

         $periodTypes = array(1=>"Comparativo Semanal", 2=>"Comparativo Mensual", 3=>"Comparativo Trimestral", 4=>"Comparativo Anual");

         $titulo = 'Seguimiento de Objetivos y Consumo';
         return view("seguimiento_objetivos.seguimiento", compact("arreglo_potencia", "user", "tipo_count", "tipo_tarifa",
             "titulo", "current_count", "domicilio", "dataPlotting", "dataComparison", "datesInfo", "dir_image_count",
             "periodTypes", "period_type", "label_interval"));
    }

    private function getLabelsPlot($data_calculos)
    {
        $period_type = $data_calculos["period_type"];
        $daysNames = array("1"=>"Lunes", "2"=>"Martes", "3"=>"Miercoles",
            "4"=>"Jueves","5"=>"Viernes", "6"=>"Sabado", "7"=>"Domingo");
        $monthNames = array("1"=>"Enero", "2"=>"Febrero", "3"=>"Marzo",
                "4"=>"Abril", "5"=>"Mayo","6"=>"Junio", "7"=>"Julio",
                "8"=>"Agosto", "9"=>"Septiembre","10"=>"Octubre",
                "11"=>"Noviembre", "12"=>"Diciembre");

        $date1 = Carbon::createFromFormat("Y-m-d", $data_calculos["data_period_1"]["date_to"]);
        $date2 = Carbon::createFromFormat("Y-m-d", $data_calculos["data_period_2"]["date_to"]);

        $period_label_1 = "";
        $period_label_2 = "";
        $aux_label = "";
        $label_interval = "";
        $interval_keys = array();
        $interval_values = array();

        switch($period_type){
            case 1:
                $aux_label = "Día :";
                $period_label_1 = "Semana ".$date1->weekOfYear. " (".$date1->year.")";
                $period_label_2 = "Semana ".$date2->weekOfYear. " (".$date2->year.")";
                $label_interval = "Comparativo Semanal";

                for($i = 1; $i <= 7; $i++)
                {
                    $interval_keys[] = $i;
                    $interval_values[] = $daysNames[$i];
                }

            break;
            case 2:
                $aux_label = "Día :";
                $period_label_1 = $monthNames[$date1->month]. " (".$date1->year.")";
                $period_label_2 = $monthNames[$date2->month]. " (".$date2->year.")";
                $label_interval = "Comparativo Mensual";

                $date1->endOfMonth();
                $date2->endOfMonth();
                if($date2->day > $date1->day)
                {
                    $maxDay = $date2->day;
                }
                else
                {
                    $maxDay = $date1->day;
                }

                for($i = 1; $i <= $maxDay; $i++)
                {
                    $interval_keys[] = $i;
                    $interval_values[] = $i;
                }
            break;
            case 3:
                $aux_label = "Mes :";
                $trimester_no_1 = floor(($date1->month - 1) /3) + 1;
                $trimester_no_2 = floor(($date2->month - 1) / 3) + 1;
                $period_label_1 = "Trimestre ".$trimester_no_1. " (".$date1->year.")";
                $period_label_2 = "Trimestre ".$trimester_no_2. " (".$date2->year.")";
                $label_interval = "Comparativo Trimestral";

                for($i = 1; $i <= 3; $i++)
                {
                    $interval_keys[] = $i;
                    $interval_values[] = "Mes ".$i;
                }
            break;
            case 4:
                $period_label_1 = "Año ".$date1->year;
                $period_label_2 = "Año ".$date2->year;
                $label_interval = "Comparativo Anual";

                for($i = 1; $i <= 12; $i++)
                {
                    $interval_keys[] = $i;
                    $interval_values[] = $monthNames[$i];
                }
            break;
        }

        $data_label_period = array($period_label_1, $period_label_2);


        $dateInterval = array();
        $dateInterval["interval_keys"] = $interval_keys;
        $dateInterval["interval_values"] = $interval_values;
        $dateInterval["aux_label"] = $aux_label;
        $dateInterval["data_label_period"] = $data_label_period;
        $dateInterval["label_interval"] = $label_interval;
        return $dateInterval;
    }

    private function getPlotSeguimiento($data_calculos,$date_labels)
    {
        $data_consumo_1 = $data_calculos["data_consumo_1"];
        $data_consumo_2 = $data_calculos["data_consumo_2"];
        $data_labels = $data_calculos["data_labels"];
        $vector_linea_base = $data_calculos["vector_linea_base"];
        $period_type = $data_calculos["period_type"];

        $dataPlot1 = array_fill_keys($data_labels["interval_keys"], 0.0);
        $dataPlot2 = array_fill_keys($data_labels["interval_keys"], 0.0);
        $dataObjective = array_fill_keys($data_labels["interval_keys"], 0.0);

        foreach($data_consumo_1 as $data)
        {
            $keyData = $this->getKeyPlotDate($data->date, $period_type);
            if(strlen($keyData) > 0)
            {
                $dataPlot1[$keyData] += $data->consumo;
                $dataObjective[$keyData] += $data->linea_base;
            }
        }

        foreach($data_consumo_2 as $data)
        {
            $keyData = $this->getKeyPlotDate($data->date, $period_type);
            if(strlen($keyData) > 0)
            {
                $dataPlot2[$keyData] += $data->consumo;
            }
        }

        foreach($dataObjective as $idx=>$objective)
        {
            $dataObjective[$idx] = round($objective, 0);
        }

        $plot = array();
        $plot["name"] = "Seguimiento de Objetivos y Consumos";
        $plot["labels"] = json_encode($data_labels["interval_values"]);
        $plot["series"] = array();

        $serie = array();
        $serie["name"] = $date_labels["data_label_period"][0];
        $serie["color"] = "#004165";
        $serie["values"] = json_encode(array_values($dataPlot1));
        $serie["aux_label"] = $data_labels["aux_label"];
        $plot["series"][] = $serie;

        $serie = array();
        $serie["name"] = $date_labels["data_label_period"][1];
        $serie["color"] = "#B9C9D0";
        $serie["values"] = json_encode(array_values($dataPlot2));
        $serie["aux_label"] = "";
        $plot["series"][] = $serie;

        $serie = array();
        $serie["name"] = "Objetivo";
        $serie["color"] = "#FE2E2E";
        $serie["values"] = json_encode(array_values($dataObjective));
        $serie["aux_label"] = "";
        $plot["series"][] = $serie;


        $dataPlotting = array();
        $dataPlotting["total"] = $plot;
        return $dataPlotting;
    }

    private function getDataComparison($data_calculos)
    {
        $data_consumo_1 = $data_calculos["data_consumo_1"];
        $data_consumo_2 = $data_calculos["data_consumo_2"];
        $arreglo_potencia = $data_calculos["arreglo_potencia"];
        $data_labels = $data_calculos["data_labels"];

        $data1 = array();
        $data2 = array();
        $dataVariation = array();
        $dataBaseLine1 = array();
        $dataBaseLine2 = array();
        for($i = 0; $i < count($arreglo_potencia); $i++){
            $data1[$i] = 0.0;
            $data2[$i] = 0.0;
            $dataVariation[$i] = 0.0;
            $dataBaseLine1[$i] = 0.0;
            $dataBaseLine2[$i] = 0.0;
        }

        $totalData = array(0.0, 0.0);
        $totalBaseLine = array(0.0, 0.0);
        $totalDifferences = array(0.0, 0.0);
        $totalVariation = 0.0;
        $totalVariationBaseline = 0.0;
        $totalVariationDifference = 0.0;

        foreach($data_consumo_1 as $data) {
            $idx = $data->periodo - 1;
            $data1[$idx] += $data->consumo;
            $dataBaseLine1[$idx] += $data->linea_base;
            //echo $data->consumo."\n";
            //print_r($data);
        }

        foreach($data_consumo_2 as $data) {
            $idx = $data->periodo - 1;
            $data2[$idx] += $data->consumo;
            $dataBaseLine2[$idx] += $data->linea_base;
        }

        for($i = 0; $i < count($data1); $i++){
            $dataVariation[$i] = $data1[$i] - $data2[$i];
            $totalData[0] += $data1[$i];
            $totalBaseLine[0] += $dataBaseLine1[$i];

            $totalData[1] += $data2[$i];
            $totalBaseLine[1] += $dataBaseLine2[$i];
        }
        $totalDifferences[0] = $totalData[0] - $totalBaseLine[0];
        $totalDifferences[1] = $totalData[1] - $totalBaseLine[1];

        $totalVariation = $totalData[0] - $totalData[1];
        $totalVariationBaseline = $totalBaseLine[0] - $totalBaseLine[1];
        $totalVariationDifference = $totalDifferences[0] - $totalDifferences[1];

        $dataComparison = array();
        $dataComparison["data1"] = $data1;
        $dataComparison["data2"] = $data2;
        $dataComparison["dataBaseLine1"] = $dataBaseLine1;
        $dataComparison["dataBaseLine2"] = $dataBaseLine2;
        $dataComparison["dataVariation"] = $dataVariation;
        $dataComparison["totalData"] = $totalData;
        $dataComparison["totalBaseLine"] = $totalBaseLine;
        $dataComparison["totalDifferences"] = $totalDifferences;
        $dataComparison["totalVariation"] = $totalVariation;
        $dataComparison["totalVariationBaseline"] = $totalVariationBaseline;
        $dataComparison["totalVariationDifference"] = $totalVariationDifference;
        $dataComparison["dataLabelPeriods"] = $data_labels["data_label_period"];

        return $dataComparison;
    }

    function getDatesSeguimiento(Request $request)
    {
        $dates = $request->session()->get('seguimiento_dates', '');
        if(strlen($dates) > 0)
        {
            $dates = unserialize($dates);
        }
        else
        {
            $period_type = 1;
            $date1 = Carbon::now();
            $data_period = array();
            $data_period["date_from"] = $date1->toDateString();
            $data_period["date_to"] = $date1->toDateString();
            $data_period["period_type"] = $period_type;
            $datePeriod1 = $this->adjustDatesPeriod($data_period);


            $date2 = Carbon::now();
            $date2 = $date2->addWeek(-1)->startOfWeek();
            $data_period = array();
            $data_period["date_from"] = $date2->toDateString();
            $data_period["date_to"] = $date2->toDateString();
            $data_period["period_type"] = $period_type;
            $datePeriod2 = $this->adjustDatesPeriod($data_period);

            $dates = array($datePeriod1, $datePeriod2, $period_type);
            $datesS = serialize($dates);
            $request->session()->put("seguimiento_dates", $datesS);
        }
        return $dates;
    }

    function SeguimientoCambiarFechas(Request $request)
    {
        $interval_change = $request->input('interval_change');
        $date_select = $request->input('date_select');
        $type_date = $request->input('type_date');
        $date_from_o = $request->input('date_from');
        $counter_id = $request->input('count');
        $period_type = $request->input('period_type');
        $user_id = Auth::user()->id;
        $counter = EnergyMeter::find($counter_id);

        $dates = $request->session()->get('seguimiento_dates', '');

        if(strlen($dates) > 0)
        {
            $dates = unserialize($dates);

            if(strlen($date_select) > 0)
            {
                $date1 = Carbon::createFromFormat("Y-m-d", $date_select);
                $data_period = array();
                $data_period["date_from"] = $date1->toDateString();
                $data_period["date_to"] = $date1->toDateString();
                $data_period["period_type"] = $period_type;
                $dates[$type_date] = $this->adjustDatesPeriod($data_period);
            }
            else
            {
                $date = Carbon::createFromFormat("Y-m-d", $date_from_o);
                switch($period_type){
                    case 1:
                        $date->addWeek($interval_change);
                    break;
                    case 2:
                        $date->addMonth($interval_change);
                    break;
                    case 3:
                        $date->addMonth(3*$interval_change);
                    break;
                    case 4:
                        $date->addYear($interval_change);
                    break;
                }
                $data_period = array();
                $data_period["date_from"] = $date->toDateString();
                $data_period["date_to"] = $date->toDateString();
                $data_period["period_type"] = $period_type;
                $dates[$type_date] = $this->adjustDatesPeriod($data_period);
            }

            $datesS = serialize($dates);
            $request->session()->put("seguimiento_dates", $datesS);
            return redirect()->route('seguimiento.objetivos.count',[$user_id, $counter_id]);
        }
    }

    function SeguimientoCambiarPeriodo(Request $request)
    {
        $dates_from = $request->get("dates_from");
        $dates_to = $request->get("dates_to");
        $period_type = $request->get("period_type");
        $data_period = array();
        $data_period["date_from"] = $dates_from[0];
        $data_period["date_to"] = $dates_to[0];
        $data_period["period_type"] = $period_type;
        $counter_id = $request->input('count');
        $user_id = Auth::user()->id;
        $counter = EnergyMeter::find($counter_id);

        $datePeriod1 = $this->adjustDatesPeriod($data_period);

        $data_period = array();
        $data_period["date_from"] = $dates_from[1];
        $data_period["date_to"] = $dates_to[1];
        $data_period["period_type"] = $period_type;

        $datePeriod2 = $this->adjustDatesPeriod($data_period);

        $dates = array($datePeriod1, $datePeriod2, $period_type);
        $datesS = serialize($dates);
        $request->session()->put("seguimiento_dates", $datesS);

        return redirect()->route('seguimiento.objetivos.count',[$user_id, $counter_id]);
    }

    private function adjustDatesPeriod($data_period)
    {
        $date_from = $data_period["date_from"];
        $date_to = $data_period["date_to"];
        $period_type = $data_period["period_type"];

        switch($period_type)
        {
            case 1:
                $date_aux = Carbon::createFromFormat("Y-m-d", $date_from);
                $date_aux = $date_aux->startOfWeek();
                $date_from = $date_aux->toDateString();
                $date_aux = $date_aux->endOfWeek();
                $date_to = $date_aux->toDateString();
            break;
            case 2:
                $date_aux = Carbon::createFromFormat("Y-m-d", $date_from);
                $date_from = $date_aux->startOfMonth()->toDateString();
                $date_to = $date_aux->endOfMonth()->toDateString();
            break;
            case 3:
                $date_aux = Carbon::createFromFormat("Y-m-d", $date_from);
                $date_aux->month;
                $trimester_idx = floor(($date_aux->month - 1)/3);
                $begin_month = $trimester_idx * 3 + 1;
                $end_month = ($trimester_idx + 1) * 3;

                $date_from_aux = Carbon::createFromFormat("Y-n-d", $date_aux->year."-".($begin_month)."-01");
                $date_from = $date_from_aux->toDateString();

                $date_to_aux = Carbon::createFromFormat("Y-n-d", $date_aux->year."-".($end_month)."-01");
                $date_to_aux->endOfMonth();
                $date_to = $date_to_aux->toDateString();
            break;
            case 4:
                $date_aux = Carbon::createFromFormat("Y-m-d", $date_from);
                $date_from = $date_aux->startOfYear()->toDateString();
                $date_to = $date_aux->endOfYear()->toDateString();
            break;
        }

        $datePeriod = array("date_from"=>$date_from, "date_to"=>$date_to);
        return $datePeriod;
    }

    private function getKeyPlotDate($date, $period_type)
    {
        $date_aux = Carbon::createFromFormat("Y-m-d", $date);
        $key = "";
        switch ($period_type){
            case 1:
                $key = $date_aux->dayOfWeekIso;
            break;
            case 2:
                $key = $date_aux->day;
            break;
            case 3:
                $key = (($date_aux->month - 1) % 3) + 1;
            break;
            case 4:
                $key = $date_aux->month;
            break;
        }
        return $key;
    }
}
