<?php
namespace App\Components\Api;

use Illuminate\Http\Request;

class DetectJson{

    public static function getData(Request $request){
        $data = $request->all();
        if(!empty($data)){
            return $data;
        }
        $data = $request->getContent() ?? [];
        if(!empty($data)){
            return json_decode($data,true);
        }
        return null;
    }
}
