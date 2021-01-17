@extends('trainer_panel.layouts.app')

@section('content')
<div style="min-height: 600px;">
<div class="table-agile-info">
  <div class="panel panel-default">
    <div class="panel-heading">
      Add Yoga Workout
    </div>
      <div class="row">
        <div class="col-lg-12">
          <section class="panel">
            <div class="panel-body">
              <div class="form">
                @if (session('er_status'))
                  <div class="alert alert-danger">{!! session('er_status') !!}</div>
                @endif
                @if (session('su_status'))
                  <div class="alert alert-success">{!! session('su_status') !!}</div>
                @endif
                <form class="cmxform form-horizontal " id="signupForm" method="post" action="{!! url('trainer/add_yoga_workout_action') !!}" novalidate="novalidate" enctype="multipart/form-data">
                  @csrf
                    <div class="form-group ">
                      <label for="title" class="control-label col-lg-3">Title</label>
                      <div class="col-lg-6">
					  @if(empty($workout_existing))
                        <input class="form-control" id="title" value="{{ old('title') }}" name="title" type="text">
					@else
					 <input class="form-control" id="title" value="{{$workout_existing->title}}" name="title" type="text">
					 <input type="hidden" value="{{$workout_existing->_id}}" name="existing_id" type="text">
					@endif

                        @if ($errors->has('title'))
                          <div class="alert alert-danger">
                            {!! $errors->first('title') !!}
                          </div>
                        @endif
                      </div>
                    </div>
					 <div class="form-group ">
                      <label for="title" class="control-label col-lg-3">Week</label>
                      <div class="col-lg-6">
					  @if(empty($workout_existing))
                        <input class="form-control" id="week" value="{{ old('week') }}" name="week" type="text">
					@else
					 <input class="form-control" id="title" value="{{$workout_existing->week}}" name="week" type="text">
					@endif
					  
                       
                      </div>
                    </div>
					<input type="hidden" name="approval" value="pending" />
                    <div class="form-group ">
                      <label for="description" class="control-label col-lg-3">Description</label>
                      <div class="col-lg-6">
					  @if(empty($workout_existing))
                       	<textarea class="form-control" id="description" value="" name="description" maxlength="400" rows="5"></textarea>
					@else
					 <textarea class="form-control" id="description" value="" name="description" maxlength="400" rows="5">{{$workout_existing->description}}</textarea>
					@endif
					  
					   @if ($errors->has('description'))
                          <div class="alert alert-danger">
                            {!! $errors->first('description') !!}
                          </div>
                        @endif
                      </div>
                    </div>
                    
					     <div class="form-group ">
                      <label for="exercises" class="control-label col-lg-3"></label>
                      <div class="col-lg-3">
					  <!-- <a href="{{url('trainer/add_yoga_wo_day')}}">Add Exercises</a> -->
					 <a href="#" id="submitBtn">Add Exercises</a>


                      </div>
					  <div class="col-lg-3">
					   <a href="#" data-toggle="modal" data-target="#exampleModal3" data-whatever="@mdo" >Link workout</a>

                      </div>
                    </div> 
					<div class="form-group ">
					<div class="exercises">
                      <label for="exercises" class="control-label col-lg-3">Exercises</label>
					  </div>
					  
                      <div class="the col-lg-3">
					
					  @if(!empty($WorkoutDetail))
					<input type="hidden" name="workoutdetails_id" value="{{$WorkoutDetail->_id}}" />

					 @foreach($WorkoutDetail['exercise_id'] as $workout_exercises)
					    <div class="row">
							<div class="running">
							<div class="day-run col-lg-3">
						day{{ $WorkoutDetail->day }}

						</div>
					
					  <div class="run-exercise col-lg-7">
					   
                        <input type="text"  class="form-control"   name="exercises[]" value="{!! $workout_exercises !!}" />
						
						</div>
						
					</div>
						</div>
						@endforeach
                      
					   @endif
                      </div>
					 
                    </div>
					
					
					
                    <div class="form-group ">
                      <label for="time" class="control-label col-lg-3">Time</label>
                      <div class="col-lg-6">
                        <input class="form-control" id="time" value="{{ old('time') }}" name="time" type="text">
                        @if ($errors->has('time'))
                          <div class="alert alert-danger">
                            {!! $errors->first('time') !!}
                          </div>
                        @endif
                      </div>
                    </div>
					<!-- <div class="form-group ">
                      <label for="period" class="control-label col-lg-3">Period</label>
                      <div class="col-lg-6">
                        <input class="form-control" id="period" value="{{ old('period') }}" name="period" type="number">
                        @if ($errors->has('period'))
                          <div class="alert alert-danger">
                            {!! $errors->first('period') !!}
                          </div>
                        @endif
                      </div>
                    </div> -->
					<div class="form-group ">
                      <label for="period" class="control-label col-lg-3">Linked Workout</label>
                      <div class="col-lg-6" id="linked">
                      
                      </div>
                    </div>
                    <div class="form-group ">
                      <label for="image" class="control-label col-lg-3">Image</label>
                      <div class="col-lg-6">
                        <input class="form-control" id="image" name="image" type="file">
                        @if ($errors->has('image'))
                          <div class="alert alert-danger">
                            {!! $errors->first('image') !!}
                          </div>
                        @endif
                      </div>
                    </div>
					 <div class="form-group ">
                      <label for="category" class="control-label col-lg-3">Workout Price</label>
                      <div class="col-sm-6">
                        <select id="price" name="price">
                        <!-- <option value="1">set price</option> -->
                         <option value="2">Set Price($)</option>
						  <option value="3">Bundle Price($)</option>
                          
                        </select>
						 <input type="number" name="amount" id="amount" placeholder="enter the price" />
                        
                      </div>
					  
					 <!-- <div class="col-lg-3" style="margin-top:5px;" id="price1" style="display:none;">
					  <input type="number" name="price"   />
                    </div> -->
					</div>
					<div class="form-group ">
                      <label for="category" class="control-label col-lg-3">Level</label>
                      <div class="col-lg-6">
                        <select  id="level" name="level">
                         <option value="beginner">Beginner</option>
                         <option value="intermediate ">Intermediate</option>
                         <option value="advanced ">Advanced </option>
                          
                        </select>
                        @if ($errors->has('level'))
                          <div class="alert alert-danger">
                            {!! $errors->first('level') !!}
                          </div>
                        @endif
                      </div>
                    </div>
					
                    <div class="form-group">
                      <div class="col-lg-offset-3 col-lg-6">
                        <button class="btn btn-primary" type="submit">Submit</button>
                      </div>
                    </div>
					<div class="modal fade" id="exampleModal3" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
				  <div class="modal-dialog" role="document">
					<div class="modal-content">
					  <div class="modal-header">
						<h5 class="modal-title" id="exampleModalLabel">Link Workout</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						  <span aria-hidden="true">&times;</span>
						</button>
					  </div>
					  <div class="modal-body">
						  <div class="form-group">
							<div class="col-lg-6">
									   <select  id="link_workout" name="link_workout">
										<option value="none">None</option>
									   @foreach($workout as $workouts)
										 <option value="{{$workouts->title}}">{{$workouts->title}}</option>
										 @endforeach
										</select>
							 </div>
						  </div>       
					  <div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
						
					  </div>
					  </div>
					</div>
				  </div>
				</div>
				
				
				
                  </form>
                </div>
              </div>
            </section>
          </div>
        </div>
  </div>
</div>
</div>

<script type="text/javascript">

$(document).ready(function(){
	
	$("#link_workout").change(function(){
		
		var link_workouts = $(this).val();
		if(link_workouts)
		{
			//$("#linked").load();
			$("#linked").empty();

			$("#linked").append(link_workouts);
		}
		
		if(link_workouts!='none')
		{
			//alert(345);
			// $("#price").val('3'); 
			 $("#price option[value=3]").attr('selected', 'selected'); 
		}
		else{			
		     $("#price option[value=2]").attr('selected', 'selected'); 
		}
		
	});
	
});

</script>



<script type="text/javascript">


$(document).ready(function(){
	
	$("#price").change(function(){
		
		var price_name = $(this).val();
		
		if(price_name==2 || price_name==3)
		{
			
			$("#amount").show();
			
		}
		else
		{
			$("#amount").hide();
			
		}
		
	});
	
	
});

</script>

<script>
$(document).ready(function(){
    $("#submitBtn").click(function(){   	
        $("#signupForm").submit(); // Submit the form	
		alert("Add the exercises for Yoga workout");	
    });
});
</script>

@endsection