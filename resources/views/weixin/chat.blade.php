@extends('layout.bst')

@section('content')
    <div class="container">
        <h2>聊天室{{$openid}}</h2>
        <div class="chat" id="chat_div">
        </div>
        <hr>

        <form action="" class="form-inline">
            <input type="hidden" value="{{$openid}}" id="openid">
            <input type="hidden" value="1" id="msg_pos">
            <textarea name="" id="send_msg" cols="100" rows="5"></textarea>
            <button class="btn btn-info" id="send_msg_btn">Send</button>
        </form>
    </div>
@endsection
@section('footer')
    @parent
    <script src="{{URL::asset('/js/weixin/chat.js')}}"></script>
@endsection