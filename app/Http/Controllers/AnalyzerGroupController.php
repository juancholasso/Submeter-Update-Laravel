<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\AnalyzerGroup;
use App\AnalyzerGroupDetails;
use Validator;

class AnalyzerGroupController extends Controller
{
    public function show(Request $request, $analyzer_group_id)
    {
        $aGroup = AnalyzerGroup::find($analyzer_group_id);
        
        $data = [];
        $data["id"] = $aGroup->id;
        $data["name"] = $aGroup->name;
        $data["file_image"] = asset($aGroup->file_image);
        $data["dependencies_ids"] = is_null($aGroup->dependencies_ids) ? [] : json_decode($aGroup->dependencies_ids);
        $data["rest_operation"] = is_null($aGroup->rest_operation) ? FALSE : $aGroup->rest_operation;
        
        $data_analyzers = [];
        $analyzers = $aGroup->analyzers;
        if($analyzers) {
            foreach($analyzers as $analyzer) {
                $data_analyzers[] = $analyzer->analyzer_id;
            }
        }
        $dataSend = [];
        $dataSend["error"] = false;
        $dataSend["data"] = $data;
        $dataSend["analyzers"] = $data_analyzers;
        return $dataSend;
    }
    
    public function save(Request $request)
    {
        $messages = [
            'name.required' => 'Debes agregar un nombre'
        ];
        
        $v = Validator::make($request->all(), [
            'name' => 'required'
        ],$messages);
        
        if ($v->fails())
        {
            $data = [];
            $data["error"] = true;
            $data["messages"] = $v->errors();
            return $data;
        }
        
        $aGroup = new AnalyzerGroup;
        $aGroup->name = request("name");
        
        if($request->hasFile("image_analyzer"))
        {
            $file = $request->file('image_analyzer');
            $extension = $file->getClientOriginalExtension();
            $filename =time().'.'.$extension;
            $file->move(public_path("images/uploaded_schemes"), $filename);
            $aGroup->file_image = "images/uploaded_schemes/". $filename;
        }

        $dependencyArray = [];
        $dependencies = $request->get("dependencies", []);
        foreach($dependencies as $dependency){
            if(!empty($dependency))
                $dependencyArray[] = (int)$dependency;
        }
        $aGroup->dependencies_ids = json_encode($dependencyArray);
        $aGroup->rest_operation = $request->has('rest_operation') ? TRUE : FALSE;

        $aGroup->save();
        
        AnalyzerGroupDetails::where("analyzer_group_id", $aGroup->id)
                ->update(["updated" => 0]);
        $analyzers = $request->get("analyzers", []);
        foreach($analyzers as $analyzer)
        {
            $aDetail = AnalyzerGroupDetails::where("analyzer_group_id", $aGroup->id)
                                ->where("analyzer_id", $analyzer)->first();
            if(!$aDetail)
            {
                $aDetail = new AnalyzerGroupDetails;
                $aDetail->analyzer_group_id = $aGroup->id;
                $aDetail->analyzer_id = $analyzer;
            }
            $aDetail->updated = 1;
            $aDetail->save();
        }
        
        AnalyzerGroupDetails::where("analyzer_group_id", $aGroup->id)
                ->where("updated", 0)
                ->delete();
        
        $data = [];
        $data["error"] = false;
        $data["data"] = $aGroup;
        return $data;
    }
    
    public function update(Request $request, $analyzer_group_id)
    {
        $aGroup = AnalyzerGroup::find($analyzer_group_id);
        if(!$aGroup)
        {
            $data = [];
            $data["error"] = true;
            $data["messages"] = [];
            return $data;
        }
        
        $messages = [
            'id.required' => 'ID Requerido',
            'id.numeric' => 'ID debe ser numerico', 
            'name.required' => 'Debes agregar un nombre'
        ];
        
        $v = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'name' => 'required'
        ],$messages);
        
        if ($v->fails())
        {
            $data = [];
            $data["error"] = true;
            $data["messages"] = $v->errors();
            return $data;
        }
        
        $aGroup->name = request("name");
        if($request->hasFile("image_analyzer"))
        {
            $file = $request->file('image_analyzer');
            $extension = $file->getClientOriginalExtension();
            $filename =time().'.'.$extension;
            $file->move(public_path("images/uploaded_schemes"), $filename);
            $aGroup->file_image = "images/uploaded_schemes/". $filename;
        }

        $dependencyArray = [];
        $dependencies = $request->get("dependencies", []);
        foreach($dependencies as $dependency){
            if(!empty($dependency))
                $dependencyArray[] = (int)$dependency;
        }
        $aGroup->dependencies_ids = json_encode($dependencyArray);
        $aGroup->rest_operation = $request->has('rest_operation') ? TRUE : FALSE;

        $aGroup->save();
        
        AnalyzerGroupDetails::where("analyzer_group_id", $aGroup->id)
                                ->update(["updated" => 0]);
        
        $analyzers = $request->get("analyzers", []);
        foreach($analyzers as $analyzer)
        {
            $aDetail = AnalyzerGroupDetails::where("analyzer_group_id", $aGroup->id)
            ->where("analyzer_id", $analyzer)->first();
            if(!$aDetail)
            {
                $aDetail = new AnalyzerGroupDetails;
                $aDetail->analyzer_group_id = $aGroup->id;
                $aDetail->analyzer_id = $analyzer;
            }
            $aDetail->updated = 1;
            $aDetail->save();
        }
        
        AnalyzerGroupDetails::where("analyzer_group_id", $aGroup->id)
                                ->where("updated", 0)
                                ->delete();
        
        $data = [];
        $data["error"] = false;
        $data["data"] = $aGroup;
        return $data;
    }
    
    public function delete(Request $request, $analyzer_group_id)
    {
        $aGroup = AnalyzerGroup::find($analyzer_group_id);
        if(!$aGroup)
        {
            $data = [];
            $data["error"] = true;
            $data["messages"] = [];
            return $data;
        }
        $aGroup->delete();
        
        $data = [];
        $data["error"] = false;
        return $data;
    }
    
    public function getAnalyzersGroupList()
    {
        $analyzer_groups = AnalyzerGroup::all();
        $data_analyzers_groups = [];
        foreach($analyzer_groups as $group)
        {
            $option = ["value"=>$group->id, "name"=>$group->name];
            $data_analyzers_groups[] = $option;
        }        
        $data = [];
        $data["error"] = false;
        $data["options"] = $data_analyzers_groups;
        return $data;
    }
}
