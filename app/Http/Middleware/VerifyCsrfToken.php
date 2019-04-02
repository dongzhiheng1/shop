<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        //
        '/pay/alipay/notify',
        '/weixin/valid',
        '/weixin/valid1',
        '/weixin/material',
        '/weixin/pay/notice',
        '/weixin/pay/wxsuccess',
        '/encrypt',
        '/sign',
        '/pub',
        '/a',
        '/test/*',
        '/user',
        '/center',
        '/app',
        '/a/center',
        '/yz'
    ];
}
