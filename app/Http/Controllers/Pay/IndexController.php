<?php

namespace App\Http\Controllers\Pay;

use App\Model\OrderModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class IndexController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index(){

    }
    //订单支付
    public function order($order_id){
        //查询订单
        $where=[
            'order_id'=>$order_id
        ];
        $orderInfo=OrderModel::where($where)->first();
        if(!$orderInfo){
            die('订单不存在');
        }
        if($orderInfo->pay_time>0){
            die("此订单已被支付");
        }
        //调用支付宝


        //支付成功 修改支付时间
        $data=[
            'pay_time'=>time(),
            'pay_amount'=>rand(1111,9999),
            'is_pay'=>1,
            'order_status'=>2
        ];
        OrderModel::where(['order_id'=>$order_id])->update($data);

        //增加修改积分
        header('refresh:1;url=/order/list');
        echo "支付成功,正在跳转";
    }
}
