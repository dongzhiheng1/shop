<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
</head>
<body>
      <form method="post" action="/weixin/smenu">
          <table class="table">
              {{csrf_field()}}
              <tr class="omenu">
                  <td>
                      <input type="button" value="一级按钮" >名字:<input type="text" name="firstname"> <input type="button" value="克隆" id="one">
                  </td>
              </tr>
              <tr>
                  <td>
                      <input type="button" value="二级按钮"> <input type="button" value="克隆" id="tmenu"><br>
                      按钮类型:
                      <select>
                          <option> 请选择...</option><br>
                      </select>
                      二级按钮名字:<input type="text" name="secondname"><br>
                      二级按钮url:<input type="text" name="secondurl"><br>
                      二级按钮名字key:<input type="text" name="secondkey"><br>
                  </td>
              </tr>
          </table>
          <div>
              <input type="submit" value="发布" id="send">
          </div>
      </form>
</body>
<script src="{{URL::asset('/js/jquery-1.12.4.min.js')}}"></script>
<script src="{{URL::asset('/bootstrap/js/bootstrap.js')}}"></script>
</html>
<script>
    $(function(){
        $(document).on('click','#one',function(){
            _this=$(this);
           par=_this.parents('table').clone();
           _this.parents('table').after(par)
            if(_this.parents('table').siblings('table')>=4){
                $('#one').attr('disable',true);
            }
        })
        $(document).on('click','#tmenu',function(){
            _this=$(this);
            clone=_this.parent('td').clone()
           part= _this.parent('td').after(clone);
        })

    })
</script>