<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\RegisterUserRequest;
use Illuminate\Support\Facades\Hash;
use App\Mail\CreateClienteMail as CreateClient;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Input;
use App\Http\Requests\PerfilUserRequest;
use App\Jobs\SendEmailJob;
use App\Http\Requests\UsuarioRegistradoRequest;
use App\User;
use App\Informes;
use App\Alertas;
use App\User2;
use App\Perfil;
use App\Count;
use App\CurrentCount;
use App\Analizador;
use App\intervalos_user;
use Session;
use Validator;
use Auth;
use File;
use PDF;
use Response;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Carbon\CarbonInterval;

class AnalisisPotencia extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['graficaAnalisis']]);
    }

    function Optimizacion($id=NULL, $id_delete_intervals=NULL,Request $request)
    {

    }
    
    function graficaAnalisis($data)
    {
        $data = base64_decode($data);
        $data = unserialize($data);
        
        $keySerie = array();
        $dataSerie = array();
        $percentageSerie = array();
        $total = 0.0;
        foreach($data as $key => $serie)
        {
            $keySerie[] = $key;
            $dataSerie[] = $serie;
            $total += $serie;
        }
        
        foreach($dataSerie as $value)
        {
            if($total > 0)
            {
                $percentageSerie[] = floatval(number_format(100*$value / $total, 2));
            } else
            {
                $percentageSerie[] = 0.0;
            }            
        }
        
        $keySerie = json_encode($keySerie);
        $dataSerie = json_encode($dataSerie);
        $percentageSerie = json_encode($percentageSerie);
        
        return view('simulacion_facturas.simulacion_facturas_chart', compact("keySerie", "dataSerie", "percentageSerie"));
    }
    
    public function AnalisisPotencia($id, Request $request)
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

        $ktep = $db->table('Coeficientes_Excesos')->where('Coeficiente', 'Ktep')->first();
        $kiP1 = $db->table('Coeficientes_Excesos')->where('Coeficiente', 'KiP1')->first();
        $kiP2 = $db->table('Coeficientes_Excesos')->where('Coeficiente', 'KiP2')->first();
        $kiP3 = $db->table('Coeficientes_Excesos')->where('Coeficiente', 'KiP3')->first();
        $kiP4 = $db->table('Coeficientes_Excesos')->where('Coeficiente', 'KiP4')->first();
        $kiP5 = $db->table('Coeficientes_Excesos')->where('Coeficiente', 'KiP5')->first();
        $kiP6 = $db->table('Coeficientes_Excesos')->where('Coeficiente', 'KiP6')->first();

        $ki = array($kiP1->valor_coeficiente, $kiP2->valor_coeficiente, $kiP3->valor_coeficiente, $kiP4->valor_coeficiente, $kiP5->valor_coeficiente, $kiP6->valor_coeficiente);
        
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
            ->select(\DB::raw("Periodo as periodo, MAX(`Potencia_contratada`) as potencia_contratada, 
                RIGHT(Periodo,1) as periodo_int"))
            ->where('date_start','<=',$date_from)->orWhere('date_end','>=',$date_to)
            ->groupBy('Periodo')->get();
        
        $arreglo_potencia = array();
        $vector_potencia = array();
        foreach($potencia_contratada as $potencia){
            $arreglo_potencia[] = array("periodo"=>$potencia->periodo, "potencia"=>$potencia->potencia_contratada);
            $idx_periodo = intval($potencia->periodo_int) - 1;
            $vector_potencia[$idx_periodo] = doubleval($potencia->potencia_contratada);
        }
        
        $potencia_contratada_optima = $db->table('Potencia_Contratada_Optima')
            ->select(\DB::raw("Periodo as periodo, `Potencia_contratada` as potencia_contratada, 
                RIGHT(Periodo,1) as periodo_int"))
            ->get();
        
        $arreglo_potencia_optima = array();
        $vector_potencia_optima = array();
        foreach($potencia_contratada_optima as $potencia){
            $arreglo_potencia_optima[] = array("periodo"=>$potencia->periodo, "potencia"=>$potencia->potencia_contratada);
            $idx_periodo = intval($potencia->periodo_int) - 1;
            $vector_potencia_optima[$idx_periodo] = doubleval($potencia->potencia_contratada);
        }
        
        $precios_potencia = $db->table("ZPI_Precio_Potencia_Contratada")
            ->select(\DB::raw("RIGHT(Periodo,1) as periodo, Precio as precio"))
            ->orderBy("Periodo")->get();
        
        $vector_costos = array();
        foreach ($precios_potencia as $precio)
        {
            $idx = intval($precio->periodo) - 1;
            $vector_costos[$idx] = 12*floatval($precio->precio) / 365;
        }
        
        $data_contador = $db->table("ZPI_Contador_Festivos_Periodos")
            ->select(\DB::raw("MONTH(date) AS month, YEAR(date) AS year, 4*`EAct imp(kWh)` AS potencia, 
                RIGHT(Periodo,1) as periodo, DATEDIFF(date, '1970-01-01') AS days_unix, date, time"))
            ->where("date", ">=", $date_from)
            ->where("date", "<=", $date_to)
            ->get()->toArray();
            
        $data_calculos = compact("vector_potencia", "vector_potencia_optima", "data_contador", "vector_costos", 
                "date_from", "date_to", "interval");

        if($tipo_tarifa == 1) {
            
            $data_analisis = $this->calcularCostos6($data_calculos);
        } else {
            $data_analisis = $this->calcularCostos3($data_calculos);
        }
        
        $data_labels = $this->getLabelsPlot($data_calculos);
        $data_calculos["data_labels"] = $data_labels;
        
        if($tipo_tarifa == 1) {
            $data_calculos["include_percent_axis"] = false;
        } else {
            $data_calculos["include_percent_axis"] = true;
        }
        
        $dataPlotting = $this->createConsumePlots($data_calculos);
        
        
        $dateToCar = \Carbon\Carbon::createFromFormat("Y-m-d", $date_to);
        $dateFromCar = \Carbon\Carbon::createFromFormat("Y-m-d", $date_from);
        $diff_dates = $dateToCar->diff($dateFromCar);
        
        $eje = array();
        $p_demandada = array();
        $p_optima = array();
        $p_contratada = array();
        $p_85_contratada = array();
        $p_105_contratada = array();
        $totalD = 0;
        $totalC = 0;
        $dates = array();
        $maxima_potencia = 0.0;
        
        $titulo = "Análisis de Potencia";
        
        
        return view("analisis_potencia.newanalisispotencia", compact("titulo", "arreglo_potencia", "arreglo_potencia_optima" ,
            "contador2", "contador_label", "data_analisis" ,"date_from", "date_to", "dates", "diff_dates", "dir_image_count", 
            "domicilio", "eje", "id", "label_intervalo", "maxima_potencia", "p_105_contratada", "p_85_contratada", "p_contratada", 
            "p_demandada", "p_optima", "tipo_count", "tipo_tarifa", "totalC", "totalD", "user", "dataPlotting"));
    }
    
    public function calcularCostos6($data_calculos)
    {

        $user = User::find(Auth::user()->id);
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

        $ktep = $db->table('Coeficientes_Excesos')->where('Coeficiente', 'Ktep')->first();
        $kiP1 = $db->table('Coeficientes_Excesos')->where('Coeficiente', 'KiP1')->first();
        $kiP2 = $db->table('Coeficientes_Excesos')->where('Coeficiente', 'KiP2')->first();
        $kiP3 = $db->table('Coeficientes_Excesos')->where('Coeficiente', 'KiP3')->first();
        $kiP4 = $db->table('Coeficientes_Excesos')->where('Coeficiente', 'KiP4')->first();
        $kiP5 = $db->table('Coeficientes_Excesos')->where('Coeficiente', 'KiP5')->first();
        $kiP6 = $db->table('Coeficientes_Excesos')->where('Coeficiente', 'KiP6')->first();

        $ki = array($kiP1->valor_coeficiente, $kiP2->valor_coeficiente, $kiP3->valor_coeficiente, $kiP4->valor_coeficiente, $kiP5->valor_coeficiente, $kiP6->valor_coeficiente);

        $dataAnios = array_fill(0, 1200, array_fill(0, 6, 0.0));
        $dataAniosOptimos = array_fill(0, 1200, array_fill(0, 6, 0.0));
        $diasConsumoMes = array_fill(0, 1200, array());        
        $vector_potencia = $data_calculos["vector_potencia"];
        $vector_potencia_optima = $data_calculos["vector_potencia_optima"];
        $vector_costos = $data_calculos["vector_costos"];
        $date_from = $data_calculos["date_from"];
        $date_to = $data_calculos["date_to"];
        $vector_max_potencia = array_fill(0, 6, 0.0);
        
        foreach($data_calculos["data_contador"] as $data)
        {
            $idxPeriodo = intval($data->periodo - 1);
            $idxMonth = intval(($data->year - 1970)*12 + $data->month - 1);
            $diasConsumoMes[$idxMonth][$data->days_unix] = 1;
            if($data->potencia > $vector_potencia[$idxPeriodo])
            {
                $diferencia = 1.0*($data->potencia - $vector_potencia[$idxPeriodo]);
                $dataAnios[$idxMonth][$idxPeriodo] += $diferencia*$diferencia;
            }
            if($data->potencia > $vector_potencia_optima[$idxPeriodo])
            {
                $diferencia = 1.0*($data->potencia - $vector_potencia_optima[$idxPeriodo]);
                $dataAniosOptimos[$idxMonth][$idxPeriodo] += $diferencia*$diferencia;
            }
            if($data->potencia > $vector_max_potencia[$idxPeriodo])
            {
                $vector_max_potencia[$idxPeriodo] = $data->potencia;
            }
        }
        
        $date_begin = Carbon::createFromFormat("Y-m-d", $date_from)->startOfMonth();
        $date_end = Carbon::createFromFormat("Y-m-d", $date_to)->endOfMonth();
        
        $idxMonthBegin = 12*($date_begin->year - 1970) + $date_begin->month - 1;
        $idxMonthEnd = 12*($date_end->year - 1970) + $date_end->month - 1;
        
        $namesMonth = array("1"=>"Enero", "2"=>"Febrero", "3"=>"Marzo", "4"=>"Abril", "5"=>"Mayo","6"=>"Junio",
            "7"=>"Julio", "8"=>"Agosto", "9"=>"Septiembre", "10"=>"Octubre", "11"=>"Noviembre", "12"=>"Diciembre"
        );
        
        $dataMonths = array();
        $totalConsumo = 0.0;
        $totalFPEConsumo = 0.0;
        $totalFCConsumo = 0.0;
        $totalConsumoOptimo = 0.0;
        $totalFPEConsumoOptimo = 0.0;
        $totalFCConsumoOptimo = 0.0;
        
        $totalFP = array();
        $totalFPC = array();
        $totalFPE = array();
        $totalFPOptimal = array();
        $totalFPCOptimal = array();
        $totalFPEOptimal = array();
        for($idx = 1; $idx <= 6; $idx++)
        {
            $totalFP[$idx] = 0.0;
            $totalFPC[$idx] = 0.0;
            $totalFPE[$idx] = 0.0;
            $totalFPOptimal[$idx] = 0.0;
            $totalFPCOptimal[$idx] = 0.0;
            $totalFPEOptimal[$idx] = 0.0;
        }
        
        
        $totalDays = 0;
        for($idxMonth = $idxMonthBegin; $idxMonth <= $idxMonthEnd; $idxMonth++)
        {
            $yearDate = 1970 + floor($idxMonth / 12);
            $monthDate = $idxMonth - 12*($yearDate - 1970) + 1;
            
            $days = count($diasConsumoMes[$idxMonth]);
            
            $dMonth = array();
            $dMonthFPE = array();
            $dMonthFPC = array();
            $dMonthOptimal = array();
            $dMonthFPOptimal = array();
            $dMonthFPCOptimal = array();
            $totalMonth = 0.0;
            $totalMonthFPE = 0.0;
            $totalMonthFPC = 0.0;
            $totalMonthOptimal = 0.0;
            $totalMonthFPEOptimal = 0.0;
            $totalMonthFPCOptimal = 0.0;
            for($idx = 0; $idx < 6; $idx++)
            {
                $fpe = $ktep->valor_coeficiente * sqrt($dataAnios[$idxMonth][$idx]) * $ki[$idx];
                $fpc = $vector_potencia[$idx]*$vector_costos[$idx] * $days;
                $fp = $fpe + $fpc;
                $dMonth[$idx + 1] = $fp;
                $dMonthFPE[$idx + 1] = $fpe;
                $dMonthFPC[$idx + 1] = $fpc;
                $totalMonth += $fp;
                $totalMonthFPE += $fpe;
                $totalMonthFPC += $fpc;
                $totalFPE[$idx + 1] += $fpe;
                $totalFPC[$idx + 1] += $fpc;
                $totalFP[$idx + 1] += $fp;
                
                $fpeO = $ktep->valor_coeficiente * sqrt($dataAniosOptimos[$idxMonth][$idx]) * $ki[$idx];
                $fpcO = $vector_potencia_optima[$idx]*$vector_costos[$idx] * $days;
                $fpO = $fpeO + $fpcO;
                $dMonthOptimal[$idx + 1] = $fpO;
                $dMonthFPOptimal[$idx + 1] = $fpeO;
                $dMonthFPCOptimal[$idx + 1] = $fpcO;
                $totalMonthOptimal += $fpO;
                $totalMonthFPEOptimal += $fpeO;
                $totalMonthFPCOptimal += $fpcO;
                $totalFPEOptimal[$idx + 1] += $fpeO;
                $totalFPCOptimal[$idx + 1] += $fpcO;
                $totalFPOptimal[$idx + 1] += $fpO;
            }
            
            $month = array();
            $month["name"] = $namesMonth[$monthDate];
            $month["year"] = $yearDate;
            $month["dataFP"] = $dMonth;
            $month["dataFPE"] = $dMonthFPE;
            $month["dataFPC"] = $dMonthFPC;
            $month["dataFP_optimal"] = $dMonthOptimal;
            $month["dataFPE_optimal"] = $dMonthFPOptimal;
            $month["dataFPC_optimal"] = $dMonthFPCOptimal;
            $month["totalFP"] = $totalMonth;
            $month["totalFPE"] = $totalMonthFPE;
            $month["totalFPC"] = $totalMonthFPC;
            $month["totalFP_optimal"] = $totalMonthOptimal;
            $month["totalFPE_optimal"] = $totalMonthFPEOptimal;
            $month["totalFPC_optimal"] = $totalMonthFPCOptimal;
            $month["totalFP_difference"] = $totalMonth - $totalMonthOptimal;
            $month["totalFPE_difference"] = $totalMonthFPE - $totalMonthFPEOptimal;
            $month["totalFPE_difference"] = $totalMonthFPC - $totalMonthFPCOptimal;
            $dataMonths[] = $month;
            $totalConsumo += $totalMonth;
            $totalFPEConsumo += $totalMonthFPE;
            $totalFCConsumo += $totalMonthFPC;
            $totalConsumoOptimo += $totalMonthOptimal;
            $totalFPEConsumoOptimo += $totalMonthFPEOptimal;
            $totalFCConsumoOptimo += $totalMonthFPCOptimal;
            $totalDays += $days;
        }
        
        $costoDias = $vector_costos;
        foreach($costoDias as $index => $costo) 
        {
            $costoDias[$index] = $costo * $totalDays;
        }
        
        $totalFPDifference = $totalConsumo - $totalConsumoOptimo;
        
        $data_analisis = array();
        $data_analisis["dataFP_max"] = $vector_max_potencia;
        $data_analisis["months"] = $dataMonths;
        $data_analisis["dataFP"] = $totalFP;
        $data_analisis["dataFPE"] = $totalFPE;
        $data_analisis["dataFPC"] = $totalFPC;
        $data_analisis["dataFP_optimal"] = $totalFPOptimal;
        $data_analisis["dataFPE_optimal"] = $totalFPEOptimal;
        $data_analisis["dataFPC_optimal"] = $totalFPCOptimal;
        $data_analisis["totalFP"] = $totalConsumo;
        $data_analisis["totalFPE"] = $totalFPEConsumo;
        $data_analisis["totalFC"] = $totalFCConsumo;
        $data_analisis["totalFP_optimal"] = $totalConsumoOptimo;
        $data_analisis["totalFPE_optimal"] = $totalFPEConsumoOptimo;
        $data_analisis["totalFC_optimal"] = $totalFCConsumoOptimo;
        $data_analisis["totalFPDifference"] = $totalFPDifference;
        $data_analisis["costoDias"] = $costoDias;
        return $data_analisis;
    }
    
    public function calcularCostos3($data_calculos)
    {
        $dataAnios = array_fill(0, 1200, array_fill(0, 3, 0.0));
        $dataAniosOptimos = array_fill(0, 1200, array_fill(0, 3, 0.0));
        $diasConsumoMes = array_fill(0, 1200, array());
        
        
        $vector_potencia = $data_calculos["vector_potencia"];
        $vector_potencia_optima = $data_calculos["vector_potencia_optima"];
        $vector_costos = $data_calculos["vector_costos"];
        $vector_max_potencia = array_fill(0, 3, 0.0);
        
        $date_from = $data_calculos["date_from"];
        $date_to = $data_calculos["date_to"];
        
        foreach($data_calculos["data_contador"] as $data)
        {
            $idxPeriodo = intval($data->periodo - 1);
            $idxMonth = intval(($data->year - 1970)*12 + $data->month - 1);
            $diasConsumoMes[$idxMonth][$data->days_unix] = 1;
            if($data->potencia > $dataAnios[$idxMonth][$idxPeriodo])
            {
                $dataAnios[$idxMonth][$idxPeriodo] = $data->potencia;
            }
            if($data->potencia > $dataAniosOptimos[$idxMonth][$idxPeriodo])
            {
                $dataAniosOptimos[$idxMonth][$idxPeriodo] = $data->potencia;
            }
            
            if($data->potencia > $vector_max_potencia[$idxPeriodo])
            {
                $vector_max_potencia[$idxPeriodo] = $data->potencia;
            }
        }
        
        $date_begin = Carbon::createFromFormat("Y-m-d", $date_from)->startOfMonth();
        $date_end = Carbon::createFromFormat("Y-m-d", $date_to)->endOfMonth();
        
        $idxMonthBegin = 12*($date_begin->year - 1970) + $date_begin->month - 1;
        $idxMonthEnd = 12*($date_end->year - 1970) + $date_end->month - 1;
        
        $namesMonth = array("1"=>"Enero", "2"=>"Febrero", "3"=>"Marzo", "4"=>"Abril", "5"=>"Mayo","6"=>"Junio",
            "7"=>"Julio", "8"=>"Agosto", "9"=>"Septiembre", "10"=>"Octubre", "11"=>"Noviembre", "12"=>"Diciembre"
        );
        
        $dataMonths = array();
        $totalConsumo = 0.0;        
        $totalConsumoOptimo = 0.0;        
        
        $totalFP = array();        
        $totalFPOptimal = array();        
        for($idx = 1; $idx <= 3; $idx++)
        {
            $totalFP[$idx] = 0.0;
            $totalFPOptimal[$idx] = 0.0;            
        }
        
        $totalDays = 0;
        for($idxMonth = $idxMonthBegin; $idxMonth <= $idxMonthEnd; $idxMonth++)
        {
            $yearDate = 1970 + floor($idxMonth / 12);
            $monthDate = $idxMonth - 12*($yearDate - 1970) + 1;
            
            $days = count($diasConsumoMes[$idxMonth]);
            
            $dMonth = array();
            $dMonthOptimal = array();            
            $totalMonth = 0.0;            
            $totalMonthOptimal = 0.0;
            
            for($idx = 0; $idx < 3; $idx++)
            {
                if($dataAnios[$idxMonth][$idx] < 0.85*$vector_potencia[$idx])
                {
                    $fp = 0.85*$vector_potencia[$idx]*$days*$vector_costos[$idx];
                } else if($dataAnios[$idxMonth][$idx] >= 0.85*$vector_potencia[$idx] && $dataAnios[$idxMonth][$idx] < 1.05*$vector_potencia[$idx]){
                    $fp = $dataAnios[$idxMonth][$idx]*$days*$vector_costos[$idx];
                } else {
                    $fp = ($dataAnios[$idxMonth][$idx] + 2*($dataAnios[$idxMonth][$idx] - 1.05*$vector_potencia[$idx]))*$days*$vector_costos[$idx];
                }
                $dMonth[$idx + 1] = $fp;                
                $totalMonth += $fp;                
                $totalFP[$idx + 1] += $fp;
                
                if($dataAniosOptimos[$idxMonth][$idx] < 0.85*$vector_potencia_optima[$idx])
                {
                    $fpO = 0.85*$vector_potencia_optima[$idx]*$days*$vector_costos[$idx];
                } else if($dataAniosOptimos[$idxMonth][$idx] >= 0.85*$vector_potencia_optima[$idx] && $dataAniosOptimos[$idxMonth][$idx] < 1.05*$vector_potencia_optima[$idx]){
                    $fpO = $dataAniosOptimos[$idxMonth][$idx]*$days*$vector_costos[$idx];
                } else {
                    $fpO = ($dataAniosOptimos[$idxMonth][$idx] + 2*($dataAniosOptimos[$idxMonth][$idx] - 1.05*$vector_potencia_optima[$idx]))*$days*$vector_costos[$idx];
                }
                $dMonthOptimal[$idx + 1] = $fpO;
                $totalMonthOptimal += $fpO;                
                $totalFPOptimal[$idx + 1] += $fpO;
            }            
            $month = array();
            $month["name"] = $namesMonth[$monthDate];
            $month["year"] = $yearDate;
            $month["dataFP"] = $dMonth;
            $month["dataFP_optimal"] = $dMonthOptimal;
            $month["totalFP"] = $totalMonth;
            $month["totalFP_optimal"] = $totalMonthOptimal;
            $month["totalFP_difference"] = $totalMonth - $totalMonthOptimal;
            
            $dataMonths[] = $month;
            $totalConsumo += $totalMonth;            
            $totalConsumoOptimo += $totalMonthOptimal;  
            $totalDays += $days;
        }
        
        $costoDias = $vector_costos;
        foreach($costoDias as $index => $costo)
        {
            $costoDias[$index] = $costo * $totalDays;
        }
        
        $totalFPDifference = $totalConsumo - $totalConsumoOptimo;
        
        $data_analisis = array();
        $data_analisis["dataFP_max"] = $vector_max_potencia;
        $data_analisis["months"] = $dataMonths;
        $data_analisis["dataFP"] = $totalFP;
        $data_analisis["dataFP_optimal"] = $totalFPOptimal;
        $data_analisis["totalFP"] = $totalConsumo;
        $data_analisis["totalFP_optimal"] = $totalConsumoOptimo;
        $data_analisis["totalFPDifference"] = $totalFPDifference;
        $data_analisis["costoDias"] = $costoDias;        
        return $data_analisis;
    }

    private function getDatesAnalysis($date_reference = null)
    {
        $interval = "";
        $session = Session::get('_flash');
        if(array_key_exists('intervalos', $session))
        {
            $interval = $session['intervalos'];
        }        
        
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
                
                $period = new CarbonPeriod($date_from." 00:15:00", '15 minutes', $date_to." 23:59:00");
                foreach ($period as $key => $date) {
                    $interval_keys[] = $date->format("Y-m-d-H-i");
                    $interval_values[] = $date->format("H:i");
                }
                $interval_keys[] = $date_from."-23-59";
                $interval_values[] = "23:59";
                $aux_label = "Hora: ";
                break;
            case 2:
                $date_label = 'Hoy';
                
                $period = new CarbonPeriod($date_from." 00:15:00", '15 minutes', $date_to." 23:59:00");
                foreach ($period as $key => $date) {
                    $interval_keys[] = $date->format("Y-m-d-H-i");
                    $interval_values[] = $date->format("H:i");
                }
                $interval_keys[] = $date_from."-23-59";
                $interval_values[] = "23:59";
                $aux_label = "Hora: ";
                break;
            case 3:
                $date_label = 'Semana Actual';
                
                $period = new CarbonPeriod($date_from, '1 days', $date_to);
                
                foreach ($period as $key => $date) {
                    $periodDay = new CarbonPeriod($date->format("Y-m-d 00:15:00"), '15 minutes', $date->format("Y-m-d  23:59:00"));
                    foreach($periodDay as $keyDay => $dateDay)
                    {
                        $interval_keys[] = $dateDay->format("Y-m-d-H-i");
                        $interval_values[] = $daysNames[$date->dayOfWeekIso]." ".$dateDay->format("H:i");
                    }
                    $interval_keys[] = $date->format("Y-m-d-23-59");
                    $interval_values[] = $daysNames[$date->dayOfWeekIso]." 23:59";
                }
                break;
            case 4:
                $date_label = 'Semana Anterior';
                
                $period = new CarbonPeriod($date_from, '1 days', $date_to);
                foreach ($period as $key => $date) {
                    $periodDay = new CarbonPeriod($date->format("Y-m-d 00:15:00"), '15 minutes', $date->format("Y-m-d  23:59:00"));
                    foreach($periodDay as $keyDay => $dateDay)
                    {
                        $interval_keys[] = $dateDay->format("Y-m-d-H-i");
                        $interval_values[] = $daysNames[$date->dayOfWeekIso]." ".$dateDay->format("H:i");
                    }
                    $interval_keys[] = $date->format("Y-m-d-23-59");
                    $interval_values[] = $daysNames[$date->dayOfWeekIso]." 23:59";
                }
                
                break;
            case 5:
                $date_label = 'Mes Actual';
                $interval_keys = array();
                $interval_values = array();
                
                $period = new CarbonPeriod($date_from, '1 days', $date_to);
                foreach ($period as $key => $date) {
                    $periodDay = new CarbonPeriod($date->format("Y-m-d 00:15:00"), '15 minutes', $date->format("Y-m-d  23:59:00"));
                    foreach($periodDay as $keyDay => $dateDay)
                    {
                        $interval_keys[] = $dateDay->format("Y-m-d-H-i");
                        $interval_values[] = $date->day." ".$dateDay->format("H:i");
                    }
                    $interval_keys[] = $date->format("Y-m-d-23-59");
                    $interval_values[] = $date->day." 23:59";
                }
                $aux_label = "Día ";
                //$aux_interval = 1;
                break;
            case 6:
                $date_label = 'Mes Anterior';
                
                $period = new CarbonPeriod($date_from, '1 days', $date_to);
                foreach ($period as $key => $date) {
                    $periodDay = new CarbonPeriod($date->format("Y-m-d 00:15:00"), '15 minutes', $date->format("Y-m-d  23:59:00"));
                    foreach($periodDay as $keyDay => $dateDay)
                    {
                        $interval_keys[] = $dateDay->format("Y-m-d-H-i");
                        $interval_values[] = $date->day." ".$dateDay->format("H:i");
                    }
                    $interval_keys[] = $date->format("Y-m-d-23-59");
                    $interval_values[] = $date->day." 23:59";
                }
                $aux_label = "Día ";
                //$aux_interval = 1;
                break;
            case 7:
                $date_label = 'Ultimo Trimestre';
                
                $interval_keys = array();
                $interval_values = array();
                
                $period = new CarbonPeriod($date_from, '1 days', $date_to);
                foreach ($period as $key => $date) {
                    $interval_keys[] = $date->format("Y-m-d");
                    $interval_values[] = $date->format("Y-m-d");
                }
                
                break;
            case 10:
                $date_label = 'Trimestre Actual';
                
                $interval_keys = array();
                $interval_values = array();
                
                $period = new CarbonPeriod($date_from, '1 days', $date_to);
                foreach ($period as $key => $date) {
                    $interval_keys[] = $date->format("Y-m-d");
                    $interval_values[] = $date->format("Y-m-d");
                }
                
                break;
            case 8:
                $date_label = 'Último Año';
                
                $interval_keys = array();
                $interval_values = array();
                
                $period = new CarbonPeriod($date_from, '1 days', $date_to);
                foreach ($period as $key => $date) {
                    $interval_keys[] = $date->format("Y-m-d");
                    $interval_values[] = $date->format("Y-m-d");
                }
                
                break;
            case 11:
                $date_label = 'Año Actual';
                
                $interval_keys = array();
                $interval_values = array();
                
                
                $period = new CarbonPeriod($date_from, '1 days', $date_to);
                foreach ($period as $key => $date) {
                    $interval_keys[] = $date->format("Y-m-d");
                    $interval_values[] = $date->format("Y-m-d");
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
                
                $period = new CarbonPeriod($date_from." 00:15:00", '15 minutes', $date_to." 23:59:00");
                foreach ($period as $key => $date) {
                    $interval_keys[] = $date->format("Y-m-d-H-i");
                    $interval_values[] = $date->format("H:i");
                }
                $interval_keys[] = $date_from."-23-59";
                $interval_values[] = "23:59";
                break;
        }
        
        $dateInterval = array();
        $dateInterval["interval_keys"] = $interval_keys;
        $dateInterval["interval_values"] = $interval_values; 
        $dateInterval["aux_label"] = $aux_label;
        $dateInterval["interval"] = $aux_interval;
        return $dateInterval;
    }
    
    private function createConsumePlots($data_calculos)
    {
        $interval = $data_calculos["interval"];
        $data_labels = $data_calculos["data_labels"];
        $data_contador = $data_calculos["data_contador"];
        $vector_potencia = $data_calculos["vector_potencia"];
        $vector_potencia_optima = $data_calculos["vector_potencia_optima"];
        $include_percent_axis = $data_calculos["include_percent_axis"];        
        
        $dataTotal = array_fill_keys($data_labels["interval_keys"], 0.0);
        $dataTotalPeriodo = array_fill_keys($data_labels["interval_keys"], 0.0);
        $dataTotalPeriodoOptimo = array_fill_keys($data_labels["interval_keys"], 0.0);
        $dataPeriodos = array_fill(0, count($vector_potencia), array_fill_keys($data_labels["interval_keys"], 0.0 ));
        $dataPeriodosOptimos = array_fill(0, count($vector_potencia), array_fill_keys($data_labels["interval_keys"], 0.0 ));
        
        $max_total = 0.0;
        
        $dataPlot = array();
        $totalPlot = array();
        foreach($vector_potencia as $key=>$value)
        {
            $totalPlot[$key] = 0.0;
            $dataPlot[$key] = array_fill_keys($data_labels["interval_keys"], 0.0);
        }        
        
        foreach($data_contador as $data)
        {
            $idxPeriodo = intval($data->periodo - 1);
            $keyData = $this->getKeyPlot($interval, $data->date, $data->time);
            if($data->potencia > $dataPlot[$idxPeriodo][$keyData])
            {
                $dataPlot[$idxPeriodo][$keyData] = $data->potencia;
            }
            if($data->potencia > $totalPlot[$idxPeriodo])
            {
                $totalPlot[$idxPeriodo] = $data->potencia;
            }
            if($data->potencia > $dataTotal[$keyData])
            {
                $dataTotal[$keyData] = $data->potencia;
            }
            if($data->potencia > $max_total)
            {
                $max_total = $data->potencia;
            }
            $dataTotalPeriodo[$keyData] = $this->getValuePlot($interval, $dataTotalPeriodo[$keyData], $vector_potencia[$idxPeriodo]);
            $dataTotalPeriodoOptimo[$keyData] = $this->getValuePlot($interval, $dataTotalPeriodoOptimo[$keyData], $vector_potencia_optima[$idxPeriodo]);
            $dataPeriodos[$idxPeriodo][$keyData] = $this->getValuePlot($interval, $dataPeriodos[$idxPeriodo][$keyData], $vector_potencia[$idxPeriodo]);
            $dataPeriodosOptimos[$idxPeriodo][$keyData] = $this->getValuePlot($interval, $dataPeriodosOptimos[$idxPeriodo][$keyData], $vector_potencia_optima[$idxPeriodo]);
        }
        
        $dataTotalPeriodo = array_values($dataTotalPeriodo);
        $dataTotalPeriodoOptimo = array_values($dataTotalPeriodoOptimo);
        
        $plotTotal = array();
        $plotTotal["name"] = "Análisis Potencia Demandada - Contratada";
        $plotTotal["labels"] = json_encode($data_labels["interval_values"]);
        $plotTotal["max"] = $max_total;
        $plotTotal["series"] = array();
        
        $serie = array();
        $serie["name"] = "Potencia Demandada";
        $serie["color"] = "#004165";
        $serie["values"] = json_encode(array_values($dataTotal));
        $serie["aux_label"] = $data_labels["aux_label"];
        $serie["interval"] = $data_labels["interval"];
        $plotTotal["series"][] = $serie;
        
        $serie = array();
        $serie["name"] = "Potencia Contratada";
        $serie["color"] = "#B9C9D0";
        $serie["values"] = json_encode($dataTotalPeriodo);
        $serie["aux_label"] = "";
        $serie["interval"] = $data_labels["interval"];
        $plotTotal["series"][] = $serie;
        
        $serie = array();
        $serie["name"] = "Potencia Óptima";
        $serie["color"] = "#7D9AAA";
        $serie["values"] = json_encode($dataTotalPeriodoOptimo);
        $serie["aux_label"] = "";
        $serie["interval"] = $data_labels["interval"];
        $plotTotal["series"][] = $serie;
        
        if($include_percent_axis)
        {
            $dataTotalPeriodo85 = [];
            $dataTotalPeriodo105 = [];
            $dataTotalPeriodo85Optimo = [];
            $dataTotalPeriodo105Optimo = [];
            for($i = 0; $i < count($dataTotalPeriodo); $i++)
            {
                $dataTotalPeriodo85[] = round(0.85 * $dataTotalPeriodo[$i]);
                $dataTotalPeriodo105[] = round(1.05 * $dataTotalPeriodo[$i]);
                $dataTotalPeriodo85Optimo[]= round(0.85 * $dataTotalPeriodoOptimo[$i]);
                $dataTotalPeriodo105Optimo[] = round(1.85 * $dataTotalPeriodoOptimo[$i]);
            }
            
            $serie = array();
            $serie["name"] = "85% Pot. Contratada";
            $serie["color"] = "#FE2E2E";
            $serie["values"] = json_encode($dataTotalPeriodo85);
            $serie["aux_label"] = "";
            $serie["interval"] = $data_labels["interval"];
            $plot["series"][] = $serie;
            $plotTotal["series"][] = $serie;
            
            $serie = array();
            $serie["name"] = "105% Pot. Contratada";
            $serie["color"] = "#FE2E2E";
            $serie["values"] = json_encode($dataTotalPeriodo105);
            $serie["aux_label"] = "";
            $serie["interval"] = $data_labels["interval"];
            $plot["series"][] = $serie;
            $plotTotal["series"][] = $serie;
        }
        
        $vColors = ['#FF0000', '#FFA500', '#008000', '#1E90FF', '#800080', '#C0C0C0'];
        
        $plots = array();
        foreach($vector_potencia as $keyVector=>$value)
        {
            $dataReferenciaPeriodo = array_values($dataPeriodos[$keyVector]);
            $dataReferenciaPeriodoOptimo = array_values($dataPeriodosOptimos[$keyVector]);
            
            $plot = array();
            $plot["name"] = "Análisis Potencia Demandada - Contratada P".($keyVector + 1);
            $plot["labels"] = json_encode($data_labels["interval_values"]);
            $plot["max"] = $totalPlot[$keyVector];
            $plot["series"] = array();
            
            $serie = array();
            $serie["name"] = "Potencia Demandada";
            $serie["color"] = $vColors[$keyVector];
            $serie["values"] = json_encode(array_values($dataPlot[$keyVector]));
            $serie["aux_label"] = $data_labels["aux_label"];
            $serie["interval"] = $data_labels["interval"];
            $plot["series"][] = $serie;
            
            $serie = array();
            $serie["name"] = "Potencia Contratada";
            $serie["color"] = "#B9C9D0";
            $serie["values"] = json_encode($dataReferenciaPeriodo);
            $serie["aux_label"] = "";
            $serie["interval"] = $data_labels["interval"];
            $plot["series"][] = $serie;
            
            $serie = array();
            $serie["name"] = "Potencia Óptima";
            $serie["color"] = "#7D9AAA";
            $serie["values"] = json_encode($dataReferenciaPeriodoOptimo);
            $serie["aux_label"] = "";
            $serie["interval"] = $data_labels["interval"];
            $plot["series"][] = $serie;            
            
            if($include_percent_axis)
            {
                $dataReferenciaPeriodo85 = [];
                $dataReferenciaPeriodo105 = [];
                $dataReferenciaPeriodo85Optimo = [];
                $dataReferenciaPeriodo105Optimo = [];
                for($i = 0; $i < count($dataReferenciaPeriodo); $i++)
                {
                    $dataReferenciaPeriodo85[] = round(0.85 * $dataReferenciaPeriodo[$i]);
                    $dataReferenciaPeriodo105[] = round(1.05 * $dataReferenciaPeriodo[$i]);
                    $dataReferenciaPeriodo85Optimo[]= round(0.85 * $dataReferenciaPeriodoOptimo[$i]);
                    $dataReferenciaPeriodo105Optimo[] = round(1.85 * $dataReferenciaPeriodoOptimo[$i]);
                }
                
                $serie = array();
                $serie["name"] = "85% Pot. Contratada";
                $serie["color"] = "#FE2E2E";
                $serie["values"] = json_encode($dataReferenciaPeriodo85);
                $serie["aux_label"] = "";
                $serie["interval"] = $data_labels["interval"];
                $plot["series"][] = $serie;                
                
                $serie = array();
                $serie["name"] = "105% Pot. Contratada";
                $serie["color"] = "#FE2E2E";
                $serie["values"] = json_encode($dataReferenciaPeriodo105);
                $serie["aux_label"] = "";
                $serie["interval"] = $data_labels["interval"];
                $plot["series"][] = $serie;
            }
            
            $plots[] = $plot;
        }
        
        $dataPlotting = array();
        $dataPlotting["total"] = $plotTotal;
        $dataPlotting["periodos"] = $plots;
        return $dataPlotting;
    }
    
    private function getKeyPlot($interval, $date, $time)
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
                $key = $date->format("Y-m-d-H-i");
                break;
            case 4:
                $date_label = 'Semana Anterior';
                $key = $date->format("Y-m-d-H-i");
                break;
            case 5:
                $date_label = 'Mes Actual';
                $key = $date->format("Y-m-d-H-i");
                break;
            case 6:
                $date_label = 'Mes Anterior';
                $key = $date->format("Y-m-d-H-i");
                break;
            case 7:
                $date_label = 'Ultimo Trimestre';
                $key = $date->format("Y-m-d");
                break;
            case 10:
                $date_label = 'Trimestre Actual';
                $key = $date->format("Y-m-d");
                break;
            case 8:
                $date_label = 'Último Año';
                $key = $date->format("Y-m-d");
                break;
            case 11:
                $date_label = 'Año Actual';
                $key = $date->format("Y-m-d");
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
    
    private function getValuePlot($interval, $last_value, $new_value)
    {
        switch ($interval){
            case 1:
                $date_label = 'Ayer';
                $value_return = $new_value;
                break;
            case 2:
                $date_label = 'Hoy';
                $value_return = $new_value;
                break;
            case 3:
                $date_label = 'Semana Actual';
                $value_return = $new_value;
                break;
            case 4:
                $date_label = 'Semana Anterior';
                $value_return = $new_value;
                break;
            case 5:
                $date_label = 'Mes Actual';
                $value_return = $new_value;
                break;
            case 6:
                $date_label = 'Mes Anterior';
                $value_return = $new_value;
                break;
            case 7:
                $date_label = 'Ultimo Trimestre';
                if($new_value > $last_value)
                {
                    $value_return = $new_value;
                }
                else
                {
                    $value_return = $last_value;
                }
                break;
            case 10:
                $date_label = 'Trimestre Actual';
                if($new_value > $last_value)
                {
                    $value_return = $new_value;
                }
                else
                {
                    $value_return = $last_value;
                }
                break;
            case 8:
                $date_label = 'Último Año';
                if($new_value > $last_value)
                {
                    $value_return = $new_value;
                }
                else
                {
                    $value_return = $last_value;
                }
                break;
            case 11:
                $date_label = 'Año Actual';
                if($new_value > $last_value)
                {
                    $value_return = $new_value;
                }
                else
                {
                    $value_return = $last_value;
                }
                break;
            case 9:
                $date_label = 'Personalizado';
                if($new_value > $last_value)
                {
                    $value_return = $new_value;
                }
                else
                {
                    $value_return = $last_value;
                }
                break;
            default:
                $date_label = 'Hoy';
                $value_return = $new_value;
                break;
        }
        return $value_return;
    }
}
