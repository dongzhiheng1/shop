<?php

namespace App\Http\Controllers\Order;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\CartModel;
use App\Model\GoodsModel;
use App\Model\OrderModel;
use Ramsey\Uuid\Codec\OrderedTimeCodec;

class IndexController extends Controller
{
    public function index(){
        echo __METHOD__;
    }
    //下单
    public function add(Request $request){
        //查询购物车中的商品
        $goods=CartModel::where(['uid'=>session()->get('uid')])->orderBy('cart_id','desc')->get()->toArray();
        if(empty($goods)){
            die("购物车中无商品");
        }
        $order_amount=0;
        foreach($goods as $k=>$v){
            $goodsInfo=GoodsModel::where(['goods_id'=>$v['goods_id']])->first()->toArray();
            $goodsInfo['goods_num']=$v['buy_num'];
            $list[]=$goodsInfo;
            //计算订单价格 = 商品数量*单价
            $order_amount+=$goodsInfo['goods_price']*$v['buy_num'];
        }
        //生成订单号
        $order_number=OrderModel::generateOrderSN();
        $data=[
            'order_number'=>$order_number,
            'uid'=>session()->get('uid'),
            'add_time'=>time(),
            'order_amount'=>$order_amount
        ];
        $oid=OrderModel::insertGetId($data);
        if(!$oid){
            echo "生成订单失败";
        }
        echo "下单成功,您的订单号为：".$order_number.'跳转支付';

        //清空购物车
        CartModel::where(['uid'=>session()->get('uid')])->delete();

    }
}
