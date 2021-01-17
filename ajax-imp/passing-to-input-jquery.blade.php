@extends('admin.layouts.app')
@section('title',$title)
@section('content')
<div class="content-wrapper">
   <div class="row">
      <div class="col-lg-12 stretch-card">

         <div class="card">
            <div class="card-body">
           @if (session('er_status'))
                  <div class="alert alert-danger">{!! session('er_status') !!}</div>
                @endif
                @if (session('su_status'))
                  <div class="alert alert-success">{!! session('su_status') !!}</div>
                @endif
               <h4 class="card-title">{{$title}}</h4>
               <input type="text" placeholder="Search.." id="searchUser" class="searchUser" style="width:655px;">
               <a class="nav-link add_button" href="{{route('users.create')}}">
                <i class=" icon-plus menu-icon"></i>
                <span class="menu-title">Add</span>
              </a>
              
               <a href="{{url('/admin/download_csv_all_user')}}"><button type="button" class="btn btn-primary btn-sm" id="change-btn" style="float:right" >Get All User Data</button></a><br></br><br>		 
			   <div class="form-group">
			    <div class="row">
				<div class="col-sm-10"></div>
				<div class="col-sm-2 show-button">
			   <button type="button" name="check_data" class="check_data">Get checked data</button> 
			   </div>
			   </div>
			   </div>
			   <div class="table-responsive hide-table">
                  <table class="table" id="study-table">
                     <thead>
                        <tr>
						<th>Check</th>
                           <th>User Id</th>
                           <!-- <th>User Type</th> -->
                           <th>Study Id</th>

                           <th>Option</th>
                           <th>Report</th>
                           <th>Info</th>
                           <th>Change Pin</th>
                        </tr>
                     </thead>
                      <tbody>
                  @foreach($users_info as $users)
                  <tr>
				  <td><input type="checkbox" name="check_id" data-target="#check_id" value="{{$users['userId']}}" class="check_id" class="form-control"</td>
                    <td>{{$users['userId']}}</td>
                    <!--<td>{{$users['userType']}}</td> -->
                   <td><a href="{{url('/admin/all_questions')}}/{{$users['study_id']}}">{{$users['studyId']}}</a></td>
                    <td>{{$users['options']}}</td>
                    <td><a href="{{url('/admin/download_csv_single_user')}}/{{$users['_id']}}"><i class="fa fa-download" aria-hidden="true">
Download</i></a></td>
                    <td><a data-toggle="modal" id="check" data-id="{{$users['_id']}}" class="passingID">
                         <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"   data-target="#ModalExample1">
                      Info</button></a></td>
                    <td><button type="button" class="btn btn-primary btn-sm update_id" data-id="{{$users['_id']}}"   data-toggle="modal" data-target="#ModalExample">
                    Change Pin</button></td>
                  </tr>
<!-- /.modal-dialog -->
            </div><!-- /.modal -->
                  @endforeach
                </tbody>
                                    </table>
                                    {{$user->links()}}

               </div>
			   
               <!-- Modal HTML Markup -->
            <div id="ModalExample" class="modal fade">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title text-xs-center">Update Pin</h4>
                        </div>
                        <div class="modal-body">
                            <form id="editPin">
                                @csrf
                                <input type="hidden" name="_token" value="">
                               <!-- <input type="hidden" name="user_id1" id="user_id1" value="{{$users['_id']}}"/> -->
							   <input type="hidden" name="cafeId" id="cafeId" />

                                 <div class="form-group required">
                                  <div class="row">
                                        <div class="col-md-6">
                                  <label class="control-label" for="name"> Pin (Length:4) </label>
                                  <input name="pin" id="pin" class="form-control"
                                      oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
                                      type = "number"
                                      maxlength = "4"
                                      required />
                                </div></div></div>
                                 <div class="form-group required">
                                  <div class="row">
                                        <div class="col-md-6">
                                  <label class="control-label" for="name"> Confirm Pin (Length:4) </label>
                                  <input name="cpin" id="cpin" class="form-control"
                                      oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
                                      type = "number"
                                      maxlength = "4"
                                      required />
                                </div></div></div>
                                  </div>
                                  <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                  <button type="submit" name="submit" class="btn btn-secondary" >Submit</button>
                        </div>     
                            </form>
                        </div>
                      
                    </div><!-- /.modal-content -->
                </div>
                             <div id="tableview1"></div> 

                              <!-- Modal HTML Markup -->
             <div id="ModalExample1" class="modal fade">
                  <div class="modal-dialog">
                      <div class="modal-content">
                          <div class="modal-header">
                              <h4 class="modal-title text-xs-center">User Information</h4>
                          </div>
                          <div class="modal-body">
                             <div id="tableview"></div> 
                             <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                          </div>
                      </div>
                  </div>
              </div>  
            </div>
         </div>
      </div>
   </div>
</div>
@endsection
@section('headerScript')
@parent
<link rel="stylesheet" href="//cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css">
@endsection

@section('footerScript')
@parent
<script src="//code.jquery.com/jquery.js"></script>
<script src="//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

<script>

$(document).ready(function(){
   $(".update_id").click(function(){ // Click to only happen on announce links
     var d= $(this).data('id');
	$('#cafeId').val(d); 
	var user_id=$("#cafeId").val();
   });
});

</script>



<script type="text/javascript">
$(document).on('submit','#editPin',function(e){
  //alert(232);
            e.preventDefault();
            var pin=$("#pin").val();
            var cpin=$("#cpin").val();
            //var user_id=$("#user_id1").val();
			var user_id=$("#cafeId").val();
		//alert(user_id);
            if(pin!=cpin){
              swal({
                title: "Alert",
                text: "Please use correct confirm pin",
                type: "warning",
                showCancelButton    : true,
                confirmButtonColor  : "#ff0000",
                confirmButtonText   : "Yes",
                allowOutsideClick: false,
              });
              return false;
            }

            $.ajax({
                type:'POST',
                url:'{{route("change_pin")}}',
                data:{
                      "_token": "{{ csrf_token() }}",
                      'status': 1,
                      'pin':pin,
                      'cpin':cpin,
                      'user_id':user_id,
                    },

                success:function(response){
                 // console.log(response);
                  if(response.status==1)
                  {
                  console.log(232);
                  location.reload();
                  }
                  else
                  {
                    console.log(333);
                  }
                                         
                },
                error:function(data){
                   
                   console.log(data);
                   console.log(00);
                }
            });
        });
</script>


 <script>
    $(document).on("click", ".passingID", function () {
          var user_id= $(this).attr('data-id');
         // alert(d);
        var op ="";
    //alert(cycle);
    $.ajax({
    type : 'POST',
    url : '{{route("single_user")}}',
    data:{"_token": "{{ csrf_token() }}",'user_id':user_id},
    success:function(data2){
      //console.log(data2);
      //console.log(data2.data.length);
      var data3=data2.data;
      //console.log(data3.userId);
       op+='<table class="table table-striped">';
            op+='<tr><th>User Id</th><th>Study</th><th>Pin</th><th>Training Option</th></tr>';
              op+='<tr>';
              op+='<td>'+data3.userId+'</td><td>'+data2.study.studyId
+'</td><td>'+data3.pin+'</td><td>'+data3.options+'</td></tr>';
          
             op+='</table>';
             $('#tableview').html(op);
    }
    });

    });
  </script>

  <script>

$(document).ready(function(){
  $("#searchUser").on('keyup',function(){
  $value=$(this).val();

  var op ="";

if($value!='')
    {
            $('.hide-table').hide();
                  $('#tableview1').show();

    }
    else
    {
      $('.hide-table').show();
      $('#tableview1').hide();
    }

//alert($value);
$.ajax({
type : 'POST',
url : '{{route("search_users")}}',
data:{"_token": "{{ csrf_token() }}",'userId':$value},
success:function(data2){
  //console.log(data2.data.length);
  var data3=data2.data;
  //console.log(data3);
   op+='<table class="table table-striped">';
        op+='<tr><th>UserId</th><th>Study Id </th><th> Options</th><th> Report</th></tr>';
        for(var i=0;i<data3.length;i++){
          op+='<tr>';
          op+='<td>'+data3[i].userId+'</td><td>'+'<a href="{{url('/admin/all_questions')}}/{{$users['study_id']}}">'+data3[i].studyId+'</a>'+'</td><td>'+data3[i].options+'</td><td>'+'<a href="{{url('/admin/download_csv_single_user')}}/{{$users['_id']}}">'+'<i class="fa fa-download" aria-hidden="true">'+
'Download'+'</i>'+'</a>'+'</td></tr>';
        }
         op+='</table>';
         $('#tableview1').html(op);
//$('tbody').html(data);
}
});


  });
});


  </script>
  
  <script>
  $('.check_data').click(function(){
			var val = [];
			$(':checkbox:checked').each(function(i){
			  val[i] = $(this).val();
			});
		  //alert(val);
            $.ajax({
                type:'POST',
                url:'{{route("multiple_download_user")}}',
                data:{
                      "_token": "{{ csrf_token() }}",
                      'status': 1,
                      'options':val,
                    },

                success:function(response){
                  //console.log(response);
				  //var val=['RR0001'];
				  window.location.href = 'multiple_download_user1'+'/'+val+'/'+'dwn';                 			  
                },
                error:function(data){
                   console.log(data);
                   console.log(00);
                }
            });

		  });
		  
  </script>
  
  <script>
$(document).ready(function(){
  $(".check_id").on('click',function(){
	       var val = [];
			$(':checkbox:checked').each(function(i){
			  val[i] = $(this).val();
			}); 
			
			if(val !='')
			{
				
				$('.show-button').show();
			}
			else
			{
				$('.show-button').hide();
			}	     
   });
});
  
  </script>

  
  @endsection
