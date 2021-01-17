
        $(document).on('submit','#editProfileForm',function(e){
            e.preventDefault();
            var formobject=$(this);
            $('#editProfileForm').find('.error_span').html('');
            $.ajax({
                type:'POST',
                url:'{{route("talent_profile_update")}}',
                data:formobject.serialize(),
                beforeSend:function(){
                    startLoader('body');
                },
                complete:function(){
                    stopLoader('body');
                },
                success:function(response){
                    if(response.status){
                        swal({title: "Success", text: response.message, type: "success"}).then(function(){ 
                            location.reload();
                        });
                    }else{
                        swal({title: "Oops!", text: response.message, type: "error"});
                    }
                },
                error:function(data){
                    stopLoader('body');
                    if(data.responseJSON){
                        var err_response = data.responseJSON;  
                        if(err_response.errors==undefined && err_response.message) {
                            swal({title: "Error!", text: err_response.message, type: "error"});
                        }          
                        $.each(err_response.errors, function(i, obj){
                            $('#editProfileForm').find('.error_span.'+i+'_error').text(obj).show();
                        });
                    }
                }
            });
        });

        $(document).on('submit','#editWorkExpForm',function(e){
            e.preventDefault();
            var formobject=$(this);
            var $form = $('#editWorkExpForm');
            $form.find('.error_span').html('');
            $.ajax({
                type:'POST',
                url:'{{route("talent_workexp_update")}}',
                data:formobject.serialize(),
                beforeSend:function(){
                    startLoader('body');
                },
                complete:function(){
                    stopLoader('body');
                },
                success:function(response){
                    if(response.status){
                        swal({title: "Success", text: response.message, type: "success"}).then(function(){ 
                            location.reload();
                        });
                    }else{
                        swal({title: "Oops!", text: response.message, type: "error"});
                    }
                },
                error:function(data){
                    stopLoader('body');
                    if(data.responseJSON){
                        var err_response = data.responseJSON;  
                        if(err_response.errors==undefined && err_response.message) {
                            swal({title: "Error!", text: err_response.message, type: "error"});
                        }          
                        $.each(err_response.errors, function(i, obj){
                            $form.find('.error_span.'+i+'_error').text(obj).show();
                        });
                    }
                }
            });
        });