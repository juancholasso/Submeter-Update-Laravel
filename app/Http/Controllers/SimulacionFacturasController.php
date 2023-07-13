<?php

namespace App\Http\Controllers;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Http\Request;
use App\Count;
use App\User;
use Session;
use Auth;

class SimulacionFacturasController extends Controller
{
    function SimulacionFactura($id, Request $request)
    {
        // id representa el id del usuario que se desea ver  y $ctrl el control que indica que
        // la vista mostrada viene del panel administrativo

        $user = User::where('id',$id)->get()->first();
        $contador = strtolower(request()->input('contador'));
        $tipo_count = strtolower(request()->input('tipo'));
        $interval = Session::get('_flash')['intervalos'];
        if(empty($tipo_count))
        {
            $tipo_count = 1;

        }
        $array_coste_activa = array();
        $coste_activa = 0;
        $array_coste_reactiva = array();
        $coste_reactiva = 0;
        $array_potencia_contratada = array();
        $potencia_contratada = 0;
        $array_exceso_potencia = array();
        $exceso_potencia = 0;
        $array_impuesto = array();
        $impuesto = 0;
        $array_equipo = array();
        $equipo = 0;
        // variables
        $E_Activa = array();
        $MES = array();
        $precio_energia = array();
        $E_Reactiva = array();
        $potencia_demandada = array();
        $coste_potencia_contratada_max = array();
        $total1 = 0;
        $Pie1 = array();

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
            Session::flash('message-error', "La base de datos a la que se desea conectar está mal configurada. Por favor, edite los parámetros de configuración de conexión.");
            return \Redirect::back();
        }
        $db = \DB::connection('mysql2');

        $domicilio = $db->table('Area_Cliente')->select(\DB::raw('`DENOMINACIÓN SOCIAL` denominacion_social, `SOCIAL DOMICILIO` social_domicilio, `SUMINISTRO DEL  DOMICILIO` suministro_del_domicilio, CUPS, CIF, `DISTRIBUIDORA EMPRESA` distribuidora_empresa, `COMERCIALIZADORA EMPRESA` comercializadora_empresa, `CONTACTO DE  PERSONA` contacto_persona, `TELÉFONO`, `CONTACTO O  AYUDA` contacto_ayuda, `TARIFA`'))->first();

        $interval = Session::get('_flash')['intervalos'];

        $titulo = 'Simulacion de Factura';

        switch ($interval) {
            case '1':
                $date_from = \Carbon\Carbon::yesterday()->toDateString();
                $date_to = $date_from;
                if(isset(Session::get('_flash')['label_intervalo_navigation']) && Session::get('_flash')['label_intervalo_navigation'] == "Ayer")
                {
                    $date_from = Session::get('_flash')['date_from_personalice'];
                    $date_to = Session::get('_flash')['date_to_personalice'];
                }
                $label_intervalo = 'Ayer';
                break;

            case '3':
                $date_from = \Carbon\Carbon::now()->startOfWeek()->toDateString();
                $date_to = \Carbon\Carbon::now()->endOfWeek()->toDateString();
                if(isset(Session::get('_flash')['label_intervalo_navigation']) && Session::get('_flash')['label_intervalo_navigation'] == 'Semana Actual')
                {
                    $date_from = Session::get('_flash')['date_from_personalice'];
                    $date_to = Session::get('_flash')['date_to_personalice'];
                }
                $label_intervalo = 'Semana Actual';
                break;

            case '4':
                $date_from = \Carbon\Carbon::now()->subWeeks(1)->startOfWeek()->toDateString();
                $date_to = \Carbon\Carbon::now()->subWeeks(1)->endOfWeek()->toDateString();
                if(isset(Session::get('_flash')['label_intervalo_navigation']) && Session::get('_flash')['label_intervalo_navigation'] == "Semana Anterior")
                {
                    $date_from = Session::get('_flash')['date_from_personalice'];
                    $date_to = Session::get('_flash')['date_to_personalice'];
                }
                $label_intervalo = 'Semana Anterior';
                break;

            case '5':
                $date_from = \Carbon\Carbon::now()->startOfMonth()->toDateString();
                $date_to = \Carbon\Carbon::now()->endOfMonth()->toDateString();
                if(isset(Session::get('_flash')['label_intervalo_navigation']) && Session::get('_flash')['label_intervalo_navigation'] == "Mes Actual")
                {
                    $date_from = Session::get('_flash')['date_from_personalice'];
                    $date_to = Session::get('_flash')['date_to_personalice'];
                }
                $label_intervalo = 'Mes Actual';
                break;

            case '6':
                $date_from = \Carbon\Carbon::now()->subMonths(1)->startOfMonth()->toDateString();
                $date_to = \Carbon\Carbon::now()->subMonths(1)->endOfMonth()->toDateString();
                if(isset(Session::get('_flash')['label_intervalo_navigation']) && Session::get('_flash')['label_intervalo_navigation'] == "Mes Anterior")
                {
                    $date_from = Session::get('_flash')['date_from_personalice'];
                    $date_to = Session::get('_flash')['date_to_personalice'];
                }
                $label_intervalo = 'Mes Anterior';
                break;

            case '7':
                $now = \Carbon\Carbon::now()->month;
                $dont = 0;
                if(isset(Session::get('_flash')['label_intervalo_navigation']) && (Session::get('_flash')['label_intervalo_navigation'] == "Ultimo Trimestre" || Session::get('_flash')['label_intervalo_navigation'] == "Último Trimestre"))
                {
                    $now = \Carbon\Carbon::parse(Session::get('_flash')['date_from_personalice'])->addMonth(1)->month;
                    $date_from = Session::get('_flash')['date_from_personalice'];
                    $date_to = Session::get('_flash')['date_to_personalice'];
                    $dont = 1;
                }
                if($dont == 0)
                {
                    if($now == 1 || $now == 2 || $now == 3)
                    {
                        if($dont == 0)
                        {
                            $date_from = \Carbon\Carbon::now()->subMonths($now+2)->startOfMonth()->toDateString();
                            $date_to = \Carbon\Carbon::now()->subMonths($now)->endOfMonth()->toDateString();
                        }
                        $date_from = \Carbon\Carbon::now()->subMonths($now+2)->startOfMonth()->toDateString();
                        $date_to = \Carbon\Carbon::now()->subMonths($now)->endOfMonth()->toDateString();
                        $eje[0] = 'Octubre('.\Carbon\Carbon::now()->year.')';
                        $eje[1] = 'Noviembre('.\Carbon\Carbon::now()->year.')';
                        $eje[2] = 'Diciembre('.\Carbon\Carbon::now()->year.')';
                    }elseif($now == 4 || $now == 7 || $now == 10){
                        // $date_from = \Carbon\Carbon::now()->subMonths(3)->startOfMonth()->toDateString();
                        // $date_to = \Carbon\Carbon::now()->subMonths(1)->endOfMonth()->toDateString();
                        if($dont == 0)
                        {
                            $date_from = \Carbon\Carbon::now()->subMonths(3)->startOfMonth()->toDateString();
                            $date_to = \Carbon\Carbon::now()->subMonths(1)->endOfMonth()->toDateString();
                        }
                        if($now == 4)
                        {
                            $eje[0] = 'Enero('.\Carbon\Carbon::now()->year.')';
                            $eje[1] = 'Febrero('.\Carbon\Carbon::now()->year.')';
                            $eje[2] = 'Marzo('.\Carbon\Carbon::now()->year.')';
                        }elseif($now == 7){
                            $eje[0] = 'Abril('.\Carbon\Carbon::now()->year.')';
                            $eje[1] = 'Mayo('.\Carbon\Carbon::now()->year.')';
                            $eje[2] = 'Junio('.\Carbon\Carbon::now()->year.')';
                        }elseif($now == 10){
                            $eje[0] = 'Julio('.\Carbon\Carbon::now()->year.')';
                            $eje[1] = 'Agosto('.\Carbon\Carbon::now()->year.')';
                            $eje[2] = 'Septiembre('.\Carbon\Carbon::now()->year.')';
                        }
                    }elseif($now == 5 || $now == 8 || $now == 11){
                        // $date_from = \Carbon\Carbon::now()->subMonths(4)->startOfMonth()->toDateString();
                        // $date_to = \Carbon\Carbon::now()->subMonths(2)->endOfMonth()->toDateString();
                        if($dont == 0)
                        {
                            $date_from = \Carbon\Carbon::now()->subMonths(4)->startOfMonth()->toDateString();
                            $date_to = \Carbon\Carbon::now()->subMonths(2)->endOfMonth()->toDateString();
                        }
                        if($now == 5)
                        {
                            $eje[0] = 'Enero('.\Carbon\Carbon::now()->year.')';
                            $eje[1] = 'Febrero('.\Carbon\Carbon::now()->year.')';
                            $eje[2] = 'Marzo('.\Carbon\Carbon::now()->year.')';
                        }elseif($now == 8){
                            $eje[0] = 'Abril('.\Carbon\Carbon::now()->year.')';
                            $eje[1] = 'Mayo('.\Carbon\Carbon::now()->year.')';
                            $eje[2] = 'Junio('.\Carbon\Carbon::now()->year.')';
                        }elseif($now == 11){
                            $eje[0] = 'Julio('.\Carbon\Carbon::now()->year.')';
                            $eje[1] = 'Agosto('.\Carbon\Carbon::now()->year.')';
                            $eje[2] = 'Septiembre('.\Carbon\Carbon::now()->year.')';
                        }
                    }elseif($now == 6 || $now == 9 || $now == 12){
                        // $date_from = \Carbon\Carbon::now()->subMonths(5)->startOfMonth()->toDateString();
                        // $date_to = \Carbon\Carbon::now()->subMonths(3)->endOfMonth()->toDateString();
                        if($dont == 0)
                        {
                            $date_from = \Carbon\Carbon::now()->subMonths(5)->startOfMonth()->toDateString();
                            $date_to = \Carbon\Carbon::now()->subMonths(3)->endOfMonth()->toDateString();
                        }
                        if($now == 6)
                        {
                            $eje[0] = 'Enero('.\Carbon\Carbon::parse($date_from)->year.')';
                            $eje[1] = 'Febrero('.\Carbon\Carbon::now()->year.')';
                            $eje[2] = 'Marzo('.\Carbon\Carbon::now()->year.')';
                        }elseif($now == 9){
                            $eje[0] = 'Abril('.\Carbon\Carbon::now()->year.')';
                            $eje[1] = 'Mayo('.\Carbon\Carbon::now()->year.')';
                            $eje[2] = 'Junio('.\Carbon\Carbon::now()->year.')';
                        }elseif($now == 12){
                            $eje[0] = 'Julio('.\Carbon\Carbon::now()->year.')';
                            $eje[1] = 'Agosto('.\Carbon\Carbon::now()->year.')';
                            $eje[2] = 'Septiembre('.\Carbon\Carbon::now()->year.')';
                        }
                    }
                }else{
                    if($now == 1)
                    {
                        $eje[0] = 'Enero('.\Carbon\Carbon::parse($date_from)->year.')';
                        $eje[1] = 'Febrero('.\Carbon\Carbon::parse($date_from)->year.')';
                        $eje[2] = 'Marzo('.\Carbon\Carbon::parse($date_from)->year.')';
                    }elseif($now == 4){
                        $eje[0] = 'Abril('.\Carbon\Carbon::parse($date_from)->year.')';
                        $eje[1] = 'Mayo('.\Carbon\Carbon::parse($date_from)->year.')';
                        $eje[2] = 'Junio('.\Carbon\Carbon::parse($date_from)->year.')';
                    }elseif($now == 7){
                        $eje[0] = 'Julio('.\Carbon\Carbon::parse($date_from)->year.')';
                        $eje[1] = 'Agosto('.\Carbon\Carbon::parse($date_from)->year.')';
                        $eje[2] = 'Septiembre('.\Carbon\Carbon::parse($date_from)->year.')';
                    }elseif($now == 10){
                        $eje[0] = 'Octubre('.\Carbon\Carbon::parse($date_from)->year.')';
                        $eje[1] = 'Noviembre('.\Carbon\Carbon::parse($date_from)->year.')';
                        $eje[2] = 'Diciembre('.\Carbon\Carbon::parse($date_from)->year.')';
                    }
                }

                $label_intervalo = 'Último Trimestre';
                break;
            case '10':
                $now = \Carbon\Carbon::now()->month;
                $dont = 0;
                if(isset(Session::get('_flash')['label_intervalo_navigation']) && Session::get('_flash')['label_intervalo_navigation'] == "Trimestre Actual")
                {
                    $now = \Carbon\Carbon::parse(Session::get('_flash')['date_from_personalice'])->addMonth(1)->month;
                    $date_from = Session::get('_flash')['date_from_personalice'];
                    $date_to = Session::get('_flash')['date_to_personalice'];
                    $dont = 1;
                }
                if($now == 1 || $now == 2 || $now == 3)
                {
                    if($dont == 0)
                    {
                        $date_from = \Carbon\Carbon::create(null, 1, 1, 0)->toDateString();
                        $date_to = \Carbon\Carbon::now()->addMonths(3-$now)->endOfMonth()->toDateString();
                    }
                }elseif($now == 4 || $now == 5 || $now == 6){
                    if($dont == 0)
                    {
                        $date_from = \Carbon\Carbon::create(null, 4, 1, 0)->toDateString();
                        $date_to = \Carbon\Carbon::now()->addMonths(6-$now)->endOfMonth()->toDateString();
                    }
                }elseif($now == 7 || $now == 8 || $now == 9){
                    if($dont == 0)
                    {
                        $date_from = \Carbon\Carbon::create(null, 7, 1, 0)->toDateString();
                        $date_to = \Carbon\Carbon::now()->addMonths(9-$now)->endOfMonth()->toDateString();
                    }
                }elseif($now == 10 || $now == 11 || $now == 12){
                    if($dont == 0)
                    {
                        $date_from = \Carbon\Carbon::create(null, 10, 1, 0)->toDateString();
                        $date_to = \Carbon\Carbon::now()->addMonths(12-$now)->endOfMonth()->toDateString();
                    }
                }
                $label_intervalo = 'Trimestre Actual';
            break;

            case '11':
                $date_from = \Carbon\Carbon::now()->startOfYear()->toDateString();
                $date_to = \Carbon\Carbon::now()->endOfYear()->toDateString();
                $dont = 0;
                if(isset(Session::get('_flash')['label_intervalo_navigation']) && Session::get('_flash')['label_intervalo_navigation'] == "Año Actual")
                {
                    $date_from = Session::get('_flash')['date_from_personalice'];
                    $date_to = Session::get('_flash')['date_to_personalice'];
                    $dont = 1;
                }
                $label_intervalo = 'Año Actual';
            break;

            case '8':
                $date_from = \Carbon\Carbon::now()->subYears(1)->startOfYear()->toDateString();
                $date_to = \Carbon\Carbon::now()->subYears(1)->endOfYear()->toDateString();
                $dont = 0;
                if(isset(Session::get('_flash')['label_intervalo_navigation']) && Session::get('_flash')['label_intervalo_navigation'] == "Último Año")
                {
                    $date_from = Session::get('_flash')['date_from_personalice'];
                    $date_to = Session::get('_flash')['date_to_personalice'];
                    $dont = 1;
                }
                $label_intervalo = 'Último Año';
                break;

            case '9':
                $date_from = Session::get('_flash')['date_from_personalice'];
                $date_to = Session::get('_flash')['date_to_personalice'];
                $label_intervalo = 'Personalizado';
                // dd($date_from,$date_to);
                break;

            default:
                $date_from = \Carbon\Carbon::now()->toDateString();
                $date_to = $date_from;
                if(isset(Session::get('_flash')['label_intervalo_navigation']) && Session::get('_flash')['label_intervalo_navigation'] == "Ayer")
                {
                    $date_from = Session::get('_flash')['date_from_personalice'];
                    $date_to = Session::get('_flash')['date_to_personalice'];
                }
                $label_intervalo = 'Hoy';
                break;
        }
        $fechaEmision = \Carbon\Carbon::parse($date_from);
        $fechaExpiracion = \Carbon\Carbon::parse($date_to);

        $diasDiferencia = $fechaExpiracion->diffInDays($fechaEmision);
        $data_analisis = [];
        $data_calculos = [];
        if($contador2->tipo < 3)
        {
            for ($i=1; $i < 7 ; $i++) {
                $periodos2[] = 'P'.$i;
            }

            $MES = $db->table('Datos_Contador')->select(\DB::raw("MONTH(date) as MES"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy(\DB::raw('MONTH(date)'))->get()->toArray();

            // COSTE DE LA ENERGÍA ACTIVA
            $precio_energia = $db->table('Precio_Energia')->select('Periodo','precio')->where('date_start','<=',$date_from)->where('date_end','>=',$date_to)->orderBy('Periodo','ASC')->get()->toArray();
            if($tipo_tarifa == 1)
                $coste_energia = $db->table('Coste_Energia_Activa')->select(\DB::raw("SUM(P1) costeP1, SUM(P2) costeP2, SUM(P3) costeP3, SUM(P4) costeP4, SUM(P5) costeP5, SUM(P6) costeP6"))->where('date','>=',$date_from)->where('date','<=',$date_to)->get()->toArray();
            else
                $coste_energia = $db->table('Coste_Energia_Activa')->select(\DB::raw("SUM(P1) costeP1, SUM(P2) costeP2, SUM(P3) costeP3"))->where('date','>=',$date_from)->where('date','<=',$date_to)->get()->toArray();

            $index = 0;
            if($tipo_tarifa == 1)
            {
                foreach ($coste_energia as $coste_ener) {
                    $aux_index = 'costeP';
                    $aux_coste_energia[$index][$aux_index.($index+1)] = $coste_ener->costeP1;
                    $aux_coste_energia[$index][$aux_index.($index+2)] = $coste_ener->costeP2;
                    $aux_coste_energia[$index][$aux_index.($index+3)] = $coste_ener->costeP3;
                    $aux_coste_energia[$index][$aux_index.($index+4)] = $coste_ener->costeP4;
                    $aux_coste_energia[$index][$aux_index.($index+5)] = $coste_ener->costeP5;
                    $aux_coste_energia[$index][$aux_index.($index+6)] = $coste_ener->costeP6;
                    $index++;
                }
            }else{
                foreach ($coste_energia as $coste_ener) {
                    $aux_index = 'costeP';
                    $aux_coste_energia[$index][$aux_index.($index+1)] = $coste_ener->costeP1;
                    $aux_coste_energia[$index][$aux_index.($index+2)] = $coste_ener->costeP2;
                    $aux_coste_energia[$index][$aux_index.($index+3)] = $coste_ener->costeP3;
                    $index++;
                }
            }


            $coste_energia = $aux_coste_energia;

            if($tipo_tarifa == 1)
                $db_coste_reactiva = ($db->table('Coste_Energia_Reactiva')->select(\DB::raw("SUM(P1) costeP1, SUM(P2) costeP2, SUM(P3) costeP3, SUM(P4) costeP4, SUM(P5) costeP5, SUM(P6) costeP6"))->where('date','>=',$date_from)->where('date','<=',$date_to)->get()->toArray());
            else
                $db_coste_reactiva = ($db->table('Coste_Energia_Reactiva')->select(\DB::raw("SUM(P1) costeP1, SUM(P2) costeP2, SUM(P3) costeP3"))->where('date','>=',$date_from)->where('date','<=',$date_to)->get()->toArray());
            $index = 0;
            if($tipo_tarifa == 1)
            {
                foreach ($db_coste_reactiva as $coste_reac) {
                    $aux_index = 'costeP';
                    $aux_coste_reactiva[$index][$aux_index.($index+1)] = $coste_reac->costeP1;
                    $aux_coste_reactiva[$index][$aux_index.($index+2)] = $coste_reac->costeP2;
                    $aux_coste_reactiva[$index][$aux_index.($index+3)] = $coste_reac->costeP3;
                    $aux_coste_reactiva[$index][$aux_index.($index+4)] = $coste_reac->costeP4;
                    $aux_coste_reactiva[$index][$aux_index.($index+5)] = $coste_reac->costeP5;
                    $aux_coste_reactiva[$index][$aux_index.($index+6)] = $coste_reac->costeP6;
                    $index++;
                }
            }else{
                foreach ($db_coste_reactiva as $coste_reac) {
                    $aux_index = 'costeP';
                    $aux_coste_reactiva[$index][$aux_index.($index+1)] = $coste_reac->costeP1;
                    $aux_coste_reactiva[$index][$aux_index.($index+2)] = $coste_reac->costeP2;
                    $aux_coste_reactiva[$index][$aux_index.($index+3)] = $coste_reac->costeP3;
                    $index++;
                }
            }

            $coste_reactiva = $aux_coste_reactiva;


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

            $objAnalisis = new AnalisisPotencia();
            if($tipo_tarifa == 1) {
                $data_analisis = $objAnalisis->calcularCostos6($data_calculos);
            } else {
                $data_analisis = $objAnalisis->calcularCostos3($data_calculos);
            }
            $total_analisis = $data_analisis["totalFP"];

            $E_Activa = $db->table('Energia_Consumida_Activa_y_Reactiva')->select(\DB::raw("SUM(`Energia Activa (kWh)`) as Activa, `Energia Reactiva Inductiva (kVArh)` as Reactiva"))->where("date", '>=',$date_from)->where("date", '<=',$date_to)->groupBy('Periodo')->get()->toArray();

            $aux = array();
            $i = 0;
            $total1 = 0;
            $total_ = 0;
            $total2 = 0;
            $total3 = 0;
            // dd($E_Activa);
            if(!empty($E_Activa))
            {
                foreach ($E_Activa as $val) {
                    // PARTE DE TERMINO ENERGÍA ACTIVA
                    if(isset($coste_energia[0]))
                    {
                        $totales_parciales_energiaAct[] = $coste_energia[0]['costeP'.($i+1)];
                    }else{
                        $totales_parciales_energiaAct[]=0;
                    }
                    $total1 = $total1 + $totales_parciales_energiaAct[$i];

                    if(isset($coste_reactiva[0]))
                    {
                        $totales_parciales_energiaReact[] = $coste_reactiva[0]['costeP'.($i+1)];
                    }else{
                        $totales_parciales_energiaReact[]=0;
                    }
                    $total_ = $total_ + $totales_parciales_energiaReact[$i];

                    // PARTE DE ENERGÍA REACTIVA

                    // PARTE DE TÉRMINO DE POTENCIA

                    if($tipo_tarifa == 1)
                    {
                        if(isset($precio_potencia[$i%6]) && !empty($precio_potencia))
                        {
                            $totales_parciales_potencia[] = floatval($potencia_demandada[$i%6]->potencia_demandada)*floatval($precio_potencia[$i%6]->precio_potencia);
                        }else{
                            $totales_parciales_potencia[] = 0;
                        }
                    }else{
                        $totales_parciales_potencia[]=0;
                    }
                    //$total2 = $total2 + $totales_parciales_potencia[$i];

                    $i++;
                }
                // dd($exceso_potencia[0]);
            }else{
                $totales_parciales_energiaAct[] = 0;
                $total1= 0;
                $totales_parciales_potencia[]=0;
                $total2=0;
                $total3=0;
            }



            if($contador2->iee == 3)
            {
                $aux_iee = 0;
            }elseif($contador2->iee == 2){
                $aux_iee = 0.15;
            }else{
                $aux_iee = 1;
            }


            $sumatoria = $total1 + $total2 + $total_analisis + $total_;
            $impuesto = $sumatoria*0.0511269632*$aux_iee;
            // if($tipo_tarifa == 1)
            // {
                $equipo = ($db->table('Alquiler_Equipo_Medida')->select(\DB::raw("Alquiler_Equipo_Medida valor"))->where('date_start','<=',$date_from)->where('date_end','>=',$date_to)->get()->toArray());
                if(!empty($equipo))
                {
                    foreach ($equipo as $value) {
                        $IVA = ($sumatoria + $impuesto + (floatval($value->valor)*($diasDiferencia+1)))*0.21;
                    }
                }else{
                    $IVA = ($sumatoria + $impuesto)*0.21;
                }
            // }else{
            //     $IVA = ($sumatoria + $impuesto)*0.21;
            // }
        }else{

            $consumo_GN_kWh = $db->table('Consumo_GN_kWh')->select(\DB::raw("SUM(`Consumo GN (kWh)`) consumo"))->where('date','>=',$date_from)->where('date','<=',$date_to)->get();

            $precio_variable = $db->table('Precio_variable')->select(\DB::raw("Precio, Precio_propuesto"))->where('date_start','<=',$date_from)->where('date_end','>=',$date_to)->first();

            $descuento_variable = $db->table('Descuento_variable')->select(\DB::raw("Descuento, Descuento_propuesto"))->where('date_start','<=',$date_from)->where('date_end','>=',$date_to)->first();

            $precio_fijo = $db->table('Precio')->select(\DB::raw("Precio, Precio_propuesto"))->where('date_start','<=',$date_from)->where('date_end','>=',$date_to)->first();

            $descuento = $db->table('Descuento')->select(\DB::raw("Descuento, Descuento_propuesto"))->where('date_start','<=',$date_from)->where('date_end','>=',$date_to)->first();

            $coste_precio_fijo = $db->table('Coste_Precio_Fijo')->select(\DB::raw("SUM(`Coste Precio Fijo (€)`) Precio"))->where('date','>=',$date_from)->where('date','<=',$date_to)->first();

            $coste_descuento_fijo = $db->table('Coste_Descuento_Fijo')->select(\DB::raw("SUM(`Coste Descuento Fijo (€)`) Descuento"))->where('date','>=',$date_from)->where('date','<=',$date_to)->first();

            $coste_termino_fijo = $db->table('Coste_Termino_Fijo')->select(\DB::raw("SUM(`Coste Termino Fijo (€)`) Precio"))->where('date','>=',$date_from)->where('date','<=',$date_to)->first();

            $consumo_GN_kWh_diario = $db->table('ZPI_GN_kWh_diario')->select(\DB::raw("SUM(`Qd Diaria (kWh)`) consumo"))->where('date',$date_from)->get();

            $I_E_HC = $db->table('Impuesto_HC')->select(\DB::raw("Impuesto_HC valor"))->where('date_start','<=',$date_from)->where('date_end','>=',$date_to)->first();

            $equipo_medida = $db->table('Equipo_de_medida')->select(\DB::raw("Alquiler_Equipo_Medida valor"))->where('date_start','<=',$date_from)->where('date_end','>=',$date_to)->first();
        }

        $count_id = $contador2->id;

        $hoy = \Carbon\Carbon::now();

        $cont = $contador;
        $contador_label = $contador2->count_label;
        $dir_image_count = $db->table('Area_Cliente')->select(\DB::raw('`LOGOTIPO` logo'))->first()->logo;
        \DB::disconnect('mysql2');

        if(($id != 0 && Auth::user()->id == $id) || Auth::user()->tipo == 1)
        {
            $user = User::where('id',$id)->get()->first();
            if(Auth::user()->tipo != 1)
                $ctrl = 0;
            else
                $ctrl = 1;
            if($tipo_count < 3)
                return view('simulacion_facturas.simulacion_facturas',compact('user','titulo','id','precio_energia','E_Activa', 'potencia_demandada', 'exceso_potencia', 'impuesto', 'equipo','hoy','date_from','date_to','cont','total1','total2','total3','IVA','sumatoria','label_intervalo','ctrl','tipo_count','contador_label','diasDiferencia','domicilio','dir_image_count','total_','coste_reactiva','tipo_tarifa','coste_potencia_contratada_max', 'count_id', 'data_analisis', 'data_calculos', 'Pie1'));
            else
                return view('Gas.simulacion_facturas',compact('user','titulo','id','hoy','date_from','date_to','cont','label_intervalo','ctrl','tipo_count','contador_label','consumo_GN_kWh','consumo_GN_kWh_diario','I_E_HC','equipo_medida','precio_variable','precio_fijo','descuento','descuento_variable','coste_precio_fijo','coste_descuento_fijo','coste_termino_fijo','diasDiferencia','domicilio','dir_image_count','tipo_tarifa','coste_potencia_contratada_max', 'count_id'));
        }
        return \Redirect::to('https://submeter.es/');
        // return view('simulacion_facturas.simulacion_facturas',compact('user','titulo','id','precio_potencia','precio_energia','E_Activa', 'potencia_demandada', 'exceso_potencia', 'impuesto', 'equipo','hoy','date_from','date_to','cont','total1','total2','total3','IVA','sumatoria','label_intervalo','tipo_count','coste_potencia_contratada','contador_label','diasDiferencia','domicilio','dir_image_count','tipo_tarifa','coste_potencia_contratada_max'));
    }

    function SimulacionFacturaPdf($id, Request $request){
        // id representa el id del usuario que se desea ver  y $ctrl el control que indica que
        // la vista mostrada viene del panel administrativo

        $user = User::where('id',$id)->get()->first();
        $contador = strtolower(request()->input('contador'));
        $array_coste_activa = array();
        $coste_activa = 0;
        $array_coste_reactiva = array();
        $coste_reactiva = 0;
        $array_potencia_contratada = array();
        $potencia_contratada = 0;
        $array_exceso_potencia = array();
        $exceso_potencia = 0;
        $array_impuesto = array();
        $impuesto = 0;
        $array_equipo = array();
        $equipo = 0;
        // variables
        $E_Activa = array();
        $MES = array();
        $precio_energia = array();
        $E_Reactiva = array();
        $potencia_demandada = array();
        $coste_potencia_contratada_max = array();

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
            Session::flash('message-error', "La base de datos a la que se desea conectar está mal configurada. Por favor, edite los parámetros de configuración de conexión.");
            return \Redirect::back();
        }
        $db = \DB::connection('mysql2');

        $interval = Session::get('_flash')['intervalos'];

        $titulo = 'Simulacion de Factura';

        $domicilio = '';

        $data_domicilio = $db->table("Area_Cliente")->select(\DB::raw("`SUMINISTRO DEL  DOMICILIO` as domicilio"))->first();
        if($data_domicilio)
        {
            $domicilio = $data_domicilio->domicilio;
        }

        switch ($interval) {
            case '1':
                $date_from = \Carbon\Carbon::yesterday()->toDateString();
                $date_to = $date_from;
                if(isset(Session::get('_flash')['label_intervalo_navigation']) && Session::get('_flash')['label_intervalo_navigation'] == "Ayer")
                {
                    $date_from = Session::get('_flash')['date_from_personalice'];
                    $date_to = Session::get('_flash')['date_to_personalice'];
                }
                $label_intervalo = 'Ayer';
                break;

            case '3':
                $date_from = \Carbon\Carbon::now()->startOfWeek()->toDateString();
                $date_to = \Carbon\Carbon::now()->endOfWeek()->toDateString();
                if(isset(Session::get('_flash')['label_intervalo_navigation']) && Session::get('_flash')['label_intervalo_navigation'] == 'Semana Actual')
                {
                    $date_from = Session::get('_flash')['date_from_personalice'];
                    $date_to = Session::get('_flash')['date_to_personalice'];
                }
                $label_intervalo = 'Semana Actual';
                break;

            case '4':
                $date_from = \Carbon\Carbon::now()->subWeeks(1)->startOfWeek()->toDateString();
                $date_to = \Carbon\Carbon::now()->subWeeks(1)->endOfWeek()->toDateString();
                if(isset(Session::get('_flash')['label_intervalo_navigation']) && Session::get('_flash')['label_intervalo_navigation'] == "Semana Anterior")
                {
                    $date_from = Session::get('_flash')['date_from_personalice'];
                    $date_to = Session::get('_flash')['date_to_personalice'];
                }
                $label_intervalo = 'Semana Anterior';
                break;

            case '5':
                $date_from = \Carbon\Carbon::now()->startOfMonth()->toDateString();
                $date_to = \Carbon\Carbon::now()->endOfMonth()->toDateString();
                if(isset(Session::get('_flash')['label_intervalo_navigation']) && Session::get('_flash')['label_intervalo_navigation'] == "Mes Actual")
                {
                    $date_from = Session::get('_flash')['date_from_personalice'];
                    $date_to = Session::get('_flash')['date_to_personalice'];
                }
                $label_intervalo = 'Mes Actual';
                break;

            case '6':
                $date_from = \Carbon\Carbon::now()->subMonths(1)->startOfMonth()->toDateString();
                $date_to = \Carbon\Carbon::now()->subMonths(1)->endOfMonth()->toDateString();
                if(isset(Session::get('_flash')['label_intervalo_navigation']) && Session::get('_flash')['label_intervalo_navigation'] == "Mes Anterior")
                {
                    $date_from = Session::get('_flash')['date_from_personalice'];
                    $date_to = Session::get('_flash')['date_to_personalice'];
                }
                $label_intervalo = 'Mes Anterior';
                break;

            case '7':
                $now = \Carbon\Carbon::now()->month;
                $dont = 0;
                if(isset(Session::get('_flash')['label_intervalo_navigation']) && Session::get('_flash')['label_intervalo_navigation'] == "Ultimo Trimestre")
                {
                    $now = \Carbon\Carbon::parse(Session::get('_flash')['date_from_personalice'])->addMonth(1)->month;
                    $date_from = Session::get('_flash')['date_from_personalice'];
                    $date_to = Session::get('_flash')['date_to_personalice'];
                    $dont = 1;
                }
                if($dont == 0)
                {
                    if($now == 1 || $now == 2 || $now == 3)
                    {
                        if($dont == 0)
                        {
                            $date_from = \Carbon\Carbon::now()->subMonths($now+2)->startOfMonth()->toDateString();
                            $date_to = \Carbon\Carbon::now()->subMonths($now)->endOfMonth()->toDateString();
                        }
                        $date_from = \Carbon\Carbon::now()->subMonths($now+2)->startOfMonth()->toDateString();
                        $date_to = \Carbon\Carbon::now()->subMonths($now)->endOfMonth()->toDateString();
                        $eje[0] = 'Octubre('.\Carbon\Carbon::now()->year.')';
                        $eje[1] = 'Noviembre('.\Carbon\Carbon::now()->year.')';
                        $eje[2] = 'Diciembre('.\Carbon\Carbon::now()->year.')';
                    }elseif($now == 4 || $now == 7 || $now == 10){
                        // $date_from = \Carbon\Carbon::now()->subMonths(3)->startOfMonth()->toDateString();
                        // $date_to = \Carbon\Carbon::now()->subMonths(1)->endOfMonth()->toDateString();
                        if($dont == 0)
                        {
                            $date_from = \Carbon\Carbon::now()->subMonths(3)->startOfMonth()->toDateString();
                            $date_to = \Carbon\Carbon::now()->subMonths(1)->endOfMonth()->toDateString();
                        }
                        if($now == 4)
                        {
                            $eje[0] = 'Enero('.\Carbon\Carbon::now()->year.')';
                            $eje[1] = 'Febrero('.\Carbon\Carbon::now()->year.')';
                            $eje[2] = 'Marzo('.\Carbon\Carbon::now()->year.')';
                        }elseif($now == 7){
                            $eje[0] = 'Abril('.\Carbon\Carbon::now()->year.')';
                            $eje[1] = 'Mayo('.\Carbon\Carbon::now()->year.')';
                            $eje[2] = 'Junio('.\Carbon\Carbon::now()->year.')';
                        }elseif($now == 10){
                            $eje[0] = 'Julio('.\Carbon\Carbon::now()->year.')';
                            $eje[1] = 'Agosto('.\Carbon\Carbon::now()->year.')';
                            $eje[2] = 'Septiembre('.\Carbon\Carbon::now()->year.')';
                        }
                    }elseif($now == 5 || $now == 8 || $now == 11){
                        // $date_from = \Carbon\Carbon::now()->subMonths(4)->startOfMonth()->toDateString();
                        // $date_to = \Carbon\Carbon::now()->subMonths(2)->endOfMonth()->toDateString();
                        if($dont == 0)
                        {
                            $date_from = \Carbon\Carbon::now()->subMonths(4)->startOfMonth()->toDateString();
                            $date_to = \Carbon\Carbon::now()->subMonths(2)->endOfMonth()->toDateString();
                        }
                        if($now == 5)
                        {
                            $eje[0] = 'Enero('.\Carbon\Carbon::now()->year.')';
                            $eje[1] = 'Febrero('.\Carbon\Carbon::now()->year.')';
                            $eje[2] = 'Marzo('.\Carbon\Carbon::now()->year.')';
                        }elseif($now == 8){
                            $eje[0] = 'Abril('.\Carbon\Carbon::now()->year.')';
                            $eje[1] = 'Mayo('.\Carbon\Carbon::now()->year.')';
                            $eje[2] = 'Junio('.\Carbon\Carbon::now()->year.')';
                        }elseif($now == 11){
                            $eje[0] = 'Julio('.\Carbon\Carbon::now()->year.')';
                            $eje[1] = 'Agosto('.\Carbon\Carbon::now()->year.')';
                            $eje[2] = 'Septiembre('.\Carbon\Carbon::now()->year.')';
                        }
                    }elseif($now == 6 || $now == 9 || $now == 12){
                        // $date_from = \Carbon\Carbon::now()->subMonths(5)->startOfMonth()->toDateString();
                        // $date_to = \Carbon\Carbon::now()->subMonths(3)->endOfMonth()->toDateString();
                        if($dont == 0)
                        {
                            $date_from = \Carbon\Carbon::now()->subMonths(5)->startOfMonth()->toDateString();
                            $date_to = \Carbon\Carbon::now()->subMonths(3)->endOfMonth()->toDateString();
                        }
                        if($now == 6)
                        {
                            $eje[0] = 'Enero('.\Carbon\Carbon::parse($date_from)->year.')';
                            $eje[1] = 'Febrero('.\Carbon\Carbon::now()->year.')';
                            $eje[2] = 'Marzo('.\Carbon\Carbon::now()->year.')';
                        }elseif($now == 9){
                            $eje[0] = 'Abril('.\Carbon\Carbon::now()->year.')';
                            $eje[1] = 'Mayo('.\Carbon\Carbon::now()->year.')';
                            $eje[2] = 'Junio('.\Carbon\Carbon::now()->year.')';
                        }elseif($now == 12){
                            $eje[0] = 'Julio('.\Carbon\Carbon::now()->year.')';
                            $eje[1] = 'Agosto('.\Carbon\Carbon::now()->year.')';
                            $eje[2] = 'Septiembre('.\Carbon\Carbon::now()->year.')';
                        }
                    }
                }else{
                    if($now == 1)
                    {
                        $eje[0] = 'Enero('.\Carbon\Carbon::parse($date_from)->year.')';
                        $eje[1] = 'Febrero('.\Carbon\Carbon::parse($date_from)->year.')';
                        $eje[2] = 'Marzo('.\Carbon\Carbon::parse($date_from)->year.')';
                    }elseif($now == 4){
                        $eje[0] = 'Abril('.\Carbon\Carbon::parse($date_from)->year.')';
                        $eje[1] = 'Mayo('.\Carbon\Carbon::parse($date_from)->year.')';
                        $eje[2] = 'Junio('.\Carbon\Carbon::parse($date_from)->year.')';
                    }elseif($now == 7){
                        $eje[0] = 'Julio('.\Carbon\Carbon::parse($date_from)->year.')';
                        $eje[1] = 'Agosto('.\Carbon\Carbon::parse($date_from)->year.')';
                        $eje[2] = 'Septiembre('.\Carbon\Carbon::parse($date_from)->year.')';
                    }elseif($now == 10){
                        $eje[0] = 'Octubre('.\Carbon\Carbon::parse($date_from)->year.')';
                        $eje[1] = 'Noviembre('.\Carbon\Carbon::parse($date_from)->year.')';
                        $eje[2] = 'Diciembre('.\Carbon\Carbon::parse($date_from)->year.')';
                    }
                }
                $label_intervalo = 'Último Trimestre';
            break;

            case '10':
                $now = \Carbon\Carbon::now()->month;
                $dont = 0;
                if(isset(Session::get('_flash')['label_intervalo_navigation']) && Session::get('_flash')['label_intervalo_navigation'] == "Trimestre Actual")
                {
                    $now = \Carbon\Carbon::parse(Session::get('_flash')['date_from_personalice'])->addMonth(1)->month;
                    $date_from = Session::get('_flash')['date_from_personalice'];
                    $date_to = Session::get('_flash')['date_to_personalice'];
                    $dont = 1;
                }
                if($now == 1 || $now == 2 || $now == 3)
                {
                    if($dont == 0)
                    {
                        $date_from = \Carbon\Carbon::create(null, 1, 1, 0)->toDateString();
                        $date_to = \Carbon\Carbon::now()->addMonths(3-$now)->endOfMonth()->toDateString();
                    }
                }elseif($now == 4 || $now == 5 || $now == 6){
                    if($dont == 0)
                    {
                        $date_from = \Carbon\Carbon::create(null, 4, 1, 0)->toDateString();
                        $date_to = \Carbon\Carbon::now()->addMonths(6-$now)->endOfMonth()->toDateString();
                    }
                }elseif($now == 7 || $now == 8 || $now == 9){
                    if($dont == 0)
                    {
                        $date_from = \Carbon\Carbon::create(null, 7, 1, 0)->toDateString();
                        $date_to = \Carbon\Carbon::now()->addMonths(9-$now)->endOfMonth()->toDateString();
                    }
                }elseif($now == 10 || $now == 11 || $now == 12){
                    if($dont == 0)
                    {
                        $date_from = \Carbon\Carbon::create(null, 10, 1, 0)->toDateString();
                        $date_to = \Carbon\Carbon::now()->addMonths(12-$now)->endOfMonth()->toDateString();
                    }
                }
                $label_intervalo = 'Trimestre Actual';
            break;

            case '11':
                $date_from = \Carbon\Carbon::now()->startOfYear()->toDateString();
                $date_to = \Carbon\Carbon::now()->endOfYear()->toDateString();
                $dont = 0;
                if(isset(Session::get('_flash')['label_intervalo_navigation']) && Session::get('_flash')['label_intervalo_navigation'] == "Año Actual")
                {
                    $date_from = Session::get('_flash')['date_from_personalice'];
                    $date_to = Session::get('_flash')['date_to_personalice'];
                    $dont = 1;
                }
                $label_intervalo = 'Año Actual';
            break;

            case '8':
                $date_from = \Carbon\Carbon::now()->subYears(1)->startOfYear()->toDateString();
                $date_to = \Carbon\Carbon::now()->subYears(1)->endOfYear()->toDateString();
                $dont = 0;
                if(isset(Session::get('_flash')['label_intervalo_navigation']) && Session::get('_flash')['label_intervalo_navigation'] == "Último Año")
                {
                    $date_from = Session::get('_flash')['date_from_personalice'];
                    $date_to = Session::get('_flash')['date_to_personalice'];
                    $dont = 1;
                }
                $label_intervalo = 'Último Año';
            break;

            case '9':
                $date_from = Session::get('_flash')['date_from_personalice'];
                $date_to = Session::get('_flash')['date_to_personalice'];
                $label_intervalo = 'Personalizado';
                // dd($date_from,$date_to);
            break;

            default:
                $date_from = \Carbon\Carbon::now()->toDateString();
                $date_to = $date_from;
                if(isset(Session::get('_flash')['label_intervalo_navigation']) && Session::get('_flash')['label_intervalo_navigation'] == "Ayer")
                {
                    $date_from = Session::get('_flash')['date_from_personalice'];
                    $date_to = Session::get('_flash')['date_to_personalice'];
                }
                $label_intervalo = 'Hoy';
            break;
        }
        $fechaEmision = \Carbon\Carbon::parse($date_from);
        $fechaExpiracion = \Carbon\Carbon::parse($date_to);

        $diasDiferencia = $fechaExpiracion->diffInDays($fechaEmision);

        $data_analisis = [];
        $data_calculos = [];
        if($tipo_count < 3)
        {
            for ($i=1; $i < 7 ; $i++) {
                $periodos2[] = 'P'.$i;
            }

            $MES = $db->table('Datos_Contador')->select(\DB::raw("MONTH(date) as MES"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy(\DB::raw('MONTH(date)'))->get()->toArray();

            // COSTE DE LA ENERGÍA ACTIVA
            $precio_energia = $db->table('Precio_Energia')->select('precio')->where('date_start','<=',$date_from)->where('date_end','>=',$date_to)->orderBy('Periodo','ASC')->get()->toArray();
            if($tipo_tarifa == 1)
                $coste_energia = $db->table('Coste_Energia_Activa')->select(\DB::raw("SUM(P1) costeP1, SUM(P2) costeP2, SUM(P3) costeP3, SUM(P4) costeP4, SUM(P5) costeP5, SUM(P6) costeP6"))->where('date','>=',$date_from)->where('date','<=',$date_to)->get()->toArray();
            else
                $coste_energia = $db->table('Coste_Energia_Activa')->select(\DB::raw("SUM(P1) costeP1, SUM(P2) costeP2, SUM(P3) costeP3"))->where('date','>=',$date_from)->where('date','<=',$date_to)->get()->toArray();

            $index = 0;
            if($tipo_tarifa == 1)
            {
                foreach ($coste_energia as $coste_ener) {
                    $aux_index = 'costeP';
                    $aux_coste_energia[$index][$aux_index.($index+1)] = $coste_ener->costeP1;
                    $aux_coste_energia[$index][$aux_index.($index+2)] = $coste_ener->costeP2;
                    $aux_coste_energia[$index][$aux_index.($index+3)] = $coste_ener->costeP3;
                    $aux_coste_energia[$index][$aux_index.($index+4)] = $coste_ener->costeP4;
                    $aux_coste_energia[$index][$aux_index.($index+5)] = $coste_ener->costeP5;
                    $aux_coste_energia[$index][$aux_index.($index+6)] = $coste_ener->costeP6;
                    $index++;
                }
            }else{
                foreach ($coste_energia as $coste_ener) {
                    $aux_index = 'costeP';
                    $aux_coste_energia[$index][$aux_index.($index+1)] = $coste_ener->costeP1;
                    $aux_coste_energia[$index][$aux_index.($index+2)] = $coste_ener->costeP2;
                    $aux_coste_energia[$index][$aux_index.($index+3)] = $coste_ener->costeP3;
                    $index++;
                }
            }


            $coste_energia = $aux_coste_energia;

            if($tipo_tarifa == 1)
                $db_coste_reactiva = ($db->table('Coste_Energia_Reactiva')->select(\DB::raw("SUM(P1) costeP1, SUM(P2) costeP2, SUM(P3) costeP3, SUM(P4) costeP4, SUM(P5) costeP5, SUM(P6) costeP6"))->where('date','>=',$date_from)->where('date','<=',$date_to)->get()->toArray());
            else
                $db_coste_reactiva = ($db->table('Coste_Energia_Reactiva')->select(\DB::raw("SUM(P1) costeP1, SUM(P2) costeP2, SUM(P3) costeP3"))->where('date','>=',$date_from)->where('date','<=',$date_to)->get()->toArray());

            $index = 0;
            if($tipo_tarifa == 1)
            {
                foreach ($db_coste_reactiva as $coste_reac) {
                    $aux_index = 'costeP';
                    $aux_coste_reactiva[$index][$aux_index.($index+1)] = $coste_reac->costeP1;
                    $aux_coste_reactiva[$index][$aux_index.($index+2)] = $coste_reac->costeP2;
                    $aux_coste_reactiva[$index][$aux_index.($index+3)] = $coste_reac->costeP3;
                    $aux_coste_reactiva[$index][$aux_index.($index+4)] = $coste_reac->costeP4;
                    $aux_coste_reactiva[$index][$aux_index.($index+5)] = $coste_reac->costeP5;
                    $aux_coste_reactiva[$index][$aux_index.($index+6)] = $coste_reac->costeP6;
                    $index++;
                }
            }else{
                foreach ($db_coste_reactiva as $coste_reac) {
                    $aux_index = 'costeP';
                    $aux_coste_reactiva[$index][$aux_index.($index+1)] = $coste_reac->costeP1;
                    $aux_coste_reactiva[$index][$aux_index.($index+2)] = $coste_reac->costeP2;
                    $aux_coste_reactiva[$index][$aux_index.($index+3)] = $coste_reac->costeP3;
                    $index++;
                }
            }

            $coste_reactiva = $aux_coste_reactiva;

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

            $objAnalisis = new AnalisisPotencia();
            if($tipo_tarifa == 1) {
                $data_analisis = $objAnalisis->calcularCostos6($data_calculos);
            } else {
                $data_analisis = $objAnalisis->calcularCostos3($data_calculos);
            }

            $total_analisis = $data_analisis["totalFP"];

            $E_Activa = $db->table('Energia_Consumida_Activa_y_Reactiva')->select(\DB::raw("SUM(`Energia Activa (kWh)`) as Activa"))->where("date", '>=',$date_from)->where("date", '<=',$date_to)->groupBy('Periodo')->get()->toArray();

            $aux = array();
            $i = 0;
            $total1 = 0;
            $total_ = 0;
            $total2 = 0;
            $total3 = 0;

            if(!empty($E_Activa))
            {
                foreach ($E_Activa as $val) {
                    // PARTE DE TERMINO ENERGÍA ACTIVA
                    if(isset($coste_energia[0]))
                    {
                        $totales_parciales_energiaAct[] = $coste_energia[0]['costeP'.($i+1)];
                    }else{
                        $totales_parciales_energiaAct[]=0;
                    }
                    $total1 = $total1 + $totales_parciales_energiaAct[$i];

                    // PARTE DE ENERGÍA REACTIVA
                    if(isset($coste_reactiva[0]))
                    {
                        $totales_parciales_energiaReact[] = $coste_reactiva[0]['costeP'.($i+1)];
                    }else{
                        $totales_parciales_energiaReact[]=0;
                    }
                    $total_ = $total_ + $totales_parciales_energiaReact[$i];

                    // PARTE DE TÉRMINO DE POTENCIA
                    if($tipo_tarifa == 1)
                    {
                        if(isset($precio_potencia[$i%6]) && !empty($precio_potencia))
                        {
                            $totales_parciales_potencia[] = floatval($potencia_demandada[$i%6]->potencia_demandada)*floatval($precio_potencia[$i%6]->precio_potencia);
                        }else{
                            $totales_parciales_potencia[]=0;
                        }
                    }else{
                        $totales_parciales_potencia[]=0;
                    }

                    $total2 = $total2 + $totales_parciales_potencia[$i];;

                    $i++;
                }


            }else{
                $totales_parciales_energiaAct[] = 0;
                $total1= 0;
                $totales_parciales_potencia[]=0;
                $total2=0;
                $total3=0;
            }



            if($contador2->iee == 3)
            {
                $aux_iee = 0;
            }elseif($contador2->iee == 2){
                $aux_iee = 0.15;
            }else{
                $aux_iee = 1;
            }

            $sumatoria = $total1 + $total2 + $total_analisis + $total_;
            $impuesto = $sumatoria*0.0511269632*$aux_iee;

            // if($tipo_tarifa == 1)
            // {
                $equipo = ($db->table('Alquiler_Equipo_Medida')->select(\DB::raw("Alquiler_Equipo_Medida valor"))->where('date_start','<=',$date_from)->where('date_end','>=',$date_to)->get()->toArray());
                if(!empty($equipo))
                {
                    foreach ($equipo as $value) {
                        $IVA = ($sumatoria + $impuesto + (floatval($value->valor)*($diasDiferencia+1)))*0.21;
                    }
                }else{
                    $IVA = ($sumatoria + $impuesto)*0.21;
                }
            // }else{
            //     $IVA = ($sumatoria + $impuesto)*0.21;
            // }
        }else{
            $consumo_GN_kWh = $db->table('Consumo_GN_kWh')->select(\DB::raw("SUM(`Consumo GN (kWh)`) consumo"))->where('date','>=',$date_from)->where('date','<=',$date_to)->get();

            $precio_variable = $db->table('Precio_variable')->select(\DB::raw("Precio, Precio_propuesto"))->where('date_start','<=',$date_from)->where('date_end','>=',$date_to)->first();

            $descuento_variable = $db->table('Descuento_variable')->select(\DB::raw("Descuento, Descuento_propuesto"))->where('date_start','<=',$date_from)->where('date_end','>=',$date_to)->first();

            $precio_fijo = $db->table('Precio')->select(\DB::raw("Precio, Precio_propuesto"))->where('date_start','<=',$date_from)->where('date_end','>=',$date_to)->first();

            $descuento = $db->table('Descuento')->select(\DB::raw("Descuento, Descuento_propuesto"))->where('date_start','<=',$date_from)->where('date_end','>=',$date_to)->first();

            $coste_precio_fijo = $db->table('Coste_Precio_Fijo')->select(\DB::raw("SUM(`Coste Precio Fijo (€)`) Precio"))->where('date','>=',$date_from)->where('date','<=',$date_to)->first();

            $coste_descuento_fijo = $db->table('Coste_Descuento_Fijo')->select(\DB::raw("SUM(`Coste Descuento Fijo (€)`) Descuento"))->where('date','>=',$date_from)->where('date','<=',$date_to)->first();

            $coste_termino_fijo = $db->table('Coste_Termino_Fijo')->select(\DB::raw("SUM(`Coste Termino Fijo (€)`) Precio"))->where('date','>=',$date_from)->where('date','<=',$date_to)->first();

            $consumo_GN_kWh_diario = $db->table('ZPI_GN_kWh_diario')->select(\DB::raw("SUM(`Qd Diaria (kWh)`) consumo"))->where('date',$date_from)->get();

            $I_E_HC = $db->table('Impuesto_HC')->select(\DB::raw("Impuesto_HC valor"))->where('date_start','<=',$date_from)->where('date_end','>=',$date_to)->first();

            $equipo_medida = $db->table('Equipo_de_medida')->select(\DB::raw("Alquiler_Equipo_Medida valor"))->where('date_start','<=',$date_from)->where('date_end','>=',$date_to)->first();
        }

        if($tipo_count < 3)
        {
            $dataPlot = array();
            $dataPlot["Término de Energía"] = $total_ + $total1;
            if(isset($equipo) && is_array($equipo) && is_object($equipo[0]))
            {
                $dataPlot["Equipo de Medida"] = $equipo[0]->valor*($diasDiferencia+1);
            }
            else
            {
                $dataPlot["Equipo de Medida"] = 0;
            }
            $dataPlot["Término de Potencia"] = $total_analisis;
            $dataPlot["I.E.E."] = $impuesto;

            $dataPlot = serialize($dataPlot);
            $dataPlot = base64_encode($dataPlot);

            $pathImage = url("grafica_analisis_potencia/".$dataPlot);
            $file_name_plot = tempnam(sys_get_temp_dir(), "").".png";

            $process_command = "wkhtmltoimage --crop-x 0 --crop-w 600 --crop-y 0 --crop-h 600 ".$pathImage." ".$file_name_plot;
            $process = new Process($process_command);
            $process->run();

            if (!$process->isSuccessful()) {
                try
                {
                    $message_process = $process->getOutput();
                    $data = json_decode($message_process);
                    $msg_error = "Command: ". $process_command." \nResponse: ".$data;
                    Log::info($msg_error);
                }
                catch(Exception $error)
                {

                }

            }

            $resource_image = imagecreatefrompng ($file_name_plot);
            imagealphablending($resource_image,false);
            imagesavealpha($resource_image,true);

            $new_resource = imagecropauto($resource_image, IMG_CROP_WHITE);
            if($new_resource)
            {
                imagealphablending($new_resource,false);
                imagesavealpha($new_resource,true);
                imagepng ($new_resource, $file_name_plot , 6, PNG_ALL_FILTERS);
            }

        } else {

            $total1 = 0.0;
            if(isset($consumo_GN_kWh[0]) && isset($precio_variable))
            {
                $total1 += $consumo_GN_kWh[0]->consumo*$precio_variable->Precio;
            }
            if(isset($consumo_GN_kWh[0]) && isset($descuento_variable->Descuento))
            {
                $total1 += $consumo_GN_kWh[0]->consumo*(-1)*$descuento_variable->Descuento;
            }

            $total2 = 0.0;
            if(isset($coste_termino_fijo))
            {
                $total2 = $coste_termino_fijo->Precio;
            }

            $total3 = 0.0;
            if(isset($I_E_HC->valor) && isset($consumo_GN_kWh[0]->consumo))
            {
                $total3 = $consumo_GN_kWh[0]->consumo*$I_E_HC->valor;
            }

            $total4 = 0.0;
            if(isset($equipo_medida->valor))
            {
                $total4 = $equipo_medida->valor*($diasDiferencia+1);
            }

            $dataPlot = array();
            $dataPlot["Término Variable"] = floatval($total1);
            $dataPlot["Término Fijo"] = floatval($total2);
            $dataPlot["I.E.HC"] = floatval($total3);
            $dataPlot["Equipo de Medida"] = floatval($total4);

            $dataPlot = serialize($dataPlot);
            $dataPlot = base64_encode($dataPlot);

            $pathImage = url("grafica_analisis_potencia/".$dataPlot);
            $file_name_plot = tempnam(sys_get_temp_dir(), "").".png";

            $process_command = "wkhtmltoimage --crop-x 0 --crop-w 600 --crop-y 0 --crop-h 600 ".$pathImage." ".$file_name_plot;
            $process = new Process($process_command);
            $process->run();

            if (!$process->isSuccessful()) {
                try
                {
                    $message_process = $process->getOutput();
                    $data = json_decode($message_process);
                    $msg_error = "Command: ". $process_command." \nResponse: ".$data;
                    Log::info($msg_error);
                }
                catch(Exception $error)
                {

                }

            }

            $resource_image = imagecreatefrompng ($file_name_plot);
            imagealphablending($resource_image,false);
            imagesavealpha($resource_image,true);

            $new_resource = imagecropauto($resource_image, IMG_CROP_WHITE);
            if($new_resource)
            {
                imagealphablending($new_resource,false);
                imagesavealpha($new_resource,true);
                imagepng ($new_resource, $file_name_plot , 6, PNG_ALL_FILTERS);
            }
        }

        $hoy = \Carbon\Carbon::now();
        $cont = $contador;
        $contador_label = $contador2->count_label;
        if(!is_null($db->table('Area_Cliente')->select(\DB::raw('`LOGOTIPO` logo'))->first()))
            $dir_image_count = $db->table('Area_Cliente')->select(\DB::raw('`LOGOTIPO` logo'))->first()->logo;
        else
            $dir_image_count = $db->table('Area_Cliente')->select(\DB::raw('`LOGOTIPO` logo'))->first();
        \DB::disconnect('mysql2');

        if(($contador2->user_id != 0 && Auth::user()->id == $contador2->user_id) || Auth::user()->tipo == 1 || true)
        {
            if(Auth::user()->tipo != 1)
                $ctrl = 0;
            else
                $ctrl = 1;

            if(!is_null($user->_perfil))
                $image = $user->_perfil->avatar;
            else{
                $image = "images/avatar.png";
            }
            //dd($contador_label);
            $titulo = "Simulacion_Facturas";
            $nombreArchivoPdf = $titulo."_".$contador_label."_".$date_from."_".$date_to.".pdf";
            if($tipo_count < 3)
                $pdf = \PDF::loadView('simulacion_facturas.simulacion_facturas_pdf',compact('user','titulo','id','precio_potencia','precio_energia','E_Activa', 'potencia_demandada', 'exceso_potencia', 'impuesto', 'equipo','hoy','date_from','date_to','cont','total1','total2','total3','IVA','sumatoria','label_intervalo','ctrl','image','coste_potencia_contratada','contador_label','diasDiferencia','dir_image_count','coste_reactiva','total_','tipo_tarifa','coste_potencia_contratada_max', 'file_name_plot', 'domicilio', 'data_analisis', 'data_calculos'));
            else
                $pdf = \PDF::loadView('Gas.simulacion_facturas_pdf',compact('user','titulo','id','hoy','date_from','date_to','cont','label_intervalo','ctrl','tipo_count','contador_label','consumo_GN_kWh','consumo_GN_kWh_diario','I_E_HC','equipo_medida','precio_variable','precio_fijo','descuento','descuento_variable','image','ctrl','coste_precio_fijo','coste_descuento_fijo','coste_termino_fijo','diasDiferencia','dir_image_count','tipo_tarifa','coste_potencia_contratada_max', 'file_name_plot', 'domicilio'));
                $pdf->setPaper("Letter", "portrait");
            return $pdf->download($nombreArchivoPdf);
        }
        return \Redirect::to('https://submeter.es/');
    }
}
