<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{csrf_token()}}">
    <title>Document</title>
    <script src="{{URL::asset('/js/jquery-1.12.4.min.js')}}"></script>
</head>
<body>
     <b class="openid">o4Xdz5_z78eeXZaR89xdN6vb4Yek</b>
     <div style="border: black solid 1px;height:200px;width:200px;overflow: auto" id="ms"></div>
     <input type="text" class="message">
     <input type="button" value="发送"  id='send'>
</body>
<script>
    $(function(){
        $('#send').click(function(e){
            e.preventDefault();
            var _this=$(this)
            message=_this.prev().val()
           //ms= _this.prevAll('div[id=ms]').text(message)
            $('#ms').append(message+"<br/>")
            _this.prev().val('')
            openid=_this.prevAll('b').text();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url : '/weixin/send',
                type : 'post',
                data :{openid:openid,message:message},
                dataType:'json',
                success :function(result){
                    console.log(result)
                }
            });
        })
    })
</script>