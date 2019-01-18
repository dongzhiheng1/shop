<?php

namespace App\Http\Controllers\Cart;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\CartModel;
use App\Model\GoodsModel;
use DB;
use Illuminate\Support\Facades\Auth;

class IndexController extends Controller
{
    public $uid;
    public $cart_goods;
    public function  __construct(){
        $this->middleware(function($request,$next){
            $this->uid=Auth::id();
            return $next($request);
        });
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
        $uid=Auth::id();
        $cart_goods=CartModel::where(['uid'=>$uid])->get()->toArray();
        if(empty($cart_goods)){
            die("购物车为空");
        }
        if($cart_goods){
            foreach ($cart_goods as $k=>$v){
                $goodsInfo=GoodsModel::where(['goods_id'=>$v['goods_id']])->first()->toArray();
                $goodsInfo['goods_num']=$v['buy_num'];
                $list[]=$goodsInfo;
            }
            //print_r($goodsInfo);
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
//    public function del($goods_id){
//        //判断商品是否在购物车中
//        $goods=session()->get('cart_goods');
//        if(in_array($goods_id,$goods)){
//            foreach($goods as $k=>$v){
//                if($goods_id==$v){
//                   session()->pull('cart_goods.'.$k);
//                   echo "删除成功";
//                   header('refresh:1;url=/cart/index');
//                }
//            }
//        }else{
//            echo "商品已不在购物车中";
//        }
//    }
    //添加
    public function add2(Request $request)
    {
        $goods_id=$request->input('goods_id');
        $buy_num=$request->input('buy_num');
        //检查库存
        $goods_num=GoodsModel::where(['goods_id'=>$goods_id])->value('goods_num');
        if($buy_num>=$goods_num||$goods_num<=0){
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
            'uid'=>$this->uid,
            'session_token'=>session()->get('u_token')
        ];
        $where=[
            'uid'=>$this->uid,
            'goods_id'=>$goods_id
        ];
        $arr=CartModel::where($where)->first();
        $num=$arr['buy_num'];
        if($arr){
            $data2=[
                'buy_num'=>$buy_num+$num,
                'add_time'=>time(),
                'session_token'=>session()->get('u_token')
            ];
            $res=CartModel::where($where)->update($data2);
            if(!$res){
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
        }else{
            $getid=CartModel::insertGetId($data);
            if(!$getid){
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
        //删除
        public function del2($goods_id){
                $res=CartModel::where(['goods_id'=>$goods_id,'uid'=>$this->uid])->delete();
                if($res){
                    echo "删除成功";
                    header("refresh:1;url=/cart/index");
                }else{
                    echo "删除成功";
                    header("refresh:1;url=/cart/index");
                }
        }
}
