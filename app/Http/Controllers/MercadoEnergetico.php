<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Carbon\CarbonInterval;
use App\User;
use App\Count;
use App\intervalos_user;
use Response;
use Auth;
use Session;

class MercadoEnergetico extends Controller
{
    public function MercadoEnergetico(Request $request, $id)
    {

        $user = User::find($id);
        $contador = strtolower(request()->input('contador'));

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
        $dataRequest["contador"] = $contador;
        $dataRequest["interval"] = $interval;
        $dataRequest["flash_current_count"] = $flash_current_count;

        $contador2 = ContadorController::getCurrrentController($dataRequest);


        $tipo_count = $contador2->tipo;
        $tipo_tarifa = $contador2->tarifa;
        $contador_label = $contador2->count_label;
        $current_count = $contador_label;

        config(['database.connections.mysql2.host' => $contador2->host]);
        config(['database.connections.mysql2.port' => $contador2->port]);
        config(['database.connections.mysql2.database' => $contador2->database]);
        config(['database.connections.mysql2.username' => $contador2->username]);
        config(['database.connections.mysql2.password' => $contador2->password]);
        env('MYSQL2_HOST',$contador2->host);
        env('MYSQL2_DATABASE',$contador2->database);
        env('MYSQL2_USERNAME', $contador2->username);
        env('MYSQL2_PASSWORD',$contador2->password);
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

        $flash = Session::get('_flash');

        if(array_key_exists("date_from_personalice", $flash)){
            $date_from = $flash['date_from_personalice'];
        }

        if(!isset($date_from)){
            $dateInfo = $this->getDatesAnalysis();
            $date_from = $dateInfo["date_from"];
            $date_to = $dateInfo["date_to"];
            $label_intervalo = $dateInfo["date_label"];
        } else {
            $flash = Session::get('_flash');

            $date_to = Session::get('_flash')['date_to_personalice'];
            if(array_key_exists("label_intervalo_navigation", $flash)){
                $dateInfo = $this->getDatesAnalysis();
                $label_intervalo = $dateInfo["date_label"];
            } else {
                $dateInfo = $this->getDatesAnalysis();
                $label_intervalo = $dateInfo["date_label"];
            }
        }

        $potencia_contratada = $db->table('Potencia_Contratada')
                                    ->select(\DB::raw("RIGHT(Periodo,1) as periodo_int, Periodo as periodo"))
                                    ->where('date_start','<=',$date_from)->where('date_end','>=',$date_to)
                                    ->groupBy('Periodo')->get();

        $precios_energia = $db->table('Periodo_Precios_Energia')
                                ->select(\DB::raw("date, time_start, time_end, OMIE_precio as omie, REE_precio as ree,
                                    Cliente_precio as cliente, RIGHT(Periodo,1) as periodo_int, Periodo as periodo"))
                                ->where('date','>=',$date_from)->where('date','<=',$date_to)->get();

        /* Cambiado Coste_Energia_Activa_OMIE por Coste_Energia_Activa_REE ya que utilizan la misma estructura */
        $coste_omie = $db->table('Coste_Energia_Activa_REE')
                            ->where('date','>=',$date_from)->where('date','<=',$date_to)->get();

        $coste_activa = $db->table('Coste_Energia_Activa')
                            ->where('date','>=',$date_from)->where('date','<=',$date_to)->get();

        $vector_potencia = array();
        foreach($potencia_contratada as $potencia){
            $idx_periodo = intval($potencia->periodo_int) - 1;
            $vector_potencia[$idx_periodo] = $potencia->periodo;
        }
        $vector_potencia = array_values($vector_potencia);

        $data_calculos = compact("coste_activa", "coste_omie", "date_from", "date_to", "interval",
                                "precios_energia" ,"vector_potencia");

        $data_labels = $this->getLabelsPlot($data_calculos);
        $data_calculos["data_labels"] = $data_labels;

        $dataPlotting = $this->createMarketPlots($data_calculos);

        $dataMercado = $this->calcularCostosMercado($data_calculos);

        $titulo = "Mercado Energético";

        return view("mercado_energetico.mercado_energetico", compact("contador_label", "contador2", "dataMercado", "dataPlotting",
                "date_from", "date_to", "dir_image_count", "domicilio", "label_intervalo", "tipo_count", "titulo", "user",
                "vector_potencia"));
    }

    private function calcularCostosMercado($data_calculos)
    {
        $coste_omie = $data_calculos["coste_omie"];
        $coste_activa = $data_calculos["coste_activa"];
        $vector_potencia = $data_calculos["vector_potencia"];
        $precios_energia = $data_calculos["precios_energia"];

        $dataREE = array_fill_keys($vector_potencia, 0.0);
        $dataOMIE = array_fill_keys($vector_potencia, 0.0);
        $dataCliente = array_fill_keys($vector_potencia, 0.0);

        $countREE = array_fill_keys($vector_potencia, 0);
        $countOMIE = array_fill_keys($vector_potencia, 0);
        $countCliente = array_fill_keys($vector_potencia, 0);

        $totalRee = 0.0;
        $totalOmie = 0.0;
        $totalActiva = 0.0;

        foreach($precios_energia as $index => $precios)
        {
            $keyData = $precios->periodo;
            if($keyData)
            {
                if($precios->ree !== null)
                {
                    $dataREE[$keyData] += $precios->ree;
                    $countREE[$keyData] ++;
                }
                if($precios->omie !== null)
                {
                    $dataOMIE[$keyData] += $precios->omie;
                    $countOMIE[$keyData] ++;
                }
                if($precios->cliente !== null)
                {
                    $dataCliente[$keyData] += $precios->cliente;
                    $countCliente[$keyData] ++;
                }
            }
        }

        foreach($coste_omie as $coste)
        {
            foreach($vector_potencia as $potencia)
            {
                if(property_exists($coste, $potencia))
                {
                    $totalOmie += $coste->$potencia;
                }
            }
        }

        foreach($coste_activa as $coste)
        {
            foreach($vector_potencia as $potencia)
            {
                if(property_exists($coste, $potencia))
                {
                    $totalActiva += $coste->$potencia;
                }
            }
        }

        foreach($countREE as $keyData => $numberData)
        {
            if($numberData > 0)
            {
                $dataREE[$keyData] /= $numberData;
                $dataREE[$keyData] = round($dataREE[$keyData], 2);
            }
        }

        foreach($countOMIE as $keyData => $numberData)
        {
            if($numberData > 0)
            {
                $dataOMIE[$keyData] /= $numberData;
                $dataOMIE[$keyData] = round($dataOMIE[$keyData], 2);
            }
        }

        foreach($countCliente as $keyData => $numberData)
        {
            if($numberData > 0)
            {
                $dataCliente[$keyData] /= $numberData;
                $dataCliente[$keyData] = round($dataCliente[$keyData], 2);
            }
        }

        $dataREE = array_values($dataREE);
        $dataOMIE = array_values($dataOMIE);
        $dataCliente = array_values($dataCliente);

        $dataMercado = [];
        $dataMercado["dataRee"] = $dataREE;
        $dataMercado["dataOmie"] = $dataOMIE;
        $dataMercado["dataCliente"] = $dataCliente;
        $dataMercado["totalRee"] = $totalRee;
        $dataMercado["totalOmie"] = $totalOmie;
        $dataMercado["totalActiva"] = $totalActiva;
        $dataMercado["diferenciaEnergia"] = $totalOmie - $totalActiva;
        return $dataMercado;
    }

    private function getDatesAnalysis($date_reference = null)
    {
        $interval = Session::get('_flash')['intervalos'];

        $monthsNames = array(1=>"Enero", 2=>"Febrero", 3=>"Marzo", 4=>"Abril",
            5=>"Mayo", 6=>"Junio", 7=>"Julio", 8=>"Agosto", 9=>"Septiembre",
            10=>"Octubre", 11=>"Noviembre", 12=>"Diciembre");
        if(!is_numeric($interval))
        {
            $interval = 2;
        }
        $date_label = "";

        switch ($interval){
            case 1:
                $date_from = Carbon::yesterday()->toDateString();
                $date_to = $date_from;
                $date_label = 'Ayer';
                break;
            case 2:
                $date_from = Carbon::now()->toDateString();
                $date_to = $date_from;
                $date_label = 'Hoy';
                break;
            case 3:
                $date_from = Carbon::now()->startOfWeek()->toDateString();
                $date_to = Carbon::now()->endOfWeek()->toDateString();
                $date_label = 'Semana Actual';
                break;
            case 4:
                $date_from = Carbon::now()->subWeeks(1)->startOfWeek()->toDateString();
                $date_to = Carbon::now()->subWeeks(1)->endOfWeek()->toDateString();
                $date_label = 'Semana Anterior';
                break;
            case 5:
                $date_from = Carbon::now()->startOfMonth()->toDateString();
                $date_to = Carbon::now()->endOfMonth()->toDateString();
                $date_label = 'Mes Actual';
                break;
            case 6:
                $date_from = Carbon::now()->subMonths(1)->startOfMonth()->toDateString();
                $date_to = Carbon::now()->subMonths(1)->endOfMonth()->toDateString();
                $date_label = 'Mes Anterior';
                break;
            case 7:
                $date_now = Carbon::now();
                $month = $date_now->month;
                $trimestre_actual = 3 + ceil($month/3);
                $trimestre_anterior = $trimestre_actual - 1;
                $diff_year = ceil($trimestre_anterior / 3);

                $trimestre = $trimestre_anterior % 4;
                if($diff_year == 1)
                {
                    $year = ($date_now->year - 1);
                    $monthBegin = 3 * ($trimestre) + 1;
                    $monthEnd = 3 * ($trimestre + 1);
                    $dateBegin = Carbon::createFromFormat("Y-n-d", $year."-".$monthBegin."-01");
                    $dateEnd = Carbon::createFromFormat("Y-n-d", $year."-".$monthEnd."-01");
                    $dateEnd->endOfMonth();
                } else {
                    $year = ($date_now->year);
                    $monthBegin = 3 * ($trimestre) + 1;
                    $monthEnd = 3 * ($trimestre + 1);
                    $dateBegin = Carbon::createFromFormat("Y-n-d", $year."-".$monthBegin."-01");
                    $dateEnd = Carbon::createFromFormat("Y-n-d", $year."-".$monthEnd."-01");
                    $dateEnd->endOfMonth();
                }
                $date_from = $dateBegin->toDateString();
                $date_to = $dateEnd->toDateString();
                $date_label = 'Ultimo Trimestre';
                break;
            case 10:
                $date_now = Carbon::now();
                $month = $date_now->month;
                $trimestre_actual = ceil($month/3) - 1;

                $year = $date_now->year;
                $monthBegin = 3 * ($trimestre_actual) + 1;
                $monthEnd = 3 * ($trimestre_actual + 1);
                $dateBegin = Carbon::createFromFormat("Y-n-d", $year."-".$monthBegin."-01");
                $dateEnd = Carbon::createFromFormat("Y-n-d", $year."-".$monthEnd."-01");
                $dateEnd->endOfMonth();

                $date_from = $dateBegin->toDateString();
                $date_to = $dateEnd->toDateString();
                $date_label = 'Trimestre Actual';
                break;
            case 8:
                $date_from = Carbon::now()->subYears(1)->startOfYear()->toDateString();
                $date_to = Carbon::now()->subYears(1)->endOfYear()->toDateString();
                $date_label = 'Último Año';
                break;
            case 11:
                $date_from = \Carbon\Carbon::now()->startOfYear()->toDateString();
                $date_to = \Carbon\Carbon::now()->endOfYear()->toDateString();
                $date_label = 'Año Actual';
                break;
            case 9:
                $date_from = Session::get('_flash')['date_from_personalice'];
                $date_to = Session::get('_flash')['date_to_personalice'];
                $date_label = 'Personalizado';
                break;
            default:
                $date_from = \Carbon\Carbon::now()->toDateString();
                $date_to = $date_from;
                $date_label = 'Hoy';
                break;
        }

        $dateInfo = array();
        $dateInfo["date_from"] = $date_from;
        $dateInfo["date_to"] = $date_to;
        $dateInfo["date_label"] = $date_label;
        return $dateInfo;
    }

    private function getLabelsPlot($data_calculos)
    {
        $date_from = $data_calculos["date_from"];
        $date_to = $data_calculos["date_to"];
        $interval = $data_calculos["interval"];

        $monthsNames = array(1=>"Enero", 2=>"Febrero", 3=>"Marzo", 4=>"Abril",
            5=>"Mayo", 6=>"Junio", 7=>"Julio", 8=>"Agosto", 9=>"Septiembre",
            10=>"Octubre", 11=>"Noviembre", 12=>"Diciembre");

        $daysNames = array("1"=>"Lunes", "2"=>"Martes", "3"=>"Miercoles",
            "4"=>"Jueves","5"=>"Viernes", "6"=>"Sabado", "7"=>"Domingo");

        $aux_label = "";
        $aux_interval = "";
        switch ($interval){
            case 1:
                $date_label = 'Ayer';

                $period = new CarbonPeriod($date_from." 00:00:00", '1 hour', $date_to." 23:00:00");
                foreach ($period as $key => $date) {
                    $interval_keys[] = $date->format("Y-m-d-H");

                    $interval_values[] = ($date->hour + 1).":00";
                }

                $aux_label = "Hora: ";
                break;
            case 2:
                $date_label = 'Hoy';

                $period = new CarbonPeriod($date_from." 00:00:00", '1 hour', $date_to." 23:00:00");
                foreach ($period as $key => $date) {
                    $interval_keys[] = $date->format("Y-m-d-H");
                    $interval_values[] = ($date->hour + 1).":00";
                }

                $aux_label = "Hora: ";
                break;
            case 3:
                $date_label = 'Semana Actual';

                $period = new CarbonPeriod($date_from, '1 days', $date_to);
                foreach ($period as $key => $date) {
                    $interval_keys[] = $date->format("Y-m-d");
                    $interval_values[] = $daysNames[$date->dayOfWeekIso];
                }

                break;
            case 4:
                $date_label = 'Semana Anterior';

                $period = new CarbonPeriod($date_from, '1 days', $date_to);
                foreach ($period as $key => $date) {
                    $interval_keys[] = $date->format("Y-m-d");
                    $interval_values[] = $daysNames[$date->dayOfWeekIso];
                }

                break;
            case 5:
                $date_label = 'Mes Actual';

                $period = new CarbonPeriod($date_from, '1 days', $date_to);
                foreach ($period as $key => $date) {
                    $interval_keys[] = $date->format("Y-m-d");
                    $interval_values[] = $date->day;
                }
                $aux_label = "Día ";
                $aux_interval = 1;
                break;
            case 6:
                $date_label = 'Mes Anterior';

                $interval_keys = array();
                $interval_values = array();

                $period = new CarbonPeriod($date_from, '1 days', $date_to);
                foreach ($period as $key => $date) {
                    $interval_keys[] = $date->format("Y-m-d");
                    $interval_values[] = $date->day;
                }
                $aux_label = "Día ";
                $aux_interval = 1;
                break;
            case 7:
                $date_label = 'Ultimo Trimestre';

                $interval_keys = array();
                $interval_values = array();

                $period = new CarbonPeriod($date_from, '1 month', $date_to);
                foreach ($period as $key => $date) {
                    $interval_keys[] = $date->format("Y-m");
                    $interval_values[] = $monthsNames[$date->month]."(".$date->year.")";
                }

                break;
            case 10:
                $date_label = 'Trimestre Actual';

                $interval_keys = array();
                $interval_values = array();

                $period = new CarbonPeriod($date_from, '1 month', $date_to);
                foreach ($period as $key => $date) {
                    $interval_keys[] = $date->format("Y-m");
                    $interval_values[] = $monthsNames[$date->month]."(".$date->year.")";
                }

                break;
            case 8:
                $date_label = 'Último Año';

                $interval_keys = array();
                $interval_values = array();

                $period = new CarbonPeriod($date_from, '1 month', $date_to);
                foreach ($period as $key => $date) {
                    $interval_keys[] = $date->format("Y-m");
                    $interval_values[] = $monthsNames[$date->month]."(".$date->year.")";
                }

                break;
            case 11:
                $date_label = 'Año Actual';

                $interval_keys = array();
                $interval_values = array();


                $period = new CarbonPeriod($date_from, '1 month', $date_to);
                foreach ($period as $key => $date) {
                    $interval_keys[] = $date->format("Y-m");
                    $interval_values[] = $monthsNames[$date->month]."(".$date->year.")";
                }

                break;
            case 9:
                $date_label = 'Personalizado';

                $period = new CarbonPeriod($date_from, '1 days', $date_to);
                foreach ($period as $key => $date) {
                    $interval_keys[] = $date->format("Y-m-d");
                    $interval_values[] = $date->format("Y-m-d");
                }

                break;
            default:
                $date_label = 'Hoy';

                $period = new CarbonPeriod($date_from." 00:00:00", '1 hour', $date_to." 23:00:00");
                foreach ($period as $key => $date) {
                    $interval_keys[] = $date->format("Y-m-d-H");
                    $date->addHour(1);
                    $interval_values[] = ($date->hour + 1).":00";
                }

                $aux_label = "Hora: ";
                break;
        }

        $dateInterval = array();
        $dateInterval["interval_keys"] = $interval_keys;
        $dateInterval["interval_values"] = $interval_values;
        $dateInterval["aux_label"] = $aux_label;
        $dateInterval["interval"] = $aux_interval;
        return $dateInterval;
    }

    private function createMarketPlots($data_calculos)
    {
        $interval = $data_calculos["interval"];
        $data_labels = $data_calculos["data_labels"];
        $precios_energia = $data_calculos["precios_energia"];


        $dataREE = array_fill_keys($data_labels["interval_keys"], 0.0);
        $countREE = array_fill_keys($data_labels["interval_keys"], 0);

        $dataOMIE = array_fill_keys($data_labels["interval_keys"], 0.0);
        $countOMIE = array_fill_keys($data_labels["interval_keys"], 0);

        $dataCliente = array_fill_keys($data_labels["interval_keys"], 0.0);
        $countCliente = array_fill_keys($data_labels["interval_keys"], 0);

        foreach($precios_energia as $precios)
        {
          if($precios->time_start !== "24:00:00")
          {
            $keyData = $this->getKeyPlot($interval, $precios->date, $precios->time_start);
            if($precios->ree !== null )
            {
                $dataREE[$keyData] += $precios->ree;
                $countREE[$keyData] ++;
            }
            if($precios->omie !== null )
            {
                $dataOMIE[$keyData] += $precios->omie;
                $countOMIE[$keyData] ++;
            }
            if($precios->cliente !== null)
            {
                $dataCliente[$keyData] += $precios->cliente;
                $countCliente[$keyData] ++;
            }
          }
        }

        foreach($countREE as $keyData => $numberData)
        {
            if($numberData > 0)
            {
                $dataREE[$keyData] /= $numberData;
                $dataREE[$keyData] = round($dataREE[$keyData], 2);
            }
        }

        foreach($countOMIE as $keyData => $numberData)
        {
            if($numberData > 0)
            {
                $dataOMIE[$keyData] /= $numberData;
                $dataOMIE[$keyData] = round($dataOMIE[$keyData], 2);
            }
        }

        foreach($countCliente as $keyData => $numberData)
        {
            if($numberData > 0)
            {
                $dataCliente[$keyData] /= $numberData;
                $dataCliente[$keyData] = round($dataCliente[$keyData], 2);
            }
        }

        $dataREE = array_values($dataREE);
        $dataOMIE = array_values($dataOMIE);
        $dataCliente = array_values($dataCliente);

        $plotTotal = array();
        $plotTotal["name"] = "Mercado Energético ( OMIE | REE | Cliente )";
        $plotTotal["labels"] = json_encode($data_labels["interval_values"]);
        $plotTotal["series"] = array();

        $serie = array();
        $serie["name"] = "REE";
        $serie["color"] = "#000000";
        $serie["type"] = "area";
        $serie["values"] = json_encode($dataREE);
        $serie["aux_label"] = $data_labels["aux_label"];
        $plotTotal["series"][] = $serie;

        $serie = array();
        $serie["name"] = "OMIE";
        $serie["color"] = "#0066CC";
        $serie["type"] = "area";
        $serie["values"] = json_encode($dataOMIE);
        $serie["aux_label"] = "";
        $plotTotal["series"][] = $serie;

        $serie = array();
        $serie["name"] = "Cliente";
        $serie["color"] = "#FF0000";
        $serie["type"] = "spline";
        $serie["values"] = json_encode($dataCliente);
        $serie["aux_label"] = "";
        $plotTotal["series"][] = $serie;

        $plotTotal["dataOMIE"] = $dataOMIE;
        $plotTotal["dataREE"] = $dataREE;
        $plotTotal["dataCliente"] = $dataCliente;
        $plotTotal["labelPeriodos"] = $data_labels["interval_values"];

        $dataPlotting = array();
        $dataPlotting["total"] = $plotTotal;
        return $dataPlotting;
    }

    private function getKeyPlot($interval, $date, $time)
    {
        $date = Carbon::createFromFormat("Y-m-d H:i:s", $date." ".$time);
        $key = "";
        switch ($interval){
            case 1:
                $date_label = 'Ayer';
                $key = $date->format("Y-m-d-H");
                break;
            case 2:
                $date_label = 'Hoy';
                $key = $date->format("Y-m-d-H");
                break;
            case 3:
                $date_label = 'Semana Actual';
                $key = $date->format("Y-m-d");
                break;
            case 4:
                $date_label = 'Semana Anterior';
                $key = $date->format("Y-m-d");
                break;
            case 5:
                $date_label = 'Mes Actual';
                $key = $date->format("Y-m-d");
                break;
            case 6:
                $date_label = 'Mes Anterior';
                $key = $date->format("Y-m-d");
                break;
            case 7:
                $date_label = 'Ultimo Trimestre';
                $key = $date->format("Y-m");
                break;
            case 10:
                $date_label = 'Trimestre Actual';
                $key = $date->format("Y-m");
                break;
            case 8:
                $date_label = 'Último Año';
                $key = $date->format("Y-m");
                break;
            case 11:
                $date_label = 'Año Actual';
                $key = $date->format("Y-m");
                break;
            case 9:
                $date_label = 'Personalizado';
                $key = $date->format("Y-m-d");
                break;
            default:
                $date_label = 'Hoy';
                $key = $date->format("Y-m-d-H");
                break;
        }
        return $key;
    }

    public function exportCSVMercado(Request $request){

      $dataPlotting = array();
      $date_from = $request->date_from;
      $date_to = $request->date_to;
      $contador_label = $request->contador_label;
      $periodo = unserialize($request->labelPeriodos);
      $OMIE = unserialize($request->dataOMIE);
      $REE = unserialize($request->dataREE);
      $Cliente = unserialize($request->dataCliente);

      $filename = "Datos_Mercado_".$contador_label."_".$date_from."_".$date_to.".csv";
      $handle = fopen($filename, 'w+');


        fputcsv($handle, array('Periodo', 'Omie', 'REE', 'Cliente'),';');


        $avg_OMIE = number_format(array_sum($OMIE)/count(array_filter($OMIE)),2,',','.');
        $avg_REE = number_format(array_sum($REE)/count(array_filter($REE)),2,',','.');
        $avg_Cliente = number_format(array_sum($Cliente)/count(array_filter($Cliente)),2,',','.');

      for($i=0;$i<count($periodo);$i++)  {

        $OMIE[$i] = number_format($OMIE[$i],2,',','.');
        $REE[$i] = number_format($REE[$i],2,',','.');
        $Cliente[$i] = number_format($Cliente[$i],2,',','.');

            fputcsv($handle, array(
              $periodo[$i], $OMIE[$i], $REE[$i], $Cliente[$i]
              ),';');

            }

fputcsv($handle, array(
  "Precio Medio", $avg_OMIE, $avg_REE, $avg_Cliente
  ),';');

      fclose($handle);

      $headers = array(
                      'Content-Type' => 'text/csv',
      );


      return Response::download($filename, $filename, $headers);

    }

}
