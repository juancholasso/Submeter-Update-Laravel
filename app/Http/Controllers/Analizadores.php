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



class Analizadores extends Controller
{
  function AnalizadoresGraficas($user_id, $id, Request $request)
  {
      $analizador = Analizador::where('id',$id)->first();
      $color_etiqueta = $analizador->color_etiqueta;
      $count_id = $analizador->count_id;
      $user = User::where('id',$user_id)->first();

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
      $tipo_tarifa = $contador2->tarifa;

      $aux_contador = $contador2;
      $eje = array();
      $values_graficas_ = array();
      $values_corriente_ = array();
      $td_Frecuencia=array();
      $td_COSPHI=array();
      $td_FDP=array();
      $td_Intensidad=array();
      $td_PotReac=array();
      $td_PotAct_avg=array();
      $td_PotAct_max=array();
      $td_aux=array();


      config(['database.connections.mysql2.host' => $analizador->host]);
      config(['database.connections.mysql2.port' => $analizador->port]);
      config(['database.connections.mysql2.database' => $analizador->database]);
      config(['database.connections.mysql2.username' => $analizador->username]);
      config(['database.connections.mysql2.password' => $analizador->password]);
      env('MYSQL2_HOST',$analizador->host);
      env('MYSQL2_DATABASE',$analizador->database);
      env('MYSQL2_USERNAME', $analizador->username);
      env('MYSQL2_PASSWORD',$analizador->password);
      try {
          \DB::connection('mysql2')->getPdo();
      } catch (\Exception $e) {
          Session::flash('message-error', "La base de datos a la que se desea conectar está mal configurada. Por favor, edite los parámetros de configuración de conexión.");
          return \Redirect::back();
      }
      $db = \DB::connection('mysql2');

      $domicilio = $db->table('Area_Cliente')->select(\DB::raw('`DENOMINACIÓN SOCIAL` denominacion_social, `SOCIAL DOMICILIO` social_domicilio, `SUMINISTRO DEL  DOMICILIO` suministro_del_domicilio, CUPS, CIF, `DISTRIBUIDORA EMPRESA` distribuidora_empresa, `COMERCIALIZADORA EMPRESA` comercializadora_empresa, `CONTACTO DE  PERSONA` contacto_persona, `TELÉFONO`, `CONTACTO O  AYUDA` contacto_ayuda, `TARIFA`'))->first();


  \DB::disconnect('mysql2');

      $interval = Session::get('_flash')['intervalos'];
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

              $values_graficas = $db->table('Potencia_Activa_Por_Fase')->select(\DB::raw("DATE_FORMAT(time, '%H:%i') eje, SUM(`POWact1 (kW)`) potencia_activa_L1, SUM(`POWact2 (kW)`) potencia_activa_L2, SUM(`POWact3 (kW)`) potencia_activa_L3"))->where('date',$date_from)->groupBy('time')->get();

              $values_corriente = $db->table('Corrientes_Por_Fase')->select(\DB::raw("DATE_FORMAT(time, '%H:%i') eje, SUM(`IAC1 (A)`) corriente_L1, SUM(`IAC2 (A)`) corriente_L2, SUM(`IAC3 (A)`) corriente_L3"))->where('date',$date_from)->groupBy('time')->get();
              $values_graficas_ = $values_graficas;
              $values_corriente_ = $values_corriente;

              $table_data = $db->table('Analizadores_Tipo')->select(\DB::raw("DATE_FORMAT(time, '%H:%i') time, `FRE (Hz)` FRE, `COSPHI` COSPHI, `PF1` PF1, `PF2` PF2, `PF3` PF3, `IAC1 (A)` IAC1, `IAC2 (A)` IAC2, `IAC3 (A)` IAC3, `POWrea1 (kVAr)` POWrea1, `POWrea2 (kVAr)` POWrea2, `POWrea3 (kVAr)` POWrea3, `POWact1 (kW)` POWact1_avg, `POWact2 (kW)` POWact2_avg, `POWact3 (kW)` POWact3_avg, `POWact1 (kW)` POWact1_max, `POWact2 (kW)` POWact2_max, `POWact3 (kW)` POWact3_max, `POWact_Total (kW)` POWact_total"))->where('date','>=',$date_from)->where('date','<=',$date_to)->get();

            foreach ( $table_data as $i=>$dat ) {
                  $td_aux[$i]['time'] = "$dat->time";
                  $td_PotAct_total[$i]['POWact_total'] = number_format($dat->POWact_total, 2, '.', '');
                  $td_Frecuencia[$i]['FRE'] = number_format($dat->FRE, 2, '.', '');
                  $td_PotAct_avg[$i]['POWact1_avg'] = number_format($dat->POWact1_avg, 2, '.', '');
                  $td_PotAct_avg[$i]['POWact2_avg'] = number_format($dat->POWact2_avg, 2, '.', '');
                  $td_PotAct_avg[$i]['POWact3_avg'] = number_format($dat->POWact3_avg, 2, '.', '');
                  $td_PotAct_max[$i]['POWact1_max'] = number_format($dat->POWact1_max, 2, '.', '');
                  $td_PotAct_max[$i]['POWact2_max'] = number_format($dat->POWact2_max, 2, '.', '');
                  $td_PotAct_max[$i]['POWact3_max'] = number_format($dat->POWact3_max, 2, '.', '');
                  $td_PotReac[$i]['POWrea1'] = number_format($dat->POWrea1, 2, '.', '');
                  $td_PotReac[$i]['POWrea2'] = number_format($dat->POWrea2, 2, '.', '');
                  $td_PotReac[$i]['POWrea3'] = number_format($dat->POWrea3, 2, '.', '');
                  $td_Intensidad[$i]['IAC1'] = number_format($dat->IAC1, 2, '.', '');
                  $td_Intensidad[$i]['IAC2'] = number_format($dat->IAC2, 2, '.', '');
                  $td_Intensidad[$i]['IAC3'] = number_format($dat->IAC3, 2, '.', '');
                  $td_FDP[$i]['PF1'] = number_format($dat->PF1, 2, '.', '');
                  $td_FDP[$i]['PF2'] = number_format($dat->PF2, 2, '.', '');
                  $td_FDP[$i]['PF3'] = number_format($dat->PF3, 2, '.', '');
                  $td_COSPHI[$i]['COSPHI'] = number_format($dat->COSPHI, 2, '.', '');

              }

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


              $values_graficas = $db->table('Potencia_Activa_Por_Fase')->select(\DB::raw("(CASE DAYNAME(date) WHEN 'Monday' THEN 'Lunes' WHEN 'Tuesday' THEN 'Martes' WHEN 'Wednesday' THEN 'Miércoles' WHEN 'Thursday' THEN 'Jueves' WHEN 'Friday' THEN 'Viernes' WHEN 'Saturday' THEN 'Sabado' WHEN 'Sunday' THEN 'Domingo' END) eje, DATE_FORMAT(time, '%H:%i') hora, MAX(`POWact1 (kW)`) potencia_activa_L1, MAX(`POWact2 (kW)`) potencia_activa_L2, MAX(`POWact3 (kW)`) potencia_activa_L3"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy('date')->get();

              $values_corriente = $db->table('Corrientes_Por_Fase')->select(\DB::raw("(CASE DAYNAME(date) WHEN 'Monday' THEN 'Lunes' WHEN 'Tuesday' THEN 'Martes' WHEN 'Wednesday' THEN 'Miércoles' WHEN 'Thursday' THEN 'Jueves' WHEN 'Friday' THEN 'Viernes' WHEN 'Saturday' THEN 'Sabado' WHEN 'Sunday' THEN 'Domingo' END) eje,DATE_FORMAT(time, '%H:%i') hora, MAX(`IAC1 (A)`) corriente_L1, MAX(`IAC2 (A)`) corriente_L2, MAX(`IAC3 (A)`) corriente_L3"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy('date')->get();
              $values_graficas_ = $values_graficas;
              $values_corriente_ = $values_corriente;

              $table_data = $db->table('Analizadores_Tipo')->select(\DB::raw("(CASE DAYNAME(date) WHEN 'Monday' THEN 'Lunes' WHEN 'Tuesday' THEN 'Martes' WHEN 'Wednesday' THEN 'Miércoles' WHEN 'Thursday' THEN 'Jueves' WHEN 'Friday' THEN 'Viernes' WHEN 'Saturday' THEN 'Sabado' WHEN 'Sunday' THEN 'Domingo' END) eje, `date` date, DATE_FORMAT(time, '%H:%i') time, `FRE (Hz)` FRE, `COSPHI` COSPHI, `PF1` PF1, `PF2` PF2, `PF3` PF3, `IAC1 (A)` IAC1, `IAC2 (A)` IAC2, `IAC3 (A)` IAC3, `POWrea1 (kVAr)` POWrea1, `POWrea2 (kVAr)` POWrea2, `POWrea3 (kVAr)` POWrea3, `POWact1 (kW)` POWact1_avg, `POWact2 (kW)` POWact2_avg, `POWact3 (kW)` POWact3_avg, `POWact1 (kW)` POWact1_max, `POWact2 (kW)` POWact2_max, `POWact3 (kW)` POWact3_max, `POWact_Total (kW)` POWact_total "))->where('date','>=',$date_from)->where('date','<=',$date_to)->get();

              $eje_aux = array("Lunes", "Martes", "Miércoles", "Jueves","Viernes", "Sabado", "Domingo");

/*
                for ($i=0; $i < 6; $i++){
                  $td_Frecuencia[$i]['eje'] = $eje_aux[$i];


                  foreach ( $table_data as $dat ) {
                    if($eje_aux[$i] == $dat->date) {
                      $td_Frecuencia[$i]['FRE'] = number_format($dat->FRE, 2, '.', '');
                    }
                  }
                }
*/

                  foreach ( $table_data as $i=>$dat ) {
                        $td_aux[$i]['eje'] = "$dat->eje";
                        $td_aux[$i]['date'] = "$dat->date";
                        $td_aux[$i]['time'] = "$dat->time";
                        $td_PotAct_total[$i]['POWact_total'] = number_format($dat->POWact_total, 2, '.', '');
                        $td_Frecuencia[$i]['FRE'] = number_format($dat->FRE, 2, '.', '');
                        $td_PotAct_avg[$i]['POWact1_avg'] = number_format($dat->POWact1_avg, 2, '.', '');
                        $td_PotAct_avg[$i]['POWact2_avg'] = number_format($dat->POWact2_avg, 2, '.', '');
                        $td_PotAct_avg[$i]['POWact3_avg'] = number_format($dat->POWact3_avg, 2, '.', '');
                        $td_PotAct_max[$i]['POWact1_max'] = number_format($dat->POWact1_max, 2, '.', '');
                        $td_PotAct_max[$i]['POWact2_max'] = number_format($dat->POWact2_max, 2, '.', '');
                        $td_PotAct_max[$i]['POWact3_max'] = number_format($dat->POWact3_max, 2, '.', '');
                        $td_PotReac[$i]['POWrea1'] = number_format($dat->POWrea1, 2, '.', '');
                        $td_PotReac[$i]['POWrea2'] = number_format($dat->POWrea2, 2, '.', '');
                        $td_PotReac[$i]['POWrea3'] = number_format($dat->POWrea3, 2, '.', '');
                        $td_Intensidad[$i]['IAC1'] = number_format($dat->IAC1, 2, '.', '');
                        $td_Intensidad[$i]['IAC2'] = number_format($dat->IAC2, 2, '.', '');
                        $td_Intensidad[$i]['IAC3'] = number_format($dat->IAC3, 2, '.', '');
                        $td_FDP[$i]['PF1'] = number_format($dat->PF1, 2, '.', '');
                        $td_FDP[$i]['PF2'] = number_format($dat->PF2, 2, '.', '');
                        $td_FDP[$i]['PF3'] = number_format($dat->PF3, 2, '.', '');
                        $td_COSPHI[$i]['COSPHI'] = number_format($dat->COSPHI, 2, '.', '');

                    }

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

              $values_graficas = $db->table('Potencia_Activa_Por_Fase')->select(\DB::raw("(CASE DAYNAME(date) WHEN 'Monday' THEN 'Lunes' WHEN 'Tuesday' THEN 'Martes' WHEN 'Wednesday' THEN 'Miércoles' WHEN 'Thursday' THEN 'Jueves' WHEN 'Friday' THEN 'Viernes' WHEN 'Saturday' THEN 'Sabado' WHEN 'Sunday' THEN 'Domingo' END) eje,DATE_FORMAT(time, '%H:%i') hora, MAX(`POWact1 (kW)`) potencia_activa_L1, MAX(`POWact2 (kW)`) potencia_activa_L2, MAX(`POWact3 (kW)`) potencia_activa_L3"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy('date')->get();

              $values_corriente = $db->table('Corrientes_Por_Fase')->select(\DB::raw("(CASE DAYNAME(date) WHEN 'Monday' THEN 'Lunes' WHEN 'Tuesday' THEN 'Martes' WHEN 'Wednesday' THEN 'Miércoles' WHEN 'Thursday' THEN 'Jueves' WHEN 'Friday' THEN 'Viernes' WHEN 'Saturday' THEN 'Sabado' WHEN 'Sunday' THEN 'Domingo' END) eje, DATE_FORMAT(time, '%H:%i') hora, MAX(`IAC1 (A)`) corriente_L1, MAX(`IAC2 (A)`) corriente_L2, MAX(`IAC3 (A)`) corriente_L3"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy('date')->get();
              $values_graficas_ = $values_graficas;
              $values_corriente_ = $values_corriente;
              // dd($values_graficas, $values_corriente);

              $table_data = $db->table('Analizadores_Tipo')->select(\DB::raw("(CASE DAYNAME(date) WHEN 'Monday' THEN 'Lunes' WHEN 'Tuesday' THEN 'Martes' WHEN 'Wednesday' THEN 'Miércoles' WHEN 'Thursday' THEN 'Jueves' WHEN 'Friday' THEN 'Viernes' WHEN 'Saturday' THEN 'Sabado' WHEN 'Sunday' THEN 'Domingo' END) eje, `date` date, DATE_FORMAT(time, '%H:%i') time, `FRE (Hz)` FRE, `COSPHI` COSPHI, `PF1` PF1, `PF2` PF2, `PF3` PF3, `IAC1 (A)` IAC1, `IAC2 (A)` IAC2, `IAC3 (A)` IAC3, `POWrea1 (kVAr)` POWrea1, `POWrea2 (kVAr)` POWrea2, `POWrea3 (kVAr)` POWrea3, `POWact1 (kW)` POWact1_avg, `POWact2 (kW)` POWact2_avg, `POWact3 (kW)` POWact3_avg, `POWact1 (kW)` POWact1_max, `POWact2 (kW)` POWact2_max, `POWact3 (kW)` POWact3_max, `POWact_Total (kW)` POWact_total"))->where('date','>=',$date_from)->where('date','<=',$date_to)->get();

              $eje_aux = array("Lunes", "Martes", "Miércoles", "Jueves","Viernes", "Sabado", "Domingo");

/*
                for ($i=0; $i < 6; $i++){
                  $td_Frecuencia[$i]['eje'] = $eje_aux[$i];


                  foreach ( $table_data as $dat ) {
                    if($eje_aux[$i] == $dat->date) {
                      $td_Frecuencia[$i]['FRE'] = number_format($dat->FRE, 2, '.', '');
                    }
                  }
                }
*/

                  foreach ( $table_data as $i=>$dat ) {
                        $td_aux[$i]['eje'] = "$dat->eje";
                        $td_aux[$i]['date'] = "$dat->date";
                        $td_aux[$i]['time'] = "$dat->time";
                        $td_PotAct_total[$i]['POWact_total'] = number_format($dat->POWact_total, 2, '.', '');
                        $td_Frecuencia[$i]['FRE'] = number_format($dat->FRE, 2, '.', '');
                        $td_PotAct_avg[$i]['POWact1_avg'] = number_format($dat->POWact1_avg, 2, '.', '');
                        $td_PotAct_avg[$i]['POWact2_avg'] = number_format($dat->POWact2_avg, 2, '.', '');
                        $td_PotAct_avg[$i]['POWact3_avg'] = number_format($dat->POWact3_avg, 2, '.', '');
                        $td_PotAct_max[$i]['POWact1_max'] = number_format($dat->POWact1_max, 2, '.', '');
                        $td_PotAct_max[$i]['POWact2_max'] = number_format($dat->POWact2_max, 2, '.', '');
                        $td_PotAct_max[$i]['POWact3_max'] = number_format($dat->POWact3_max, 2, '.', '');
                        $td_PotReac[$i]['POWrea1'] = number_format($dat->POWrea1, 2, '.', '');
                        $td_PotReac[$i]['POWrea2'] = number_format($dat->POWrea2, 2, '.', '');
                        $td_PotReac[$i]['POWrea3'] = number_format($dat->POWrea3, 2, '.', '');
                        $td_Intensidad[$i]['IAC1'] = number_format($dat->IAC1, 2, '.', '');
                        $td_Intensidad[$i]['IAC2'] = number_format($dat->IAC2, 2, '.', '');
                        $td_Intensidad[$i]['IAC3'] = number_format($dat->IAC3, 2, '.', '');
                        $td_FDP[$i]['PF1'] = number_format($dat->PF1, 2, '.', '');
                        $td_FDP[$i]['PF2'] = number_format($dat->PF2, 2, '.', '');
                        $td_FDP[$i]['PF3'] = number_format($dat->PF3, 2, '.', '');
                        $td_COSPHI[$i]['COSPHI'] = number_format($dat->COSPHI, 2, '.', '');
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



              $values_graficas = $db->table('Potencia_Activa_Por_Fase')->select(\DB::raw("DAY(date) eje, DATE_FORMAT(time, '%H:%i') hora, MAX(`POWact1 (kW)`) potencia_activa_L1, MAX(`POWact2 (kW)`) potencia_activa_L2, MAX(`POWact3 (kW)`) potencia_activa_L3"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy('date')->get();

              $values_corriente = $db->table('Corrientes_Por_Fase')->select(\DB::raw("DAY(date) eje, DATE_FORMAT(time, '%H:%i') hora, MAX(`IAC1 (A)`) corriente_L1, MAX(`IAC2 (A)`) corriente_L2, MAX(`IAC3 (A)`) corriente_L3"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy('date')->get();
              $values_graficas_ = $values_graficas;
              $values_corriente_ = $values_corriente;

              $table_data = $db->table('Analizadores_Tipo')->select(\DB::raw("DAY(date) eje, avg(`FRE (Hz)`) FRE, avg(`COSPHI`) COSPHI, avg(`PF1`) PF1, avg(`PF2`) PF2, avg(`PF3`) PF3, avg(`IAC1 (A)`) IAC1, avg(`IAC2 (A)`) IAC2, avg(`IAC3 (A)`) IAC3, avg(`POWrea1 (kVAr)`) POWrea1, avg(`POWrea2 (kVAr)`) POWrea2, avg(`POWrea3 (kVAr)`) POWrea3, avg(`POWact1 (kW)`) POWact1_avg, avg(`POWact2 (kW)`) POWact2_avg, avg(`POWact3 (kW)`) POWact3_avg, max(`POWact1 (kW)`) POWact1_max, max(`POWact2 (kW)`) POWact2_max, max(`POWact3 (kW)`) POWact3_max"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy('date')->get();

              $eje_aux = date('d', strtotime($date_to));

                for ($i=0; $i < $eje_aux; $i++){
                  if($i < 9){
                  $td_aux[$i]['eje'] = str_pad($i+1, 2, '0', STR_PAD_LEFT);
                  $td_aux[$i]['date'] = date('Y-m-', strtotime($date_to)).$td_aux[$i]['eje'];
                  $td_aux[$i]['eje'] = $i+1;
                }else{
                  $td_aux[$i]['eje'] = $i+1;
                  $td_aux[$i]['date'] = date('Y-m-', strtotime($date_to)).$td_aux[$i]['eje'];
                }

                  foreach ( $table_data as $dat ) {
                    if($i+1 == $dat->eje) {

                      $td_Frecuencia[$i]['FRE'] = number_format($dat->FRE, 2, '.', '');
                      $td_PotAct_avg[$i]['POWact1_avg'] = number_format($dat->POWact1_avg, 2, '.', '');
                      $td_PotAct_avg[$i]['POWact2_avg'] = number_format($dat->POWact2_avg, 2, '.', '');
                      $td_PotAct_avg[$i]['POWact3_avg'] = number_format($dat->POWact3_avg, 2, '.', '');
                      $td_PotAct_max[$i]['POWact1_max'] = number_format($dat->POWact1_max, 2, '.', '');
                      $td_PotAct_max[$i]['POWact2_max'] = number_format($dat->POWact2_max, 2, '.', '');
                      $td_PotAct_max[$i]['POWact3_max'] = number_format($dat->POWact3_max, 2, '.', '');
                      $td_PotReac[$i]['POWrea1'] = number_format($dat->POWrea1, 2, '.', '');
                      $td_PotReac[$i]['POWrea2'] = number_format($dat->POWrea2, 2, '.', '');
                      $td_PotReac[$i]['POWrea3'] = number_format($dat->POWrea3, 2, '.', '');
                      $td_Intensidad[$i]['IAC1'] = number_format($dat->IAC1, 2, '.', '');
                      $td_Intensidad[$i]['IAC2'] = number_format($dat->IAC2, 2, '.', '');
                      $td_Intensidad[$i]['IAC3'] = number_format($dat->IAC3, 2, '.', '');
                      $td_FDP[$i]['PF1'] = number_format($dat->PF1, 2, '.', '');
                      $td_FDP[$i]['PF2'] = number_format($dat->PF2, 2, '.', '');
                      $td_FDP[$i]['PF3'] = number_format($dat->PF3, 2, '.', '');
                      $td_COSPHI[$i]['COSPHI'] = number_format($dat->COSPHI, 2, '.', '');
                    }else{
                      if (!isset($td_Frecuencia[$i]['FRE'])) {
                          $td_Frecuencia[$i]['FRE'] = null;
                          $td_PotAct_avg[$i]['POWact1_avg'] = null;
                          $td_PotAct_avg[$i]['POWact2_avg'] = null;
                          $td_PotAct_avg[$i]['POWact3_avg'] = null;
                          $td_PotAct_max[$i]['POWact1_max'] = null;
                          $td_PotAct_max[$i]['POWact2_max'] = null;
                          $td_PotAct_max[$i]['POWact3_max'] = null;
                          $td_PotReac[$i]['POWrea1'] = null;
                          $td_PotReac[$i]['POWrea2'] = null;
                          $td_PotReac[$i]['POWrea3'] = null;
                          $td_Intensidad[$i]['IAC1'] = null;
                          $td_Intensidad[$i]['IAC2'] = null;
                          $td_Intensidad[$i]['IAC3'] = null;
                          $td_FDP[$i]['PF1'] = null;
                          $td_FDP[$i]['PF2'] = null;
                          $td_FDP[$i]['PF3'] = null;
                          $td_COSPHI[$i]['COSPHI'] = null;

                      }
                    }
                  }
                }

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

              $values_graficas = $db->table('Potencia_Activa_Por_Fase')->select(\DB::raw("DAY(date) eje, DATE_FORMAT(time, '%H:%i') hora, MAX(`POWact1 (kW)`) potencia_activa_L1, MAX(`POWact2 (kW)`) potencia_activa_L2, MAX(`POWact3 (kW)`) potencia_activa_L3"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy('date')->get();

              $values_corriente = $db->table('Corrientes_Por_Fase')->select(\DB::raw("DAY(date) eje, DATE_FORMAT(time, '%H:%i') hora, MAX(`IAC1 (A)`) corriente_L1, MAX(`IAC2 (A)`) corriente_L2, MAX(`IAC3 (A)`) corriente_L3"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy('date')->get();
              $values_graficas_ = $values_graficas;
              $values_corriente_ = $values_corriente;

              $table_data = $db->table('Analizadores_Tipo')->select(\DB::raw("DAY(date) eje, avg(`FRE (Hz)`) FRE, avg(`COSPHI`) COSPHI, avg(`PF1`) PF1, avg(`PF2`) PF2, avg(`PF3`) PF3, avg(`IAC1 (A)`) IAC1, avg(`IAC2 (A)`) IAC2, avg(`IAC3 (A)`) IAC3, avg(`POWrea1 (kVAr)`) POWrea1, avg(`POWrea2 (kVAr)`) POWrea2, avg(`POWrea3 (kVAr)`) POWrea3, avg(`POWact1 (kW)`) POWact1_avg, avg(`POWact2 (kW)`) POWact2_avg, avg(`POWact3 (kW)`) POWact3_avg, max(`POWact1 (kW)`) POWact1_max, max(`POWact2 (kW)`) POWact2_max, max(`POWact3 (kW)`) POWact3_max"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy('date')->get();

              $eje_aux = date('d', strtotime($date_to));

                for ($i=0; $i < $eje_aux; $i++){
                  if($i < 9){
                  $td_aux[$i]['eje'] = str_pad($i+1, 2, '0', STR_PAD_LEFT);
                  $td_aux[$i]['date'] = date('Y-m-', strtotime($date_to)).$td_aux[$i]['eje'];
                  $td_aux[$i]['eje'] = $i+1;
                }else{
                  $td_aux[$i]['eje'] = $i+1;
                  $td_aux[$i]['date'] = date('Y-m-', strtotime($date_to)).$td_aux[$i]['eje'];
                }

                  foreach ( $table_data as $dat ) {
                    if($i+1 == $dat->eje) {

                      $td_Frecuencia[$i]['FRE'] = number_format($dat->FRE, 2, '.', '');
                      $td_PotAct_avg[$i]['POWact1_avg'] = number_format($dat->POWact1_avg, 2, '.', '');
                      $td_PotAct_avg[$i]['POWact2_avg'] = number_format($dat->POWact2_avg, 2, '.', '');
                      $td_PotAct_avg[$i]['POWact3_avg'] = number_format($dat->POWact3_avg, 2, '.', '');
                      $td_PotAct_max[$i]['POWact1_max'] = number_format($dat->POWact1_max, 2, '.', '');
                      $td_PotAct_max[$i]['POWact2_max'] = number_format($dat->POWact2_max, 2, '.', '');
                      $td_PotAct_max[$i]['POWact3_max'] = number_format($dat->POWact3_max, 2, '.', '');
                      $td_PotReac[$i]['POWrea1'] = number_format($dat->POWrea1, 2, '.', '');
                      $td_PotReac[$i]['POWrea2'] = number_format($dat->POWrea2, 2, '.', '');
                      $td_PotReac[$i]['POWrea3'] = number_format($dat->POWrea3, 2, '.', '');
                      $td_Intensidad[$i]['IAC1'] = number_format($dat->IAC1, 2, '.', '');
                      $td_Intensidad[$i]['IAC2'] = number_format($dat->IAC2, 2, '.', '');
                      $td_Intensidad[$i]['IAC3'] = number_format($dat->IAC3, 2, '.', '');
                      $td_FDP[$i]['PF1'] = number_format($dat->PF1, 2, '.', '');
                      $td_FDP[$i]['PF2'] = number_format($dat->PF2, 2, '.', '');
                      $td_FDP[$i]['PF3'] = number_format($dat->PF3, 2, '.', '');
                      $td_COSPHI[$i]['COSPHI'] = number_format($dat->COSPHI, 2, '.', '');
                    }else{
                      if (!isset($td_Frecuencia[$i]['FRE'])) {
                          $td_Frecuencia[$i]['FRE'] = null;
                          $td_PotAct_avg[$i]['POWact1_avg'] = null;
                          $td_PotAct_avg[$i]['POWact2_avg'] = null;
                          $td_PotAct_avg[$i]['POWact3_avg'] = null;
                          $td_PotAct_max[$i]['POWact1_max'] = null;
                          $td_PotAct_max[$i]['POWact2_max'] = null;
                          $td_PotAct_max[$i]['POWact3_max'] = null;
                          $td_PotReac[$i]['POWrea1'] = null;
                          $td_PotReac[$i]['POWrea2'] = null;
                          $td_PotReac[$i]['POWrea3'] = null;
                          $td_Intensidad[$i]['IAC1'] = null;
                          $td_Intensidad[$i]['IAC2'] = null;
                          $td_Intensidad[$i]['IAC3'] = null;
                          $td_FDP[$i]['PF1'] = null;
                          $td_FDP[$i]['PF2'] = null;
                          $td_FDP[$i]['PF3'] = null;
                          $td_COSPHI[$i]['COSPHI'] = null;
                      }
                    }
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
                      $eje[0] = 'Octubre('.\Carbon\Carbon::parse($date_from)->year.')';
                      $eje[1] = 'Noviembre('.\Carbon\Carbon::parse($date_from)->year.')';
                      $eje[2] = 'Diciembre('.\Carbon\Carbon::parse($date_from)->year.')';
                  }elseif($now == 4 || $now == 7 || $now == 10){
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
                  // dd($now);
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

              $values_graficas = $db->table('Potencia_Activa_Por_Fase')->select(\DB::raw("(CASE MONTH(date) WHEN '01' THEN CONCAT('Enero(',YEAR(date),')') WHEN '02' THEN CONCAT('Febrero(',YEAR(date),')') WHEN '03' THEN CONCAT('Marzo(',YEAR(date),')') WHEN '04' THEN CONCAT('Abril(',YEAR(date),')') WHEN '05' THEN CONCAT('Mayo(',YEAR(date),')') WHEN '06' THEN CONCAT('Junio(',YEAR(date),')') WHEN '07' THEN CONCAT('Julio(',YEAR(date),')') WHEN '08' THEN CONCAT('Agosto(',YEAR(date),')') WHEN '09' THEN CONCAT('Septiembre(',YEAR(date),')') WHEN '10' THEN CONCAT('Octubre(',YEAR(date),')') WHEN '11' THEN CONCAT('Noviembre(',YEAR(date),')') WHEN '12' THEN CONCAT('Diciembre(',YEAR(date),')') END) eje, DATE_FORMAT(time, '%H:%i') hora, MAX(`POWact1 (kW)`) potencia_activa_L1, MAX(`POWact2 (kW)`) potencia_activa_L2, MAX(`POWact3 (kW)`) potencia_activa_L3"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy(\DB::raw('MONTH(date)'))->orderBy(\DB::raw('MONTH(date)'),'ASC')->get()->toArray();
              for ($t=0; $t < 3; $t++)
              {
                  $values_graficas_[$t]['eje'] = $eje[$t];
                  $values_graficas_[$t]['potencia_activa_L1'] = 0;
                  $values_graficas_[$t]['potencia_activa_L2'] = 0;
                  $values_graficas_[$t]['potencia_activa_L3'] = 0;
                  foreach ($values_graficas as $val)
                  {
                      // dd($val);
                      $band = 1;
                      if(!empty($val) || !is_null($val))
                      {
                          if($val->eje == $eje[$t])
                          {
                              $values_graficas_[$t]['potencia_activa_L1'] = $val->potencia_activa_L1;
                              $values_graficas_[$t]['potencia_activa_L2'] = $val->potencia_activa_L2;
                              $values_graficas_[$t]['potencia_activa_L3'] = $val->potencia_activa_L3;
                              break;
                          }else{
                              $values_graficas_[$t]['potencia_activa_L1'] = 0;
                              $values_graficas_[$t]['potencia_activa_L2'] = 0;
                              $values_graficas_[$t]['potencia_activa_L3'] = 0;
                          }
                      }
                  }
              }

              $values_corriente = $db->table('Corrientes_Por_Fase')->select(\DB::raw("(CASE MONTH(date) WHEN '01' THEN CONCAT('Enero(',YEAR(date),')') WHEN '02' THEN CONCAT('Febrero(',YEAR(date),')') WHEN '03' THEN CONCAT('Marzo(',YEAR(date),')') WHEN '04' THEN CONCAT('Abril(',YEAR(date),')') WHEN '05' THEN CONCAT('Mayo(',YEAR(date),')') WHEN '06' THEN CONCAT('Junio(',YEAR(date),')') WHEN '07' THEN CONCAT('Julio(',YEAR(date),')') WHEN '08' THEN CONCAT('Agosto(',YEAR(date),')') WHEN '09' THEN CONCAT('Septiembre(',YEAR(date),')') WHEN '10' THEN CONCAT('Octubre(',YEAR(date),')') WHEN '11' THEN CONCAT('Noviembre(',YEAR(date),')') WHEN '12' THEN CONCAT('Diciembre(',YEAR(date),')') END) eje, DATE_FORMAT(time, '%H:%i') hora, MAX(`IAC1 (A)`) corriente_L1, MAX(`IAC2 (A)`) corriente_L2, MAX(`IAC3 (A)`) corriente_L3"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy(\DB::raw('MONTH(date)'))->orderBy(\DB::raw('MONTH(date)'),'ASC')->get();
              for ($t=0; $t < 3; $t++) {
                  $values_corriente_[$t]['eje'] = $eje[$t];
                  $values_corriente_[$t]['corriente_L1'] = 0;
                  $values_corriente_[$t]['corriente_L2'] = 0;
                  $values_corriente_[$t]['corriente_L3'] = 0;
                  foreach ($values_corriente as $val)
                  {
                      $band = 1;
                      if(!empty($val) || !is_null($val))
                      {
                          if($val->eje == $eje[$t])
                          {
                              $values_corriente_[$t]['corriente_L1'] = $val->corriente_L1;
                              $values_corriente_[$t]['corriente_L2'] = $val->corriente_L2;
                              $values_corriente_[$t]['corriente_L3'] = $val->corriente_L3;
                              break;
                          }else{
                              $values_corriente_[$t]['corriente_L1'] = 0;
                              $values_corriente_[$t]['corriente_L2'] = 0;
                              $values_corriente_[$t]['corriente_L3'] = 0;
                          }
                      }
                  }
              }

              $table_data = $db->table('Analizadores_Tipo')->select(\DB::raw("(CASE MONTH(date) WHEN '01' THEN CONCAT('Enero(',YEAR(date),')') WHEN '02' THEN CONCAT('Febrero(',YEAR(date),')') WHEN '03' THEN CONCAT('Marzo(',YEAR(date),')') WHEN '04' THEN CONCAT('Abril(',YEAR(date),')') WHEN '05' THEN CONCAT('Mayo(',YEAR(date),')') WHEN '06' THEN CONCAT('Junio(',YEAR(date),')') WHEN '07' THEN CONCAT('Julio(',YEAR(date),')') WHEN '08' THEN CONCAT('Agosto(',YEAR(date),')') WHEN '09' THEN CONCAT('Septiembre(',YEAR(date),')') WHEN '10' THEN CONCAT('Octubre(',YEAR(date),')') WHEN '11' THEN CONCAT('Noviembre(',YEAR(date),')') WHEN '12' THEN CONCAT('Diciembre(',YEAR(date),')') END) eje, avg(`FRE (Hz)`) FRE, avg(`COSPHI`) COSPHI, avg(`PF1`) PF1, avg(`PF2`) PF2, avg(`PF3`) PF3, avg(`IAC1 (A)`) IAC1, avg(`IAC2 (A)`) IAC2, avg(`IAC3 (A)`) IAC3, avg(`POWrea1 (kVAr)`) POWrea1, avg(`POWrea2 (kVAr)`) POWrea2, avg(`POWrea3 (kVAr)`) POWrea3, avg(`POWact1 (kW)`) POWact1_avg, avg(`POWact2 (kW)`) POWact2_avg, avg(`POWact3 (kW)`) POWact3_avg, max(`POWact1 (kW)`) POWact1_max, max(`POWact2 (kW)`) POWact2_max, max(`POWact3 (kW)`) POWact3_max"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy(\DB::raw('MONTH(date)'))->orderBy(\DB::raw('MONTH(date)'),'ASC')->get()->toArray();


              for ($i=0; $i < 3; $i++){
                $td_aux[$i]['eje'] = $eje[$i];


                foreach ( $table_data as $dat ) {
                  if($eje[$i] == $dat->eje) {
                    $td_Frecuencia[$i]['FRE'] = number_format($dat->FRE, 2, '.', '');
                    $td_PotAct_avg[$i]['POWact1_avg'] = number_format($dat->POWact1_avg, 2, '.', '');
                    $td_PotAct_avg[$i]['POWact2_avg'] = number_format($dat->POWact2_avg, 2, '.', '');
                    $td_PotAct_avg[$i]['POWact3_avg'] = number_format($dat->POWact3_avg, 2, '.', '');
                    $td_PotAct_max[$i]['POWact1_max'] = number_format($dat->POWact1_max, 2, '.', '');
                    $td_PotAct_max[$i]['POWact2_max'] = number_format($dat->POWact2_max, 2, '.', '');
                    $td_PotAct_max[$i]['POWact3_max'] = number_format($dat->POWact3_max, 2, '.', '');
                    $td_PotReac[$i]['POWrea1'] = number_format($dat->POWrea1, 2, '.', '');
                    $td_PotReac[$i]['POWrea2'] = number_format($dat->POWrea2, 2, '.', '');
                    $td_PotReac[$i]['POWrea3'] = number_format($dat->POWrea3, 2, '.', '');
                    $td_Intensidad[$i]['IAC1'] = number_format($dat->IAC1, 2, '.', '');
                    $td_Intensidad[$i]['IAC2'] = number_format($dat->IAC2, 2, '.', '');
                    $td_Intensidad[$i]['IAC3'] = number_format($dat->IAC3, 2, '.', '');
                    $td_FDP[$i]['PF1'] = number_format($dat->PF1, 2, '.', '');
                    $td_FDP[$i]['PF2'] = number_format($dat->PF2, 2, '.', '');
                    $td_FDP[$i]['PF3'] = number_format($dat->PF3, 2, '.', '');
                    $td_COSPHI[$i]['COSPHI'] = number_format($dat->COSPHI, 2, '.', '');
                  }else{
                    if (!isset($td_Frecuencia[$i]['FRE'])) {
                        $td_Frecuencia[$i]['FRE'] = null;
                        $td_PotAct_avg[$i]['POWact1_avg'] = null;
                        $td_PotAct_avg[$i]['POWact2_avg'] = null;
                        $td_PotAct_avg[$i]['POWact3_avg'] = null;
                        $td_PotAct_max[$i]['POWact1_max'] = null;
                        $td_PotAct_max[$i]['POWact2_max'] = null;
                        $td_PotAct_max[$i]['POWact3_max'] = null;
                        $td_PotReac[$i]['POWrea1'] = null;
                        $td_PotReac[$i]['POWrea2'] = null;
                        $td_PotReac[$i]['POWrea3'] = null;
                        $td_Intensidad[$i]['IAC1'] = null;
                        $td_Intensidad[$i]['IAC2'] = null;
                        $td_Intensidad[$i]['IAC3'] = null;
                        $td_FDP[$i]['PF1'] = null;
                        $td_FDP[$i]['PF2'] = null;
                        $td_FDP[$i]['PF3'] = null;
                        $td_COSPHI[$i]['COSPHI'] = null;
                      }
                    }
                  }
                }

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
              $values_graficas = $db->table('Potencia_Activa_Por_Fase')->select(\DB::raw("(CASE MONTH(date) WHEN '01' THEN CONCAT('Enero(',YEAR(date),')') WHEN '02' THEN CONCAT('Febrero(',YEAR(date),')') WHEN '03' THEN CONCAT('Marzo(',YEAR(date),')') WHEN '04' THEN CONCAT('Abril(',YEAR(date),')') WHEN '05' THEN CONCAT('Mayo(',YEAR(date),')') WHEN '06' THEN CONCAT('Junio(',YEAR(date),')') WHEN '07' THEN CONCAT('Julio(',YEAR(date),')') WHEN '08' THEN CONCAT('Agosto(',YEAR(date),')') WHEN '09' THEN CONCAT('Septiembre(',YEAR(date),')') WHEN '10' THEN CONCAT('Octubre(',YEAR(date),')') WHEN '11' THEN CONCAT('Noviembre(',YEAR(date),')') WHEN '12' THEN CONCAT('Diciembre(',YEAR(date),')') END) eje, DATE_FORMAT(time, '%H:%i') hora, MAX(`POWact1 (kW)`) potencia_activa_L1, MAX(`POWact2 (kW)`) potencia_activa_L2, MAX(`POWact3 (kW)`) potencia_activa_L3"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy(\DB::raw('MONTH(date)'))->orderBy(\DB::raw('MONTH(date)'),'ASC')->get()->toArray();
              for ($t=0; $t < 3; $t++)
              {
                  $values_graficas_[$t]['eje'] = $eje[$t];
                  $values_graficas_[$t]['potencia_activa_L1'] = 0;
                  $values_graficas_[$t]['potencia_activa_L2'] = 0;
                  $values_graficas_[$t]['potencia_activa_L3'] = 0;
                  foreach ($values_graficas as $val)
                  {
                      $band = 1;
                      if(!empty($val) || !is_null($val))
                      {
                          if($val->eje == $eje[$t])
                          {
                              $values_graficas_[$t]['eje'] = $eje[$t];
                              $values_graficas_[$t]['potencia_activa_L1'] = $val->potencia_activa_L1;
                              $values_graficas_[$t]['potencia_activa_L2'] = $val->potencia_activa_L2;
                              $values_graficas_[$t]['potencia_activa_L3'] = $val->potencia_activa_L3;
                              break;
                          }else{
                              $values_graficas_[$t]['potencia_activa_L1'] = 0;
                              $values_graficas_[$t]['potencia_activa_L2'] = 0;
                              $values_graficas_[$t]['potencia_activa_L3'] = 0;
                          }
                      }
                  }
              }

              $values_corriente = $db->table('Corrientes_Por_Fase')->select(\DB::raw("(CASE MONTH(date) WHEN '01' THEN CONCAT('Enero(',YEAR(date),')') WHEN '02' THEN CONCAT('Febrero(',YEAR(date),')') WHEN '03' THEN CONCAT('Marzo(',YEAR(date),')') WHEN '04' THEN CONCAT('Abril(',YEAR(date),')') WHEN '05' THEN CONCAT('Mayo(',YEAR(date),')') WHEN '06' THEN CONCAT('Junio(',YEAR(date),')') WHEN '07' THEN CONCAT('Julio(',YEAR(date),')') WHEN '08' THEN CONCAT('Agosto(',YEAR(date),')') WHEN '09' THEN CONCAT('Septiembre(',YEAR(date),')') WHEN '10' THEN CONCAT('Octubre(',YEAR(date),')') WHEN '11' THEN CONCAT('Noviembre(',YEAR(date),')') WHEN '12' THEN CONCAT('Diciembre(',YEAR(date),')') END) eje, DATE_FORMAT(time, '%H:%i') hora, MAX(`IAC1 (A)`) corriente_L1, MAX(`IAC2 (A)`) corriente_L2, MAX(`IAC3 (A)`) corriente_L3"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy(\DB::raw('MONTH(date)'))->orderBy(\DB::raw('MONTH(date)'),'ASC')->get()->toArray();
              for ($t=0; $t < 3; $t++)
              {
                  $values_corriente_[$t]['eje'] = $eje[$t];
                  $values_corriente_[$t]['corriente_L1'] = 0;
                  $values_corriente_[$t]['corriente_L2'] = 0;
                  $values_corriente_[$t]['corriente_L3'] = 0;
                  foreach ($values_corriente as $val)
                  {
                      $band = 1;
                      if(!empty($val) || !is_null($val))
                      {
                          if($val->eje == $eje[$t])
                          {
                              $values_graficas_[$t]['eje'] = $eje[$t];
                              $values_corriente_[$t]['corriente_L1'] = $val->corriente_L1;
                              $values_corriente_[$t]['corriente_L2'] = $val->corriente_L2;
                              $values_corriente_[$t]['corriente_L3'] = $val->corriente_L3;

                              break;
                          }else{
                              $values_graficas_[$t]['eje'] = $eje[$t];
                              $values_corriente_[$t]['corriente_L1'] = 0;
                              $values_corriente_[$t]['corriente_L2'] = 0;
                              $values_corriente_[$t]['corriente_L3'] = 0;
                          }
                      }
                  }
              }

              $table_data = $db->table('Analizadores_Tipo')->select(\DB::raw("(CASE MONTH(date) WHEN '01' THEN CONCAT('Enero(',YEAR(date),')') WHEN '02' THEN CONCAT('Febrero(',YEAR(date),')') WHEN '03' THEN CONCAT('Marzo(',YEAR(date),')') WHEN '04' THEN CONCAT('Abril(',YEAR(date),')') WHEN '05' THEN CONCAT('Mayo(',YEAR(date),')') WHEN '06' THEN CONCAT('Junio(',YEAR(date),')') WHEN '07' THEN CONCAT('Julio(',YEAR(date),')') WHEN '08' THEN CONCAT('Agosto(',YEAR(date),')') WHEN '09' THEN CONCAT('Septiembre(',YEAR(date),')') WHEN '10' THEN CONCAT('Octubre(',YEAR(date),')') WHEN '11' THEN CONCAT('Noviembre(',YEAR(date),')') WHEN '12' THEN CONCAT('Diciembre(',YEAR(date),')') END) eje, avg(`FRE (Hz)`) FRE, avg(`COSPHI`) COSPHI, avg(`PF1`) PF1, avg(`PF2`) PF2, avg(`PF3`) PF3, avg(`IAC1 (A)`) IAC1, avg(`IAC2 (A)`) IAC2, avg(`IAC3 (A)`) IAC3, avg(`POWrea1 (kVAr)`) POWrea1, avg(`POWrea2 (kVAr)`) POWrea2, avg(`POWrea3 (kVAr)`) POWrea3, avg(`POWact1 (kW)`) POWact1_avg, avg(`POWact2 (kW)`) POWact2_avg, avg(`POWact3 (kW)`) POWact3_avg, max(`POWact1 (kW)`) POWact1_max, max(`POWact2 (kW)`) POWact2_max, max(`POWact3 (kW)`) POWact3_max"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy(\DB::raw('MONTH(date)'))->orderBy(\DB::raw('MONTH(date)'),'ASC')->get()->toArray();

              for ($i=0; $i < 3; $i++){
                $td_aux[$i]['eje'] = $eje[$i];


                foreach ( $table_data as $dat ) {
                  if($eje[$i] == $dat->eje) {
                    $td_Frecuencia[$i]['FRE'] = number_format($dat->FRE, 2, '.', '');
                    $td_PotAct_avg[$i]['POWact1_avg'] = number_format($dat->POWact1_avg, 2, '.', '');
                    $td_PotAct_avg[$i]['POWact2_avg'] = number_format($dat->POWact2_avg, 2, '.', '');
                    $td_PotAct_avg[$i]['POWact3_avg'] = number_format($dat->POWact3_avg, 2, '.', '');
                    $td_PotAct_max[$i]['POWact1_max'] = number_format($dat->POWact1_max, 2, '.', '');
                    $td_PotAct_max[$i]['POWact2_max'] = number_format($dat->POWact2_max, 2, '.', '');
                    $td_PotAct_max[$i]['POWact3_max'] = number_format($dat->POWact3_max, 2, '.', '');
                    $td_PotReac[$i]['POWrea1'] = number_format($dat->POWrea1, 2, '.', '');
                    $td_PotReac[$i]['POWrea2'] = number_format($dat->POWrea2, 2, '.', '');
                    $td_PotReac[$i]['POWrea3'] = number_format($dat->POWrea3, 2, '.', '');
                    $td_Intensidad[$i]['IAC1'] = number_format($dat->IAC1, 2, '.', '');
                    $td_Intensidad[$i]['IAC2'] = number_format($dat->IAC2, 2, '.', '');
                    $td_Intensidad[$i]['IAC3'] = number_format($dat->IAC3, 2, '.', '');
                    $td_FDP[$i]['PF1'] = number_format($dat->PF1, 2, '.', '');
                    $td_FDP[$i]['PF2'] = number_format($dat->PF2, 2, '.', '');
                    $td_FDP[$i]['PF3'] = number_format($dat->PF3, 2, '.', '');
                    $td_COSPHI[$i]['COSPHI'] = number_format($dat->COSPHI, 2, '.', '');
                  }else{
                    if (!isset($td_Frecuencia[$i]['FRE'])) {
                        $td_Frecuencia[$i]['FRE'] = null;
                        $td_PotAct_avg[$i]['POWact1_avg'] = null;
                        $td_PotAct_avg[$i]['POWact2_avg'] = null;
                        $td_PotAct_avg[$i]['POWact3_avg'] = null;
                        $td_PotAct_max[$i]['POWact1_max'] = null;
                        $td_PotAct_max[$i]['POWact2_max'] = null;
                        $td_PotAct_max[$i]['POWact3_max'] = null;
                        $td_PotReac[$i]['POWrea1'] = null;
                        $td_PotReac[$i]['POWrea2'] = null;
                        $td_PotReac[$i]['POWrea3'] = null;
                        $td_Intensidad[$i]['IAC1'] = null;
                        $td_Intensidad[$i]['IAC2'] = null;
                        $td_Intensidad[$i]['IAC3'] = null;
                        $td_FDP[$i]['PF1'] = null;
                        $td_FDP[$i]['PF2'] = null;
                        $td_FDP[$i]['PF3'] = null;
                        $td_COSPHI[$i]['COSPHI'] = null;
                      }
                    }
                  }
                }

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

              $label_intervalo = 'Último Año';

              $values_graficas = $db->table('Potencia_Activa_Por_Fase')->select(\DB::raw("(CASE MONTH(date) WHEN '01' THEN CONCAT('Enero(',YEAR(date),')') WHEN '02' THEN CONCAT('Febrero(',YEAR(date),')') WHEN '03' THEN CONCAT('Marzo(',YEAR(date),')') WHEN '04' THEN CONCAT('Abril(',YEAR(date),')') WHEN '05' THEN CONCAT('Mayo(',YEAR(date),')') WHEN '06' THEN CONCAT('Junio(',YEAR(date),')') WHEN '07' THEN CONCAT('Julio(',YEAR(date),')') WHEN '08' THEN CONCAT('Agosto(',YEAR(date),')') WHEN '09' THEN CONCAT('Septiembre(',YEAR(date),')') WHEN '10' THEN CONCAT('Octubre(',YEAR(date),')') WHEN '11' THEN CONCAT('Noviembre(',YEAR(date),')') WHEN '12' THEN CONCAT('Diciembre(',YEAR(date),')') END) eje, DATE_FORMAT(time, '%H:%i') hora, MAX(`POWact1 (kW)`) potencia_activa_L1, MAX(`POWact2 (kW)`) potencia_activa_L2, MAX(`POWact3 (kW)`) potencia_activa_L3"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy(\DB::raw('MONTH(date)'))->orderBy(\DB::raw('MONTH(date)'),'ASC')->get();
              for ($t=0; $t < 12; $t++)
              {
                  $values_graficas_[$t]['eje'] = $eje[$t];
                  $values_graficas_[$t]['potencia_activa_L1'] = 0;
                  $values_graficas_[$t]['potencia_activa_L2'] = 0;
                  $values_graficas_[$t]['potencia_activa_L3'] = 0;
                  foreach ($values_graficas as $val)
                  {
                      if($val->eje == $eje[$t])
                      {
                          $values_graficas_[$t]['eje'] = $eje[$t];
                          $values_graficas_[$t]['potencia_activa_L1'] = $val->potencia_activa_L1;
                          $values_graficas_[$t]['potencia_activa_L2'] = $val->potencia_activa_L2;
                          $values_graficas_[$t]['potencia_activa_L3'] = $val->potencia_activa_L3;
                          break;
                      }else{
                          $values_graficas_[$t]['potencia_activa_L1'] = 0;
                          $values_graficas_[$t]['potencia_activa_L2'] = 0;
                          $values_graficas_[$t]['potencia_activa_L3'] = 0;
                      }
                  }
              }
              // \DB::raw('MONTH(date))'

              $values_corriente = $db->table('Corrientes_Por_Fase')->select(\DB::raw("(CASE MONTH(date) WHEN '01' THEN CONCAT('Enero(',YEAR(date),')') WHEN '02' THEN CONCAT('Febrero(',YEAR(date),')') WHEN '03' THEN CONCAT('Marzo(',YEAR(date),')') WHEN '04' THEN CONCAT('Abril(',YEAR(date),')') WHEN '05' THEN CONCAT('Mayo(',YEAR(date),')') WHEN '06' THEN CONCAT('Junio(',YEAR(date),')') WHEN '07' THEN CONCAT('Julio(',YEAR(date),')') WHEN '08' THEN CONCAT('Agosto(',YEAR(date),')') WHEN '09' THEN CONCAT('Septiembre(',YEAR(date),')') WHEN '10' THEN CONCAT('Octubre(',YEAR(date),')') WHEN '11' THEN CONCAT('Noviembre(',YEAR(date),')') WHEN '12' THEN CONCAT('Diciembre(',YEAR(date),')') END) eje, DATE_FORMAT(time, '%H:%i') hora, MAX(`IAC1 (A)`) corriente_L1, MAX(`IAC2 (A)`) corriente_L2, MAX(`IAC3 (A)`) corriente_L3"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy(\DB::raw('MONTH(date)'))->orderBy(\DB::raw('MONTH(date)'),'ASC')->get();
              for ($t=0; $t < 12; $t++)
              {
                  $values_corriente_[$t]['eje'] = $eje[$t];
                  $values_corriente_[$t]['corriente_L1'] = 0;
                  $values_corriente_[$t]['corriente_L2'] = 0;
                  $values_corriente_[$t]['corriente_L3'] = 0;
                  foreach ($values_corriente as $val)
                  {
                      if($val->eje == $eje[$t])
                      {
                          $values_corriente_[$t]['eje'] = $eje[$t];
                          $values_corriente_[$t]['corriente_L1'] = $val->corriente_L1;
                          $values_corriente_[$t]['corriente_L2'] = $val->corriente_L2;
                          $values_corriente_[$t]['corriente_L3'] = $val->corriente_L3;
                          break;
                      }else{
                          $values_corriente_[$t]['corriente_L1'] = 0;
                          $values_corriente_[$t]['corriente_L2'] = 0;
                          $values_corriente_[$t]['corriente_L3'] = 0;
                      }
                  }
              }

              $table_data = $db->table('Analizadores_Tipo')->select(\DB::raw("(CASE MONTH(date) WHEN '01' THEN CONCAT('Enero(',YEAR(date),')') WHEN '02' THEN CONCAT('Febrero(',YEAR(date),')') WHEN '03' THEN CONCAT('Marzo(',YEAR(date),')') WHEN '04' THEN CONCAT('Abril(',YEAR(date),')') WHEN '05' THEN CONCAT('Mayo(',YEAR(date),')') WHEN '06' THEN CONCAT('Junio(',YEAR(date),')') WHEN '07' THEN CONCAT('Julio(',YEAR(date),')') WHEN '08' THEN CONCAT('Agosto(',YEAR(date),')') WHEN '09' THEN CONCAT('Septiembre(',YEAR(date),')') WHEN '10' THEN CONCAT('Octubre(',YEAR(date),')') WHEN '11' THEN CONCAT('Noviembre(',YEAR(date),')') WHEN '12' THEN CONCAT('Diciembre(',YEAR(date),')') END) eje, avg(`FRE (Hz)`) FRE, avg(`COSPHI`) COSPHI, avg(`PF1`) PF1, avg(`PF2`) PF2, avg(`PF3`) PF3, avg(`IAC1 (A)`) IAC1, avg(`IAC2 (A)`) IAC2, avg(`IAC3 (A)`) IAC3, avg(`POWrea1 (kVAr)`) POWrea1, avg(`POWrea2 (kVAr)`) POWrea2, avg(`POWrea3 (kVAr)`) POWrea3, avg(`POWact1 (kW)`) POWact1_avg, avg(`POWact2 (kW)`) POWact2_avg, avg(`POWact3 (kW)`) POWact3_avg, max(`POWact1 (kW)`) POWact1_max, max(`POWact2 (kW)`) POWact2_max, max(`POWact3 (kW)`) POWact3_max"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy(\DB::raw('MONTH(date)'))->orderBy(\DB::raw('MONTH(date)'),'ASC')->get();

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

              for ($i=0; $i < 12; $i++){
                $td_aux[$i]['eje'] = $eje[$i];


                foreach ( $table_data as $dat ) {
                  if($eje[$i] == $dat->eje) {
                    $td_Frecuencia[$i]['FRE'] = number_format($dat->FRE, 2, '.', '');
                    $td_PotAct_avg[$i]['POWact1_avg'] = number_format($dat->POWact1_avg, 2, '.', '');
                    $td_PotAct_avg[$i]['POWact2_avg'] = number_format($dat->POWact2_avg, 2, '.', '');
                    $td_PotAct_avg[$i]['POWact3_avg'] = number_format($dat->POWact3_avg, 2, '.', '');
                    $td_PotAct_max[$i]['POWact1_max'] = number_format($dat->POWact1_max, 2, '.', '');
                    $td_PotAct_max[$i]['POWact2_max'] = number_format($dat->POWact2_max, 2, '.', '');
                    $td_PotAct_max[$i]['POWact3_max'] = number_format($dat->POWact3_max, 2, '.', '');
                    $td_PotReac[$i]['POWrea1'] = number_format($dat->POWrea1, 2, '.', '');
                    $td_PotReac[$i]['POWrea2'] = number_format($dat->POWrea2, 2, '.', '');
                    $td_PotReac[$i]['POWrea3'] = number_format($dat->POWrea3, 2, '.', '');
                    $td_Intensidad[$i]['IAC1'] = number_format($dat->IAC1, 2, '.', '');
                    $td_Intensidad[$i]['IAC2'] = number_format($dat->IAC2, 2, '.', '');
                    $td_Intensidad[$i]['IAC3'] = number_format($dat->IAC3, 2, '.', '');
                    $td_FDP[$i]['PF1'] = number_format($dat->PF1, 2, '.', '');
                    $td_FDP[$i]['PF2'] = number_format($dat->PF2, 2, '.', '');
                    $td_FDP[$i]['PF3'] = number_format($dat->PF3, 2, '.', '');
                    $td_COSPHI[$i]['COSPHI'] = number_format($dat->COSPHI, 2, '.', '');
                  }else{
                    if (!isset($td_Frecuencia[$i]['FRE'])) {
                        $td_Frecuencia[$i]['FRE'] = null;
                        $td_PotAct_avg[$i]['POWact1_avg'] = null;
                        $td_PotAct_avg[$i]['POWact2_avg'] = null;
                        $td_PotAct_avg[$i]['POWact3_avg'] = null;
                        $td_PotAct_max[$i]['POWact1_max'] = null;
                        $td_PotAct_max[$i]['POWact2_max'] = null;
                        $td_PotAct_max[$i]['POWact3_max'] = null;
                        $td_PotReac[$i]['POWrea1'] = null;
                        $td_PotReac[$i]['POWrea2'] = null;
                        $td_PotReac[$i]['POWrea3'] = null;
                        $td_Intensidad[$i]['IAC1'] = null;
                        $td_Intensidad[$i]['IAC2'] = null;
                        $td_Intensidad[$i]['IAC3'] = null;
                        $td_FDP[$i]['PF1'] = null;
                        $td_FDP[$i]['PF2'] = null;
                        $td_FDP[$i]['PF3'] = null;
                        $td_COSPHI[$i]['COSPHI'] = null;
                      }
                    }
                  }
                }



              // $values_graficas_ = $values_graficas;
              // $values_corriente_ = $values_corriente;
              // \DB::raw('MONTH(date)')
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
              $values_graficas = $db->table('Potencia_Activa_Por_Fase')->select(\DB::raw("(CASE MONTH(date) WHEN '01' THEN CONCAT('Enero(',YEAR(date),')') WHEN '02' THEN CONCAT('Febrero(',YEAR(date),')') WHEN '03' THEN CONCAT('Marzo(',YEAR(date),')') WHEN '04' THEN CONCAT('Abril(',YEAR(date),')') WHEN '05' THEN CONCAT('Mayo(',YEAR(date),')') WHEN '06' THEN CONCAT('Junio(',YEAR(date),')') WHEN '07' THEN CONCAT('Julio(',YEAR(date),')') WHEN '08' THEN CONCAT('Agosto(',YEAR(date),')') WHEN '09' THEN CONCAT('Septiembre(',YEAR(date),')') WHEN '10' THEN CONCAT('Octubre(',YEAR(date),')') WHEN '11' THEN CONCAT('Noviembre(',YEAR(date),')') WHEN '12' THEN CONCAT('Diciembre(',YEAR(date),')') END) eje, DATE_FORMAT(time, '%H:%i') hora, MAX(`POWact1 (kW)`) potencia_activa_L1, MAX(`POWact2 (kW)`) potencia_activa_L2, MAX(`POWact3 (kW)`) potencia_activa_L3"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy(\DB::raw('MONTH(date)'))->orderBy(\DB::raw('MONTH(date)'),'ASC')->get();
              // \DB::raw('MONTH(date))'

              $values_corriente = $db->table('Corrientes_Por_Fase')->select(\DB::raw("(CASE MONTH(date) WHEN '01' THEN CONCAT('Enero(',YEAR(date),')') WHEN '02' THEN CONCAT('Febrero(',YEAR(date),')') WHEN '03' THEN CONCAT('Marzo(',YEAR(date),')') WHEN '04' THEN CONCAT('Abril(',YEAR(date),')') WHEN '05' THEN CONCAT('Mayo(',YEAR(date),')') WHEN '06' THEN CONCAT('Junio(',YEAR(date),')') WHEN '07' THEN CONCAT('Julio(',YEAR(date),')') WHEN '08' THEN CONCAT('Agosto(',YEAR(date),')') WHEN '09' THEN CONCAT('Septiembre(',YEAR(date),')') WHEN '10' THEN CONCAT('Octubre(',YEAR(date),')') WHEN '11' THEN CONCAT('Noviembre(',YEAR(date),')') WHEN '12' THEN CONCAT('Diciembre(',YEAR(date),')') END) eje, DATE_FORMAT(time, '%H:%i') hora, MAX(`IAC1 (A)`) corriente_L1, MAX(`IAC2 (A)`) corriente_L2, MAX(`IAC3 (A)`) corriente_L3"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy(\DB::raw('MONTH(date)'))->orderBy(\DB::raw('MONTH(date)'),'ASC')->get();
              $values_graficas_ = $values_graficas;
              $values_corriente_ = $values_corriente;


              $table_data = $db->table('Analizadores_Tipo')->select(\DB::raw("(CASE MONTH(date) WHEN '01' THEN CONCAT('Enero(',YEAR(date),')') WHEN '02' THEN CONCAT('Febrero(',YEAR(date),')') WHEN '03' THEN CONCAT('Marzo(',YEAR(date),')') WHEN '04' THEN CONCAT('Abril(',YEAR(date),')') WHEN '05' THEN CONCAT('Mayo(',YEAR(date),')') WHEN '06' THEN CONCAT('Junio(',YEAR(date),')') WHEN '07' THEN CONCAT('Julio(',YEAR(date),')') WHEN '08' THEN CONCAT('Agosto(',YEAR(date),')') WHEN '09' THEN CONCAT('Septiembre(',YEAR(date),')') WHEN '10' THEN CONCAT('Octubre(',YEAR(date),')') WHEN '11' THEN CONCAT('Noviembre(',YEAR(date),')') WHEN '12' THEN CONCAT('Diciembre(',YEAR(date),')') END) eje, avg(`FRE (Hz)`) FRE, avg(`COSPHI`) COSPHI, avg(`PF1`) PF1, avg(`PF2`) PF2, avg(`PF3`) PF3, avg(`IAC1 (A)`) IAC1, avg(`IAC2 (A)`) IAC2, avg(`IAC3 (A)`) IAC3, avg(`POWrea1 (kVAr)`) POWrea1, avg(`POWrea2 (kVAr)`) POWrea2, avg(`POWrea3 (kVAr)`) POWrea3, avg(`POWact1 (kW)`) POWact1_avg, avg(`POWact2 (kW)`) POWact2_avg, avg(`POWact3 (kW)`) POWact3_avg, max(`POWact1 (kW)`) POWact1_max, max(`POWact2 (kW)`) POWact2_max, max(`POWact3 (kW)`) POWact3_max"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy(\DB::raw('MONTH(date)'))->orderBy(\DB::raw('MONTH(date)'),'ASC')->get();

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


              for ($i=0; $i < 12; $i++){
                $td_aux[$i]['eje'] = $eje[$i];


                foreach ( $table_data as $dat ) {
                  if($eje[$i] == $dat->eje) {
                    $td_Frecuencia[$i]['FRE'] = number_format($dat->FRE, 2, '.', '');
                    $td_PotAct_avg[$i]['POWact1_avg'] = number_format($dat->POWact1_avg, 2, '.', '');
                    $td_PotAct_avg[$i]['POWact2_avg'] = number_format($dat->POWact2_avg, 2, '.', '');
                    $td_PotAct_avg[$i]['POWact3_avg'] = number_format($dat->POWact3_avg, 2, '.', '');
                    $td_PotAct_max[$i]['POWact1_max'] = number_format($dat->POWact1_max, 2, '.', '');
                    $td_PotAct_max[$i]['POWact2_max'] = number_format($dat->POWact2_max, 2, '.', '');
                    $td_PotAct_max[$i]['POWact3_max'] = number_format($dat->POWact3_max, 2, '.', '');
                    $td_PotReac[$i]['POWrea1'] = number_format($dat->POWrea1, 2, '.', '');
                    $td_PotReac[$i]['POWrea2'] = number_format($dat->POWrea2, 2, '.', '');
                    $td_PotReac[$i]['POWrea3'] = number_format($dat->POWrea3, 2, '.', '');
                    $td_Intensidad[$i]['IAC1'] = number_format($dat->IAC1, 2, '.', '');
                    $td_Intensidad[$i]['IAC2'] = number_format($dat->IAC2, 2, '.', '');
                    $td_Intensidad[$i]['IAC3'] = number_format($dat->IAC3, 2, '.', '');
                    $td_FDP[$i]['PF1'] = number_format($dat->PF1, 2, '.', '');
                    $td_FDP[$i]['PF2'] = number_format($dat->PF2, 2, '.', '');
                    $td_FDP[$i]['PF3'] = number_format($dat->PF3, 2, '.', '');
                    $td_COSPHI[$i]['COSPHI'] = number_format($dat->COSPHI, 2, '.', '');
                  }else{
                    if (!isset($td_Frecuencia[$i]['FRE'])) {
                        $td_Frecuencia[$i]['FRE'] = null;
                        $td_PotAct_avg[$i]['POWact1_avg'] = null;
                        $td_PotAct_avg[$i]['POWact2_avg'] = null;
                        $td_PotAct_avg[$i]['POWact3_avg'] = null;
                        $td_PotAct_max[$i]['POWact1_max'] = null;
                        $td_PotAct_max[$i]['POWact2_max'] = null;
                        $td_PotAct_max[$i]['POWact3_max'] = null;
                        $td_PotReac[$i]['POWrea1'] = null;
                        $td_PotReac[$i]['POWrea2'] = null;
                        $td_PotReac[$i]['POWrea3'] = null;
                        $td_Intensidad[$i]['IAC1'] = null;
                        $td_Intensidad[$i]['IAC2'] = null;
                        $td_Intensidad[$i]['IAC3'] = null;
                        $td_FDP[$i]['PF1'] = null;
                        $td_FDP[$i]['PF2'] = null;
                        $td_FDP[$i]['PF3'] = null;
                        $td_COSPHI[$i]['COSPHI'] = null;
                      }
                    }
                  }
                }

          break;

          case '9':

              $date_from = Session::get('_flash')['date_from_personalice'];
              $date_to = Session::get('_flash')['date_to_personalice'];
              $label_intervalo = 'Personalizado';

              $values_graficas = $db->table('Potencia_Activa_Por_Fase')->select(\DB::raw("date eje, MAX(`POWact1 (kW)`) potencia_activa_L1, MAX(`POWact2 (kW)`) potencia_activa_L2, MAX(`POWact3 (kW)`) potencia_activa_L3"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy('time')->get();

              $values_corriente = $db->table('Corrientes_Por_Fase')->select(\DB::raw("date eje, MAX(`IAC1 (A)`) corriente_L1, MAX(`IAC2 (A)`) corriente_L2, MAX(`IAC3 (A)`) corriente_L3"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy('time')->get();
              $values_graficas_ = $values_graficas;
              $values_corriente_ = $values_corriente;

              $table_data = $db->table('Analizadores_Tipo')->select(\DB::raw("`date` date, DATE_FORMAT(time, '%H:%i') time, `FRE (Hz)` FRE, `COSPHI` COSPHI, `PF1` PF1, `PF2` PF2, `PF3` PF3, `IAC1 (A)` IAC1, `IAC2 (A)` IAC2, `IAC3 (A)` IAC3, `POWrea1 (kVAr)` POWrea1, `POWrea2 (kVAr)` POWrea2, `POWrea3 (kVAr)` POWrea3, `POWact1 (kW)` POWact1_avg, `POWact2 (kW)` POWact2_avg, `POWact3 (kW)` POWact3_avg, `POWact1 (kW)` POWact1_max, `POWact2 (kW)` POWact2_max, `POWact3 (kW)` POWact3_max, `POWact_Total (kW)` POWact_total"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy('time')->orderBy('date')->orderBy('time')->get();

              foreach ( $table_data as $i=>$dat ) {
                    $td_aux[$i]['date'] = "$dat->date";
                    $td_aux[$i]['time'] = "$dat->time";
                    $td_PotAct_total[$i]['POWact_total'] = number_format($dat->POWact_total, 2, '.', '');
                    $td_Frecuencia[$i]['FRE'] = number_format($dat->FRE, 2, '.', '');
                    $td_PotAct_avg[$i]['POWact1_avg'] = number_format($dat->POWact1_avg, 2, '.', '');
                    $td_PotAct_avg[$i]['POWact2_avg'] = number_format($dat->POWact2_avg, 2, '.', '');
                    $td_PotAct_avg[$i]['POWact3_avg'] = number_format($dat->POWact3_avg, 2, '.', '');
                    $td_PotAct_max[$i]['POWact1_max'] = number_format($dat->POWact1_max, 2, '.', '');
                    $td_PotAct_max[$i]['POWact2_max'] = number_format($dat->POWact2_max, 2, '.', '');
                    $td_PotAct_max[$i]['POWact3_max'] = number_format($dat->POWact3_max, 2, '.', '');
                    $td_PotReac[$i]['POWrea1'] = number_format($dat->POWrea1, 2, '.', '');
                    $td_PotReac[$i]['POWrea2'] = number_format($dat->POWrea2, 2, '.', '');
                    $td_PotReac[$i]['POWrea3'] = number_format($dat->POWrea3, 2, '.', '');
                    $td_Intensidad[$i]['IAC1'] = number_format($dat->IAC1, 2, '.', '');
                    $td_Intensidad[$i]['IAC2'] = number_format($dat->IAC2, 2, '.', '');
                    $td_Intensidad[$i]['IAC3'] = number_format($dat->IAC3, 2, '.', '');
                    $td_FDP[$i]['PF1'] = number_format($dat->PF1, 2, '.', '');
                    $td_FDP[$i]['PF2'] = number_format($dat->PF2, 2, '.', '');
                    $td_FDP[$i]['PF3'] = number_format($dat->PF3, 2, '.', '');
                    $td_COSPHI[$i]['COSPHI'] = number_format($dat->COSPHI, 2, '.', '');
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

              $values_graficas = $db->table('Potencia_Activa_Por_Fase')->select(\DB::raw("DATE_FORMAT(time, '%H:%i') eje, MAX(`POWact1 (kW)`) potencia_activa_L1, MAX(`POWact2 (kW)`) potencia_activa_L2, MAX(`POWact3 (kW)`) potencia_activa_L3"))->where('date',$date_from)->groupBy('time')->get();

              $values_corriente = $db->table('Corrientes_Por_Fase')->select(\DB::raw("DATE_FORMAT(time, '%H:%i') eje, MAX(`IAC1 (A)`) corriente_L1, MAX(`IAC2 (A)`) corriente_L2, MAX(`IAC3 (A)`) corriente_L3"))->where('date',$date_from)->groupBy('time')->get();
              $values_graficas_ = $values_graficas;
              $values_corriente_ = $values_corriente;

              $table_data = $db->table('Analizadores_Tipo')->select(\DB::raw("DATE_FORMAT(time, '%H:%i') time, `FRE (Hz)` FRE, `COSPHI` COSPHI, `PF1` PF1, `PF2` PF2, `PF3` PF3, `IAC1 (A)` IAC1, `IAC2 (A)` IAC2, `IAC3 (A)` IAC3, `POWrea1 (kVAr)` POWrea1, `POWrea2 (kVAr)` POWrea2, `POWrea3 (kVAr)` POWrea3, `POWact1 (kW)` POWact1_avg, `POWact2 (kW)` POWact2_avg, `POWact3 (kW)` POWact3_avg, `POWact1 (kW)` POWact1_max, `POWact2 (kW)` POWact2_max, `POWact3 (kW)` POWact3_max, `POWact_Total (kW)` POWact_total"))->where('date','>=',$date_from)->where('date','<=',$date_to)->get();

            foreach ( $table_data as $i=>$dat ) {
                  $td_aux[$i]['time'] = "$dat->time";
                  $td_PotAct_total[$i]['POWact_total'] = number_format($dat->POWact_total, 2, '.', '');
                  $td_Frecuencia[$i]['FRE'] = number_format($dat->FRE, 2, '.', '');
                  $td_PotAct_avg[$i]['POWact1_avg'] = number_format($dat->POWact1_avg, 2, '.', '');
                  $td_PotAct_avg[$i]['POWact2_avg'] = number_format($dat->POWact2_avg, 2, '.', '');
                  $td_PotAct_avg[$i]['POWact3_avg'] = number_format($dat->POWact3_avg, 2, '.', '');
                  $td_PotAct_max[$i]['POWact1_max'] = number_format($dat->POWact1_max, 2, '.', '');
                  $td_PotAct_max[$i]['POWact2_max'] = number_format($dat->POWact2_max, 2, '.', '');
                  $td_PotAct_max[$i]['POWact3_max'] = number_format($dat->POWact3_max, 2, '.', '');
                  $td_PotReac[$i]['POWrea1'] = number_format($dat->POWrea1, 2, '.', '');
                  $td_PotReac[$i]['POWrea2'] = number_format($dat->POWrea2, 2, '.', '');
                  $td_PotReac[$i]['POWrea3'] = number_format($dat->POWrea3, 2, '.', '');
                  $td_Intensidad[$i]['IAC1'] = number_format($dat->IAC1, 2, '.', '');
                  $td_Intensidad[$i]['IAC2'] = number_format($dat->IAC2, 2, '.', '');
                  $td_Intensidad[$i]['IAC3'] = number_format($dat->IAC3, 2, '.', '');
                  $td_FDP[$i]['PF1'] = number_format($dat->PF1, 2, '.', '');
                  $td_FDP[$i]['PF2'] = number_format($dat->PF2, 2, '.', '');
                  $td_FDP[$i]['PF3'] = number_format($dat->PF3, 2, '.', '');
                  $td_COSPHI[$i]['COSPHI'] = number_format($dat->COSPHI, 2, '.', '');

              }

          break;
      }

      $table_data = $db->table('Analizadores_Tipo')->select(\DB::raw('`date` date, `time` time,`POWact1 (kW)` POWact1, `POWact2 (kW)` POWact2, `POWact3 (kW)` POWact3, `POWact_Total (kW)` POWact_Total, `POWapp1 (kVA)` POWapp1, `POWapp2 (kVA)` POWapp2, `POWapp3 (kVA)` POWapp3, `POWapa_Total (kVA)` POWapa_Total, `POWrea1 (kVAr)` POWrea1, `POWrea2 (kVAr)` POWrea2, `POWrea3 (kVAr)` POWrea3, `POWrea_Total (kVAr)` POWrea_Total, `PF1` PF1, `PF2` PF2, `PF3` PF3, `PF_Total` PF_Total, `FRE (Hz)` FRE, `IAC1 (A)` IAC1, `IAC2 (A)` IAC2 , `IAC3 (A)` IAC3, `VAC1 (V)` VAC1, `VAC2  (V)` VAC2, `VAC3  (V)` VAC3, `VAC  (V)` VAC, `COSPHI` COSPHI,`THDV1(%)` THDV1, `THDV2(%)` THDV2, `THDV3(%)` THDV3, `THDI1(%)` THDI1, `THDI2(%)` THDI2, `THDI3(%)` THDI3'))->where('date','>=',$date_from)->where('date','<=',$date_to)->get();

      $total_energias = $db->table('Analizadores_Tipo')->select(\DB::raw("SUM(`ENEact (kWh)`) energia_activa, SUM(`ENErea (kVArh)`) energia_reactiva"))->where('date','>=',$date_from)->where('date','<=',$date_to)->first();

      $datos_analizador_corriente = $db->table('Corrientes_Por_Fase')->select(\DB::raw("date, time, (`IAC1 (A)`) corriente_L1,
                                              (`IAC2 (A)`) corriente_L2, (`IAC3 (A)`) corriente_L3"))
                                          ->where('date','>=',$date_from)->where('date','<=',$date_to)->orderBy('date')->get();
      $datos_analizador_potencia = $db->table('Potencia_Activa_Por_Fase')->select(\DB::raw("date, time, (`POWact1 (kW)`) potencia_activa_L1, (`POWact2 (kW)`) potencia_activa_L2, (`POWact3 (kW)`) potencia_activa_L3"))->where('date','>=',$date_from)->where('date','<=',$date_to)->orderBy('date')->get();
      $total1 = 0; $total2 = 0; $total3 = 0; $total4 = 0; $total5 = 0; $total6 = 0;

      foreach ($datos_analizador_potencia as $value) {
          $total1 += $value->potencia_activa_L1;
          $value->potencia_activa_L1 = number_format($value->potencia_activa_L1,2,',','.');
          $total2 += $value->potencia_activa_L2;
          $value->potencia_activa_L2 = number_format($value->potencia_activa_L2,2,',','.');
          $total3 += $value->potencia_activa_L3;
          $value->potencia_activa_L3 = number_format($value->potencia_activa_L3,2,',','.');
      }

      foreach ($datos_analizador_corriente as $key) {
          $total4 += $key->corriente_L1;
          $key->corriente_L1 = number_format($key->corriente_L1,2,',','.');
          $total5 += $key->corriente_L2;
          $key->corriente_L2 = number_format($key->corriente_L2,2,',','.');
          $total6 += $key->corriente_L3;
          $key->corriente_L3 = number_format($key->corriente_L3,2,',','.');
      }


      if($db->table('information_schema.columns')->select(\DB::raw("column_name"))->whereRaw("table_name = 'Area_Cliente' AND column_name = '`LOGOTIPO`'")->first())
      {
          $dir_image_count = $db->table('Area_Cliente')->select(\DB::raw('`LOGOTIPO` logo'))->first()->logo;
      }

      $titulo = "Resumen Diario";
      $tipo_count = $aux_contador->tipo;
      $contador_label = $aux_contador->count_label;
      $contador_id = $aux_contador->id;
      $id = $user_id;
      if(($id != 0 && Auth::user()->id == $id) || Auth::user()->tipo == 1)
      {
          $user = User::where('id',$user_id)->get()->first();
          if(Auth::user()->tipo != 1)
              $ctrl = 0;
              else
                  $ctrl = 1;

                  if(is_null($user->_perfil))
                      $direccion = 'sin ubicación';
                      else
                          $direccion = $user->_perfil->direccion;

                          return view('analizadores.analizador_graficas',compact('user','titulo','id','ctrl','values_graficas','values_corriente','label_intervalo','date_from','date_to','direccion','tipo_count','contador_label','analizador','total_energias','datos_analizador_potencia','datos_analizador_corriente','total1','total2','total3','total4','total5','total6','color_etiqueta','contador_id','domicilio','dir_image_count','tipo_tarifa','values_corriente_','values_graficas_','eje','table_data', 'contador2','td_Frecuencia','td_COSPHI','td_FDP','td_Intensidad','td_PotReac','td_PotAct_avg','td_PotAct_max','td_aux'));
      }
      return \Redirect::to('https://submeter.es/');
      // return view('analizadores.analizador_graficas',compact('user','titulo','id','ctrl','values_graficas','values_corriente','label_intervalo','date_from','date_to','direccion','tipo_count','contador_label','analizador','total_energias','datos_analizador_potencia','datos_analizador_corriente','total1','total2','total3','total4','total5','total6','color_etiqueta','contador_id','domicilio','dir_image_count','tipo_tarifa'));
  }
  function exportCSVAnalizador(Request $request)
  {
      $analizador = Analizador::where('id',$request->analizador_id)->first();
      config(['database.connections.mysql2.host' => $analizador->host]);
      config(['database.connections.mysql2.port' => $analizador->port]);
      config(['database.connections.mysql2.database' => $analizador->database]);
      config(['database.connections.mysql2.username' => $analizador->username]);
      config(['database.connections.mysql2.password' => $analizador->password]);
      env('MYSQL2_HOST',$analizador->host);
      env('MYSQL2_DATABASE',$analizador->database);
      env('MYSQL2_USERNAME', $analizador->username);
      env('MYSQL2_PASSWORD',$analizador->password);
      try {
          \DB::connection('mysql2')->getPdo();
      } catch (\Exception $e) {
          Session::flash('message-error', "La base de datos a la que se desea conectar está mal configurada. Por favor, edite los parámetros de configuración de conexión.");
          return \Redirect::back();
      }
      $db = \DB::connection('mysql2');

      $table_data = $db->table('Analizadores_Tipo')->select(\DB::raw('`date` date, `time` time,`ENEact (kWh)` ENEact,`ENEapa (kVAh)` ENEapa, `ENErea_Ind (kVArh)` ENErea_Ind,`ENErea_Cap (kVArh)` ENErea_Cap,`ENErea (kVArh)` ENErea,`POWact1 (kW)` POWact1, `POWact2 (kW)` POWact2, `POWact3 (kW)` POWact3, `POWact_Total (kW)` POWact_Total, `POWapp1 (kVA)` POWapp1, `POWapp2 (kVA)` POWapp2, `POWapp3 (kVA)` POWapp3, `POWapa_Total (kVA)` POWapa_Total, `POWrea1 (kVAr)` POWrea1, `POWrea2 (kVAr)` POWrea2, `POWrea3 (kVAr)` POWrea3, `POWrea_Total (kVAr)` POWrea_Total, `PF1` PF1, `PF2` PF2, `PF3` PF3, `PF_Total` PF_Total, `FRE (Hz)` FRE, `IAC1 (A)` IAC1, `IAC2 (A)` IAC2 , `IAC3 (A)` IAC3, `VAC1 (V)` VAC1, `VAC2  (V)` VAC2, `VAC3  (V)` VAC3, `VAC  (V)` VAC, `COSPHI` COSPHI,`THDV1(%)` THDV1, `THDV2(%)` THDV2, `THDV3(%)` THDV3, `THDI1(%)` THDI1, `THDI2(%)` THDI2, `THDI3(%)` THDI3'))->where('date','>=',$request->date_from)->where('date','<=',$request->date_to)->orderBy('date')->orderBy('time')->get();

      $filename = "Datos_".$analizador->label.".csv";
      $handle = fopen($filename, 'w+');
      fputcsv($handle, array('Fecha', 'Tiempo', 'ENEact (kWh)', 'ENEapa (kVAh)', 'ENErea_Ind (kVArh)', 'ENErea_Cap (kVArh)', 'ENErea (kVArh)', 'POWact1 (kW)', 'POWact2 (kW)','POWact3 (kW)','POWact_Trifasico (kW)','POWapp1 (kVA)','POWapp2 (kVA)','POWapp3 (kVA)','POWapp_Trifasico (kVA)','POWrea1 (kVAr)','POWrea2 (kVAr)','POWrea3 (kVAr)','POWrea_Trifasico (kVAr)','PF1','PF2','PF3','PF_Trifasico','FRE (Hz)','IAC1 (A)','IAC2 (A)','IAC3 (A)','VAC1 (V)','VAC2 (V)','VAC3 (V)','VAC_Trifasico (V)','COSPHI','THDV1(%)','THDV2(%)','THDV3(%)','THDI1(%)','THDI2(%)','THDI3(%)'),';');
      $i = 0;
      foreach($table_data as $data) {
          fputcsv($handle, array(
            $data->date, $data->time,
          number_format($data->ENEact,3,',','.'),
          number_format($data->ENEapa,3,',','.'),
          number_format($data->ENErea_Ind,3,',','.'),
          number_format($data->ENErea_Cap,3,',','.'),
          number_format($data->ENErea,3,',','.'),
          number_format($data->POWact1,3,',','.'),
          number_format($data->POWact2,3,',','.'),
          number_format($data->POWact3,3,',','.'),
          number_format($data->POWact_Total,3,',','.'),
          number_format($data->POWapp1,3,',','.'),
          number_format($data->POWapp2,3,',','.'),
          number_format($data->POWapp3,3,',','.'),
          number_format($data->POWapa_Total,3,',','.'),
          number_format($data->POWrea1,3,',','.'),
          number_format($data->POWrea2,3,',','.'),
          number_format($data->POWrea3,3,',','.'),
          number_format($data->POWrea_Total,3,',','.'),
          number_format($data->PF1,3,',','.'),
          number_format($data->PF2,3,',','.'),
          number_format($data->PF3,3,',','.'),
          number_format($data->PF_Total,3,',','.'),
          number_format($data->FRE,3,',','.'),
          number_format($data->IAC1,3,',','.'),
          number_format($data->IAC2,3,',','.'),
          number_format($data->IAC3,3,',','.'),
          number_format($data->VAC1,3,',','.'),
          number_format($data->VAC2,3,',','.'),
          number_format($data->VAC3,3,',','.'),
          number_format($data->VAC,3,',','.'),
          number_format($data->COSPHI,3,',','.'),
          number_format($data->THDV1,3,',','.'),
          number_format($data->THDV2,3,',','.'),
          number_format($data->THDV3,3,',','.'),
          number_format($data->THDI1,3,',','.'),
          number_format($data->THDI2,3,',','.'),
          number_format($data->THDI3,3,',','.')
        ),';');

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
