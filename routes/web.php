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

//登录
Route::get('/user/login','User\UserController@login');
Route::post('/user/login','User\UserController@doLogin');
Route::get('/user/center','User\UserController@center');


//中间件
Route::get('/test/checkcookie','Test\TestController@checkcookie')->middleware('check.cookie');
Route::get('/test/mid2','Test\TestController@mid2')->middleware('check.uid');

//购物车
//登录
Route::get('/cart/index','Cart\IndexController@index')->middleware('check.login');
//添加购物车
//Route::get('/cart/add/{goods_id}','Cart\IndexController@add')->middleware('check.login');
//添加购物车
Route::post('/cart/add2','Cart\IndexController@add2')->middleware('check.login');
//删除购物车
//Route::get('/cart/del/{goods_id}','Cart\IndexController@del')->middleware('check.login');
//删除购物车
Route::any('/cart/del2/{goods_id}','Cart\IndexController@del2')->middleware('check.login');


//商品详情页
Route::get('/goods/{goods_id}','goods\IndexController@index');

//退出
Route::any('/user/quit','user\UserController@quit');

//订单
Route::any('/order/add','Order\IndexController@add')->middleware('check.login');

//订单展示
Route::any('/order/list','Order\IndexController@list')->middleware('check.login');

//订单支付
Route::any('/pay/order/{order_id}','Pay\IndexController@order')->middleware('check.login');
