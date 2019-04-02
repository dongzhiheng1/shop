<?php

namespace App\Http\Controllers\Login;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\UserModel;
use Illuminate\Support\Facades\Redis;

class IndexController extends Controller
{
    //pc端登录
    public function Alogin(){
           return view("login.login");
      }
    public function doAlogin(Request $request){
            $u_name=$request->input('u_name');
            $u_pwd=$request->input('u_pwd');
            $where=[
                'name'=>$u_name,
            ];
            $res=UserModel::where($where)->first();
            if($res){
                if(password_verify($u_pwd,$res->pwd)){
                    $token=substr(md5(time().mt_rand(1,9999)),10,10);
                    setcookie('uid',$res->u_id,time()+86400,'/','wangby.cn',false,true);
                    setcookie('token',$token,time()+86400,'/user','',false,true);
                    setcookie('uname',$u_name,time()+86400,'/','wangby.cn',false,true);
                    $uid=$res->u_id;
                    $redis_key_token='str:u:token:'.$uid;
                    Redis::del($redis_key_token);
                    Redis::hset($redis_key_token,'web',$token);
                    Redis::expire( $redis_key_token,10);
                    echo "登录成功";
                    header('refresh:1;url=/u/list');
                }else{
                    echo("账号或密码错误");
                    header('refresh:1;url=/a/login');
                }
            }else{
                die("用户不存在");
            }
        }
   //用户列表
      public function  userList(){
          $uid=$_COOKIE['uid'];
          $token=Redis::hget('str:u:token:'.$uid,'web');
          if(empty($token) || empty($uid)){
              echo "登录已过期,请重新登录";
              header('refresh:1;url=/a/login');die;
          }
          $list=UserModel::get();
          $data=[
              'list'=>$list
          ];
            return view("login.user",$data);
      }
    //app登录
    public function receive(Request $request){
        $uname=$request->input('uname');
        $pwd=$request->input('pwd');
//        echo $uname;echo "<br>";
//        echo $pwd;die;
        $where = [
            'name' =>  $uname,
        ];
        if(empty($uname)|| empty($pwd)){
           return [
                'error'=>400,
                'msg'=>'账号或密码不能为空'
            ];
        }
        $res = UserModel::where($where)->first();
//        echo $res;die;
        if ($res) {
            if (password_verify($pwd, $res->pwd)){
                $token = substr(md5(time()) . mt_rand(1, 9999), 10, 10);
                setcookie('uid', $res->u_id, time() + 86400, '/', 'wangby.cn', false, true);
                setcookie('token', $token, time() + 86400, '/', 'wangby.cn', false, true);
//                $request->session()->put('u_token', $token);
//                $request->session()->put('uid', $res->u_id);
//                echo $token;die;
                $redis_key_token='str:u:token:'.$res->u_id;
                Redis::del($redis_key_token);
                Redis::hset($redis_key_token,'android',$token);
                Redis::expire( $redis_key_token,10);
                $response=[
                    'error'=>0,
                    'msg'=>'登录成功',
                    'token'=>$token,
                    'user'=>$uname,
                    'uid'=>$res->u_id
                ];
            }else {
                return [
                    'error'=>5001,
                    'msg'=>'登录失败'
                ];
            }
        }else{
            return [
                'error'=>500,
                'msg'=>'账号或密码错误'
            ];
        }
        return $response;
    }
    public function center(Request $request){
        $token=$request->input('token');
        $uid=$request->input('uid');
        if(empty($token) || empty($uid)){
            $response=[
                'errno'=>5001,
                'msg'=>'请先登录'
            ];
            return $response;
        }
        $a_token=Redis::hget('str:u:token:'.$uid,'android');
        if($token==$a_token){
            $response=[
                'errno'=>0,
                'msg'=>'ok'
            ];
        }else{
            $response=[
                'errno'=>5002,
                'msg'=>'非法登录'
            ];
        }
        if(empty($a_token)){
            return [
                'errno'=>'50002',
                'msg'=>'长时间未操作，请重新登录'
            ];
        }
        return $response;
    }
}
