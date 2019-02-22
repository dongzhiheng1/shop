<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    $router->resource('/goods',GoodsController::class);
    $router->resource('/wx/wxusers',WeixinController::class);
    $router->resource('/wx/wxmedia',WeixinMediaController::class);
    //永久素材上传
    $router->resource('/wx/wxmaterial',WeixinMaterialController::class);
    $router->post('/wx/wxmaterial','WeixinMaterialController@formTest');
});

