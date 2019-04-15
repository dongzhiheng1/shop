<?php
namespace App\Http\Controllers\Api;
use App\Model\CodeModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\UserModel;
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

class AdminController extends Controller
{
    //查找数据
    public function codeList(Request $request)
    {
//        echo 1111;die;
        $codeInfo = CodeModel::get()->toArray();
//           var_dump($codeInfo);die;

        foreach($codeInfo as $k=>$v){
            if(time() - $v['time'] >300){
                $codeInfo[$k]['is_valid'] = '已过期';
            }else{
                $codeInfo[$k]['is_valid'] = '可用';
            }
          $codeInfo[$k]['time']=(date('Y-m-d H:i:s',$v['time']));
        }
        $count = CodeModel::count();
//           var_dump($count);die;
        $info = [
            'code' => 0,
            'msg' => '',
            'count' => $count,
            'data' => $codeInfo,
        ];
        return $info;

    }

    //搜索数据
    public function searchTelCode(Request $request){
        $tel = $request->input('tel');
        if($tel){
            $tel_code_info = CodeModel::where('tel','like',$tel.'%')->get();
            $count = CodeModel::where('tel','like',$tel.'%')->count();
        }else{
            $tel_code_info = CodeModel::all();
            $count = CodeModel::count();
        }

        if(empty($tel_code_info)){
            return [
                'status'    =>  1000,
                'msg'       =>  '',
                'data'      =>  [],
                'count'     =>  0,
            ];
        }
        $info = $tel_code_info->toArray();
        foreach ($info as $k => $v){
//            var_dump($v);die;
            if(time() - $v['time'] >300){
                $info[$k]['is_valid'] = '已过期';
            }else{
                $info[$k]['is_valid'] = '可用';
            }
            $info[$k]['time'] = date('Y-m-d H:i:s' , $v['time']);

        }
//         var_dump($info);die;
        $data=[
            'code'      =>   0,
            'status'    =>  1000,
            'msg'       =>  '',
            'data'      =>  $info,
            'count'     =>  $count
        ];
        return $data;
    }
}

