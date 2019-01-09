@extends('layout.bst')

@section('content')
    <div class="container">
        <table  class="table table-bordered">
            <tbody>
            <tr>
                <td>商品名称</td>
                <td>商品价格</td>
                <td>添加时间</td>
                <td>操作</td>
            </tr>
            </tbody>
            <tbody>
            @foreach($list as $k=>$v)
                <tr>
                    <td>{{$v['goods_name']}}</td>
                    <td>{{$v['goods_price']}}</td>
                    <td>{{date('Y-m-d H:i:s',$v['add_time'])}}</td>
                    <td><a href="/cart/del2/{{$v['goods_id']}}">删除</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endsection
@section('footer')
    @parent
    @endsection