<div class="create-miletone">
    <h3 class="custom"> <span style="font-size: 20px;">+</span>&nbsp;Add your work for today
    </h3>
    <form action="{{route('create_timesheet')}}" method="post" onsubmit="return validateForm()">
        @csrf
    <div class="milestone-form">
        <div class="form-group">
            {{-- <label>Upload Project Brief File</label> --}}
            <div id="file-uploadsection" class="fallback dropzone  text-center p-2">
          
            {{-- <input type="file" name="project_file"  /> --}}
          </div>

          
          </div>
        <input type="text" name="description" placeholder="Description" required>
        <div class="row">
            <div class="col-md-6">
                <select name="hours" required>
                    <option value="" selected="selected" disabled>No. of hours worked</option>
                    @for($i=1;$i<=10;$i++)
                <option value="{{$i}}">{{$i}}</option>
                  @endfor
                </select>
            </div>
            <div class="col-md-6">
                <select name="milestone_id" required>
                    <option value="" selected="selected" disabled>Milestone</option>
                    @foreach($milestones as $key=> $milestone)
                <option value="{{$milestone->id}}">{{$milestone->title}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <button type="submit">upload</button>
    </div>
</form>
</div>

@foreach($milestones as $key=> $milestone)
@if($milestone->timesheet->isNotEmpty())
<h3>{{$milestone->title}}</h3>
<div class="milestoneTableDiv">
    <table class="table table-striped">
        <thead>
            <tr>
                <th scope="col">Date and time</th>
                <th scope="col">Description</th>
                <th scope="col">HRS</th>
                <th scope="col">Document</th>
                <th scope="col">Files</th>
                <th scope="col">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($milestone->timesheet as $times)
            <tr>
                <td>
                    {{date('d/m/Y',strtotime($times->updated_at))}}
                    <p class="time"> {{date('h:i a',strtotime($times->updated_at))}}</p>
                </td>
                <td>{{$times->description}}</td>
                <td>{{$times->hours}}</td>
                <td>{{$times->document}}</td>
                <td>
                    <a href="{{$times->full_file_url}}" type="button" class="downBtn" target="_blank">
                        <img src="{{asset('web/images/dwnlrd.png')}}" alt="file">
                    </a>
                </td>
                <td>
                    @if($times->client_approved==0)
                        PENDING
                    @elseif($times->client_approved==1)
                        APPROVED
                    @else
                        DECLINED
                    @endif
                </td>
            </tr>
            @endforeach
           
        </tbody>
    </table>
</div>
@endif
@endforeach