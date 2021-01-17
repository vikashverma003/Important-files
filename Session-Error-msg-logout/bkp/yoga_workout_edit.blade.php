@extends('trainer_panel.layouts.app')

@section('content')
<div style="min-height: 600px;">
<div class="table-agile-info">
  <div class="panel panel-default">
    <div class="panel-heading">
      Edit Yoga Workout
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
                <form class="cmxform form-horizontal " id="signupForm" method="post" action="{!! url('trainer/yoga_workout_update') !!}" novalidate="novalidate" enctype="multipart/form-data">
                  @csrf
                    <div class="form-group ">
                      <input type="hidden" name="yoga_workout_id" value="{!! $yoga_workout['_id'] !!}">
                      <label for="title" class="control-label col-lg-3">Title</label>
                      <div class="col-lg-6">
                        <input class="form-control" id="title" value="{!! $yoga_workout['title'] !!}" name="title" type="text">
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
                        <input class="form-control" id="description" value="{!! $yoga_workout['description'] !!}" name="description" type="text">
                        @if ($errors->has('description'))
                          <div class="alert alert-danger">
                            {!! $errors->first('description') !!}
                          </div>
                        @endif
                      </div>
                    </div>
                    @if($yoga_workout['exercises'])<div class="form-group ">
                      <label for="exercises" class="control-label col-lg-3">Exercises</label>
                      <div class="col-lg-6">
                        <input class="form-control" id="exercises" value="{!! $yoga_workout['exercises'] !!}" name="exercises" type="text">
                        @if ($errors->has('exercises'))
                          <div class="alert alert-danger">
                            {!! $errors->first('exercises') !!}
                          </div>
                        @endif
                      </div>
                    </div> @endif
                    <div class="form-group ">
                      <label for="time" class="control-label col-lg-3">Time</label>
                      <div class="col-lg-6">
                        <input class="form-control" id="time" value="{!! $yoga_workout['time'] !!}" name="time" type="text">
                        @if ($errors->has('time'))
                          <div class="alert alert-danger">
                            {!! $errors->first('time') !!}
                          </div>
                        @endif
                      </div>
                    </div>
					<div class="form-group ">
                      <label for="time" class="control-label col-lg-3">Period</label>
                      <div class="col-lg-6">
                        <input class="form-control" id="period" value="{!! $yoga_workout['period'] !!}" name="period" type="number">
                        @if ($errors->has('period'))
                          <div class="alert alert-danger">
                            {!! $errors->first('period') !!}
                          </div>
                        @endif
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
                        <br/>
                        <br/>
                        <img src="{!! url('public/images/'.$yoga_workout['image']) !!}" width="300" height="250">
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