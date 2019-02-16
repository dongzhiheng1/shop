<?php

namespace App\Http\Controllers\Movie;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;

class IndexController extends Controller
{

    public function index(){
        $key="test_bit";
        $data=[];
        for($i=0;$i<=30;$i++){
            $status=Redis::getbit($key,$i);
            $data[$i]=$status;
        }
        $info=[
            'title'=>'电影选座',
            'data'=>$data
        ];
        return view('movie.index',$info);
    }

    public function buy($pos,$status){
        $key='test_bit';
        Redis::setbit($key,$pos,1);
        echo "购票成功";
        header('refresh:1;url=/movie/show');
    }
}
