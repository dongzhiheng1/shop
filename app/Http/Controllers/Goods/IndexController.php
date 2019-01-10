<?php

namespace App\Http\Controllers\Goods;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\GoodsModel;

class IndexController extends Controller
{
    public function index($goods_id){
        $goods=GoodsModel::where(['goods_id'=>$goods_id])->first();
        $data=[
            'goods'=>$goods
        ];
        return view('goods.index',$data);
    }
    public function list(){
        $list=GoodsModel::get()->toArray();
//        var_dump($list);die;
        $data=[
            'list'=>$list
        ];
        return view('goods.list',$data);
    }
}
