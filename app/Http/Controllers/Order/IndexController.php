<?php

namespace App\Http\Controllers\Order;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\CartModel;
use App\Model\GoodsModel;
use App\Model\OrderModel;
use Ramsey\Uuid\Codec\OrderedTimeCodec;
use Illuminate\Support\Facades\Auth;

class IndexController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index(){
        echo __METHOD__;
    }
    //下单
    public function add(Request $request){
        //查询购物车中的商品
        $goods=CartModel::where(['uid'=>Auth::id()])->orderBy('cart_id','desc')->get()->toArray();
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
            'uid'=>Auth::id(),
            'add_time'=>time(),
            'order_amount'=>$order_amount
        ];
        $oid=OrderModel::insertGetId($data);
        if(!$oid){
            echo "生成订单失败";
        }
        echo "下单成功,您的订单号为：".$order_number.'跳转支付';
        header('refresh:1;url=/order/list');
        //清空购物车
        CartModel::where(['uid'=>Auth::id()])->delete();

    }
    //订单展示
    public function list(){
        $uid=Auth::id();
        $where=[
            'uid'=>$uid
        ];
        $list=OrderModel::where($where)->get();
        if(empty($list)){
            die("订单为空");
        }
        $data=[
            'list'=>$list
        ];
        return view('order.index',$data);
    }
}
