@extends('layout.bst')

@section('content')

<div class="container">
    <h1>{{$goods->goods_name}}</h1>
    <span> 价格：￥{{$goods->goods_price/100}}</span>
    <form class="form-inline">
        <div class="form-group">
            <label class="sr-only" for="goods_num">Amount (in dollars)</label>
            <div class="input-group">
                <input type="text" class="form-control" id="goods_num" value="1">
            </div>
        </div>
        <input type="hidden" id="goods_id" value="{{$goods->goods_id}}">
        <button type="submit" class="btn btn-primary" id="add_cart_btn">加入购物车</button>
    </form>
</div>
@endsection
@section('footer')
    @parent
  <script src="{{URL::asset('/js/goods/goods.js')}}"></script>
@endsection
