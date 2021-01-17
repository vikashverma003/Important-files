@extends('web.layouts.app')
@section('title', __('messages.header_titles.SIGNUP'))

@section('content')


<section class="login-section header-mt">
    <div class="container" style="text-align: center;">
        <img src="{{asset('images/signup_freelancer.png')}}" alt="" style="padding-bottom: 40px;">
        <div class="row">
            <div class="offset-md-2 col-md-8">
                <div class="row wizzard-form">
                    <div class="col-md-4 text-center">
                        <p class="active">Post Project</p>
                        <span class="dot-wizzard active"></span>
                    </div>
                    <div class="col-md-4 text-center">
                        <p class="active">Select Talents</p>
                        <span class="dot-wizzard active"></span>
                    </div>
                    <div class="col-md-4 text-center">
                        <p>Summary</p>
                        <span class="dot-wizzard active"></span>
                    </div>
                    <div class="col-md-12">
                        <div class="form-wizard">
                            <div class="line-colored third"></div>
                        </div>
                    </div>
                </div>
 
                <div class="row wizard-data">
                    <div class="col-md-10 offset-md-1">
                        <h4 class="mt-4 mb-3">Enter your work details below</h4>
                        <form class="post-project" method="post" id="signup_work_Form" onsubmit="return validateForm()">
                            @csrf
                            <div class="form-group">
                                jobCategories
                                <select  class="form-control custom" type="text" name="job_field" required>
                                    <option value="" selected="selected" disabled>--Job Field--</option>
                                    @foreach($jobCategories as $va)
                                      <option value="{{$va->name}}">{{$va->name}}</option>
                                    @endforeach
                                </select>
                                @if($errors->has('job_field'))
                                  <p class="text-danger">{{ $errors->first('job_field') }}</p>
                                @endif
                                {{-- <input class="form-control custom" type="text" name="job_field" placeholder="Job Field"> --}}
                            </div>
                            <div class="form-group">
                                <input class="form-control custom" type="text" name="job_title" placeholder="Job Title" value="{{old('job_title')}}" required>
                                @if($errors->has('job_title'))
                                  <p class="text-danger">{{ $errors->first('job_title') }}</p>
                                @endif
                            </div>
                            <div class="form-group">
                                <input class="form-control custom" type="text" name="expected_hourly_rate" placeholder="Expected Hourly Rate" value="{{old('expected_hourly_rate')}}" required>
                                @if($errors->has('expected_hourly_rate'))
                                    <p class="text-danger">{{ $errors->first('expected_hourly_rate') }}</p>
                                @endif
                            </div>

                            <select class="form-control custom" name="work_experience" required>
                                <option value="">Work Experience (in years)</option>
                                <option value="1">1-3</option>
                                <option value="2">3-7</option>
                                <option value="3">7+</option>
                            </select>
                            @if($errors->has('work_experience'))
                                <p class="text-danger">{{ $errors->first('work_experience') }}</p>
                            @endif

                            

                            <div class="form-group  mt-4">
                                <h6>Upload work reference</h6>
                            </div>
                            <div class="form-group mt-3">
                                <div id="file-uploadsection" class="fallback dropzone  text-center p-2"> 
                                     <!-- <input type="file" id="kfile-uploadsection"name="project_file"  /> -->
                                  </div>
                            </div>

                            <div class="form-group  mt-4">
                                <h6>Upload Resume</h6>
                            </div>
                            <div class="form-group mt-3">
                                <div id="file-resumesection" class="fallback dropzone  text-center p-2">
                                  </div>
                            </div>

                          

                            <div class="form-group  mt-4">
                                <h6>Add Skills</h6>
                                <div class="form-group">
                                    <input class="form-control custom" type="text" name="project_title" placeholder="Select Skills" autocomplete="off">
                                    <div class="full-width" style="position:relative">
                                        <div class="full-width  " id="skills">
                      
                                        </div>
                                    </div>
                                </div>
                                @if($errors->has('skills'))
                                  <p class="text-danger">{{ $errors->first('skills') }}</p>
                                @endif
                            </div>
                            <div class="form-group selected-box mt-4">
                
                            </div>
                            <div class="form-group">

                                <h6>Set your availability for the platform </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <input class="form-control custom" type="text" id="start_datepicker" name="available_start_date" placeholder="Start Date" readonly='true'>
                                        @if($errors->has('available_start_date'))
                                        <p class="text-danger">{{ $errors->first('available_start_date') }}</p>
                                      @endif
                                    </div>
                                    <div class="col-md-6">
                                        <input class="form-control custom" type="text"  id="end_datepicker"  name="available_end_date" placeholder="End Date" readonly='true'>
                                        @if($errors->has('available_end_date'))
                                          <p class="text-danger">{{ $errors->first('available_end_date') }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">

                                <h6>Set your working days and hours</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <select class="form-control custom" name="working_type" required>
                                            @for($i=1;$i<=7;$i++)
                                              <option value="{{$i}}">{{$i}} day / week</option>
                                            @endfor
                                        </select>
                                         @if($errors->has('working_type'))
                                          <p class="text-danger">{{ $errors->first('working_type') }}</p>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <select class="form-control custom" name="hours" required>
                                            @for($i=1;$i<=40;$i++)
                                            <option value="{{$i}}">{{$i}} Hours</option>
                                                @endfor
                                        </select>
                                        @if($errors->has('hours'))
                                          <p class="text-danger">{{ $errors->first('hours') }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <button class="theme-button next-btn formsubmitbtn active hover-ripple mb-4 mt-4 text-upper mr-3"
                                    style="width: 100%;">Create Account </button>
                                <!-- <button
                                    class="theme-button tranaparent-btn hover-ripple mb-4 mt-4 text-upper">Cancel</button> -->
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    

    div#skills {
        top: 0;
        position: absolute;
        background: #fff;
        z-index: 5;
        margin-top: 2px;
    }
    li.full-width.pb-2.skill-list {
        padding: 10px;
        border-bottom: 1px solid #ccc;
        cursor: pointer;
    }
    ul.list-inline {
        text-align:left;
        border-left: 1px solid #ccc;
        border-right: 1px solid #ccc;
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

   <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
   <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
   <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
   <script  src="{{asset('web/dropzone/dropzone.min.js')}}" ></script>
   <script>
   $( function() {
       $( "#start_datepicker" ).datepicker({ minDate: 0});
       $( "#end_datepicker" ).datepicker({ minDate: 0});
     } );
     </script>
     <script>
       var workReferenceUpload = false;
       var resumeUpload = false;

       function validateForm(){
            if(!workReferenceUpload){
              swal({
                title: "Alert",
                text: "Please upload work reference",
                type: "warning",
                showCancelButton    : true,
                confirmButtonColor  : "#ff0000",
                confirmButtonText   : "Yes",
                allowOutsideClick: false,
              });
              return false;
            }
            if(!resumeUpload){
              swal({
                title: "Alert",
                text: "Please upload resume",
                type: "warning",
                showCancelButton    : true,
                confirmButtonColor  : "#ff0000",
                confirmButtonText   : "Yes",
                allowOutsideClick: false,
              });
              return false;
            }
        }
        $(document).ready(function(){


          
        $(document).on("keyup","input[name='project_title']",function($e){
            $e.preventDefault();
            var keyward = $("input[name='project_title']").val();
            if(keyward.length==0){
                return false;
            }
            $.ajax({
                type:'POST',
                url:'{{route("talent_search_skill")}}',
                data:{ 
                    "_token": "{{ csrf_token() }}",
                   'keyward':keyward
                },
                success:function(response){
                    $("#skills").html(response);
                }
            });
        });
        
        $(document).on('click', function(){
            // $('#skills').hide();
        })
        
        $(document).on("focus","input[name='project_title']",function($e){
           $e.preventDefault();
              var keyward = '';
                 $.ajax({
                  type:'POST',
                  url:'{{route("talent_search_skill")}}',
                  data:{ "_token": "{{ csrf_token() }}",
      
                   'keyward':keyward
                   },
                  success:function(response){
                    $("#skills").html(response);
                   // console.log(response);
                 //  var res= JSON.parse(response);
                  
              }
           });
        });
        $(document).delegate(".skill-list",'click',function($e){
            var id=$(this).data('id');
            var name=$(this).data('name');
            $(".selected-box ").append(`<span class="mr-3" id="skill-${id}">${name}<a href="javascript:void(0)" class="delete-skill" data-id="${id}">+</a>
            <input type="hidden" class="d-none" name="skills[]" value="${id}" /></span>`);
            $("#skills").html("");
            $('input[name="project_title"]').val("");
           
          });
          $(document).delegate(".delete-skill",'click',function($e){
            var id=$(this).data('id');
            $("#skill-"+id).remove();
          });
        });
       
        </script>
        <script>
            $("div#file-uploadsection").dropzone({
                url: "{{route('talent-upload-image')}}",
                addRemoveLinks : true,
                maxFilesize: 10,
                timeout: 100000,
                acceptedFiles: ".jpeg,.jpg,.png,.gif,.txt",
                dictDefaultMessage: '<span class="theme-color fw-500">Upload</span> or <span class="theme-color fw-500">Drag</span> &amp; Drop your file.[accepted file formats<span class="theme-color fw-500"> jpeg,png,gif,txt</span> ] max upload size <span class="theme-color fw-500"> 20 MB</span> ',
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
                        url: "{{route('talent-file-delete')}}",
                        data: {filename:name,'type':'workReference'},
                        dataType: 'html'
                    });
                    workReferenceUpload = false;
                    var fileRef;
                    return (fileRef = file.previewElement) != null ? 
                    fileRef.parentNode.removeChild(file.previewElement) : void 0;
                },
                success: function(file, response) {
                  workReferenceUpload = true;
                },
            });

            $("div#file-resumesection").dropzone({
                url: "{{route('upload-talent-resume')}}",
                addRemoveLinks : true,
                maxFilesize: 10,
                timeout: 100000,
                acceptedFiles: ".jpeg,.jpg,.png,.gif,.txt",
                dictDefaultMessage: '<span class="theme-color fw-500">Upload</span> or <span class="theme-color fw-500">Drag</span> &amp; Drop your file.[accepted file formats<span class="theme-color fw-500"> jpeg,png,gif,txt</span> ] max upload size <span class="theme-color fw-500"> 20 MB</span> ',
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
                        url: "{{route('talent-file-delete')}}",
                        data: {filename:name,'type':'resume'},
                        dataType: 'html'
                    });
                    var fileRef;
                    resumeUpload = false;
                    return (fileRef = file.previewElement) != null ? 
                    fileRef.parentNode.removeChild(file.previewElement) : void 0;
                },
                success: function(file, response) {
                  resumeUpload = true;
                },
            });

            </script>
  
@endsection