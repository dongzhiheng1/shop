<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class IndexController extends Controller
{
   public function api(Request $request){
     return ['status'=>1000,'msg'=>'success','data'=>[]];
   }
}
