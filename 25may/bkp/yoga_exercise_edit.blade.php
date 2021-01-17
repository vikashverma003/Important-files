@extends('trainer_panel.layouts.app')

@section('content')
<div style="min-height: 600px;">
<div class="table-agile-info">
  <div class="panel panel-default">
    <div class="panel-heading">
      Edit Yoga Exercise
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
                <form class="cmxform form-horizontal " id="signupForm" method="post" action="{!! url('trainer/yoga_exercise_update') !!}" novalidate="novalidate" enctype="multipart/form-data">
                  @csrf
                   
                    <div class="form-group ">
                      <label for="title" class="control-label col-lg-3">Title</label>
                      <div class="col-lg-6">
                        <input class="form-control" id="title" value="{!! $yoga_exercise['title'] !!}" name="title" type="text">
                        @if ($errors->has('title'))
                          <div class="alert alert-danger">
                            {!! $errors->first('title') !!}
                          </div>
                        @endif
                      </div>
                    </div>
					
					<input type="hidden" name="yoga_id" value="{{ $yoga_exercise['_id']}}">
                    <div class="form-group ">
                      <label for="description" class="control-label col-lg-3">Description</label>
                      <div class="col-lg-6">
                        <!-- <input class="form-control" id="description" value="{!! $yoga_exercise['description'] !!}" name="description" type="text"> -->
                        
						<textarea class="form-control" id="description"  name="description" maxlength="400" rows="5">{!! $yoga_exercise['description'] !!}</textarea>

						@if ($errors->has('description'))
                          <div class="alert alert-danger">
                            {!! $errors->first('description') !!}
                          </div>
                        @endif
                      </div>
                    </div>
					<div class="form-group ">
                      <label for="description" class="control-label col-lg-3">Tips</label>
                      <div class="col-lg-6">
                        <!-- <input class="form-control" id="tips" value="{!! $yoga_exercise['tips'] !!}" name="tips" type="text"> -->
                        
					<textarea class="form-control" id="tips"  name="tips" maxlength="400" rows="5">{!! $yoga_exercise['tips'] !!}</textarea>

						
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
                        <br/><br/>
                        <img src="{!! url('public/images/'.$yoga_exercise['image']) !!}" width="300" height="250">
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
						@foreach($yoga_exercise->equipment_name as $eq_name)
							<input type="text" class="form-control" name="equipment_name[]"  value="{{$eq_name}}" />
						
						@endforeach
					
						</div>
					<!-- </div> -->
					  
                    </div>
					<div class="form-group ">
                      <label for="category" class="control-label col-lg-3">Level</label>
                      <div class="col-lg-6">
                        <select id="level" name="level">
                         <option value="beginner" <?php if($yoga_exercise->level==="beginner") echo "selected";  ?>>Beginner</option>
                         <option value="intermediate" <?php if($yoga_exercise->level==="intermediate") echo "selected";  ?>>Intermediate</option>
                         <option value="advanced " <?php if($yoga_exercise->level==="advanced") echo "selected";  ?>>Advanced </option>
                          
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

@endsection