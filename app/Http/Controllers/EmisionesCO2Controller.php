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

class EmisionesCO2Controller extends Controller
{
    function EmisionesCO2($id,Request $request)
    {
        $user = User::find($id);
        $contador = (request()->input('contador'));

        $emisiones = array();
        $emisiones_antes = array();
        $eje = array();
        $eje_antes = array();
        $emisiones2 = array();
        $emisiones2_antes = array();
        $dates = array();


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

                $emisiones = $db->table('Emisiones_CO2')->select(\DB::raw("DATE_FORMAT(time, '%H:%i') eje, SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones"))->where('date',$date_from)->groupBy('time')->get();

                $date_antes_from = strtotime ( '-1 days' , strtotime ( $date_from ) ) ;
                $date_antes_from = date ( 'Y-m-d' , $date_antes_from );

                $emisiones_antes = $db->table('Emisiones_CO2')->select(\DB::raw("DATE_FORMAT(time, '%H:%i') eje, SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones"))->where('date',$date_antes_from)->groupBy('time')->get();

                // $emisiones = \DB::select("SELECT DATE_FORMAT(time, '%H:%i') eje, SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones FROM ".$contador.".`emisiones_co2` WHERE date = '".$date_from."' GROUP BY time");
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

                $date_antes_from = strtotime ( '-7 days' , strtotime ( $date_from ) ) ;
                $date_antes_from = date ( 'Y-m-d' , $date_antes_from );

                $date_antes_to = strtotime ( '-7 days' , strtotime ( $date_to ) ) ;
                $date_antes_to = date ( 'Y-m-d' , $date_antes_to );

                $eje[0] = 'Lunes';
                $eje[1] = 'Martes';
                $eje[2] = 'Miércoles';
                $eje[3] = 'Jueves';
                $eje[4] = 'Viernes';
                $eje[5] = 'Sabado';
                $eje[6] = 'Domingo';
                $primer_dia_mes = 1;

                $emisiones = $db->table('Emisiones_CO2')->select(\DB::raw("(CASE DAYNAME(date) WHEN 'Monday' THEN 'Lunes' WHEN 'Tuesday' THEN 'Martes' WHEN 'Wednesday' THEN 'Miércoles' WHEN 'Thursday' THEN 'Jueves' WHEN 'Friday' THEN 'Viernes' WHEN 'Saturday' THEN 'Sabado' WHEN 'Sunday' THEN 'Domingo' END) eje, SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy('date')->orderBy('date','ASC')->get();

                $emisiones_antes = $db->table('Emisiones_CO2')->select(\DB::raw("(CASE DAYNAME(date) WHEN 'Monday' THEN 'Lunes' WHEN 'Tuesday' THEN 'Martes' WHEN 'Wednesday' THEN 'Miércoles' WHEN 'Thursday' THEN 'Jueves' WHEN 'Friday' THEN 'Viernes' WHEN 'Saturday' THEN 'Sabado' WHEN 'Sunday' THEN 'Domingo' END) eje, SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones"))->where('date','>=',$date_antes_from)->where('date','<=',$date_antes_to)->groupBy('date')->orderBy('date','ASC')->get();


                for ($t=0; $t < 7; $t++) {
                    $emisiones2[$t] = 0;
                    foreach($emisiones as $val)
                    {
                        if($val->eje == $eje[$t])
                        {
                            $emisiones2[$t] = $val->emisiones;
                            break;
                        }
                    }


                    if(date('d',strtotime($date_from))+$t > date('t',strtotime($date_from))){
                      $eje[$t] =$eje[$t]." (0".$primer_dia_mes.date('-m-Y',strtotime($date_to)).")";
                      $primer_dia_mes= $primer_dia_mes+1;
                    }else{
                      $eje[$t] =$eje[$t]." (".sprintf('%02d',date('d',strtotime($date_from))+$t).date('-m-Y',strtotime($date_from)).")";
                    }
                }

                $eje_antes[0] = 'Lunes';
                $eje_antes[1] = 'Martes';
                $eje_antes[2] = 'Miércoles';
                $eje_antes[3] = 'Jueves';
                $eje_antes[4] = 'Viernes';
                $eje_antes[5] = 'Sabado';
                $eje_antes[6] = 'Domingo';
                $primer_dia_mes = 1;

                for ($t=0; $t < 7; $t++) {
                    $emisiones2_antes[$t] = 0;
                    foreach($emisiones_antes as $val)
                    {
                        if($val->eje == $eje_antes[$t])
                        {
                            $emisiones2_antes[$t] = $val->emisiones;
                            break;
                        }
                    }
                    if(date('d',strtotime($date_antes_from))+$t > date('t',strtotime($date_antes_from))){
                      $eje_antes[$t] =$eje_antes[$t]." (0".$primer_dia_mes.date('-m-Y',strtotime($date_antes_to)).")";
                      $primer_dia_mes= $primer_dia_mes+1;
                    }else{
                      $eje_antes[$t] =$eje_antes[$t]." (".sprintf('%02d',date('d',strtotime($date_antes_from))+$t).date('-m-Y',strtotime($date_antes_from)).")";
                    }
                }




                // $emisiones = \DB::select("SELECT (CASE DAYNAME(date) WHEN 'Monday' THEN 'Lunes' WHEN 'Tuesday' THEN 'Martes' WHEN 'Wednesday' THEN 'Miércoles' WHEN 'Thursday' THEN 'Jueves' WHEN 'Friday' THEN 'Viernes' WHEN 'Saturday' THEN 'Sabado' WHEN 'Sunday' THEN 'Domingo' END) eje, SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones FROM ".$contador.".`emisiones_co2` WHERE date >= '".$date_from."' AND date <= '".$date_to."' GROUP BY date ORDER BY date ASC");
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

                $date_antes_from = strtotime ( '-7 days' , strtotime ( $date_from ) ) ;
                $date_antes_from = date ( 'Y-m-d' , $date_antes_from );

                $date_antes_to = strtotime ( '-7 days' , strtotime ( $date_to ) ) ;
                $date_antes_to = date ( 'Y-m-d' , $date_antes_to );

                $eje[0] = 'Lunes';
                $eje[1] = 'Martes';
                $eje[2] = 'Miércoles';
                $eje[3] = 'Jueves';
                $eje[4] = 'Viernes';
                $eje[5] = 'Sabado';
                $eje[6] = 'Domingo';
                $primer_dia_mes = 1;

                $emisiones = $db->table('Emisiones_CO2')->select(\DB::raw("(CASE DAYNAME(date) WHEN 'Monday' THEN 'Lunes' WHEN 'Tuesday' THEN 'Martes' WHEN 'Wednesday' THEN 'Miércoles' WHEN 'Thursday' THEN 'Jueves' WHEN 'Friday' THEN 'Viernes' WHEN 'Saturday' THEN 'Sabado' WHEN 'Sunday' THEN 'Domingo' END) eje, SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy('date')->orderBy('date','ASC')->get();

                $emisiones_antes = $db->table('Emisiones_CO2')->select(\DB::raw("(CASE DAYNAME(date) WHEN 'Monday' THEN 'Lunes' WHEN 'Tuesday' THEN 'Martes' WHEN 'Wednesday' THEN 'Miércoles' WHEN 'Thursday' THEN 'Jueves' WHEN 'Friday' THEN 'Viernes' WHEN 'Saturday' THEN 'Sabado' WHEN 'Sunday' THEN 'Domingo' END) eje, SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones"))->where('date','>=',$date_antes_from)->where('date','<=',$date_antes_to)->groupBy('date')->orderBy('date','ASC')->get();

                for ($t=0; $t < 7; $t++) {
                    $emisiones2[$t] = 0;
                    foreach($emisiones as $val)
                    {
                        if($val->eje == $eje[$t])
                        {
                            $emisiones2[$t] = $val->emisiones;
                            break;
                        }
                    }


                    if(date('d',strtotime($date_from))+$t > date('t',strtotime($date_from))){
                      $eje[$t] =$eje[$t]." (0".$primer_dia_mes.date('-m-Y',strtotime($date_to)).")";
                      $primer_dia_mes= $primer_dia_mes+1;
                    }else{
                      $eje[$t] =$eje[$t]." (".sprintf('%02d',date('d',strtotime($date_from))+$t).date('-m-Y',strtotime($date_from)).")";
                    }
                }

                $eje_antes[0] = 'Lunes';
                $eje_antes[1] = 'Martes';
                $eje_antes[2] = 'Miércoles';
                $eje_antes[3] = 'Jueves';
                $eje_antes[4] = 'Viernes';
                $eje_antes[5] = 'Sabado';
                $eje_antes[6] = 'Domingo';
                $primer_dia_mes = 1;

                for ($t=0; $t < 7; $t++) {
                    $emisiones2_antes[$t] = 0;
                    foreach($emisiones_antes as $val)
                    {
                        if($val->eje == $eje_antes[$t])
                        {
                            $emisiones2_antes[$t] = $val->emisiones;
                            break;
                        }
                    }
                    if(date('d',strtotime($date_antes_from))+$t > date('t',strtotime($date_antes_from))){
                      $eje_antes[$t] =$eje_antes[$t]." (0".$primer_dia_mes.date('-m-Y',strtotime($date_antes_to)).")";
                      $primer_dia_mes= $primer_dia_mes+1;
                    }else{
                      $eje_antes[$t] =$eje_antes[$t]." (".sprintf('%02d',date('d',strtotime($date_antes_from))+$t).date('-m-Y',strtotime($date_antes_from)).")";
                    }
                }



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

                $date_antes_from = strtotime ( '-1 month' , strtotime ( $date_from ) ) ;
                $date_antes_to  = date( 'Y-m-t', $date_antes_from);
                $date_antes_from = date ( 'Y-m-d' , $date_antes_from );

                $emisiones = $db->table('Emisiones_CO2')->select(\DB::raw("DAY(date) eje,SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy('date')->orderBy('date','ASC')->get();

                $emisiones_antes = $db->table('Emisiones_CO2')->select(\DB::raw("DAY(date) eje,SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones"))->where('date','>=',$date_antes_from)->where('date','<=',$date_antes_to)->groupBy('date')->orderBy('date','ASC')->get();

                for ($t=0; $t < date('t', strtotime($date_from)); $t++) {
                    $emisiones2[$t] = 0;
                        $eje[$t] = $t+1;
                    foreach($emisiones as $val)
                    {
                        if($val->eje == $eje[$t])
                        {
                            $emisiones2[$t] = $val->emisiones;
                            break;
                        }
                    }
                    if($t < 9){
                      $eje[$t] = "0".$eje[$t]."-".date('m-Y', strtotime($date_from));
                    }else{
                      $eje[$t] = $eje[$t]."-".date('m-Y', strtotime($date_from));
                    }

                }

                for ($t=0; $t < date('t', strtotime($date_antes_from)); $t++) {
                    $emisiones2_antes[$t] = 0;
                    $eje_antes[$t] = $t+1;
                    foreach($emisiones_antes as $val)
                    {
                        if($val->eje == $eje_antes[$t])
                        {
                            $emisiones2_antes[$t] = $val->emisiones;
                            break;
                        }

                    }
                    if($t < 9){
                      $eje_antes[$t] = "0".$eje_antes[$t]."-".date('m-Y', strtotime($date_antes_from));
                    }else{
                      $eje_antes[$t] = $eje_antes[$t]."-".date('m-Y', strtotime($date_antes_from));
                    }
                }

                // $emisiones = \DB::select("SELECT DAY(date) eje,SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones FROM ".$contador.".`emisiones_co2` WHERE date >= '".$date_from."' AND date <= '".$date_to."' GROUP BY date ORDER BY date ASC");
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

                $date_antes_from = strtotime ( '-1 month' , strtotime ( $date_from ) ) ;
                $date_antes_to  = date( 'Y-m-t', $date_antes_from);
                $date_antes_from = date ( 'Y-m-d' , $date_antes_from );


                $emisiones = $db->table('Emisiones_CO2')->select(\DB::raw("DAY(date) eje,SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy('date')->orderBy('date','ASC')->get();

                $emisiones_antes = $db->table('Emisiones_CO2')->select(\DB::raw("DAY(date) eje,SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones"))->where('date','>=',$date_antes_from)->where('date','<=',$date_antes_to)->groupBy('date')->orderBy('date','ASC')->get();

                for ($t=0; $t < date('t', strtotime($date_from)); $t++) {
                    $emisiones2[$t] = 0;
                        $eje[$t] = $t+1;
                    foreach($emisiones as $val)
                    {
                        if($val->eje == $eje[$t])
                        {
                            $emisiones2[$t] = $val->emisiones;
                            break;
                        }
                    }
                    if($t < 9){
                      $eje[$t] = "0".$eje[$t]."-".date('m-Y', strtotime($date_from));
                    }else{
                      $eje[$t] = $eje[$t]."-".date('m-Y', strtotime($date_from));
                    }

                }

                for ($t=0; $t < date('t', strtotime($date_antes_from)); $t++) {
                    $emisiones2_antes[$t] = 0;
                    $eje_antes[$t] = $t+1;
                    foreach($emisiones_antes as $val)
                    {
                        if($val->eje == $eje_antes[$t])
                        {
                            $emisiones2_antes[$t] = $val->emisiones;
                            break;
                        }

                    }
                    if($t < 9){
                      $eje_antes[$t] = "0".$eje_antes[$t]."-".date('m-Y', strtotime($date_antes_from));
                    }else{
                      $eje_antes[$t] = $eje_antes[$t]."-".date('m-Y', strtotime($date_antes_from));
                    }
                }

                break;

            case '7':
                $now = \Carbon\Carbon::now()->month;
                $dont = 0;
                if(isset(Session::get('_flash')['label_intervalo_navigation']) && Session::get('_flash')['label_intervalo_navigation'] == "Ultimo Trimestre")
                {
                    $now = \Carbon\Carbon::parse(Session::get('_flash')['date_from_personalice'])->month;
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
                        $eje[0] = 'Octubre('.\Carbon\Carbon::parse($date_from)->year.')';
                        $eje[1] = 'Noviembre('.\Carbon\Carbon::parse($date_from)->year.')';
                        $eje[2] = 'Diciembre('.\Carbon\Carbon::parse($date_from)->year.')';
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
                            $eje[0] = 'Enero('.\Carbon\Carbon::parse($date_from)->year.')';
                            $eje[1] = 'Febrero('.\Carbon\Carbon::parse($date_from)->year.')';
                            $eje[2] = 'Marzo('.\Carbon\Carbon::parse($date_from)->year.')';
                        }elseif($now == 7){
                            $eje[0] = 'Abril('.\Carbon\Carbon::parse($date_from)->year.')';
                            $eje[1] = 'Mayo('.\Carbon\Carbon::parse($date_from)->year.')';
                            $eje[2] = 'Junio('.\Carbon\Carbon::parse($date_from)->year.')';
                        }elseif($now == 10){
                            $eje[0] = 'Julio('.\Carbon\Carbon::parse($date_from)->year.')';
                            $eje[1] = 'Agosto('.\Carbon\Carbon::parse($date_from)->year.')';
                            $eje[2] = 'Septiembre('.\Carbon\Carbon::parse($date_from)->year.')';
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
                            $eje[0] = 'Enero('.\Carbon\Carbon::parse($date_from)->year.')';
                            $eje[1] = 'Febrero('.\Carbon\Carbon::parse($date_from)->year.')';
                            $eje[2] = 'Marzo('.\Carbon\Carbon::parse($date_from)->year.')';
                        }elseif($now == 8){
                            $eje[0] = 'Abril('.\Carbon\Carbon::parse($date_from)->year.')';
                            $eje[1] = 'Mayo('.\Carbon\Carbon::parse($date_from)->year.')';
                            $eje[2] = 'Junio('.\Carbon\Carbon::parse($date_from)->year.')';
                        }elseif($now == 11){
                            $eje[0] = 'Julio('.\Carbon\Carbon::parse($date_from)->year.')';
                            $eje[1] = 'Agosto('.\Carbon\Carbon::parse($date_from)->year.')';
                            $eje[2] = 'Septiembre('.\Carbon\Carbon::parse($date_from)->year.')';
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
                            $eje[1] = 'Febrero('.\Carbon\Carbon::parse($date_from)->year.')';
                            $eje[2] = 'Marzo('.\Carbon\Carbon::parse($date_from)->year.')';
                        }elseif($now == 9){
                            $eje[0] = 'Abril('.\Carbon\Carbon::parse($date_from)->year.')';
                            $eje[1] = 'Mayo('.\Carbon\Carbon::parse($date_from)->year.')';
                            $eje[2] = 'Junio('.\Carbon\Carbon::parse($date_from)->year.')';
                        }elseif($now == 12){
                            $eje[0] = 'Julio('.\Carbon\Carbon::parse($date_from)->year.')';
                            $eje[1] = 'Agosto('.\Carbon\Carbon::parse($date_from)->year.')';
                            $eje[2] = 'Septiembre('.\Carbon\Carbon::parse($date_from)->year.')';
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
                $label_intervalo = 'Ultimo Trimestre';

                $emisiones = $db->table('Emisiones_CO2')->select(\DB::raw("(CASE MONTH(date) WHEN '01' THEN CONCAT('Enero(',YEAR(date),')') WHEN '02' THEN CONCAT('Febrero(',YEAR(date),')') WHEN '03' THEN CONCAT('Marzo(',YEAR(date),')') WHEN '04' THEN CONCAT('Abril(',YEAR(date),')') WHEN '05' THEN CONCAT('Mayo(',YEAR(date),')') WHEN '06' THEN CONCAT('Junio(',YEAR(date),')') WHEN '07' THEN CONCAT('Julio(',YEAR(date),')') WHEN '08' THEN CONCAT('Agosto(',YEAR(date),')') WHEN '09' THEN CONCAT('Septiembre(',YEAR(date),')') WHEN '10' THEN CONCAT('Octubre(',YEAR(date),')') WHEN '11' THEN CONCAT('Noviembre(',YEAR(date),')') WHEN '12' THEN CONCAT('Diciembre(',YEAR(date),')') END) eje,SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy(\DB::raw('MONTH(date)'))->orderBy(\DB::raw('MONTH(date)'),'ASC')->get();

                $date_antes_from = strtotime ( '-3 month' , strtotime ( $date_from ) ) ;
                $date_antes_to = strtotime ( '-1 month' , strtotime ( $date_from ) ) ;
                $date_antes_to  = date( 'Y-m-t', $date_antes_to);
                $date_antes_from = date ( 'Y-m-d' , $date_antes_from );
                if($dont == 0){
                  $now = \Carbon\Carbon::now()->subMonths(3)->month;
                }



                if($now == 4 || $now == 5 || $now == 6){
                    $eje_antes[0] = 'Enero('.\Carbon\Carbon::parse($date_antes_from)->year.')';
                    $eje_antes[1] = 'Febrero('.\Carbon\Carbon::parse($date_antes_from)->year.')';
                    $eje_antes[2] = 'Marzo('.\Carbon\Carbon::parse($date_antes_from)->year.')';
                }elseif($now == 7 || $now == 8 || $now == 9 ){
                    $eje_antes[0] = 'Abril('.\Carbon\Carbon::parse($date_antes_from)->year.')';
                    $eje_antes[1] = 'Mayo('.\Carbon\Carbon::parse($date_antes_from)->year.')';
                    $eje_antes[2] = 'Junio('.\Carbon\Carbon::parse($date_antes_from)->year.')';
                }elseif($now == 10 || $now == 11 || $now == 12){
                    $eje_antes[0] = 'Julio('.\Carbon\Carbon::parse($date_antes_from)->year.')';
                    $eje_antes[1] = 'Agosto('.\Carbon\Carbon::parse($date_antes_from)->year.')';
                    $eje_antes[2] = 'Septiembre('.\Carbon\Carbon::parse($date_antes_from)->year.')';
                }elseif($now == 1 || $now == 2 || $now == 3){
                    $eje_antes[0] = 'Octubre('.\Carbon\Carbon::parse($date_antes_from)->year.')';
                    $eje_antes[1] = 'Noviembre('.\Carbon\Carbon::parse($date_antes_from)->year.')';
                    $eje_antes[2] = 'Diciembre('.\Carbon\Carbon::parse($date_antes_from)->year.')';
                }

                $emisiones_antes = $db->table('Emisiones_CO2')->select(\DB::raw("(CASE MONTH(date) WHEN '01' THEN CONCAT('Enero(',YEAR(date),')') WHEN '02' THEN CONCAT('Febrero(',YEAR(date),')') WHEN '03' THEN CONCAT('Marzo(',YEAR(date),')') WHEN '04' THEN CONCAT('Abril(',YEAR(date),')') WHEN '05' THEN CONCAT('Mayo(',YEAR(date),')') WHEN '06' THEN CONCAT('Junio(',YEAR(date),')') WHEN '07' THEN CONCAT('Julio(',YEAR(date),')') WHEN '08' THEN CONCAT('Agosto(',YEAR(date),')') WHEN '09' THEN CONCAT('Septiembre(',YEAR(date),')') WHEN '10' THEN CONCAT('Octubre(',YEAR(date),')') WHEN '11' THEN CONCAT('Noviembre(',YEAR(date),')') WHEN '12' THEN CONCAT('Diciembre(',YEAR(date),')') END) eje,SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones"))->where('date','>=',$date_antes_from)->where('date','<=',$date_antes_to)->groupBy(\DB::raw('MONTH(date)'))->orderBy(\DB::raw('MONTH(date)'),'ASC')->get();

                for ($t=0; $t < 3; $t++) {
                    $emisiones2[$t] = 0;
                    foreach($emisiones as $val)
                    {
                        if($val->eje == $eje[$t])
                        {
                            $emisiones2[$t] = $val->emisiones;
                            break;
                        }
                    }
                }

                for ($t=0; $t < 3; $t++) {
                    $emisiones2_antes[$t] = 0;
                    foreach($emisiones_antes as $val)
                    {
                        if($val->eje == $eje_antes[$t])
                        {
                            $emisiones2_antes[$t] = $val->emisiones;
                            break;
                        }
                    }
                }





                // $emisiones = \DB::select("SELECT (CASE MONTH(date) WHEN '01' THEN CONCAT('Enero(',YEAR(date),')') WHEN '02' THEN CONCAT('Febrero(',YEAR(date),')') WHEN '03' THEN CONCAT('Marzo(',YEAR(date),')') WHEN '04' THEN CONCAT('Abril(',YEAR(date),')') WHEN '05' THEN CONCAT('Mayo(',YEAR(date),')') WHEN '06' THEN CONCAT('Junio(',YEAR(date),')') WHEN '07' THEN CONCAT('Julio(',YEAR(date),')') WHEN '08' THEN CONCAT('Agosto(',YEAR(date),')') WHEN '09' THEN CONCAT('Septiembre(',YEAR(date),')') WHEN '10' THEN CONCAT('Octubre(',YEAR(date),')') WHEN '11' THEN CONCAT('Noviembre(',YEAR(date),')') WHEN '12' THEN CONCAT('Diciembre(',YEAR(date),')') END) eje,SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones FROM ".$contador.".`emisiones_co2` WHERE date >= '".$date_from."' AND date <= '".$date_to."' GROUP BY MONTH(date) ORDER BY MONTH(date) ASC");
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
                    // $date_from = \Carbon\Carbon::create(null, 1, 1, 0)->toDateString();
                    // $date_to = \Carbon\Carbon::now()->addMonths(3-$now)->endOfMonth()->toDateString();
                    if($dont == 0)
                    {
                        $date_from = \Carbon\Carbon::create(null, 1, 1, 0)->toDateString();
                        $date_to = \Carbon\Carbon::now()->addMonths(3-$now)->endOfMonth()->toDateString();
                    }
                    $eje[0] = 'Enero('.\Carbon\Carbon::parse($date_from)->year.')';
                    $eje[1] = 'Febrero('.\Carbon\Carbon::parse($date_from)->year.')';
                    $eje[2] = 'Marzo('.\Carbon\Carbon::parse($date_from)->year.')';
                }elseif($now == 4 || $now == 5 || $now == 6){
                    // $date_from = \Carbon\Carbon::create(null, 4, 1, 0)->toDateString();
                    // $date_to = \Carbon\Carbon::now()->addMonths(6-$now)->endOfMonth()->toDateString();
                    if($dont == 0)
                    {
                        $date_from = \Carbon\Carbon::create(null, 4, 1, 0)->toDateString();
                        $date_to = \Carbon\Carbon::now()->addMonths(6-$now)->endOfMonth()->toDateString();
                    }
                    $eje[0] = 'Abril('.\Carbon\Carbon::parse($date_from)->year.')';
                    $eje[1] = 'Mayo('.\Carbon\Carbon::parse($date_from)->year.')';
                    $eje[2] = 'Junio('.\Carbon\Carbon::parse($date_from)->year.')';
                }elseif($now == 7 || $now == 8 || $now == 9){
                    // $date_from = \Carbon\Carbon::create(null, 7, 1, 0)->toDateString();
                    // $date_to = \Carbon\Carbon::now()->addMonths(9-$now)->endOfMonth()->toDateString();
                    if($dont == 0)
                    {
                        $date_from = \Carbon\Carbon::create(null, 7, 1, 0)->toDateString();
                        $date_to = \Carbon\Carbon::now()->addMonths(9-$now)->endOfMonth()->toDateString();
                    }
                    $eje[0] = 'Julio('.\Carbon\Carbon::parse($date_from)->year.')';
                    $eje[1] = 'Agosto('.\Carbon\Carbon::parse($date_from)->year.')';
                    $eje[2] = 'Septiembre('.\Carbon\Carbon::parse($date_from)->year.')';
                }elseif($now == 10 || $now == 11 || $now == 12){
                    // $date_from = \Carbon\Carbon::create(null, 10, 1, 0)->toDateString();
                    // $date_to = \Carbon\Carbon::now()->addMonths(12-$now)->endOfMonth()->toDateString();
                    if($dont == 0)
                    {
                        $date_from = \Carbon\Carbon::create(null, 10, 1, 0)->toDateString();
                        $date_to = \Carbon\Carbon::now()->addMonths(12-$now)->endOfMonth()->toDateString();
                    }
                    $eje[0] = 'Octubre('.\Carbon\Carbon::parse($date_from)->year.')';
                    $eje[1] = 'Noviembre('.\Carbon\Carbon::parse($date_from)->year.')';
                    $eje[2] = 'Diciembre('.\Carbon\Carbon::parse($date_from)->year.')';
                }
                $label_intervalo = 'Trimestre Actual';

                $emisiones = $db->table('Emisiones_CO2')->select(\DB::raw("(CASE MONTH(date) WHEN '01' THEN CONCAT('Enero(',YEAR(date),')') WHEN '02' THEN CONCAT('Febrero(',YEAR(date),')') WHEN '03' THEN CONCAT('Marzo(',YEAR(date),')') WHEN '04' THEN CONCAT('Abril(',YEAR(date),')') WHEN '05' THEN CONCAT('Mayo(',YEAR(date),')') WHEN '06' THEN CONCAT('Junio(',YEAR(date),')') WHEN '07' THEN CONCAT('Julio(',YEAR(date),')') WHEN '08' THEN CONCAT('Agosto(',YEAR(date),')') WHEN '09' THEN CONCAT('Septiembre(',YEAR(date),')') WHEN '10' THEN CONCAT('Octubre(',YEAR(date),')') WHEN '11' THEN CONCAT('Noviembre(',YEAR(date),')') WHEN '12' THEN CONCAT('Diciembre(',YEAR(date),')') END) eje,SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy(\DB::raw('MONTH(date)'))->orderBy(\DB::raw('MONTH(date)'),'ASC')->get();

                $date_antes_from = strtotime ( '-3 month' , strtotime ( $date_from ) ) ;
                $date_antes_to = strtotime ( '-1 month' , strtotime ( $date_from ) ) ;
                $date_antes_to  = date( 'Y-m-t', $date_antes_to);
                $date_antes_from = date ( 'Y-m-d' , $date_antes_from );

                if($now == 4 || $now == 5 || $now == 6)
                {
                    $eje_antes[0] = 'Enero('.\Carbon\Carbon::parse($date_antes_from)->year.')';
                    $eje_antes[1] = 'Febrero('.\Carbon\Carbon::parse($date_antes_from)->year.')';
                    $eje_antes[2] = 'Marzo('.\Carbon\Carbon::parse($date_antes_from)->year.')';
                }elseif($now == 7 || $now == 8 || $now == 9 ){
                    $eje_antes[0] = 'Abril('.\Carbon\Carbon::parse($date_antes_from)->year.')';
                    $eje_antes[1] = 'Mayo('.\Carbon\Carbon::parse($date_antes_from)->year.')';
                    $eje_antes[2] = 'Junio('.\Carbon\Carbon::parse($date_antes_from)->year.')';
                }elseif($now == 10 || $now == 11 || $now == 12){
                    $eje_antes[0] = 'Julio('.\Carbon\Carbon::parse($date_antes_from)->year.')';
                    $eje_antes[1] = 'Agosto('.\Carbon\Carbon::parse($date_antes_from)->year.')';
                    $eje_antes[2] = 'Septiembre('.\Carbon\Carbon::parse($date_antes_from)->year.')';
                }elseif($now == 1 || $now == 2 || $now == 3){
                    $eje_antes[0] = 'Octubre('.\Carbon\Carbon::parse($date_antes_from)->year.')';
                    $eje_antes[1] = 'Noviembre('.\Carbon\Carbon::parse($date_antes_from)->year.')';
                    $eje_antes[2] = 'Diciembre('.\Carbon\Carbon::parse($date_antes_from)->year.')';
                }

                $emisiones_antes = $db->table('Emisiones_CO2')->select(\DB::raw("(CASE MONTH(date) WHEN '01' THEN CONCAT('Enero(',YEAR(date),')') WHEN '02' THEN CONCAT('Febrero(',YEAR(date),')') WHEN '03' THEN CONCAT('Marzo(',YEAR(date),')') WHEN '04' THEN CONCAT('Abril(',YEAR(date),')') WHEN '05' THEN CONCAT('Mayo(',YEAR(date),')') WHEN '06' THEN CONCAT('Junio(',YEAR(date),')') WHEN '07' THEN CONCAT('Julio(',YEAR(date),')') WHEN '08' THEN CONCAT('Agosto(',YEAR(date),')') WHEN '09' THEN CONCAT('Septiembre(',YEAR(date),')') WHEN '10' THEN CONCAT('Octubre(',YEAR(date),')') WHEN '11' THEN CONCAT('Noviembre(',YEAR(date),')') WHEN '12' THEN CONCAT('Diciembre(',YEAR(date),')') END) eje,SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones"))->where('date','>=',$date_antes_from)->where('date','<=',$date_antes_to)->groupBy(\DB::raw('MONTH(date)'))->orderBy(\DB::raw('MONTH(date)'),'ASC')->get();

                $band = 0;
                for ($t=0; $t < 3; $t++) {
                    $emisiones2[$t] = 0;
                    foreach($emisiones as $val)
                    {
                        if($val->eje == $eje[$t])
                        {
                            $emisiones2[$t] = $val->emisiones;
                            break;
                        }
                    }
                }

                for ($t=0; $t < 3; $t++) {
                    $emisiones2_antes[$t] = 0;
                    foreach($emisiones_antes as $val)
                    {
                        if($val->eje == $eje_antes[$t])
                        {
                            $emisiones2_antes[$t] = $val->emisiones;
                            break;
                        }
                    }
                }
                // dd($eje,$emisiones2);
                // $emisiones = \DB::select("SELECT (CASE MONTH(date) WHEN '01' THEN CONCAT('Enero(',YEAR(date),')') WHEN '02' THEN CONCAT('Febrero(',YEAR(date),')') WHEN '03' THEN CONCAT('Marzo(',YEAR(date),')') WHEN '04' THEN CONCAT('Abril(',YEAR(date),')') WHEN '05' THEN CONCAT('Mayo(',YEAR(date),')') WHEN '06' THEN CONCAT('Junio(',YEAR(date),')') WHEN '07' THEN CONCAT('Julio(',YEAR(date),')') WHEN '08' THEN CONCAT('Agosto(',YEAR(date),')') WHEN '09' THEN CONCAT('Septiembre(',YEAR(date),')') WHEN '10' THEN CONCAT('Octubre(',YEAR(date),')') WHEN '11' THEN CONCAT('Noviembre(',YEAR(date),')') WHEN '12' THEN CONCAT('Diciembre(',YEAR(date),')') END) eje,SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones FROM ".$contador.".`emisiones_co2` WHERE date >= '".$date_from."' AND date <= '".$date_to."' GROUP BY MONTH(date) ORDER BY MONTH(date) ASC");
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
                // $eje[0] = "Enero(".\Carbon\Carbon::now()->subYears(1)->year.")";
                // $eje[1] = "Febrero(".\Carbon\Carbon::now()->subYears(1)->year.")";
                // $eje[2] = "Marzo(".\Carbon\Carbon::now()->subYears(1)->year.")";
                // $eje[3] = "Abril(".\Carbon\Carbon::now()->subYears(1)->year.")";
                // $eje[4] = "Mayo(".\Carbon\Carbon::now()->subYears(1)->year.")";
                // $eje[5] = "Junio(".\Carbon\Carbon::now()->subYears(1)->year.")";
                // $eje[6] = "Julio(".\Carbon\Carbon::now()->subYears(1)->year.")";
                // $eje[7] = "Agosto(".\Carbon\Carbon::now()->subYears(1)->year.")";
                // $eje[8] = "Septiembre(".\Carbon\Carbon::now()->subYears(1)->year.")";
                // $eje[9] = "Octubre(".\Carbon\Carbon::now()->subYears(1)->year.")";
                // $eje[10] = "Noviembre(".\Carbon\Carbon::now()->subYears(1)->year.")";
                // $eje[11] = "Diciembre(".\Carbon\Carbon::now()->subYears(1)->year.")";
                if($dont == 0)
                {
                    $eje[0] = "Enero(".\Carbon\Carbon::now()->subYears(1)->year.")";
                    $eje[1] = "Febrero(".\Carbon\Carbon::now()->subYears(1)->year.")";
                    $eje[2] = "Marzo(".\Carbon\Carbon::now()->subYears(1)->year.")";
                    $eje[3] = "Abril(".\Carbon\Carbon::now()->subYears(1)->year.")";
                    $eje[4] = "Mayo(".\Carbon\Carbon::now()->subYears(1)->year.")";
                    $eje[5] = "Junio(".\Carbon\Carbon::now()->subYears(1)->year.")";
                    $eje[6] = "Julio(".\Carbon\Carbon::now()->subYears(1)->year.")";
                    $eje[7] = "Agosto(".\Carbon\Carbon::now()->subYears(1)->year.")";
                    $eje[8] = "Septiembre(".\Carbon\Carbon::now()->subYears(1)->year.")";
                    $eje[9] = "Octubre(".\Carbon\Carbon::now()->subYears(1)->year.")";
                    $eje[10] = "Noviembre(".\Carbon\Carbon::now()->subYears(1)->year.")";
                    $eje[11] = "Diciembre(".\Carbon\Carbon::now()->subYears(1)->year.")";
                }else{
                    $a_o = \Carbon\Carbon::parse($date_from)->year;
                    $eje[0] = "Enero(".$a_o.")";
                    $eje[1] = "Febrero(".$a_o.")";
                    $eje[2] = "Marzo(".$a_o.")";
                    $eje[3] = "Abril(".$a_o.")";
                    $eje[4] = "Mayo(".$a_o.")";
                    $eje[5] = "Junio(".$a_o.")";
                    $eje[6] = "Julio(".$a_o.")";
                    $eje[7] = "Agosto(".$a_o.")";
                    $eje[8] = "Septiembre(".$a_o.")";
                    $eje[9] = "Octubre(".$a_o.")";
                    $eje[10] = "Noviembre(".$a_o.")";
                    $eje[11] = "Diciembre(".$a_o.")";
                }

                $emisiones = $db->table('Emisiones_CO2')->select(\DB::raw("(CASE MONTH(date) WHEN '01' THEN CONCAT('Enero(',YEAR(date),')') WHEN '02' THEN CONCAT('Febrero(',YEAR(date),')') WHEN '03' THEN CONCAT('Marzo(',YEAR(date),')') WHEN '04' THEN CONCAT('Abril(',YEAR(date),')') WHEN '05' THEN CONCAT('Mayo(',YEAR(date),')') WHEN '06' THEN CONCAT('Junio(',YEAR(date),')') WHEN '07' THEN CONCAT('Julio(',YEAR(date),')') WHEN '08' THEN CONCAT('Agosto(',YEAR(date),')') WHEN '09' THEN CONCAT('Septiembre(',YEAR(date),')') WHEN '10' THEN CONCAT('Octubre(',YEAR(date),')') WHEN '11' THEN CONCAT('Noviembre(',YEAR(date),')') WHEN '12' THEN CONCAT('Diciembre(',YEAR(date),')') END) eje,SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy(\DB::raw('MONTH(date)'))->orderBy(\DB::raw('MONTH(date)'),'ASC')->get();

                $date_antes_from = strtotime ( '-1 year' , strtotime ( $date_from ) ) ;
                $date_antes_to = date ( 'Y-12-31' , $date_antes_from );
                $date_antes_from = date ( 'Y-01-01' , $date_antes_from );

                $a_o = \Carbon\Carbon::parse($date_antes_from)->year;
                $eje_antes[0] = "Enero(".$a_o.")";
                $eje_antes[1] = "Febrero(".$a_o.")";
                $eje_antes[2] = "Marzo(".$a_o.")";
                $eje_antes[3] = "Abril(".$a_o.")";
                $eje_antes[4] = "Mayo(".$a_o.")";
                $eje_antes[5] = "Junio(".$a_o.")";
                $eje_antes[6] = "Julio(".$a_o.")";
                $eje_antes[7] = "Agosto(".$a_o.")";
                $eje_antes[8] = "Septiembre(".$a_o.")";
                $eje_antes[9] = "Octubre(".$a_o.")";
                $eje_antes[10] = "Noviembre(".$a_o.")";
                $eje_antes[11] = "Diciembre(".$a_o.")";

                $emisiones_antes = $db->table('Emisiones_CO2')->select(\DB::raw("(CASE MONTH(date) WHEN '01' THEN CONCAT('Enero(',YEAR(date),')') WHEN '02' THEN CONCAT('Febrero(',YEAR(date),')') WHEN '03' THEN CONCAT('Marzo(',YEAR(date),')') WHEN '04' THEN CONCAT('Abril(',YEAR(date),')') WHEN '05' THEN CONCAT('Mayo(',YEAR(date),')') WHEN '06' THEN CONCAT('Junio(',YEAR(date),')') WHEN '07' THEN CONCAT('Julio(',YEAR(date),')') WHEN '08' THEN CONCAT('Agosto(',YEAR(date),')') WHEN '09' THEN CONCAT('Septiembre(',YEAR(date),')') WHEN '10' THEN CONCAT('Octubre(',YEAR(date),')') WHEN '11' THEN CONCAT('Noviembre(',YEAR(date),')') WHEN '12' THEN CONCAT('Diciembre(',YEAR(date),')') END) eje,SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones"))->where('date','>=',$date_antes_from)->where('date','<=',$date_antes_to)->groupBy(\DB::raw('MONTH(date)'))->orderBy(\DB::raw('MONTH(date)'),'ASC')->get();
                // for ($t=0; $t < 12; $t++) {
                //     if(isset($emisiones[$t]->emisiones) && $emisiones[$t]->eje == $eje[$t])
                //     {
                //         $emisiones2[$t] = $emisiones[$t]->emisiones;
                //     }else{
                //         $emisiones2[$t] = 0;
                //     }
                // }
                for ($t=0; $t < 12; $t++)
                {
                    $emisiones2[$t] = 0;
                    foreach ($emisiones as $val)
                    {
                        if($val->eje == $eje[$t])
                        {
                            $emisiones2[$t] = $val->emisiones;
                            break;
                        }
                    }
                }

                for ($t=0; $t < 12; $t++)
                {
                    $emisiones2_antes[$t] = 0;
                    foreach ($emisiones_antes as $val)
                    {
                        if($val->eje == $eje_antes[$t])
                        {
                            $emisiones2_antes[$t] = $val->emisiones;
                            break;
                        }
                    }
                }



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
                // $eje[0] = "Enero(".\Carbon\Carbon::now()->startOfYear()->year.")";
                // $eje[1] = "Febrero(".\Carbon\Carbon::now()->startOfYear()->year.")";
                // $eje[2] = "Marzo(".\Carbon\Carbon::now()->startOfYear()->year.")";
                // $eje[3] = "Abril(".\Carbon\Carbon::now()->startOfYear()->year.")";
                // $eje[4] = "Mayo(".\Carbon\Carbon::now()->startOfYear()->year.")";
                // $eje[5] = "Junio(".\Carbon\Carbon::now()->startOfYear()->year.")";
                // $eje[6] = "Julio(".\Carbon\Carbon::now()->startOfYear()->year.")";
                // $eje[7] = "Agosto(".\Carbon\Carbon::now()->startOfYear()->year.")";
                // $eje[8] = "Septiembre(".\Carbon\Carbon::now()->startOfYear()->year.")";
                // $eje[9] = "Octubre(".\Carbon\Carbon::now()->startOfYear()->year.")";
                // $eje[10] = "Noviembre(".\Carbon\Carbon::now()->startOfYear()->year.")";
                // $eje[11] = "Diciembre(".\Carbon\Carbon::now()->startOfYear()->year.")";
                if($dont == 0)
                {
                    $eje[0] = "Enero(".\Carbon\Carbon::now()->startOfYear()->year.")";
                    $eje[1] = "Febrero(".\Carbon\Carbon::now()->startOfYear()->year.")";
                    $eje[2] = "Marzo(".\Carbon\Carbon::now()->startOfYear()->year.")";
                    $eje[3] = "Abril(".\Carbon\Carbon::now()->startOfYear()->year.")";
                    $eje[4] = "Mayo(".\Carbon\Carbon::now()->startOfYear()->year.")";
                    $eje[5] = "Junio(".\Carbon\Carbon::now()->startOfYear()->year.")";
                    $eje[6] = "Julio(".\Carbon\Carbon::now()->startOfYear()->year.")";
                    $eje[7] = "Agosto(".\Carbon\Carbon::now()->startOfYear()->year.")";
                    $eje[8] = "Septiembre(".\Carbon\Carbon::now()->startOfYear()->year.")";
                    $eje[9] = "Octubre(".\Carbon\Carbon::now()->startOfYear()->year.")";
                    $eje[10] = "Noviembre(".\Carbon\Carbon::now()->startOfYear()->year.")";
                    $eje[11] = "Diciembre(".\Carbon\Carbon::now()->startOfYear()->year.")";
                }else{
                    $a_o = \Carbon\Carbon::parse($date_from)->year;
                    $eje[0] = "Enero(".$a_o.")";
                    $eje[1] = "Febrero(".$a_o.")";
                    $eje[2] = "Marzo(".$a_o.")";
                    $eje[3] = "Abril(".$a_o.")";
                    $eje[4] = "Mayo(".$a_o.")";
                    $eje[5] = "Junio(".$a_o.")";
                    $eje[6] = "Julio(".$a_o.")";
                    $eje[7] = "Agosto(".$a_o.")";
                    $eje[8] = "Septiembre(".$a_o.")";
                    $eje[9] = "Octubre(".$a_o.")";
                    $eje[10] = "Noviembre(".$a_o.")";
                    $eje[11] = "Diciembre(".$a_o.")";
                }

                $emisiones = $db->table('Emisiones_CO2')->select(\DB::raw("(CASE MONTH(date) WHEN '01' THEN CONCAT('Enero(',YEAR(date),')') WHEN '02' THEN CONCAT('Febrero(',YEAR(date),')') WHEN '03' THEN CONCAT('Marzo(',YEAR(date),')') WHEN '04' THEN CONCAT('Abril(',YEAR(date),')') WHEN '05' THEN CONCAT('Mayo(',YEAR(date),')') WHEN '06' THEN CONCAT('Junio(',YEAR(date),')') WHEN '07' THEN CONCAT('Julio(',YEAR(date),')') WHEN '08' THEN CONCAT('Agosto(',YEAR(date),')') WHEN '09' THEN CONCAT('Septiembre(',YEAR(date),')') WHEN '10' THEN CONCAT('Octubre(',YEAR(date),')') WHEN '11' THEN CONCAT('Noviembre(',YEAR(date),')') WHEN '12' THEN CONCAT('Diciembre(',YEAR(date),')') END) eje,SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy(\DB::raw('MONTH(date)'))->orderBy(\DB::raw('MONTH(date)'),'ASC')->get();

                $date_antes_from = strtotime ( '-1 year' , strtotime ( $date_from ) ) ;
                $date_antes_to = date ( 'Y-12-31' , $date_antes_from );
                $date_antes_from = date ( 'Y-01-01' , $date_antes_from );

                $a_o = \Carbon\Carbon::parse($date_antes_from)->year;
                $eje_antes[0] = "Enero(".$a_o.")";
                $eje_antes[1] = "Febrero(".$a_o.")";
                $eje_antes[2] = "Marzo(".$a_o.")";
                $eje_antes[3] = "Abril(".$a_o.")";
                $eje_antes[4] = "Mayo(".$a_o.")";
                $eje_antes[5] = "Junio(".$a_o.")";
                $eje_antes[6] = "Julio(".$a_o.")";
                $eje_antes[7] = "Agosto(".$a_o.")";
                $eje_antes[8] = "Septiembre(".$a_o.")";
                $eje_antes[9] = "Octubre(".$a_o.")";
                $eje_antes[10] = "Noviembre(".$a_o.")";
                $eje_antes[11] = "Diciembre(".$a_o.")";


                $emisiones_antes = $db->table('Emisiones_CO2')->select(\DB::raw("(CASE MONTH(date) WHEN '01' THEN CONCAT('Enero(',YEAR(date),')') WHEN '02' THEN CONCAT('Febrero(',YEAR(date),')') WHEN '03' THEN CONCAT('Marzo(',YEAR(date),')') WHEN '04' THEN CONCAT('Abril(',YEAR(date),')') WHEN '05' THEN CONCAT('Mayo(',YEAR(date),')') WHEN '06' THEN CONCAT('Junio(',YEAR(date),')') WHEN '07' THEN CONCAT('Julio(',YEAR(date),')') WHEN '08' THEN CONCAT('Agosto(',YEAR(date),')') WHEN '09' THEN CONCAT('Septiembre(',YEAR(date),')') WHEN '10' THEN CONCAT('Octubre(',YEAR(date),')') WHEN '11' THEN CONCAT('Noviembre(',YEAR(date),')') WHEN '12' THEN CONCAT('Diciembre(',YEAR(date),')') END) eje,SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones"))->where('date','>=',$date_antes_from)->where('date','<=',$date_antes_to)->groupBy(\DB::raw('MONTH(date)'))->orderBy(\DB::raw('MONTH(date)'),'ASC')->get();

                for ($t=0; $t < 12; $t++)
                {
                    $emisiones2[$t] = 0;
                    foreach ($emisiones as $val)
                    {
                        if($val->eje == $eje[$t])
                        {
                            $emisiones2[$t] = $val->emisiones;
                            break;
                        }
                    }
                }


                for ($t=0; $t < 12; $t++)
                {
                    $emisiones2_antes[$t] = 0;
                    foreach ($emisiones_antes as $val)
                    {
                        if($val->eje == $eje_antes[$t])
                        {
                            $emisiones2_antes[$t] = $val->emisiones;
                            break;
                        }
                    }
                }

                break;

            case '9':
                $date_from = Session::get('_flash')['date_from_personalice'];
                $date_to = Session::get('_flash')['date_to_personalice'];
                $date_from_Car = \Carbon\Carbon::parse($date_from);
                $date_to_Car = \Carbon\Carbon::parse($date_to);
                $label_intervalo = 'Personalizado';
                for($date = $date_from_Car; $date->lte($date_to_Car); $date->addDay())
                {
                    $dates[] = $date->format('Y-m-d');
                }
                $eje = $dates;

                if($date_from != $date_to)
                {
                    $emisiones = $db->table('Emisiones_CO2')->select(\DB::raw("date eje, SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy('date')->get();
                }else{
                    $emisiones = $db->table('Emisiones_CO2')->select(\DB::raw("DATE_FORMAT(time, '%H:%i') eje, SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones"))->where('date',$date_from)->groupBy('time')->get();
                }
                break;

            default:
                $date_from = \Carbon\Carbon::now()->toDateString();
                $date_to = $date_from;
                if(isset(Session::get('_flash')['label_intervalo_navigation']) && Session::get('_flash')['label_intervalo_navigation'] == "Hoy")
                {
                    $date_from = Session::get('_flash')['date_from_personalice'];
                    $date_to = Session::get('_flash')['date_to_personalice'];
                }
                $label_intervalo = 'Hoy';

                $emisiones = $db->table('Emisiones_CO2')->select(\DB::raw("DATE_FORMAT(time, '%H:%i') eje, SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones"))->where('date',$date_from)->groupBy('time')->get();

                $date_antes_from = strtotime ( '-1 days' , strtotime ( $date_from ) ) ;
                $date_antes_from = date ( 'Y-m-d' , $date_antes_from );



                $emisiones_antes = $db->table('Emisiones_CO2')->select(\DB::raw("DATE_FORMAT(time, '%H:%i') eje, SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones"))->where('date',$date_antes_from)->groupBy('time')->get();

                // $emisiones = \DB::select("SELECT DATE_FORMAT(time, '%H:%i') eje, SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones FROM ".$contador.".`emisiones_co2` WHERE date = '".$date_from."' GROUP BY time");
                break;
        }

        $user = Auth::user();//usuario logeado
        $titulo = 'Emisiones CO2';//Título del content
        $contador_label = $contador2->count_label;
        if(!is_null($db->table('Area_Cliente')->select(\DB::raw('`LOGOTIPO` logo'))->first()))
        {
            $dir_image_count = $db->table('Area_Cliente')->select(\DB::raw('`LOGOTIPO` logo'))->first()->logo;
        }
        else
        {
                $dir_image_count = $db->table('Area_Cliente')->select(\DB::raw('`LOGOTIPO` logo'))->first();
        }
        \DB::disconnect('mysql2');

        $aux_current_count = \DB::select("SELECT label_current_count FROM current_count WHERE user_id = ".$id);

        if(is_null($aux_current_count) || empty($aux_current_count))
        {
            \DB::insert("INSERT INTO current_count (label_current_count,user_id) VALUES ('".$current_count."',".$id.")");
        }
        else
        {
            \DB::update("UPDATE current_count SET label_current_count = '".$current_count."' WHERE user_id = ".$id);
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

            if(is_null($user->_perfil))
            {
                $direccion = 'sin ubicación';
            }
            else
            {
                $direccion = $user->_perfil->direccion;
            }
            return view('emisiones_co2.emisiones_co2',compact('user','titulo','cliente','id','ctrl','emisiones','label_intervalo','date_from','date_to','direccion','tipo_count','contador_label','domicilio','dir_image_count','eje','emisiones2','dates','tipo_tarifa','date_antes_from','date_antes_to','emisiones_antes','emisiones2_antes','eje_antes'));
        }
        return \Redirect::to('https://submeter.es/');
                        // return view('emisiones_co2.emisiones_co2',compact('user','titulo','cliente','id','ctrl','emisiones','label_intervalo','date_from','date_to','direccion','tipo_count','contador_label','domicilio','dir_image_count','tipo_tarifa'));
    }

    function exportCSVCo2(Request $request){


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

      $date_from = $request->date_from;
      $date_to = $request->date_to;


      $filename = "Datos_Emisiones_Co2_".$contador_label."_".$request->date_from."_".$request->date_to.".csv";
      $handle = fopen($filename, 'w+');

      $total = 0;
      $total_antes = 0;


      if($interval == 1 || $interval == 2){
        //hoy y ayer

        $emisiones = $db->table('Emisiones_CO2')->select(\DB::raw("DATE_FORMAT(time, '%H:%i') eje, `Emisiones CO2 (kg CO2 eq)` emisiones"))->where('date',$date_from)->orderBy('time')->get();

        $date_antes_from = strtotime ( '-1 days' , strtotime ( $date_from ) ) ;
        $date_antes_from = date ( 'Y-m-d' , $date_antes_from );

        $emisiones_antes = $db->table('Emisiones_CO2')->select(\DB::raw("DATE_FORMAT(time, '%H:%i') eje, `Emisiones CO2 (kg CO2 eq)` emisiones"))->where('date',$date_antes_from)->orderBy('time')->get();


        foreach($emisiones as $data) {
          $total = $data->emisiones+$total;
        }

        foreach($emisiones_antes as $data) {
          $total_antes = $data->emisiones+$total_antes;
        }

        if($total_antes==0){
          $division = 0;
        }else{
          $division = $total/$total_antes;
        }

          fputcsv($handle, array('Intervalo', ''.$date_from.'', ''.$date_antes_from.'', 'Variacion', 'Var (%)'),';');

          fputcsv($handle, array('Total' ,
          number_format($total, 2, ',', '.').' kg CO2 eq.',
          number_format($total_antes, 2, ',', '.').' kg CO2 eq.',
          number_format($total-$total_antes, 2, ',', '.').' kg CO2 eq.',
          ''.number_format((($division)-1)*100, 2, ',', '.').' %'
          ),';');



      }elseif($interval == 3 || $interval == 4){
        //semana y semana anterior

        $date_antes_from = strtotime ( '-7 days' , strtotime ( $date_from ) ) ;
        $date_antes_from = date ( 'Y-m-d' , $date_antes_from );

        $date_antes_to = strtotime ( '-7 days' , strtotime ( $date_to ) ) ;
        $date_antes_to = date ( 'Y-m-d' , $date_antes_to );

        $eje[0] = 'Lunes';
        $eje[1] = 'Martes';
        $eje[2] = 'Miércoles';
        $eje[3] = 'Jueves';
        $eje[4] = 'Viernes';
        $eje[5] = 'Sabado';
        $eje[6] = 'Domingo';
        $primer_dia_mes = 1;

        $emisiones = $db->table('Emisiones_CO2')->select(\DB::raw("(CASE DAYNAME(date) WHEN 'Monday' THEN 'Lunes' WHEN 'Tuesday' THEN 'Martes' WHEN 'Wednesday' THEN 'Miércoles' WHEN 'Thursday' THEN 'Jueves' WHEN 'Friday' THEN 'Viernes' WHEN 'Saturday' THEN 'Sabado' WHEN 'Sunday' THEN 'Domingo' END) eje, SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy('date')->orderBy('date','ASC')->get();

        $emisiones_antes = $db->table('Emisiones_CO2')->select(\DB::raw("(CASE DAYNAME(date) WHEN 'Monday' THEN 'Lunes' WHEN 'Tuesday' THEN 'Martes' WHEN 'Wednesday' THEN 'Miércoles' WHEN 'Thursday' THEN 'Jueves' WHEN 'Friday' THEN 'Viernes' WHEN 'Saturday' THEN 'Sabado' WHEN 'Sunday' THEN 'Domingo' END) eje, SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones"))->where('date','>=',$date_antes_from)->where('date','<=',$date_antes_to)->groupBy('date')->orderBy('date','ASC')->get();

        for ($t=0; $t < 7; $t++) {
            $emisiones2[$t] = 0;
            foreach($emisiones as $val)
            {
                if($val->eje == $eje[$t])
                {
                    $emisiones2[$t] = $val->emisiones;
                    break;
                }
            }

            if($eje[$t] == 'Miércoles'){
              $eje[$t] = 'Miercoles';
            }

            if(date('d',strtotime($date_from))+$t > date('t',strtotime($date_from))){
              $eje[$t] =$eje[$t]." (0".$primer_dia_mes.date('-m-Y',strtotime($date_to)).")";
              $primer_dia_mes= $primer_dia_mes+1;
            }else{
              $eje[$t] =$eje[$t]." (".sprintf('%02d',date('d',strtotime($date_from))+$t).date('-m-Y',strtotime($date_from)).")";
            }
        }

        $eje_antes[0] = 'Lunes';
        $eje_antes[1] = 'Martes';
        $eje_antes[2] = 'Miércoles';
        $eje_antes[3] = 'Jueves';
        $eje_antes[4] = 'Viernes';
        $eje_antes[5] = 'Sabado';
        $eje_antes[6] = 'Domingo';
        $primer_dia_mes = 1;

        for ($t=0; $t < 7; $t++) {
            $emisiones2_antes[$t] = 0;
            foreach($emisiones_antes as $val)
            {
                if($val->eje == $eje_antes[$t])
                {
                    $emisiones2_antes[$t] = $val->emisiones;
                    break;
                }
            }
            if(date('d',strtotime($date_antes_from))+$t > date('t',strtotime($date_antes_from))){
              $eje_antes[$t] =$eje_antes[$t]." (0".$primer_dia_mes.date('-m-Y',strtotime($date_antes_to)).")";
              $primer_dia_mes= $primer_dia_mes+1;
            }else{
              $eje_antes[$t] =$eje_antes[$t]." (".sprintf('%02d',date('d',strtotime($date_antes_from))+$t).date('-m-Y',strtotime($date_antes_from)).")";
            }
        }

        fputcsv($handle, array('Intervalo', 'Semana: '.date("W", strtotime($date_from)).'', 'Semana: '.date("W", strtotime($date_antes_from)).'', 'Variacion', 'Var (%)'),';');



        for ($t=0; $t < 7; $t++) {
        if($emisiones2_antes[$t]==0){
          $division[$t] = 0;
        }else{
          $division[$t] = $emisiones2[$t]/$emisiones2_antes[$t];
        }

        fputcsv($handle, array($eje[$t] ,
        number_format($emisiones2[$t], 2, ',', '.').' kg CO2 eq.',
        number_format($emisiones2_antes[$t], 2, ',', '.').' kg CO2 eq.',
        number_format($emisiones2[$t]-$emisiones2_antes[$t], 2, ',', '.').' kg CO2 eq.',
        ''.number_format((($division[$t])-1)*100, 2, ',', '.').' %'
        ),';');

        $total = $emisiones2[$t]+$total;
        $total_antes = $emisiones2_antes[$t]+$total_antes;
      }

      if($total_antes==0){
        $division_total = 0;
      }else{
        $division_total = $total/$total_antes;
      }

      fputcsv($handle, array('Total' ,
      number_format($total, 2, ',', '.').' kg CO2 eq.',
      number_format($total_antes, 2, ',', '.').' kg CO2 eq.',
      number_format($total-$total_antes, 2, ',', '.').' kg CO2 eq.',
      ''.number_format((($division_total)-1)*100, 2, ',', '.').' %'
      ),';');

      }elseif($interval == 5 || $interval == 6){
        //mes y mes anterior

        $date_antes_from = strtotime ( '-1 month' , strtotime ( $date_from ) ) ;
        $date_antes_to  = date( 'Y-m-t', $date_antes_from);
        $date_antes_from = date ( 'Y-m-d' , $date_antes_from );

        $emisiones = $db->table('Emisiones_CO2')->select(\DB::raw("DAY(date) eje,SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy('date')->orderBy('date','ASC')->get();

        $emisiones_antes = $db->table('Emisiones_CO2')->select(\DB::raw("DAY(date) eje,SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones"))->where('date','>=',$date_antes_from)->where('date','<=',$date_antes_to)->groupBy('date')->orderBy('date','ASC')->get();

        $months_T = array (1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre');

        $date_from_nombre = date("m", strtotime($date_from));
        $date_from_nombre = $months_T[(int)$date_from_nombre];
        $date_antes_from_nombre = date("m", strtotime($date_antes_from));
        $date_antes_from_nombre = $months_T[(int)$date_antes_from_nombre];

        foreach($emisiones as $data) {
          $total = $data->emisiones+$total;
        }

        foreach($emisiones_antes as $data) {
          $total_antes = $data->emisiones+$total_antes;
        }

        if($total_antes==0){
          $division = 0;
        }else{
          $division = $total/$total_antes;
        }

        $date_from_nombre = $date_from_nombre." ( ".date("Y", strtotime($date_from))." )";
        $date_antes_from_nombre = $date_antes_from_nombre." ( ".date("Y", strtotime($date_antes_from)). " )";


        fputcsv($handle, array('Intervalo', $date_from_nombre, $date_antes_from_nombre, 'Variacion', 'Var (%)'),';');

        fputcsv($handle, array('Total' ,
        number_format($total, 2, ',', '.').' kg CO2 eq.',
        number_format($total_antes, 2, ',', '.').' kg CO2 eq.',
        number_format($total-$total_antes, 2, ',', '.').' kg CO2 eq.',
        ''.number_format((($division)-1)*100, 2, ',', '.').' %'
        ),';');

      }elseif($interval == 7 || $interval == 10){
        //trimestre y ultimo trimestre

        $emisiones = $db->table('Emisiones_CO2')->select(\DB::raw("(CASE MONTH(date) WHEN '01' THEN CONCAT('Enero(',YEAR(date),')') WHEN '02' THEN CONCAT('Febrero(',YEAR(date),')') WHEN '03' THEN CONCAT('Marzo(',YEAR(date),')') WHEN '04' THEN CONCAT('Abril(',YEAR(date),')') WHEN '05' THEN CONCAT('Mayo(',YEAR(date),')') WHEN '06' THEN CONCAT('Junio(',YEAR(date),')') WHEN '07' THEN CONCAT('Julio(',YEAR(date),')') WHEN '08' THEN CONCAT('Agosto(',YEAR(date),')') WHEN '09' THEN CONCAT('Septiembre(',YEAR(date),')') WHEN '10' THEN CONCAT('Octubre(',YEAR(date),')') WHEN '11' THEN CONCAT('Noviembre(',YEAR(date),')') WHEN '12' THEN CONCAT('Diciembre(',YEAR(date),')') END) eje,SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy(\DB::raw('MONTH(date)'))->orderBy(\DB::raw('MONTH(date)'),'ASC')->get();

        $date_antes_from = strtotime ( '-3 month' , strtotime ( $date_from ) ) ;
        $date_antes_to = strtotime ( '-1 month' , strtotime ( $date_from ) ) ;
        $date_antes_to  = date( 'Y-m-t', $date_antes_to);
        $date_antes_from = date ( 'Y-m-d' , $date_antes_from );

        $emisiones_antes = $db->table('Emisiones_CO2')->select(\DB::raw("(CASE MONTH(date) WHEN '01' THEN CONCAT('Enero(',YEAR(date),')') WHEN '02' THEN CONCAT('Febrero(',YEAR(date),')') WHEN '03' THEN CONCAT('Marzo(',YEAR(date),')') WHEN '04' THEN CONCAT('Abril(',YEAR(date),')') WHEN '05' THEN CONCAT('Mayo(',YEAR(date),')') WHEN '06' THEN CONCAT('Junio(',YEAR(date),')') WHEN '07' THEN CONCAT('Julio(',YEAR(date),')') WHEN '08' THEN CONCAT('Agosto(',YEAR(date),')') WHEN '09' THEN CONCAT('Septiembre(',YEAR(date),')') WHEN '10' THEN CONCAT('Octubre(',YEAR(date),')') WHEN '11' THEN CONCAT('Noviembre(',YEAR(date),')') WHEN '12' THEN CONCAT('Diciembre(',YEAR(date),')') END) eje,SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones"))->where('date','>=',$date_antes_from)->where('date','<=',$date_antes_to)->groupBy(\DB::raw('MONTH(date)'))->orderBy(\DB::raw('MONTH(date)'),'ASC')->get();

        foreach($emisiones as $data) {
          $total = $data->emisiones+$total;
        }

        foreach($emisiones_antes as $data) {
          $total_antes = $data->emisiones+$total_antes;
        }

        if($total_antes==0){
          $division = 0;
        }else{
          $division = $total/$total_antes;
        }

        $date_from_nombre = ceil(date("m", strtotime($date_from))/3)."T ( ". date("Y", strtotime($date_from))." )";
        $date_antes_from_nombre = ceil(date("m", strtotime($date_antes_from))/3)."T ( ". date("Y", strtotime($date_antes_from))." )";

        fputcsv($handle, array('Intervalo', $date_from_nombre, $date_antes_from_nombre, 'Variacion', 'Var (%)'),';');

        fputcsv($handle, array('Total' ,
        number_format($total, 2, ',', '.').' kg CO2 eq.',
        number_format($total_antes, 2, ',', '.').' kg CO2 eq.',
        number_format($total-$total_antes, 2, ',', '.').' kg CO2 eq.',
        ''.number_format((($division)-1)*100, 2, ',', '.').' %'
        ),';');


      }elseif($interval == 8 || $interval == 11){
        //año actual y ultimo año

        $a_o = \Carbon\Carbon::parse($date_from)->year;
        $eje[0] = "Enero(".$a_o.")";
        $eje[1] = "Febrero(".$a_o.")";
        $eje[2] = "Marzo(".$a_o.")";
        $eje[3] = "Abril(".$a_o.")";
        $eje[4] = "Mayo(".$a_o.")";
        $eje[5] = "Junio(".$a_o.")";
        $eje[6] = "Julio(".$a_o.")";
        $eje[7] = "Agosto(".$a_o.")";
        $eje[8] = "Septiembre(".$a_o.")";
        $eje[9] = "Octubre(".$a_o.")";
        $eje[10] = "Noviembre(".$a_o.")";
        $eje[11] = "Diciembre(".$a_o.")";


        $emisiones = $db->table('Emisiones_CO2')->select(\DB::raw("(CASE MONTH(date) WHEN '01' THEN CONCAT('Enero(',YEAR(date),')') WHEN '02' THEN CONCAT('Febrero(',YEAR(date),')') WHEN '03' THEN CONCAT('Marzo(',YEAR(date),')') WHEN '04' THEN CONCAT('Abril(',YEAR(date),')') WHEN '05' THEN CONCAT('Mayo(',YEAR(date),')') WHEN '06' THEN CONCAT('Junio(',YEAR(date),')') WHEN '07' THEN CONCAT('Julio(',YEAR(date),')') WHEN '08' THEN CONCAT('Agosto(',YEAR(date),')') WHEN '09' THEN CONCAT('Septiembre(',YEAR(date),')') WHEN '10' THEN CONCAT('Octubre(',YEAR(date),')') WHEN '11' THEN CONCAT('Noviembre(',YEAR(date),')') WHEN '12' THEN CONCAT('Diciembre(',YEAR(date),')') END) eje,SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy(\DB::raw('MONTH(date)'))->orderBy(\DB::raw('MONTH(date)'),'ASC')->get();

        $date_antes_from = strtotime ( '-1 year' , strtotime ( $date_from ) ) ;
        $date_antes_to = date ( 'Y-12-31' , $date_antes_from );
        $date_antes_from = date ( 'Y-01-01' , $date_antes_from );

        $a_o = \Carbon\Carbon::parse($date_antes_from)->year;
        $eje_antes[0] = "Enero(".$a_o.")";
        $eje_antes[1] = "Febrero(".$a_o.")";
        $eje_antes[2] = "Marzo(".$a_o.")";
        $eje_antes[3] = "Abril(".$a_o.")";
        $eje_antes[4] = "Mayo(".$a_o.")";
        $eje_antes[5] = "Junio(".$a_o.")";
        $eje_antes[6] = "Julio(".$a_o.")";
        $eje_antes[7] = "Agosto(".$a_o.")";
        $eje_antes[8] = "Septiembre(".$a_o.")";
        $eje_antes[9] = "Octubre(".$a_o.")";
        $eje_antes[10] = "Noviembre(".$a_o.")";
        $eje_antes[11] = "Diciembre(".$a_o.")";


        $emisiones_antes = $db->table('Emisiones_CO2')->select(\DB::raw("(CASE MONTH(date) WHEN '01' THEN CONCAT('Enero(',YEAR(date),')') WHEN '02' THEN CONCAT('Febrero(',YEAR(date),')') WHEN '03' THEN CONCAT('Marzo(',YEAR(date),')') WHEN '04' THEN CONCAT('Abril(',YEAR(date),')') WHEN '05' THEN CONCAT('Mayo(',YEAR(date),')') WHEN '06' THEN CONCAT('Junio(',YEAR(date),')') WHEN '07' THEN CONCAT('Julio(',YEAR(date),')') WHEN '08' THEN CONCAT('Agosto(',YEAR(date),')') WHEN '09' THEN CONCAT('Septiembre(',YEAR(date),')') WHEN '10' THEN CONCAT('Octubre(',YEAR(date),')') WHEN '11' THEN CONCAT('Noviembre(',YEAR(date),')') WHEN '12' THEN CONCAT('Diciembre(',YEAR(date),')') END) eje,SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones"))->where('date','>=',$date_antes_from)->where('date','<=',$date_antes_to)->groupBy(\DB::raw('MONTH(date)'))->orderBy(\DB::raw('MONTH(date)'),'ASC')->get();

        for ($t=0; $t < 12; $t++)
        {
            $emisiones2[$t] = 0;
            foreach ($emisiones as $val)
            {
                if($val->eje == $eje[$t])
                {
                    $emisiones2[$t] = $val->emisiones;
                    break;
                }
            }
        }


        for ($t=0; $t < 12; $t++)
        {
            $emisiones2_antes[$t] = 0;
            foreach ($emisiones_antes as $val)
            {
                if($val->eje == $eje_antes[$t])
                {
                    $emisiones2_antes[$t] = $val->emisiones;
                    break;
                }
            }
        }

        fputcsv($handle, array('Intervalo', date("Y", strtotime($date_from)), date("Y", strtotime($date_antes_from)), 'Variacion', 'Var (%)'),';');

        for ($t=0; $t < 12; $t++) {
        if($emisiones2_antes[$t]==0){
          $division[$t] = 0;
        }else{
          $division[$t] = $emisiones2[$t]/$emisiones2_antes[$t];
        }

        $meses[0] = "Enero";
        $meses[1] = "Febrero";
        $meses[2] = "Marzo";
        $meses[3] = "Abril";
        $meses[4] = "Mayo";
        $meses[5] = "Junio";
        $meses[6] = "Julio";
        $meses[7] = "Agosto";
        $meses[8] = "Septiembre";
        $meses[9] = "Octubre";
        $meses[10] = "Noviembre";
        $meses[11] = "Diciembre";

        fputcsv($handle, array($meses[$t] ,
        number_format($emisiones2[$t], 2, ',', '.').' kg CO2 eq.',
        number_format($emisiones2_antes[$t], 2, ',', '.').' kg CO2 eq.',
        number_format($emisiones2[$t]-$emisiones2_antes[$t], 2, ',', '.').' kg CO2 eq.',
        ''.number_format((($division[$t])-1)*100, 2, ',', '.').' %'
        ),';');

        $total = $emisiones2[$t]+$total;
        $total_antes = $emisiones2_antes[$t]+$total_antes;
      }

      if($total_antes==0){
        $division_total = 0;
      }else{
        $division_total = $total/$total_antes;
      }

      fputcsv($handle, array('Total' ,
      number_format($total, 2, ',', '.').' kg CO2 eq.',
      number_format($total_antes, 2, ',', '.').' kg CO2 eq.',
      number_format($total-$total_antes, 2, ',', '.').' kg CO2 eq.',
      ''.number_format((($division_total)-1)*100, 2, ',', '.').' %'
      ),';');

      }elseif($interval == 9){
        //personalizado

        $emisiones = $db->table('Emisiones_CO2')->select(\DB::raw("`date` date, DATE_FORMAT(time, '%H:%i') time, `Emisiones CO2 (kg CO2 eq)` emisiones"))->where('date','>=',$date_from)->where('date','<=',$date_to)->orderBy('date')->orderBy('time')->get();


        fputcsv($handle, array('Fecha', 'Tiempo', 'Emisiones Co2(kg CO2 eq.)'),';');

        $i = 0;
        foreach($emisiones as $data) {

                $P1 = number_format($data->emisiones,3,',','.');

                fputcsv($handle, array(
                  $data->date, $data->time, $P1
                  ),';');

                  $i++;
              }

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
