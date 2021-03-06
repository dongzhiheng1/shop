<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
//Route::get('/', function () {
//   phpinfo();
//});

//Route::get('/', function () {
//    echo date("Y-m-d H:i:s");
//});

Route::get('/user/add','User\UserController@add');

//路由跳转
Route::redirect('/hello1','/world1',301);
Route::get('/world1','Test\TestController@world1');

Route::get('hello2','Test\TestController@hello2');
Route::get('world2','Test\TestController@world2');


//路由参数
Route::get('/user/test','User\UserController@test');
//Route::get('/user/{uid}','User\UserController@user');
Route::get('/month/{m}/date/{d}','Test\TestController@md');
Route::get('/name/{str?}','Test\TestController@showName');



// View视图路由
Route::view('/mvc','mvc');
Route::view('/error','error',['code'=>40300]);


// Query Builder
Route::get('/query/get','Test\TestController@query1');
Route::get('/query/where','Test\TestController@query2');


//Route::match(['get','post'],'/test/abc','Test\TestController@abc');
Route::any('/test/abc','Test\TestController@abc');


Route::get('/view/test1','Test\TestController@viewTest1');
Route::get('/view/test2','Test\TestController@viewTest2');

//注册
Route::get('/user/reg','User\UserController@register');
Route::post('/user/reg','User\UserController@doRegister');

//////登录
Route::get('/user/login','User\UserController@login');
Route::post('/user/login','User\UserController@doLogin');
Route::get('/user/changepwd','User\UserController@changepwd');
Route::post('/user/changepwddo','User\UserController@changepwddo');


//中间件
Route::get('/test/checkcookie','Test\TestController@checkcookie');
Route::get('/test/mid2','Test\TestController@mid2');

//购物车
//登录
Route::get('/cart/index','Cart\IndexController@index');
//添加购物车
//Route::get('/cart/add/{goods_id}','Cart\IndexController@add')->middleware('check.login');
//添加购物车
Route::post('/cart/add2','Cart\IndexController@add2');
//删除购物车
//Route::get('/cart/del/{goods_id}','Cart\IndexController@del')->middleware('check.login');
//删除购物车
Route::get('/cart/del2/{goods_id}','Cart\IndexController@del2');


//商品详情页
Route::get('/goods/{goods_id}','Goods\IndexController@index');

//商品展示
Route::get('/goodslist','Goods\IndexController@list');

//退出
Route::get('/user/quit','User\UserController@quit');

//订单
Route::get('/order/add','Order\IndexController@add');

//订单展示
Route::get('/order/list','Order\IndexController@list');

//订单支付
//Route::get('/pay/order/{order_id}','Pay\IndexController@order')->middleware('check.login');
Route::get('/pay/alipay/pay/{order_id}','Pay\AlipayController@pay');
Route::get('/pay/alipay/return','Pay\AlipayController@aliReturn');//支付宝同步
Route::post('/pay/alipay/notify','Pay\AlipayController@aliNotify');//支付宝异步

Auth::routes();
Route::get('/home', 'HomeController@index')->name('home');


//文件上传
Route::get('/upload','Goods\IndexController@upload');
Route::post('/goods/uploadpdf','Goods\IndexController@uploadPDF');


Route::get('/movie/show','Movie\IndexController@index');
Route::get('/movie/buy/{pos}/{status}','Movie\IndexController@buy');


//微信
Route::get('/weixin/test','Weixin\WeixinController@test');
Route::get('/weixin/valid','Weixin\WeixinController@validToken');
Route::get('/weixin/valid1','Weixin\WeixinController@validToken1');
Route::post('/weixin/valid1','Weixin\WeixinController@wxEvent');        //接收微信服务器事件推送
Route::post('/weixin/valid','Weixin\WeixinController@validToken');



//微信公众号菜单
Route::get('/weixin/menu','Weixin\WeixinController@wxMenu');
//单发
Route::post('/weixin/send','Weixin\WeixinController@sendAll');


//素材
Route::get('/weixin/fodder','Weixin\WeixinController@fodder');


//测试
Route::get('/weixin/show','Weixin\WeixinController@formShow');
Route::post('/weixin/test','Weixin\WeixinController@formTest');

//单发
Route::get('/weixin/one','Weixin\WeixinController@one');


//微信聊天
Route::get('/weixin/chat','Weixin\WeixinController@chatShow');
Route::get('/weixin/get_msg','Weixin\WeixinController@getChatMsg');
Route::post('/weixin/weixinChat','Weixin\WeixinController@weixinChat');

//微信支付
Route::get('/weixin/pay/test/{order_id}','Weixin\PayController@test');
Route::post('/weixin/pay/notice','Weixin\PayController@notice');
Route::get('/weixin/pay/wxsuccess','Weixin\PayController@WxSuccess');


//微信的登录
Route::get('/weixin/login','Weixin\WeixinController@wxLogin');
Route::get('/weixin/getcode','Weixin\WeixinController@getCode');
//微信jssdk
Route::get('/weixin/jssdk','Weixin\WeixinController@jsSdk');
Route::get('/weixin/token','Weixin\WeixinController@token');

//微信菜单
Route::get('/weixin/menu','Menu\MenuController@menuAll');
Route::get('/weixin/token','Menu\MenuController@getAccessToken');
Route::post('/weixin/smenu','Menu\MenuController@wxMenu');

//api
Route::get('/api','Test\TestController@api');
Route::post('/encrypt','Test\TestController@encrypt');

Route::post('/sign','Test\TestController@sign');


Route::post('/pub','Test\TestController@pub');
Route::get('/fbnq','Test\TestController@fbnq');
Route::post('/a','Test\TestController@a');
Route::post('/test/login','Test\TestController@login');
Route::get('/persion','Test\TestController@persion')->middleware('check.cookie');



Route::post('/user','Test\TestController@user');


Route::get('/','Test\TestController@index')->middleware('check.cookie');
//个人中心
Route::post('/center','Test\TestController@center');
//支付宝支付
Route::post('/test/alipay/{order_id}','Test\TestController@alipay');


//测试商品展示
Route::get('g/list','Test\TestController@goodslist');





//pc登录
Route::get('/a/login','Login\IndexController@Alogin');
Route::post('/b/login','Login\IndexController@doAlogin');
//用户列表
Route::get('/u/list','Login\IndexController@userList');

//app登录
Route::post('/app','Login\IndexController@receive');
Route::post('/a/center','Login\IndexController@center');

//定时查询
Route::post('/yz','Login\IndexController@yz');

Route::get('/aaa','Test\TestController@aaa');

//申请页面
Route::get('/cgi','Test\TestController@cgi');

//处理页面
Route::post('/cgia','Test\TestController@cgia');

//审核列表
Route::get('/idlist','Test\TestController@idlist');

//审核
Route::get('/check/{id}','Test\TestController@check');

//验证签名
Route::get('/check/{id}','Test\TestController@check');

//接口加密解密
Route::any('/api/login','Api\IndexController@api')->middleware('check.api');

//多端测试
Route::post('/all/login/{tty}','Test\TestController@allLogin');

//文件上传
Route::post('/api/upload','Api\IndexController@uploadImg')->middleware('check.api');

//测试get方式
Route::get('abc/','Api\IndexController@abc');

/*前台验证
************
*/
//传sid
Route::post('/api/code','Api\IndexController@vCode')->middleware('check.api');


//验证码展示
Route::get('/vcode/{sid}/{r}','Api\IndexController@code');

//接收传过来的数据
Route::post('/getvcode','Api\IndexController@getVcode')->middleware('check.api');

//登录验证
Route::post('/getlogin','Api\IndexController@getLogin')->middleware('check.api');


//注册验证
Route::post('/getregister','Api\IndexController@getRegister')->middleware('check.api');

//手机号发送信息
Route::post('/gettelcode','Api\IndexController@getTelCode')->middleware('check.api');

/**
 *
 * 后台接收数据
 */
Route::post('/getcodelist','Api\AdminController@codeList')->middleware('check.api');



//接收搜索的手机号返回数据

Route::post('/code/search','Api\AdminController@searchTelCode')->middleware('check.api');
