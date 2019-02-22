<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
</head>
<body>
     <form action="/weixin/test" method="post" enctype="multipart/form-data">
         {{csrf_field()}}
         <input type="test" name="test"><br/><br/><br/>
         <input type="file" name="media"><br/><br/><br/>
         <input type="submit" value="上传">
     </form>
</body>
</html>