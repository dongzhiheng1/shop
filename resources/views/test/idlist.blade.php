@extends('layout.bst')
@section('content')
    <h2>审核列表</h2>
    <div class="container">
        <table  class="table table-bordered">
            <tbody>
            <tr>
                <td>id</td>
                <td>用户名</td>
                <td>接口用途</td>
                <td>审核状态</td>
            </tr>
            </tbody>
            <tbody>
            @foreach($list as $k=>$v)
                <tr>
                    <td>{{$v['id']}}</td>
                    <td>{{$v['name']}}</td>
                    <td>{{$v['use']}}</td>
                    @if($v['is_pass']==1)
                        <td>已通过</td>
                    @else
                        <td><a href="/check/{{$v['id']}}">审核</a></td>
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