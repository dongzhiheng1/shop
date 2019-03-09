<?php

namespace App\Http\Controllers\Test;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\UserModel;
use Illuminate\Support\Facades\Redis;
use DB;

class TestController extends Controller
{
    //

    public function abc()
    {
        var_dump($_POST);echo '</br>';
        var_dump($_GET);echo '</br>';
    }

	public function world1()
	{
		echo __METHOD__;
	}


	public function hello2()
	{
		echo __METHOD__;
		header('Location:http://cms.com');
	}

	public function world2()
	{
		echo __METHOD__;
	}

	public function md($m,$d)
	{
		echo 'm: '.$m;echo '<br>';
		echo 'd: '.$d;echo '<br>';
	}

	public function showName($name=null)
	{
		var_dump($name);
	}

	public function query1()
	{
		$list = DB::table('p_users')->get()->toArray();
		echo '<pre>';print_r($list);echo '</pre>';
	}

	public function query2()
	{
		$user = DB::table('p_users')->where('uid', 3)->first();
		echo '<pre>';print_r($user);echo '</pre>';echo '<hr>';
		$email = DB::table('p_users')->where('uid', 4)->value('email');
		var_dump($email);echo '<hr>';
		$info = DB::table('p_users')->pluck('age', 'name')->toArray();
		echo '<pre>';print_r($info);echo '</pre>';
	}
	public function viewTest1(){
        $data=[];
        return view('test.index',$data);
    }
    public function viewTest2(){
        $list=UserModel::all()->toArray();
        $data=[
            'title'=>'mama',
            'list'=>$list
        ];
        return view('test.child',$data);
    }
    public function checkcookie(){
        echo __METHOD__;
    }
    public function mid2(){
        echo __METHOD__;
    }
    public function weixinLogin(){
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

	public function wxLogin(){
		$code=urlencode('http://mall.77sc.com.cn');
		var_dump($code);die;
		return view('test.weixinlogin');
	}

}
