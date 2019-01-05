@extends('layout.mama')
@section('title')
{{$title}}
@endsection

@section('header')
    @parent
    <p style="color:green;"></p>
@endsection

@section('content')
    <p>这里是 child content</p>
    <table border="1">
        <thead>
        <td>UID</td><td>Name</td><td>Age</td><td>Email</td><td>Reg_time</td>
        </thead>
        <tbody>
            @foreach($list as $v)
                <tr>
                    <td>{{$v['u_id']}}</td>
                    <td>{{$v['name']}}</td>
                    <td>{{$v['age']}}</td>
                    <td>{{$v['email']}}</td>
                    <td>{{date("Y-m-d H:i:s",$v['reg_time'])}}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

@section('footer')
    @parent
    <p style="color:yellow">这是child footer</p>
@endsection