
$(document).ready(function(){
	$(document).on("submit","#addcardform",function($e){
        $e.preventDefault();
        $(".loader-icon").show();
        $(".btn-content").hide();
       
        $.ajax({
            type:'POST',
            url:'{{$cardregistrtion->CardRegistrationURL}}',
            data:$(this).serialize(),
            success:function(response){
                $(".loader-icon").hide();
                $(".btn-content").show();
                console.log(response);
                $.ajax({
                    type:'POST',
                    url:'{{route("save_card")}}',
                    data:{ "_token": "{{ csrf_token() }}","card_id":"{{$cardregistrtion->Id}}","card_token":response},
                    beforeSend:function(){
                        startLoader('body');
                    },
                    complete:function(){
                        stopLoader('body');
                    },
                    success:function(response){
                        $(".loader-icon").hide();
                        $(".btn-content").show();
                        if(response.ResultMessage=='Success' && response.Status=='VALIDATED'){
                            swal({title: "Card Added Success", text: 'Card added to mangopay successfully', type: "success"}).then(function(){ 
                                    window.location.href = response.backUrl;
                                });
                        }else{
                            swal({title: "OOps", text: 'There is error to add card in mangopay', type: "error"}).then(function(){ 
                                location.reload();
                            });
                        }
                    },
                    error:function(){
                        stopLoader('body');
                    }
                });   
            }
        });
	});
});


</script>