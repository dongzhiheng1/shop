<?php

namespace App\Admin\Controllers;

use App\Model\WeixinMaterial;
use App\Model\WeixinUser;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use GuzzleHttp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class WeixinMaterialController extends Controller
{
    protected $redis_weixin_access_token = 'str:weixin_access_token';     //微信 access_token
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('Index')
            ->description('description')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('Create')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new WeixinMaterial);

        $grid->id('Id');
        $grid->media_id('Media id');
        $grid->new_file_name('New file name');

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(WeixinMaterial::findOrFail($id));

        $show->id('Id');
        $show->media_id('Media id');
        $show->new_file_name('New file name');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */



    protected function form()
    {
        $form = new Form(new WeixinMaterial);
        $form->file('media','上传');
        return $form;
    }

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


    public function formTest(Request $request)
    {
        $img_file = $request->file('media');
//        var_dump($img_file);die;
        $img_origin_name = $img_file->getClientOriginalName();
//        var_dump($img_origin_name);
        $file_ext = $img_file->getClientOriginalExtension();
//        var_dump($file_ext);
        $new_file_name = str_random(15) . '.' . $file_ext;
//        var_dump($new_file_name);
        //文件保存路径

        //保存文件
        $save_file_path = $request->media->storeAs('form_test', $new_file_name);
//        var_dump($save_file_path);


        //上传至微信永久素材
        $this->upMaterialTest($save_file_path);

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
//        print_r($d);die;
        $data=[
            'media_id'=>$d['media_id'],
            'url'=>$d['url']
        ];
        $mid=WeixinMaterial::insertGetId($data);
//        var_dump($mid);
        if($mid){
            echo "上传成功";
        }else{
            echo "上传失败";
        }
    }
    //微信群发
    public function sendAll(Request $request)
    {

        $name=$request->all();
        $content= $name['name'];
        $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=' . $this->getWXAccessToken();
        //echo $url;exit;
        //openid
        $wxUserInfo = WeixinUser::get()->toArray();
        //var_dump($wxUserInfo);
        foreach ($wxUserInfo as $v) {
            $openid[] = $v['openid'];
        }
        //print_r($openid);
        //文本群发消息
        $data = [
            "touser" => $openid,
            "msgtype" => "text",
            "text" => [
                "content" => $content
            ]
        ];
        $client = new GuzzleHttp\Client(['base_uri' => $url]);
        $r = $client->request('POST', $url, [
            'body' => json_encode($data, JSON_UNESCAPED_UNICODE)
        ]);
        $respone_arr = json_decode($r->getBody(), true);
        if($respone_arr['errcode']==0){
            echo "群发成功";
        }
    }
    public function sendShow(Content $content)
    {
        $f = new \Encore\Admin\Widgets\Form();
        $f->action('admin/sendAll');
        $f->textarea('name', '群发');
        return $content
            ->header('Create')
            ->description('description')
            ->body($f);
    }

}
