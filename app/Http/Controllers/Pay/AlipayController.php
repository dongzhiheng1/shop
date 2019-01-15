<?php

namespace App\Http\Controllers\Pay;

use App\Model\OrderModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use SebastianBergmann\ObjectReflector\ObjectReflectorTest;

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
    public function pay($order_id){
        $OrderInfo=OrderModel::where(['order_id'=>$order_id])->first()->toArray();
        print_r($OrderInfo);die;
        if($OrderInfo['is_pay']==1){
            die('订单已支付,请勿重复支付');
        }
        if($OrderInfo['is_delete']==1){
            die('订单已删除,无法正常支付');
        }
        $bizcont=[
            'subject'=>'goods'.$order_id,
            'out_trade_no'=>$order_id,
            'total_amount'=>$OrderInfo['order_amount']/100,
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
       header('refresh:1;url=/order/list');
       echo "订单".$_GET['out_trade_no'].'支付成功，正在跳转';

        //处理订单逻辑
        $this->dealOrder($_GET);
    }
    public function aliNotify(){
        $data=json_encode($_POST);
        $log_str= '>>>>'.date('Y-m-d H:i:s').$data."<<<<\n\n";
        //记录日志
        file_put_contents('logs/alipay.log',$log_str,FILE_APPEND);
        $res=$this->verify($_POST);
        if($res===false){
            //记录日志 验签失败
            $log_str .= " Sign Failed!<<<<< \n\n";
            file_put_contents('logs/alipay.log',$log_str,FILE_APPEND);
        }else{
            $log_str .= " Sign OK!<<<<< \n\n";
            file_put_contents('logs/alipay.log',$log_str,FILE_APPEND);
        }
        //验证订单状态
        if($_POST['trade_status']=='TRADE_SUCCESS'){
            //更新订单状态
            $oid=$_POST['out_trade_no'];
            $info=[
                'is_pay'=>1,//支付状态 0未支付 1已支付
                'pay_amount'=>$_POST['total_amount']*100,//支付金额
                'pay_time'=>strtotime($_POST['gmt_payment']),//支付时间
                'plat_oid'=>$_POST['trade_no'], //支付宝订单号
                'plat'=>1, //平台编号 1支付宝 2微信
            ];
            OrderModel::where(['order_id'=>$oid])->update($info);
        }
        //处理订单逻辑
        $this->dealOrder($_POST);
        echo "success";
    }
    //验签
    function verify($params) {
        $sign = $params['sign'];
        $params['sign_type'] = null;
        $params['sign'] = null;

        //读取公钥文件
        $pubKey = file_get_contents($this->aliPubKey);
        $pubKey = "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($pubKey, 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";
        //转换为openssl格式密钥

        $res = openssl_get_publickey($pubKey);
        ($res) or die('支付宝RSA公钥错误。请检查公钥文件格式是否正确');

        //调用openssl内置方法验签，返回bool值

        $result = (openssl_verify($this->getSignContent($params), base64_decode($sign), $res, OPENSSL_ALGO_SHA256)===1);
        openssl_free_key($res);

        return $result;
    }

    //处理订单逻辑 更新订单 支付状态 更新订单支付金额 支付时间
    public function dealOrder($data){
        //加积分

        //减库存

    }
}
