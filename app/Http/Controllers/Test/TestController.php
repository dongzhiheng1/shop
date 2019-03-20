<?php

namespace App\Http\Controllers\Test;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\UserModel;
use Illuminate\Support\Facades\Redis;
use DB;
use GuzzleHttp\Client;

class TestController extends Controller
{
    //
	public  $PrivateKey="./key/priv2.key";
	public  $PublicKey="./key/pub.key";
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

	public function api(){
		$url='http://www.api.com/test.php?type=1';
		$client=new Client();
		$response=$client->request('GET',$url);
		$r=$response->getBody();
		$data=json_decode($r,true);
		print_r($data);
	}
	public function encrypt(){
		$data=$_POST['data'];
		$time=$_GET['time'];
		$key='password';
		$method='AES-128-CBC';
		$salt='123456';
		$iv=substr(md5($time.$salt),5,16);
        $str_data=base64_decode($data);
		$enc_data=openssl_decrypt($str_data,$method,$key,OPENSSL_RAW_DATA,$iv);
		$json_str=json_decode($enc_data,true);
//		var_dump($json_str) ;die;
		if($json_str!=null){
			$now_time=time();
			$msg_data=[
				'errno'=>0,
				'msg'=>'ok'
			];
			$iv2=substr(md5($now_time.$salt),5,16);
			$dec_data=openssl_encrypt(json_encode($msg_data),$method,$key,OPENSSL_RAW_DATA,$iv2);
			$base_data=base64_encode($dec_data);
			$n_time=[
			   'now_time'=>$now_time,
				'data'=>$base_data
			];
			echo json_encode($n_time);
		}

	}

	//签名
	public function sign(){
		 $data=[
			'name'=>'zhangsan',
			 'sex'=>'男'
		  ];
		    $dat=json_encode($data);
			$priKey = file_get_contents($this->PrivateKey);
			$res = openssl_get_privatekey($priKey);
//		    var_dump($priKey);die;
			($res) or die('您使用的私钥格式错误，请检查RSA私钥配置');
			openssl_sign($dat, $sign, $res, OPENSSL_ALGO_SHA256);
			$sign = base64_encode($sign);
		    $info=[
				 'data'=>$data,
				 'sign'=>$sign
			 ];
		echo json_encode($info);
	}
	public function pub()
	{
	    $sign=$_POST['sign'];
		$data=$_POST['data'];
		$publicKey = file_get_contents($this->PublicKey);
		$res = openssl_get_publickey($publicKey);
		($res) or die('RSA公钥错误。请检查公钥文件格式是否正确');
		$result = (openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_SHA256) === 1);
		openssl_free_key($res);
		var_dump($result);
	}
	public function  fbnq(){
         $arr[0] = 1;
		 $arr[1] = 1;
          for($i = 2;$i <=20;$i++){
		   $arr[$i] = $arr[$i-1] + $arr[$i-2];
		  }
		 echo "<pre>";print_r($arr);echo "</pre>";
	}

	public function a(){
//		echo 1;
		echo "<pre>";print_r($_POST);echo "</pre>";
	}
}
