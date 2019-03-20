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