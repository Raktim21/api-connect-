<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    public function index(){

        if (request()->search && request()->search != null) {
            $data = Http::get('https://oga.fasah.sa/api/applicationSupport/api/trader?id='.request()->search)->json()['listImporterNebras']['importerNebras'];

            $keys = [
                "commrgstrtntype",
                "hqimprnbr",
                "imprexprtype",
                "arbcimprname",
                "busid",
                "busidbrnch",
                "busidexprddt",
                "msg"
            ];
            
            
            if (empty($data)) {
                $final_datas = [];
            }else {
                
                $result = array_map(function($item) use ($keys) {
                    return array_intersect_key($item, array_flip($keys));
                }, $data);
        
                $final_datas  = $data = array_map("unserialize", array_unique(array_map("serialize", $result)));
            }
            
        }else {
            $final_datas = [];
        }
        
        return view('admin.index',compact('final_datas'));
    }



    public function importerIndex(Request $request){


        if (request()->search && request()->search != null) {
            
            $data = Http::get('https://oga.fasah.sa/api/applicationSupport/api/trader?importerNumber='.request()->search.'&port='. Auth::user()->port)->json();
            
            $key = [
                "hqimprnbr",
                "busid",
                "busidbrnch",
                "busidexprddt",
                "imprexprtype",
                "arbcimprname",
            ];



            if (empty($data)) {
                $final_datas = [];
            }else {
                
                $result = array_map(function($item) use ($key) {
                    return array_intersect_key($item, array_flip($key));
                }, $data);
            
    
                $final_datas  = $data = array_map("unserialize", array_unique(array_map("serialize", $result)));
            }
        }else {
            $final_datas = [];
        }

        
        return view('admin.importer',compact('final_datas'));
    }
}
