<?php

namespace App\Http\Controllers\Pay;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client;

class AlipayController extends Controller
{
    public $app_id =null;
    public $gate_way ;
    public $notify_url ;
    public $return_url ;
    public $rsaPrivateKeyFilePath = './key/priv.key';
    public $aliPubkey = './key/ali_pub.key';

    public function __construct()
    {

        $this->app_id=env('ALIPAY_APPID');
        $this->gate_way=env('ALIPAY_GATEWAY');
        $this->notify_url=env('ALIPAY_NOTIFY');
        $this->return_url=env('ALIPAY_RETURN');
    }

    //请求订单服务 处理订单逻辑
    public function test(){
        $bizcont=[
            'subject'=>'ancsd'.mt_rand(1111,9999).str_random(6),
            'out_trade_no'=>'order_id'.date('YmdHis').mt_rand(1111,2222),
            'total_amount'=>0.01,
            'product_code'=>'QUICK_WAP_WAY',
        ];
        $data=[
            'app_id'=>$this->app_id,
            'method'=>'alipay.trade.wap.pay',
            'format'=>'JSON',
            'charset'=>'utf-8',
            'sign_type'=>'RSA2',
            'timestamp'=>date('Y-m-d H:i:s'),
            'version'=>'1.0',
            'return_url'=>$this->return_url,//同步通知
            'notify_url'=>$this->notify_url,//异步通知
            'biz_content'=>json_encode($bizcont),
        ];
        $sign=$this->rsaSign($data);
        $data['sign'] = $sign;
        $param_str='?';
        foreach($data as $k=>$v){
            $param_str.=$k.'='.urlencode($v).'&';
        }
        $url=rtrim($param_str,'&');
        $url=$this->gate_way.$url;
        header('Location:'.$url);
    }
    public function rsaSign($params){
        return $this->sign($this->getSignContent($params));
    }
   protected function sign($data){
        $priKey=file_get_contents($this->rsaPrivateKeyFilePath);
        $res=openssl_get_privatekey($priKey);
        ($res) or die('您使用的私钥格式错误,请检查RSA私钥配置');
        openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256);
        if(!$this->checkEmpty($this->rsaPrivateKeyFilePath)){
            openssl_free_key($res);
        }
        $sign=base64_encode($sign);
        return $sign;
    }
    public function getSignContent($params){
        ksort($params);
        $stringToBeSigned="";
        $i=0;
        foreach ($params as $k=>$v){
            if(false===$this->checkEmpty($v)&&'@'!=substr($v,0,1)){
                //转换成目标字符集
                $v=$this->characet($v,'UTF-8');
                if($i==0){
                    $stringToBeSigned .="$k"."="."$v";
                }else{
                    $stringToBeSigned .="&" ."$k"."="."$v";
                }
                $i++;
            }
        }
        unset($k,$v);
        return $stringToBeSigned;
    }
    protected function checkEmpty($value){
        if(!isset($value))
            return true;
        if(!$value===null)
            return true;
        if(trim($value)==="")
            return true;

        return false;
    }

    //转换字符集编码
    function characet($data,$targetCharset){
        if(!empty($data)){
            $fileType='UTF-8';
            if(strcasecmp($fileType,$targetCharset)!=0){
                $data=mb_convert_encoding($data,$targetCharset,$fileType);
            }
        }
        return $data;
    }
    //支付宝同步通知回调
    public function aliReturn()
    {
        echo '<pre>';print_r($_GET);echo '</pre>';
        //验签 支付宝的公钥
        if(!$this->verify()){
            echo 'error';
        }

        //处理订单逻辑
        $this->dealOrder($_GET);
    }
    public function aliNotify(){
        $data=json_encode($_POST);
        $log_str= '>>>>'.date('Y-m-d H:i:s').$data."<<<<\n\n";
        //记录日志
        file_put_contents('logs/alipay.log',$log_str,FILE_APPEND);
        $res=$this->verify();
        if($res===false){
            echo "error";
            //记录日志 验签失败
        }

        //处理订单逻辑
        $this->dealOrder($_POST);
        echo "success";
    }
    //验签
    function verify(){
        return true;
    }

    //处理订单逻辑 更新订单 支付状态 更新订单支付金额 支付时间
    public function dealOrder($data){
        //加积分

        //减库存

    }
}
