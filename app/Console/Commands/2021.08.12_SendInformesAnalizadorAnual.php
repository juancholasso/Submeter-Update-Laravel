<?php

namespace app\Console\Commands;
use App\Informes_analizadores;
use App\analyzer_alertas_informes;
use App\Analizador;
use App\AnalyzerMeter;
use App\AnalyzerGroupDetails;
use App\AnalyzerGroup;
use App\EnergyMeter;
use App\Mail\SendMailIformeAnalizadores;
use Illuminate\Support\Facades\Mail;
use Illuminate\Console\Command;

class SendInformesAnalizadorAnual extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:InformesAnalizadorAnual';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Programacion Anual';

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
      $minut = Informes_analizadores::where('check',5)->get();
      if(!is_null($minut))
      {
          $informes = "Anual";

          foreach ($minut as $key)
          {

            $analyzer = array();
            $analyzerGroupDetails = array();
            $analyzerGroup = array();
            $analizador = array();
            $group = array();
            $data_analizadores = array();
            $FDP = array();
            $index = array();

            $mails = explode(';', $key['emails']);
            $contador_name = EnergyMeter::where('id',$key->meter_id)->first()->count_label;

            // $user = User::where('id',$key->user_id)->first();
            $analyzer_meters = AnalyzerMeter::where('meter_id',$key->meter_id)->get();

            $i=0;

        if(!is_null($analyzer_meters))
        {
            foreach ($analyzer_meters as $value)
            {

              $analyzer_informes[$i] = Analyzer_alertas_informes::where('analyzer_id', $value->analyzer_id)->where('user_id', $key->user_id)->where('informes', 1)->first();

              if(isset($analyzer_informes[$i]))
              {
                $analyzerGroupDetails = AnalyzerGroupDetails::where('analyzer_id',$value->analyzer_id)->first();
                $analyzer[$i]['analyzer_id'] = $analyzerGroupDetails->analyzer_id;
                $analyzer[$i]['analyzer_group_id'] = $analyzerGroupDetails->analyzer_group_id;

                $analyzerGroup = AnalyzerGroup::where('id',$analyzer[$i]['analyzer_group_id'])->first();
                $group[$i]['analyzer_group_id'] = $analyzer[$i]['analyzer_group_id'];
                $group[$i]['analyzer_group_name'] = $analyzerGroup->name;

                $analizador = Analizador::where('id',$value->analyzer_id)->first();
                $analyzer[$i]['analyzer_name'] = $analizador->label;
                $analyzer[$i]['host'] = $analizador->host;
                $analyzer[$i]['port'] = $analizador->port;
                $analyzer[$i]['database'] = $analizador->database;
                $analyzer[$i]['username'] = $analizador->username;
                $analyzer[$i]['password'] = $analizador->password;
                $analyzer[$i]['principal'] = $analizador->principal;

                $i++;
              }
            }

          //  if(isset($analyzer))

            $group = array_unique($group, SORT_REGULAR);
            foreach($group as $t=>$gr)
            {
              $group[$t]['energia_activa'] = 0;
              $group[$t]['energia_reactiva'] = 0;
              $group[$t]['POWact_avg'] = 0;
              $group[$t]['POWrea_avg'] = 0;
              $group[$t]['PF_avg'] = 0;
              $FDP[$t] = 0;
              $index[$t] = 0;
            }
            for ($j=0; $j < 5; $j++)
            {
              $data_analizadores_total[$j]['energia_activa'] = 0;
              $data_analizadores_total[$j]['energia_reactiva'] = 0;
              $data_analizadores_total[$j]['POWact_avg'] = 0;
              $data_analizadores_total[$j]['POWrea_avg'] = 0;
              $data_analizadores_total[$j]['PF_avg'] = 0;
              $FDP_total[$j] = 0;
            }



            foreach($analyzer as $i=>$value)
            {
              $data_analizadores[$i]['analyzer_name'] = $value['analyzer_name'];
              $data_analizadores[$i]['analyzer_group_id'] = $value['analyzer_group_id'];


              config(['database.connections.mysql2.host' => $value['host']]);
              config(['database.connections.mysql2.port' => $value['port']]);
              config(['database.connections.mysql2.database' => $value['database']]);
              config(['database.connections.mysql2.username' => $value['username']]);
              config(['database.connections.mysql2.password' => $value['password']]);
              env('MYSQL2_HOST',$value['host']);
              env('MYSQL2_DATABASE',$value['database']);
              env('MYSQL2_USERNAME', $value['username']);
              env('MYSQL2_PASSWORD',$value['password']);
              \DB::connection('mysql2')->getPdo();

              $db = \DB::connection('mysql2');

              $date_from = \Carbon\Carbon::now()->subYears(1)->startOfYear()->toDateString();
              $date_to = \Carbon\Carbon::now()->subYears(1)->endOfYear()->toDateString();
              $label_intervalo = "Año ".date("Y", strtotime($date_from))."";

              $data_analizadores[$i]['POWact_max']  = 0;
              $data_analizadores[$i]['POWact_avg']  = 0;
              $data_analizadores[$i]['POWrea_avg']  = 0;
              $IAC1_max = 0;
              $IAC2_max = 0;
              $IAC3_max = 0;
              $data_analizadores[$i]['IAC_max'] = 0;
              $data_analizadores[$i]['PF_avg'] = 0;
              $data_analizadores[$i]['energia_activa'] = 0;
              $data_analizadores[$i]['energia_reactiva'] = 0;


              $tb_data = $db->table('Analizadores_Tipo')->select(\DB::raw("`ENEact (kWh)` energia_activa,
                                          `ENErea (kVArh)` AS energia_reactiva, `POWact_Total (kW)` POWact_Total, `POWrea_Total (kVAr)` POWrea_Total, `IAC1 (A)` IAC1, `IAC2 (A)` IAC2, `IAC3 (A)` IAC3,`PF_Total` PF_Total "))
                                          ->where('date','>=', $date_from)->where('date','<=', $date_to)->orderBy("date", "ASC")
                                          ->orderBy("time", "ASC")->get();


              if(!is_null($tb_data))
              {
                  $size_data = count($tb_data);
                  if($size_data >= 2)
                  {
                    $data_analizadores[$i]['energia_activa'] = $tb_data[$size_data - 1]->energia_activa - $tb_data[0]->energia_activa;
                    $data_analizadores[$i]['energia_reactiva'] = $tb_data[$size_data - 1]->energia_reactiva - $tb_data[0]->energia_reactiva;
                  }


                $data_analizadores[$i]['POWact_max']  = $tb_data->max('POWact_Total');
                $data_analizadores[$i]['POWact_avg']  = $tb_data->avg('POWact_Total');
                $data_analizadores[$i]['POWrea_avg']  = $tb_data->avg('POWrea_Total');
                $IAC1_max = $tb_data->max('IAC1');
                $IAC2_max = $tb_data->max('IAC2');
                $IAC3_max = $tb_data->max('IAC3');
                $data_analizadores[$i]['IAC_max'] = max($IAC1_max, $IAC2_max, $IAC3_max);
                $data_analizadores[$i]['PF_avg'] = $tb_data->avg('PF_Total');

                foreach($group as $t=>$gr)
                {
                  if($group[$t]['analyzer_group_id'] == $data_analizadores[$i]['analyzer_group_id'])
                  {
                    $group[$t]['energia_activa'] += $data_analizadores[$i]['energia_activa'];
                    $group[$t]['energia_reactiva'] += $data_analizadores[$i]['energia_reactiva'];
                    $group[$t]['POWact_avg'] += $data_analizadores[$i]['POWact_avg'];
                    $group[$t]['POWrea_avg'] += $data_analizadores[$i]['POWrea_avg'];
                    $FDP[$t] += $data_analizadores[$i]['PF_avg'];
                    $index[$t]++;
                  }
                }
              }


              //aqui añadir un else y igual lo anterior a "0".

              for ($j=0; $j < 5; $j++)
              {
                $date_from = \Carbon\Carbon::now()->subYears(1+$j)->startOfYear()->toDateString();
                $date_to = \Carbon\Carbon::now()->subYears(1+$j)->endOfYear()->toDateString();

                $tb_data = $db->table('Analizadores_Tipo')->select(\DB::raw("`ENEact (kWh)` energia_activa,
                                            `ENErea (kVArh)` AS energia_reactiva, `POWact_Total (kW)` POWact_Total, `POWrea_Total (kVAr)` POWrea_Total, `IAC1 (A)` IAC1, `IAC2 (A)` IAC2, `IAC3 (A)` IAC3,`PF_Total` PF_Total "))
                                            ->where('date','>=', $date_from)->where('date','<=', $date_to)->orderBy("date", "ASC")
                                            ->orderBy("time", "ASC")->get();

                $data_analizadores_total[$j]['date'] = "Año ".date("Y", strtotime($date_from))."";

                if(!is_null($tb_data) && $value['principal']==1)
                {
                    $size_data = count($tb_data);
                    if($size_data >= 2)
                    {
                      $data_analizadores_total[$j]['energia_activa'] += $tb_data[$size_data - 1]->energia_activa - $tb_data[0]->energia_activa;
                      $data_analizadores_total[$j]['energia_reactiva'] += $tb_data[$size_data - 1]->energia_reactiva - $tb_data[0]->energia_reactiva;
                    }
                    $data_analizadores_total[$j]['POWact_avg']  += $tb_data->avg('POWact_Total');
                    $data_analizadores_total[$j]['POWrea_avg']  += $tb_data->avg('POWrea_Total');
                    $FDP_total[$j] += $tb_data->avg('PF_Total');
                }else{
                    $data_analizadores_total[$j]['energia_activa'] += 0;
                    $data_analizadores_total[$j]['energia_reactiva'] += 0;
                    $data_analizadores_total[$j]['POWact_avg']  += 0;
                    $data_analizadores_total[$j]['POWrea_avg']  += 0;
                    $FDP_total[$j] += 0;
                }
              }


              \DB::disconnect('mysql2');
            }


          if(isset($analyzer_informes[0]))
          {

              for ($j=0; $j < 5; $j++)
              {
                if($FDP_total[$j] !== 0)
                {
                  $data_analizadores_total[$j]['PF_avg'] = $FDP_total[$j]/count($analyzer);
                }else{
                  $data_analizadores_total[$j]['PF_avg'] = 0;
                }
              }

              foreach($group as $t=>$gr)
              {
                $group[$t]['PF_avg'] = $FDP[$t]/$index[$t];
              }

              foreach ($mails as $value)
              {
                  Mail::to($value,'Submeter 4.0 (Informes Programados)')->send(new SendMailIformeAnalizadores($group,$data_analizadores,$informes,$contador_name,$label_intervalo,$data_analizadores_total));
              }

          }
        }
      }
    }
  }
}
