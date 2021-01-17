@extends('trainer_panel.layouts.app')

@section('content')
<?php
use App\Trainer as Trainer;
?>
<div style="min-height: 600px;">
<div class="table-agile-info">
  <div class="panel panel-default">
    <div class="panel-heading">
      Yoga Exercise List
    </div>
    <div class="table-responsive" style="padding: 20px;">
      <a href="{!! url('trainer/add_yoga_exercise') !!}" class="btn btn-success"><i class="fa fa-plus"></i> Add Yoga Exercise</a>
       <br/><br/>
    	@if (session('er_status'))
        <div class="alert alert-danger">{!! session('er_status') !!}</div>
      @endif
      @if (session('su_status'))
        <div class="alert alert-success">{!! session('su_status') !!}</div>
      @endif

      <div class="tab-content">
          <table class="table table-striped b-t b-light" id="example">
            <thead>
              <tr>
                <th style="width:20px;">#</th>
                <th>Title</th>
               <!-- <th>Exercises</th> -->
                <th>Time</th>
                <th>Approval</th>
                <th>Equipment</th>
                <th style="width:100px;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php $i = 1; ?>
                @foreach($yoga_exercises as $yoga_exercise)
                    <tr>
                      <td>{!! $i++ !!}</td>
                      <td>{!! $yoga_exercise['title'] !!}</td>
                     <!-- <td>{!! $yoga_exercise['exercises'] !!}</td> -->
                      <td>{!! $yoga_exercise['time'] !!}</td>
                      <td>{!! $yoga_exercise['approval'] !!}</td>
					  <td><ul style="margin-left: 54px;">   
						@foreach($yoga_exercise['equipment_name'] as $info) 
								  <li>{{$info}}</li>
					    @endforeach
						</ul>
					  </td>
                      <td>
                        <a href="{!! url('trainer/yoga_exercise_profile',$yoga_exercise['_id']) !!}"><i class="fa fa-eye"></i></a>&nbsp;&nbsp;&nbsp;<a href="{!! url('trainer/yoga_exercise_edit',$yoga_exercise['_id']) !!}"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;&nbsp;<a href="{!! url('trainer/yoga_exercise_delete',$yoga_exercise['_id']) !!}"><i class="fa fa-times text-danger text"></i></a>
                      </td>
                    </tr>
                @endforeach
            </tbody>
          </table>
      </div>
    </div>
  </div>
</div>
</div>

@endsection