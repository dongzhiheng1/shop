<?php
namespace App\Http\Controllers\Api;
use App\Model\CodeModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\UserModel;
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

class IndexController extends Controller
{
   public function api(Request $request){
     return ['status'=>1000,'msg'=>'success','data'=>[]];
   }

    //文件
    public function uploadImg(Request $request)
    {
//        var_dump($request->post('content'));die;
        if(empty($request->post('content'))){
            return ['status'=>6,'data'=>[],'msg'=>'上传的内容不能为空'];
        }
        $file_save_path = base_path() . '/storage/uploads/' . date('Ym') . '/';
//        var_dump($file_save_path);die;
        if (!is_dir($file_save_path)) {
            mkdir($file_save_path, 0777, true);
        };
            $file_name = time() . rand(11111, 99999) . '.tmp';
            $byte = file_put_contents(
                $file_save_path . $file_name,
                base64_decode($request->post('content')));
//            var_dump($byte);die;
            if ($byte > 0) {
                $info = getimagesize($file_save_path . $file_name);
//                var_dump($info);die;
                if (!$info) {
                    return ['status' => 7, 'data' => [], 'msg' => '图片内容或者格式不对'];
                };
                switch ($info['mime']) {
                    case 'image/jpeg':
                        $new_file_name = str_replace('tmp', 'jpg', $file_name);
                        break;
                    case 'image/png':
                        $new_file_name = str_replace('tmp', 'png', $file_name);
                        break;
                    default:
                        return ['status' => 8, 'data' => [], 'msg' => '图片内容或者格式不对'];
                        break;
                }
                //文件重命名
                rename($file_save_path . $file_name, $file_save_path . $new_file_name);
                $api_response = [];
                $access_path = str_replace(base_path() . '/storage', '', $file_save_path);
//               echo $access_path;die;
                $api_response = env('UPLOAD_URL') . $access_path .$new_file_name;
                return ['status' => 1000, 'msg' => 'success', 'data' =>$api_response];
            }
        }

    //页面携带sid
    public function vCode(){
        $api_url='http://www.dongzhiheng.com';
        session_start();
        $sid=session_id();
        $vcode_url=$api_url.'/vcode/'.$sid;
//        var_dump($vcode_url);die;
        $data=[
            'url'=>$vcode_url,
            'sid'=>$sid
        ];
//        var_dump($data);die;
        return ['status=>1000','msg'=>'success','data'=>$data];
    }
      //展示图片验证码
       public function code($sid,$r){
           session_id($sid);
           session_start();
           $a=rand(1,9);
           $b=rand(1,9);
           $type=rand(1,4);
           if($type==1){
               $c=$a+$b;
               $code=$a."+".$b."=?";
               $_SESSION['vcode']=$c;
           }elseif($type==2){
               $c=$a*$b;
               $code=$a."*".$b."=?";
               $_SESSION['vcode']=$c;
           }elseif($type==3){
               $c=$a*$b;
               $b=$c/$a;
               $code=$c."/".$b."=?";
               $_SESSION['vcode']=$a;
           }else{
               $c=$a+$b;
               $b=$c-$a;
               $code=$c."-".$b."=?";
               $_SESSION['vcode']=$a;
           }


           header("Content-type: image/png");
// Create the image
           $im = imagecreatetruecolor(120,32);

// Create some colors
           $white = imagecolorallocate($im, 255, 255, 255);
           $grey = imagecolorallocate($im, 62, 62, 62);
           imagefilledrectangle($im, 0, 0, 399, 70, $white);

// The text to draw
           $text = $code;
// Replace path by your own font path
           $font = '/data/wwwroot/shop/arial.ttf';

// Add the text
           $i=0;
           $len=strlen($code);
           while($i<$len){
               if(is_numeric($code[$i])){
                   imagettftext($im, 20,rand(-20,20), 10+$i*20, 20,  $grey, $font, $code[$i]);
               }else{
                   imagettftext($im, 20,0, 10+$i*20, 20,  $grey, $font, $code[$i]);
               }
               $i++;
           }
// Using imagepng() results in clearer text compared with imagejpeg()
           imagepng($im);
           imagedestroy($im);exit;
       }
    //接收传过来的值
    public function getVcode(Request $request){
//        echo 111;die;
//       var_dump($request->all());die;
        $sid=$request->post('sid');
        $vcode=$request->post('vcode');
//        var_dump($sid);
//        var_dump($vcode);die;
        session_id($sid);
        session_start();
        if($_SESSION['vcode']==$vcode){
           return [
                'status'=>1000,
                'msg'=>'验证成功',
                'data'=>[]
            ];
        }else{
            return [
                'status'=>500,
                'msg'=>'验证码错误',
            ];
        }
    }
    //验证登录
   public function getLogin(Request $request){
       $tel=$request->post('tel');
       $pwd=$request->post('pwd');
    //    var_dump($tel);
    //    var_dump($pwd);die;
       $where = [
           'name' => $tel,
       ];
       $res = UserModel::where($where)->first();
       if ($res) {
           if (password_verify($pwd, $res->pwd)){
               return [
                   'status'=>1000,
                   'msg'=>'登录成功'
               ];
           } else {
               return [
                   'status'=>5001,
                   'msg'=>'账号或密码错误'
               ];
           }
       }else{
           return [
               'status'=>5002,
               'msg'=>'账号或密码错误'
           ];
       }
   }
    //验证注册信息
  public function getRegister(Request $request){
      $tel=$request->post('u_tel');
      $pwd=$request->post('u_pwd');
      $pwd2=$request->post('u_pwd2');
      $vcode=$request->post('vcode');
      $telcode=$request->post('telcode');
      if(empty($tel)){
          return [
              'status'=>50001,
              'msg'=>'手机号不能为空'
          ];
      }
      if(empty($pwd)){
          return [
              'status'=>50002,
              'msg'=>'密码不能为空'
          ];
      }
      if(empty($pwd2)){
          return [
              'status'=>50003,
              'msg'=>'确认密码不能为空'
          ];
      }
      if($pwd!==$pwd2){
          return [
              'status'=>50004,
              'msg'=>'密码不一致'
          ];
      };
      if(empty($vcode)){
          return [
              'status'=>50005,
              'msg'=>'数字验证码不能为空'
          ];
      }
      if(empty($telcode)){
          return [
              'status'=>50006,
              'msg'=>'手机验证码不能为空'
          ];
      }
      $res=UserModel::where(['name'=>$tel])->first();
      if($res){
          return [
              'status'=>50007,
              'msg'=>'账号已存在'
          ];
      }
      $where=[
          'code'=>$telcode
      ];
      $res2=CodeModel::where($where)->first();
      if($res2){
          $pwd=password_hash($pwd,PASSWORD_BCRYPT);
          $data=[
              'name'=>$tel,
              'pwd'  =>$pwd,
              'reg_time'=>time(),
          ];
          $uid=UserModel::insertGetId($data);
          if($uid){
              return [
                  'status'=>1000,
                  'msg'=>'注册成功'
              ];
          }else{
              return [
                  'status'=>50008,
                  'msg'=>'注册失败'
              ];
          }
      }else{
          return [
              'status'=>50008,
              'msg'=>'手机验证码错误'
          ];
      }
  }
    //手机验证码发送
    public function getTelCode(Request $request){
        $tel= $request->post('tel');
        // var_dump($tel);die;
        if(empty($tel)){
            return [
                'msg'=>"手机号不能为空",
                'status'=>5
            ];
        }
        $where=[
            'name'=>$tel
        ];
        $res2=UserModel::where($where)->first();
//        var_dump($res2);die;
        if($res2){
            return [
                'status'=>5002,
                'msg'=>'手机号已存在'
            ];
        }else{
            $code=rand(111111,999999);
            $where=[
                'tel'=>$tel
            ];
            $res=CodeModel::where($where)->first();
            if(empty($res)){
                $data=[
                    'tel'=>$tel,
                    'code'=>$code,
                    'time'=>time()
                ];
                CodeModel::insert($data);

            }else if(time()-$res['time']<120){
                return [
                    'msg'=>'不能频繁发送',
                    'status'=>6
                ];
            }else{
                $data=[
                    'code'=>$code,
                    'time'=>time()
                ];
                CodeModel::where($where)->update($data);
            }
            $msg= $this->sendMessage($tel,$code);
            //print_r($msg);die;
            if($msg['Message']=="OK"){
                return [
                    'status'=>1000,
                    'msg'=>'发送成功'
                ];
            }else{
                return [
                    'status'=>7,
                    'msg'=>'发送失败'
                ];
            }
        }

    }
    //发送信息
    public  function  sendMessage($tel ,$code){
        $code=[
            'code'=>$code
        ];
        AlibabaCloud::accessKeyClient('LTAIzwdubfUCZOnY', 'xwloQ6gxL5CKEC6qHWgoP9GvAfSTVG')
            ->regionId('cn-hangzhou') // replace regionId as you need
            ->asGlobalClient();

        try {
            $result = AlibabaCloud::rpcRequest()
                ->product('Dysmsapi')
                // ->scheme('https') // https | http
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->options([
                    'query' => [
                        'PhoneNumbers' => $tel,
                        'SignName' => 'layui',
                        'TemplateCode' => 'SMS_151830156',
                        'TemplateParam' => json_encode($code),
                    ],
                ])
                ->request();
            return  $result->toArray();
        } catch (ClientException $e) {
            echo $e->getErrorMessage() . PHP_EOL;
        } catch (ServerException $e) {
            echo $e->getErrorMessage() . PHP_EOL;
        }
    }
}

