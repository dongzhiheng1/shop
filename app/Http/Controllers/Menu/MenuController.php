<?php

namespace App\Http\Controllers\Menu;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use GuzzleHttp;

class MenuController extends Controller
{
    protected  $redis_weixin_access_token='str:weixin_access_token';
    public function menuAll(){
        return view('weixin.menu');
    }
    //获取token
    public function getAccessToken(){
        $token=Redis::get($this->redis_weixin_access_token);
        if(!$token){
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . env('WEIXIN_APPID') . '&secret=' . env('WEIXIN_APPSECRET');
            $data=json_decode(file_get_contents($url),true);
            $token=$data['access_token'];
            Redis::set($this->redis_weixin_access_token,$token);
            Redis::setTimeout($this->redis_weixin_access_token,3600);
        }
        return $token;
    }
    public function wxMenu(Request $request){
        $firstname=$request->input('firstname');
        $secondname=$request->input('secondname');
        $secondurl=$request->input('secondurl');
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$this->getAccessToken();
        $client = new GuzzleHttp\Client(['base_uri' => $url]);
        $data=[
            'button' => [
                [
                    'name' =>$firstname,
                    'sub_button' => [
                        [
                            "type" => "view",
                            "name" => $secondname,
                            "url" => $secondurl
                        ]
                    ]
                ],
            ]
        ];
        $r = $client->request('POST', $url, [
            'body' => json_encode($data, JSON_UNESCAPED_UNICODE)
        ]);
        $resopnse_array = json_decode($r->getBody(), true);
        //print_r($resopnse_array);die;
        if ($resopnse_array['errcode'] == 0) {
            echo "创建菜单成功";
        }
    }
}
