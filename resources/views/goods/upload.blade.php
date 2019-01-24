@extends('layout.bst')
@section('content')
<form class="form-horizontal" method="post" action="/goods/uploadpdf" enctype="multipart/form-data">
{{csrf_field()}}
    <input type="file" class="form-control" name="pdf" >
    <input type="submit" class="form-control" value="上传" >
</form>
@endsection('content')

