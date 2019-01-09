<?php

namespace App\Http\Controllers\Cart;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\CartModel;
use App\Model\GoodsModel;
use DB;
class IndexController extends Controller
{
    //
    public function  __construct(){

    }
    public function index(Request $request){
//        $goods=session()->get('cart_goods');
//        if(empty($goods)){
//            echo "购物车为空";
//        }else{
//            foreach($goods as $k=>$v){
//                $detail=GoodsModel::where(['goods_id'=>$v])->first()->toArray();
//                print_r($detail);
//            }
//
//        }
        $uid=session()->get('uid');
        $cart_goods=CartModel::where(['uid'=>$uid])->get()->toArray();
        if(empty($cart_goods)){
            die("购物车为空");
        }
        if($cart_goods){
            foreach ($cart_goods as $k=>$v){
                $goodsInfo=GoodsModel::where(['goods_id'=>$v])->first()->toArray();
                $goodsInfo['goods_num']=$v['goods_num'];
                $list[]=$goodsInfo;
            }
        }
        $data=[
            'list'=>$list
        ];
        return view('cart.index',$data);
    }
//    //添加商品
//    public function add($goods_id){
//        $cart_goods=session()->get('cart_goods');
//        if(!empty($cart_goods)){
//            if(in_array($goods_id,$cart_goods)){
//                echo "已存入购物车";exit;
//            }
//        }
//        session()->push('cart_goods',$goods_id);
//
//        //减库存
//        $where=['goods_id'=>$goods_id];
//        $goods_num=GoodsModel::where($where)->value('goods_num');
//        if($goods_num<=0){
//            echo "库存不足";exit;
//        }
//        $res=GoodsModel::where(['goods_id'=>$goods_id])->decrement('goods_num');
//        if($res){
//            echo "加入购物车成功";
//        }else{
//            echo "加入购物车成功";
//        }
//
//    }
    //删除商品
    public function del($goods_id){
        //判断商品是否在购物车中
        $goods=session()->get('cart_goods');
        if(in_array($goods_id,$goods)){
            foreach($goods as $k=>$v){
                if($goods_id==$v){
                   session()->pull('cart_goods.'.$k);
                   echo "删除成功";
                   header('refresh:1;url=/cart/index');
                }
            }
        }else{
            echo "商品已不在购物车中";
        }
    }
    public function add2(Request $request)
    {
        $goods_id=$request->input('goods_id');
        $buy_num=$request->input('buy_num');
        //检查库存
        $goods_num=GoodsModel::where(['goods_id'=>$goods_id])->value('goods_num');
        if($buy_num>=$goods_num){
           $response=[
               'errno'=>5001,
               'msg'=>'库存不足',
           ];
           return $response;
        }
        //写入购物车表
        $data=[
            'goods_id'=>$goods_id,
            'buy_num'=>$buy_num,
            'add_time'=>time(),
            'uid'=>session()->get('uid'),
            'session_token'=>session()->get('u_token')
        ];
        $cid=CartModel::insertGetId($data);
        if(!$cid){
            $response=[
                'errno'=>5002,
                'msg'=>'添加购物车失败,请重试',
            ];
            return $response;
        }
        $response=[
            'errno'=>0,
            'msg'=>'添加成功',
        ];
        return $response;

    }

}
