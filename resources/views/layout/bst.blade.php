<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{csrf_token()}}">
    <title>BootStrap</title>
    <link rel="stylesheet" href="{{URL::asset("/bootstrap/css/bootstrap.css")}}">
</head>
<body>
<div class="container">
<nav class="navbar navbar-inverse" role="navigation">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" href="/">首页</a>
        </div>
        <div>
            <ul class="nav navbar-nav">
                <li class="active"><a href="/goodslist">商品列表</a></li>
                <li class="active"><a href="/cart/index">购物车列表</a></li>
                <li><a href="#"></a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right hidden-sm" >
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        个人中心 <b class="caret"></b>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="/order/list">我的订单</a></li>
                        <li><a href="#">待收货</a></li>
                        <li><a href="#">Jasper Report</a></li>
                        <li class="divider"></li>
                        <li><a href="#">MyMoney</a></li>
                        <li class="divider"></li>
                        <li><a href="#">MyCore</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
@yield('content')
</div>
@section('footer')
    <script src="{{URL::asset('/js/jquery-1.12.4.min.js')}}"></script>
    <script src="{{URL::asset('/bootstrap/js/bootstrap.js')}}"></script>
    @show
    </body>
    </html>