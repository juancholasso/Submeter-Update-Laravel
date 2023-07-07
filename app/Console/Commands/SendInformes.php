<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Informes;
use App\Mail\SendMailable;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendEmailJob;
use App\EnergyMeter;
use App\User;
use Session;
use Auth;

use Carbon\Carbon;

class SendInformes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:informes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Programacion diaria';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // $emailJob = (new SendEmailJob());
        // dispatch($emailJob);
        // Mail::to('submeter@submeter.email','Submeter 4.0 (Informes Programados)')->send(new SendMailable());

        $historicalComparison = null;

        $minut = Informes::where('check',1)->get();
        if(!is_null($minut))
        {
            $informes = "diario";
            foreach ($minut as $key) {
                $mails = explode(';', $key['emails']);

                $contador2 = EnergyMeter::where('id',$key->meter_id)->first();
                // if($key->contador == 'Avda Carlos III 6P')
                //     dd($contador2);
                // if(is_null($contador2))
                //     dd($key->contador);

                $user = User::where('id',$key->user_id)->first();
                foreach ($mails as $value)
                {                            
                    $tipo_count = $contador2->tipo;
                    $eje = array();
                    $consumo_activa = array();
                    $consumo_activa_diaria = array();
                    $consumo_capacitiva = array();
                    $consumo_inductiva = array();
                    $eje_analisis = array();
                    $eje_consu = array();
                    $p_demandada = array();
                    $p_contratada_analisis = array();
                    $emisiones = array();
                    $Energia_Act = array();
                    $Energia_Reac_Induc = array();
                    $Energia_Reac_Cap = array();
                    $periodos2 = array();
                    $db_EAct = array();
                    $EAct = array();
                    $db_p_contratada = array();
                    $p_contratada = array();
                    $periodos_coste = array();

                    $db_Generacion = array();
                    $generacion = array();

                    $consumo_GN_kWh = 0;
                    $consumo_GN_Nm3 = 0;
                    $caudal_contratado = 0;
                    $caudal_medio_consumido = 0;
                    $caudal_medio_consumido2 = 0;
                    $caudal_maximo_consumido = array();
                    $presion_suministro = 0;
                    $emisiones_gas = 0;
                    $caudal_maximo = 0;
                    $posicion = 0;
                    $tipo_tarifa = '';

                    config(['database.connections.mysql2.host' => $contador2->host]);
                    config(['database.connections.mysql2.port' => $contador2->port]);
                    config(['database.connections.mysql2.database' => $contador2->database]);
                    config(['database.connections.mysql2.username' => $contador2->username]);
                    config(['database.connections.mysql2.password' => $contador2->password]);
                    env('MYSQL2_HOST',$contador2->host);
                    env('MYSQL2_DATABASE',$contador2->database);
                    env('MYSQL2_USERNAME', $contador2->username);
                    env('MYSQL2_PASSWORD',$contador2->password);
                    $tipo_tarifa = $contador2->tarifa;
                    try {
                        \DB::connection('mysql2')->getPdo();
                    } catch (\Exception $e) {
                        //Enviar correo del problema generado
                        // Mail::to($value,'Submeter 4.0 (Informes Programados)')->send(new SendMailable());
                    }
                    
                    $db = \DB::connection('mysql2');

                    $date_from = \Carbon\Carbon::yesterday()->toDateString();
                    $date_to = $date_from;
                    $potencia_optima = array();

                    if($tipo_count < 3)
                        $aux_periodos = $db->table('Potencia_Contratada')->select(\DB::raw("COUNT(*) cont"))->groupBy('Periodo')->get()->toArray();
                    else
                        $aux_periodos = array();

                    if($db->table('information_schema.columns')->select(\DB::raw("column_name"))->whereRaw("table_name = 'Potencia_Contratada_Optima' AND column_name = 'Potencia_contratada'")->first())
                    {
                        $potencia_optima = $db->table('Potencia_Contratada_Optima')->select(\DB::raw("Periodo eje,`Potencia_contratada` p_optima"))->orderBy('Periodo')->get();
                        //dd($potencia_optima);
                    }else{
                        $potencia_optima[0]['p_optima'] = 0;
                        $potencia_optima[1]['p_optima'] = 0;
                        $potencia_optima[2]['p_optima'] = 0;
                        $potencia_optima[3]['p_optima'] = 0;
                        $potencia_optima[4]['p_optima'] = 0;
                        $potencia_optima[5]['p_optima'] = 0;
                    }

                    if($tipo_count < 3)
                    {
                        if((int)$tipo_count === 1){
                            $historicalComparison = $this->getHistoricalComparisonConsumption($db);
                        }else{
                            $historicalComparison = $this->getHistoricalComparisonConsumptionAndGeneration($db);
                        } 
                        
                        if($tipo_tarifa == 1)
                        {
                            $consumo_diario_energia = $db->table('Consumo_Diario_Energia')->select(\DB::raw("Hora eje,SUM(`Energia Activa (kWh)`) activa, SUM(`Energia Reactiva Inductiva (kVArh)`) inductiva, SUM(`Energia Reactiva Capacitiva (kVArh)`) capacitiva"))->where('date',$date_from)->groupBy('Hora')->get();

                            $consumo_energia_activa = $db->table('Consumo_Energia_Activa')->select(\DB::raw("DATE_FORMAT(time, '%H:%i') eje,SUM(`Energia Activa (kWh)`) activa"))->where('date',$date_from)->groupBy('time')->get();

                            $db_Generacion = $db->table('Generacion_Energia')->select(\DB::raw("DATE_FORMAT(time, '%H:%i') eje,SUM(`Generacion Energia`) generacion_energia"))->where('date',$date_from)->groupBy('time')->get();

                            $emisiones = $db->table('Emisiones_CO2')->select(\DB::raw("DATE_FORMAT(time, '%H:%i') eje, SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones"))->where('date',$date_from)->groupBy('time')->get();

                            $potencia_demandada = $db->table('Analisis_Potencia')->select(\DB::raw("DATE_FORMAT(time, '%H:%i') eje,MAX(`Potencia Demandada (kW)`) demandada"))->where('date',$date_from)->groupBy('time')->get();
                            foreach ($potencia_demandada as $consu) {
                                $eje_analisis[] = $consu->eje;
                                $p_demandada[] = $consu->demandada;
                            }

                            $potencia_contratada = $db->table('Analisis_Potencia')->select(\DB::raw("DATE_FORMAT(time, '%H:%i') eje,MAX(`Potencia Contratada (kW)`) contratada"))->where('date',$date_from)->groupBy('time')->get();
                            foreach ($potencia_contratada as $consu) {
                                $p_contratada_analisis[] = $consu->contratada;
                            }

                            foreach ($consumo_diario_energia as $consu) {
                                $eje[] = $consu->eje;
                                $consumo_activa_diaria[] = $consu->activa;
                                $consumo_inductiva[] = $consu->inductiva;
                                $consumo_capacitiva[] = $consu->capacitiva;
                            }
                            if(!empty($consumo_energia_activa))
                            {
                                foreach ($consumo_energia_activa as $consu) {
                                $eje_consu[] = $consu->eje;
                                $consumo_activa[] = $consu->activa;
                                }
                            }else{
                                $eje_consu = array();
                            }
                            // }else{
                            $balance = $db->table('Balance_Neto_Diario')->select(\DB::raw("time eje,SUM(`Consumo Energia (kWh)`) consumo_energia, SUM(`Generacion Energia (kWh)`) generacion_energia, SUM(`Balance Neto (kWh)`) balance_neto"))->where('date',$date_from)->groupBy('time')->get();

                            $periodos2 = array();
                            $db_EAct = array();
                            $EAct = array();
                            $db_p_contratada = array();
                            $p_contratada = array();
                            $periodos_coste = array();

                            // SE CREAN ARRAYS CON LOS PERÍODOS DISPONIBLES EN LA COMPAÑÍA
                            for ($i=1; $i < 7 ; $i++) {
                                $periodos2[] = 'P'.$i;
                                $periodos_coste[] = 'P'.$i;
                            }
                            // SE UNE AL ARRAY DE PERÍODOS DE LA COMPAÑÍA, LA OPCIÓN DE TOTAL
                            array_push($periodos_coste, "Total");

                            $MES = $db->table('Datos_Contador')->select(\DB::raw("MONTH(date) as MES"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy(\DB::raw('MONTH(date)'))->get()->toArray();
                            $total = 0;

                            if(!empty($MES))
                            {
                                $k = 0;
                                foreach ($MES as $mes) {
                                    foreach ($periodos2 as $p) {
                                        // SELECCIONA LA ENERGÍA ACTIVA MÁXIMA CONSUMIDA EN EL PERÍODO SELECCIONADO
                                        if($tipo_count == 1)
                                        {
                                            $db_EAct[] = $db->table('Potencia_Demandada_Contratada')->select(\DB::raw("MAX(`Potencia Demandada (kW)`) as prom"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy('Periodo')->get()->toArray();
                                            if(is_null($db_EAct[$k][0]->prom))
                                            {
                                                $db_EAct[$k][0]->prom = 0;
                                            }
                                            break;
                                        }
                                        $k++;
                                    }
                                }
                            }
                            $db_coste_potencia = $db->table('Coste_Potencia_Contratada')->select(\DB::raw("SUM(P1) costeP1, SUM(P2) costeP2, SUM(P3) costeP3, SUM(P4) costeP4, SUM(P5) costeP5, SUM(P6) costeP6"))->where('date','>=',$date_from)->where('date','<=',$date_to)->get()->toArray();
                            $index = 0;
                            foreach ($db_coste_potencia as $coste_poten) {
                                $aux_index = 'costeP';
                                $aux_coste_potencia[$index][$aux_index.($index+1)] = $coste_poten->costeP1;
                                $aux_coste_potencia[$index][$aux_index.($index+2)] = $coste_poten->costeP2;
                                $aux_coste_potencia[$index][$aux_index.($index+3)] = $coste_poten->costeP3;
                                $aux_coste_potencia[$index][$aux_index.($index+4)] = $coste_poten->costeP4;
                                $aux_coste_potencia[$index][$aux_index.($index+5)] = $coste_poten->costeP5;
                                $aux_coste_potencia[$index][$aux_index.($index+6)] = $coste_poten->costeP6;
                                $index++;
                            }
                            $db_excesos = $db->table('Coste_Exceso_Potencia')->select(\DB::raw("SUM(P1) costeP1, SUM(P2) costeP2, SUM(P3) costeP3, SUM(P4) costeP4, SUM(P5) costeP5, SUM(P6) costeP6"))->where('date','>=',$date_from)->where('date','<=',$date_to)->get()->toArray();
                            $index = 0;
                            foreach ($db_excesos as $excesos) {
                                $aux_index = 'costeP';
                                $aux_excesos[$index][$aux_index.($index+1)] = $excesos->costeP1;
                                $aux_excesos[$index][$aux_index.($index+2)] = $excesos->costeP2;
                                $aux_excesos[$index][$aux_index.($index+3)] = $excesos->costeP3;
                                $aux_excesos[$index][$aux_index.($index+4)] = $excesos->costeP4;
                                $aux_excesos[$index][$aux_index.($index+5)] = $excesos->costeP5;
                                $aux_excesos[$index][$aux_index.($index+6)] = $excesos->costeP6;
                                $index++;
                            }

                            $db_coste_termino_energia = $db->table('Coste_Termino_Energia')->select(\DB::raw("SUM(P1) costeP1, SUM(P2) costeP2, SUM(P3) costeP3, SUM(P4) costeP4, SUM(P5) costeP5, SUM(P6) costeP6"))->where('date','>=',$date_from)->where('date','<=',$date_to)->get()->toArray();
                            $index = 0;
                            foreach ($db_coste_termino_energia as $energia) {
                                $aux_index = 'costeP';
                                $aux_energia[$index][$aux_index.($index+1)] = $energia->costeP1;
                                $aux_energia[$index][$aux_index.($index+2)] = $energia->costeP2;
                                $aux_energia[$index][$aux_index.($index+3)] = $energia->costeP3;
                                $aux_energia[$index][$aux_index.($index+4)] = $energia->costeP4;
                                $aux_energia[$index][$aux_index.($index+5)] = $energia->costeP5;
                                $aux_energia[$index][$aux_index.($index+6)] = $energia->costeP6;
                                $index++;
                            }

                            $db_p_contratada[] = $db->table('Potencia_Contratada')->select(\DB::raw("Potencia_contratada p_contratada"))->groupBy('Periodo')->get()->toArray();

                            // INICIALIZA EL VECTOR DONDE SE ALMACENARÁ EL COSTO DE POTENCIA
                            // DE ACUERDO AL INTERVALO SELECCIONADO
                            $flag_aux = 0;
                            $aux = array();
                            for ($i=0; $i < 6; $i++) {
                                $coste_potencia[] = 0;
                                $coste_termino_energia[] = 0;
                                $aux[] = 0;
                            }
                            if($tipo_count == 1)
                            {
                                for ($j=0; $j < count($aux_periodos); $j++)
                                {
                                    if(isset($db_EAct[0][$j]))
                                    {
                                        if($aux[$j] <= $db_EAct[0][$j]->prom)
                                        {
                                            $aux[$j] = $db_EAct[0][$j];
                                            $flag_aux = 1;
                                        }
                                    }
                                }
                            }
                            $P = array();
                            // ALMACENA LA SUMATORIA DE LOS COSTOS DE POTENCIA DE ACUERDO A CADA
                            // PERÍODO DENTRO DEL INTERVALO SELECCIONADO
                            $total = 0;
                            $total2 = 0;

                            $i = 0;
                            for ($i=0; $i < 6; $i++)
                            {
                                $aux_index = 'costeP'.($i+1);
                                if(!empty($db_coste_potencia) && (isset($aux_coste_potencia[0][$aux_index]) && isset($aux_excesos[0][$aux_index])) )
                                {
                                    $coste_potencia[$i%6] = $coste_potencia[$i%6] + $aux_coste_potencia[0][$aux_index] + $aux_excesos[0][$aux_index];
                                    $total = $aux_coste_potencia[0][$aux_index] + $total + $aux_excesos[0][$aux_index];
                                }else{
                                    $coste_potencia[$i] = 0;
                                    $total = 0;
                                }

                                if(!empty($aux_energia) && isset($aux_energia[0][$aux_index]))
                                {
                                    $coste_termino_energia[$i%6] = $coste_termino_energia[$i%6] + $aux_energia[0][$aux_index];
                                    $total2 = $aux_energia[0][$aux_index] + $total2;
                                }else{
                                    $coste_termino_energia[$i] = 0;
                                    $total2 = 0;
                                }

                                if(!empty($db_p_contratada) && isset($db_p_contratada[0][$i]))
                                {
                                    $p_contratada[] = $db_p_contratada[0][$i]->p_contratada;
                                }else{
                                    $p_contratada[$i] = 0;
                                }
                            }

                            array_push($coste_potencia, $total);
                            array_push($coste_termino_energia, $total2);

                            $j = 0;
                            if($flag_aux == 1)
                            {
                                if($tipo_count == 1)
                                {
                                    foreach ($aux as $prom_EAct) {
                                        if(is_null($prom_EAct->prom))
                                        {
                                            $EAct[] = 0;
                                            continue;
                                        }else{
                                            $EAct[$j] = $prom_EAct->prom;
                                        }
                                        $i++;
                                        $j++;
                                    }
                                }
                            }else{
                                for ($i=0; $i < 6; $i++)
                                {
                                    $EAct[] = 0;
                                }
                            }

                            // ******************************************************************
                            // Calculo de la gráfica para Energía consumida Activa y Reactiva
                            // ******************************************************************

                            $Energia_Act_Reac_Consu = array();
                            $db_Ener_Consu_Acti_Reacti = array();

                            $db_Ener_Consu_Acti_Reacti[] = $db->table('Energia_Consumida_Activa_y_Reactiva')->select(\DB::raw("`Periodo`,SUM(`Energia Activa (kWh)`) E_Activa, SUM(`Energia Reactiva Inductiva (kVArh)`) E_Reac_Induc, SUM(`Energia Reactiva Capacitiva (kVArh)`) E_Reac_Cap"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy('Periodo')->get()->toArray();

                            $db_Venta_Energia = $db->table('Venta_Energia_Activa')->select(\DB::raw("SUM(P1) ventaP1, SUM(P2) ventaP2, SUM(P3) ventaP3, SUM(P4) ventaP4, SUM(P5) ventaP5, SUM(P6) ventaP6"))->where('date','>=',$date_from)->where('date','<=',$date_to)->get()->toArray();

                            $generacion = $db->table('Generacion_Energia_Activa_y_Reactiva')->select(\DB::raw("Periodo,SUM(`Generación Energia Activa (kWh)`) generacion_energia"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy('Periodo')->get()->toArray();
                            //dd($generacion);

                            $total_ventas = 0;
                            $h = 0;
                            foreach ($db_Venta_Energia[0] as $ventas) {
                                $total_ventas = $total_ventas + $ventas;
                                $h++;
                            }

                            if(empty($db_Ener_Consu_Acti_Reacti[0])){
                                for ($i=0; $i < 6; $i++) {
                                    $Energia_Act[$i] = 0;
                                    $Energia_Reac_Induc[$i] = 0;
                                    $Energia_Reac_Cap[$i] = 0;
                                }
                            }else
                            {
                                foreach ($db_Ener_Consu_Acti_Reacti[0] as $it) {
                                    $Energia_Act[] = $it->E_Activa;
                                    $Energia_Reac_Induc[] = $it->E_Reac_Induc;
                                    $Energia_Reac_Cap[] = $it->E_Reac_Cap;
                                }
                            }
                            // dd($Energia_Act, $Energia_Reac_Induc, $Energia_Reac_Cap );
                            // if($key->contador == 'Avda Carlos III 6P')
                            // if($value == 'miguel.pena@3seficiencia.com')

                            $label_intervalo =  "Fecha: ".$date_from."";

                            Mail::to($value,'Submeter 4.0 (Informes Programados)')->send(new SendMailable($user,$contador2,$tipo_count,$periodos2,$EAct,$p_contratada,$Energia_Act,$Energia_Reac_Cap,$Energia_Reac_Induc,$eje,$consumo_activa_diaria,$consumo_capacitiva,$consumo_inductiva,$db_Generacion,$eje_consu,$consumo_activa,$eje_analisis,$p_demandada,$p_contratada_analisis,$emisiones,$generacion,$informes, $consumo_GN_kWh, $consumo_GN_Nm3,$caudal_contratado,$caudal_medio_consumido, $caudal_maximo_consumido, $presion_suministro, $emisiones_gas,$caudal_maximo,$posicion,$caudal_medio_consumido2,$potencia_optima,$label_intervalo, $historicalComparison));
                        }else{
                            $index = 0;
                            for ($i=1; $i < 4 ; $i++) {
                                $periodos2[] = 'P'.$i;
                                $periodos_coste[] = 'P'.$i;
                            }

                            $consumo_diario_energia = $db->table('Consumo_Diario_Energia')->select(\DB::raw("Hora eje,SUM(`Energia Activa (kWh)`) activa, SUM(`Energia Reactiva Inductiva (kVArh)`) inductiva, SUM(`Energia Reactiva Capacitiva (kVArh)`) capacitiva"))->where('date',$date_from)->groupBy('Hora')->get();

                            $consumo_energia_activa = $db->table('Consumo_Energia_Activa')->select(\DB::raw("DATE_FORMAT(time, '%H:%i') eje,SUM(`Energia Activa (kWh)`) activa"))->where('date',$date_from)->groupBy('time')->get();

                            $emisiones = $db->table('Emisiones_CO2')->select(\DB::raw("DATE_FORMAT(time, '%H:%i') eje, SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones"))->where('date',$date_from)->groupBy('time')->get();

                            $potencia_demandada = $db->table('Analisis_Potencia')->select(\DB::raw("DATE_FORMAT(time, '%H:%i') eje,MAX(`Potencia Demandada (kW)`) demandada"))->where('date',$date_from)->groupBy('time')->get();

                            foreach ($potencia_demandada as $consu) {
                                $eje_analisis[] = $consu->eje;
                                $p_demandada[] = $consu->demandada;
                            }

                            $potencia_contratada = $db->table('Analisis_Potencia')->select(\DB::raw("DATE_FORMAT(time, '%H:%i') eje,MAX(`Potencia Contratada (kW)`) contratada"))->where('date',$date_from)->groupBy('time')->get();

                            foreach ($potencia_contratada as $consu) {
                                $p_contratada_analisis[] = $consu->contratada;
                            }

                            foreach ($consumo_diario_energia as $consu) {
                                $eje[] = $consu->eje;
                                $consumo_activa_diaria[] = $consu->activa;
                                $consumo_inductiva[] = $consu->inductiva;
                                $consumo_capacitiva[] = $consu->capacitiva;
                            }

                            if(!empty($consumo_energia_activa))
                            {
                                foreach ($consumo_energia_activa as $consu) {
                                $eje_consu[] = $consu->eje;
                                $consumo_activa[] = $consu->activa;
                                }
                            }else{
                                $eje_consu = array();
                            }

                            $potencia_85_contratada = $db->table('Analisis_Potencia')->select(\DB::raw("DATE_FORMAT(time, '%H:%i') eje, MAX(`85% Potencia Contatratada (KW)`) contratada_ochenta"))->where('date',$date_from)->groupBy('time')->get();
                            foreach ($potencia_85_contratada as $potencia)
                            {
                                $p_85_contratada[] = $potencia->contratada_ochenta;
                            }

                            $potencia_105_contratada = $db->table('Analisis_Potencia')->select(\DB::raw("DATE_FORMAT(time, '%H:%i') eje, MAX(`105% Potencia Contatada (KW)`) contratada_ciento"))->where('date',$date_from)->groupBy('time')->get();
                            foreach ($potencia_105_contratada as $potencia)
                            {
                                $p_105_contratada[] = $potencia->contratada_ciento;
                            }

                            $db_EAct[] = $db->table('Potencia_Demandada_Contratada')->select(\DB::raw("MAX(`Potencia Demandada (kW)`) as prom"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy('Periodo')->get()->toArray();
                            $db_coste_potencia = $db->table('ZPI_Potencia_Maxima_Dia')->select(\DB::raw("(MAX(CASE when (`Potencia_Contratada` * 0.85) > `Potencia_Maxima` AND `Periodo`='P1' then (`Potencia_Contratada` * 0.85) * `Precio`*12/365 when (`Potencia_Contratada` * 0.85) <= `Potencia_Maxima` and (`Potencia_Contratada` * 1.05) >= `Potencia_Maxima` AND `Periodo`='P1' then `Potencia_Maxima` * `Precio`*12/365 when (`Potencia_Contratada` * 1.05) < `Potencia_Maxima` AND `Periodo`='P1' then ((((`Potencia_Maxima` - (`Potencia_Contratada` * 1.05)) * 2) + `Potencia_Maxima`) * `Precio`*12/365) ELSE '0' END))*COUNT(DISTINCT`date`) AS 'costeP1', (MAX(CASE when (`Potencia_Contratada` * 0.85) > `Potencia_Maxima` AND `Periodo`='P2' then (`Potencia_Contratada` * 0.85) * `Precio`*12/365 when (`Potencia_Contratada` * 0.85) <= `Potencia_Maxima` and (`Potencia_Contratada` * 1.05) >= `Potencia_Maxima` AND `Periodo`='P2' then `Potencia_Maxima` * `Precio`*12/365 when (`Potencia_Contratada` * 1.05) < `Potencia_Maxima` AND `Periodo`='P2' then ((((`Potencia_Maxima` - (`Potencia_Contratada` * 1.05)) * 2) + `Potencia_Maxima`) * `Precio`*12/365) ELSE '0' END))*COUNT(DISTINCT`date`) AS 'costeP2', (MAX(CASE when (`Potencia_Contratada` * 0.85) > `Potencia_Maxima` AND `Periodo`='P3' then (`Potencia_Contratada` * 0.85) * `Precio`*12/365 when (`Potencia_Contratada` * 0.85) <= `Potencia_Maxima` and (`Potencia_Contratada` * 1.05) >= `Potencia_Maxima` AND `Periodo`='P3' then `Potencia_Maxima` * `Precio`*12/365 when (`Potencia_Contratada` * 1.05) < `Potencia_Maxima` AND `Periodo`='P3' then ((((`Potencia_Maxima` - (`Potencia_Contratada` * 1.05)) * 2) + `Potencia_Maxima`) * `Precio`*12/365) ELSE '0' END))*COUNT(DISTINCT`date`) AS 'costeP3'"))->where('date','>=',$date_from)->where('date','<=',$date_to)->get()->toArray();
                            foreach ($db_coste_potencia as $coste_poten) {
                                $aux_index = 'costeP';
                                $aux_coste_potencia[$index][$aux_index.($index+1)] = $coste_poten->costeP1*1;
                                $aux_coste_potencia[$index][$aux_index.($index+2)] = $coste_poten->costeP2*1;
                                $aux_coste_potencia[$index][$aux_index.($index+3)] = $coste_poten->costeP3*1;
                                $index++;
                            }
                            $db_coste_termino_energia = $db->table('Coste_Termino_Energia')->select(\DB::raw("SUM(P1) costeP1, SUM(P2) costeP2, SUM(P3) costeP3"))->where('date','>=',$date_from)->where('date','<=',$date_to)->get()->toArray();
                            foreach ($db_coste_termino_energia as $energia)
                            {
                                $aux_index = 'costeP';
                                $aux_energia[$index][$aux_index.($index+1)] = $energia->costeP1;
                                $aux_energia[$index][$aux_index.($index+2)] = $energia->costeP2;
                                $aux_energia[$index][$aux_index.($index+3)] = $energia->costeP3;
                                $index++;
                            }

                            $db_p_contratada[] = $db->table('Potencia_Contratada')->select(\DB::raw("Potencia_contratada p_contratada"))->groupBy('Periodo')->get()->toArray();

                            // INICIALIZA EL VECTOR DONDE SE ALMACENARÁ EL COSTO DE POTENCIA
                            // DE ACUERDO AL INTERVALO SELECCIONADO
                            $flag_aux = 0;
                            $aux = array();
                            for ($i=0; $i < count($aux_periodos); $i++) {
                                $coste_potencia[] = 0;
                                $coste_termino_energia[] = 0;
                                $aux[] = 0;
                            }

                            for ($j=0; $j < count($aux_periodos); $j++)
                            {
                                if(isset($db_EAct[0][$j]))
                                {
                                        // dd(intval($db_EAct[0][$j]->prom));
                                    if($aux[$j] <= intval($db_EAct[0][$j]->prom))
                                    {
                                        $aux[$j] = $db_EAct[0][$j];
                                        $flag_aux = 1;
                                    }
                                }
                            }

                            $P = array();
                            // ALMACENA LA SUMATORIA DE LOS COSTOS DE POTENCIA DE ACUERDO A CADA
                            // PERÍODO DENTRO DEL INTERVALO SELECCIONADO
                            $total = 0;
                            $total2 = 0;
                            // dd($db_coste_potencia, $db_excesos);
                            $i = 0;
                            for ($i=0; $i < count($aux_periodos); $i++)
                            {
                                $aux_index = 'costeP'.($i+1);
                                if(!empty($db_coste_potencia) && (isset($aux_coste_potencia[0][$aux_index]) && isset($aux_excesos[0][$aux_index])) )
                                {
                                    $coste_potencia[$i%count($aux_periodos)] = $coste_potencia[$i%count($aux_periodos)] + $aux_coste_potencia[0][$aux_index] + $aux_excesos[0][$aux_index];
                                    $total = $aux_coste_potencia[0][$aux_index] + $total + $aux_excesos[0][$aux_index];
                                }elseif(!empty($db_coste_potencia) && isset($aux_coste_potencia[0][$aux_index])){
                                    $coste_potencia[$i%count($aux_periodos)] = $coste_potencia[$i%count($aux_periodos)] + $aux_coste_potencia[0][$aux_index];
                                    $total = $aux_coste_potencia[0][$aux_index] + $total;
                                }else{
                                    $coste_potencia[$i] = 0;
                                    $total = 0;
                                }

                                if(!empty($aux_energia) && isset($aux_energia[0][$aux_index]))
                                {
                                    $coste_termino_energia[$i%count($aux_periodos)] = $coste_termino_energia[$i%count($aux_periodos)] + $aux_energia[0][$aux_index];
                                    $total2 = $aux_energia[0][$aux_index] + $total2;
                                }else{
                                    $coste_termino_energia[$i] = 0;
                                    $total2 = 0;
                                }

                                if(!empty($db_p_contratada) && isset($db_p_contratada[0][$i]))
                                {
                                    $p_contratada[] = $db_p_contratada[0][$i]->p_contratada;
                                }else{
                                    $p_contratada[$i] = 0;
                                }
                            }

                            array_push($coste_potencia, $total);
                            array_push($coste_termino_energia, $total2);
                            // if(Auth::user()->id == 18)
                            //     dd($date_from, $date_to,$coste_potencia,$aux_coste_potencia);
                            // dd($coste_potencia);
                            $j = 0;
                            if($flag_aux == 1)
                            {
                                if($tipo_count == 1)
                                {
                                    foreach ($aux as $prom_EAct) {
                                        if(is_null($prom_EAct))
                                        {
                                            $EAct[] = 0;
                                            continue;
                                        }else{
                                            // dd($prom_EAct->prom);
                                            $EAct[$j] = $prom_EAct->prom;
                                        }
                                        $i++;
                                        $j++;
                                    }
                                }

                            }else{
                                for ($i=0; $i < count($aux_periodos); $i++)
                                {
                                    $EAct[] = 0;
                                }
                            }

                            $Energia_Act_Reac_Consu = array();
                            $db_Ener_Consu_Acti_Reacti = array();

                            $db_Ener_Consu_Acti_Reacti[] = $db->table('Energia_Consumida_Activa_y_Reactiva')->select(\DB::raw("`Periodo`,SUM(`Energia Activa (kWh)`) E_Activa, SUM(`Energia Reactiva Inductiva (kVArh)`) E_Reac_Induc, SUM(`Energia Reactiva Capacitiva (kVArh)`) E_Reac_Cap"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy('Periodo')->get()->toArray();

                            $energia_activa_max = $db->table('Energia_Consumida_Activa_y_Reactiva')->select(\DB::raw("(SUM(`Energia Activa (kWh)`)) max_Activa"))->where('date','>=',$date_from)->where('date','<=',$date_to)->groupBy('Periodo')->orderBy('max_Activa','DESC')->first();
                            if(!is_null($energia_activa_max))
                            {
                                $energia_activa_max = $energia_activa_max->max_Activa;
                            }
                            else
                                $energia_activa_max = 0;

                            if(empty($db_Ener_Consu_Acti_Reacti[0])){
                                for ($i=0; $i < count($aux_periodos); $i++) {
                                    $Energia_Act[$i] = 0;
                                    $Energia_Reac_Induc[$i] = 0;
                                    $Energia_Reac_Cap[$i] = 0;
                                }
                            }else
                            {
                                foreach ($db_Ener_Consu_Acti_Reacti[0] as $it) {
                                    $Energia_Act[] = $it->E_Activa;
                                    $Energia_Reac_Induc[] = $it->E_Reac_Induc;
                                    $Energia_Reac_Cap[] = $it->E_Reac_Cap;
                                }
                            }

                            $label_intervalo =  "Fecha: ".$date_from."";

                            // dd($Energia_Act, $Energia_Reac_Induc, $Energia_Reac_Cap );
                            // if($value == 'miguel.pena@3seficiencia.com')
                            Mail::to($value,'Submeter 4.0 (Informes Programados)')->send(new SendMailable($user,$contador2,$tipo_count,$periodos2,$EAct,$p_contratada,$Energia_Act,$Energia_Reac_Cap,$Energia_Reac_Induc,$eje,$consumo_activa_diaria,$consumo_capacitiva,$consumo_inductiva,$db_Generacion,$eje_consu,$consumo_activa,$eje_analisis,$p_demandada,$p_contratada_analisis,$emisiones,$generacion,$informes, $consumo_GN_kWh, $consumo_GN_Nm3,$caudal_contratado,$caudal_medio_consumido, $caudal_maximo_consumido, $presion_suministro, $emisiones_gas,$caudal_maximo,$posicion,$caudal_medio_consumido2,$potencia_optima,$label_intervalo, $historicalComparison));
                        }

                        // if($db->table('information_schema.columns')->select(\DB::raw("column_name"))->whereRaw("table_name = 'Potencia_Contratada_Optima' AND column_name = 'Potencia_contratada'")->first())
                        // {
                        //     $potencia_optima = $db->table('Potencia_Contratada_Optima')->select(\DB::raw("Periodo eje,`Potencia_contratada` p_optima"))->orderBy('Periodo')->get();
                        // }else{
                        //     $potencia_optima[0]['p_optima'] =0;
                        //     $potencia_optima[1]['p_optima'] =0;
                        //     $potencia_optima[2]['p_optima'] =0;
                        //     $potencia_optima[3]['p_optima'] =0;
                        //     $potencia_optima[4]['p_optima'] =0;
                        //     $potencia_optima[5]['p_optima'] =0;
                        // }

                    }else{
                        $consumo_GN_kWh = $db->table('Consumo_GN_kWh')->select(\DB::raw("SUM(`Consumo GN (kWh)`) consumo, COUNT(*) cuantos"))->where('date','>=',$date_from)->where('date','<=',$date_to)->first();

                        $consumo_GN_Nm3 = $db->table('Consumo_GN_Nm3')->select(\DB::raw("SUM(`Consumo GN (Nm3)`) consumo"))->where('date','>=',$date_from)->where('date','<=',$date_to)->first();

                        $caudal_contratado = $db->table('Caudal_diario_contratado')->select(\DB::raw("Caudal_diario_contratado caudal"))->where('date_start','<=',$date_from)->where('date_end','>=',$date_to)->first();

                        $caudal_medio_consumido = $db->table('Consumo_GN_kWh')->select(\DB::raw("AVG(`Consumo GN (kWh)`) prome"))->where('date','>=',$date_from)->where('date','<=',$date_to)->first();

                        $caudal_medio_consumido2 = $caudal_medio_consumido->prome;

                        $caudal_maximo = $db->table('Consumo_GN_kWh')->select(\DB::raw("MAX(`Consumo GN (kWh)`) max"))->where('date','>=',$date_from)->where('date','<=',$date_to)->first()->max;

                        if($date_from == $date_to)
                            $caudal_maximo_consumido = $db->table('Consumo_GN_kWh')->select(\DB::raw("DATE_FORMAT(time, '%H:%i') valor, `Consumo GN (kWh)` consumo"))->where('date','>=',$date_from)->where('date','<=',$date_to)->orderBy('consumo','DESC')->get()->toArray();
                        else
                            $caudal_maximo_consumido = $db->table('Consumo_GN_kWh')->select(\DB::raw("DATE_FORMAT(time, '%H:%i') valor, `Consumo GN (kWh)` consumo"))->where('date','>=',$date_from)->where('date','<=',$date_to)->orderBy('consumo','DESC')->get()->toArray();

                        foreach ($caudal_maximo_consumido as $value2) {
                            if($value2->consumo == $caudal_maximo)
                            {
                                $posicion++;
                            }
                        }

                        $presion_suministro = $db->table('Datos_Contador')->select(\DB::raw("AVG(`Presion (bar)`) presion"))->where('date','>=',$date_from)->where('date','<=',$date_to)->first();

                        $emisiones_gas = $db->table('Emisiones_CO2')->select(\DB::raw("SUM(`Emisiones CO2 (kg CO2 eq)`) emisiones"))->where('date','>=',$date_from)->where('date','<=',$date_to)->first();

                        $label_intervalo =  "Fecha: ".$date_from."";

                        Mail::to($value,'Submeter 4.0 (Informes Programados)')->send(new SendMailable(
                            $user,
                            $contador2,
                            $tipo_count,
                            $periodos2,
                            $EAct,
                            $p_contratada,
                            $Energia_Act,
                            $Energia_Reac_Cap,
                            $Energia_Reac_Induc,
                            $eje,
                            $consumo_activa_diaria,
                            $consumo_capacitiva,
                            $consumo_inductiva,
                            $db_Generacion,
                            $eje_consu,
                            $consumo_activa,
                            $eje_analisis,$p_demandada,$p_contratada_analisis,$emisiones,$generacion,$informes, $consumo_GN_kWh, $consumo_GN_Nm3,$caudal_contratado,$caudal_medio_consumido, $caudal_maximo_consumido, $presion_suministro, $emisiones_gas,$caudal_maximo,$posicion,$caudal_medio_consumido2,$potencia_optima, $label_intervalo, $historicalComparison));
                    }

                    \DB::disconnect('mysql2');
                }
            }
        }
    }

    public function getHistoricalComparisonConsumption($db)
    {
        $maxCycles = 7;
        $cycles = [];

        $dt1 = Carbon::now(config('app.timezone'));

        //$months[] = $dt1->format('Y-m');

        for($i = 1 ; $i <= $maxCycles+1 ; $i++){
            // Nos ubicamos en el mes anterior
            $cycles[] = $dt1->subDay()->format('Y-m-d');
        }

        $start = $cycles[$maxCycles];
        $end = $cycles[0];

        $dt1 = Carbon::instance(new \DateTime("{$start} 00:00:00", new \DateTimeZone(config('app.timezone'))));
        $dt2 = Carbon::instance(new \DateTime("{$end} 23:59:59", new \DateTimeZone(config('app.timezone'))));

        $date_from = $dt1->format('Y-m-d');
        $date_to = $dt2->format('Y-m-d');

        $consumosActiva = $db->table('Consumo_Energia_Activa')->select("date", "Energia Activa (kWh) as E_Activa")->where('date','>=',$date_from)->where('date','<=',$date_to)->get()->toArray();
        $consumosReactiva = $db->table('Consumo_Energia_Reactiva')->select("date", "Energia Reactiva Inductiva (kVArh) as E_Reac_Induc", "Energia Reactiva Capacitiva (kVArh) as E_Reac_Cap")->where('date','>=',$date_from)->where('date','<=',$date_to)->get()->toArray();
        $potencias = $db->table('Potencia_Demandada_Contratada')->select('date', 'Periodo', 'Potencia Demandada (kW) as Potencia_Demandada', 'Potencia Contratada (kW) as Potencia_Contratada')->where('date','>=',$date_from)->where('date','<=',$date_to)->get()->toArray();

        $acum1 = [];
        $acum2 = [];
        $acum3 = [];

        foreach($cycles as $cycle){
            foreach($consumosActiva as $consumo){
                $key = $consumo->date;

                if($cycle === $key){
                    $acum1[$cycle][] = [
                        'E_Activa' => $consumo->E_Activa
                    ];
                }
            }

            if(! array_key_exists($cycle, $acum1)){
                $acum1[$cycle][] = [
                    'E_Activa'      => 0
                ];
            }
        }

        foreach($cycles as $cycle){
            foreach($consumosReactiva as $consumo){
                $key = $consumo->date;

                if($cycle === $key){
                    $acum2[$cycle][] = [
                        'E_Reac_Induc'  => $consumo->E_Reac_Induc,
                        'E_Reac_Cap'    => $consumo->E_Reac_Cap,
                    ];
                }
            }

            if(! array_key_exists($cycle, $acum2)){
                $acum2[$cycle][] = [
                    'E_Reac_Induc'  => 0,
                    'E_Reac_Cap'    => 0,
                ];
            }
        }

        foreach($cycles as $cycle){
            foreach($potencias as $potencia){
                $key = $potencia->date;

                if($cycle === $key){
                    $acum3[$cycle][] = [
                        'date'                  => $potencia->date,
                        'Periodo'               => $potencia->Periodo,
                        'Potencia_Demandada'    => $potencia->Potencia_Demandada,
                        'Potencia_Contratada'   => $potencia->Potencia_Contratada,
                    ];
                }
            }

            if(! array_key_exists($cycle, $acum3)){
                $acum3[$cycle][] = [
                    'date'                  => "",
                    'Periodo'               => 0,
                    'Potencia_Demandada'    => 0,
                    'Potencia_Contratada'   => 0,
                ];
            }
        }

        $_acum1 = [];
        $_acum2 = [];
        $_acum3 = [];

        foreach($acum1 as $d => $val){
            $d = $this->dateFormat($d);
            $_acum1[$d] = $val;
        }

        foreach($acum2 as $d => $val){
            $d = $this->dateFormat($d);
            $_acum2[$d] = $val;
        }

        foreach($acum3 as $d => $val){
            $d = $this->dateFormat($d);
            $_acum3[$d] = $val;
        }

        $totalConsumosActiva = [];
        $totalConsumosReactiva = [];
        $totalPotencias = [];

        foreach($_acum1 as $d => $rows) {
            $E_Activa = 0;

            foreach($rows as $row){
                $E_Activa       += $row["E_Activa"];
            }

            $totalConsumosActiva[] = [
                'date'          => $d,
                'E_Activa'      => $E_Activa
            ];
        }

        foreach($_acum2 as $d => $rows) {
            $E_Reac_Induc = 0;
            $E_Reac_Cap = 0;

            foreach($rows as $row){
                $E_Reac_Induc   += $row["E_Reac_Induc"];
                $E_Reac_Cap     += $row["E_Reac_Cap"];
            }

            $totalConsumosReactiva[] = [
                'date'          => $d,
                'E_Reac_Induc'  => $E_Reac_Induc,
                'E_Reac_Cap'    => $E_Reac_Cap,
            ];
        }

        foreach($_acum3 as $d => $rows) {
            $_periodo = null;
            $_demandada = 0;
            $_contratada = 0;


            foreach($rows as $row){
                if($row["Potencia_Demandada"] > $_demandada){
                    $_periodo = $row["Periodo"];
                    $_demandada = $row["Potencia_Demandada"];
                    $_contratada = $row["Potencia_Contratada"];
                }
            }

            $totalPotencias[] = [
                'date'                  => $d,
                'Periodo'               => $_periodo,
                'Potencia_Demandada'    => $_demandada,
                'Potencia_Contratada'   => $_contratada,
            ];
        } 

        return [
            'consumosActivas'   => $totalConsumosActiva,
            'consumosReactivas' => $totalConsumosReactiva,
            'potencias'         => $totalPotencias
        ];
    }

    public function getHistoricalComparisonConsumptionAndGeneration($db)
    {
        $maxCycles = 7;
        $cycles = [];

        $dt1 = Carbon::now(config('app.timezone'));

        //$months[] = $dt1->format('Y-m');

        for($i = 1 ; $i <= $maxCycles+1 ; $i++){
            // Nos ubicamos en el mes anterior
            $cycles[] = $dt1->subDay()->format('Y-m-d');
        }

        $start = $cycles[$maxCycles];
        $end = $cycles[0];

        $dt1 = Carbon::instance(new \DateTime("{$start} 00:00:00", new \DateTimeZone(config('app.timezone'))));
        $dt2 = Carbon::instance(new \DateTime("{$end} 23:59:59", new \DateTimeZone(config('app.timezone'))));

        $date_from = $dt1->format('Y-m-d');
        $date_to = $dt2->format('Y-m-d');

        $consumosActivaReactiva = $db->table('Energia_Consumida_Activa_y_Reactiva')->select("date", "Energia Activa (kWh) as E_Activa", "Energia Reactiva Inductiva (kVArh) as E_Reac_Induc", "Energia Reactiva Capacitiva (kVArh) as E_Reac_Cap")->where('date','>=',$date_from)->where('date','<=',$date_to)->get()->toArray();
        $generacionActivaReactiva = $db->table('Generacion_Energia_Activa_y_Reactiva')->select("date", "Generación Energia Activa (kWh) as E_Activa", "Generación Energia Reactiva Inductiva (kVArh) as E_Reac_Induc", "Generación Energia Reactiva Capacitiva (kVArh) as E_Reac_Cap")->where('date','>=',$date_from)->where('date','<=',$date_to)->get()->toArray();

        $acum1 = [];
        $acum2 = [];

        foreach($cycles as $cycle){
            foreach($consumosActivaReactiva as $consumo){
                $key = $consumo->date;

                if($cycle === $key){
                    $acum1[$cycle][] = [
                        'E_Activa'      => $consumo->E_Activa,
                        'E_Reac_Induc'  => $consumo->E_Reac_Induc,
                        'E_Reac_Cap'    => $consumo->E_Reac_Cap,
                    ];
                }
            }

            if(! array_key_exists($cycle, $acum1)){
                $acum1[$cycle][] = [
                    'E_Activa'      => 0,
                    'E_Reac_Induc'  => 0,
                    'E_Reac_Cap'    => 0,
                ];
            }
        }

        foreach($cycles as $cycle){
            foreach($generacionActivaReactiva as $consumo){
                $key = $consumo->date;

                if($cycle === $key){
                    $acum2[$cycle][] = [
                        'E_Activa'      => $consumo->E_Activa,
                        'E_Reac_Induc'  => $consumo->E_Reac_Induc,
                        'E_Reac_Cap'    => $consumo->E_Reac_Cap,
                    ];
                }
            }

            if(! array_key_exists($cycle, $acum2)){
                $acum2[$cycle][] = [
                    'E_Activa'      => 0,
                    'E_Reac_Induc'  => 0,
                    'E_Reac_Cap'    => 0,
                ];
            }
        }

        $_acum1 = [];
        $_acum2 = [];

        foreach($acum1 as $d => $val){
            $d = $this->dateFormat($d);
            $_acum1[$d] = $val;
        }

        foreach($acum2 as $d => $val){
            $d = $this->dateFormat($d);
            $_acum2[$d] = $val;
        }

        $totalConsumos = [];
        $totalGeneracion = [];

        foreach($_acum1 as $d => $rows) {
            $E_Activa = 0;
            $E_Reac_Induc = 0;
            $E_Reac_Cap = 0;

            foreach($rows as $row){
                $E_Activa       += $row["E_Activa"];
                $E_Reac_Induc   += $row["E_Reac_Induc"];
                $E_Reac_Cap     += $row["E_Reac_Cap"];
            }

            $totalConsumos[] = [
                'date'          => $d,
                'E_Activa'      => $E_Activa,
                'E_Reac_Induc'  => $E_Reac_Induc,
                'E_Reac_Cap'    => $E_Reac_Cap,
            ];
        }

        foreach($_acum2 as $d => $rows) {
            $E_Activa = 0;
            $E_Reac_Induc = 0;
            $E_Reac_Cap = 0;

            foreach($rows as $row){
                $E_Activa       += $row["E_Activa"];
                $E_Reac_Induc   += $row["E_Reac_Induc"];
                $E_Reac_Cap     += $row["E_Reac_Cap"];
            }

            $totalGeneracion[] = [
                'date'          => $d,
                'E_Activa'      => $E_Activa,
                'E_Reac_Induc'  => $E_Reac_Induc,
                'E_Reac_Cap'    => $E_Reac_Cap,
            ];
        }

        return [
            'totalConsumos'   => $totalConsumos,
            'totalGeneracion' => $totalGeneracion,
        ];
    }

    public function dateFormat($str){
        return $str;
    }
}
