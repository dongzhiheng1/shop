@extends('layout.bst')
@section('content')
    <h2>商品列表</h2>
    <div class="container">
        <table  class="table table-bordered">
            <tbody>
            <tr>
                <td>ID</td>
                <td>NAME</td>
                <td>GOODS_NUMBER</td>
                <td>OPERATIION</td>
            </tr>
            </tbody>
            <tbody>
            @foreach($list as $k=>$v)
                <tr>
                    <td style="width:200px">{{$v['goods_id']}}</td>
                    <td style="width:200px">{{$v['goods_name']}}</td>
                    <td style="width:200px">{{$v['goods_num']}}</td>
                    <td style="width:200px"><a href="/goods/{{$v['goods_id']}}">查看商品详情</a></td>
                </tr>

            @endforeach
            </tbody>
        </table>
        {{$list->links()}}
    </div>
@endsection
@section('footer')
    @parent
@endsection