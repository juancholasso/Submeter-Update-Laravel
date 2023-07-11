<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Count;
use App\User;
use Response;
use Auth;
use Session;

class ConsumoEnergiaController extends Controller
{
    public function ConsumoEnergia(Request $request, $id)
    {
        $dates =array();
        $eje = array();
        $eje2 = array();
        $consumo_activa = array();
        $consumo_induc = array();
        $consumo_cap = array();
        $totalInduc = 0;
        $totalCapa = 0;
        $totalActiva = 0;
        $generacion2 = array();
        $db_Generacion = array();

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
                                `SOCIAL DOMICILIO` social_domicilio,
                                `SUMINISTRO DEL  DOMICILIO` suministro_del_domicilio, CUPS, CIF,
                                `DISTRIBUIDORA EMPRESA` distribuidora_empresa, `COMERCIALIZADORA EMPRESA` comercializadora_empresa,
                                `CONTACTO DE  PERSONA` contacto_persona, `TELÉFONO`, `CONTACTO O  AYUDA` contacto_ayuda, `TARIFA`'))
                        ->first();

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
                                    ->select(\DB::raw("Periodo as periodo, MAX(`Potencia_contratada`) as potencia_contratada,
                                        RIGHT(Periodo,1) as periodo_int"))
                                    ->where('date_start','<=',$date_from)->orWhere('date_end','>=',$date_to)
                                    ->groupBy('Periodo')->get();

        $vector_potencia = array();
        foreach($potencia_contratada as $potencia){
            $idx_periodo = intval($potencia->periodo_int) - 1;
            $vector_potencia[$idx_periodo] = doubleval($potencia->potencia_contratada);
        }

        $data_consumo = $db->table("ZPI_Contador_Festivos_Periodos")->select(\DB::raw("date, time, RIGHT(Periodo,1) as periodo,
                                    `EAct imp(kWh)` as energia_activa,
                                    `ERInd imp(kvarh)` as energia_reactiva_inductiva,
                                    ABS(`ERCap imp(kvarh)`) as energia_reactiva_capacitiva,
                                    ABS(`EAct exp(kWh)`) as generacion_energia_activa,
                                    ABS(`ERInd exp(kvarh)`) as generacion_energia_reactiva_inductiva,
                                    ABS(`ERCap exp(kvarh)`) as generacion_energia_reactiva_capacitiva
                                    "))
                            ->where("date", ">=", $date_from)
                            ->where("date", "<=", $date_to)->get();

        $data_calculos = compact("date_from", "date_to", "interval", "data_consumo", "vector_potencia");

        $data_labels = $this->getLabelsPlot($data_calculos);
        $data_calculos["data_labels"] = $data_labels;


        $dataPlotting = $this->createConsumptionPlots($data_calculos);

        $dataConsumo = $this->createDataConsumption($data_calculos);

        $dataPeriodo = [];
        $dataPeriodo['interval'] = $interval;
        $dataPeriodo['date_from'] = $date_from;
        $dataPeriodo['date_to'] = $date_to;
        $dataSubperiodo = $this->getInfoSubPeriodo($dataPeriodo);

        $user = Auth::user();//usuario logeado
        $titulo = 'Consumo de Energía';//Título del content
        $contador_label = $contador2->count_label;

        if(!is_null($db->table('Area_Cliente')->select(\DB::raw('`LOGOTIPO` logo'))->first()))
        {
            $dir_image_count = $db->table('Area_Cliente')->select(\DB::raw('`LOGOTIPO` logo'))->first()->logo;
        }
        else
        {
            $dir_image_count = $db->table('Area_Cliente')->select(\DB::raw('`LOGOTIPO` logo'))->first();
        }

        $aux_current_count = \DB::select("SELECT label_current_count FROM current_count WHERE user_id = ".$id);

        if(is_null($aux_current_count) || empty($aux_current_count))
        {
            \DB::insert("INSERT INTO current_count (label_current_count,user_id) VALUES ('".$current_count."',".$id.")");
        }
        else
        {
                \DB::update("UPDATE current_count SET label_current_count = '".$current_count."' WHERE user_id = ".$id);
        }

        if(!is_null($db->table('Area_Cliente')->select(\DB::raw('`LOGOTIPO` logo'))->first()))
        {
            $dir_image_count = $db->table('Area_Cliente')->select(\DB::raw('`LOGOTIPO` logo'))->first()->logo;
        }
        else
        {
            $db->table('Area_Cliente')->select(\DB::raw('`LOGOTIPO` logo'))->first();
        }

        if(($id != 0 && Auth::user()->id == $id) || Auth::user()->tipo == 1)
        {
            $user = User::where('id',$id)->get()->first();
            if(Auth::user()->tipo != 1)
            {
                $ctrl = 0;
            }
            else
            {
                $ctrl = 1;
            }

            return view('consumo_energia.consumo_energia', compact("contador2", "contador_label", 'ctrl', "data_calculos" ,"dataConsumo", "dataPlotting", "dataSubperiodo",
                            "date_from", "date_to", "dir_image_count" ,"domicilio", 'id', "label_intervalo", 'titulo', "tipo_count", 'user'));
        }
        return \Redirect::to('https://submeter.es/');
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

    private function createDataConsumption($data_calculos)
    {
        $interval = $data_calculos["interval"];
        $data_labels = $data_calculos["data_labels"];
        $data_consumo = $data_calculos["data_consumo"];
        $vector_potencia = $data_calculos["vector_potencia"];

        $dataEActiva = array_fill_keys($data_labels["data_keys"], array_fill(0, count($vector_potencia), 0.0));
        $dataEActivaPeriodo = array_fill(0, count($vector_potencia), 0.0);
        $dataEActivaTotales = array_fill_keys($data_labels["data_keys"], 0.0);
        $total_activa = 0.0;

        $dataEGeneracion = array_fill_keys($data_labels["data_keys"], array_fill(0, count($vector_potencia), 0.0));
        $dataEGeneracionPeriodo = array_fill(0, count($vector_potencia), 0.0);
        $dataEGeneracionTotales = array_fill_keys($data_labels["data_keys"], 0.0);
        $total_generacion = 0.0;

        $dataEBalance = array_fill_keys($data_labels["data_keys"], array_fill(0, count($vector_potencia), 0.0));
        $dataEBalancePeriodo = array_fill(0, count($vector_potencia), 0.0);
        $dataEBalanceTotales = array_fill_keys($data_labels["data_keys"], 0.0);
        $total_balance = 0.0;

        $displayValues = [];
        foreach($data_labels["data_keys"] as $idx => $value)
        {
            $displayValues[$value] = $data_labels["data_values"][$idx];
        }

        foreach($data_consumo as $data)
        {
            $idxPeriodo = $data->periodo - 1;
            $keyData = $this->getKeyData($interval, $data->date, $data->time);

            $dataEActiva[$keyData][$idxPeriodo] += $data->energia_activa;
            $dataEActivaPeriodo[$idxPeriodo] += $data->energia_activa;
            $dataEActivaTotales[$keyData] += $data->energia_activa;
            $total_activa += $data->energia_activa;

            $dataEGeneracion[$keyData][$idxPeriodo] += $data->generacion_energia_activa;
            $dataEGeneracionPeriodo[$idxPeriodo] += $data->generacion_energia_activa;
            $dataEGeneracionTotales[$keyData] += $data->generacion_energia_activa;
            $total_generacion += $data->generacion_energia_activa;

            $dataEBalance[$keyData][$idxPeriodo] += $data->generacion_energia_activa - $data->energia_activa;
            $dataEBalancePeriodo[$idxPeriodo] += $data->generacion_energia_activa - $data->energia_activa;
            $dataEBalanceTotales[$keyData] += $data->generacion_energia_activa - $data->energia_activa;
        }
        $total_balance = $total_generacion - $total_activa;

        $dataEActivaPorc = [];
        $dataEActivaPeriodoPorc = [];
        $dataEGeneracionPorc = [];
        $dataEGeneracionPeriodoPorc = [];
        for($idxPeriodo = 0; $idxPeriodo < count($vector_potencia); $idxPeriodo++)
        {
            foreach($data_labels["data_keys"] as $keyData)
            {
                if($dataEActivaTotales[$keyData] > 0)
                {
                    $dataEActivaPorc[$keyData][$idxPeriodo] = 100.0 * $dataEActiva[$keyData][$idxPeriodo];
                    $dataEActivaPorc[$keyData][$idxPeriodo] /= $dataEActivaTotales[$keyData];
                }
                else
                {
                    $dataEActivaPorc[$keyData][$idxPeriodo] = 0.0;
                }

                if($dataEGeneracionTotales[$keyData] > 0)
                {
                    $dataEGeneracionPorc[$keyData][$idxPeriodo] = 100.0 * $dataEGeneracion[$keyData][$idxPeriodo];
                    $dataEGeneracionPorc[$keyData][$idxPeriodo] /= $dataEGeneracionTotales[$keyData];
                }
                else
                {
                    $dataEGeneracionPorc[$keyData][$idxPeriodo] = 0.0;
                }
            }

            if($total_activa > 0)
            {
                $dataEActivaPeriodoPorc[$idxPeriodo] = 100 * $dataEActivaPeriodo[$idxPeriodo] / $total_activa;
            }
            else
            {
                $dataEActivaPeriodoPorc[$idxPeriodo] = 0.0;
            }

            if($total_generacion > 0)
            {
                $dataEGeneracionPeriodoPorc[$idxPeriodo] = 100 * $dataEGeneracionPeriodo[$idxPeriodo] / $total_generacion;
            }
            else
            {
                $dataEGeneracionPeriodoPorc[$idxPeriodo] = 0.0;
            }
        }

        $dataCompsumption = [];
        $dataCompsumption["aux_label"] = $data_calculos["data_labels"]["aux_label"];
        $dataCompsumption["EActiva"] = $dataEActiva;
        $dataCompsumption["EActivaPorc"] = $dataEActivaPorc;
        $dataCompsumption["EActivaPeriodo"] = $dataEActivaPeriodo;
        $dataCompsumption["EActivaPeriodoPorc"] = $dataEActivaPeriodoPorc;
        $dataCompsumption["EActivaTotales"] = $dataEActivaTotales;
        $dataCompsumption["EGeneracion"] = $dataEGeneracion;
        $dataCompsumption["EGeneracionPorc"] = $dataEGeneracionPorc;
        $dataCompsumption["EGeneracionPeriodo"] = $dataEGeneracionPeriodo;
        $dataCompsumption["EGeneracionPeriodoPorc"] = $dataEGeneracionPeriodoPorc;
        $dataCompsumption["EGeneracionTotales"] = $dataEGeneracionTotales;
        $dataCompsumption["EBalance"] = $dataEBalance;
        $dataCompsumption["EBalancePeriodo"] = $dataEBalancePeriodo;
        $dataCompsumption["EBalanceTotales"] = $dataEBalanceTotales;
        $dataCompsumption["totalActiva"] = $total_activa;
        $dataCompsumption["totalGeneracion"] = $total_generacion;
        $dataCompsumption["totalBalance"] = $total_balance;
        $dataCompsumption["displayValues"] = $displayValues;
        return $dataCompsumption;
    }

    public static function createConsumptionPlots($data_calculos)
    {
        $interval = $data_calculos["interval"];
        $data_labels = $data_calculos["data_labels"];
        $data_consumo = $data_calculos["data_consumo"];

        $dataActiva = array_fill_keys($data_labels["interval_keys"], 0.0);
        $dataReactivaInductiva = array_fill_keys($data_labels["interval_keys"], 0.0);
        $dataReactivaCapacitiva = array_fill_keys($data_labels["interval_keys"], 0.0);
        $dataGeneracion = array_fill_keys($data_labels["interval_keys"], 0.0);
        $dataGeneracionN = array_fill_keys($data_labels["interval_keys"], 0.0);
        $dataGeneracionReactivaInductiva = array_fill_keys($data_labels["interval_keys"], 0.0);
        $dataGeneracionReactivaCapacitiva = array_fill_keys($data_labels["interval_keys"], 0.0);
        $dataBalance = array_fill_keys($data_labels["interval_keys"], 0.0);
        $total_activa = 0.0;
        $total_reactiva_inductiva = 0.0;
        $total_reactiva_capacitiva = 0.0;
        $total_generacion = 0.0;
        $total_generacion_reactiva_inductiva = 0.0;
        $total_generacion_reactiva_capacitiva = 0.0;
        $max_energia_activa = 0.0;
        $max_energia_balance = 0.0;


        foreach($data_consumo as $data)
        {
            $keyData = ConsumoEnergiaController::getKeyPlot($interval, $data->date, $data->time);
            if(array_key_exists($keyData, $dataActiva))
            {
                $dataActiva[$keyData] += $data->energia_activa;
                $total_activa += $data->energia_activa;
                if($data->energia_activa > $max_energia_activa)
                {
                    $max_energia_activa = $data->energia_activa;
                }
            }
            if(array_key_exists($keyData, $dataReactivaInductiva))
            {
                $dataReactivaInductiva[$keyData] += $data->energia_reactiva_inductiva;
                $total_reactiva_inductiva += $data->energia_reactiva_inductiva;
                if($data->energia_reactiva_inductiva > $max_energia_activa)
                {
                    $max_energia_activa = $data->energia_reactiva_inductiva;
                }
            }
            if(array_key_exists($keyData, $dataReactivaCapacitiva))
            {
                $dataReactivaCapacitiva[$keyData] += $data->energia_reactiva_capacitiva;
                $total_reactiva_capacitiva += $data->energia_reactiva_capacitiva;
                if($data->energia_reactiva_capacitiva > $max_energia_activa)
                {
                    $max_energia_activa = $data->energia_reactiva_capacitiva;
                }

            }
            if(array_key_exists($keyData, $dataGeneracion))
            {
                $dataGeneracion[$keyData] += $data->generacion_energia_activa;
                $total_generacion += $data->generacion_energia_activa;
                if($data->generacion_energia_activa > $max_energia_activa)
                {
                    $max_energia_activa = $data->generacion_energia_activa;
                }
            }
            if(array_key_exists($keyData, $dataGeneracionReactivaInductiva))
            {
                $dataGeneracionReactivaInductiva[$keyData] += $data->generacion_energia_reactiva_inductiva;
                $total_generacion_reactiva_inductiva += $data->generacion_energia_reactiva_inductiva;
                if($data->generacion_energia_reactiva_inductiva > $max_energia_activa)
                {
                    $max_energia_activa = $data->generacion_energia_reactiva_inductiva;
                }
            }
            if(array_key_exists($keyData, $dataGeneracionReactivaCapacitiva))
            {
                $dataGeneracionReactivaCapacitiva[$keyData] += $data->generacion_energia_reactiva_capacitiva;
                $total_generacion_reactiva_capacitiva += $data->generacion_energia_reactiva_capacitiva;
                if($data->generacion_energia_reactiva_capacitiva > $max_energia_activa)
                {
                    $max_energia_activa = $data->generacion_energia_reactiva_capacitiva;
                }
            }
        }

        foreach($dataGeneracion as $keyData => $data_generacion) {
            $dataGeneracionN[$keyData] = -$dataGeneracion[$keyData];
            $dataBalance[$keyData] = $dataActiva[$keyData] - $dataGeneracion[$keyData];
            if($dataBalance[$keyData] > $max_energia_balance)
            {
                $max_energia_balance = $dataBalance[$keyData];
            }
        }

        $plotActiva = array();
        $plotActiva["name"] = "Consumo Energía Activa";
        $plotActiva["suffix"] = "kWh";
        $plotActiva["time_label"] = $data_labels["aux_label"];
        $plotActiva["index_label"] = $data_labels["index_label"];
        $plotActiva["labels"] = json_encode($data_labels["interval_values"]);
        $plotActiva["series"] = array();

        $serie = array();
        $serie["name"] = "Energía Activa";
        $serie["color"] = "#004165";
        $serie["suffix"] = "kWh";
        $serie["values"] = json_encode(array_values($dataActiva));
        $serie["aux_label"] = "Total de Energía Activa";
        $serie["total"] = $total_activa;
        $plotActiva["series"][] = $serie;

        $plotReactiva = array();
        $plotReactiva["name"] = "Consumo Energía Reactiva";
        $plotReactiva["suffix"] = "kVArh";
        $plotReactiva["time_label"] = $data_labels["aux_label"];
        $plotReactiva["index_label"] = $data_labels["index_label"];
        $plotReactiva["labels"] = json_encode($data_labels["interval_values"]);
        $plotReactiva["series"] = array();

        $serie = array();
        $serie["name"] = "Energía Reactiva Inductiva";
        $serie["color"] = "#B9C9D0";
        $serie["suffix"] = "kVArh";
        $serie["values"] = json_encode(array_values($dataReactivaInductiva));
        $serie["aux_label"] = "Total Consumo Inductiva";
        $serie["total"] = $total_reactiva_inductiva;
        $plotReactiva["series"][] = $serie;

        $serie = array();
        $serie["name"] = "Energía Reactiva Capacitiva";
        $serie["color"] = "#7D9AAA";
        $serie["suffix"] = "kVArh";
        $serie["values"] = json_encode(array_values($dataReactivaCapacitiva));
        $serie["aux_label"] = " \n\n\n\n\n\n\n\n\n\n\n\nTotal Consumo Capacitiva";
        $serie["total"] = $total_reactiva_capacitiva;
        $plotReactiva["series"][] = $serie;

//Generacion
        $plotGeneracion = array();
        $plotGeneracion["name"] = "Generación Energía";
        $plotGeneracion["suffix"] = "kWh";
        $plotGeneracion["time_label"] = $data_labels["aux_label"];
        $plotGeneracion["index_label"] = $data_labels["index_label"];
        $plotGeneracion["labels"] = json_encode($data_labels["interval_values"]);
        $plotGeneracion["series"] = array();

        $serie = array();
        $serie["name"] = "Generación Energía";
        $serie["color"] = "#004165";
        $serie["suffix"] = "kWh";
        $serie["values"] = json_encode(array_values($dataGeneracion));
        $serie["aux_label"] = "Total Generación Energía";
        $serie["total"] = $total_generacion;
        $plotGeneracion["series"][] = $serie;

        $serie = array();
        $serie["name"] = "Energía Reactiva Inductiva";
        $serie["color"] = "#B9C9D0";
        $serie["suffix"] = "kVArh";
        $serie["values"] = json_encode(array_values($dataGeneracionReactivaInductiva));
        $serie["aux_label"] = " \n\n\n\n\n\n\n\n\n\n\n\nTotal Generación Inductiva";
        $serie["total"] = $total_generacion_reactiva_inductiva;
        $plotGeneracion["series"][] = $serie;

        $serie = array();
        $serie["name"] = "Energía Reactiva Capacitiva";
        $serie["color"] = "#7D9AAA";
        $serie["suffix"] = "kVArh";
        $serie["values"] = json_encode(array_values($dataGeneracionReactivaCapacitiva));
        $serie["aux_label"] = " \n\n\n\n\n\n\n\n\n\n\n\nTotal Generación Capacitiva";
        $serie["total"] = $total_generacion_reactiva_capacitiva;
        $plotGeneracion["series"][] = $serie;

        $plotActivaReactiva = array();
        $plotActivaReactiva["name"] = "Consumo Energía Activa";
        $plotActivaReactiva["suffix"] = "kWh";
        $plotActivaReactiva["time_label"] = $data_labels["aux_label"];
        $plotActivaReactiva["index_label"] = $data_labels["index_label"];
        $plotActivaReactiva["labels"] = json_encode($data_labels["interval_values"]);
        $plotActivaReactiva["series"] = array();

        $serie = array();
        $serie["name"] = "Energía Activa";
        $serie["color"] = "#004165";
        $serie["suffix"] = "Kwh";
        $serie["values"] = json_encode(array_values($dataActiva));
        $serie["aux_label"] = "Total de Energía Activa";
        $serie["total"] = $total_activa;
        $plotActivaReactiva["series"][] = $serie;

        $serie = array();
        $serie["name"] = "Energía Reactiva Inductiva";
        $serie["color"] = "#B9C9D0";
        $serie["suffix"] = "kVArh";
        $serie["values"] = json_encode(array_values($dataReactivaInductiva));
        $serie["aux_label"] = " \n\n\n\n\n\n\n\n\n\n\n\nTotal Consumo Inductiva";
        $serie["total"] = $total_reactiva_inductiva;
        $plotActivaReactiva["series"][] = $serie;

        $serie = array();
        $serie["name"] = "Energía Reactiva Capacitiva";
        $serie["color"] = "#7D9AAA";
        $serie["suffix"] = "kVArh";
        $serie["values"] = json_encode(array_values($dataReactivaCapacitiva));
        $serie["aux_label"] = " \n\n\n\n\n\n\n\n\n\n\n\nTotal Consumo Capacitiva";
        $serie["total"] = $total_reactiva_capacitiva;
        $plotActivaReactiva["series"][] = $serie;

        $plotConsumo = array();
        $plotConsumo["time_label"] = $data_labels["aux_label"];
        $plotConsumo["labels"] = json_encode($data_labels["interval_values"]);
        $plotConsumo["max"] = $max_energia_balance;
        $plotConsumo["series"] = array();

        $serie = array();
        $serie["values"] = json_encode(array_values($dataActiva));
        $plotConsumo["series"][] = $serie;

        $serie = array();
        $serie["values"] = json_encode(array_values($dataReactivaInductiva));
        $plotConsumo["series"][] = $serie;

        $serie = array();
        $serie["values"] = json_encode(array_values($dataReactivaCapacitiva));
        $plotConsumo["series"][] = $serie;

        $plotBalance = array();
        $plotBalance["time_label"] = $data_labels["aux_label"];
        $plotBalance["labels"] = json_encode($data_labels["interval_values"]);
        $plotBalance["max"] = $max_energia_activa;
        $plotBalance["series"] = array();

        $serie = array();
        $serie["values"] = json_encode(array_values($dataActiva));
        $plotBalance["series"][] = $serie;

        $serie = array();
        $serie["values"] = json_encode(array_values($dataGeneracionN));
        $plotBalance["series"][] = $serie;

        $serie = array();
        $serie["values"] = json_encode(array_values($dataBalance));
        $plotBalance["series"][] = $serie;

        $dataPlotting = array();
        $dataPlotting["activa"] = $plotActiva;
        $dataPlotting["activareactiva"] = $plotActivaReactiva;
        $dataPlotting["reactiva"] = $plotReactiva;
        $dataPlotting["generacion"] = $plotGeneracion;
        $dataPlotting["consumo"] = $plotConsumo;
        $dataPlotting["balance"] = $plotBalance;
        return $dataPlotting;
    }

    public static function getLabelsPlot($data_calculos)
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
        $index_label = "";
        switch ($interval){
            case 1:
                $date_label = 'Ayer';

                $period = new CarbonPeriod($date_from." 00:00:00", '15 minutes', $date_to." 23:45:00");
                foreach ($period as $key => $date) {
                    $interval_keys[] = $date->format("Y-m-d-H-i");
                    $interval_values[] = $date->format("H:i");
                }
                $interval_keys[] = $date->format("Y-m-d-23-59");
                $interval_values[] = $date->format("23:59");

                $data_keys = [];
                $data_values = [];
                $period = new CarbonPeriod($date_from." 00:00:00", '1 Hour', $date_to." 23:00:00");
                foreach ($period as $key => $date) {
                    $data_keys[] = $date->format("Y-m-d-H");
                    $data_values[] = $date->format("H:00");
                }

                $aux_label = "Hora";
                break;
            case 2:
                $date_label = 'Hoy';

                $period = new CarbonPeriod($date_from." 00:00:00", '15 minutes', $date_to." 23:00:00");
                foreach ($period as $key => $date) {
                    $interval_keys[] = $date->format("Y-m-d-H-i");
                    $interval_values[] = $date->format("H:i");
                }
                $interval_keys[] = $date->format("Y-m-d-23-59");
                $interval_values[] = $date->format("23:59");

                $data_keys = [];
                $data_values = [];
                $period = new CarbonPeriod($date_from." 00:00:00", '1 Hour', $date_to." 23:00:00");
                foreach ($period as $key => $date) {
                    $data_keys[] = $date->format("Y-m-d-H");
                    $data_values[] = $date->format("H:00");
                }

                $aux_label = "Hora";
                break;
            case 3:
                $date_label = 'Semana Actual';

                $period = new CarbonPeriod($date_from, '1 days', $date_to);
                foreach ($period as $key => $date) {
                    $interval_keys[] = $date->format("Y-m-d");
                    $interval_values[] = $daysNames[$date->dayOfWeekIso];
                }
                $data_keys = $interval_keys;
                $data_values = $interval_values;
                $aux_label = "Día";
                break;
            case 4:
                $date_label = 'Semana Anterior';

                $period = new CarbonPeriod($date_from, '1 days', $date_to);
                foreach ($period as $key => $date) {
                    $interval_keys[] = $date->format("Y-m-d");
                    $interval_values[] = $daysNames[$date->dayOfWeekIso];
                }
                $data_keys = $interval_keys;
                $data_values = $interval_values;
                $aux_label = "Día";
                break;
            case 5:
                $date_label = 'Mes Actual';

                $period = new CarbonPeriod($date_from, '1 days', $date_to);
                foreach ($period as $key => $date) {
                    $interval_keys[] = $date->format("Y-m-d");
                    $interval_values[] = $date->day;
                }
                $data_keys = $interval_keys;
                $data_values = $interval_values;
                $aux_label = "Día";
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
                $data_keys = $interval_keys;
                $data_values = $interval_values;
                $aux_label = "Día";
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
                $data_keys = $interval_keys;
                $data_values = $interval_values;
                $index_label = "{y}";
                $aux_label = "Mes";
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
                $data_keys = $interval_keys;
                $data_values = $interval_values;
                $index_label = "{y}";
                $aux_label = "Mes";
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
                $data_keys = $interval_keys;
                $data_values = $interval_values;
                $aux_label = "Mes";
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
                $data_keys = $interval_keys;
                $data_values = $interval_values;
                $aux_label = "Mes";
                break;
            case 9:
                $date_label = 'Personalizado';

                $period = new CarbonPeriod($date_from, '1 days', $date_to);
                foreach ($period as $key => $date) {
                    $interval_keys[] = $date->format("Y-m-d");
                    $interval_values[] = $date->format("Y-m-d");
                }
                $data_keys = $interval_keys;
                $data_values = $interval_values;
                break;
            default:
                $date_label = 'Hoy';

                $period = new CarbonPeriod($date_from." 00:00:00", '15 minutes', $date_to." 23:00:00");
                foreach ($period as $key => $date) {
                    $interval_keys[] = $date->format("Y-m-d-H-i");
                    $interval_values[] = $date->format("H:i");
                }
                $interval_keys[] = $date->format("Y-m-d-23-59");
                $interval_values[] = $date->format("23:59");

                $data_keys = [];
                $data_values = [];
                $period = new CarbonPeriod($date_from." 00:00:00", '1 Hour', $date_to." 23:00:00");
                foreach ($period as $key => $date) {
                    $data_keys[] = $date->format("Y-m-d-H");
                    $data_values[] = $date->format("H:00");
                }

                $aux_label = "Hora";
                break;
        }

        $dateInterval = array();
        $dateInterval["interval_keys"] = $interval_keys;
        $dateInterval["interval_values"] = $interval_values;
        $dateInterval["data_keys"] = $data_keys;
        $dateInterval["data_values"] = $data_values;
        $dateInterval["aux_label"] = $aux_label;
        $dateInterval["index_label"] = $index_label;
        return $dateInterval;
    }

    private function getInfoSubPeriodo($dataPeriodo)
    {
        $interval = $dataPeriodo['interval'];
        $date_from = $dataPeriodo['date_from'];
        $date_to = $dataPeriodo['date_to'];

        if($interval == 8 || $interval == 11)
        {
            $periods = CarbonPeriod::create($date_from, '1 month', $date_to);
            $begin_periods = [];
            $end_periods = [];
            foreach($periods as $date)
            {
                $date->addMonth(-3);
                $month = floor(($date->month - 1) / 3);
                $date_begin = $date->year."-".(3 * $month + 1)."-01";
                $date_c = Carbon::createFromFormat("Y-m-d", $date_begin);
                $date_begin = $date_c->toDateString();
                $date_end = $date->year."-".(3 * $month + 3)."-01";
                $date_c = Carbon::createFromFormat("Y-m-d", $date_end);
                $date_end = $date_c->endOfMonth()->toDateString();
                $begin_periods[] = $date_begin;
                $end_periods[] = $date_end;
            }
            $label = "Trimestre Actual";
        }
        else if($interval == 7 || $interval == 10)
        {
            $begin_periods = [];
            $end_periods = [];
            $periods = CarbonPeriod::create($date_from, '1 month', $date_to);
            foreach($periods as $date)
            {
                $date->addMonth(-1);
                $date_begin = $date->startOfMonth()->toDateString();
                $date_end = $date->endOfMonth()->toDateString();
                $begin_periods[] = $date_begin;
                $end_periods[] = $date_end;
            }
            $label = "Mes Actual";
        }
        else if($interval >= 3 && $interval <= 6)
        {
            $begin_periods = [];
            $end_periods = [];
            $periods = CarbonPeriod::create($date_from, '1 day', $date_to);
            foreach($periods as $date)
            {
                $date->addDay(-1);
                $date_begin = $date->toDateString();
                $date_end = $date->toDateString();
                $begin_periods[] = $date_begin;
                $end_periods[] = $date_end;
            }
            $label = "Ayer";
        }
        else
        {
            $begin_periods = [];
            $end_periods = [];
            $label = "N/A";
        }

        $dataSubperiod = [];
        $dataSubperiod["begin_periods"] = $begin_periods;
        $dataSubperiod["end_periods"] = $end_periods;
        $dataSubperiod["label"] = $label;
        return $dataSubperiod;
    }

    public static function getKeyPlot($interval, $date, $time)
    {
        $date = Carbon::createFromFormat("Y-m-d H:i:s", $date." ".$time);
        $key = "";
        switch ($interval){
            case 1:
                $date_label = 'Ayer';
                $key = $date->format("Y-m-d-H-i");
                break;
            case 2:
                $date_label = 'Hoy';
                $key = $date->format("Y-m-d-H-i");
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
                $key = $date->format("Y-m-d-H-i");
                break;
        }
        return $key;
    }

    private function getKeyData($interval, $date, $time)
    {
        $date = Carbon::createFromFormat("Y-m-d H:i:s", $date." ".$time);
        $key = "";
        switch ($interval){
            case 1:
                $date_label = 'Ayer';
                if($time != "23:59:00")
                {
                    $date = $date->addMinute(-1);
                }
                $key = $date->format("Y-m-d-H");
                break;
            case 2:
                $date_label = 'Hoy';
                if($time != "23:59:00")
                {
                    $date = $date->addMinute(-1);
                }
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
                if($time != "23:59:00")
                {
                    $date = $date->addMinute(-1);
                }
                $key = $date->format("Y-m-d-H");
                break;
        }
        return $key;
    }

    function exportCSVConsumoEnergia(Request $request){


      $user = User::find($request->user_id);
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

        $datos_contador = $db->table('ZPI_Contador_Festivos_Periodos')->select(\DB::raw("date, time, `EAct imp(kWh)` EAct_imp, `Periodo` Periodo"))->where('date','>=',$request->date_from)->where('date','<=',$request->date_to)->orderBy('date')->orderBy('time')->get()->toArray();

        $filename = "Datos_Consumo_".$contador_label."_".$request->date_from."_".$request->date_to.".csv";
        $handle = fopen($filename, 'w+');
        if($tipo_tarifa == 1){

          fputcsv($handle, array('Fecha', 'Tiempo', 'P1', 'P2', 'P3', 'P4', 'P5', 'P6'),';');

        }elseif($tipo_tarifa == 2 || $tipo_tarifa == 3){

          fputcsv($handle, array('Fecha', 'Tiempo', 'P1', 'P2', 'P3'),';');

        }
        $i = 0;
        foreach($datos_contador as $data) {
              if($data->Periodo == "P1"){
                $P1 = number_format($data->EAct_imp,0,',','.');
              }else{
                $P1 = 0;
              }
              if($data->Periodo == "P2"){
                $P2 = number_format($data->EAct_imp,0,',','.');
              }else{
                $P2 = 0;
              }
              if($data->Periodo == "P3"){
                $P3 = number_format($data->EAct_imp,0,',','.');
              }else{
                $P3 = 0;
              }
              if($data->Periodo == "P4"){
                $P4 = number_format($data->EAct_imp,0,',','.');
              }else{
                $P4 = 0;
              }
              if($data->Periodo == "P5"){
                $P5 = number_format($data->EAct_imp,0,',','.');
              }else{
                $P5 = 0;
              }
              if($data->Periodo == "P6"){
                $P6 = number_format($data->EAct_imp,0,',','.');
              }else{
                $P6 =  0;
              }
              if($tipo_tarifa == 1){
              fputcsv($handle, array(
                $data->date, $data->time, $P1, $P2, $P3, $P4, $P5, $P6
                ),';');
              }elseif($tipo_tarifa == 2 || $tipo_tarifa == 3){
              fputcsv($handle, array(
                $data->date, $data->time, $P1, $P2, $P3
                ),';');
              }
            $i++;
        }

        fclose($handle);

        $headers = array(
                        'Content-Type' => 'text/csv',
        );
        // if($db->table('Area_Cliente')->select(\DB::raw('`LOGOTIPO` logo'))->first())
        //     $dir_image_count = $db->table('Area_Cliente')->select(\DB::raw('`LOGOTIPO` logo'))->first()->logo;
        // else
        //     $dir_image_count = $db->table('Area_Cliente')->select(\DB::raw('`LOGOTIPO` logo'))->first();
        return Response::download($filename, $filename, $headers);
        \DB::disconnect('mysql2');
    }

    function exportCSVGeneracion(Request $request){


      $user = User::find($request->user_id);
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

        $datos_contador = $db->table('ZPI_Contador_Festivos_Periodos')->select(\DB::raw("date, time, `EAct exp(kWh)` EAct_exp, `Periodo` Periodo"))->where('date','>=',$request->date_from)->where('date','<=',$request->date_to)->orderBy('date')->orderBy('time')->get()->toArray();

        $filename = "Datos_Generacion_".$contador_label."_".$request->date_from."_".$request->date_to.".csv";
        $handle = fopen($filename, 'w+');
        if($tipo_tarifa == 1){

          fputcsv($handle, array('Fecha', 'Tiempo', 'P1', 'P2', 'P3', 'P4', 'P5', 'P6'),';');

        }elseif($tipo_tarifa == 2 || $tipo_tarifa == 3){

          fputcsv($handle, array('Fecha', 'Tiempo', 'P1', 'P2', 'P3'),';');

        }
        $i = 0;
        foreach($datos_contador as $data) {
              if($data->Periodo == "P1"){
                $P1 = number_format($data->EAct_exp,0,',','.');
              }else{
                $P1 = 0;
              }
              if($data->Periodo == "P2"){
                $P2 = number_format($data->EAct_exp,0,',','.');
              }else{
                $P2 = 0;
              }
              if($data->Periodo == "P3"){
                $P3 = number_format($data->EAct_exp,0,',','.');
              }else{
                $P3 = 0;
              }
              if($data->Periodo == "P4"){
                $P4 = number_format($data->EAct_exp,0,',','.');
              }else{
                $P4 = 0;
              }
              if($data->Periodo == "P5"){
                $P5 = number_format($data->EAct_exp,0,',','.');
              }else{
                $P5 = 0;
              }
              if($data->Periodo == "P6"){
                $P6 = number_format($data->EAct_exp,0,',','.');
              }else{
                $P6 =  0;
              }
              if($tipo_tarifa == 1){
              fputcsv($handle, array(
                $data->date, $data->time, $P1, $P2, $P3, $P4, $P5, $P6
                ),';');
              }elseif($tipo_tarifa == 2 || $tipo_tarifa == 3){
              fputcsv($handle, array(
                $data->date, $data->time, $P1, $P2, $P3
                ),';');
              }
            $i++;
        }

        fclose($handle);

        $headers = array(
                        'Content-Type' => 'text/csv',
        );
        // if($db->table('Area_Cliente')->select(\DB::raw('`LOGOTIPO` logo'))->first())
        //     $dir_image_count = $db->table('Area_Cliente')->select(\DB::raw('`LOGOTIPO` logo'))->first()->logo;
        // else
        //     $dir_image_count = $db->table('Area_Cliente')->select(\DB::raw('`LOGOTIPO` logo'))->first();
        return Response::download($filename, $filename, $headers);
        \DB::disconnect('mysql2');
    }

    function exportCSVBalance(Request $request){


      $user = User::find($request->user_id);
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

        $datos_contador = $db->table('ZPI_Contador_Festivos_Periodos')->select(\DB::raw("date, time, `EAct imp(kWh)` EAct_imp, `EAct exp(kWh)` EAct_exp, `Periodo` Periodo"))->where('date','>=',$request->date_from)->where('date','<=',$request->date_to)->orderBy('date')->orderBy('time')->get()->toArray();

        $filename = "Datos_Balance_".$contador_label."_".$request->date_from."_".$request->date_to.".csv";
        $handle = fopen($filename, 'w+');
        if($tipo_tarifa == 1){

          fputcsv($handle, array('Fecha', 'Tiempo', 'P1', 'P2', 'P3', 'P4', 'P5', 'P6'),';');

        }elseif($tipo_tarifa == 2 || $tipo_tarifa == 3){

          fputcsv($handle, array('Fecha', 'Tiempo', 'P1', 'P2', 'P3'),';');

        }
        $i = 0;
        foreach($datos_contador as $data) {
              if($data->Periodo == "P1"){
                $P1 = number_format($data->EAct_exp-$data->EAct_imp,0,',','.');
              }else{
                $P1 = 0;
              }
              if($data->Periodo == "P2"){
                $P2 = number_format($data->EAct_exp-$data->EAct_imp,0,',','.');
              }else{
                $P2 = 0;
              }
              if($data->Periodo == "P3"){
                $P3 = number_format($data->EAct_exp-$data->EAct_imp,0,',','.');
              }else{
                $P3 = 0;
              }
              if($data->Periodo == "P4"){
                $P4 = number_format($data->EAct_exp-$data->EAct_imp,0,',','.');
              }else{
                $P4 = 0;
              }
              if($data->Periodo == "P5"){
                $P5 = number_format($data->EAct_exp-$data->EAct_imp,0,',','.');
              }else{
                $P5 = 0;
              }
              if($data->Periodo == "P6"){
                $P6 = number_format($data->EAct_exp-$data->EAct_imp,0,',','.');
              }else{
                $P6 =  0;
              }
              if($tipo_tarifa == 1){
              fputcsv($handle, array(
                $data->date, $data->time, $P1, $P2, $P3, $P4, $P5, $P6
                ),';');
              }elseif($tipo_tarifa == 2 || $tipo_tarifa == 3){
              fputcsv($handle, array(
                $data->date, $data->time, $P1, $P2, $P3
                ),';');
              }
            $i++;
        }

        fclose($handle);

        $headers = array(
                        'Content-Type' => 'text/csv',
        );
        // if($db->table('Area_Cliente')->select(\DB::raw('`LOGOTIPO` logo'))->first())
        //     $dir_image_count = $db->table('Area_Cliente')->select(\DB::raw('`LOGOTIPO` logo'))->first()->logo;
        // else
        //     $dir_image_count = $db->table('Area_Cliente')->select(\DB::raw('`LOGOTIPO` logo'))->first();
        return Response::download($filename, $filename, $headers);
        \DB::disconnect('mysql2');
    }

}
?>
