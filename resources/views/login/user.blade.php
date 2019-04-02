@extends('layout.bst')
@section('content')
    <h2>用户列表</h2>
    <div class="container">
        <table  class="table table-bordered">
            <tbody>
            <tr>
                <td>id</td>
                <td>用户名</td>
                <td>在线状态</td>
            </tr>
            </tbody>
            <tbody>
            @foreach($list as $k=>$v)
                <tr>
                    <td>{{$v['u_id']}}</td>
                    <td>{{$v['name']}}</td>
                    @if($v['is_login']==1)
                    <td>在线</td>
                        @else
                        <td>离线</td>
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