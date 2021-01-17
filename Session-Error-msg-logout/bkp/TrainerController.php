<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Admin as Admin;
use App\User as User;
use App\Trainer as Trainer;
use App\Workout as Workout;
use App\WorkoutDetail as WorkoutDetail;
use App\TrainingDetail as TrainingDetail;
use App\Goal as Goal;
use App\Diet as Diet;
use App\Recipe as Recipe;
use App\DietDetail as DietDetail;
use App\WlCategory as WlCategory;
use App\YogaCategory as YogaCategory;
use App\Category as Category;
use App\ApproveWorkout;
use Validator;
use Hash;
use Illuminate\Support\Facades\Mail;

use App\DietDetailWeek;

use DB;
class TrainerController extends Controller
{
    public function trainerlogin(Request $request){
    	$messages = [
            'email.required' => 'Email is required',
            'password.required' => 'Password is required'
        ];

        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required' 

        ], $messages);

        if($validator->fails()){
            return redirect()->back()->withErrors($validator)->withInput();
        }
        else{
			
			$email = $request->input('email');
			
			// echo $email;
			
			// die();
			
            // $users = Trainer::where('email','=',$request->input('email'))->first();
			       //$users = Trainer::where('email','=',array($email))->first();
				  // $users= Trainer::where(DB::raw('email'), $email)->first();
				  
	             // $users =  Trainer::whereRaw("UPPER('.$email.') LIKE '%'". strtoupper($value)."'%'"); 
				 
				$users = Trainer::where('email', 'like', '%' . $email . '%')->first();
				
            if($users){
            	$hashedPassword = $users['password'];
            	if (Hash::check($request->input('password'), $hashedPassword)) {
            		$request->session()->put('trainer_user_id', $users['_id']);
                	return redirect('trainer/dashboard');
            	}
            	else{
            		return redirect()->back()->with("er_status","Wrong Email ID or Password")->withInput();
            	}
            }
            else{
                return redirect()->back()->with("er_status","Wrong Email ID or Password")->withInput();
            }
        }
    }

    public function dashboard(Request $request){
    	$user_id = $request->session()->get('trainer_user_id');
    	if($user_id){
    		return view('trainer_panel/dashboard');
    	}
    	else{
    		return redirect('/')->with('er_status','Session Expired. Please Login again.');
    	}
    }
	
	
	public function changePasswordTrainer(Request $request)
	{
	
		
	  $user = $request->session()->get('trainer_user_id');
	  
	  $new_password = $request->input('new_password');
	  $current_password = $request->input('current_password');
	  
		if($user)
		{
			$trainer = Trainer::where('_id', '=',  $user)->first();
			
			if(Hash::check($current_password, $trainer['password'])){
				
				$trainer->password= Hash::make($new_password);
			    $trainer->save();
				 
				return redirect('trainer/training_list')->with('su_status', "Password has been changed successfully");
				//echo "password has been changed successfully";
			}
			
			else
			{
				return redirect('trainer/training_list')->with('er_status', "Incorrect current password");
				//echo "password does not match";
			}
			
		}
		
	}

    public function logout(Request $request){
    	$request->session()->forget('trainer_user_id');
    	return redirect('/trainer/login');
    }

    public function training_list(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $trainings = Workout::where(['page_for'=>'running'])->where(['type'=>'workout'])->where(['user_id'=>$user_id])->orderBy('_id','desc')->get();
            if($trainings){
                return view('trainer_panel/training/list',['trainings' => $trainings]);
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }
	
	
	 public function tl_exercise_list(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $wl_exercises = Workout::where(['page_for'=>'running'])->where(['type'=>'exercise'])->where(['user_id'=>$user_id])->orderBy('_id','desc')->get();
            
			if($wl_exercises){
                return view('trainer_panel/training/e_list',['wl_exercises' => $wl_exercises]);
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }
	
	 public function profile_exercise_running(Request $request, $id){
		
       $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $r_exercises = Workout::where(['_id'=>$id])->first();  
			
			if($r_exercises){
                return view('trainer_panel/training/e_profile',['wl_exercises' => $r_exercises]);
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }
	
	 public function delete_exercise_running(Request $request, $id){
		
       $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $r_exercises = Workout::where(['_id'=>$id])->delete();  
			
			return redirect('/trainer/tl_exercise_list');
			
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }
	
	 public function edit_exercise_running(Request $request, $id){
		
       $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $r_exercises = Workout::where(['_id'=>$id])->first();  
			
			  return view('trainer_panel/training/edit_e',['r_exercises' => $r_exercises]);

        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }
	
	    public function update_exercise_running(Request $request){
		
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $messages = [
                'title.required' => 'Title is required.',
                'description.required' => 'Description is required.',
                'activity.required' => 'Actitvity is required.',
            ];

            $validator = Validator::make($request->all(), [
               
                'title' => 'required',
                'description' => 'required',
                'activity' => 'required',
               
            ], $messages);
      
            if ($validator->fails()) {
                  return redirect()->back()->withErrors($validator)->withInput();
            } else {
				$training_id = $request->input('id_name');
				$exercise_image = "";
                if($request->hasFile('image')) {
                    $image = $request->file('image');
                    $exercise_image = time().'.'.$image->getClientOriginalExtension();
                    $destinationPath = public_path('/images');
                    $image->move($destinationPath, $exercise_image);
                }
				else{
					 $trainerdetails = Workout::where(['_id'=>$training_id])->select('image')->first();
                    $exercise_image = $trainerdetails['image'];
					
					
				}
                $updateData = [
                    'title' => $request->input('title'),
					'day' => $request->input('day'),
                    'description' => $request->input('description'),
                    'activity' => $request->input('activity'),
					 'equipment_name' => $request->input('equipment_name'),
                    'type' => "exercise",
					'page_for' => "running",
                    'image' => $exercise_image,
                    "rating"=>"",
                    'created_at' => date('Y-m-d h:i:s')
                ];
				
				$training_id = $request->input('id_name');
                $update =  Workout::where(['_id'=>$training_id])->update($updateData);

                if ($update) {
                     return redirect('trainer/tl_exercise_list')->with('su_status', 'Exercise updated Sucessfully!');
                } else {
                    return redirect('trainer/training_list')->with('er_status', 'Something Went Wrong Please Check Your Inputs!');
                }
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }
	
	

    public function wl_exercise_list(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $wl_exercises = Workout::where(['page_for'=>'weightlifting'])->where(['type'=>'exercise'])->where(['user_id'=>$user_id])->orderBy('_id','desc')->get();
            if($wl_exercises){
                return view('trainer_panel/weightlifting/e_list',['wl_exercises' => $wl_exercises]);
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }
	

    public function wl_workout_list(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $wl_workouts = Workout::where(['page_for'=>'weightlifting'])->where(['type'=>'workout'])->where(['user_id'=>$user_id])->orderBy('_id','desc')->get();
			// echo "<pre>";
			// print_r($wl_workouts);
			// die();
            if($wl_workouts){
                return view('trainer_panel/weightlifting/w_list',['wl_workouts' => $wl_workouts]);
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function yoga_exercise_list(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $yoga_exercises = Workout::where(['page_for'=>'yoga'])->where(['type'=>'exercise'])->where(['user_id'=>$user_id])->orderBy('_id','desc')->get();
            if($yoga_exercises){
                return view('trainer_panel/yoga/e_list',['yoga_exercises' => $yoga_exercises]);
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function yoga_workout_list(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $yoga_workouts = Workout::where(['page_for'=>'yoga'])->where(['type'=>'workout'])->where(['user_id'=>$user_id])->orderBy('_id','desc')->get();
            if($yoga_workouts){
                return view('trainer_panel/yoga/w_list',['yoga_workouts' => $yoga_workouts]);
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function add_training(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
			
			$workout= Workout::where('user_id', '=', $user_id)->where('type', '=', 'workout')->get();
			$workout_exercise= Workout::where('user_id', '=', $user_id)->where('type', '=', 'exercise')->get();

            //return view('trainer_panel/training/add')->with('workout', $workout);
			return view('trainer_panel/training/add', ['workout'=>$workout, 'workout_exercise'=>$workout_exercise]);
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function add_training_action(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $messages = [
                'title.required' => 'Title is required.',
                'description.required' => 'Description is required.',
                //'exercises.required' => 'Exercises field is required.',
                'time.required' => 'Time is required.',
                'image.required' => 'Image is required.',
                'level.required' => 'level is required.',
				// 'price.required' => 'price is required.',

            ];

            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'description' => 'required',
               // 'exercises' => 'required',
                'time' => 'required',
                'image' => 'required',
                'level' => 'required',
				//'price' => 'required',

            ], $messages);
      
            if ($validator->fails()) {
                  return redirect()->back()->withErrors($validator)->withInput();
            } else {
                $training_image = "";
                if($request->hasFile('image')) {
                    $image = $request->file('image');
                    $training_image = time().'.'.$image->getClientOriginalExtension();
                    $destinationPath = public_path('/images');
                    $image->move($destinationPath, $training_image);
                }
				                
				 $link_workout =  Workout::where('user_id', '=', $user_id)->where('type', '=', 'workout')->where('title','=', $request->input('link_workout'))->first();
				 $new_amount = $request->input('amount');
				 $link_id='';
				 if(!empty($link_workout))
				 {
					 $link_id=$link_workout->_id;
					 $link_workout_amount= $link_workout->amount;
					 $total_workout_amount= $link_workout_amount + $new_amount;
				 }
				 
                $insertData = [
                    'user_id' => $user_id,
                    'title' => $request->input('title'),
                    'description' => $request->input('description'),
                    'type' => "workout",
                    'page_for' => "running",
                    'image' => $training_image,
                    'exercises'=>$request->input('exercises')?$request->input('exercises'):'',
                    'time'=>$request->input('time'),
					 'period'=>$request->input('period'),
                    'link_workout'=>$link_id,
                    'amount'=> $link_workout?$total_workout_amount:$new_amount,
                    'level'=>$request->input('level'),
					'approval'=>$request->input('approval'),
					'isEdit'=>0,

                    "rating"=>"",
                    'created_at' => date('Y-m-d h:i:s')
                ];
   
                $insert =  Workout::insertGetId($insertData);
				
                if ($insert) {
					
                     return redirect('trainer/training_list')->with('su_status', 'Training Added Sucessfully!');
                } else {
                    return redirect('trainer/add_training')->with('er_status', 'Something Went Wrong Please Check Your Inputs!');
                }
            }
        } else {
            return redirect('/')->with('er_status', 'Your Session is Expired Please Login Again');
        }
    }
	
	public function delete_workout_exercise(Request $request, $id)
	{
	   $workout_exercise_delete= Workout::where('_id', '=', $id)->where('type', '=', 'exercise')->delete();
	    return redirect('trainer/add_training');
	 
	}
	
	public function delete_workout_exercise_yoga(Request $request, $id)
	{
	   $workout_exercise_delete= Workout::where('_id', '=', $id)->where('type', '=', 'exercise')->where('page_for', '=', 'yoga')->delete();
	    return redirect('trainer/add_yoga_workout');
	 
	}
	public function delete_diet_recipe(Request $request, $id)
	{
	   $workout_exercise_delete= DietDetail::where('_id', '=', $id)->delete();
	    return redirect('trainer/add_diet');
	 
	}
	
	
    public function training_profile(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $training = Workout::where(['_id'=>$id])->first();
            if($training){
				         
                $t_workouts = TrainingDetail::where(['training_id'=>$training['_id']])->get();
				
                return view('trainer_panel/training/profile',['training' => $training, 't_workouts'=>$t_workouts]);
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function add_training_workout(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            return view('trainer_panel/training/add_workout', ['id'=>$id]);
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function add_training_workout_action(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $messages = [
                'day.required' => 'Day is required.',
                'title.required' => 'Title is required.',
                'subtitle.required' => 'Subtitle is required.',
                'miles.required' => 'Miles field is required.',
                'time.required' => 'Time is required.',
                'image.required' => 'Image is required.',
            ];

            $validator = Validator::make($request->all(), [
                'day' => 'required',
                'title' => 'required',
                'subtitle' => 'required',
                'miles' => 'required',
                'time' => 'required',
                'image' => 'required',
            ], $messages);
      
            if ($validator->fails()) {
                  return redirect()->back()->withErrors($validator)->withInput();
            } else {
                $training_workout_image = "";
                if($request->hasFile('image')) {
                    $image = $request->file('image');
                    $training_workout_image = time().'.'.$image->getClientOriginalExtension();
                    $destinationPath = public_path('/images/image');
                    $image->move($destinationPath, $training_workout_image);
                }
                $insertData = [
                    'training_id' => $request->input('training_id'),
                    'day' => $request->input('day'),
                    'title' => $request->input('title'),
                    'image' => $training_workout_image,
                    'subtitle' => $request->input('subtitle'),
                    'time'=>$request->input('time'),
                    'miles'=>$request->input('miles'),
                    'created_at' => date('Y-m-d h:i:s')
                ];
   
                $insert =  TrainingDetail::insertGetId($insertData);

                if ($insert) {
                     return redirect('trainer/training_list')->with('su_status', 'Training Workout Added Sucessfully!');
                } else {
                    return redirect('trainer/training_list')->with('er_status', 'Something Went Wrong Please Check Your Inputs!');
                }
            }
        } else {
            return redirect('/')->with('er_status', 'Your Session is Expired Please Login Again');
        }
    }

    public function training_delete(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $training = Workout::where(['_id'=>$id])->delete();
            if($training){
                return redirect('trainer/training_list')->with('su_status','Training Deleted Successfully');
            }
            else{
                return redirect('trainer/training_list')->with('er_status','Training Not Found');
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function training_worouts_delete(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $training_workout = TrainingDetail::where(['_id'=>$id])->delete();
            if($training_workout){
                return redirect('trainer/training_list')->with('su_status','Training Workout Deleted Successfully');
            }
            else{
                return redirect('trainer/training_list')->with('er_status','Training Workout Not Found');
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function training_edit(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $training = Workout::where(['_id'=>$id])->first();
            if($training){
                return view('trainer_panel/training/edit',['training'=>$training]);
            }
            else{
                return redirect('trainer/training_list')->with('er_status','Training Not Found');
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function training_update(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $messages = [
                'title.required' => 'Title is required.',
                'description.required' => 'Description is required.',
                //'exercises.required' => 'Exercises field is required.',
                'time.required' => 'Time is required.',
            ];

            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'description' => 'required',
                //'exercises' => 'required',
                'time' => 'required',
            ], $messages);
      
            if ($validator->fails()) {
                  return redirect()->back()->withErrors($validator)->withInput();
            } else {
                $training_id = $request->input('training_id');
                $training_image = "";
                if($request->hasFile('image')) {
                    $image = $request->file('image');
                    $training_image = time().'.'.$image->getClientOriginalExtension();
                    $destinationPath = public_path('/images');
                    $image->move($destinationPath, $training_image);
                }
                else{
                    $trainerdetails = Workout::where(['_id'=>$training_id])->select('image')->first();
                    $training_image = $trainerdetails['image'];
                }
                $updateData = [
                    'title' => $request->input('title'),
                    'description' => $request->input('description'),
                    'image' => $training_image,
                    'exercises'=>$request->input('exercises'),
                    'time'=>$request->input('time'),
                    'updated_at'=> date('d-m-y h:i:s')
                ];
   
                $update =  Workout::where(['_id'=>$training_id])->update($updateData);

                if ($update) {
                     return redirect('trainer/training_list')->with('su_status', 'workout updated Sucessfully!');
                } else {
                    return redirect('trainer/training_list')->with('er_status', 'Something Went Wrong Please Check Your Inputs!');
                }
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function training_worouts_edit(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $training_workout = TrainingDetail::where(['_id'=>$id])->first();
            if($training_workout){
                return view('trainer_panel/training/edit_workout',['training_workout'=>$training_workout]);
            }
            else{
                return redirect('trainer/training_list')->with('er_status','Training Workout Not Found');
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function training_worouts_update(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $messages = [
                'day.required' => 'Day is required.',
                'title.required' => 'Title is required.',
                'subtitle.required' => 'Subtitle is required.',
                'miles.required' => 'Miles field is required.',
                'time.required' => 'Time is required.',
            ];

            $validator = Validator::make($request->all(), [
                'day' => 'required',
                'title' => 'required',
                'subtitle' => 'required',
                'miles' => 'required',
                'time' => 'required',
            ], $messages);
      
            if ($validator->fails()) {
                  return redirect()->back()->withErrors($validator)->withInput();
            } else {
                $trainingWorkout_id = $request->input('trainingWorkout_id');
                $training_workout_image = "";
                if($request->hasFile('image')) {
                    $image = $request->file('image');
                    $training_workout_image = time().'.'.$image->getClientOriginalExtension();
                    $destinationPath = public_path('/images/image');
                    $image->move($destinationPath, $training_workout_image);
                }
                else{
                    $trainerdetails = TrainingDetail::where(['_id'=>$trainingWorkout_id])->select('image')->first();
                    $training_workout_image = $trainerdetails['image'];
                }
                $updateData = [
                    'day' => $request->input('day'),
                    'title' => $request->input('title'),
                    'image' => $training_workout_image,
                    'subtitle' => $request->input('subtitle'),
                    'time'=>$request->input('time'),
                    'miles'=>$request->input('miles'),
                    'updated_at'=> date('d-m-y h:i:s')
                ];
   
                $update =  TrainingDetail::where(['_id'=>$trainingWorkout_id])->update($updateData);

                if ($update) {
                     return redirect('trainer/training_list')->with('su_status', 'Training Workout updated Sucessfully!');
                } else {
                    return redirect('trainer/training_list')->with('er_status', 'Something Went Wrong Please Check Your Inputs!');
                }
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function diet_list(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $diets = Diet::where(['user_id'=>$user_id])->orderBy('_id','desc')->get();
			
			// foreach( $diets as  $diet)
			// {
			// $workout = Workout::where('_id', '=', $diet['workout_id'])->first();
			
			// }
			
            // if($diets){
                // return view('trainer_panel/diet/list',['diets' => $diets, 'workout'=>$workout]);
            // }
			
			 if($diets){
                return view('trainer_panel/diet/list',['diets' => $diets]);
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function add_diet(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
			
			$data = array();
            $workout = Workout::where(['type'=>'workout'])->where('user_id', '=',  $user_id)->select('_id','title')->get();

			foreach($workout as  $workouts)
			{
				$diet = Diet::where(['workout_id'=>$workouts['_id']])->first();
				
					if(empty($diet))
					{
						array_push($data, $workouts);
						
					}
			}
			$recipe_name = DietDetail::where('user_id', '=', $user_id)->get();
			
            return view('trainer_panel/diet/add',['workout'=>$data, 'recipe_name'=>$recipe_name]);
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function add_recipe(Request $request){
        //print_r("Ss");die();
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            return view('trainer_panel/diet/add_recipe');
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function add_recipe_action(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $messages = [
                'recipe_name.required' => 'Recipe Name is required.',
                'activity_name.required' => 'activity name is required.',
                'serving_size.required' => 'serving size field is required.',
                'ingredients.required' => 'Ingredients is required.',
            ];

            $validator = Validator::make($request->all(), [
                'recipe_name' => 'required',
                'activity_name' => 'required',
                'serving_size' => 'required',
                'ingredients' => 'required',
            ], $messages);
      
            if ($validator->fails()) {
                  return redirect()->back()->withErrors($validator)->withInput();
            } else {
                $insertData = [
                    'user_id' => $user_id,
                    'recipe_name' => $request->input('recipe_name'),
                    'activity_name' => $request->input('activity_name'),
                    'serving_size' => $request->input('serving_size'),
                    'ingredients' => $request->input('ingredients'),
                    'created_at' => date('Y-m-d h:i:s')
                ];
   
                $insert =  Recipe::insertGetId($insertData);

                if ($insert) {
                     return redirect('trainer/diet_list')->with('su_status', 'Recipe Added Sucessfully!');
                } else {
                    return redirect('trainer/diet/add_recipe')->with('er_status', 'Something Went Wrong Please Check Your Inputs!');
                }
            }
        } else {
            return redirect('/')->with('er_status', 'Your Session is Expired Please Login Again');
        }
    }

    public function add_diet_action(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $messages = [
                'name.required' => 'Name is required.',
                'description.required' => 'Description is required.',
                'period.required' => 'Period field is required.',
               // 'amount.required' => 'Amount is required.',
                'image.required' => 'Image is required.',
                'workout.required' => 'Workout is required.',
                'price.required' => 'Price is required.',
				 'carb.required' => 'Carb is required.',
                'fat.required' => 'Fat is required.',
                'protein.required' => 'Protein is required.',
				 'sugar.required' => 'Sugar is required.',
			];

            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'description' => 'required',
                'period' => 'required',
               // 'amount' => 'required',
                'image' => 'required',
                'workout' => 'required',
                'price' => 'required',
				'carb' => 'required',
                'fat' => 'required',
			    'protein' => 'required',
				'sugar' => 'required',
			], $messages);
      
            if ($validator->fails()) {
                  return redirect()->back()->withErrors($validator)->withInput();
            } else if($request->input('period')>25)
				return redirect()->back()->with('period', 'Period must be between 1 and 25')->withInput();
			else {
                $diet_image = "";
                if($request->hasFile('image')) {
                    $image = $request->file('image');
                    $diet_image = time().'.'.$image->getClientOriginalExtension();
                    $destinationPath = public_path('/images/diet');
                    $image->move($destinationPath, $diet_image);
                }
				
                /*if ($request->input('price') == '0') {
                    $price = 'free';
                }
                else{
                    $price = $request->input('other');
                } */
					$insertData = [
                    'user_id' => $user_id,
                    'name' => $request->input('name'),
                    'description' => $request->input('description'),
                    'period' => $request->input('period'),
                    'amount' => $request->input('amount'),
                    'workout_id' => $request->input('workout'),
                    'carb' => $request->input('carb'),
                    'fat' => $request->input('fat'),
                    'protein' => $request->input('protein'),
                    'sugar' => $request->input('sugar'),
					'grocery_list'=>$request->input('field_name'),
					'recipe_names'=>$request->input('recipe_names'),
                    'price' => $request->input('other'),
                    'image' => $diet_image,
                    "rating"=>"0",
                    'status' => "0",
                    'created_at' => date('Y-m-d h:i:s')
                ];
   
                $insert =  Diet::insertGetId($insertData);

                if ($insert) {
                     return redirect('trainer/diet_list')->with('su_status', 'Diet Added Sucessfully!');
                } else {
                    return redirect('trainer/add_diet')->with('er_status', 'Something Went Wrong Please Check Your Inputs!');
                }
            }
        } else {
            return redirect('/')->with('er_status', 'Your Session is Expired Please Login Again');
        }
    }
	
	public function search_training_exercise(Request $request, $name)
	{
		
	  $user_exist = Workout::where('title', 'like', $name.'%')->get()->toarray();
	  
		return view('trainer_panel/training/search_training_exercise_view', ['user_exist'=>$user_exist]);
		
	}
	
	 public function add_Training_exercise(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
           // $categories = WlCategory::get();
		   //$categories = Category::get();
            return view('trainer_panel/training/add_e');
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }
	
	
    public function add_tranning_exercise_action(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $messages = [
                'title.required' => 'Title is required.',
                'description.required' => 'Description is required.',
                'activity.required' => 'Actitvity is required.',
                'distance.required' => 'distance is required.',
                'unit.required' => 'unit is required.',
               // 'exercises.required' => 'Exercises field is required.',
               // 'time.required' => 'Time is required.',
                'image.required' => 'Image is required.',
            ];

            $validator = Validator::make($request->all(), [
               
                'title' => 'required',
                'description' => 'required',
                'activity' => 'required',
                'distance' => 'required',
                'unit' => 'required',
                //'exercises' => 'required',
                //'time' => 'required',
                'image' => 'required',
            ], $messages);
      
            if ($validator->fails()) {
                  return redirect()->back()->withErrors($validator)->withInput();
            } else {
				
                $exercise_image = "";
                if($request->hasFile('image')) {
                    $image = $request->file('image');
                    $exercise_image = time().'.'.$image->getClientOriginalExtension();
                    $destinationPath = public_path('/images');
                    $image->move($destinationPath, $exercise_image);
                }
                $insertData = [
                    'user_id' => $user_id,
                    'title' => $request->input('title'),
					'day' => $request->input('day'),
                    'description' => $request->input('description'),
                    'activity' => $request->input('activity'),
                    'distance' => $request->input('distance').$request->input('unit'),
                    'in' => $request->input('time') .$request->input('pace'),
					 'equipment_name' => $request->input('equipment_name'),
					 'mylist' => $request->input('mylist'),
					 'myrecipe' => $request->input('myrecipe'),
					 'approval' => $request->input('approval'),
                    'type' => "exercise",
					'page_for' => "running",
                    'image' => $exercise_image,
                    //'exercises'=>$request->input('exercises'),
                    //'time'=>$request->input('time'),
					
                    "rating"=>"",
                    'created_at' => date('Y-m-d h:i:s')
                ];
   
                $insert =  Workout::insertGetId($insertData);

                if ($insert) {
					return redirect('trainer/add_training')->with('su_status', 'Exercise Added Sucessfully!');
                    // return redirect('trainer/tl_exercise_list')->with('su_status', 'Exercise Added Sucessfully!');
                } else {
                    return redirect('trainer/add_training_exercise')->with('er_status', 'Something Went Wrong Please Check Your Inputs!');
                }
            }
        } else {
            return redirect('/')->with('er_status', 'Your Session is Expired Please Login Again');
        }
    }
	
	
    public function add_weightlift_exercise(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
           // $categories = WlCategory::get();
		  // $categories = Category::get();
            return view('trainer_panel/weightlifting/add_e');
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function add_weightlift_exercise_action(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $messages = [
                'title.required' => 'Title is required.',
                'description.required' => 'Description is required.',
               // 'exercises.required' => 'Exercises field is required.',
               // 'time.required' => 'Time is required.',
                'image.required' => 'Image is required.',
                'activity.required' => 'activity is required.',
            ];

            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'description' => 'required',
                //'exercises' => 'required',
                //'time' => 'required',
                'image' => 'required',
                'activity' => 'required',
            ], $messages);
      
            if ($validator->fails()) {
                  return redirect()->back()->withErrors($validator)->withInput();
            } else {
                $exercise_image = "";
                if($request->hasFile('image')) {
                    $image = $request->file('image');
                    $exercise_image = time().'.'.$image->getClientOriginalExtension();
                    $destinationPath = public_path('/images');
                    $image->move($destinationPath, $exercise_image);
                }
                $insertData = [
                    'user_id' => $user_id,
                    'title' => $request->input('title'),
                    'description' => $request->input('description'),
					'activity' => $request->input('activity'),
					'approval' => $request->input('approval'),
					'time' => $request->input('time'),
                    'type' => "exercise",
					'day' => $request->input('day'),
                    'page_for' => "weightlifting",
                    'image' => $exercise_image,
                    'mylist'=>$request->input('mylist'),
                    'myexercise'=>$request->input('myexercise'),
                    //'time'=>$request->input('time'),
                    "rating"=>"",
					'set1_reps'=>$request->input('set1_reps'),
					'set1_rest'=>$request->input('set1_rest'),
					'set1_reps_percentage'=>$request->input('set1_reps_percentage'),
					'set1_tips'=>$request->input('set1_tips'),
					'set1_equipment'=>$request->input('set1_equipment'),
					'set1_superset'=>$request->input('set1_superset'),
					'set1_superset_reps'=>$request->input('set1_superset_reps'),
					'set2_reps'=>$request->input('set2_reps'),
					'set2_rest'=>$request->input('set2_rest'),
					'set2_reps_percentage'=>$request->input('set2_reps_percentage'),
					'set2_tips'=>$request->input('set2_tips'),
					'set2_equipment'=>$request->input('set2_equipment'),
					'set2_superset'=>$request->input('set2_superset'),
					'set2_superset_reps'=>$request->input('set2_superset_reps'),
					
					'set3_reps'=>$request->input('set3_reps'),
					'set3_rest'=>$request->input('set3_rest'),
					'set3_reps_percentage'=>$request->input('set3_reps_percentage'),
					'set3_tips'=>$request->input('set3_tips'),
					'set3_equipment'=>$request->input('set3_equipment'),
					'set3_superset'=>$request->input('set3_superset'),
					'set3_superset_reps'=>$request->input('set3_superset_reps'),
                    'created_at' => date('Y-m-d h:i:s')
                ];
   
                $insert =  Workout::insertGetId($insertData);

                if ($insert) {
                     return redirect('trainer/wl_exercise_list')->with('su_status', 'Exercise Added Sucessfully!');
                } else {
                    return redirect('trainer/add_weightlift_exercise')->with('er_status', 'Something Went Wrong Please Check Your Inputs!');
                }
            }
        } else {
            return redirect('/')->with('er_status', 'Your Session is Expired Please Login Again');
        }
    }
    public function add_weightlift_workout(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
			$workout = Workout::where('user_id', '=', $user_id)->where('type', '=', 'workout')->get();
		    $workout_exercise = Workout::where('user_id', '=', $user_id)->where('page_for', '=', 'weightlifting')->where('type', '=', 'exercise')->get();
            //return view('trainer_panel/weightlifting/add_w')->with('workout', $workout);
			 return view('trainer_panel/weightlifting/add_w', ['workout'=>$workout, 'workout_exercise'=>$workout_exercise]);
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function add_weightlift_workout_action(Request $request){
		
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $messages = [
                'title.required' => 'Title is required.',
                'description.required' => 'Description is required.',
                'time.required' => 'Time is required.',
                'period.required' => 'period is required.',
                'image.required' => 'Image is required.',
                'level.required' => 'level is required.',
            ];

            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'description' => 'required',
                'time' => 'required',
                'period' => 'required',
                'image' => 'required',
                'level' => 'required',
            ], $messages);
      
            if ($validator->fails()) {
                  return redirect()->back()->withErrors($validator)->withInput();
            } else {
                $workout_image = "";
                if($request->hasFile('image')) {
                    $image = $request->file('image');
                    $workout_image = time().'.'.$image->getClientOriginalExtension();
                    $destinationPath = public_path('/images');
                    $image->move($destinationPath, $workout_image);
                }
				
				 $link_workout =  Workout::where('user_id', '=', $user_id)->where('type', '=', 'workout')->where('title','=', $request->input('link_workout'))->first();
				 $new_amount = $request->input('amount');
				 $link_id='';
				 if(!empty($link_workout))
				 {
					 $link_id=$link_workout->_id;
					 $link_workout_amount= $link_workout->amount;
					 $total_workout_amount= $link_workout_amount + $new_amount;
				 }
				 
                $insertData = [
                    'user_id' => $user_id,
                    'title' => $request->input('title'),
                    'description' => $request->input('description'),
                    'type' => "workout",
                    'page_for' => "weightlifting",
                    'image' => $workout_image,
                    'exercises'=>$request->input('exercises')?$request->input('exercises'):'',
                    'time'=>$request->input('time'),
                    'level'=>$request->input('level'),
                    'link_workout'=>$link_id,
                    //'wl_title'=>$request->input('wl_title'),
                    //wl_title'=>$request->input('wl_title'),
                    'period'=>$request->input('period'),
					'approval'=>"pending",
					'amount'=> $link_workout?$total_workout_amount:$new_amount,

                    "rating"=>"",
                    'created_at' => date('Y-m-d h:i:s')
                ];
   
                $insert =  Workout::insertGetId($insertData);

                if ($insert) {
                     return redirect('trainer/wl_workout_list')->with('su_status', 'Workout Added Sucessfully!');
                } else {
                    return redirect('trainer/add_weightlift_workout')->with('er_status', 'Something Went Wrong Please Check Your Inputs!');
                }
            }
        } else {
            return redirect('/')->with('er_status', 'Your Session is Expired Please Login Again');
        }
    }

    public function add_yoga_exercise(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $categories = Category::get();
            return view('trainer_panel/yoga/add_e', ['categories'=>$categories]);
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function add_yoga_exercise_action(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $messages = [
               // 'category.required' => 'Category is required.',
                'title.required' => 'Title is required.',
                'description.required' => 'Description is required.',
                //'exercises.required' => 'Exercises field is required.',
                'image.required' => 'Image is required.',
            ];

            $validator = Validator::make($request->all(), [
                //'category' => 'required',
                'title' => 'required',
                'description' => 'required',
                //'exercises' => 'required',
                'image' => 'required',
            ], $messages);
      
            if ($validator->fails()) {
                  return redirect()->back()->withErrors($validator)->withInput();
            } else {
                $exercise_image = "";
                if($request->hasFile('image')) {
                    $image = $request->file('image');
                    $exercise_image = time().'.'.$image->getClientOriginalExtension();
                    $destinationPath = public_path('/images');
                    $image->move($destinationPath, $exercise_image);
                }
                $insertData = [
                    'user_id' => $user_id,
                    'title' => $request->input('title'),
                    'description' => $request->input('description'),
                    'tips' => $request->input('tips'),
                    'level' => $request->input('level'),
                    'type' => "exercise",
                    'page_for' => "yoga",
                    'image' => $exercise_image,
					'equipment_name' => $request->input('equipment_name'),
                    'created_at' => date('Y-m-d h:i:s')
                ];
   
                $insert =  Workout::insertGetId($insertData);

                if ($insert) {
                     return redirect('trainer/yoga_exercise_list')->with('su_status', 'Exercise Added Sucessfully!');
                } else {
                    return redirect('trainer/add_yoga_exercise')->with('er_status', 'Something Went Wrong Please Check Your Inputs!');
                }
            }
        } else {
            return redirect('/')->with('er_status', 'Your Session is Expired Please Login Again');
        }
    }

    public function add_yoga_workout(Request $request, $id=null){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
			
			if(empty($id))
			{
			
			$workout= Workout::where('user_id', '=', $user_id)->where('type', '=', 'workout')->get();
			$workout_exercise = Workout::where('user_id', '=', $user_id)->where('page_for', '=', 'yoga')->where('type', '=', 'exercise')->get();
            return view('trainer_panel/yoga/add_w', ['workout'=>$workout, 'workout_exercise'=>$workout_exercise]);    
			//return view('trainer_panel/yoga/add_w')->with('workout', $workout);
			}
			else
			{
				$WorkoutDetail=WorkoutDetail::where('_id', '=',$id)->first();
				
				$workout= Workout::where('user_id', '=', $user_id)->where('type', '=', 'workout')->get();
				$workout_exercise = Workout::where('user_id', '=', $user_id)->where('page_for', '=', 'yoga')->where('type', '=', 'exercise')->get();
				return view('trainer_panel/yoga/add_w', ['workout'=>$workout, 'workout_exercise'=>$workout_exercise,'WorkoutDetail'=>$WorkoutDetail]); 
				
			}
			
			
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }
	

    public function add_yoga_workout_action(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $messages = [
                'title.required' => 'Title is required.',
                'description.required' => 'Description is required.',
                //'exercises.required' => 'Exercises field is required.',
                'time.required' => 'Time is required.',
                'period.required' => 'Period is required.',
                'image.required' => 'Image is required.',
                'level.required' => 'level is required.',
            ];

            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'description' => 'required',
               // 'exercises' => 'required',
                'time' => 'required',
                'period' => 'required',
                'image' => 'required',
                'level' => 'required',
            ], $messages);
      
            if ($validator->fails()) {
                  return redirect()->back()->withErrors($validator)->withInput();
            } else {
                $workout_image = "";
                if($request->hasFile('image')) {
                    $image = $request->file('image');
                    $workout_image = time().'.'.$image->getClientOriginalExtension();
                    $destinationPath = public_path('/images');
                    $image->move($destinationPath, $workout_image);
                }
			    $link_workout =  Workout::where('user_id', '=', $user_id)->where('type', '=', 'workout')->where('title','=', $request->input('link_workout'))->first();
				$new_amount = $request->input('amount');
				$link_id='';
				 if(!empty($link_workout))
				 {
					 $link_id=$link_workout->_id;
					 $link_workout_amount= $link_workout->amount;
					 $total_workout_amount= $link_workout_amount + $new_amount;
				 }			   
                $insertData = [
                    'user_id' => $user_id,
                    'title' => $request->input('title'),
                    'description' => $request->input('description'),
                    'type' => "workout",
                    'page_for' => "yoga",
                    'image' => $workout_image,
                    'exercises'=>$request->input('exercises')?$request->input('exercises'):'',
                    'approval'=>$request->input('approval'),
                    'time'=>$request->input('time'),
                    'period'=>$request->input('period'),
                    'level'=>$request->input('level'),
                    'link_workout'=>$link_id,
                    //'amount'=>$request->input('amount'),
                   	'amount'=> $link_workout?$total_workout_amount:$new_amount,
                    "rating"=>"",
                    'created_at' => date('Y-m-d h:i:s')
                ];
                $insert =  Workout::insertGetId($insertData);
                if ($insert) {
                     return redirect('trainer/yoga_workout_list')->with('su_status', 'Workout Added Sucessfully!');
                } else {
                    return redirect('trainer/add_yoga_workout')->with('er_status', 'Something Went Wrong Please Check Your Inputs!');
                }
            }
        } else {
            return redirect('/')->with('er_status', 'Your Session is Expired Please Login Again');
        }
    }

    public function wl_exercise_delete(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $wl_exercise = Workout::where(['_id'=>$id])->delete();
            if($wl_exercise){
                return redirect('trainer/wl_exercise_list')->with('su_status','Weightlifting Exercise Deleted Successfully');
            }
            else{
                return redirect('trainer/wl_exercise_list')->with('er_status','Weightlifting Exercise Not Found');
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function wl_exercise_edit(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $wl_exercise = Workout::where(['_id'=>$id])->first();
            if($wl_exercise){
               // $categories = WlCategory::get();
                return view('trainer_panel/weightlifting/wl_exercise_edit',['wl_exercise'=>$wl_exercise]);
            }
            else{
                return redirect('trainer/wl_exercise_list')->with('er_status','Weightlifting Exercise Not Found');
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function wl_exercise_update(Request $request){
		
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $messages = [
                'title.required' => 'Title is required.',
                'description.required' => 'Description is required.',
               // 'exercises.required' => 'Exercises field is required.',
               // 'time.required' => 'Time is required.',
            ];

            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'description' => 'required',
                //'exercises' => 'required',
                //'time' => 'required',
            ], $messages);
      
            if ($validator->fails()) {
                  return redirect()->back()->withErrors($validator)->withInput();
            } else {
				$workout_id = $request->input('id_name');
                $exercise_image = "";
                if($request->hasFile('image')) {
                    $image = $request->file('image');
                    $exercise_image = time().'.'.$image->getClientOriginalExtension();
                    $destinationPath = public_path('/images');
                    $image->move($destinationPath, $exercise_image);
                }
                else{
                    $workoutDetail =  Workout::where(['_id'=>$workout_id])->select('image')->first();
                    $exercise_image = $workoutDetail['image'];
                }
				
				$workout_id = $request->input('id_name');
                $updateData = [
                    'title' => $request->input('title'),
                    'description' => $request->input('description'),
					'activity' => $request->input('activity'),
                    'image' => $exercise_image,
                   // 'exercises'=>$request->input('exercises'),
                    //'time'=>$request->input('time'),
                    'updated_at' => date('Y-m-d h:i:s')
                ];
   
                $update =  Workout::where(['_id'=>$workout_id])->update($updateData);

                if ($update) {
                     return redirect('trainer/wl_exercise_list')->with('su_status', 'Exercise updated Sucessfully!');
                } else {
                    return redirect('trainer/wl_exercise_list')->with('er_status', 'Something Went Wrong Please Check Your Inputs!');
                }
            }
        } else {
            return redirect('/')->with('er_status', 'Your Session is Expired Please Login Again');
        }
    }

    public function wl_workout_delete(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $wl_workout = Workout::where(['_id'=>$id])->delete();
            if($wl_workout){
                return redirect('trainer/wl_workout_list')->with('su_status','Weightlifting Workout Deleted Successfully');
            }
            else{
                return redirect('trainer/wl_workout_list')->with('er_status','Weightlifting Workout Not Found');
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function wl_workout_edit(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $wl_workout = Workout::where(['_id'=>$id])->first();
            if($wl_workout){
                return view('trainer_panel/weightlifting/wl_workout_edit',['wl_workout'=>$wl_workout]);
            }
            else{
                return redirect('trainer/wl_workout_list')->with('er_status','Weightlifting Workout Not Found');
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function wl_workout_update(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $messages = [
                'title.required' => 'Title is required.',
                'description.required' => 'Description is required.',
               // 'exercises.required' => 'Exercises field is required.',
                'time.required' => 'Time is required.',
                'period.required' => 'Period is required.',
            ];

            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'description' => 'required',
                //'exercises' => 'required',
                'time' => 'required',
                'period' => 'required',
            ], $messages);
      
            if ($validator->fails()) {
                  return redirect()->back()->withErrors($validator)->withInput();
            } else {
                $workout_image = "";
                if($request->hasFile('image')) {
                    $image = $request->file('image');
                    $workout_image = time().'.'.$image->getClientOriginalExtension();
                    $destinationPath = public_path('/images');
                    $image->move($destinationPath, $workout_image);
                }
                else{
                    $workoutDetail =  Workout::where(['_id'=>$request->input('wl_workout_id')])->select('image')->first();
                    $workout_image = $workoutDetail['image'];
                }
                $updateData = [
                    'title' => $request->input('title'),
                    'description' => $request->input('description'),
                    'image' => $workout_image,
                    'exercises'=>$request->input('exercises'),
                    'time'=>$request->input('time'),
                    'period'=>$request->input('period'),
                    'updated_at' => date('Y-m-d h:i:s')
                ];
   
                $update =  Workout::where(['_id'=>$request->input('wl_workout_id')])->update($updateData);

                if ($update) {
                     return redirect('trainer/wl_workout_list')->with('su_status', 'Workout Updated Sucessfully!');
                } else {
                    return redirect('trainer/wl_workout_list')->with('er_status', 'Something Went Wrong Please Check Your Inputs!');
                }
            }
        } else {
            return redirect('/')->with('er_status', 'Your Session is Expired Please Login Again');
        }
    }

    public function yoga_exercise_delete(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $yoga_exercise = Workout::where(['_id'=>$id])->delete();
            if($yoga_exercise){
                return redirect('trainer/yoga_exercise_list')->with('su_status','Yoga Exercise Deleted Successfully');
            }
            else{
                return redirect('trainer/yoga_exercise_list')->with('er_status','Yoga Exercise Not Found');
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function yoga_exercise_edit(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $yoga_exercise = Workout::where(['_id'=>$id])->first();
            if($yoga_exercise){
                $categories = YogaCategory::get();
                return view('trainer_panel/yoga/yoga_exercise_edit',['yoga_exercise'=>$yoga_exercise, 'categories'=>$categories]);
            }
            else{
                return redirect('trainer/yoga_exercise_list')->with('er_status','Yoga Exercise Not Found');
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function yoga_exercise_update(Request $request){
		
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $messages = [
               // 'category.required' => 'Category is required.',
                'title.required' => 'Title is required.',
                'description.required' => 'Description is required.',
                //'exercises.required' => 'Exercises field is required.',
            ];

            $validator = Validator::make($request->all(), [
                //'category' => 'required',
                'title' => 'required',
                'description' => 'required',
               // 'exercises' => 'required',
            ], $messages);
      
            if ($validator->fails()) {
                  return redirect()->back()->withErrors($validator)->withInput();
            } else {
                $exercise_image = "";
                if($request->hasFile('image')) {
                    $image = $request->file('image');
                    $exercise_image = time().'.'.$image->getClientOriginalExtension();
                    $destinationPath = public_path('/images');
                    $image->move($destinationPath, $exercise_image);
                }
                else{
                    $exerciseDetail =  Workout::where(['_id'=>$request->input('yoga_id')])->select('image')->first();
                    $exercise_image = $exerciseDetail['image'];
                }
                $updateData = [
                    'title' => $request->input('title'),
                    'description' => $request->input('description'),
                   // 'category' => $request->input('category'),
                    'image' => $exercise_image,
                   'tips'=>$request->input('tips'),
                    'updated_at' => date('Y-m-d h:i:s')
                ];
   
                $update =  Workout::where(['_id'=>$request->input('yoga_id')])->update($updateData);

                if ($update) {
                     return redirect('trainer/yoga_exercise_list')->with('su_status', 'Exercise Updated Sucessfully!');
                } else {
                    return redirect('trainer/yoga_exercise_list')->with('er_status', 'Something Went Wrong Please Check Your Inputs!');
                }
            }
        } else {
            return redirect('/')->with('er_status', 'Your Session is Expired Please Login Again');
        }
    }

    public function yoga_workout_delete(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $yoga_workout = Workout::where(['_id'=>$id])->delete();
            if($yoga_workout){
                return redirect('trainer/yoga_workout_list')->with('su_status','Yoga Workout Deleted Successfully');
            }
            else{
                return redirect('trainer/yoga_workout_list')->with('er_status','Yoga Workout Not Found');
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function yoga_workout_edit(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $yoga_workout = Workout::where(['_id'=>$id])->first();
            if($yoga_workout){
                return view('trainer_panel/yoga/yoga_workout_edit',['yoga_workout'=>$yoga_workout]);
            }
            else{
                return redirect('trainer/yoga_workout_list')->with('er_status','Yoga Exercise Not Found');
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function yoga_workout_update(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $messages = [
                'title.required' => 'Title is required.',
                'description.required' => 'Description is required.',
               // 'exercises.required' => 'Exercises field is required.',
                'time.required' => 'Time is required.',
                'period.required' => 'Period is required.',
            ];

            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'description' => 'required',
               // 'exercises' => 'required',
                'time' => 'required',
                'period' => 'required',
            ], $messages);
      
            if ($validator->fails()) {
                  return redirect()->back()->withErrors($validator)->withInput();
            } else {
                $workout_image = "";
                if($request->hasFile('image')) {
                    $image = $request->file('image');
                    $workout_image = time().'.'.$image->getClientOriginalExtension();
                    $destinationPath = public_path('/images');
                    $image->move($destinationPath, $workout_image);
                }
                else{
                    $workoutDetail =  Workout::where(['_id'=>$request->input('yoga_workout_id')])->select('image')->first();
                    $workout_image = $workoutDetail['image'];
                }
                $updateData = [
                    'title' => $request->input('title'),
                    'description' => $request->input('description'),
                    'image' => $workout_image,
                    'exercises'=>$request->input('exercises'),
                    'time'=>$request->input('time'),
                    'period'=>$request->input('period'),
                    'updated_at' => date('Y-m-d h:i:s')
                ];
   
                $update =  Workout::where(['_id'=>$request->input('yoga_workout_id')])->update($updateData);

                if ($update) {
                     return redirect('trainer/yoga_workout_list')->with('su_status', 'Workout Updated Sucessfully!');
                } else {
                    return redirect('trainer/yoga_workout_list')->with('er_status', 'Something Went Wrong Please Check Your Inputs!');
                }
            }
        } else {
            return redirect('/')->with('er_status', 'Your Session is Expired Please Login Again');
        }
    }

    public function diet_delete(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $diet = Diet::where(['_id'=>$id])->delete();
            if($diet){
                return redirect('trainer/diet_list')->with('su_status','Diet Deleted Successfully');
            }
            else{
                return redirect('trainer/diet_list')->with('er_status','Diet Not Found');
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function diet_edit(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $diet = Diet::where(['_id'=>$id])->first();
            $workoutName = Workout::where(['_id'=>$diet['workout_id']])->select('title')->first();
            //print_r($workoutName);die();
            if($diet){
                return view('trainer_panel/diet/diet_edit',['diet'=>$diet,'workoutName'=>$workoutName]);
            }
            else{
                return redirect('trainer/diet_list')->with('er_status','Diet Not Found');
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function diet_update(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $messages = [
                'name.required' => 'Name is required.',
                'description.required' => 'Description is required.',
                'period.required' => 'Period field is required.',
                'amount.required' => 'Amount is required.',
            ];

            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'description' => 'required',
                'period' => 'required',
                'amount' => 'required',
            ], $messages);
      
            if ($validator->fails()) {
                  return redirect()->back()->withErrors($validator)->withInput();
            } else {
                $diet_image = "";
                if($request->hasFile('image')) {
                    $image = $request->file('image');
                    $diet_image = time().'.'.$image->getClientOriginalExtension();
                    $destinationPath = public_path('/images/diet');
                    $image->move($destinationPath, $diet_image);
                }
                else{
                    $dietDetail =  Diet::where(['_id'=>$request->input('diet_id')])->select('image')->first();
                    $diet_image = $dietDetail['image'];
                }
                $updateData = [
                    'name' => $request->input('name'),
                    'description' => $request->input('description'),
                    'period' => $request->input('period'),
                    'amount' => $request->input('amount'),
					'carb' => $request->input('carb'),
                    'fat' => $request->input('fat'),
                    'protein' => $request->input('protein'),
                    'sugar' => $request->input('sugar'),
                    'image' => $diet_image,
                    'updated_at' => date('Y-m-d h:i:s')
                ];
   
                $update =  Diet::where(['_id'=>$request->input('diet_id')])->update($updateData);

                if ($update) {
                     return redirect('trainer/diet_list')->with('su_status', 'Diet Updated Sucessfully!');
                } else {
                    return redirect('trainer/diet_list')->with('er_status', 'Something Went Wrong Please Check Your Inputs!');
                }
            }
        } else {
            return redirect('/')->with('er_status', 'Your Session is Expired Please Login Again');
        }
    }

    public function wl_exercise_profile(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $wl_exercise = Workout::where(['_id'=>$id])->first();
            if($wl_exercise){
                $wl_exercise_details = WorkoutDetail::where(['workout_id'=>$wl_exercise['_id']])->get();
                return view('trainer_panel/weightlifting/wl_exercise_profile',['wl_exercise' => $wl_exercise, 'wl_exercise_details'=>$wl_exercise_details]);
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function wl_workout_profile(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $wl_workout = Workout::where(['_id'=>$id])->first();
            if($wl_workout){
                $wl_workout_details = WorkoutDetail::where(['workout_id'=>$wl_workout['_id']])->get();
                return view('trainer_panel/weightlifting/wl_workout_profile',['wl_workout' => $wl_workout, 'wl_workout_details'=>$wl_workout_details]);
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function yoga_exercise_profile(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $yoga_exercise = Workout::where(['_id'=>$id])->first();
            if($yoga_exercise){
                $yoga_exercise_details = WorkoutDetail::where(['workout_id'=>$yoga_exercise['_id']])->get();
                return view('trainer_panel/yoga/yoga_exercise_profile',['yoga_exercise' => $yoga_exercise, 'yoga_exercise_details'=>$yoga_exercise_details]);
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function yoga_workout_profile(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $yoga_workout = Workout::where(['_id'=>$id])->first();
            if($yoga_workout){
                $yoga_workout_details = WorkoutDetail::where(['workout_id'=>$yoga_workout['_id']])->get();
                return view('trainer_panel/yoga/yoga_workout_profile',['yoga_workout' => $yoga_workout, 'yoga_workout_details'=>$yoga_workout_details]);
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function diet_profile(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $diet = Diet::where(['_id'=>$id])->first();
            if($diet){
                $diet_details = DietDetail::where(['diet_id'=>$diet['_id']])->get();
				
				$diet_details_week = DietDetailWeek::where(['diet_id'=>$diet['_id']])->get();
				
                return view('trainer_panel/diet/diet_profile',['diet' => $diet, 'diet_details'=>$diet_details, 'diet_details_week'=>$diet_details_week]);
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }
	
	
	public function displayWeekDiet(Request $request, $id, $week)
	{
		
		$user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $diet = Diet::where(['_id'=>$id])->first();
            if($diet){
				
                $diet_details = DietDetail::where(['diet_id'=>$diet['_id']])->where('week', '=', $week)->get();
				return view('trainer_panel/diet/diet_week',['diet' => $diet, 'diet_details'=>$diet_details]);

				//$diet_details_week = DietDetailWeek::where(['diet_id'=>$diet['_id']])->get();
				
                //return view('trainer_panel/diet/diet_profile',['diet' => $diet, 'diet_details'=>$diet_details, 'diet_details_week'=>$diet_details_week]);
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }

	}
	
	
	public function displayWeekWorkout(Request $request, $id, $week)
	{
		
		$user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $Workout = Workout::where(['_id'=>$id])->first();
            if($Workout){
				
                $workout_details = WorkoutDetail::where(['workout_id'=>$Workout['_id']])->where('week', '=', $week)->get();
				
				// print_r($workout_details);
				
				// die();
				
				
				return view('trainer_panel/weightlifting/wl_workout_week',['Workout' => $Workout, 'workout_details'=>$workout_details]);

				//$diet_details_week = DietDetailWeek::where(['diet_id'=>$diet['_id']])->get();
				
                //return view('trainer_panel/diet/diet_profile',['diet' => $diet, 'diet_details'=>$diet_details, 'diet_details_week'=>$diet_details_week]);
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }

	}
	
	
	
	
    public function add_wl_ex_daywise(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            return view('trainer_panel/weightlifting/add_wl_ex_daywise', ['id'=>$id]);
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function add_wl_ex_daywise_action(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $messages = [
                'day.required' => 'Day is required.',
                'title.required' => 'Title is required.',
                'daily_desc.required' => 'Description is required.',
                'reps.required' => 'Reps field is required.',
                'rest_time.required' => 'Rest Time is required.',
                'image.required' => 'Image is required.',
            ];

            $validator = Validator::make($request->all(), [
                'day' => 'required',
                'title' => 'required',
                'daily_desc' => 'required',
                'reps' => 'required',
                'rest_time' => 'required',
                'image' => 'required',
            ], $messages);
      
            if ($validator->fails()) {
                  return redirect()->back()->withErrors($validator)->withInput();
            } else {
                $image1 = "";
                if($request->hasFile('image')) {
                    $image = $request->file('image');
                    $image1 = time().'.'.$image->getClientOriginalExtension();
                    $destinationPath = public_path('/images/image');
                    $image->move($destinationPath, $image1);
                }
                $insertData = [
                    'workout_id' => $request->input('wl_excercise_id'),
                    'day' => $request->input('day'),
                    'title' => $request->input('title'),
                    'image' => $image1,
                    'daily_desc' => $request->input('daily_desc'),
                    'reps'=>$request->input('reps'),
                    'rest_time'=>$request->input('rest_time'),
                    'created_at' => date('Y-m-d h:i:s')
                ];
   
                $insert =  WorkoutDetail::insertGetId($insertData);

                if ($insert) {
                     return redirect('trainer/wl_exercise_list')->with('su_status', 'Excercise Added Sucessfully!');
                } else {
                    return redirect('trainer/wl_exercise_list')->with('er_status', 'Something Went Wrong Please Check Your Inputs!');
                }
            }
        } else {
            return redirect('/')->with('er_status', 'Your Session is Expired Please Login Again');
        }
    }

    public function wl_ex_daywise_edit(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $wl_ex_daywise_detail = WorkoutDetail::where(['_id'=>$id])->first();
            if($wl_ex_daywise_detail){
                return view('trainer_panel/weightlifting/edit_wl_ex_daywise',['wl_ex_daywise_detail'=>$wl_ex_daywise_detail]);
            }
            else{
                return redirect('trainer/wl_exercise_list')->with('er_status','Excercise Not Found');
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function wl_ex_daywise_update(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $messages = [
                'day.required' => 'Day is required.',
                'title.required' => 'Title is required.',
                'daily_desc.required' => 'Description is required.',
                'reps.required' => 'Reps field is required.',
                'rest_time.required' => 'Time is required.',
            ];

            $validator = Validator::make($request->all(), [
                'day' => 'required',
                'title' => 'required',
                'daily_desc' => 'required',
                'reps' => 'required',
                'rest_time' => 'required',
            ], $messages);
      
            if ($validator->fails()) {
                  return redirect()->back()->withErrors($validator)->withInput();
            } else {
                $wl_ex_daywise_id = $request->input('wl_ex_daywise_id');
                $image1 = "";
                if($request->hasFile('image')) {
                    $image = $request->file('image');
                    $image1 = time().'.'.$image->getClientOriginalExtension();
                    $destinationPath = public_path('/images/image');
                    $image->move($destinationPath, $image1);
                }
                else{
                    $trainerdetails = WorkoutDetail::where(['_id'=>$wl_ex_daywise_id])->select('image')->first();
                    $image1 = $trainerdetails['image'];
                }
                $updateData = [
                    'day' => $request->input('day'),
                    'title' => $request->input('title'),
                    'image' => $image1,
                    'daily_desc' => $request->input('daily_desc'),
                    'reps'=>$request->input('reps'),
                    'rest_time'=>$request->input('rest_time'),
                    'updated_at'=> date('d-m-y h:i:s')
                ];
   
                $update =  WorkoutDetail::where(['_id'=>$wl_ex_daywise_id])->update($updateData);

                if ($update) {
                     return redirect('trainer/wl_exercise_list')->with('su_status', 'Excercise updated Sucessfully!');
                } else {
                    return redirect('trainer/wl_exercise_list')->with('er_status', 'Something Went Wrong Please Check Your Inputs!');
                }
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function wl_ex_daywise_delete(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $detail = WorkoutDetail::where(['_id'=>$id])->delete();
            if($detail){
                return redirect('trainer/wl_exercise_list')->with('su_status','Exercise Deleted Successfully');
            }
            else{
                return redirect('trainer/wl_exercise_list')->with('er_status','Exercise Not Found');
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function add_wl_wo_daywise(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
			
			$workout = Workout::where('_id', '=', $id)->first();
			
			$workout_time = $workout->period;
			
            return view('trainer_panel/weightlifting/add_wl_wo_daywise', ['id'=>$id, 'workout_time'=>$workout_time]);
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function add_wl_wo_daywise_action(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $messages = [
			    'week.required' => 'Week is required.',
                'day.required' => 'Day is required.',
                'title.required' => 'Title is required.',
                'daily_desc.required' => 'Description is required.',
                'reps.required' => 'Reps field is required.',
                'rest_time.required' => 'Rest Time is required.',
                'image.required' => 'Image is required.',
            ];

            $validator = Validator::make($request->all(), [
				 'week' => 'required',
                'day' => 'required',
                'title' => 'required',
                'daily_desc' => 'required',
                'reps' => 'required',
                'rest_time' => 'required',
                'image' => 'required',
            ], $messages);
      
            if ($validator->fails()) {
                  return redirect()->back()->withErrors($validator)->withInput();
            } else {
                $image1 = "";
                if($request->hasFile('image')) {
                    $image = $request->file('image');
                    $image1 = time().'.'.$image->getClientOriginalExtension();
                    $destinationPath = public_path('/images/image');
                    $image->move($destinationPath, $image1);
                }
                $insertData = [
                    'workout_id' => $request->input('wl_workout_id'),
					 'week' => $request->input('week'),
                    'day' => $request->input('day'),
                    'title' => $request->input('title'),
                    'image' => $image1,
                    'daily_desc' => $request->input('daily_desc'),
                    'reps'=>$request->input('reps'),
                    'rest_time'=>$request->input('rest_time'),
                    'created_at' => date('Y-m-d h:i:s')
                ];
   
                $insert =  WorkoutDetail::insertGetId($insertData);

                if ($insert) {
                     return redirect('trainer/wl_workout_list')->with('su_status', 'Workout Added Sucessfully!');
                } else {
                    return redirect('trainer/wl_workout_list')->with('er_status', 'Something Went Wrong Please Check Your Inputs!');
                }
            }
        } else {
            return redirect('/')->with('er_status', 'Your Session is Expired Please Login Again');
        }
    }

    public function wl_wo_daywise_edit(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $wl_wo_daywise_detail = WorkoutDetail::where(['_id'=>$id])->first();
            if($wl_wo_daywise_detail){
                return view('trainer_panel/weightlifting/wl_wo_daywise_edit',['wl_wo_daywise_detail'=>$wl_wo_daywise_detail]);
            }
            else{
                return redirect('trainer/wl_workout_list')->with('er_status','Workout Not Found');
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function wl_wo_daywise_update(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $messages = [
                'day.required' => 'Day is required.',
                'title.required' => 'Title is required.',
                'daily_desc.required' => 'Description is required.',
                'reps.required' => 'Reps field is required.',
                'rest_time.required' => 'Time is required.',
            ];

            $validator = Validator::make($request->all(), [
                'day' => 'required',
                'title' => 'required',
                'daily_desc' => 'required',
                'reps' => 'required',
                'rest_time' => 'required',
            ], $messages);
      
            if ($validator->fails()) {
                  return redirect()->back()->withErrors($validator)->withInput();
            } else {
                $wl_wo_daywise_id = $request->input('wl_wo_daywise_id');
                $image1 = "";
                if($request->hasFile('image')) {
                    $image = $request->file('image');
                    $image1 = time().'.'.$image->getClientOriginalExtension();
                    $destinationPath = public_path('/images/image');
                    $image->move($destinationPath, $image1);
                }
                else{
                    $trainerdetails = WorkoutDetail::where(['_id'=>$wl_wo_daywise_id])->select('image')->first();
                    $image1 = $trainerdetails['image'];
                }
                $updateData = [
                    'day' => $request->input('day'),
                    'title' => $request->input('title'),
                    'image' => $image1,
                    'daily_desc' => $request->input('daily_desc'),
                    'reps'=>$request->input('reps'),
                    'rest_time'=>$request->input('rest_time'),
                    'updated_at'=> date('d-m-y h:i:s')
                ];
   
                $update =  WorkoutDetail::where(['_id'=>$wl_wo_daywise_id])->update($updateData);

                if ($update) {
                     return redirect('trainer/wl_workout_list')->with('su_status', 'Workout updated Sucessfully!');
                } else {
                    return redirect('trainer/wl_workout_list')->with('er_status', 'Something Went Wrong Please Check Your Inputs!');
                }
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function wl_wo_daywise_delete(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $detail = WorkoutDetail::where(['_id'=>$id])->delete();
            if($detail){
                return redirect('trainer/wl_workout_list')->with('su_status','Workout Deleted Successfully');
            }
            else{
                return redirect('trainer/wl_workout_list')->with('er_status','Workout Not Found');
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function add_yoga_ex_daywise(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            return view('trainer_panel/yoga/add_yoga_ex_daywise', ['id'=>$id]);
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function add_yoga_ex_daywise_action(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $messages = [
                'day.required' => 'Day is required.',
                'title.required' => 'Title is required.',
                'daily_desc.required' => 'Description is required.',
                'sets.required' => 'Sets field is required.',
                'hold_time.required' => 'Hold Time is required.',
                'image.required' => 'Image is required.',
            ];

            $validator = Validator::make($request->all(), [
                'day' => 'required',
                'title' => 'required',
                'daily_desc' => 'required',
                'sets' => 'required',
                'hold_time' => 'required',
                'image' => 'required',
            ], $messages);
      
            if ($validator->fails()) {
                  return redirect()->back()->withErrors($validator)->withInput();
            } else {
                $image1 = "";
                if($request->hasFile('image')) {
                    $image = $request->file('image');
                    $image1 = time().'.'.$image->getClientOriginalExtension();
                    $destinationPath = public_path('/images/image');
                    $image->move($destinationPath, $image1);
                }
                $insertData = [
                    'workout_id' => $request->input('yoga_excercise_id'),
                    'day' => $request->input('day'),
                    'title' => $request->input('title'),
                    'image' => $image1,
                    'daily_desc' => $request->input('daily_desc'),
                    'sets'=>$request->input('sets'),
                    'hold_time'=>$request->input('hold_time'),
                    'created_at' => date('Y-m-d h:i:s')
                ];
   
                $insert =  WorkoutDetail::insertGetId($insertData);

                if ($insert) {
                     return redirect('trainer/yoga_exercise_list')->with('su_status', 'Exercise Added Sucessfully!');
                } else {
                    return redirect('trainer/yoga_exercise_list')->with('er_status', 'Something Went Wrong Please Check Your Inputs!');
                }
            }
        } else {
            return redirect('/')->with('er_status', 'Your Session is Expired Please Login Again');
        }
    }

    public function yoga_ex_daywise_edit(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $yoga_ex_daywise_detail = WorkoutDetail::where(['_id'=>$id])->first();
            if($yoga_ex_daywise_detail){
                return view('trainer_panel/yoga/yoga_ex_daywise_edit',['yoga_ex_daywise_detail'=>$yoga_ex_daywise_detail]);
            }
            else{
                return redirect('trainer/yoga_exercise_list')->with('er_status','Workout Not Found');
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function yoga_ex_daywise_update(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $messages = [
                'day.required' => 'Day is required.',
                'title.required' => 'Title is required.',
                'daily_desc.required' => 'Description is required.',
                'sets.required' => 'sets field is required.',
                'hold_time.required' => 'Time is required.',
            ];

            $validator = Validator::make($request->all(), [
                'day' => 'required',
                'title' => 'required',
                'daily_desc' => 'required',
                'sets' => 'required',
                'hold_time' => 'required',
            ], $messages);
      
            if ($validator->fails()) {
                  return redirect()->back()->withErrors($validator)->withInput();
            } else {
                $yoga_ex_daywise_id = $request->input('yoga_ex_daywise_id');
                $image1 = "";
                if($request->hasFile('image')) {
                    $image = $request->file('image');
                    $image1 = time().'.'.$image->getClientOriginalExtension();
                    $destinationPath = public_path('/images/image');
                    $image->move($destinationPath, $image1);
                }
                else{
                    $trainerdetails = WorkoutDetail::where(['_id'=>$yoga_ex_daywise_id])->select('image')->first();
                    $image1 = $trainerdetails['image'];
                }
                $updateData = [
                    'day' => $request->input('day'),
                    'title' => $request->input('title'),
                    'image' => $image1,
                    'daily_desc' => $request->input('daily_desc'),
                    'sets'=>$request->input('sets'),
                    'hold_time'=>$request->input('hold_time'),
                    'updated_at'=> date('d-m-y h:i:s')
                ];
   
                $update =  WorkoutDetail::where(['_id'=>$yoga_ex_daywise_id])->update($updateData);

                if ($update) {
                     return redirect('trainer/yoga_exercise_list')->with('su_status', 'Exercise updated Sucessfully!');
                } else {
                    return redirect('trainer/yoga_exercise_list')->with('er_status', 'Something Went Wrong Please Check Your Inputs!');
                }
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function yoga_ex_daywise_delete(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $detail = WorkoutDetail::where(['_id'=>$id])->delete();
            if($detail){
                return redirect('trainer/yoga_exercise_list')->with('su_status','Exercise Deleted Successfully');
            }
            else{
                return redirect('trainer/yoga_exercise_list')->with('er_status','Exercise Not Found');
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }
	
  public function add_yoga_wo_day(Request $request){
	  
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
		$workout_exercise = Workout::where('user_id', '=', $user_id)->where('page_for', '=', 'yoga')->where('type', '=', 'exercise')->get();

            return view('trainer_panel/yoga/add_yog_day')->with('workout_exercise',$workout_exercise);
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }
	
    public function add_yoga_wo_daywise(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
			
            return view('trainer_panel/yoga/add_yoga_wo_daywise', ['id'=>$id]);
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function add_yoga_wo_daywise_action(Request $request){
		
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $messages = [
                //'day.required' => 'Day is required.',
                'title.required' => 'Title is required.',
                //'daily_desc.required' => 'Description is required.',
                //'sets.required' => 'sets field is required.',
                //'hold_time.required' => 'Hold Time is required.',
                //'image.required' => 'Image is required.',
            ];

            $validator = Validator::make($request->all(), [
                //'day' => 'required',
                'title' => 'required',
                //'daily_desc' => 'required',
               // 'sets' => 'required',
               // 'hold_time' => 'required',
               // 'image' => 'required',
            ], $messages);
      
            if ($validator->fails()) {
                  return redirect()->back()->withErrors($validator)->withInput();
            } else {
                // $image1 = "";
                // if($request->hasFile('image')) {
                    // $image = $request->file('image');
                    // $image1 = time().'.'.$image->getClientOriginalExtension();
                    // $destinationPath = public_path('/images/image');
                    // $image->move($destinationPath, $image1);
                // }
                $insertData = [
                    //'workout_id' => $request->input('yoga_workout_id'),
                    'exercise_id' => $request->input('link_exercise'),
                    'day' => $request->input('day'),
                    'title' => $request->input('title'),
                    //'image' => $image1,
                  //  'daily_desc' => $request->input('daily_desc'),
                    'sets'=>$request->input('sets'),
                    'activity'=>$request->input('activity'),
                    // 'hold_time'=>$request->input('hold_time'),
                    'time'=>$request->input('time'),
                    'created_at' => date('Y-m-d h:i:s')
                ];
   
                $insert =  WorkoutDetail::insertGetId($insertData);
				$id= $insert;
                if ($insert) {
                     //return redirect('trainer/yoga_workout_list')->with('su_status', 'Workout Added Sucessfully!');
                    // return redirect('trainer/add_yoga_workout/'.$d.');
					
					//return redirect()->route('/trainer/add_yoga_workout/'.$id);
					//return redirect()->route('trainer/add_yoga_workout/');
					  return redirect('trainer/add_yoga_workout/'.$id);


                } else {
                    return redirect('trainer/yoga_workout_list')->with('er_status', 'Something Went Wrong Please Check Your Inputs!');
                }
            }
        } else {
            return redirect('/')->with('er_status', 'Your Session is Expired Please Login Again');
        }
    }

    public function yoga_wo_daywise_edit(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $yoga_wo_daywise_detail = WorkoutDetail::where(['_id'=>$id])->first();
            if($yoga_wo_daywise_detail){
                return view('trainer_panel/yoga/yoga_wo_daywise_edit',['yoga_wo_daywise_detail'=>$yoga_wo_daywise_detail]);
            }
            else{
                return redirect('trainer/yoga_workout_list')->with('er_status','Workout Not Found');
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function yoga_wo_daywise_update(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $messages = [
                'day.required' => 'Day is required.',
                'title.required' => 'Title is required.',
                'daily_desc.required' => 'Description is required.',
                'sets.required' => 'sets field is required.',
                'hold_time.required' => 'Time is required.',
            ];

            $validator = Validator::make($request->all(), [
                'day' => 'required',
                'title' => 'required',
                'daily_desc' => 'required',
                'sets' => 'required',
                'hold_time' => 'required',
            ], $messages);
      
            if ($validator->fails()) {
                  return redirect()->back()->withErrors($validator)->withInput();
            } else {
                $yoga_wo_daywise_id = $request->input('yoga_wo_daywise_id');
                $image1 = "";
                if($request->hasFile('image')) {
                    $image = $request->file('image');
                    $image1 = time().'.'.$image->getClientOriginalExtension();
                    $destinationPath = public_path('/images/image');
                    $image->move($destinationPath, $image1);
                }
                else{
                    $trainerdetails = WorkoutDetail::where(['_id'=>$yoga_wo_daywise_id])->select('image')->first();
                    $image1 = $trainerdetails['image'];
                }
                $updateData = [
                    'day' => $request->input('day'),
                    'title' => $request->input('title'),
                    'image' => $image1,
                    'daily_desc' => $request->input('daily_desc'),
                    'sets'=>$request->input('sets'),
                    'hold_time'=>$request->input('hold_time'),
                    'updated_at'=> date('d-m-y h:i:s')
                ];
   
                $update =  WorkoutDetail::where(['_id'=>$yoga_wo_daywise_id])->update($updateData);

                if ($update) {
                     return redirect('trainer/yoga_workout_list')->with('su_status', 'Workout updated Sucessfully!');
                } else {
                    return redirect('trainer/yoga_workout_list')->with('er_status', 'Something Went Wrong Please Check Your Inputs!');
                }
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function yoga_wo_daywise_delete(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $detail = WorkoutDetail::where(['_id'=>$id])->delete();
            if($detail){
                return redirect('trainer/yoga_workout_list')->with('su_status','Workout Deleted Successfully');
            }
            else{
                return redirect('trainer/yoga_workout_list')->with('er_status','Workout Not Found');
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function add_diet_daywise(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
			
			$week = Diet::where('_id', $id)->first();
			
			return view('trainer_panel/diet/add_diet_daywise', ['id'=>$id, 'week'=>$week['period']]);
			
            // return view('trainer_panel/diet/add_diet_daywise', ['id'=>$id]);
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function add_diet_daywise_action(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $messages = [
                'day.required' => 'Day is required.',
                'description.required' => 'Description is required.',
            ];

            $validator = Validator::make($request->all(), [
                'day' => 'required',
                'description' => 'required',
            ], $messages);
            if ($validator->fails()) {
                  return redirect()->back()->withErrors($validator)->withInput();
            } else {
				
                $serving = $request->input('servingsize');
                $recipename = $request->input('recipename');
                $ingredients = $request->input('name');
				 // $all_weeks=$request->input('all_weeks');
                $ingred = implode(",",$ingredients);
                $insertData = [
                    'activity' => $request->input('activity'),
                    'recipename' => $request->input('recipename'),
                    'serving_size' => $serving?$serving:0,
                    'ingredients' => $ingred?$ingred:0,
                    'diet_id' => $request->input('diet_id'),
                    'day' => $request->input('day'),
					 'week' => $request->input('week'),
					 'mylist' => $request->input('mylist'),
					 'approval'=>'pending',
					 'myrecipe' => $request->input('myrecipe'),
					 // 'all_weeks' =>$all_weeks?$all_weeks:0,
                    'daily_desc' => $request->input('description'),
                    'created_at' => date('Y-m-d h:i:s')
                ];
   
                $insert =  DietDetail::insertGetId($insertData);
				$week = Diet::where('_id', $request->input('diet_id'))->first();

				if($request->input('all_weeks'))
				{
				for($i=1;$i<=$week['period'];$i++)
				{
				$DietDetailWeek = new DietDetailWeek;				
				$DietDetailWeek->diet_id = $request->input('diet_id');
				$DietDetailWeek->DietDetail_id = $insert;
				$DietDetailWeek->day = $request->input('day');
				$DietDetailWeek->week = $i;
				$DietDetailWeek->save();
				}
				}
				else{
				$DietDetailWeek = new DietDetailWeek;				
				$DietDetailWeek->diet_id = $request->input('diet_id');
				$DietDetailWeek->DietDetail_id = $insert;
				$DietDetailWeek->day = $request->input('day');
				$DietDetailWeek->week = $request->input('week');
				$DietDetailWeek->save();
				}
				

                if ($insert) {
                     return redirect('trainer/diet_list')->with('su_status', 'Diet details Added Sucessfully!');
                } else {
                    return redirect('trainer/diet_list')->with('er_status', 'Something Went Wrong Please Check Your Inputs!');
                }
            }
        } else {
            return redirect('/')->with('er_status', 'Your Session is Expired Please Login Again');
        }
    }

   public function add_diet_daywise_recipe(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
			
		
			return view('trainer_panel/diet/add_diet_daywise_recipe');
            // return view('trainer_panel/diet/add_diet_daywise', ['id'=>$id]);
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }
	
	
	
    public function add_diet_daywise_recipe_action(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $messages = [
                'day.required' => 'Day is required.',
                'activity.required' => 'activity is required.',
               // 'description.required' => 'Description is required.',
            ];

            $validator = Validator::make($request->all(), [
                'day' => 'required',
                //'description' => 'required',
                'activity' => 'required',
            ], $messages);
            if ($validator->fails()) {
                  return redirect()->back()->withErrors($validator)->withInput();
            } else {
				
                $serving = $request->input('servingsize');
                $recipename = $request->input('recipename');
                $ingredients = $request->input('add_ingredients');
                $notes = $request->input('notes');
                $instructions = $request->input('instructions');
				 // $all_weeks=$request->input('all_weeks');
               // $ingred = implode(",",$ingredients);
                $insertData = [
                    'activity' => $request->input('activity'),
                    'approval' => $request->input('approval'),
                    'recipename' => $request->input('recipename'),
                    'serving_size' => $serving?$serving:0,
                    'ingredients' => $ingredients?$ingredients:0,
                    //'diet_id' => $request->input('diet_id'),
                    'day' => $request->input('day'),
					 'week' => $request->input('week'),
					 'mylist' => $request->input('mylist'),
					 'user_id' =>$user_id,
					 'myrecipe' => $request->input('myrecipe'),
					 // 'all_weeks' =>$all_weeks?$all_weeks:0,
                    'notes' =>$notes?$notes:'',
                    'instructions' =>$instructions?$instructions:'',
                    'created_at' => date('Y-m-d h:i:s')
                ];
				
                $insert =  DietDetail::insertGetId($insertData);
				/*$week = Diet::where('_id', $request->input('diet_id'))->first();

				if($request->input('all_weeks'))
				{
				for($i=1;$i<=$week['period'];$i++)
				{
				$DietDetailWeek = new DietDetailWeek;				
				$DietDetailWeek->diet_id = $request->input('diet_id');
				$DietDetailWeek->DietDetail_id = $insert;
				$DietDetailWeek->day = $request->input('day');
				$DietDetailWeek->week = $i;
				$DietDetailWeek->save();
				}
				}
				else{
				$DietDetailWeek = new DietDetailWeek;				
				$DietDetailWeek->diet_id = $request->input('diet_id');
				$DietDetailWeek->DietDetail_id = $insert;
				$DietDetailWeek->day = $request->input('day');
				$DietDetailWeek->week = $request->input('week');
				$DietDetailWeek->save();
				}
				*/

                if ($insert) {
                     return redirect('trainer/add_diet')->with('su_status', 'Recipe Added Sucessfully!');
                } else {
                    return redirect('trainer/diet_list')->with('er_status', 'Something Went Wrong Please Check Your Inputs!');
                }
            }
        } else {
            return redirect('/')->with('er_status', 'Your Session is Expired Please Login Again');
        }
    }
	
	
	public function recipe_list(Request $request){
		
		    $user_id = $request->session()->get('trainer_user_id');
			if($user_id)
			{
				$diet_details = DietDetail::get();
				
				//echo 2342;die();
				return view('trainer_panel/diet/recipe_list')->with('diet_details',$diet_details);
			}
			else
			{
				return redirect('/')->with('er_status','Session Expired. Please Login again.');
			}
		
	}
	public function recipe_list_delete(Request $request, $id){
		
		    $user_id = $request->session()->get('trainer_user_id');
			if($user_id)
			{
				$detail = DietDetail::where(['_id'=>$id])->delete();
					if($detail){
						return redirect('trainer/recipe_list')->with('su_status','Recipe Deleted Successfully');
					}
				
			}
			else
			{
				return redirect('/')->with('er_status','Session Expired. Please Login again.');
			}
		
	}
	
	
	
	public function recipe_list_edit(Request $request, $id)
	{
		//echo $id;
		$user_id = $request->session()->get('trainer_user_id');
        if($user_id){
							
				$recipe_edit = DietDetail::where(['_id'=>$id])->first();
				if(!empty($recipe_edit))
				{
				return view('trainer_panel/diet/recipe_list_edit')->with('recipe_edit',$recipe_edit);
				}
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
	}
	
	public function recipe_list_update(Request $request)
	{
		
		$user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $messages = [
                'day.required' => 'Day is required.',
                'activity.required' => 'activity is required.',
               // 'description.required' => 'Description is required.',
            ];

            $validator = Validator::make($request->all(), [
                'day' => 'required',
                //'description' => 'required',
                'activity' => 'required',
            ], $messages);
            if ($validator->fails()) {
                  return redirect()->back()->withErrors($validator)->withInput();
            } else {
				
                $serving = $request->input('servingsize');
                $recipename = $request->input('recipename');
                $ingredients = $request->input('add_ingredients');
                $notes = $request->input('notes');
                $instructions = $request->input('instructions');
				 // $all_weeks=$request->input('all_weeks');
               // $ingred = implode(",",$ingredients);
                $insertData = [
                    'activity' => $request->input('activity'),
                    'approval' => $request->input('approval'),
                    'recipename' => $request->input('recipename'),
                    'serving_size' => $serving?$serving:0,
                    'ingredients' => $ingredients?$ingredients:0,
                    //'diet_id' => $request->input('diet_id'),
                    'day' => $request->input('day'),
					 'week' => $request->input('week'),
					 'mylist' => $request->input('mylist'),
					 'user_id' =>$user_id,
					 'myrecipe' => $request->input('myrecipe'),
					 // 'all_weeks' =>$all_weeks?$all_weeks:0,
                    'notes' =>$notes?$notes:'',
                    'instructions' =>$instructions?$instructions:'',
                    'created_at' => date('Y-m-d h:i:s')
                ];
				
                $insert =  DietDetail::where('_id', '=', $request->input('recipe_id'))->update($insertData);
                if ($insert) {
                     return redirect('trainer/recipe_list')->with('su_status', 'Recipe updatd Sucessfully!');
                } else {
                    return redirect('trainer/recipe_list')->with('er_status', 'Something Went Wrong Please Check Your Inputs!');
                }
            }
        } else {
            return redirect('/')->with('er_status', 'Your Session is Expired Please Login Again');
        }
		
		
		
	}
	
	
	

    public function diet_daywise_edit(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $diet_daywise_detail = DietDetail::where(['_id'=>$id])->first();
            if($diet_daywise_detail){
                return view('trainer_panel/diet/diet_daywise_edit',['diet_daywise_detail'=>$diet_daywise_detail]);
            }
            else{
                return redirect('trainer/diet_list')->with('er_status','Diet Not Found');
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function diet_daywise_update(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $messages = [
                'day.required' => 'Day is required.',
                'description.required' => 'Description is required.',
            ];

            $validator = Validator::make($request->all(), [
                'day' => 'required',
                'description' => 'required',
            ], $messages);
      
            if ($validator->fails()) {
                  return redirect()->back()->withErrors($validator)->withInput();
            } else {
                $diet_daywise_id = $request->input('diet_daywise_id');
                $updateData = [
                    'day' => $request->input('day'),
                    'daily_desc' => $request->input('description'),
                    'recipename' => $request->input('recipename'),
                    'activity' => $request->input('activity'),
                    'serving_size' => $request->input('servingsize'),
                    'updated_at'=> date('d-m-y h:i:s')
                ];
   
                $update =  DietDetail::where(['_id'=>$diet_daywise_id])->update($updateData);

                if ($update) {
                     return redirect('trainer/diet_list')->with('su_status', 'Diet Detail updated Sucessfully!');
                } else {
                    return redirect('trainer/diet_list')->with('er_status', 'Something Went Wrong Please Check Your Inputs!');
                }
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function diet_daywise_delete(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $detail = DietDetail::where(['_id'=>$id])->delete();
            if($detail){
                return redirect('trainer/diet_list')->with('su_status','Diet Detail Deleted Successfully');
            }
            else{
                return redirect('trainer/diet_list')->with('er_status','Diet Detail Not Found');
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function add_wl_categories(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            return view('trainer_panel/weightlifting/add_wl_categories');
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function add_wl_categories_action(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $messages = [
                'category.required' => 'Category is required.'
            ];

            $validator = Validator::make($request->all(), [
                'category' => 'required'
            ], $messages);
            if ($validator->fails()) {
                  return redirect()->back()->withErrors($validator)->withInput();
            } else {
                $insertData = [
                    'category' => $request->input('category'),
                    'created_at' => date('Y-m-d h:i:s')
                ];
   
                $insert =  WlCategory::insertGetId($insertData);

                if ($insert) {
                     return redirect('trainer/wl_categories_list')->with('su_status', 'Excercise Category Added Sucessfully!');
                } else {
                    return redirect('trainer/wl_categories_list')->with('er_status', 'Something Went Wrong Please Check Your Inputs!');
                }
            }
        } else {
            return redirect('/')->with('er_status', 'Your Session is Expired Please Login Again');
        }
    }

    public function wl_categories_edit(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $category = WlCategory::where(['_id'=>$id])->first();
            if($category){
                return view('trainer_panel/weightlifting/wl_categories_edit',['category'=>$category]);
            }
            else{
                return redirect('trainer/wl_categories_list')->with('er_status','Excercise Category Not Found');
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function wl_categories_update(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $messages = [
                'category.required' => 'Category is required.'
            ];

            $validator = Validator::make($request->all(), [
                'category' => 'required'
            ], $messages);
      
            if ($validator->fails()) {
                  return redirect()->back()->withErrors($validator)->withInput();
            } else {
                $category_id = $request->input('category_id');
                $updateData = [
                    'category' => $request->input('category'),
                    'updated_at'=> date('d-m-y h:i:s')
                ];
   
                $update =  WlCategory::where(['_id'=>$category_id])->update($updateData);

                if ($update) {
                     return redirect('trainer/wl_categories_list')->with('su_status', 'Excercise Category updated Sucessfully!');
                } else {
                    return redirect('trainer/wl_categories_list')->with('er_status', 'Something Went Wrong Please Check Your Inputs!');
                }
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function wl_categories_delete(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $detail = WlCategory::where(['_id'=>$id])->delete();
            if($detail){
                return redirect('trainer/wl_categories_list')->with('su_status','Excercise Category Deleted Successfully');
            }
            else{
                return redirect('trainer/wl_categories_list')->with('er_status','Excercise Category Not Found');
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function wl_categories_list(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $categories = WlCategory::orderBy('_id','desc')->get();
            if($categories){
                return view('trainer_panel/weightlifting/wl_categories_list',['categories' => $categories]);
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function add_yoga_categories(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            return view('trainer_panel/yoga/add_yoga_categories');
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function add_yoga_categories_action(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $messages = [
                'category.required' => 'Category is required.'
            ];

            $validator = Validator::make($request->all(), [
                'category' => 'required'
            ], $messages);
            if ($validator->fails()) {
                  return redirect()->back()->withErrors($validator)->withInput();
            } else {
                $insertData = [
                    'category' => $request->input('category'),
                    'created_at' => date('Y-m-d h:i:s')
                ];
   
                $insert =  YogaCategory::insertGetId($insertData);

                if ($insert) {
                     return redirect('trainer/yoga_categories_list')->with('su_status', 'Excercise Category Added Sucessfully!');
                } else {
                    return redirect('trainer/yoga_categories_list')->with('er_status', 'Something Went Wrong Please Check Your Inputs!');
                }
            }
        } else {
            return redirect('/')->with('er_status', 'Your Session is Expired Please Login Again');
        }
    }

    public function yoga_categories_edit(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $category = YogaCategory::where(['_id'=>$id])->first();
            if($category){
                return view('trainer_panel/yoga/yoga_categories_edit',['category'=>$category]);
            }
            else{
                return redirect('trainer/yoga_categories_list')->with('er_status','Excercise Category Not Found');
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function yoga_categories_update(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $messages = [
                'category.required' => 'Category is required.'
            ];

            $validator = Validator::make($request->all(), [
                'category' => 'required'
            ], $messages);
      
            if ($validator->fails()) {
                  return redirect()->back()->withErrors($validator)->withInput();
            } else {
                $category_id = $request->input('category_id');
                $updateData = [
                    'category' => $request->input('category'),
                    'updated_at'=> date('d-m-y h:i:s')
                ];
   
                $update =  YogaCategory::where(['_id'=>$category_id])->update($updateData);

                if ($update) {
                     return redirect('trainer/yoga_categories_list')->with('su_status', 'Excercise Category updated Sucessfully!');
                } else {
                    return redirect('trainer/yoga_categories_list')->with('er_status', 'Something Went Wrong Please Check Your Inputs!');
                }
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function yoga_categories_delete(Request $request, $id){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $detail = YogaCategory::where(['_id'=>$id])->delete();
            if($detail){
                return redirect('trainer/yoga_categories_list')->with('su_status','Excercise Category Deleted Successfully');
            }
            else{
                return redirect('trainer/yoga_categories_list')->with('er_status','Excercise Category Not Found');
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }

    public function yoga_categories_list(Request $request){
        $user_id = $request->session()->get('trainer_user_id');
        if($user_id){
            $categories = YogaCategory::orderBy('_id','desc')->get();
            if($categories){
                return view('trainer_panel/yoga/yoga_categories_list',['categories' => $categories]);
            }
        }
        else{
            return redirect('/')->with('er_status','Session Expired. Please Login again.');
        }
    }
}
