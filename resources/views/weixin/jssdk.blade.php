@extends('layout.bst')
@section('content')
    <h2>jssdk</h2>
    <button id="btn">选择照片</button>
@endsection
@section('footer')
    @parent
    <script src="{{URL::asset('/js/weixin/chat.js')}}"></script>
    <script src="https://res2.wx.qq.com/open/js/jweixin-1.4.0.js"></script>
    <script>
        wx.config({
            debug: true, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
            appId: "{{$jsconfig['appid']}}", // 必填，公众号的唯一标识
            timestamp: "{{$jsconfig['timestamp']}}", // 必填，生成签名的时间戳
            nonceStr: "{{$jsconfig['noncestr']}}", // 必填，生成签名的随机串
            signature: "{{$jsconfig['sign']}}",// 必填，签名
            jsApiList: ['chooseImage', 'uploadImage', 'getLocalImgData', 'startRecord'] // 必填，需要使用的JS接口列表
        });
        wx.ready(function(){
            $("#btn").click(function () {
                wx.chooseImage({
                    count: 9, // 默认9
                    sizeType: ['original', 'compressed'], // 可以指定是原图还是压缩图，默认二者都有
                    sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
                    success: function (res) {
                        var localIds = res.localIds; // 返回选定照片的本地ID列表，localId可以作为img标签的src属性显示图片
                    }
                });
            });
        });
    </script>
@endsection