@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
   <div class="row">
   <div class="col-md-10 offset-md-1 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                @if (\Session::has('error'))
                  <div class="alert alert-danger">
                     {!! \Session::get('error') !!}
                  </div>
                @endif
                  <h4 class="card-title">Add Words</h4>
                  
                <form class="forms-sample" method="post" action="{{route('words.store')}}" enctype="multipart/form-data" id="create_company">
                  @csrf

                      <div class="form-group ">
                       <label class="control-label">Select Conditions</label>
                        <div class="col-md-6">
                        <select class="form-control"  name="condition" id="condition">
                        <option value="1">Select Condition</option>
                        @foreach($condition as $value)
                         <option value="{{$value}}">{{$value}}</option>
                          @endforeach
                        </select>
                         </div>
                         @if ($errors->has('condition'))
                    <div class="error">{{ $errors->first('condition') }}</div>
                    @endif
                      </div>

                      <div class="form-group study-data" style="display:none;" required>
                       <label class="control-label">Select Study</label>
                       <div class="fetch-data"></div>
                       
                      </div>

                    <div class="words" style="display:none;">
                    <div class="form-group">
                      <div class="row">
                            <div class="col-md-6">
                      <label class="control-label" for="name"> Threat Word </label>
                      <input type="text" name="threat_word" class="form-control" id="threat_word" placeholder="threat_word"
                         />
                    </div>
                  </div></div>
                    
                    <div class="form-group">
                      <div class="row">
                            <div class="col-md-6">
                      <label class="control-label" for="training_trial_count"> Neutral Word
                      </label>
                      <input type="neutral_word" name="neutral_word" class="form-control" id="neutral_word" placeholder="neutral_word"
                        />
                    </div>
                  </div></div>
                </div>

                <div class="faces" style="display:none;">
                    <div class="form-group">
                      <div class="row">
                            <div class="col-md-6">
                      <label class="control-label" for="name"> Threat Face </label>
                      <input type="file" name="threat_face" class="form-control" 
                         />
                    </div>
                  </div></div>
                    
                    <div class="form-group">
                      <div class="row">
                            <div class="col-md-6">
                      <label class="control-label" for="training_trial_count"> Neutral Face
                      </label>
                      <input type="file" name="neutral_face" class="form-control"
                        />
                    </div>
                  </div></div>
                </div>
                    
                    <div class="form-group sub-btn" style="display:none;">

                    <button type="submit" class="btn btn-primary mr-2">Submit</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
   </div>
</div>

@endsection

@section('footerScript')
@parent

<script>
$(document).ready(function() {

  $("#condition").on('change',function(){
  var conditions=$(this).val();
  if(conditions!='')
  {
          $('.study-data').show();

  }
  if(conditions=='ABM_WITH_WORDS'||conditions=='ACT_WITH_WORDS'||conditions=='PLACEBO_WITH_WORDS'||conditions=='QUESTIONS')
    {
            $('.faces').hide();
            $('.words').show();
            $('.sub-btn').show();
    }
    else if(conditions==1){
          $('.study-data').hide();
          $('.faces').hide();
          $('.words').hide();
          $('.sub-btn').hide();
    }
    else
    {
            $('.faces').show();
            $('.words').hide();
            $('.sub-btn').show();

    }
  //alert(answer_type);
  // if(answer_type=='rating' || answer_type=='text')
  // {
  //  $('.options').attr('disabled', 'disabled');
  // }
  // else{
  //      $('.options').attr('disabled', false);
  // }
});

});

</script>
<script>
$(document).ready(function() {

  $("#condition").on('change',function(){
  var conditions=$(this).val();
  var op=''; 
  //alert(conditions);
   $.ajax({
    type : 'POST',
    url : '{{route("match_studies")}}',
    data:{"_token": "{{ csrf_token() }}",'search':conditions},
    success:function(data2){
       

      console.log(data2);
      //console.log(data2.data.length);
      var data3=data2.data;
      console.log(data3);
       op+='<div class="col-md-6">';
            op+='<select class="form-control"  name="study" id="study">';
            if(data3.length>0)
            {
            for(var i=0;i<data3.length;i++){
              op+='<option value='+data3[i]['_id']+'>'+data3[i]['studyId']+'</option>';
            }
          }
          else
          {
            op+='<option value="">'+"No Study Found for the selected Condition .Please Select Another Condition"+'</option>';

          }
             op+='</div>';
             $('.fetch-data').html(op); 
    }
    });

  
});

});

</script>

  
  @endsection
  