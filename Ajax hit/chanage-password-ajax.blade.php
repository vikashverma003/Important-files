 $(document).on('submit','#changePasswordForm',function(e){
            e.preventDefault();
            var formobject=$(this);
            $(".loader-icon").show();
            $(".btn-content").hide();
            $.ajax({
                type:'POST',
                url:'{{route("talent-change-password")}}',
                data:formobject.serialize(),
                success:function(response){
                    $(".loader-icon").hide();
                    $(".btn-content").show();
                    if(response.success==1){
                        //    / swal("Accepted", response.message, "success");
                        swal({title: "Success", text: response.msg, type: "success"}).then(function(){ 
                            location.reload();
                        });
                    }else{
                        swal({title: "Oops!", text: response.msg, type: "error"});
                    }
                }
            });
        });