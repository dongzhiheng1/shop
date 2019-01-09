$('#add_cart_btn').click(function(e){
    e.preventDefault();
    var buy_num=$('#goods_num').val();
    var goods_id=$('#goods_id').val();
    $.ajax({
       header:{
           'X-CSRF-TOKEN':$("meta[name='csrf-token']").attr('content')
       },
        url : '/cart/add2',
        type : 'post',
        data :{goods_id:goods_id,buy_num:buy_num},
        dataType:'json',
        success :function(result){
           if(result.error==301){
               window.location.href=result.url;
           }else{
               alert(result.msg)
           }
        }
    })
})