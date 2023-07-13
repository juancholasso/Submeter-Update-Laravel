<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Analizador;
use App\EnergyMeter;
use App\AnalyzerMeter;
use App\EnterpriseUser;
use App\EnterpriseAnalyzerGroups;
use App\AnalyzerGroup;
use App\User;
use App\UserAnalyzers;
use App\AnalyzerGroupDetails;
use App\Enterprise;
use Validator;
use Session;
use Auth;

class AnalyzerController extends Controller
{
    public function list(Request $request)
    {
        $columnNames = [0=>["id", true], 1 =>["count_label", true], 2 => ["label", true], 3 => ["assign", false], 4 => ["edit", false], 5=>["delete", false]];
        $columns = $request["columns"];
        $search = $request["search"];
        $orders = $request["order"];
        $startData = $request["start"];
        $lengthData = $request["length"];
        $dataList = Analizador::take(1e9);

        if(strlen($search["value"]) > 0)
        {
            foreach($columns as $index => $column)
            {
                if($column["searchable"] == "true" && $columnNames[$index][1] && $index != 1)
                {
                    $dataList->orWhere($columnNames[$index][0], "LIKE", "%".$search["value"]."%");
                }
            }
        }

        if($columns[1]["searchable"] == "true" && array_has($search, "value") &&strlen($search["value"]) > 0)
        {
            $dataList->orWhereHas("meter", function($query) use($search, $columnNames) {
                $query->where($columnNames[1][0], "LIKE", "%".$search["value"]."%");
            });
        }

        foreach($orders as $index => $order)
        {
            $dataList->orderBy($columnNames[$order["column"]][0], $order["dir"]);
        }
        $total = $dataList->count();

        if($startData > 0)
        {
            $dataList->skip($startData);
        }
        $dataList->take($lengthData);

        $data = $dataList->get();

        $dataRows = [];
        foreach($data as $dat)
        {
            $count_label = "";
            $principal = ($dat->principal)?'SI':'NO';
            if($dat->meter)
            {
                $count_label = $dat->meter->count_label;
            }
            $dataRows[] = [$dat->id, $count_label , $dat->label, $principal ,1, 1, 1];
        }

        $dataList = [];
        $dataList["draw"] = $request->get("draw");
        $dataList["recordsTotal"] = $total;
        $dataList["recordsFiltered"] = $total;
        $dataList["data"] = $dataRows;
        return $dataList;
    }

    public function show(Request $request, $analyzer_id)
    {
        $analizador = Analizador::find($analyzer_id);

        $data = [];
        $data["id"] = $analizador->id;
        $data["name"] = $analizador->label;
        $data["host"] = $analizador->host;
        $data["database"] = $analizador->database;
        $data["username"] = $analizador->username;
        $data["password"] = $analizador->password;
        $data["port"] = $analizador->port;
        $data["main"] = $analizador->principal;
        $data["color"] = $analizador->color_etiqueta;
        $data["meters"] = $analizador->analyzer_meters;

        $dataSend = [];
        $dataSend["error"] = false;
        $dataSend["data"] = $data;
        return $dataSend;
    }

    public function save(Request $request)
    {
        $messages = [
            'name.required' => 'Debes agregar un nombre',
            'host.required' => 'Debes agregar un host',
            'database.required' => 'Debes agregar una base de datos',
            'username.required' => 'Debes agregar un usuario',
            'password.required' => 'Debes agregar una contraseña',
            'port.required' => 'Debes agregar un puerto',
            'port.numeric' => 'El puerto debe ser numerico',
            'color.required' => 'Color requerido'
        ];

        $v = Validator::make($request->all(), [
            'name' => 'required',
            'host' => 'required',
            'database' => 'required',
            'username' => 'required',
            'password' => 'required',
            'port' => 'required|numeric',
            'color' => 'required'
        ],$messages);

        if ($v->fails())
        {
            $data = [];
            $data["error"] = true;
            $data["messages"] = $v->errors();
            return $data;
        }

        $analizador = new Analizador;
        $principal = $request->get("main", 0);
        $meter_id = $request->get("meter");

        $analizador->label = $request->get("name");
        $analizador->host = $request->get("host");
        $analizador->port = $request->get("port");
        $analizador->database = $request->get("database");
        $analizador->username = $request->get("username");
        $analizador->password = $request->get("password");
        $analizador->color_etiqueta = $request->get("color");
        $analizador->principal = $principal;
        $analizador->save();

        AnalyzerMeter::where("analyzer_id", $analizador->id)
                ->update(["updated" => 0]);
        $meters = $request->get("analyzerMeter", []);
        foreach($meters as $meter)
        {
            $analyzerMeter = AnalyzerMeter::where("analyzer_id", $analizador->id)
                    ->where("meter_id", $meter)->first();
            if(!$analyzerMeter)
            {
                $analyzerMeter = new AnalyzerMeter;
                $analyzerMeter->analyzer_id = $analizador->id;
                $analyzerMeter->meter_id = $meter;
            }
            $analyzerMeter->updated = 1;
            $analyzerMeter->save();
        }

        AnalyzerMeter::where("analyzer_id", $analizador->id)
                ->where("updated", 0)
                ->delete();

        $analizador->meter_count = 0;
        if($analizador->analyzer_meters)
        {
            $analizador->meter_count = count($analizador->analyzer_meters);
        }

        $data = [];
        $data["error"] = false;
        $data["data"] = $analizador;
        return $data;
    }

    public function update(Request $request, $analyzer_id)
    {
        $analizador = Analizador::find($analyzer_id);
        if(!$analizador)
        {
            $data = [];
            $data["error"] = true;
            $data["messages"] = [];
            return $data;
        }
        $messages = [
            'id.required' => 'ID Requerido',
            'id.numeric' => 'ID debe ser numerico',
            'name.required' => 'Debes agregar un nombre',
            'host.required' => 'Debes agregar un host',
            'database.required' => 'Debes agregar una base de datos',
            'username.required' => 'Debes agregar un usuario',
            'password.required' => 'Debes agregar una contraseña',
            'port.required' => 'Debes agregar un puerto',
            'port.numeric' => 'El puerto debe ser numerico',
            'color.required' => 'Color requerido'
        ];

        $v = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'name' => 'required',
            'host' => 'required',
            'database' => 'required',
            'username' => 'required',
            'password' => 'required',
            'port' => 'required|numeric',
            'color' => 'required'
        ],$messages);

        if ($v->fails())
        {
            $data = [];
            $data["error"] = true;
            $data["messages"] = $v->errors();
            return $data;
        }

        $principal = $request->get("main", 0);
        $meter_id = $request->get("meter");

        $analizador->label = $request->get("name");
        $analizador->host = $request->get("host");
        $analizador->port = $request->get("port");
        $analizador->database = $request->get("database");
        $analizador->username = $request->get("username");
        $analizador->password = $request->get("password");
        $analizador->color_etiqueta = $request->get("color");
        $analizador->principal = $principal;
        $analizador->save();

        AnalyzerMeter::where("analyzer_id", $analizador->id)
                ->update(["updated" => 0]);
        $meters = $request->get("analyzerMeter", []);
        foreach($meters as $meter)
        {
            $analyzerMeter = AnalyzerMeter::where("analyzer_id", $analizador->id)
                    ->where("meter_id", $meter)->first();
            if(!$analyzerMeter)
            {
                $analyzerMeter = new AnalyzerMeter;
                $analyzerMeter->analyzer_id = $analizador->id;
                $analyzerMeter->meter_id = $meter;
            }
            $analyzerMeter->updated = 1;
            $analyzerMeter->save();
        }

        AnalyzerMeter::where("analyzer_id", $analizador->id)
                ->where("updated", 0)
                ->delete();

        $analizador->meter_count = 0;
        if($analizador->analyzer_meters)
        {
            $analizador->meter_count = count($analizador->analyzer_meters);
        }

        $data = [];
        $data["error"] = false;
        $data["data"] = $analizador;
        return $data;
    }

    public function delete(Request $request, $analyzer_id)
    {
        $analizador = Analizador::find($analyzer_id);
        if(!$analizador)
        {
            $data = [];
            $data["error"] = true;
            $data["messages"] = [];
            return $data;
        }
        $analizador->delete();

        $data = [];
        $data["error"] = false;
        return $data;
    }

    public function getAnalyzersList()
    {
        $analyzers = Analizador::all();
        $data_analyzers = [];
        foreach($analyzers as $analyzer)
        {
            $option = ["value"=>$analyzer->id, "name"=>$analyzer->label];
            $data_analyzers[] = $option;
        }
        $data = [];
        $data["error"] = false;
        $data["options"] = $data_analyzers;
        return $data;
    }

    public function showAnalyzers($id = 0)
    {
        $session_user_id = Auth::user()->id;
        if($id != $session_user_id) return redirect("/analizadores/grupos/$session_user_id");

		ini_set('memory_limit', '512M');
        $user = User::find($id);
        $contador = strtolower(request()->input('contador'));

        $interval = "";
        $flash_current_count = null;
        $session = Session::get('_flash');
        
        if(array_key_exists('intervalos', $session)){
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

        $eUser = EnterpriseUser::where("user_id", $user->id)->first();
        $enterprise = Enterprise::find($eUser->enterprise_id);
        $currentGAnalyzer = EnterpriseAnalyzerGroups::where("enterprise_id", $eUser->enterprise_id)->first();

        if($currentGAnalyzer){
            $current_group = AnalyzerGroup::find($currentGAnalyzer->analyzer_group_id);
        }
        $data_analyzers = [];
        $analyzers_data = [];

        $data = [];
        $data["user"] = $user;
        $data["contador"] = $contador2;
        $data["enterprise"] = $enterprise;
        $data["date_from"] = $date_from;
        $data["date_to"] = $date_to;

        $groups_total_energy = 0;
        $data_groups = [];
        $current_group_data = [];
        $new_data_group = [];
        $new_data_group_ids = [];
        $new_data_subgroup_1 = [];
        $new_groups_total_energy = 0;
        $eGroups = EnterpriseAnalyzerGroups::where("enterprise_id", $enterprise->id)->get();
        foreach($eGroups as $eGroup){
            $group_analyzer = AnalyzerGroup::find($eGroup->analyzer_group_id);

            if($group_analyzer){
                $data["group_analyzer"] = $group_analyzer;
                $analyzers_data = $this->computeDataAnalyzerGroup($data);
                if(!isset($analyzers_data["analyzers_stats"][0])){
                    continue;
                }
                $groups_total_energy += $analyzers_data["total_energia_activa"];
                //if($group_analyzer->id == $current_group->id){
                    //$current_group_data = $analyzers_data;
                //}
                $data_groups[] = $analyzers_data;
                if(!in_array($analyzers_data['group_id'], $new_data_group_ids) && count($analyzers_data['dependencies_ids'])===0){
                    unset($analyzers_data['dependencies_ids']);
                    unset($analyzers_data['dependencies_first']);
                    $new_groups_total_energy += $analyzers_data["total_energia_activa"];
                    $new_data_group[] = $analyzers_data;
                    $new_data_group_ids[] = $analyzers_data['group_id'];
                }else if(count($analyzers_data['dependencies_ids'])>0){
                    $new_data_subgroup_1[] = $analyzers_data;
                }
            }
        }
        $new_data_group = $this->evalDataStructure($new_data_subgroup_1, $new_data_group, $new_groups_total_energy);

        if(!empty($data_groups)){
            for($i = 0; $i < count($data_groups); $i++){

                if($groups_total_energy > 0){
                    $data_groups[$i]["porcentaje_energia_activa"] = 100*$data_groups[$i]["total_energia_activa"] / $groups_total_energy;
                    if(isset($data_groups[$i]["dependencies_first"]['id'])){
                        $data_groups[$i]["dependencies_first"]['porcentaje_energia_activa'] = 100*$data_groups[$i]["dependencies_first"]["energia_activa"] / $groups_total_energy;
                    }
                } else {
                    $data_groups[$i]["porcentaje_energia_activa"] = 0.0;
                    if(isset($data_groups[$i]["dependencies_first"]['id'])){
                        $data_groups[$i]["dependencies_first"]['porcentaje_energia_activa'] = 0.0;
                    }
                }

                for($j = 0; $j < count($data_groups[$i]["analyzers_stats"]); $j++){
                    if($groups_total_energy > 0){
                        $data_groups[$i]["analyzers_stats"][$j]["porcentaje_energia_activa"] = 100* $data_groups[$i]["analyzers_stats"][$j]["energia_activa"] / $groups_total_energy;
                    } else {
                        $data_groups[$i]["analyzers_stats"][$j]["porcentaje_energia_activa"] = 0.0;
                    }
                }
            }
        }
        if(empty($current_group_data) && count($new_data_group)>0){
            $current_group_data = $new_data_group[0];
        }
        /*
        echo "3.-";
        echo json_encode($data_groups);
        echo "<br><br><br>4.-";
        echo json_encode($new_data_group);
        echo "<br><br><br>5.-";
        echo json_encode($current_group_data);
        echo "<br><br><br>6.-";
        echo json_encode($current_group);
        exit();
        */
        $chartjsnew = true;
        $titulo = "Analizadores Submetering";
        return view("analizadores.groupanalyzers", compact("contador2", "analyzers_data", "chartjsnew" , "current_group_data", "date_from", "date_to", "data_groups", "groups_total_energy", "label_intervalo", "tipo_count", "titulo" ,"user", "new_data_group"));
    }

    private function evalDataStructure($new_data_subgroup_1, $new_data_group, $new_groups_total_energy){
        $new_data_subgroup_2 = [];
        if(count($new_data_subgroup_1)>0){
            foreach($new_data_subgroup_1 as $newsubgroup1){
                $dependencies_id = $newsubgroup1['dependencies_ids'][0];
                $hasPosition = false;
                foreach($new_data_group as $index_a => $newgroup){
                    foreach($newgroup['analyzers_stats'] as $index_b => $analyzerb){
                        $analyzer_id = isset($analyzerb['id']) ? $analyzerb['id'] : (isset($analyzerb['dependencies_ids'])&&count($analyzerb['dependencies_ids'])>0 ? $analyzerb['dependencies_ids'][0] : 0);
                        if($analyzer_id == $dependencies_id){
                            $hasPosition = true;
                            unset($newsubgroup1['dependencies_ids']);
                            unset($newsubgroup1['dependencies_first']);
                            /*
                            if(!isset($new_data_group[$index_a]['analyzers_stats'][$index_b]['analyzers_stats'])){
                                $new_data_group[$index_a]['analyzers_stats'][$index_b]['analyzers_stats'] = [];
                            }
                            $new_data_group[$index_a]['analyzers_stats'][$index_b]['analyzers_stats'][] = $newsubgroup1;
                            */
                            if(isset($newsubgroup1['analyzers_stats']) && count($newsubgroup1['analyzers_stats'])>0){
                                $new_data_group[$index_a]['analyzers_stats'][$index_b]['analyzers_stats'] = $newsubgroup1['analyzers_stats'];
                                if(!isset($new_data_group[$index_a]['analyzers_stats'][$index_b]['rest_operation']) && isset($newsubgroup1['rest_operation'])){
                                    $new_data_group[$index_a]['analyzers_stats'][$index_b]['rest_operation'] = $newsubgroup1['rest_operation'];
                                }
                            }
                        }
                    }
                }
                if(!$hasPosition){
                    $new_data_subgroup_2[] = $newsubgroup1;
                }
            }
        }
        if(count($new_data_subgroup_2)>0){
            $new_data_subgroup_3 = [];
            foreach($new_data_subgroup_2 as $newsubgroup2){
                $dependencies_id = $newsubgroup2['dependencies_ids'][0];
                $hasPosition = false;
                foreach($new_data_group as $index_a => $newgroup){
                    foreach($newgroup['analyzers_stats'] as $index_b => $analyzerb){
                        if(isset($analyzerb['analyzers_stats']) && count($analyzerb['analyzers_stats'])>0){
                            foreach($analyzerb['analyzers_stats'] as $index_c => $analyzerc){
                                $analyzer_id = isset($analyzerc['id']) ? $analyzerc['id'] : (isset($analyzerc['dependencies_ids'])&&count($analyzerc['dependencies_ids'])>0 ? $analyzerc['dependencies_ids'][0] : 0);
                                if($analyzer_id == $dependencies_id){
                                    $hasPosition = true;
                                    unset($newsubgroup2['dependencies_ids']);
                                    unset($newsubgroup2['dependencies_first']);
                                    /*
                                    if(!isset($new_data_group[$index_a]['analyzers_stats'][$index_b]['analyzers_stats'][$index_c]['analyzers_stats'])){
                                        $new_data_group[$index_a]['analyzers_stats'][$index_b]['analyzers_stats'][$index_c]['analyzers_stats'] = [];
                                    }
                                    $new_data_group[$index_a]['analyzers_stats'][$index_b]['analyzers_stats'][$index_c]['analyzers_stats'][] = $newsubgroup2;
                                    */
                                    if(isset($newsubgroup2['analyzers_stats']) && count($newsubgroup2['analyzers_stats'])>0){
                                        $new_data_group[$index_a]['analyzers_stats'][$index_b]['analyzers_stats'][$index_c]['analyzers_stats'] = $newsubgroup2['analyzers_stats'];
                                        if(!isset($new_data_group[$index_a]['analyzers_stats'][$index_b]['analyzers_stats'][$index_c]['rest_operation']) && isset($newsubgroup2['rest_operation'])){
                                            $new_data_group[$index_a]['analyzers_stats'][$index_b]['analyzers_stats'][$index_c]['rest_operation'] = $newsubgroup2['rest_operation'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                if(!$hasPosition){
                    $new_data_subgroup_3[] = $newsubgroup2;
                }
            }
            if(count($new_data_subgroup_3)>0){
                $new_data_subgroup_4 = [];
                foreach($new_data_subgroup_3 as $newsubgroup3){
                    $dependencies_id = $newsubgroup3['dependencies_ids'][0];
                    $hasPosition = false;
                    foreach($new_data_group as $index_a => $newgroup){
                        foreach($newgroup['analyzers_stats'] as $index_b => $analyzerb){
                            if(isset($analyzerb['analyzers_stats']) && count($analyzerb['analyzers_stats'])>0){
                                foreach($analyzerb['analyzers_stats'] as $index_c => $analyzerc){
                                    if(isset($analyzerc['analyzers_stats']) && count($analyzerc['analyzers_stats'])>0){
                                        foreach($analyzerc['analyzers_stats'] as $index_d => $analyzerd){
                                            $analyzer_id = isset($analyzerd['id']) ? $analyzerd['id'] : (isset($analyzerd['dependencies_ids'])&&count($analyzerd['dependencies_ids'])>0 ? $analyzerd['dependencies_ids'][0] : 0);
                                            if($analyzer_id == $dependencies_id){
                                                $hasPosition = true;
                                                unset($newsubgroup3['dependencies_ids']);
                                                unset($newsubgroup3['dependencies_first']);
                                                /*
                                                if(!isset($new_data_group[$index_a]['analyzers_stats'][$index_b]['analyzers_stats'][$index_c]['analyzers_stats'][$index_d]['analyzers_stats'])){
                                                    $new_data_group[$index_a]['analyzers_stats'][$index_b]['analyzers_stats'][$index_c]['analyzers_stats'][$index_d]['analyzers_stats'] = [];
                                                }
                                                $new_data_group[$index_a]['analyzers_stats'][$index_b]['analyzers_stats'][$index_c]['analyzers_stats'][$index_d]['analyzers_stats'][] = $newsubgroup3;
                                                */
                                                if(isset($newsubgroup3['analyzers_stats']) && count($newsubgroup3['analyzers_stats'])>0){
                                                    $new_data_group[$index_a]['analyzers_stats'][$index_b]['analyzers_stats'][$index_c]['analyzers_stats'][$index_d]['analyzers_stats'] = $newsubgroup3['analyzers_stats'];
                                                    if(!isset($new_data_group[$index_a]['analyzers_stats'][$index_b]['analyzers_stats'][$index_c]['analyzers_stats'][$index_d]['rest_operation']) && isset($newsubgroup3['rest_operation'])){
                                                        $new_data_group[$index_a]['analyzers_stats'][$index_b]['analyzers_stats'][$index_c]['analyzers_stats'][$index_d]['rest_operation'] = $newsubgroup3['rest_operation'];
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if(!$hasPosition){
                        $new_data_subgroup_4[] = $newsubgroup3;
                    }
                }
            }
        }

        foreach($new_data_group as $idx0 => $level0){
            if(isset($level0['analyzers_stats']) && count($level0['analyzers_stats'])>0){
                foreach($level0['analyzers_stats'] as $idx1 => $level1){
                    if(isset($level1['analyzers_stats']) && count($level1['analyzers_stats'])>0){
                        foreach($level1['analyzers_stats'] as $idx2 => $level2){
                            if(isset($level2['analyzers_stats']) && count($level2['analyzers_stats'])>0){
                                foreach($level2['analyzers_stats'] as $idx3 => $level3){
                                    if(isset($level3['analyzers_stats']) && count($level3['analyzers_stats'])>0){
                                        foreach($level3['analyzers_stats'] as $idx4 => $level4){
                                            if(isset($level4['analyzers_stats']) && count($level4['analyzers_stats'])>0){
                                                foreach($level4['analyzers_stats'] as $idx5 => $level5){
                                                    $new_data_group[$idx0]['analyzers_stats'][$idx1]['analyzers_stats'][$idx2]['analyzers_stats'][$idx3]['analyzers_stats'][$idx4]['analyzers_stats'][$idx5]['porcentaje_energia_activa'] = $new_groups_total_energy > 0 ? (100 * (isset($level5['total_energia_activa'])?$level5['total_energia_activa']:$level5['energia_activa']) / $new_groups_total_energy) : 0;
                                                }
                                            }
                                            $new_data_group[$idx0]['analyzers_stats'][$idx1]['analyzers_stats'][$idx2]['analyzers_stats'][$idx3]['analyzers_stats'][$idx4]['porcentaje_energia_activa'] = $new_groups_total_energy > 0 ? (100 * (isset($level4['total_energia_activa'])?$level4['total_energia_activa']:$level4['energia_activa']) / $new_groups_total_energy) : 0;
                                        }
                                    }
                                    $new_data_group[$idx0]['analyzers_stats'][$idx1]['analyzers_stats'][$idx2]['analyzers_stats'][$idx3]['porcentaje_energia_activa'] = $new_groups_total_energy > 0 ? (100 * (isset($level3['total_energia_activa'])?$level3['total_energia_activa']:$level3['energia_activa']) / $new_groups_total_energy) : 0;
                                }
                            }
                            $new_data_group[$idx0]['analyzers_stats'][$idx1]['analyzers_stats'][$idx2]['porcentaje_energia_activa'] = $new_groups_total_energy > 0 ? (100 * (isset($level2['total_energia_activa'])?$level2['total_energia_activa']:$level2['energia_activa']) / $new_groups_total_energy) : 0;
                        }
                    }
                    $new_data_group[$idx0]['analyzers_stats'][$idx1]['porcentaje_energia_activa'] = $new_groups_total_energy > 0 ? (100 * (isset($level1['total_energia_activa'])?$level1['total_energia_activa']:$level1['energia_activa']) / $new_groups_total_energy) : 0;
                }
            }
            $new_data_group[$idx0]['porcentaje_energia_activa'] = $new_groups_total_energy > 0 ? (100 * (isset($level0['total_energia_activa'])?$level0['total_energia_activa']:$level0['energia_activa']) / $new_groups_total_energy) : 0;
        }

        return $new_data_group;
    }

    public function showAnalyzersSelected(Request $request, $id, $group_id)
    {
        $session_user_id = Auth::user()->id;
        if($id != $session_user_id) return redirect("/analizadores/grupos/$session_user_id/$group_id");

		ini_set('memory_limit', '512M');
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

        $eUser = EnterpriseUser::where("user_id", $user->id)->first();
        $enterprise = Enterprise::find($eUser->enterprise_id);
        $current_group = AnalyzerGroup::find($group_id);
        $data_analyzers = [];
        $analyzers_data = [];

        $data = [];
        $data["user"] = $user;
        $data["contador"] = $contador2;
        $data["enterprise"] = $enterprise;
        $data["date_from"] = $date_from;
        $data["date_to"] = $date_to;

        $groups_total_energy = 0;
        $data_groups = [];
        $current_group_data = [];
        $new_data_group = [];
        $new_data_group_ids = [];
        $new_data_subgroup_1 = [];
        $new_groups_total_energy = 0;
        $eGroups = EnterpriseAnalyzerGroups::where("enterprise_id", $enterprise->id)->get();
        foreach($eGroups as $eGroup){
            $group_analyzer = AnalyzerGroup::find($eGroup->analyzer_group_id);
            if($group_analyzer){
                $data["group_analyzer"] = $group_analyzer;
                $analyzers_data = $this->computeDataAnalyzerGroup($data);
                if(!isset($analyzers_data["analyzers_stats"][0])){
                    continue;
                }
                $groups_total_energy += $analyzers_data["total_energia_activa"];
                //if($group_analyzer->id == $current_group->id){
                    //$current_group_data = $analyzers_data;
                //}
                $data_groups[] = $analyzers_data;
                if(!in_array($analyzers_data['group_id'], $new_data_group_ids) && count($analyzers_data['dependencies_ids'])===0){
                    unset($analyzers_data['dependencies_ids']);
                    unset($analyzers_data['dependencies_first']);
                    $new_groups_total_energy += $analyzers_data["total_energia_activa"];
                    $new_data_group[] = $analyzers_data;
                    $new_data_group_ids[] = $analyzers_data['group_id'];
                }else if(count($analyzers_data['dependencies_ids'])>0){
                    $new_data_subgroup_1[] = $analyzers_data;
                }
            }
        }
        $new_data_group = $this->evalDataStructure($new_data_subgroup_1, $new_data_group, $new_groups_total_energy);

        if(!empty($data_groups)){
            for($i = 0; $i < count($data_groups); $i++){
                if($groups_total_energy > 0){
                    $data_groups[$i]["porcentaje_energia_activa"] = 100*$data_groups[$i]["total_energia_activa"] / $groups_total_energy;
                    if(isset($data_groups[$i]["dependencies_first"]['id'])){
                        $data_groups[$i]["dependencies_first"]['porcentaje_energia_activa'] = 100*$data_groups[$i]["dependencies_first"]["energia_activa"] / $groups_total_energy;
                    }
                } else {
                    $data_groups[$i]["porcentaje_energia_activa"] = 0.0;
                    if(isset($data_groups[$i]["dependencies_first"]['id'])){
                        $data_groups[$i]["dependencies_first"]['porcentaje_energia_activa'] = 0.0;
                    }
                }

                for($j = 0; $j < count($data_groups[$i]["analyzers_stats"]); $j++){
                    if($groups_total_energy > 0){
                        $data_groups[$i]["analyzers_stats"][$j]["porcentaje_energia_activa"] = 100* $data_groups[$i]["analyzers_stats"][$j]["energia_activa"] / $groups_total_energy;
                    } else {
                        $data_groups[$i]["analyzers_stats"][$j]["porcentaje_energia_activa"] = 0.0;
                    }
                }
            }
        }
        if(empty($current_group_data) && count($data_groups)>0){
            foreach($data_groups as $gp){
                if($gp["group_id"] == $group_id){
                    $current_group_data = $gp;
                }
            }
        }
        /*
        echo "3.-";
        echo json_encode($data_groups);
        echo "<br><br><br>4.-";
        echo json_encode($new_data_group);
        echo "<br><br><br>5.-";
        echo json_encode($current_group_data);
        echo "<br><br><br>6.-";
        echo json_encode($current_group);
        exit();
        */
        $chartjsnew = true;
        $titulo = "Analizadores Submetering";
        return view("analizadores.groupanalyzers", compact("contador2", "analyzers_data", "chartjsnew" ,"current_group_data", "date_from", "date_to",
            "data_groups", "group_analyzers", "groups_total_energy", "label_intervalo", "tipo_count", "titulo" ,"user", "new_data_group"));
    }

    private function computeDataAnalyzerGroup($data)
    {
        $user = $data["user"];
        $contador = $data["contador"];
        $group_analyzers = $data["group_analyzer"];
        $enterprise = $data["enterprise"];
        $date_to = $data["date_to"];
        $date_from = $data["date_from"];

        $analyzers_stats = [];

        $aMeters = AnalyzerMeter::where("meter_id", $contador->id)->get();
        if(!isset($aMeters[0])){
            return null;
        }
      
        $total_energia_activa = 0.0;
        $total_energia_reactiva = 0.0;
        $total_potencia_activa = 0.0;
        $total_potencia_activa_promedio = 0.0;
        $total_analyzers = 0;
        
        foreach($aMeters as $aMeter)
        {
            $uAnalyzer = UserAnalyzers::where("enterprise_id", $enterprise->id)->where("user_id", $user->id)->where("analyzer_id", $aMeter->analyzer_id)->first();
            if(!$uAnalyzer)
            {
                continue;
            }

            $gDetail = AnalyzerGroupDetails::where("analyzer_id", $aMeter->analyzer_id)->where("analyzer_group_id", $group_analyzers->id)->first();
            if(!$gDetail)
            {
                continue;
            }

            $analyzer = Analizador::find($aMeter->analyzer_id);
            if(!$analyzer)
            {
                continue;
            }

            $total_analyzers++;
            $energia_activa = 0.0;
            $energia_reactiva = 0.0;
            $potencia_activa = 0.0;
            $potencia_activa_promedio = 0.0;
            $num_registros_analizador = 0;

            try
            {
                $analizador = $aMeter->analyzer;
                $analizadores[] = $aMeter->analyzer;
                config(['database.connections.mysql2.host' => $analyzer->host]);
                config(['database.connections.mysql2.port' => $analyzer->port]);
                config(['database.connections.mysql2.database' => $analyzer->database]);
                config(['database.connections.mysql2.username' => $analyzer->username]);
                config(['database.connections.mysql2.password' => $analyzer->password]);
                env('MYSQL2_HOST',$analyzer->host);
                env('MYSQL2_DATABASE',$analyzer->database);
                env('MYSQL2_USERNAME', $analyzer->username);
                env('MYSQL2_PASSWORD',$analyzer->password);
                \DB::connection('mysql2')->getPdo();

                $db = \DB::connection('mysql2');

                $data_analyzer = $db->table('Analizadores_Tipo')->select(\DB::raw("`ENEact (kWh)` energia_activa,
                                            `ENErea (kVArh)` AS energia_reactiva, `POWact_Total (kW)` potencia_total,
                                            `POWact1 (kW)` potencia_1, `POWact2 (kW)` potencia_2, `POWact3 (kW)` potencia_3"))
                                        ->where('date','>=', $date_from)->where('date','<=', $date_to)->orderBy("date", "ASC")
                                        ->orderBy("time", "ASC")->get();
                if($data_analyzer)
                {
                    foreach($data_analyzer as $data)
                    {
                        $num_registros_analizador++;
                        if($data->potencia_total == 0)
                        {
                            $data->potencia_total = $data->potencia_1 + $data->potencia_2 + $data->potencia_3;
                        }
                        $potencia_activa += $data->potencia_total;
                    }
                    $size_data = count($data_analyzer);
                    if($size_data >= 2)
                    {
                        $energia_activa = $data_analyzer[$size_data - 1]->energia_activa - $data_analyzer[0]->energia_activa;
                        $energia_reactiva = $data_analyzer[$size_data - 1]->energia_reactiva - $data_analyzer[0]->energia_reactiva;
                    }
                }
            }
            catch(\Exception $e)
            {
                dump($e);
            }

            if($num_registros_analizador > 0)
            {
                $potencia_activa_promedio = $potencia_activa / $num_registros_analizador;
            }

            $total_potencia_activa_promedio += $potencia_activa_promedio;
            $total_energia_activa += $energia_activa;
            $total_energia_reactiva += $energia_reactiva;
            $total_potencia_activa += $potencia_activa;
            $analyzer_stat = [];
            $analyzer_stat["id"] = $analyzer->id;
            $analyzer_stat["nombre"] = $analyzer->label;
            $analyzer_stat["color"] = $analyzer->color_etiqueta;
            $analyzer_stat["energia_activa"] = $energia_activa;
            $analyzer_stat["energia_reactiva"] = $energia_reactiva;
            $analyzer_stat["potencia_activa"] = $potencia_activa;
            $analyzer_stat["potencia_activa"] = $potencia_activa;
            $analyzer_stat["potencia_activa_promedio"] = $potencia_activa_promedio;
            $analyzers_stats[] = $analyzer_stat;
            \DB::disconnect('mysql2');
        }

        $dependencies_ids = isset($group_analyzers->dependencies_ids)?(!is_null($group_analyzers->dependencies_ids)?json_decode($group_analyzers->dependencies_ids):[]):[];
        $dependencies_first = [];
        if(count($dependencies_ids)>0){
            $dependencies_first = $this->computeDataAnalyzerGroupDependencia($dependencies_ids[0], $date_from, $date_to);
        }

        $data_return = [];
        $data_return["group_id"] = $group_analyzers->id;
        $data_return["name"] = $group_analyzers->name;
        $data_return["Sankey_resolutions"] = $group_analyzers->Sankey_resolutions;
        $data_return["file_image"] = $group_analyzers->file_image;
        $data_return["dependencies_ids"] = $dependencies_ids;
        $data_return["dependencies_first"] = $dependencies_first;
        $data_return["rest_operation"] = isset($group_analyzers->rest_operation) ? $group_analyzers->rest_operation : FALSE;
        $data_return["total_energia_activa"] = $total_energia_activa;
        $data_return["total_energia_reactiva"] = $total_energia_reactiva;
        $data_return["total_potencia_activa"] = $total_potencia_activa;
        $data_return["total_potencia_activa_promedio"] = $total_potencia_activa_promedio;
        $data_return["analyzers_stats"] = $analyzers_stats;
        return $data_return;
    }

    private function computeDataAnalyzerGroupDependencia($analyzer_id, $date_from, $date_to){
        $analyzer = Analizador::find($analyzer_id);
        if(!$analyzer)
        {
            return [];
        }

        $energia_activa = 0.0;
        $energia_reactiva = 0.0;
        $potencia_activa = 0.0;
        $potencia_activa_promedio = 0.0;
        $num_registros_analizador = 0;

        try
        {
            config(['database.connections.mysql2.host' => $analyzer->host]);
            config(['database.connections.mysql2.port' => $analyzer->port]);
            config(['database.connections.mysql2.database' => $analyzer->database]);
            config(['database.connections.mysql2.username' => $analyzer->username]);
            config(['database.connections.mysql2.password' => $analyzer->password]);
            env('MYSQL2_HOST',$analyzer->host);
            env('MYSQL2_DATABASE',$analyzer->database);
            env('MYSQL2_USERNAME', $analyzer->username);
            env('MYSQL2_PASSWORD',$analyzer->password);
            \DB::connection('mysql2')->getPdo();

            $db = \DB::connection('mysql2');

            $data_analyzer = $db->table('Analizadores_Tipo')->select(\DB::raw("`ENEact (kWh)` energia_activa,
                                        `ENErea (kVArh)` AS energia_reactiva, `POWact_Total (kW)` potencia_total,
                                        `POWact1 (kW)` potencia_1, `POWact2 (kW)` potencia_2, `POWact3 (kW)` potencia_3"))
                                    ->where('date','>=', $date_from)->where('date','<=', $date_to)->orderBy("date", "ASC")
                                    ->orderBy("time", "ASC")->get();

            if($data_analyzer)
            {
                foreach($data_analyzer as $data)
                {
                    $num_registros_analizador++;
                    if($data->potencia_total == 0)
                    {
                        $data->potencia_total = $data->potencia_1 + $data->potencia_2 + $data->potencia_3;
                    }
                    $potencia_activa += $data->potencia_total;
                }
                $size_data = count($data_analyzer);
                if($size_data >= 2)
                {
                    $energia_activa = $data_analyzer[$size_data - 1]->energia_activa - $data_analyzer[0]->energia_activa;
                    $energia_reactiva = $data_analyzer[$size_data - 1]->energia_reactiva - $data_analyzer[0]->energia_reactiva;
                }
            }
        }
        catch(\Exception $e)
        {
            dump($e);
        }

        if($num_registros_analizador > 0)
        {
            $potencia_activa_promedio = $potencia_activa / $num_registros_analizador;
        }

        $analyzer_stat = [];
        $analyzer_stat["id"] = $analyzer->id;
        $analyzer_stat["nombre"] = $analyzer->label;
        $analyzer_stat["color"] = $analyzer->color_etiqueta;
        $analyzer_stat["energia_activa"] = $energia_activa;
        $analyzer_stat["energia_reactiva"] = $energia_reactiva;
        $analyzer_stat["potencia_activa"] = $potencia_activa;
        $analyzer_stat["potencia_activa_promedio"] = $potencia_activa_promedio;

        \DB::disconnect('mysql2');

        return $analyzer_stat;
    }

    public static function getDataAnalyzer($analyzer_id, $date_from, $date_to)
    {
        $analyzer = Analizador::find($analyzer_id);
        $energia_activa = 0.0;
        $energia_reactiva = 0.0;
        if($analyzer)
        {
            try
            {
                config(['database.connections.mysql2.host' => $analyzer->host]);
                config(['database.connections.mysql2.port' => $analyzer->port]);
                config(['database.connections.mysql2.database' => $analyzer->database]);
                config(['database.connections.mysql2.username' => $analyzer->username]);
                config(['database.connections.mysql2.password' => $analyzer->password]);
                env('MYSQL2_HOST',$analyzer->host);
                env('MYSQL2_DATABASE',$analyzer->database);
                env('MYSQL2_USERNAME', $analyzer->username);
                env('MYSQL2_PASSWORD',$analyzer->password);
                \DB::connection('mysql2')->getPdo();

                $db = \DB::connection('mysql2');

                $data_analyzer = $db->table('Analizadores_Tipo')->select(\DB::raw("`ENEact (kWh)` energia_activa,
                                            `ENErea (kVArh)` AS energia_reactiva, `POWact_Total (kW)` potencia_total,
                                            `POWact1 (kW)` potencia_1, `POWact2 (kW)` potencia_2, `POWact3 (kW)` potencia_3"))
                                            ->where('date','>=', $date_from)->where('date','<=', $date_to)->orderBy("date", "ASC")
                                            ->orderBy("time", "ASC")->get();
                if($data_analyzer)
                {
                    $size_data = count($data_analyzer);
                    if($size_data >= 2)
                    {
                        $energia_activa = $data_analyzer[$size_data - 1]->energia_activa - $data_analyzer[0]->energia_activa;
                        $energia_reactiva = $data_analyzer[$size_data - 1]->energia_reactiva - $data_analyzer[0]->energia_reactiva;
                    }
                }
            }
            catch(\Exception $e)
            {
                dump($e);
            }
        }
        $data = [];
        $data["energia_activa"] = $energia_activa;
        $data["energia_reactiva"] = $energia_reactiva;
        return $data;
    }

    public static function getAnalyzerGroupDetails($group_id, $user_id, $contador)
    {
        $group_details = AnalyzerGroupDetails::where("analyzer_group_id", $group_id)->get();
        $eUser = EnterpriseUser::where("user_id", $user_id)->first();
        $enterprise = Enterprise::find($eUser->enterprise_id);

        $analyzers = [];
        if($group_details)
        {
            foreach($group_details as $detail)
            {
                $uAnalyzer = UserAnalyzers::where("enterprise_id", $enterprise->id)
                                ->where("user_id", $user_id)->where("analyzer_id", $detail->analyzer_id)->first();
                if(!$uAnalyzer)
                {
                    continue;
                }

                $aMeter = AnalyzerMeter::where("meter_id", $contador->id)->where("analyzer_id", $detail->analyzer_id)->first();

                if(!$aMeter)
                {
                    continue;
                }

                $analyzer = Analizador::find($aMeter->analyzer_id);
                if(!$analyzer)
                {
                    continue;
                }

                $analyzer_data = [];
                $analyzer_data["id"] = $analyzer->id;
                $analyzer_data["name"] = $analyzer->label;
                $analyzer_data["color"] = $analyzer->color_etiqueta;
                $analyzers[] = $analyzer_data;
            }
        }

        return $analyzers;
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
}
