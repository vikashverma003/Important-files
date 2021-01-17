@extends('web.layouts.app')
@section('title', __('messages.header_titles.DASHBOARD'))

@section('content')

<section class="myProSection">
    <div class="top-bar">
        <div class="container">
            <h3>{{$project->title}}</h3>
            <h5> 
            	<img src="{{asset('web/images/ic_user1.png')}}" alt="" style="padding-right: 6px;"> 
            	Project Owner ID: {{$project->client->temp_todz_id ? $project->client->temp_todz_id : 'N/A'}}
        	</h5>
            <h5> 
            	<img src="{{asset('web/images/ic_user1.png')}}" alt="" style="padding-right: 6px;visibility:hidden">Project ID:
                {{config('constants.PROJECT_ID_PREFIX')}}{{$project->id}}
            </h5>
            
            <a href="{{url('talent/message/'.$project->id.'/'.$project->client->todz_id)}}"> <button type="button" class="messageBtn2">Message</button></a>

            <button type="button" class="endProjectBtn disputeCloseProjectBtn" data-id="{{$project->id}}" data-toggle="modal" data-target="#closeProjectModal">Dispute/Close Project</button>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-md-3">

                <div class="project-tabs">
                    <h2>My Projects</h2>
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link " href="#overview" role="tab" data-toggle="tab">
                                Overview
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#details" role="tab" data-toggle="tab">
                                Details
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#milestones" role="tab" data-toggle="tab">
                                Milestones
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#timesheets" role="tab" data-toggle="tab">
                                Timesheets
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#payment" role="tab" data-toggle="tab">
                                Payment Details
                            </a>
                        </li>
                    </ul>
                </div>

            </div>
            <div class="col-md-9">
                <!-- Tab panes -->
                <div class="tab-content">
                    <div role="tabpanel " class="tab-pane fade " id="overview">
                        @include('web.talents.milestone.includes.overview-section')
                    </div>
                    <div role="tabpanel" class="tab-pane fade" id="details">
                        <div class="projectDetails">
                            <h4>Title</h4>
                            <p>{{$project->title}}</p>
                            <h4>Description</h4>
                            <p>{{$project->description}}
                            </p>
                            <ul class="overviewList">
                                <li>
                                    <h4>Months</h4>
                                    <p>{{$project->duration_month}}</p>
                                </li>
                                <li>
                                    <h4>Days</h4>
                                    <p>{{$project->duration_day}}</p>
                                </li>
                                <li>
                                    <h4>Cost</h4>
                                    <span>
                                        <strong>USD</strong>
                                        <p style="margin: 0;">{{$project->cost}}</p>
                                    </span>
                                </li>
                            </ul>
                            <h4>Project Brief File</h4>

                            @foreach($project->files as $file)
                                <a href="{{$file->file_full_path}}"><img src="{{$file->document_image}}" style="width:30px">{{$file->file_name}}</a>
                                <br/>     
                            @endforeach
                        </div>
                    </div>

                    <div role="tabpanel" class="tab-pane fade" id="milestones">
                        @include('web.talents.milestone.includes.milestones')
                    </div>

                    <div role="tabpanel" class="tab-pane fade" id="timesheets">
                        @include('web.talents.milestone.includes.timesheets')
                    </div>

                    <div role="tabpanel" class="tab-pane fade" id="payment">
                        @include('web.talents.milestone.includes.payments')
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<div id="escalationModal" class="modal fade" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" style="max-width: 40%">
        <div class="modal-content">
            <h4 class="modal-title mt-2" style="text-align: center;">Raise Escalation</h4>
            <div class="modal-body">
                <form method="post" name="escalationForm" id="escalationForm" onsubmit="addEscalationRecord()">
                    <input type="hidden" name="escalation_project_id" value="">
                    <input type="hidden" name="escalation_milestone_id" value="">
                    <input type="hidden" name="escalation_owner_id" value="">
                    <div class="form-group comment">
                        <label for="recipient-name" class="col-form-label">Comments :</label>
                        <input type="text" class="form-control" name="comment" required="true"> 
                        <span class="error"></span>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary add_escalationform_btn">Save</button>
                <button type="button" class="btn btn-secondary escalationform_dismiss" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div id="closeProjectModal" class="modal fade" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" style="max-width: 40%">
        <div class="modal-content">
            <h4 class="modal-title mt-2" style="text-align: center;">Close Project</h4>
            <div class="modal-body">
                <form method="post" name="closeDisputeProjectForm" id="closeDisputeProjectForm" onsubmit="addProjectCloseRecord()">
                    <div class="form-group request_type">
                        <label for="recipient-name" class="col-form-label">Request Type :</label>
                        <br>
                        <label>
                            <input type="radio" name="request_type" value="complete"> Completed
                        </label> 
                        <label>
                            <input type="radio" name="request_type" value="dispute" checked="true"> Dispute 
                        </label>
                        <span class="error"></span>
                    </div>

                    <div class="form-group rating completed_div" style="display: none;">
                        <label for="recipient-name" class="col-form-label">Rating :</label>
                        <select class="form-control" name="rating" required="true">
                            @foreach(range(1,5) as $row)
                                <option value="{{$row}}">{{$row}} Star</option>
                            @endforeach
                        </select>
                        <span class="error"></span>
                    </div>
                    <div class="form-group feedback completed_div" style="display: none;">
                        <label for="recipient-name" class="col-form-label">Feedback :</label>
                        <input type="text" class="form-control" name="feedback" required="true"> 
                        <span class="error"></span>
                    </div>

                    <div class="form-group reason reason_div">
                        <label for="recipient-name" class="col-form-label">Reason :</label>
                        <input type="text" class="form-control" name="reason" required="true"> 
                        <span class="error"></span>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary projectcompleteform_btn">Save</button>
                <button type="button" class="btn btn-secondary projectcompleteform_dismiss" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
    div#timer {
    background: #000000c2;
    position: fixed;
    right: 20px;
    bottom:50px;
    padding: 10px;
    color: white;
    border-radius: 5px;
    font-size: 18px;
    font-weight: 600;
}
    </style>

@endsection
@section('headerScript')
@parent
<link rel="stylesheet" href="{{asset('web/dropzone/basic.min.css')}}" >
<link rel="stylesheet" href="{{asset('web/dropzone/dropzone.min.css')}}" >

@endsection
@section('footerScript')
    @parent
    <script src="{{asset('web/js/main.js')}}"></script>
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <script  src="{{asset('web/dropzone/dropzone.min.js')}}" ></script>
  <script>
  $( function() {
    $( "#datepicker" ).datepicker({minDate: new Date()});
    $( "#datepicker1" ).datepicker({minDate: new Date()});
  } );
  </script>
  <script>

$(document).ready(function(){

	// escalation form
	$(document).on('click','.escalationBtn', function(){
		var $form = $('#escalationForm');
		$(document).find('span.error').empty();
       	$('.add_escalationform_btn').prop('disabled', false);
		$form.find('input[name="escalation_project_id"]').val($(this).data('project'));
		$form.find('input[name="escalation_milestone_id"]').val($(this).data('milestone'));
		$form.find('input[name="escalation_owner_id"]').val($(this).data('owner'));
	});

	$("#escalationForm").submit(function(e) {
		e.preventDefault();
		addEscalationRecord();
	});
	$(document).on('click','.add_escalationform_btn', function(e){
		e.preventDefault();
		addEscalationRecord();
	});

	function addEscalationRecord(){
		var formdata = new FormData($('#escalationForm')[0]);
		formdata.append('_token', "{{ csrf_token() }}");
		$.ajax({
            type:'POST',
            url:'{{route("raise_escalation")}}',
            data: formdata,
        	processData: false,  // Important!
            contentType: false,
            beforeSend:function(){
                //startLoader('.modal-content');
                $('.add_escalationform_btn').prop('disabled', true);
            },
            complete:function(){
                //stopLoader('.modal-content');
                $('.add_escalationform_btn').prop('disabled', false);
            },
            success:function(response){
                if(response.status){
                    swal("Yeah!", response.message, "success");
                    $('#escalationForm')[0].reset();
                    $(document).find('span.error').empty();
                    $('.escalationform_dismiss').trigger('click');
                }else{
                  	swal("Oops!", response.message, "error");
                }
            },
            error: function(error){
            	$('.add_escalationform_btn').prop('disabled', false);
                // stopLoader('.modal-content');
                if(error.status == 422) {
                    errors = error.responseJSON;
                    $.each(errors.errors, function(key, value) {
                        $('.'+key).find('span.error').empty().addClass('text-danger').text(value).finish().fadeIn();
                    }); 
                }
            }
        });
	}

    // dispute or close project
    $('input[type=radio][name=request_type]').change(function() {
        var val = $(this).val();
        $('.completed_div').hide();
        $('.reason_div').show();
        if (this.value == 'complete') {
            $('.completed_div').show();
            $('.reason_div').hide();
        }
    });

    $(document).on('click','.disputeCloseProjectBtn', function(){
        var $form = $('#closeDisputeProjectForm');
        $(document).find('span.error').empty();
        $('.add_escalationform_btn').prop('disabled', false);
        $('#closeDisputeProjectForm')[0].reset();
        $('.completed_div').hide();
        $('.reason_div').show();
        $('input[type=radio][name=request_type][value="dispute"]').prop('checked', true);
    });

    $("#closeDisputeProjectForm").submit(function(e) {
        e.preventDefault();
        addProjectCloseRecord();
    });
    $(document).on('click','.projectcompleteform_btn', function(e){
        e.preventDefault();
        addProjectCloseRecord();
    });

    function addProjectCloseRecord(){
        var formdata = new FormData($('#closeDisputeProjectForm')[0]);
        formdata.append('_token', "{{ csrf_token() }}");
        formdata.append('project_id', '{{$project->id}}');

        $.ajax({
            type:'POST',
            url:'{{url("talent/complete-ordispute-project")}}',
            data:formdata,
            processData: false,
            contentType: false,
            beforeSend:function(){
                //startLoader('.modal-content');
                $('.projectcompleteform_btn').prop('disabled', true);
            },
            complete:function(){
                //stopLoader('.modal-content');
                $('.projectcompleteform_btn').prop('disabled', false);
            },
            success:function(response){
                if(response.status){
                    swal("Yeah!", response.message, "success");
                    
                    $(document).find('span.error').empty();
                    $('.escalationform_dismiss').trigger('click');
                    window.location = response.url;
                }else{
                    swal("Oops!", response.message, "error");
                }
            },
            error: function(error){
                $('.projectcompleteform_btn').prop('disabled', false);
                // stopLoader('.modal-content');
                if(error.status == 422) {
                    errors = error.responseJSON;
                    $.each(errors.errors, function(key, value) {
                        $('.'+key).find('span.error').empty().addClass('text-danger').text(value).finish().fadeIn();
                    }); 
                }
            }
        });
    }

    $hash=window.location.hash;
    if(window.location.hash==''){
        $hash="#overview";
    }
    $('.nav-tabs a[href="'+ $hash + '"]').addClass('active');
    $($hash).addClass('show active');
});
$('.nav-tabs a[data-toggle="tab"]').on("click", function() {
    $('.nav-tabs a').removeClass('active');
    $(".tab-pane").removeClass('show active');
    let newUrl;
    
    const hash = $(this).attr("href");
      newUrl ="{{route('milestone_overview',[$project->id])}}" + hash;

    history.replaceState(null, null, newUrl);
   
   $('.nav-tabs a[href="'+ $hash + '"]').delay( 800 ).addClass('active');
    $($hash).delay( 800 ).addClass('show active');
  });
 
$(".create-milestrone").click(function(){

    let newUrl;
    
    const hash = $(this).attr("href");
      newUrl ="{{route('milestone_overview',[$project->id])}}" + hash;

    history.replaceState(null, null, newUrl);
   
   $('.nav-tabs a[href="#milestones"]').trigger('click');
});
  
  $(".addBtn").on('click',function(){
      $(this).hide();
    $("#createmilestoen").show();
  });
</script>
<script>
    
    $(document).on('click','.playBtn',function(e){
        var milestone_id=$(this).data('id');
      var btnObj=$(this);
        $.ajax({
            type:'POST',
            url:'{{route("start_timer")}}',
            data:{ "_token": "{{ csrf_token() }}",
    'milestone_id':milestone_id     },
            success:function(response){
                if(response.status==1){
                    swal("Yeah!", response.message, "success");
                    btnObj.html(`Pause<img src="{{asset('web/images/ic_user.png')}}" alt="">`);
                     timerVar = setInterval(countTimer, 1000);
                     totalSeconds = response.sec;
                     $("#timer").css("visibility","visible");
                  //  setTimeout(location.reload.bind(location), 3000);
                }else if(response.status==2){
                    swal("Yeah!", response.message, "success");

                    btnObj.html(`Start<img src="{{asset('web/images/ic_user.png')}}" alt="">`);
          clearInterval(timerVar)
          $("#milestone-"+milestone_id).html(response.updated_time);
          $("#timer").css("visibility","hidden");

                  //  setTimeout(location.reload.bind(location), 3000);
                }else{
                  swal("Oops!", response.message, "error");
                }
            }
        });
    });
</script>
<script>
    @if(ProjectManager::isTaskRunning($project->id))
    var timerVar = setInterval(countTimer, 1000);
    var totalSeconds = {{ProjectManager::getRuningSe($project->id)}};
    @else
    var timerVar;
    var totalSeconds =0;

    @endif
    function countTimer() {
       ++totalSeconds;
       var hour = Math.floor(totalSeconds /3600);
       var minute = Math.floor((totalSeconds - hour*3600)/60);
       var seconds = totalSeconds - (hour*3600 + minute*60);
       if(hour < 10)
         hour = "0"+hour;
       if(minute < 10)
         minute = "0"+minute;
       if(seconds < 10)
         seconds = "0"+seconds;
       document.getElementById("timer").innerHTML = hour + ":" + minute + ":" + seconds;
    }
    </script>
     <script>
       var workReferenceUpload = false;
       function validateForm(){
            if(!workReferenceUpload){
              swal({
                title: "Alert",
                text: "Please upload today's work",
                type: "warning",
                showCancelButton    : true,
                confirmButtonColor  : "#ff0000",
                confirmButtonText   : "Yes",
                allowOutsideClick: false,
              });
              return false;
            }
           
        }
        </script>

<script>
    $("div#file-uploadsection").dropzone({ 
      url: "{{route('timesheetfile_load')}}",
      addRemoveLinks : true,
    maxFilesize: 1,
    acceptedFiles: ".jpeg,.jpg,.png,.pdf,.doc,.docx",
    dictDefaultMessage: '<span class="theme-color fw-500">Upload</span> or <span class="theme-color fw-500">Drag</span> &amp; Drop your file. ',
    dictResponseError: 'Error uploading file!',
    headers: {
        'X-CSRF-TOKEN':'{{ csrf_token() }}'
    },
    removedfile: function(file) { 

var name = file.name;   
console.log(file.name);       
$.ajax({
  headers: {
    'X-CSRF-TOKEN': '{{ csrf_token() }}'
},
type: 'POST',
url: "{{route('timesheetfiledelete')}}",
data: {filename:name},
dataType: 'html'
});
var fileRef;
workReferenceUpload=false;
    return (fileRef = file.previewElement) != null ? 
          fileRef.parentNode.removeChild(file.previewElement) : void 0;
},
  success: function(file, response) {
    workReferenceUpload=true;
                 
  },

  });
    </script>
@endsection  