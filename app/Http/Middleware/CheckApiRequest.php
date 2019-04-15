<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Redis;
class CheckApiRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    private $_api_data=[];
    private $_blank_list='blank_list';
    public function handle($request, Closure $next)
    {

//        echo 111;die;
        //获取接口数据，需要先解密
      $this->_rsaDecrypt($request);
//        var_dump($res);die;
//        print_r($this->_api_data);exit;
        $not_check=[
            'showVcode'
        ];
        $arr=explode('/',$request->path());
        $route=array_shift($arr);
        if(!in_array($route ,$not_check)){
            $data1=$this->_checkApiAccessCount();
            if($data1['status']!=1000){
                return response($data1);
            }
        }
//        //访问次数限制
        //验证签名
        $data=$this->_checkSign($request);
//     var_dump($data);die;
        //判断签名是否正确
        if($data['status']==1000){
            $request->request->replace((array) $this->_api_data);
            $response=$next($request);
            $api_response=[];
            //传到客户端数据
//            $api_response['data']=$this->_encrypt($response->original);
            $api_response['data']=$this->_rsaEncrypt($response->original);
            //传到客户端签名
            $api_response['sign']=$this->_createSign($response->original);
//            var_dump($api_response);die;
            return  response($api_response);
        }

//        return $next($request);
        //把解密的数据传递到控制器
    }
    //私钥加密
    private  function _rsaEncrypt($data){
        $i=0;
        $all_encrypt='';
        $str=json_encode($data);
        while($sub_str=substr($str,$i,117)){
            openssl_private_encrypt($sub_str,
                $encrypt_data,
                file_get_contents('./private.key'),
                OPENSSL_PKCS1_PADDING
            );
            $all_encrypt.=base64_encode($encrypt_data);
            $i+=117;
        }
//        var_dump($encrypt_data);die;
        return  $all_encrypt;
    }
     //私钥解密
    private  function _rsaDecrypt($request)
    {
        $data = $request->input("data");
        if (!empty($data)) {
            //非对称解密
            $i = 0;
            $data_api = "";
            $decrypt_data='';
            while ($sub_str = substr($data, $i, 172)) {
                $decode_data=base64_decode($sub_str);
                openssl_private_decrypt($decode_data, $decrypt_data, file_get_contents("./private.key"), OPENSSL_PKCS1_PADDING);
                $data_api.=$decrypt_data;
                $i+=172;
//                var_dump($data_api);die;

            }
//           echo $data_api;die;
            $this->_api_data =json_decode($data_api,true);
//            var_dump($this->_api_data) ;die;
            return $this->_api_data;

//        $i=0;
//        $all_decrypt='';
//        $decrypt_data='';
//        while($substr=substr($request->post('data'),$i,172)){
//            $decode_data=base64_decode($substr);
//            openssl_private_decrypt(
//                $decode_data,
//                $decrypt_data,
//                file_get_contents('./private.key'),
//                OPENSSL_PKCS1_PADDING
//            );
//            $all_decrypt.=$decrypt_data;
//            $i+=172;
//        }
//        $this->_api_data=json_decode($all_decrypt,true);
        }
    }

//    //把数据加密
//    private  function _encrypt($data){
//        if(!empty($data)){
//            $encrypt_data=openssl_encrypt(json_encode($data),
//                'AES-256-CBC',
//                'lisi',
//                false,
//                '0123456701234567'
//            );
//            return $encrypt_data;
//        }
//    }


    //解密
    private  function _decrypt($request){
        $data=$request->post('data');
        if(!empty($data)){
            $decrypt_data=openssl_decrypt($data,
                'AES-256-CBC',
                'lisi',
                false,
                '0123456701234567'
            );
            $this->_api_data=json_decode( $decrypt_data,true);
        }
    }
//    private function _RsaDecrypt($request){
//    //非对称加密
//    }
//    //验证签名
    private  function _checkSign($request){

        if(!empty($this->_api_data)){
            $app_id=$this->_api_data['app_id'];
//            echo 1;die;
            //获取当前所有的app_id和key
            //生成服务器端的签名
            $map=$this->_getAppIdKey();
            //判断appid是否存在
            if(!array_key_exists($this->_api_data['app_id'],$map)){
                return [
                    'status'=>1,
                    'msg'=>'check sign fail',
                    'data'=>[]
                ];
            }
            //服务器生成签名
            //生成服务器端签名
            ksort($this->_api_data);
            if(!$app_id){
                return [];
            }
            //变成字符串 拼接app-key
            $server_str=http_build_query($this->_api_data).'&app_key='.$map[$this->_api_data['app_id']];
                if(md5($server_str)!=$request['sign']){
                    return [
                        'status'=>2,
                        'msg'=>'check sign fail2',
                        'data'=>[]
                    ];
                }
           return ['status'=>1000];
        }

    }

    //服务器端返回时返回的签名
    private function _createSign($data){
        if(!is_array($data)){
            $data=(array)$data;
        }
        $app_id=$this->_getAppId();
        $all_id=$this->_getAppIdKey();
       //排序
        ksort($data);
        //变成字符串 拼接app-key
        $sign_str=http_build_query($data).'&app_key='.$all_id[$app_id];
        return md5( $sign_str);
    }
   //获取系统现有的appid和key
    private  function _getAppIdKey(){
        //从数据库获取对应的数据
       return [
           md5(1)=>md5('123456')
       ];
    }
    //获取当前调用接口的appid
   private function _getAppId(){
     if(empty($this->_api_data['app_id'])){
         return  '';
     }else{
         return $this->_api_data['app_id'];
     }
   }
    //接口防刷
    private function _checkApiAccessCount(){
      //先获取appid
//        echo 1;die;
        $app_id=$this->_getAppId();
        //判断是否在黑名单中
        $blank_key=$this->_blank_list;
        //判断是否在黑名单中
        $add_blank_time=Redis::zScore($blank_key,$app_id);
        //不在黑名单继续走
        if(empty($add_blank_time)){
            $this->_addAppIdAccessCount();
        }else{
            //判断是否超过30分钟
            if(time()-$add_blank_time >=30){
                Redis::zRemove($blank_key,$app_id);
                $this->_addAppIdAccessCount();
            }else{
                return [
                    'status'=>3,
                    'msg'=>'暂时不能访问该接口,请稍后再试',
                    'data'=>[]
                ];
            }

        }
        return ['status'=>1000];

    }
    //记录appid对应的访问次数
    public function _addAppIdAccessCount(){
//        echo 1;
        $count=Redis::incr($this->_getAppId());
        if($count==1){
            //echo 1;
            Redis::Expire($this->_getAppId(),3600);
        }
        //大于等于100，加入黑名单
        if($count>=100){
            Redis::zAdd($this->_blank_list,time(),$this->_getAppId());
            Redis::del($this->_getAppId());
            return [
                'status'=>3,
                'msg'=>'暂时不能访问该接口,请稍后再试',
                'data'=>[]
            ];
        }
    }
}
