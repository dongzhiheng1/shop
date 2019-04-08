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
        //获取接口数据，需要先解密
        $this->_decrypt($request);
//        //访问次数限制
        $data1=$this->_checkApiAccessCount();
        if($data1['status']!=1000){
            return response($data1);
        }
        //验证签名
        $data=$this->_checkSign($request);
//        var_dump($data);die;
        //判断签名是否正确
        if($data['status']==1000){
            $response=$next($request);
            $api_response=[];
            //传到客户端数据
            $api_response['data']=$this->_encrypt($response->original);
//            var_dump($api_response);die;
            //传到客户端签名
            $api_response['sign']=$this->_createSign($response->original);
            return  response($api_response);
        }
        $request->request->replace($this->_api_data);
        return $next($request);
        //把解密的数据传递到控制器

    }


    //把数据加密
    private  function _encrypt($data){
        if(!empty($data)){
            $encrypt_data=openssl_encrypt(json_encode($data),
                'AES-256-CBC',
                'lisi',
                false,
                '0123456701234567'
            );
            return $encrypt_data;
        }
//        var_dump($this->_api_data);die;
    }


    //解密
    private  function _decrypt($request){
        $data=$request->post('data');
//        var_dump($data);die;
        if(!empty($data)){
            $decrypt_data=openssl_decrypt($data,
                'AES-256-CBC',
                'lisi',
                false,
                '0123456701234567'
            );
            $this->_api_data=json_decode( $decrypt_data,true);
//            var_dump($this->_api_data);

        }
//        var_dump($this->_api_data);die;
    }
    private function _RsaDecrypt($request){
    //非对称加密
    }
//    //验证签名
    private  function _checkSign($request){
//        var_dump($this->_api_data);die;
        if(!empty($this->_api_data)){
//            echo 1;die;
            //获取当前所有的app_id和key
            //生成服务器端的签名
            $map=$this->_getAppIdKey();
//            var_dump($map);die;
            //判断appid是否存在
            if(!array_key_exists($this->_api_data['app_id'],$map)){
                return [
                    'status'=>1,
                    'msg'=>'check sign fail',
                    'data'=>[]
                ];
            }
            //服务器生成签名
            //var_dump($this->_api_data);
//            var_dump($map);die;
            //生成服务器端签名
            ksort($this->_api_data);
            //变成字符串 拼接app-key
            $server_str=http_build_query($this->_api_data).'&app_key='.$map[$this->_api_data['app_id']];
//            var_dump($server_str);die;
                if(md5($server_str)!=$request['sign']){
                    return [
                        'status'=>2,
                        'msg'=>'check sign fail2',
                        'data'=>[]
                    ];
                }
           return ['status'=>1000];

//            var_dump($this->_api_data);die;
        }

    }

    //服务器端返回时返回的签名
    private function _createSign($data){
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
    return $this->_api_data['app_id'];
}
    //接口防刷
    private function _checkApiAccessCount(){
      //先获取appid
//        echo 1;die;
        $app_id=$this->_getAppId();
//        echo $app_id;die;
        //判断是否在黑名单中
        $blank_key=$this->_blank_list;
        //判断是否在黑名单中
        $add_blank_time=Redis::zScore($blank_key,$app_id);
        //不在黑名单继续走
        if(empty($add_blank_time)){
            $this->_addAppIdAccessCount();
//            return [];
//            return ['status'=>1000];
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
//      var_dump($count);die;
        if($count==1){
            //echo 1;
            Redis::Expire($this->_getAppId(),20);
        }
        //大于等于100，加入黑名单
        if($count>=3){
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
