<?php

namespace App\Http\Controllers\Weixin;

use App\Model\WeixinChatModel;
use App\Model\WeixinUser;
use App\Model\WeixinMedia;
use foo\bar;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Redis;
use GuzzleHttp;
use Illuminate\Support\Facades\Storage;

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
        //记录日志
        $log_str = date('Y-m-d H:i:s') . "\n" . $data . "\n<<<<<<<";
        file_put_contents('logs/wx_event.log', $log_str, FILE_APPEND);

        //解析XML
        //将 xml字符串 转换成对象
        $event = $xml->Event;
        $openid = $xml->FromUserName;                    //事件类型
        //var_dump($xml);echo '<hr>';
        if (isset($xml->MsgType)) {
            if ($xml->MsgType == 'text') {
                $msg = $xml->Content;
                //记录聊天消息
                $data = [
                    'msg'       => $msg,
                    'msgid'     => $xml->MsgId,
                    'openid'    => $openid,
                    'msg_type'  => 1 , // 1用户发送消息 2客服发送消息
                ];
                $id = WeixinChatModel::insertGetId($data);
//                var_dump($id);
            } elseif ($xml->MsgType == 'image') {  //用户发送图片信息
                //视业务需求是否需要下载保存图片
                if (1) {
                    //下载图片素材
                    $file_name = $this->dlWxImg($xml->MediaId);
                    $xml_response = '<xml><ToUserName><![CDATA[' . $openid . ']]></ToUserName><FromUserName><![CDATA[' . $xml->ToUserName . ']]></FromUserName><CreateTime>' . time() . '</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[' . str_random(10) . ' >>> ' . date('Y-m-d H:i:s') . ']]></Content></xml>';
                    echo $xml_response;

                    //写入数据库
                    $data = [
                        'openid' => $openid,
                        'add_time' => time(),
                        'msg_type' => 'image',
                        'media_id' => $xml->MediaId,
                        'format' => $xml->Format,
                        'msg_id' => $xml->MsgId,
                        'local_file_name' => $file_name
                    ];
                    $m_id = WeixinMedia::insertGetId($data);
                    var_dump($m_id);
                }
            } elseif ($xml->MsgType == 'voice') {        //处理语音信息
                $this->dlVoice($xml->MediaId);
                $xml_response = '<xml><ToUserName><![CDATA[' . $openid . ']]></ToUserName><FromUserName><![CDATA[' . $xml->ToUserName . ']]></FromUserName><CreateTime>' . time() . '</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[' . str_random(10) . ' >>> ' . date('Y-m-d H:i:s') . ']]></Content></xml>';
                echo $xml_response;
            } elseif ($xml->MsgType == 'video') {        //处理语音信息
                $this->dlVideo($xml->MediaId);
                $xml_response = '<xml><ToUserName><![CDATA[' . $openid . ']]></ToUserName><FromUserName><![CDATA[' . $xml->ToUserName . ']]></FromUserName><CreateTime>' . time() . '</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[' . str_random(10) . ' >>> ' . date('Y-m-d H:i:s') . ']]></Content></xml>';
                echo $xml_response;
            }
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
        } elseif ($event == 'CLICK') {   //click菜单
            if ($xml->EventKey == 'kefu01') {
                $this->kefu01($openid, $xml->ToUserName);
            }
        }
    }

    //获取素材列表
    public function fodder()
    {
        $access_token = $this->getWXAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=' . $access_token;
        $data = [
            'type' => 'image',
            'offset' => 0,
            'count' => 2
        ];
        $client = new GuzzleHttp\Client(['base_uri' => $url]);
        $r = $client->request('POST', $url, [
            'body' => json_encode($data, JSON_UNESCAPED_UNICODE)
        ]);
        $respone_arr = json_decode($r->getBody(), true);
        echo '<pre>';
        print_r($respone_arr);
        echo '</pre>';

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
        file_put_contents('logs/wx_event.log', $log_str, FILE_APPEND);
    }

    //客服处理
    public function kefu01($openid, $from)
    {
        // 文本消息
        $xml_response = '<xml><ToUserName><![CDATA[' . $openid . ']]></ToUserName><FromUserName><![CDATA[' . $from . ']]></FromUserName><CreateTime>' . time() . '</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[' . 'Hello World,现在时间' . date('Y-m-d H:i:s') . ']]></Content></xml>';
        echo $xml_response;
    }

    /**
     * 获取微信AccessToken
     */
    public function getWXAccessToken()
    {

        //获取缓存
        $token = Redis::get($this->redis_weixin_access_token);
        if (!$token) {        // 无缓存 请求微信接口
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . env('WEIXIN_APPID') . '&secret=' . env('WEIXIN_APPSECRET');
            $data = json_decode(file_get_contents($url), true);

            //记录缓存
            $token = $data['access_token'];
            Redis::set($this->redis_weixin_access_token, $token);
            Redis::setTimeout($this->redis_weixin_access_token, 3600);
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
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=' . $access_token . '&openid=' . $openid . '&lang=zh_CN';

        $data = json_decode(file_get_contents($url), true);
        //echo '<pre>';print_r($data);echo '</pre>';
        return $data;
    }

    //下载图片素材
    public function dlWxImg($media_id)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/media/get?access_token=' . $this->getWXAccessToken() . '&media_id=' . $media_id;
        //保存图片
        $client = new GuzzleHttp\Client();
        $response = $client->get($url);
        //获取文件名
        $file_info = $response->getHeader('Content-disposition');
        $file_name = substr(rtrim($file_info[0], '"'), -20);
        $wx_image_path = 'wx/images/' . $file_name;
        //保存图片
        $r = Storage::disk('local')->put($wx_image_path, $response->getBody());
        if ($r) {
            //保存成功
            echo "保存成功";
        } else {
            echo "保存失败";
        }
        return $file_name;

    }

    public function dlVoice($media_id)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/media/get?access_token=' . $this->getWXAccessToken() . '&media_id=' . $media_id;

        $client = new GuzzleHttp\Client();
        $response = $client->get($url);
        //$h = $response->getHeaders();
        //echo '<pre>';print_r($h);echo '</pre>';die;
        //获取文件名
        $file_info = $response->getHeader('Content-disposition');
        $file_name = substr(rtrim($file_info[0], '"'), -20);

        $wx_image_path = 'wx/voice/' . $file_name;
        //保存图片
        $r = Storage::disk('local')->put($wx_image_path, $response->getBody());
        if ($r) {     //保存成功

        } else {      //保存失败

        }
    }

    public function dlVideo($media_id)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/media/get?access_token=' . $this->getWXAccessToken() . '&media_id=' . $media_id;

        $client = new GuzzleHttp\Client();
        $response = $client->get($url);
        //$h = $response->getHeaders();
        //echo '<pre>';print_r($h);echo '</pre>';die;
        //获取文件名
        $file_info = $response->getHeader('Content-disposition');
        $file_name = substr(rtrim($file_info[0], '"'), -20);

        $wx_image_path = 'wx/video/' . $file_name;
        //保存图片
        $r = Storage::disk('local')->put($wx_image_path, $response->getBody());
        if ($r) {     //保存成功
            echo "保存成功";
        } else {      //保存失败
            echo "保存失败";
        }
    }

    public function wxMenu()
    {
        //echo __METHOD__;
        // 1 获取access_token 拼接请求接口
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $this->getWXAccessToken();
        //echo $url;echo '</br>';
        //2 请求微信接口
        $client = new GuzzleHttp\Client(
            ['base_uri' => $url]);
        $data = [
            'button' => [
                [
                    'name' => '4399',
                    'sub_button' => [
                        [
                            "type" => "view",
                            "name" => "小游戏",
                            "url" => "http://www.4399.com"
                        ]
                    ]
                ],
                [
                    'name' => '百度',
                    'sub_button' => [
                        [
                            "type" => "view",
                            "name" => "首页",
                            "url" => "http://www.baidu.com"
                        ]
                    ]
                ],
                [
                    'type' => 'click',
                    'name' => '客服1',
                    'key' => 'kefu01',
                ]
            ]

        ];
        $r = $client->request('POST', $url, [
            'body' => json_encode($data, JSON_UNESCAPED_UNICODE)
        ]);
        // 3 解析微信接口返回信息
        $resopnse_array = json_decode($r->getBody(), true);
        //echo '<pre>';print_r($response_array);echo '</pre>';
        if ($resopnse_array['errcode'] == 0) {
            echo "创建菜单成功";
        } else {
            echo "菜单创建失败,请重新测试";
            echo '<br>';
            echo $resopnse_array['errmsg'];
        }
    }
//微信群发
    public function sendAll(Request $request)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=' . $this->getWXAccessToken();
        $openid=$request->openid;
        $message=$request->message;
//        $wxUserInfo = WeixinUser::get()->toArray();
        //var_dump($wxUserInfo);
//        foreach ($wxUserInfo as $v) {
//            $openid[] = $v['openid'];
//        }
        //print_r($openid);
        //文本群发消息
        $data = [
            "touser" => $openid,
            "msgtype" => "text",
            "text" => [
                "content" =>$message
            ]
        ];
        $client = new GuzzleHttp\Client();
        $r = $client->request('POST', $url, [
            'body' => json_encode($data, JSON_UNESCAPED_UNICODE)
        ]);
        $respone_arr = json_decode($r->getBody(), true);
        print_r($respone_arr);
    }
   //永久素材保存
    public function upMaterialTest($file_path){
        $url = 'https://api.weixin.qq.com/cgi-bin/material/add_material?access_token='.$this->getWXAccessToken().'&type=image';
        $client=new GuzzleHttp\Client();
        $response=$client->request('POST',$url,[
           'multipart'=>[
               [
                   'name'=>'media',
                   'contents'=>fopen($file_path,'r')
               ],
           ]
        ]);
        $body=$response->getBody();
        $d=GuzzleHttp\json_decode($body,true);
        print_r($d);
    }

    //测试
    public function formShow()
    {
        return view('test.form');
    }

    public function formTest(Request $request)
    {
        $img_file = $request->file('media');
//       var_dump($img_file);
        $img_origin_name = $img_file->getClientOriginalName();
        var_dump($img_origin_name);
        $file_ext = $img_file->getClientOriginalExtension();
        var_dump($file_ext);
        $new_file_name = str_random(15) . '.' . $file_ext;
        var_dump($new_file_name);
        //文件保存路径

        //保存文件
        $save_file_path = $request->media->storeAs('form_test', $new_file_name);
        var_dump($save_file_path);


        //上传至微信永久素材
        $this->upMaterialTest($save_file_path);
    }
    public function one(){
        return  view('test.one');
    }
    /**
     * 微信客服聊天
     */
    public function chatShow()
    {
        $data = [
            'openid'    => 'o4Xdz5wnr4PR2dQs8BvzT0IV5vIw'
        ];
//        $where=['openid'=>$data];
//        $info=WeixinUser::where($where)->first();
        return view('weixin.chat',$data);
    }

    public function getChatMsg()
    {
        $openid = $_GET['openid'];  //用户openid
        $pos = $_GET['pos'];        //上次聊天位置
        $msg = WeixinChatModel::where(['openid'=>$openid])->where('id','>',$pos)->first();
        //$msg = WeixinChatModel::where(['openid'=>$openid])->where('id','>',$pos)->get();
        if($msg){
            $response = [
                'errno' => 0,
                'data'  => $msg->toArray()
            ];

        }else{
            $response = [
                'errno' => 50001,
                'msg'   => '服务器异常，请联系管理员'
            ];
        }
        die( json_encode($response));
    }
    public  function  weixinChat(Request $request){
        $openid=$request->input("openid");
        $msg=$request->input("msg");
//        var_dump($msg);die;
        $url='https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$this->getWXAccessToken();
        $data = [
            'touser'       =>$openid,
            'msgtype'      =>'text',
            'text'         =>[
                'content'  =>$msg,
            ]
        ];
        $client = new GuzzleHttp\Client();
        $response = $client->request('POST', $url, [
            'body' => json_encode($data,JSON_UNESCAPED_UNICODE)
        ]);
        $body = $response->getBody();
        $arr = json_decode($body,true);
        //加入数据库
        if($arr['errcode']==0){
            $info = [
                'msg_type'      =>  2,
                'msg'   =>  $msg,
                'msgid'     =>  0,
                'openid'   =>  $openid,
            ];
            $id= WeixinChatModel::insertGetId($info);
//            var_dump($id);die;
        }
        return $arr;
    }
    public function getcode(){
        // 1 回调拿到 code (用户确认登录后 微信会跳 redirect )
        echo '<pre>';print_r($_GET);echo '</pre>';echo '<hr>';
        echo '<pre>';print_r($_POST);echo '</pre>';

        $code = $_GET['code'];          // code

        //2 用code换取access_token 请求接口

        $token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=wxe24f70961302b5a5&secret=0f121743ff20a3a454e4a12aeecef4be&code='.$code.'&grant_type=authorization_code';
        $token_json = file_get_contents($token_url);
        $token_arr = json_decode($token_json,true);
        echo '<hr>';
        echo '<pre>';print_r($token_arr);echo '</pre>';

        $access_token = $token_arr['access_token'];
        $openid = $token_arr['openid'];

        // 3 携带token  获取用户信息
        $user_info_url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
        $user_json = file_get_contents($user_info_url);

        $user_arr = json_decode($user_json,true);
        echo '<hr>';
        echo '<pre>';print_r($user_arr);echo '</pre>';

    }
    public function wxlogin(){
        return view('weixin/wxlogin');
    }
}
