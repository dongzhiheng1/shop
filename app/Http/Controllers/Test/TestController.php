<?php

namespace App\Http\Controllers\Test;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\UserModel;
use App\Model\TestModel;
use App\Model\GoodsModel;
use App\Model\UseModel;
use Illuminate\Support\Facades\Redis;
use DB;
use GuzzleHttp\Client;

class TestController extends Controller
{
	//
	public $PrivateKey = "./key/priv2.key";
	public $PublicKey = "./key/pub.key";
	public $rsaPrivateKeyFilePath = "./key/priv.key";
	public $aliPubKey = './key/ali_pub.key';

	public function  __construct()
	{
		$this->app_id = env('ALIPAY_APPID');
		$this->gate_way = env('ALIPAY_GATEWAY');
		$this->notify_url = env("ALIPAY_NOTIFY");
		$this->return_url = env("ALIPAY_RETURN");
	}

	public function abc()
	{
		var_dump($_POST);
		echo '</br>';
		var_dump($_GET);
		echo '</br>';
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

	public function md($m, $d)
	{
		echo 'm: ' . $m;
		echo '<br>';
		echo 'd: ' . $d;
		echo '<br>';
	}

	public function showName($name = null)
	{
		var_dump($name);
	}

	public function query1()
	{
		$list = DB::table('p_users')->get()->toArray();
		echo '<pre>';
		print_r($list);
		echo '</pre>';
	}

	public function query2()
	{
		$user = DB::table('p_users')->where('uid', 3)->first();
		echo '<pre>';
		print_r($user);
		echo '</pre>';
		echo '<hr>';
		$email = DB::table('p_users')->where('uid', 4)->value('email');
		var_dump($email);
		echo '<hr>';
		$info = DB::table('p_users')->pluck('age', 'name')->toArray();
		echo '<pre>';
		print_r($info);
		echo '</pre>';
	}

	public function viewTest1()
	{
		$data = [];
		return view('test.index', $data);
	}

	public function viewTest2()
	{
		$list = UserModel::all()->toArray();
		$data = [
				'title' => 'mama',
				'list' => $list
		];
		return view('test.child', $data);
	}

	public function checkcookie()
	{
		echo __METHOD__;
	}

	public function mid2()
	{
		echo __METHOD__;
	}

	public function weixinLogin()
	{
		// 1 回调拿到 code (用户确认登录后 微信会跳 redirect )
		echo '<pre>';
		print_r($_GET);
		echo '</pre>';
		echo '<hr>';
		echo '<pre>';
		print_r($_POST);
		echo '</pre>';

		$code = $_GET['code'];          // code

		//2 用code换取access_token 请求接口

		$token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=wxe24f70961302b5a5&secret=0f121743ff20a3a454e4a12aeecef4be&code=' . $code . '&grant_type=authorization_code';
		$token_json = file_get_contents($token_url);
		$token_arr = json_decode($token_json, true);
		echo '<hr>';
		echo '<pre>';
		print_r($token_arr);
		echo '</pre>';

		$access_token = $token_arr['access_token'];
		$openid = $token_arr['openid'];

		// 3 携带token  获取用户信息
		$user_info_url = 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $access_token . '&openid=' . $openid . '&lang=zh_CN';
		$user_json = file_get_contents($user_info_url);
		$user_arr = json_decode($user_json, true);
		echo '<hr>';
		echo '<pre>';
		print_r($user_arr);
		echo '</pre>';

	}

	public function wxLogin()
	{
		$code = urlencode('http://mall.77sc.com.cn');
		var_dump($code);
		die;
		return view('test.weixinlogin');
	}

	public function api()
	{
		$url = 'http://www.api.com/test.php?type=1';
		$client = new Client();
		$response = $client->request('GET', $url);
		$r = $response->getBody();
		$data = json_decode($r, true);
		print_r($data);
	}

	public function encrypt()
	{
		$data = $_POST['data'];
		$time = $_GET['time'];
		$key = 'password';
		$method = 'AES-128-CBC';
		$salt = '123456';
		$iv = substr(md5($time . $salt), 5, 16);
		$str_data = base64_decode($data);
		$enc_data = openssl_decrypt($str_data, $method, $key, OPENSSL_RAW_DATA, $iv);
		$json_str = json_decode($enc_data, true);
//		var_dump($json_str) ;die;
		if ($json_str != null) {
			$now_time = time();
			$msg_data = [
					'errno' => 0,
					'msg' => 'ok'
			];
			$iv2 = substr(md5($now_time . $salt), 5, 16);
			$dec_data = openssl_encrypt(json_encode($msg_data), $method, $key, OPENSSL_RAW_DATA, $iv2);
			$base_data = base64_encode($dec_data);
			$n_time = [
					'now_time' => $now_time,
					'data' => $base_data
			];
			echo json_encode($n_time);
		}

	}

	//签名
	public function sign()
	{
		$data = [
				'name' => 'zhangsan',
				'sex' => '男'
		];
		$dat = json_encode($data);
		$priKey = file_get_contents($this->PrivateKey);
		$res = openssl_get_privatekey($priKey);
//		    var_dump($priKey);die;
		($res) or die('您使用的私钥格式错误，请检查RSA私钥配置');
		openssl_sign($dat, $sign, $res, OPENSSL_ALGO_SHA256);
		$sign = base64_encode($sign);
		$info = [
				'data' => $data,
				'sign' => $sign
		];
		echo json_encode($info);
	}

	public function pub()
	{
		$sign = $_POST['sign'];
		$data = $_POST['data'];
		$publicKey = file_get_contents($this->PublicKey);
		$res = openssl_get_publickey($publicKey);
		($res) or die('RSA公钥错误。请检查公钥文件格式是否正确');
		$result = (openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_SHA256) === 1);
		openssl_free_key($res);
		var_dump($result);
	}

	public function  fbnq()
	{
		$arr[0] = 1;
		$arr[1] = 1;
		for ($i = 2; $i <= 20; $i++) {
			$arr[$i] = $arr[$i - 1] + $arr[$i - 2];
		}
		echo "<pre>";
		print_r($arr);
		echo "</pre>";
	}

	public function login(Request $request)
	{
//    echo 1;
		$uname = $request->input('uname');
		$pwd = $request->input('pwd');
		//echo $uname;
		//echo $pwd;
		if (1) {
			return 1;
		} else {
			return 2;
		}
	}

	public function persion(Request $request)
	{
		$is_login = $request->get('is_login');
		// echo $is_login;die;
		if ($is_login == 0) {
			echo "请先登录";
			header('refresh:1;url=http://www.dongzhiheng.com');
			die;
		};
		return view('persion.persion');
	}

	public function user(Request $request)
	{
		$uname = $request->input('uname');
		$pwd = $request->input('pwd');
		$data = [
				'uname' => $uname,
				'pwd' => $pwd
		];

		$url = 'http://psp.wangby.cn/receive';
		$ch = curl_init();
		// var_dump($ch);die;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$rs = curl_exec($ch);
		$response = json_decode($rs, true);
		return $response;
	}

	public function index(Request $request)
	{
		//  var_dump($request->get('is_login'));
		$current_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$data = [
				'login' => $request->get('is_login'),
				'current_url' => urldecode($current_url)
		];
		return view('welcome', $data);
	}

	public function center(Request $request)
	{
		$token = $_POST['token'];
		$uid = $_POST['uid'];
		if (empty($token) || empty($uid)) {
			$response = [
					'errno' => 5001,
					'msg' => '请先登录'
			];
		}
		$a_token = Redis::hget('android');
		if ($token == $a_token) {
			$response = [
					'errno' => 0,
					'msg' => 'ok'
			];
		} else {
			$response = [
					'errno' => 5002,
					'msg' => '非法登录'
			];
		}
		return $response;
	}

	public function goodslist()
	{
		$key = 'h:goodsinfo';
		$goodsInfo = Redis::hGet('goodsInfo', $key);
		if (empty($goodsInfo)) {
			$goodsInfo = GoodsModel::get();
			Redis::hSet($key, 'goodsInfo', $goodsInfo);
			Redis::expire($key, 86400);
		}
		return $goodsInfo;
	}

	public function goodsdetail($id)
	{
		$where = [
				'goods_id' => $id
		];
		$key = 'set:goods_click:' . $id;
		Redis::zadd($key, time(), time());
		$goods_key = 'str:goods_detail:' . $id;
		$data = Redis::get($goods_key);
		if (empty($data)) {
			$info = GoodsModel::where($where)->first();
			Redis::set($goods_key, json_encode($info));
			Redis::expire($goods_key, 60 * 60 * 3);

		} else {
			$info = json_decode($data, true);
		}
		$info['click'] = Redis::zCard($key);
		return $info;
	}

	public function aaa()
	{
		$a = array(1, 2, 3, 4, 5, 6, 7);
		foreach ($a as $k => $v) {
			if ($v % 2 == 1) {
				$data[$k][] = $v;
			} else {
				$data[$k - 1][] = $v;
			}
		}
 echo "<pre>";print_r($data);echo "</pre>";

	}
	public function cgi(){
		return view("test.id");
	}
	public function cgia(Request $request){
		$image=$request->file('image');
//		var_dump($image);
		$uname=$request->input('u_name');
		$id_card=$request->input('id_card');
		$use=$request->input('use');
		if(empty($image)){
			echo "图片不能为空";
		}
        $image_name=$image->getClientOriginalExtension();
		$new_name=time().rand(1111,9999).$image_name;
		$save_file_path=$request->image->storeAs("/public/apply_test",$new_name);
//		var_dump($imag_name);
		$data=[
			'name'=>$uname,
			'idcard'=>$id_card,
			'use'=>$use,
			'img'=>$save_file_path
		];
		$res=UseModel::insert($data);
		if($res){
			echo "申请待审核";
		}
	}
	public function idlist(){
		$list=UseModel::get();
		$data=[
			'list'=>$list,
		];
		return view('test.idlist',$data);
	}
	//审核
	public function check($id){
//		echo $name;die;
		$where=[
			'id'=>$id,
		];
		$data=[
			'is_pass'=>1
		];
		$res=UseModel::where($where)->update($data);
		if($res){
			$app_key=time();
			$app_secret=rand(11111111111,99999999999);
			$redis_key="id";
			Redis::hset('app_key',$redis_key,$app_key);
			Redis::hset('app_secret',$redis_key,$app_secret);
			echo "审核成功";echo "<br>";
			$appkey=Redis::hget('app_key',$redis_key);
			$app_secret=Redis::hget('app_secret',$redis_key);
			echo "app_key为:".$appkey;echo "<br>";
			echo "app_secret为:".$app_secret;
		}
	}
	public function allLogin($tty){
		  //$tty 1代表电脑端登录   2代表手机端  3代表微信  4代表html
		   $where=[
			  'name'=>'xushihao',
			  'pwd'=>1
		   ];
		 $res=TestModel::where($where)->first();
		 if($res){
			echo '登录成功';
			$where=[
				'id'=>$res->id,
			];
			 $data=[
				 'tty'=>$tty,
				 'token'=>rand(1111,9999)
			 ];
			 TestModel::where($where)->update($data);
		 }
	}
	public function moreLogin($tty){
		//电脑和pc两端登录
		$where=[
				'name'=>'xushihao',
				'pwd'=>1
		];
		$res=TestModel::where($where)->first();
		if($res){
			echo '登录成功';
			$where=[
					'id'=>$res->id,
			];
			$data=[
					'tty'=>[1,2],
					'token'=>rand(1111,9999)
			];
			TestModel::where($where)->update($data);
		}
	}


	//test
	public function aa(){
		// 123563351234568861234
		// 123456

		// 12341543
		// 1234


		
	}

}
