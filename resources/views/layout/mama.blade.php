<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Lening-@yield('title')</title>
</head>
    @section('header')
        <p style="color:red;">我是你妈妈</p>
    @show
    <div class="container">
        @yield('content')
    </div>
    @section('footer')
        <p style="color:blue">这是你妈妈的脚</p>
    @show
</body>
</html>