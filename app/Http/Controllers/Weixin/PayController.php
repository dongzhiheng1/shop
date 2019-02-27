<?php

namespace App\Http\Controllers\Weixin;

use App\Model\OrderModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Weixin\WXBizDataCryptController;

class PayController extends Controller
{
    public $weixin_unifiedorder_url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
    //通知回调
     public $weixin_notify_url='https://dzh.wangby.cn/weixin/pay/notice';
    public function test($order_id)
    {
        //
        $total_fee = 1;         //用户要支付的总金额
      $where=[
          'order_id'=>$order_id
      ];
        $orderInfo=OrderModel::where($where)->first();
        $order_info = [
            'appid'         =>  env('WEIXIN_APPID_0'),      //微信支付绑定的服务号的APPID
            'mch_id'        =>  env('WEIXIN_MCH_ID'),       // 商户ID
            'nonce_str'     => str_random(16),             // 随机字符串
            'sign_type'     => 'MD5',
            'body'          => '测试订单-'.mt_rand(1111,9999) . str_random(6),
            'out_trade_no'  => $orderInfo['order_number'],                       //本地订单号
            'total_fee'     => $total_fee,
            'spbill_create_ip'  => $_SERVER['REMOTE_ADDR'],     //客户端IP
            'notify_url'    => $this->weixin_notify_url,        //通知回调地址
            'trade_type'    => 'NATIVE'                         // 交易类型
        ];
        $this->values = [];
        $this->values = $order_info;
        $this->SetSign();

        $xml = $this->ToXml();      //将数组转换为XML
        $rs = $this->postXmlCurl($xml, $this->weixin_unifiedorder_url, $useCert = false, $second = 30);
        $data =  simplexml_load_string($rs);
        $info=[
            'url'=>$data->code_url,
            'order_number'=>$orderInfo['order_number']
        ];
//       echo 'code_url: '.$data->code_url;echo '<br>';die;
        return view('weixin.wxpay',$info);
//        die;
        //echo '<pre>';print_r($data);echo '</pre>';
        //将 code_url 返回给前端，前端生成 支付二维码
    }
    public function ToXml(){
        if(!is_array($this->values) || count($this->values)<=0){
            die("数组数据异常!");
        }
        $xml='<xml>';
        foreach($this->values as $key=>$val){
            if(is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }
    private  function postXmlCurl($xml, $url, $useCert = false, $second = 30)
    {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
//		if($useCert == true){
//			//设置证书
//			//使用证书：cert 与 key 分别属于两个.pem文件
//			curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
//			curl_setopt($ch,CURLOPT_SSLCERT, WxPayConfig::SSLCERT_PATH);
//			curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
//			curl_setopt($ch,CURLOPT_SSLKEY, WxPayConfig::SSLKEY_PATH);
//		}
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            die("curl出错，错误码:$error");
        }
    }
    //签名
    public function SetSign(){
        $sign=$this->MakeSign();
        $this->values['sign']=$sign;
        return $sign;
    }
    private function MakeSign(){
        //签名步骤一;按字典排序参数
        ksort($this->values);
        $string=$this->ToUrlParams();
        ///签名步骤二;在string后加入KEY
        $string=$string."&key=".env('WEIXIN_MCH_KEY');
        //签名步骤三；MD5加密
        $string=md5($string);
        //签名步骤四;所有字符转为大写
        $result=strtoupper($string);
        return $result;
    }
    //格式化参数格式成url参数
    protected  function  ToUrlParams(){
        $buff="";
        foreach($this->values as $k=>$v){
            if($k !="sign" && $v !="" && !is_array($v)){
                $buff.=$k ."=".$v."&";
            }
        }
        $buff=trim($buff,"&");
        return $buff;
    }
    //微信支付回调
    public function notice(){
        $data=file_get_contents("php://input");
        //记录日志
        $log_str=date('Y-m-d H:i:s')."\n".$data."\n<<<<<<";
        file_put_contents('logs/wx_pay_notice.log',$log_str,FILE_APPEND);
        $xml=simplexml_load_string($data);
        if($xml->result_code=='SUCCESS' && $xml->return_code=='SUCCESS'){
            //微信支付回调
            //验证签名
            $sign=true;
            if($sign){
                //签名验证成功
                //TODO 逻辑处理 订单状态更新
            }else{
                //TODO 验签失败
                echo "非法操作";
                //TODO 记录日志
            }
        }
        $response = '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
        echo response;
    }

}
