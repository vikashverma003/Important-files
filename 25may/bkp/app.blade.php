<!DOCTYPE html>
<head>
<title>Fitneb</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="keywords" content="Visitors Responsive web template, Bootstrap Web Templates, Flat Web Templates, Android Compatible web template, 
Smartphone Compatible web template, free webdesigns for Nokia, Samsung, LG, SonyEricsson, Motorola web design" />
<script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); function hideURLbar(){ window.scrollTo(0,1); } </script>
<!-- bootstrap-css -->
<link rel="stylesheet" href="{!! url('public/css/bootstrap.min.css') !!}" >
<!-- //bootstrap-css -->
<!-- Custom CSS -->
<link href="{!! url('public/css/style.css') !!}" rel='stylesheet' type='text/css' />
<link href="{!! url('public/css/style-responsive.css') !!}" rel="stylesheet"/>
<!-- font CSS -->
<link href='//fonts.googleapis.com/css?family=Roboto:400,100,100italic,300,300italic,400italic,500,500italic,700,700italic,900,900italic' rel='stylesheet' type='text/css'>
<!-- font-awesome icons -->
<link rel="stylesheet" href="{!! url('public/css/font.css') !!}" type="text/css"/>
<link href="{!! url('public/css/font-awesome.css') !!}" rel="stylesheet"> 
<link rel="stylesheet" href="{!! url('public/css/morris.css') !!}" type="text/css"/>
<!-- calendar -->
<link rel="stylesheet" href="{!! url('public/css/monthly.css') !!}">
<!-- //calendar -->
<!-- //font-awesome icons -->
<script src="{!! url('public/js/jquery2.0.3.min.js') !!}"></script>
<script src="{!! url('public/js/raphael-min.js') !!}"></script>
<script src="{!! url('public/js/morris.js') !!}"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs/dt-1.10.18/datatables.min.css"/>
<style type="text/css">
    ul.top-menu>li>a:hover,ul.top-menu>li>a:focus {
        background:#08b7dd;
        text-decoration:none;
        color:#fff !important;
        padding-right:8px !important;
    }
    .nav .open>a, .nav .open>a:focus, .nav .open>a:hover {
         background:#08b7dd !important;
        color:#fff !important;
    }
    .top-nav ul.top-menu>li>a:hover,.top-nav ul.top-menu>li>a:focus {
        border:1px solid #08b7dd;
        background:#08b7dd !important;
        border-radius:100px;
        -webkit-border-radius:100px;
    }
    ul.sidebar-menu li a {
        color: #000;
    }
    ul.sidebar-menu li a.active{
        color: #000;
    }
    ul.sidebar-menu li a.active i {
        color: #000;
    }
    ul.sidebar-menu li ul.sub li a {
        color: #000;
    }
	.form-control[name="field_name[]"] {

    float: left;
    width: calc(100% - 34px);
    margin-bottom: 15px;

}
.add_button, .remove_button {

    float: right;
    margin: 4px 0 0 8px;

}
</style>
</head>
<body>
<section id="container">
<!--header start-->
<header class="header fixed-top clearfix" style="background: #CCBB00;">
<!--logo start-->
<div class="brand" style="background: #08b7dd;">
    <a href="{!! url('trainer/dashboard') !!}" class="logo">
        FITNEB
    </a>
    <div class="sidebar-toggle-box" style="background: #08b7dd;">
        <div class="fa fa-bars"></div>
    </div>
</div>
<!--logo end-->

<div class="top-nav clearfix">
    <!--search & user info start-->
    <ul class="nav pull-right top-menu">
        <li>
            <!-- <input type="text" class="form-control search" placeholder=" Search"> -->
        </li>
        <!-- user login dropdown start-->
        <li class="dropdown">
            <a data-toggle="dropdown" class="dropdown-toggle" href="#" style="background: #08b7dd;">
                <?php
                use App\Trainer as Trainer;
                $trainer = Trainer::where(['_id'=>Session::get('trainer_user_id')])->select('name')->first();
                ?>
                <img alt="" src="images/2.png">
                <span class="username"><?= $trainer->name; ?></span>
                <b class="caret"></b>
            </a>
            <ul class="dropdown-menu extended logout">
                <li><a href="#"><i class=" fa fa-suitcase"></i>Profile</a></li>
                <li><a href="#"><i class="fa fa-cog"></i> Settings</a></li>
				<li><a href="#" data-toggle="modal" data-target="#exampleModal" data-whatever="@mdo" ><i class="icon-large icon-unlock
"></i>Change Password</a></li>
                <li><a href="{!! url('trainer/logout') !!}"><i class="fa fa-key"></i> Log Out</a></li>
            </ul>
        </li>
        <!-- user login dropdown end -->
       
    </ul>
    <!--search & user info end-->
</div>
</header>
<!--header end-->
<!--sidebar start-->
<!-- Change Password popup -->
					
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">

  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Change Password</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
	  @if (session('er_status'))
            <div class="alert alert-danger">{!! session('er_status') !!}</div>
        @endif
		@if (session('su_status'))
                  <div class="alert alert-success">{!! session('su_status') !!}</div>
                @endif
        <form action="{!! url('trainer/changePasswordTrainer') !!}" method="post">
		@csrf
          <div class="form-group">
            <label for="recipient-name" class="col-form-label">Current Password:</label>
            <input type="text" class="form-control" id="current_password" name="current_password">
          </div>
          <div class="form-group">
            <label for="message-text" class="col-form-label">New Password:</label>
			<input type="text" class="form-control" id="new_password" name="new_password">
            <!-- <textarea class="form-control" id="message-text"></textarea> -->
          </div>
       
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit"  name="submit" class="btn btn-primary">Submit </button>
      </div>
	   </form>
      </div>
    </div>
  </div>
</div>



<aside>
    <div id="sidebar" class="nav-collapse" style="background: #feed02;">
        <!-- sidebar menu start-->
        <div class="leftside-navigation">
            <ul class="sidebar-menu" id="nav-accordion">
                <li>
                    <a class="active" href="{!! url('trainer/dashboard') !!}">
                        <i class="fa fa-dashboard"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li class="sub-menu">
                    <a href="javascript:;">
                        <i class="fa fa-book"></i>
                        <span>Running</span>
                    </a>
                    <ul class="sub">
                        <li><a href="{!! url('trainer/training_list') !!}">Workout List</a></li>
						  <li><a href="{!! url('trainer/tl_exercise_list') !!}">Exercise List</a></li> 
                    </ul>
                </li>

                <li class="sub-menu">
                    <a href="javascript:;">
                        <i class="fa fa-book"></i>
                        <span>Weight Lifting</span>
                    </a>
                    <ul class="sub">
                         <li><a href="{!! url('trainer/wl_exercise_list') !!}">Exercise List</a></li> 
                        <li><a href="{!! url('trainer/wl_workout_list') !!}">Workout List</a></li>
                       <!-- <li><a href="{!! url('trainer/wl_categories_list') !!}">Categories</a></li> -->
                    </ul>
                </li>

                <li class="sub-menu">
                    <a href="javascript:;">
                        <i class="fa fa-book"></i>
                        <span>Yoga</span>
                    </a>
                    <ul class="sub">
                        <li><a href="{!! url('trainer/yoga_exercise_list') !!}">Exercise List</a></li>
                        <li><a href="{!! url('trainer/yoga_workout_list') !!}">Workout List</a></li>
                        <!-- <li><a href="{!! url('trainer/yoga_categories_list') !!}">Categories</a></li> -->
                    </ul>
                </li>

                <li class="sub-menu">
                    <a href="javascript:;">
                        <i class="fa fa-book"></i>
                        <span>Diets</span>
                    </a>
                    <ul class="sub">
                        <li><a href="{!! url('trainer/diet_list') !!}">Diet List</a></li>
                        <li><a href="{!! url('trainer/recipe_list') !!}">Recipe List</a></li> 
                    </ul>
                </li>
            </ul>            
        </div>
        <!-- sidebar menu end-->
    </div>
</aside>
<!--sidebar end-->
<!--main content start-->
<section id="main-content">
    <section class="wrapper">
        @yield('content')
    </section>
 <!-- footer -->
          <div class="footer" style="background: #CCBB00;">
            <div class="wthree-copyright">
              <p>© 2019 Fitneb. All rights reserved.</p>
            </div>
          </div>
  <!-- / footer -->
</section>
<!--main content end-->
</section>
<script src="{!! url('public/js/bootstrap.js') !!}"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/bs/dt-1.10.18/datatables.min.js"></script>
<script src="{!! url('public/js/jquery.dcjqaccordion.2.7.js') !!}"></script>
<script src="{!! url('public/js/scripts.js') !!}"></script>
<script src="{!! url('public/js/jquery.slimscroll.js') !!}"></script>
<script src="{!! url('public/js/jquery.nicescroll.js') !!}"></script>
<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="js/flot-chart/excanvas.min.js"></script><![endif]-->
<script src="{!! url('public/js/jquery.scrollTo.js') !!}"></script>
<script src="{!! url('public/js/ckeditor.js') !!}"></script>
<!-- morris JavaScript -->  
<!-- calendar -->
    <script type="text/javascript" src="js/monthly.js"></script>
    <!-- //calendar -->
    <script type="text/javascript">
        $(document).ready(function() {
            $('#example').DataTable();
            $('.example').DataTable();
        } );
    </script>

    <script>
        ClassicEditor
            .create( document.querySelector( '#description_ckeditor' ), {
                // toolbar: [ 'heading', '|', 'bold', 'italic', 'link' ]
            } )
            .then( editor => {
                window.editor = editor;
            } )
            .catch( err => {
                console.error( err.stack );
            } );
    </script>
	
	<!-- for adding the fields -->
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	
	<script type="text/javascript">
$(document).ready(function(){
    var maxField = 10; //Input fields increment limitation
    var addButton = $('.add_button'); //Add button selector
    var wrapper = $('.field_wrapper'); //Input field wrapper
    var fieldHTML = '<div ><input type="text" class="form-control" name="field_name[]" value=""/><a href="javascript:void(0);" class="remove_button"><i class="fa fa-minus" aria-hidden="true"></i></a></div>'; //New input field html 
    var x = 1; //Initial field counter is 1
    
    //Once add button is clicked
    $(addButton).click(function(){
        //Check maximum number of input fields
        if(x < maxField){ 
            x++; //Increment field counter
            $(wrapper).append(fieldHTML); //Add field html
        }
    });
    
    //Once remove button is clicked
    $(wrapper).on('click', '.remove_button', function(e){
        e.preventDefault();
        $(this).parent('div').remove(); //Remove field html
        x--; //Decrement field counter
    });
});
</script>


<!-- adding the equipment -->
	<script type="text/javascript">
$(document).ready(function(){
    var maxField = 4; //Input fields increment limitation
    var addButton = $('.add_button'); //Add button selector
    var wrapper = $('.field_wrapper1'); //Input field wrapper
    var fieldHTML = '<div ><input type="text" class="form-control" name="equipment_name[]" value=""/><a href="javascript:void(0);" class="remove_button"><i class="fa fa-minus" aria-hidden="true"></i></a></div>'; //New input field html 
    var x = 1; //Initial field counter is 1
    
    //Once add button is clicked
    $(addButton).click(function(){
        //Check maximum number of input fields
        if(x < maxField){ 
            x++; //Increment field counter
            $(wrapper).append(fieldHTML); //Add field html
        }
    });
    
    //Once remove button is clicked
    $(wrapper).on('click', '.remove_button', function(e){
        e.preventDefault();
        $(this).parent('div').remove(); //Remove field html
        x--; //Decrement field counter
    });
});
</script>
	
<!-- Adding the sets -->
	

<!-- adding the equipment -->
	<script type="text/javascript">
$(document).ready(function(){
    var maxField = 4; //Input fields increment limitation
    var addButton = $('.add-set'); //Add button selector
    var wrapper = $('.field_wrapper2'); //Input field wrapper
   // var fieldHTML ='<div >dfxcvxcvxcvxc</div>';
   var newElement = '<tr><td>Sets1:<button type="button" class="again-button" onclick="abc()">+Add superset and reps</button></td></tr><tr><td><br/>Reps1:<input type="number" value="" name="set1_reps" placeholder="4.."/></td><td><br/>Rest:<input type="text" value="" name="set1_rest" placeholder="Why do you buy this car?"/></td><td><br/>Percentage of 1 reps in %:<input type="text" value="" name="set1_reps_percentage" placeholder="Why do you buy this car?"/></td></tr><tr></br></br><td>tips set1 <textarea placeholder="add tips" name="set1_tips" id="jaddress" class="form-control" cols="2" rows="2"></textarea></td></tr><br/><br/><br/><tr><td>Equipment: <input type="text" value="" name="set1_equipment" placeholder="4.."/></td></tr><tr><td><div class="newElement"></div></td></tr>';
   
   var newElement1 = '<tr><td>Sets2:<button type="button" class="again-button1" onclick="abc1()">+Add superset and reps</button></td></tr><tr><td><br/>Reps1:<input type="number" value="" name="set2_reps" placeholder="4.."/></td><td><br/>Rest:<input type="text" value="" name="set2_rest" placeholder="Why do you buy this car?"/></td><td><br/>Percentage of 1 reps in %:<input type="text" value="" name="set2_reps_percentage" placeholder="Why do you buy this car?"/></td></tr><tr></br></br><td>tips set2 <textarea placeholder="add tips" name="set2_tips" id="jaddress" class="form-control" cols="2" rows="2"></textarea></td></tr><br/><br/><br/><tr><td>Equipment: <input type="text" value="" name="set2_equipment[]" placeholder="4.."/></td></tr><tr><td><div class="newElement1"></div></td></tr>';
   
   var newElement2 = '<tr><td>Sets3:<button type="button" class="again-button2" onclick="abc2()">+Add superset and reps</button></td></tr><tr><td><br/>Reps1:<input type="number" value="" name="set3_reps[]" placeholder="4.."/></td><td><br/>Rest:<input type="text" value="" name="set3_rest[]" placeholder="Why do you buy this car?"/></td><td><br/>Percentage of 1 reps in %:<input type="text" value="" name="set3_reps_percentage[]" placeholder="Why do you buy this car?"/></td></tr><tr></br></br><td>tips set3 <textarea placeholder="add tips" name="set3_tips" id="jaddress" class="form-control" cols="2" rows="2"></textarea></td></tr><br/><br/><br/><tr><td>Equipment: <input type="text" value="" name="set3_equipment[]" placeholder="4.."/></td></tr><tr><td><div class="newElement2"></div></td></tr>';

	
	
    var x = 1; //Initial field counter is 1
    
    //Once add button is clicked
    $(addButton).click(function(){
        //Check maximum number of input fields
        if(x < maxField){ 
            x++; //Increment field counter
           // $(wrapper).append(fieldHTML);
		   if(x==2)
		   {
		    $(wrapper).append(newElement);
		   }
		   else if(x==3)
		   {
			   
			   $(wrapper).append(newElement1);
			   
		   }
		    else if(x==4)
		   {
			   
			   $(wrapper).append(newElement2);
			   
		   }
        }
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
function abc(){
	//$(document).ready(function(){
    var maxField = 4; //Input fields increment limitation
    var addButton = $('.again-button'); //Add button selector
    var wrapper = $('.newElement'); //Input field wrapper
    var fieldHTML = '<tr><td><div >superset<input type="text" class="form-control" name="set1_superset[]" value="" placeholder="add supersets" /><a href="javascript:void(0);" class="remove_button"><i class="fa fa-minus" aria-hidden="true"></i></a><td><div >reps<input type="number" class="form-control" name="set1_superset_reps[]" value="" placeholder="add supersets" /><a href="javascript:void(0);" class="remove_button"><i class="fa fa-minus" aria-hidden="true"></i></a></div></td><tr>'; //New input field html 
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
    
//});
	

}
</script>


<script>
function abc1(){
	$(document).ready(function(){
    var maxField = 4; //Input fields increment limitation
    var addButton = $('.again-button1'); //Add button selector
    var wrapper = $('.newElement1'); //Input field wrapper
    var fieldHTML = '<tr><td><div ><input type="text" class="form-control" name="set2_superset[]" value="" placeholder="add supersets" /><a href="javascript:void(0);" class="remove_button"><i class="fa fa-minus" aria-hidden="true"></i></a><td><div ><input type="number" class="form-control" name="set2_superset_reps[]" value="" placeholder="add supersets" /><a href="javascript:void(0);" class="remove_button"><i class="fa fa-minus" aria-hidden="true"></i></a></div></td><tr>'; //New input field html 
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


<script>
function abc2(){
	$(document).ready(function(){
    var maxField = 4; //Input fields increment limitation
    var addButton = $('.again-button2'); //Add button selector
    var wrapper = $('.newElement2'); //Input field wrapper
    var fieldHTML = '<tr><td><div ><input type="text" class="form-control" name="set3_superset[]" value="" placeholder="add supersets" /><a href="javascript:void(0);" class="remove_button"><i class="fa fa-minus" aria-hidden="true"></i></a><td><div ><input type="number" class="form-control" name="set3_superset_reps[]" value="" placeholder="add supersets" /><a href="javascript:void(0);" class="remove_button"><i class="fa fa-minus" aria-hidden="true"></i></a></div></td><tr>'; //New input field html 
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
		
		var rest  = $(this).val();
		if(rest =='rest')
		{
			$('.field_wrapper2').hide();
		}
		else
		{
			$('.field_wrapper2').show();
		}
	});

	
});

</script>



<!-- Add yoga sets -->

<!-- adding the equipment -->




	
	
	
</body>
</html>
