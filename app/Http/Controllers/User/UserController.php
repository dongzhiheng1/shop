<?php

namespace App\Http\Controllers\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\UserModel;

class UserController extends Controller
{
    //

	public function user($uid)
	{
		echo $uid;
	}

	public function test()
    {
        echo '<pre>';print_r($_GET);echo '</pre>';
    }

	public function add()
	{
		$data = [
			'name'      => str_random(5),
			'age'       => mt_rand(20,99),
			'email'     => str_random(6) . '@gmail.com',
			'reg_time'  => time()
		];

		$id = UserModel::insertGetId($data);
		var_dump($id);
	}
	//注册
	public function register(){
	    return view('user.reg');
    }
    public function doRegister(Request $request){
	    $name=$request->input('u_name');
	    if(empty($name)){
	        die("账号不能为空");
        }
        $pwd1=$request->input('u_pwd');
        if(empty($pwd1)){
            die("密码不能为空");
        }
        $pwd2=$request->input('u_pwd2');
        if(empty($pwd2)){
            die("确认密码不能为空");
        }
        if($pwd1!==$pwd2){
            die("密码不一致");
        };
	    $age=$request->input('u_age');
        if(empty($age)){
            die("年龄不能为空");
        }
	    $email=$request->input('u_email');
        if(empty($email)){
            die("邮箱不能为空");
        }
	    $res=UserModel::where(['name'=>$name])->first();
	    if($res){
	       die("账号已存在");
        }

        $pwd=password_hash($pwd1,PASSWORD_BCRYPT);
//	    echo $pwd;die;
//	    $pwd=password_verify($pwd1,'$2y$10$TGftIAn6wDc.mBF1Z0Mh8e8mxskkKbsOh8GCDnohgdhE2J/vujlCC');
//	    var_dump($pwd);die;
	    //echo __METHOD__;
	    //echo '<pre>';print_r($_POST);echo '</pre>';
	    $data=[
	        'name'=>$name,
            'pwd'  =>$pwd,
            'age'=>$age,
            'email'=>$email,
            'reg_time'=>time(),
        ];
	    $uid=UserModel::insertGetId($data);
	    //var_dump($uid);
	    if($uid){
            $token=substr(md5(time().mt_rand(1,9999)),10,10);
            setcookie('token',$token,time()+86400,'/user','',false,true);
	        setcookie('uid',$uid,time()+86400,'/','myshop.com',false,true);
            setcookie('uname',$name,time()+86400,'/','myshop.com',false,true);
            $request->session()->put('u_token',$token);
            $request->session()->put('uid',$uid);
            header("refresh:2,url=/user/center");
            echo "注册成功，正在跳转";
        }else{
	        echo "注册失败";
        }
    }
    public function login(){
        return view('user.login');
    }
    public function doLogin(Request $request){
    $u_name=$request->input('u_name');
    $u_pwd=$request->input('u_pwd');
    $where=[
        'name'=>$u_name,
    ];
	    $res=UserModel::where($where)->first();
	    if($res){
            if(password_verify($u_pwd,$res->pwd)){
              $token=substr(md5(time().mt_rand(1,9999)),10,10);
                setcookie('uid',$res->u_id,time()+86400,'/','myshop.com',false,true);
                setcookie('token',$token,time()+86400,'/user','',false,true);
                setcookie('uname',$u_name,time()+86400,'/','myshop.com',false,true);
                $request->session()->put('u_token',$token);
                $request->session()->put('uid',$res->u_id);
                header("refresh:2;url=/user/center");
                echo "登录成功";
            }else{
                echo("账号或密码错误");
                header('refresh:1;url=/user/login');
            }
        }else{
	        die("用户不存在");
        }
    }
    public function center(Request $request){
	    if($_COOKIE['token']!=$request->session()->get('u_token')){
	        die('非法请求');
        }else{
            if(empty($_COOKIE['uid'])){
                header("refresh:1;url=/user/login");
                echo "请先登录";exit;
            }else{
                echo "欢迎". $_COOKIE['uname'] ."登录";
            }
        }
//        echo 'u_token'.$request->session()->get('u_token');echo '<br/>';
//        echo '<pre>';print_r($_COOKIE);echo '</pre>';die;
    }
    public  function quit(){
	    session()->forget('u_token');
        session()->forget('uid');
        session()->forget('uname');
        cookie()->forget('token');
        cookie()->forget('uid');
	    echo "退出成功";
	    header('refresh:1;url=/user/login');
    }
}
