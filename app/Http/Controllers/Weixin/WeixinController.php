<?php

namespace App\Http\Controllers\Weixin;

use App\Model\WeixinUser;
use foo\bar;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Redis;
use GuzzleHttp;

class WeixinController extends Controller
{
    //

    protected $redis_weixin_access_token = 'str:weixin_access_token';     //微信 access_token

    public function test()
    {
        //echo __METHOD__;
        //$this->getWXAccessToken();
        $this->getUserInfo(1);
    }

    /**
     * 首次接入
     */
    public function validToken1()
    {
        //$get = json_encode($_GET);
        //$str = '>>>>>' . date('Y-m-d H:i:s') .' '. $get . "<<<<<\n";
        //file_put_contents('logs/weixin.log',$str,FILE_APPEND);
        echo $_GET['echostr'];
    }

    /**
     * 接收微信服务器事件推送
     */
    public function wxEvent()
    {
        $data = file_get_contents("php://input");
        $xml = simplexml_load_string($data);

        //解析XML
             //将 xml字符串 转换成对象
        $event = $xml->Event;
        $openid = $xml->FromUserName;                    //事件类型
        //var_dump($xml);echo '<hr>';
     if(isset($xml->MsgType)){
         if($xml->MsgType=='text'){     //用户发送图片信息
             $msg=$xml->Content;
             $xml_response='<xml><ToUserName><![CDATA['.$openid.']]></ToUserName><FromUserName><![CDATA['.$xml->ToUserName.']]></FromUserName><CreateTime>'.time().'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['. $msg. date('Y-m-d H:i:s') .']]></Content></xml>';
             echo $xml_response;
             exit();
         }elseif($xml->MsgType=='image'){  //用户发送图片信息
             //视业务需求是否需要下载保存图片
             if(1){
                 //下载图片素材
                 $this->dlWxImg($xml->MediaId);
                 $xml_response = '<xml><ToUserName><![CDATA['.$openid.']]></ToUserName><FromUserName><![CDATA['.$xml->ToUserName.']]></FromUserName><CreateTime>'.time().'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['. str_random(10) . ' >>> ' . date('Y-m-d H:i:s') .']]></Content></xml>';
                 echo $xml_response;
             }

         }
         exit();
     }
        if ($event == 'subscribe') {
                           //用户openid
            $sub_time = $xml->CreateTime;               //扫码关注时间


            echo 'openid: ' . $openid;
            echo '</br>';
            echo '$sub_time: ' . $sub_time;

            //获取用户信息
            $user_info = $this->getUserInfo($openid);
            echo '<pre>';
            print_r($user_info);
            echo '</pre>';

            //保存用户信息
            $u = WeixinUser::where(['openid' => $openid])->first();
            //var_dump($u);die;
            if ($u) {       //用户不存在
                echo '用户已存在';
            } else {
                $user_data = [
                    'openid' => $openid,
                    'add_time' => time(),
                    'nickname' => $user_info['nickname'],
                    'sex' => $user_info['sex'],
                    'headimgurl' => $user_info['headimgurl'],
                    'subscribe_time' => $sub_time,
                ];

                $id = WeixinUser::insertGetId($user_data);      //保存用户信息
                var_dump($id);
            }
        }elseif($event == 'CLICK'){   //click菜单
                if ($xml->EventKey=='kefu01') {
                    $this->kefu01($openid, $xml->ToUserName);
                }
            }
        $log_str = date('Y-m-d H:i:s') . "\n" . $data . "\n<<<<<<<";
        file_put_contents('logs/wx_event.log', $log_str, FILE_APPEND);
    }
    /**
     * 接收事件推送
     */
    public function validToken()
    {
        //$get = json_encode($_GET);
        //$str = '>>>>>' . date('Y-m-d H:i:s') .' '. $get . "<<<<<\n";
        //file_put_contents('logs/weixin.log',$str,FILE_APPEND);
        //echo $_GET['echostr'];
        $data = file_get_contents("php://input");
        $log_str = date('Y-m-d H:i:s') . "\n" . $data . "\n<<<<<<<";
        file_put_contents('logs/wx_event.log',$log_str,FILE_APPEND);
    }
    //客服处理
    public function kefu01($openid,$from)
    {
        // 文本消息
        $xml_response='<xml><ToUserName><![CDATA['.$openid.']]></ToUserName><FromUserName><![CDATA['.$from.']]></FromUserName><CreateTime>'.time().'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['.'Hello World,现在时间'.date('Y-m-d H:i:s').']]></Content></xml>';
        echo $xml_response;
    }

    /**
     * 获取微信AccessToken
     */
    public function getWXAccessToken()
    {

        //获取缓存
        $token = Redis::get($this->redis_weixin_access_token);
        if(!$token){        // 无缓存 请求微信接口
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WEIXIN_APPID').'&secret='.env('WEIXIN_APPSECRET');
            $data = json_decode(file_get_contents($url),true);

            //记录缓存
            $token = $data['access_token'];
            Redis::set($this->redis_weixin_access_token,$token);
            Redis::setTimeout($this->redis_weixin_access_token,3600);
        }
        return $token;

    }

    /**
     * 获取用户信息
     * @param $openid
     */
    public function getUserInfo($openid)
    {
        //$openid = 'oLreB1jAnJFzV_8AGWUZlfuaoQto';
        $access_token = $this->getWXAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';

        $data = json_decode(file_get_contents($url),true);
        //echo '<pre>';print_r($data);echo '</pre>';
        return $data;
    }
    //下载图片素材
    public function dlWxImg($media_id){
        $url = 'https://api.weixin.qq.com/cgi-bin/media/get?access_token='.$this->getWXAccessToken().'&media_id='.$media_id;
        //保存图片
        $client=new GuzzleHttp\Client();
        $response=$client->get($url);
        //获取文件名
        $file_info = $response->getHeader('Content-disposition');
        $file_name = substr(rtrim($file_info[0],'"'),-20);
        $wx_image_path='wx/images/'.$file_name;
        //保存图片
        $r= Storage::disk('local')->put($wx_image_path,$response->getBody());
        if($r){
            //保存成功
            echo "保存成功";
        }else{
            echo "保存失败";
        }

    }
    public function wxMenu()
    {
        //echo __METHOD__;
        // 1 获取access_token 拼接请求接口
        $url='https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$this->getWXAccessToken();
        //echo $url;echo '</br>';
        //2 请求微信接口
        $client=new GuzzleHttp\Client(
            ['base_uri'=>$url]);
        $data=[
            'button'=>[
               [
                'name'=>'4399',
                   'sub_button'=>[
                [
               "type"=>"view",
               "name"=>"小游戏",
               "url"=>"http://www.4399.com"
            ]
            ]
            ],
                [
                    'name'=>'百度',
                    'sub_button'=>[
                        [
                            "type"=>"view",
                            "name"=>"首页",
                            "url"=>"http://www.baidu.com"
                        ]
                    ]
                ],
                [
                    'type'=>'click',
                    'name'=>'客服1',
                    'key'=>'kefu01',
                ]
            ]

        ];
        $r=$client->request('POST',$url,[
            'body'=>json_encode($data,JSON_UNESCAPED_UNICODE)
        ]);
        // 3 解析微信接口返回信息
        $resopnse_array=json_decode($r->getBody(),true);
        //echo '<pre>';print_r($response_array);echo '</pre>';
        if($resopnse_array['errcode']==0){
            echo "创建菜单成功";
        }else{
            echo "菜单创建失败,请重新测试";echo'<br>';
            echo $resopnse_array['errmsg'];
        }

    }
}
