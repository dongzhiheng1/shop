<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>BootStrap</title>
    <link rel="stylesheet" href="{{URL::asset("/bootstrap/css/bootstrap.css")}}">
</head>
<body>
    <div>
        @yield('content')
    </div>
    @section('footer')
        <script src="{{URL::asset('/js/jquery-1.12.4.min.js')}}"></script>
        <script src="{{URL::asset('/bootstrap/js/bootstrap.js')}}"></script>
    @show
</body>
</html>