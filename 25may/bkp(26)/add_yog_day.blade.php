@extends('trainer_panel.layouts.app')

@section('content')
<div style="min-height: 600px;">
<div class="table-agile-info">
  <div class="panel panel-default">
    <div class="panel-heading">
      Add Yoga Workout daywise
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
                <form class="cmxform form-horizontal " id="signupForm" method="post" action="{!! url('trainer/add_yoga_wo_daywise_action') !!}" novalidate="novalidate" enctype="multipart/form-data">
                  @csrf
                    <div class="form-group ">
                      <label for="day" class="control-label col-lg-3">Day</label>
                      <div class="col-lg-6">
                        <select class="form-control" id="day" name="day">
                          <option value="">Select Day</option>
                          <option value="1" <?php if(old('day') == "1") echo "selected"; ?>>Monday</option>
                          <option value="2" <?php if(old('day') == "2") echo "selected"; ?>>Tuesday</option>
                          <option value="3" <?php if(old('day') == "3") echo "selected"; ?>>Wednesday</option>
                          <option value="4" <?php if(old('day') == "4") echo "selected"; ?>>Thursday</option>
                          <option value="5" <?php if(old('day') == "5") echo "selected"; ?>>Friday</option>
                          <option value="6" <?php if(old('day') == "6") echo "selected"; ?>>Saturday</option>
                          <option value="7" <?php if(old('day') == "7") echo "selected"; ?>>Sunday</option>
                        </select>
                        @if ($errors->has('day'))
                          <div class="alert alert-danger">
                            {!! $errors->first('day') !!}
                          </div>
                        @endif
                      </div>
                    </div>
					<div class="form-group ">
                      <label for="category" class="control-label col-lg-3">Activity</label>
                      <div class="col-lg-6">
                        <select class="form-control" id="activity" name="activity">
						<option value="">Select activity</option>
                         <option value="rest">Rest</option>
                         <option value="yoga/stretches">Yoga/Stretches</option>
                        </select>
                                              </div>
                    </div>
					<div class="form-group ">
                      <label for="title" class="control-label col-lg-3">Week</label>
                      <div class="col-lg-6">
                        <input class="form-control" id="week" value="{{ old('week') }}" name="week" type="text">
                        
                      </div>
                    </div>
					
                    <div class="form-group ">
                      <label for="title" class="control-label col-lg-3">Title</label>
                      <div class="col-lg-6">
                        <input class="form-control" id="title" value="{{ old('title') }}" name="title" type="text">
                        @if ($errors->has('title'))
                          <div class="alert alert-danger">
                            {!! $errors->first('title') !!}
                          </div>
                        @endif
                      </div>
                    </div>
                    <div class="form-group ">
                      <label for="daily_desc" class="control-label col-lg-3">Description</label>
                      <div class="col-lg-6">
                        <!-- <input class="form-control" id="daily_desc" value="{{ old('daily_desc') }}" name="daily_desc" type="text">-->
                       	<textarea class="form-control" id="daily_desc" value="" name="daily_desc" maxlength="400" rows="5"></textarea>
                       
					   @if ($errors->has('daily_desc'))
                          <div class="alert alert-danger">
                            {!! $errors->first('daily_desc') !!}
                          </div>
                        @endif
                      </div>
                    </div>
					<div class="field_wrapper3">
					
                    <div class="form-group ">
                      <label for="sets" class="control-label col-lg-3">Sets</label>
                      <div class="col-lg-6">
                        <input class="form-control" id="sets" value="{{ old('sets') }}" name="sets" type="text">
                       
                      </div>
                    </div>
                    <div class="form-group ">
                      <label for="hold_time" class="control-label col-lg-3">Time</label>
                      <div class="col-lg-6">
                        <input class="form-control" id="time" value="{{ old('time') }}" name="time" type="text">
                        
                      </div>
                    </div>
					<div class="form-group ">
                      <label for="exercises" class="control-label col-lg-3"></label>
                      
					  <div class="col-lg-3">
					   <a href="#" data-toggle="modal" data-target="#exampleModal9" id="mm" data-whatever="@mdo" >Add Exercise</a>

                      </div>
                    </div>
					<br/>
					 <div class="form-group ">
					   <label for="exercises" class="control-label col-lg-3"></label>
						<div class="col-lg-3">
					    <input type="text" class="form-control" id="selected_values" value="{{ old('selected_values') }}" name="selected_values" disabled/><br/>
                    </div>
					</div>
					<div class="form-group ">
                      <label for="period" class="control-label col-lg-3"></label>
                      <div class="col-lg-6" id="linked">
                      
                      </div>
                    </div>
					<br/><br/>
                   </div>
                    <div class="form-group">
                      <div class="col-lg-offset-3 col-lg-6">
                        <button class="btn btn-primary" type="submit">Submit</button>
                      </div>
                    </div>
						<div class="modal fade" id="exampleModal9" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
				  <div class="modal-dialog" role="document">
					<div class="modal-content">
					  <div class="modal-header">
						<h5 class="modal-title" id="exampleModalLabel">Link Exercise</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						  <span aria-hidden="true">&times;</span>
						</button>
					  </div>
					  <div class="modal-body">
						  <div class="form-group">
							<div class="col-lg-6">
									   <select  id="link_exercise" name="link_exercise[]" multiple="multiple" size=6>
										<option value="none">None</option>
									   @foreach($workout_exercise as $workouts)
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
	
	$("#activity").change(function(){
		
		var activity = $(this).val();
		
		if(activity=='rest')
		{
			$(".field_wrapper3").hide();
		}
		else
		{
			$(".field_wrapper3").show();
		}
		
	});
	
});

</script>


<script type="text/javascript">

$(document).ready(function(){
	
	/* var countries = [];
	 //$("#link_exercise").change(function(){
        $.each($("#link_exercise option:selected"), function(){            
            countries.push($(this).val());
        });
		 //alert("You have selected the country - " + countries.join(", "));
		$("#linked").append(countries.join(", "));
	// }); */
       
	var selText="";
	//var selText=[];
	 $("#link_exercise").change(function(){
		$("#link_exercise option:selected").each(function () {
			 var $this = $(this);
//$("#selected_values").empty();			 
			 if(selText !=""){
			  selText = selText.concat(","); 
			  selText = selText.concat($this.text());
			 }
			 else
				selText=$this.text();
		  });
		  document.getElementById("selected_values").value=selText;
		 
		
	}); 
	
	
});

</script>





@endsection