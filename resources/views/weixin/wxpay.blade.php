<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
    <script src="{{URL::asset('/js/qrcode.js')}}"></script>
    <script src="{{URL::asset('/js/jquery-1.12.4.min.js')}}"></script>
</head>
<body>
   <div id="qrcode" align="center"></div>
</body>
</html>
<script>
    // 设置参数方式
    var qrcode = new QRCode('qrcode', {
        text:"you content",
        width: 256,
        height: 256,
        colorDark : '#000000',
        colorLight : '#ffffff',
        correctLevel : QRCode.CorrectLevel.H
    });
    // 使用 API
    qrcode.clear();
    qrcode.makeCode("{{$url}}");
</script>