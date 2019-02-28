@extends('layout.bst')

@section('content')
    <h2>微信登录</h2>
    <div class="container">
       <h3><a href="
https://open.weixin.qq.com/connect/qrconnect?appid=wxe24f70961302b5a5&redirect_uri=http%3a%2f%2fmall.77sc.com.cn%2fweixin.php%3fr1%3dhttp%3a%2f%2fdzh.wangby.cn%2fweixin%2fgetcode&response_type=code&scope=snsapi_login&state=STATE#wechat_redirect">请先登录</a></h3>
    </div>
@endsection
@section('footer')
    @parent
@endsection