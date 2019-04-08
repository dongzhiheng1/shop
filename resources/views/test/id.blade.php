@extends('layout.bst')
@section('content')
    <form class="form-horizontal"  enctype="multipart/form-data" method="post" action="/cgia">
        {{csrf_field()}}
        <div class="form-group">
            <label for="inputEmail3" class="col-sm-2 control-label">姓名</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="u_name" >
            </div>
        </div>
        <div class="form-group">
            <label for="inputPassword3" class="col-sm-2 control-label">身份证号</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="id_card">
            </div>
        </div>
        <div class="form-group">
            <label for="inputPassword3" class="col-sm-2 control-label">上传身份证图片</label>
            <div class="col-sm-10">
                <input type="file"  name="image">
            </div>
        </div>
        <div class="form-group">
            <label for="inputPassword3" class="col-sm-2 control-label">接口用途</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="use">
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-primary">注册</button>
            </div>
        </div>
    </form>
@endsection