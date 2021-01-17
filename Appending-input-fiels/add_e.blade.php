@extends('trainer_panel.layouts.app')

@section('content')
<div style="min-height: 600px;">
<div class="table-agile-info">
  <div class="panel panel-default">
    <div class="panel-heading">
      Add Yoga Exercise
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
                <form class="cmxform form-horizontal " id="signupForm" method="post" action="{!! url('trainer/add_yoga_exercise_action') !!}" novalidate="novalidate" enctype="multipart/form-data">
                  @csrf
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
                      <label for="description" class="control-label col-lg-3">Description</label>
                      <div class="col-lg-6">
                        <!-- <input class="form-control" id="description" value="{{ old('description') }}" name="description" type="text">-->
                           
						   <textarea class="form-control" name="description" id="description" value="{{ old('description') }}" rows="5"></textarea>

					   
					   @if ($errors->has('description'))
                          <div class="alert alert-danger">
                            {!! $errors->first('description') !!}
                          </div>
                        @endif
                      </div>
                    </div>            
					 <div class="form-group ">
                      <label for="amount" class="control-label col-lg-3">Add Equipment</label>
                      <div class="col-lg-6 field_wrapper1">
                       <!-- <input class="form-control" id="grocery" value="{{ old('grocery') }}" name="grocery" type="text">
                        @if ($errors->has('grocery'))
                          <div class="alert alert-danger">
                            {!! $errors->first('grocery') !!}
                          </div>
                        @endif
                      </div> -->
					  <!-- <div class="field_wrapper"> -->
						
							<input type="text" class="form-control" name="equipment_name[]"  value="" />
						
							<a href="javascript:void(0);" class="add_button" title="Add field"><i class="fa fa-plus" aria-hidden="true"></i>
							</a>
					
						</div>
					<!-- </div> -->
					  
                    </div>
					
					<div class="form-group ">
                      <label for="description" class="control-label col-lg-3">Tips</label>
                      <div class="col-lg-6">
                        <!--<input class="form-control" id="description" value="" name="description" type="text">-->
						<textarea class="form-control" id="tips" value="" name="tips" maxlength="400" rows="5"></textarea>
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
                      <label for="category" class="control-label col-lg-3">Level</label>
                      <div class="col-lg-6">
                        <select id="level" name="level">
                         <option value="beginner">Beginner</option>
                         <option value="intermediate ">Intermediate</option>
                         <option value="advanced ">Advanced </option>
                          
                        </select>
                                              </div>
                    </div>
					
                    <div class="form-group">
                      <div class="col-lg-offset-3 col-lg-6">
                        <button class="btn btn-primary" type="submit">Submit</button>
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
    var maxField = 4; //Input fields increment limitation
    var addButton1 = $('.add-yoga-set'); //Add button selector	
    var wrapper = $('.field_wrapper3'); //Input field wrapper
   var newElement_yoga = '<tr><td>Sets1:<button type="button" class="again-button3" onclick="add_reps()">+Add reps</button></td></tr><tr><td><br/>Reps1:<input type="number" value="" name="set_reps" placeholder="4.."/></td><td><br/>Hold Stretches for:<input type="number" value="" name="set_hold_stretch" placeholder="Why do you buy this car?"/></td><td><br/>Rest:<input type="number" value="" name="set_rest" placeholder="Why do you buy this car?"/></td></tr><tr></br></br><td>tips set1 <textarea placeholder="add tips" name="set_tips" id="jaddress" class="form-control" cols="2" rows="2"></textarea></td></tr><tr><td><div class="newElement1"></div></td></tr>';
    var x = 1; //Initial field counter is 1
    $(addButton1).click(function(){
            x++; //Increment field counter
		    $(wrapper).append(newElement_yoga);
    });
    //Once remove button is clicked
    $(wrapper).on('click', '.remove_button', function(e){
        e.preventDefault();
        $(this).parent('div').remove(); //Remove field html
        x--; //Decrement field counter
    });
});
</script>


<script>
function add_reps(){
	$(document).ready(function(){
    var maxField = 4; //Input fields increment limitation
    var addButton = $('.again-button3'); //Add button selector
    var wrapper = $('.newElement1'); //Input field wrapper
    var fieldHTML = '<tr><td><div >reps<input type="text" class="form-control" name="reps[]" value="" placeholder="add supersets" /><a href="javascript:void(0);" class="remove_button"><i class="fa fa-minus" aria-hidden="true"></i></a><tr>'; //New input field html 
    var x = 1; //Initial field counter is 1
    
    //Once add button is clicked
    $(addButton).click(function(){
        //Check maximum number of input fields
        if(x < maxField){ 
           //  //Increment field counter
		   x++;
            $(wrapper).append(fieldHTML); //Add field html
			
        }
    });
	
	$(wrapper).on('click', '.remove_button', function(e){
        e.preventDefault();
        $(this).parent('div').remove(); //Remove field html
        x--; //Decrement field counter
    });
    
});
	

}
</script>

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




@endsection