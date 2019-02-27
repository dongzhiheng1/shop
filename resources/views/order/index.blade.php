@extends('layout.bst')
@section('content')
    <h2>订单列表</h2>
    <div class="container">
        <table  class="table table-bordered">
            <tbody>
            <tr>
                <td>订单号</td>
                <td>订单金额</td>
                <td>订单状态</td>
                <td>操作</td>
            </tr>
            </tbody>
            <tbody>
            @foreach($list as $k=>$v)
                <tr>
                    <td style="width:200px">{{$v->order_number}}</td>
                    <td style="width:200px">￥{{$v->order_amount/100}}</td>
                    @if($v['is_pay']==1)
                    <td style="width:200px"> 已支付 </td>
                    <td style="width:200px">
                        <a  class="btn btn-primary" id="add_cart_btn" >查看订单</a>
                    </td>
                    @else
                        <td style="width:200px">未支付</td>
                    <td style="width:200px"><a  class="btn btn-primary" id="add_cart_btn" href="/pay/alipay/pay/{{$v['order_id']}}">支付宝支付</a>
                        <a  class="btn btn-primary" id="add_cart_btn" href="/weixin/pay/test/{{$v['order_id']}}">微信支付</a>
                        <a  class="btn btn-primary" id="add_cart_btn" >取消订单</a>
                    </td>
                    @endif
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endsection
@section('footer')
    @parent
@endsection