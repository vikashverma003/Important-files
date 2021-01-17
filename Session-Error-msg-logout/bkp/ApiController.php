<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User as User;
use App\Payments as Payments;
use App\Post as Post;
use App\News as News;
use App\Diet as Diet;
use App\Like as Like;
use App\LikeGymPartner as LikeGymPartner;
use App\Block as Block;
use App\Comment as Comment;
use App\Workout as Workout;
use App\Follow as Follow;
use App\Trainer as Trainer;
use App\Favorite as Favorite;
use App\FavoriteGymPartner as FavoriteGymPartner;
use App\WorkoutInterest as WorkoutInterest;
use App\Category as Category;
use App\Report as Report;
use App\Goal as Goal;
use App\UserDetail as UserDetail;
use App\DietDetail as DietDetail;
use App\WorkoutDetail as WorkoutDetail;
use App\TrainingDetail as TrainingDetail;
use App\ChatList as ChatList;
use App\Chat as Chat;
use App\MyDiet as MyDiet;
use App\Setting as Setting;
use App\MyRunning as MyRunning;
use App\MyYoga as MyYoga;
use App\MyWeightLifting as MyWeightLifting;
use App\RateWorkout as RateWorkout;
use App\UserStat as UserStat;
use App\Notification as Notification;
use App\readNotify as readNotify;
use App\TrainerRequest as TrainerRequest;
use App\WlCategory as WlCategory;
use App\YogaCategory as YogaCategory;
use App\Helpers; // include the helper 
use Validator;
use Hash;
use Illuminate\Support\Facades\Mail;
use DB;
use Storage;
use App\Teams;

use App\DietDetailWeek;
use Carbon\Carbon;
use App\Admin;
use Stripe;

class ApiController extends Controller
{
	private $badRequestCode = 400;
	private $successCode = 200;

    public function register(Request $request){
    	$saveArray = $request->all();
    	$validator = Validator::make($request->all(), [ 
    		'first_name' => 'required',
    		'last_name' => 'required',
			'email' => 'required|email|unique:users', 
			'password' => 'required', 
			'c_password' => 'required|same:password',
    		'location' => 'required',
    		//'profile_image' => 'required',
    		'device_type'=> 'required',
    		'device_token'=> 'required',
    		'lat'=> 'required',
    		'long'=> 'required',
		]);
		if ($validator->fails()) {
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->successCode);            
		}
		else{
			$first_name = $request->input('first_name');
			$last_name = $request->input('last_name');
			$email = $request->input('email');
			$password = $request->input('password');
			$location = $request->input('location');
			$device_type = $request->input('device_type');
			$device_token = $request->input('device_token');
			$lat = floatval($request->input('lat'));
			$long = floatval($request->input('long'));
			$verification_code = mt_rand(10000, 99999);
			$en_password = Hash::make($password);
			$profile_image = "";
			if($request->hasFile('profile_image')) {
				$image = $request->file('profile_image');
				$profile_image = time().'.'.$image->getClientOriginalExtension();
				$destinationPath = public_path('/images');
				$image->move($destinationPath, $profile_image);
			}

			$locObj = array('type'=>"Point",'coordinates'=>array($long, $lat));

			$insertData = [
				'login_type'=>'N',
				'social_id'=>'',
				'first_name'=>$first_name,
				'last_name'=>$last_name,
				'email'=>$email,
				'password'=>$en_password,
				'location'=>$location,
				'profile_image'=>$profile_image,
				'verification_code'=>$verification_code,
				'verified'=>'N',
				'gym_partner_status'=>'off',
				'status'=>'Y',
				'created_at'=>date('Y-m-d H:i:s'),
				'device_type'=>$device_type,
				'device_token'=>$device_token,
				'age'=>'',
				'bio'=>'',
				'body_mass'=>'',
				'goal1'=>'',
				'goal2'=>'',
				'goal3'=>'',
				'height'=>'',
				'weight'=>'',
				'account_type'=>'public',
				'lat'=>$lat,
				'long'=>$long,
				'loc'=>$locObj
			];

			$user_id = User::insertGetId($insertData);
			
			$settingData = [
			'user_id'=>(String)$user_id,
			'make_account_private'=>'0',
			'turn_on_gym_partner'=>'0',
			'push_notification'=>'1',
			'email_notification'=>'1',
			'unit_run'=>'0',
			'audio_case_run'=>'0',
			'unit_wl'=>'0',
			'audio_cues_wl'=>'0',
			'run_distance_val'=>'0',
			'gym_partner_status'=>'off'
			];
			$setting = Setting::insertGetId($settingData);
			
			if($user_id){
				
				$insertData = [
				'user_id'=>(String)$user_id,
				'height'=>0,
				'weight'=>0,
				'body_mass_index'=>'',
				'percent_body_fat'=>'',
				'neck_circumference'=>'',
				'chest_circumference'=>'',
				'waist_circumference'=>'',
				'hip_circumference'=>'',
				'shoulder_circumference'=>'',
				'arm_circumference'=>'',
				'calf_circumference'=>'',
				];
				
				$UserDetail_id = UserDetail::insertGetId($insertData);
				
				
				$msg = [
					"first_name"=>$first_name,
					"last_name"=>$last_name,
                    "verification_code" => $verification_code
                ];
                Mail::send('welcome_mail', $msg, function($message) use ($email) {
                    $message->to($email);
                    $message->subject('Verification - Fitneb');
                });
                $goal = array(
						'',
						'',
						'',
					);

                $url = config('filesystems.disks.s3')['url'];
				return response()->json([ 
					'status' => true,
					'message' => 'User Registered Successfully',
					'data'=>[
						'user_id'=>(String)$user_id,
						'first_name'=>$first_name,
						'last_name'=>$last_name,
						'email'=>$email,
						'location'=>$location,
						'profile_image'=>url('public/images',$profile_image),
						'verification_code'=>$verification_code,
						'verified'=>'N',
						'device_type'=>$device_type,
						'device_token'=>$device_token,
						'age'=>"",
						'bio'=>"",
						'body_mass'=>"",
						'goal'=>$goal,
						'height'=>"",
						'weight'=>"",
						'account_type'=>'public',
						'lat'=>$lat,
						'long'=>$long
					],
				], $this->successCode);
			}
			else{
				return response()->json([ 
					'status' => false,
					'message' => 'User Registered Failed',
				], $this->successCode);
			}
		}
    }

    public function getstripetoken(Request $request){  
	\Stripe\Stripe::setApiKey('sk_test_Jl6H871Igtmr4WiXrYW0LRWW00U7fGQv3l');

			$intent = \Stripe\PaymentIntent::create([
				'amount' => $request->price*100,
				'currency' => 'usd',
				'receipt_email' => $request->email
			]);
			$client_secret = $intent->client_secret;
			return response()->json([
				        'status' => true,
				        'token'=>$client_secret]);
	}
	public function savepayment(Request $request){ 
		$saveArray = $request->all();
		$validator = Validator::make($request->all(), [ 
    		'user_id' => 'required',
    		'payment_token' => 'required',
    		'id' => 'required'
		]);
		if ($validator->fails()) {
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false]);            
		}
		else{
			$insertData = [
				'user_id'=>$saveArray['user_id'],
				'payment_token'=>$saveArray['payment_token'],
				'id'=>$saveArray['id']
			];

			$Payments = Payments::insertgetId($insertData);
			if (!$Payments) {
			  return response()->json(['message'=>"Payment is aborted",'status'=>false]);            
		    }
			else{
				return response()->json(['message'=>"Payment successfull",'status'=>true]); 
			}
		}  
	}
	public function login(Request $request){ 
		$saveArray = $request->all();
		$validator = Validator::make($request->all(), [ 
    		'email' => 'required',
    		'password' => 'required',
			'device_type' => 'required', 
			'device_token' => 'required',
			'location' => 'required'
		]);
		if ($validator->fails()) {
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false]);            
		}
		else{
			$userExist = User::where(['email'=> $saveArray['email']])->first() ;
	        if($userExist){ 
		        if(Hash::check($saveArray['password'],$userExist['password'])){
					
					if($userExist['status']=='Y')
					{
					
		        	$long = floatval($saveArray['long']);
		        	$lat = floatval($saveArray['lat']);
		        	$update = User::where(['_id'=>$userExist->id])->update(['location'=>$saveArray['location'],'lat'=>$lat,'long'=>$long,'device_type'=>$saveArray['device_type'], 'device_token'=>$saveArray['device_token'], 'loc'=> array('type'=>"Point",'coordinates'=>array($long, $lat))]);
		        	$goal = array(
						$userExist->goal1?$userExist->goal1:'',
						$userExist->goal2?$userExist->goal2:'',
						$userExist->goal3?$userExist->goal3:'',
					);
					
					$followerCount = Follow::where(['other_user_id'=> $userExist->id])->count();
					$followingCount = Follow::where(['user_id'=> $userExist->id])->count();
					
				    return response()->json([
				        'status' => true,
				        'message'=>'Logged in successfully',
				        'data'=>[
				        	'user_id'=>$userExist->id,
				        	'first_name'=>$userExist->first_name,
				        	'last_name'=>$userExist->last_name,
				        	'email'=>$userExist->email,
				        	'location'=>$userExist->location,
				        	'verified'=>$userExist->verified,
							'status'=>$userExist->status,
				        	'device_type'=>$saveArray['device_type'],
				        	'device_token'=>$saveArray['device_token'],
				        	'profile_image'=>url('public/images',$userExist->profile_image),
				        	'age'=>$userExist->age?$userExist->age:'',
							'bio'=>$userExist->bio?$userExist->bio:'',
							'body_mass'=>$userExist->body_mass?$userExist->body_mass:'',
							'goal'=>$goal,
							'height'=>$userExist->height?$userExist->height:'',
							'weight'=>$userExist->weight?$userExist->weight:'',
							'followerCount'=>$followerCount,
							'followingCount'=>$followingCount,
							'account_type'=>$userExist->account_type,
							'lat'=>$lat,
							'long'=>$long,
				       	]
				    ], $this->successCode); 
				}
				else
				{
					
		      return response()->json(['message'=>'Admin has disabled the account','status'=>false], $this->successCode); 

				}

				}
				else{
					return response()->json(['message'=>'User and Password is incorrect','status'=>false], $this->successCode); 
				}
	        } 
	        else{ 
	            return response()->json(['message'=>'User and Password is incorrect','status'=>false], $this->successCode); 
	        }
		}
    }

    function randomPassword() {
        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    public function forgot_password(Request $request)
	{
		$saveArray = $request->all();
		$user = User::where(['email'=>$saveArray['email']])->select('first_name','last_name')->first();
		if($user){
			$new_password = ApiController::randomPassword();
			$new_password_en = Hash::make($new_password);
			$update_password = [
                'password' => $new_password_en,
                'updated_at' => date('Y-m-d h:i:s')
            ];
            $update = User::where(['email'=>$saveArray['email']])->update($update_password);
            if($update){
            	$msg = [
            		'first_name'=>$user['first_name'],
            		'last_name'=>$user['last_name'],
                    "new_password" => $new_password
                ];
                $email = $saveArray['email'];
                Mail::send('mail', $msg, function($message) use ($email) {
                    $message->to($email);
                    $message->subject('New Password - Fitneb');
                });
                return response()->json(['status'=>true,'message'=>'New Password has been mailed to you. Please check your email'], $this->successCode);
            }
		}
		else{
			return response()->json(['message'=>'User does not exist','status'=>false], $this->successCode); 
		}
	}

	public function resend_verify_code(Request $request){
		$saveArray = $request->all();
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required'
		]);
		if($validator->fails()) {
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			$userdetails = User::where(['_id'=>$saveArray['user_id']])->select('first_name','last_name','email')->first();
			if($userdetails){
				$verification_code = mt_rand(10000, 99999);
				$updateData = [
	                'verification_code' => $verification_code,
	                'updated_at' => date('Y-m-d h:i:s')
	            ];
	            $update = User::where(['_id'=>$saveArray['user_id']])->update($updateData);
	            if($update){
	            	$msg = [
						"first_name"=>$userdetails['first_name'],
						"last_name"=>$userdetails['last_name'],
	                    "verification_code" => $verification_code
	                ];
	                $email = $userdetails['email'];
	                Mail::send('welcome_mail', $msg, function($message) use ($email) {
	                    $message->to($email);
	                    $message->subject('Verification - Fitneb');
	                });
	                return response()->json(['status'=>true,'message'=>'Verification code has been mailed to you. Please check your email'], $this->successCode);
	            }
	            else{
	            	return response()->json(['message'=>'Something went wrong','status'=>false], $this->successCode); 
	            }
			}
			else{
				return response()->json(['message'=>'User does not exist','status'=>false], $this->successCode); 
			}
		}
	}

	public function social_login(Request $request){
		$saveArray = $request->all();
		//print_R('gfdf');die;
		$validator = Validator::make($request->all(), [ 
			'login_type' => 'required',
			'social_id' => 'required',
    		'first_name' => 'required',
    		'last_name' => 'required',
			'email' => 'required',
    		'location' => 'required',
    		'profile_image'=>'required',
    		'device_type'=>'required',
    		'device_token'=>'required',
		]);
		if($validator->fails()) {
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			$login_type = $request->input('login_type');
			$social_id = $request->input('social_id');
			$first_name = $request->input('first_name');
			$last_name = $request->input('last_name');
			$email = $request->input('email');
			$location = $request->input('location');
			$profile_image = $request->input('profile_image');
			$device_type = $request->input('device_type');
			$device_token = $request->input('device_token');

			$user_exist = User::where(['email'=>$email])->first();
			if($user_exist){
				$long = floatval($saveArray['long']);
				$lat = floatval($saveArray['lat']);
				$update = User::where(['_id'=>$user_exist['_id']])->update(['device_type'=>$device_type,'lat'=>$lat,'long'=>$long,'device_token'=>$device_token, 'loc'=> array('type'=>"Point",'coordinates'=>array($long, $lat))]);
				$goal = array(
						$user_exist->goal1?$user_exist->goal1:'',
						$user_exist->goal2?$user_exist->goal2:'',
						$user_exist->goal3?$user_exist->goal3:'',
					);
					
					$followerCount = Follow::where(['other_user_id'=> $user_exist['_id']])->count();
					$followingCount = Follow::where(['user_id'=> $user_exist['_id']])->count();

					$p_image = "";
					if(strpos($profile_image, "http://") !== false){
						$p_image = $profile_image;
					}
					else if(strpos($profile_image, "https://") !== false){
						$p_image = $profile_image;
					}
					else{
						$p_image = url('public/images',$profile_image);
					}
					
				return response()->json([ 
					'status' => true,
					'message' => 'User Logged Successfully',
					'data'=>[
						'user_id'=>(String)$user_exist['_id'],
						'login_type'=>$user_exist['login_type'],
						'social_id'=>$user_exist['social_id']?$user_exist['social_id']:'',
						'first_name'=>$user_exist['first_name'],
						'last_name'=>$user_exist['last_name'],
						'email'=>$user_exist['email'],
						'location'=>$user_exist['location'],
						'profile_image'=>$p_image,
						'verified'=>'Y',
						'device_type'=>$device_type,
						'device_token'=>$device_token,
						'age'=>$user_exist->age?$user_exist->age:'',
						'bio'=>$user_exist->bio?$user_exist->bio:'',
						'body_mass'=>$user_exist->body_mass?$user_exist->body_mass:'',
						'goal'=>$goal,
						'height'=>$user_exist->height?$user_exist->height:'',
						'weight'=>$user_exist->weight?$user_exist->weight:'',
						'followerCount'=>$followerCount,
						'followingCount'=>$followingCount,
						'account_type'=>$user_exist->account_type,
						'lat'=>$lat,
						'long'=>$long,
					],
				], $this->successCode);
			}
			else{
				$profile_image = $request->input('profile_image');
				$long = floatval($saveArray['long']);
				$lat = floatval($saveArray['lat']);
				$locObj = array('type'=>"Point",'coordinates'=>array($long, $lat));
				$insertData = [
					'login_type'=>$login_type,
					'social_id'=>$social_id,
					'first_name'=>$first_name,
					'last_name'=>$last_name,
					'email'=>$email,
					'password'=>'',
					'location'=>$location,
					'profile_image'=>$profile_image,
					'verification_code'=>'',
					'verified'=>'Y',
					'status'=>'Y',
					'created_at'=>date('Y-m-d H:i:s'),
					'device_type'=>$device_type,
					'device_token'=>$device_token,
					'age'=>'',
					'bio'=>'',
					'body_mass'=>'',
					'goal1'=>'',
					'goal2'=>'',
					'goal3'=>'',
					'height'=>'',
					'weight'=>'',
					'account_type'=>'public',
					'lat'=>$lat,
					'long'=>$long,
					'loc'=>$locObj
				];
				$user_id = User::insertGetId($insertData);
				if($user_id){
					$goal = array(
						'goal1'=>'',
						'goal2'=>'',
						'goal3'=>'',
					);
					
					$followerCount = Follow::where(['other_user_id'=> $user_id])->count();
					$followingCount = Follow::where(['user_id'=> $user_id])->count();
					
					
					return response()->json([ 
						'status' => true,
						'message' => 'User Registered Successfully',
						'data'=>[
							'user_id'=>(String)$user_id,
							'login_type'=>$login_type,
							'social_id'=>$social_id,
							'first_name'=>$first_name,
							'last_name'=>$last_name,
							'email'=>$email,
							'location'=>$location,
							'profile_image'=>$profile_image,
							'verified'=>'Y',
							'device_type'=>$device_type,
							'device_token'=>$device_token,
							'age'=>'',
							'bio'=>'',
							'body_mass'=>'',
							'goal'=>$goal,
							'height'=>'',
							'weight'=>'',
							'followerCount'=>$followerCount,
							'followingCount'=>$followingCount,
							'account_type'=>'public',
							'lat'=>$lat,
							'long'=>$long,
						],
					], $this->successCode);
				}
				else{
					return response()->json([ 
						'status' => false,
						'message' => 'User Registered Failed',
					], $this->successCode);
				}
			}
		}
	}

	public function verify_code(Request $request){
		$saveArray = $request->all();
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',
			'verification_code' => 'required'
		]);
		if($validator->fails()) {
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			$user_id = $request->input('user_id');
			$verification_code = $request->input('verification_code');
			$userDetails = User::where(['_id'=>$user_id])->select('verification_code','verified','first_name', 'last_name', 'email')->first();
			
			
			$v_code = $userDetails['verification_code'];
			$v_status = $userDetails['verified'];
			$first_name =$userDetails['first_name'];
			$last_name= $userDetails['last_name'];
			
			$email = $userDetails['email'];
			
			
			
							
			if($v_status == "N"){
				if($v_code == $verification_code){
					$updateData = [
						'verified'=>'Y',
						'updated_at'=>date('Y-m-d H:i:s')
					];
					$update = User::where(['_id'=>$user_id])->update($updateData);
					if($update){
						
					$msg = [
								"first_name"=>$first_name,
								"last_name"=>$last_name
								
							];
							Mail::send('welcome_verify_mail', $msg, function($message) use ($email) {
								$message->to($email);
								$message->subject('Welcome - Fitneb');
							});
						
						
						return response()->json([ 
							'status' => true,
							'message' => 'User Verified Successfully'
						], $this->successCode);
					}
				}
				else{
					return response()->json([ 
						'status' => false,
						'message' => 'Wrong Verification Code'
					], $this->successCode);
				}
			}
			else{
				return response()->json([ 
					'status' => false,
					'message' => 'User Already Verified'
				], $this->successCode);
			}
		}
	}

	public function get_profile(Request $request){
		$saveArray = $request->all();
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required'
		]);
		if($validator->fails()) {
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			
			$followerCount = Follow::where(['other_user_id'=> $saveArray['user_id']])->count();
			$followingCount = Follow::where(['user_id'=> $saveArray['user_id']])->count();
			
			$text = Post::where(['user_id'=>$saveArray['user_id']])->count();
			$image = Post::where(['user_id'=>$saveArray['user_id']])->where('image','!=','')->select('image')->count();

			$video = Post::where(['user_id'=>$saveArray['user_id']])->where('video','!=','')->select('video')->count();

			$user_exist = User::where(['_id'=>$saveArray['user_id']])->first();
			if($user_exist){
				$profile_image = "";
				if(strpos($user_exist['profile_image'], "http://") !== false){
					$profile_image = $user_exist['profile_image'];
				}
				else if(strpos($user_exist['profile_image'], "https://") !== false){
					$profile_image = $user_exist['profile_image'];
				}
				else{
					$profile_image = url('public/images',$user_exist['profile_image']);
				}
				$goal = array(
						$user_exist->goal1?$user_exist->goal1:'',
						$user_exist->goal2?$user_exist->goal2:'',
						$user_exist->goal3?$user_exist->goal3:'',
					);
					
					
					
				return response()->json([ 
					'status' => true,
					'message' => 'User Profile',
					'data'=>[
						'user_id'=>(String)$user_exist['_id'],
						'login_type'=>$user_exist['login_type'],
						'social_id'=>$user_exist['social_id']?$user_exist['social_id']:'',
						'first_name'=>$user_exist['first_name'],
						'last_name'=>$user_exist['last_name'],
						'email'=>$user_exist['email'],
						'location'=>$user_exist['location'],
						'profile_image'=>$profile_image,
						'verified'=>'Y',
						'age'=>$user_exist->age?$user_exist->age:'',
						'bio'=>$user_exist->bio?$user_exist->bio:'',
						'body_mass'=>$user_exist->body_mass?$user_exist->body_mass:'',
						'goal'=>$goal,
						'height'=>$user_exist->height?$user_exist->height:'',
						'weight'=>$user_exist->weight?$user_exist->weight:'',
						
						'height_inches'=>$user_exist->height_inches?$user_exist->height_inches:'',
						'weight_kg'=>$user_exist->weight_kg?$user_exist->weight_kg:'',
						'sex'=>$user_exist->sex?$user_exist->sex:'',

						
						
						'followerCount'=>$followerCount,
						'followingCount'=>$followingCount,
						'text'=>$text,
						'image'=>$image,
						'video'=>$video,
						'lat'=>$user_exist['lat'],
						'long'=>$user_exist['long'],
						'account_type'=>$user_exist['account_type'],
					],
				], $this->successCode);
			}
			else{
				return response()->json([ 
					'status' => false,
					'message' => 'User not found',
				], $this->successCode);
			}
		}
	}

	public function get_profile_other(Request $request){
		$saveArray = $request->all();
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',
			'other_user_id' => 'required'
		]);
		if($validator->fails()) {
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			          $user_exist = User::where(['_id'=>$saveArray['other_user_id']])->first();
						
						if($user_exist)
						{
							
							$username =  $user_exist['first_name'];
						}
						else
						{
							 
					    $user_exist = Trainer::where(['_id'=>$saveArray['other_user_id']])->first();
						$user_exist= User::where(['email'=>$user_exist->email])->first();
						 $username =  $user_exist['first_name'];
						}
					   $is_follow = "N";
			$followStatus = Follow::where(['user_id'=>$saveArray['user_id']])->where(['other_user_id'=>$user_exist['_id']])->first();
			if($followStatus){
				$is_follow = "Y";
			}
			$followerCount = Follow::where(['other_user_id'=>$user_exist['_id']])->count();
			$followingCount = Follow::where(['user_id'=> $user_exist['_id']])->count();
			
			$text = Post::where(['user_id'=>$user_exist['_id']])->count();
			$image = Post::where(['user_id'=>$user_exist['_id'],'post_type'=>'image'])->count();
			$video = Post::where(['user_id'=>$user_exist['_id'],'post_type'=>'video'])->count();
			$workoutInterestStatus = "N";
			$workoutInterestDetail = WorkoutInterest::where(['user_id'=>$saveArray['user_id']])->where(['partner_id'=>$user_exist['_id']])->where(['interest_status'=>'yes'])->first();
			if($workoutInterestDetail){
				$workoutInterestStatus = "Y";
			}
			$favoriteStatus = "N";
			$favoriteDetail = FavoriteGymPartner::where(['user_id'=>$saveArray['user_id']])->where(['partner_id'=>$user_exist['_id']])->where(['favorite_status'=>'favorite'])->first();
			if($favoriteDetail){
				$favoriteStatus = "Y";
			}
			$chat_id = "";
			$chatdetails = ChatList::where(['user_id'=>$saveArray['user_id']])->where(['other_user_id'=>$user_exist['_id']])->first();
			if($chatdetails){
				$chat_id = (String)$chatdetails['_id'];
			}
			else{
				$chatdetails = ChatList::where(['user_id'=>$user_exist['_id']])->where(['other_user_id'=>$saveArray['user_id']])->first();
				if($chatdetails){
					$chat_id = (String)$chatdetails['_id'];
				}
			}
						

			if($user_exist){
				$profile_image = "";
				if(strpos($user_exist['profile_image'], "http://") !== false){
					$profile_image = $user_exist['profile_image'];
				}
				else if(strpos($user_exist['profile_image'], "https://") !== false){
					$profile_image = $user_exist['profile_image'];
				}
				else{
					$profile_image = url('public/images',$user_exist['profile_image']);
				}
				$goal = array(
						$user_exist->goal1?$user_exist->goal1:'',
						$user_exist->goal2?$user_exist->goal2:'',
						$user_exist->goal3?$user_exist->goal3:'',
					);
				return response()->json([ 
					'status' => true,
					'message' => 'User Profile',
					'data'=>[
						'user_id'=>(String)$user_exist['_id'],
						'login_type'=>$user_exist['login_type'],
						'social_id'=>$user_exist['social_id']?$user_exist['social_id']:'',
						'first_name'=> $username ,
						'last_name'=>$user_exist['last_name'],
						'email'=>$user_exist['email'],
						'location'=>$user_exist['location'],
						'profile_image'=>$profile_image,
						'verified'=>'Y',
						'age'=>$user_exist->age?$user_exist->age:'',
						'bio'=>$user_exist->bio?$user_exist->bio:'',
						'body_mass'=>$user_exist->body_mass?$user_exist->body_mass:'',
						'goal'=>$goal,
						'height'=>$user_exist->height?$user_exist->height:'',
						'weight'=>$user_exist->weight?$user_exist->weight:'',
						'followerCount'=>$followerCount,
						'followingCount'=>$followingCount,
						'text'=>$text,
						'image'=>$image,
						'video'=>$video,
						'lat'=>$user_exist['lat'],
						'long'=>$user_exist['long'],
						'account_type'=>$user_exist['account_type']?$user_exist['account_type']:'Public',
						'is_follow'=>$is_follow,
						'workoutInterestStatus'=>$workoutInterestStatus,
						'favoriteStatus'=>$favoriteStatus,
						'chat_id'=>$chat_id
					],
				], $this->successCode);
			}
			else{
				return response()->json([ 
					'status' => false,
					'message' => 'User not found',
				], $this->successCode);
			}
		}
	}

	public function edit_profile(Request $request){
		$saveArray = $request->all();
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required'
		]);
		if($validator->fails()) {
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			$user_exist = User::where(['_id'=>$saveArray['user_id']])->first();
			$first_name = $request->input('first_name');
			$last_name = $request->input('last_name');
			$location = $request->input('location');
			$bio = $request->input('bio');
			$weight = $request->input('weight');
			$height = $request->input('height');
			$age = $request->input('age');
			$goal1 = $request->input('goal1');
			$goal2 = $request->input('goal2');
			
			$height_inches = $request->input('height_inches');
			$weight_kg = $request->input('weight_kg');
			$sex = $request->input('sex');
			
			$goal3 = $request->input('goal3');
			$lat = floatval($request->input('lat'));
			$long = floatval($request->input('long'));
			$body_mass = $request->input('body_mass');
			/* for updating the user_details table */
			
			$body_mass_index = $request->input('body_mass_index');
			$percent_body_fat = $request->input('percent_body_fat');
			$neck_circumference = $request->input('neck_circumference');

			$chest_circumference = $request->input('chest_circumference');
			$waist_circumference = $request->input('waist_circumference');
			$hip_circumference = $request->input('hip_circumference');

			$update_details = [
			'weight'=>$weight?$weight:'',
			'height'=>$height?$height:'',
			'body_mass_index'=>$body_mass_index?$body_mass_index:'',
			'percent_body_fat'=>$percent_body_fat?$percent_body_fat:'',
			'neck_circumference'=>$neck_circumference?$neck_circumference:'',
			'chest_circumference'=>$chest_circumference?$chest_circumference:'',
			'waist_circumference'=>$waist_circumference?$waist_circumference:'',
			'hip_circumference'=>$hip_circumference?$hip_circumference:''
			
			];
			$update_user_details = UserDetail::where(['user_id'=>$saveArray['user_id']])->update($update_details);
			
			$profile_image = "";
			if($request->hasFile('profile_image')) {
				$image = $request->file('profile_image');
				$profile_image = time().'.'.$image->getClientOriginalExtension();
				$destinationPath = public_path('/images');
				$image->move($destinationPath, $profile_image);
			}
			else{
				$profile_image = $user_exist['profile_image'];
			}

			$updateData = [
				'first_name'=>$first_name?$first_name:$user_exist['first_name'],
				'last_name'=>$last_name?$last_name:$user_exist['last_name'],
				'location'=>$location?$location:$user_exist['location'],
				'profile_image'=>$profile_image?$profile_image:$user_exist['profile_image'],
				'bio'=>$bio?$bio:$user_exist['bio'],
				'weight'=>$weight?$weight:'',
				'height'=>$height?$height:'',
				'age'=>$age?$age:$user_exist['age'],
				'goal1'=>$goal1?$goal1:$user_exist['goal1'],
				'goal2'=>$goal2?$goal2:$user_exist['goal2'],
				'goal3'=>$goal3?$goal3:$user_exist['goal3'],
				
				'height_inches'=>$height_inches?$height_inches:'',
				'weight_kg'=>$weight_kg?$weight_kg:'',
				'sex'=>$sex?$sex:'',
				
				'body_mass'=>$body_mass?$body_mass:$user_exist['body_mass'],
				'lat'=>$lat?$lat:$user_exist['lat'],
				'long'=>$long?$long:$user_exist['long'],
				'loc' => array('type'=>"Point",'coordinates'=>array($long, $lat))
			];
			

			$update = User::where(['_id'=>$saveArray['user_id']])->update($updateData);
			if($update){
				$user_exist = User::where(['_id'=>$saveArray['user_id']])->first();
				$profile_image = "";
				if(strpos($user_exist['profile_image'], "http://") !== false){
					$profile_image = $user_exist['profile_image'];
				}
				else if(strpos($user_exist['profile_image'], "https://") !== false){
					$profile_image = $user_exist['profile_image'];
				}
				else{
					$profile_image = url('public/images',$user_exist['profile_image']);
				}
				$goal = array(
					$user_exist['goal1'],
					$user_exist['goal2'],
					$user_exist['goal3'],
				);
				$data = [
					'first_name'=>$user_exist['first_name'],
					'last_name'=>$user_exist['last_name'],
					'location'=>$user_exist['location'],
					'profile_image'=>$profile_image,
					'bio'=>$user_exist['bio'],
					'weight'=>$user_exist['weight'],
					'height'=>$user_exist['height'],
					'height_inches'=>$user_exist['height_inches'],
					'weight_kg'=>$user_exist['weight_kg'],
					'sex'=>$user_exist['sex'],

					'goal'=>$goal,
					'body_mass'=>$user_exist['body_mass'],
					'age'=>$user_exist['age'],
					'lat'=>$user_exist['lat'],
					'long'=>$user_exist['long'],
				];
				return response()->json([ 
					'status' => true,
					'message' => 'User profile updated',
					'data'=>$data
				], $this->successCode);
			}
			else{
				return response()->json([ 
					'status' => false,
					'message' => 'Error on profile updation',
				], $this->successCode);
			}
		}
	}
	
	
#************************************************************************************#


	public function addPost(Request $request){
		
		$saveArray = $request->all();
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',
		]);
		if($validator->fails()) {
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			$saveArray['post_type']='text';
			$image = "";
			$video = "";
			
			if($request->hasFile('image')) {
				$saveArray['post_type']='image';
				$post_image = $request->file('image');
				$image = time().'.'.$post_image->getClientOriginalExtension();
				$destinationPath = public_path('/images/image');
				$post_image->move($destinationPath, $image);
			}
			if($request->hasFile('video')) {
				$saveArray['post_type']='video';
				$post_video = $request->file('video');
				$video = time().'.'.$post_video->getClientOriginalExtension();
				$destinationPath = public_path('/images/video');
				$post_video->move($destinationPath, $video);
			}

			$insertData = [
				'user_id'=>$saveArray['user_id'],
				'post_type'=>$saveArray['post_type'],
				'description'=>$saveArray['description'],
				'image'=>$image,
				'video'=>$video,
				'hash_tag'=>$saveArray['hash_tag'],
				'location'=>$saveArray['location'],
				'created_at'=>date('Y-m-d H:i:s')
			];
			$post_id = Post::insertGetId($insertData);
			if($post_id){
				return response()->json([ 
					'status' => true,
					'message' =>'Post added successfully',
					'post_id'=>(String)$post_id,
				], $this->successCode);
			}
			else{
				return response()->json([ 
					'status' => false,
					'message' => 'Error on adding post',
				], $this->successCode);
			}
		}	
	}
	
#************************************************************************************#	

	public function get_posts(Request $request){
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',
			'post_type' => 'required',
			'tagged'	=>'required'
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			$search=$saveArray['user_id'];
			if($saveArray['post_type'] =='text'){
				if($saveArray['tagged'] == 'N'){
					$posts = Post::where(['user_id'=>$saveArray['user_id']])->where('description', '<>',"")->get()->toarray();
				}
				else{
					$posts = Post::where('hash_tag','like','%'.$saveArray['user_id'].'%')->where('description', '<>',"")->get()->toarray();
				}
			}
			
			if($saveArray['post_type'] =='image'){
				if($saveArray['tagged'] == 'N'){
					$posts = Post::where(['user_id'=>$saveArray['user_id']])->where('image', '<>',"")->get()->toarray();
				}
				else{
					$posts = Post::where('hash_tag','like',$saveArray['user_id'])->where('image', '<>',"")->get()->toarray();
				}
			}
			
			if($saveArray['post_type'] =='video'){
				if($saveArray['tagged'] == 'N'){
					$posts = Post::where(['user_id'=>$saveArray['user_id']])->where('video', '<>',"")->get()->toarray();
				}
				else{
					$posts = Post::where('hash_tag','like',$saveArray['user_id'])->where('video', '<>',"")->get()->toarray();
				}
			}
			
			
			
		
			if($posts){
				
				foreach($posts as $posts){

					$tagged_arr = explode (",", $posts['hash_tag']);
					$getUsers = User::whereIn('_id', $tagged_arr)->select('first_name','last_name')->get(); 
					
					if($posts['description']==null){
						$posts['description']="";
					}
					if($posts['hash_tag']==null){
						$posts['hash_tag']="";
					}
					if($posts['location']==null){
						$posts['location']="";
					}
					
					if(!empty($posts['image'])){
						
						$posts['image']=url('public/images/image',$posts['image']);
					}else{
						$posts['image']="";
					}
					if(!empty($posts['video'])){
						
						$posts['video']=url('public/images/video',$posts['video']);
					}else{
						$posts['video']="";
					}
				
					$helper = new Helpers();
					
							$likes = Like::where(['user_id'=> $saveArray['user_id'],'post_id'=> $posts['_id']])->first() ;
			
							if($likes){
								$like_status=$likes->like_status;
							}else{
								
								$like_status='unlike';
							}
							$totalLikes = Like::where(['post_id'=> $posts['_id']])->where(['like_status'=>'like'])->count();
							$totalComment = Comment::where(['post_id'=> $posts['_id']])->count();
					$userdetails = User::where(['_id'=>$posts['user_id']])->select('first_name','last_name','profile_image')->first();
					$profile_image = "";
					if(strpos($userdetails['profile_image'], "http://") !== false){
							$profile_image = $userdetails['profile_image'];
						}
						else if(strpos($userdetails['profile_image'], "https://") !== false){
							$profile_image = $userdetails['profile_image'];
						}
						else{
							$profile_image = url('public/images',$userdetails['profile_image']);
						}
					$records[]=array(
						'post_id'=>$posts['_id'],
						'user_id'=>$posts['user_id'],
						'first_name'=>$userdetails['first_name'],
						'last_name'=>$userdetails['last_name'],
						'profile_image' => $profile_image,
						'description'=>$posts['description'],
						'hash_tag'=>$getUsers,
						'location'=>$posts['location'],
						'image'=>$posts['image'],
						'video'=>$posts['video'],
						'created_at'=>$helper->time_elapsed_string($posts['created_at']),
						'like_status'=>$like_status,
						'totalLikes'=>$totalLikes,
						'totalComment'=>$totalComment,
					);
				
				}
				
				return response()->json([ 
					'status' => true,
					'message' => 'Success',
					'data'=>$records,
				], $this->successCode);
			}
			else{
				return response()->json([ 
					'status' => false,
					'message' => 'Not found',
				], $this->successCode);
			}
		}

	}

	public function get_all_posts(Request $request){
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			
			$posts = Post::where(['user_id'=>$saveArray['user_id']]);
			$followList = Follow::where(['user_id'=>$saveArray['user_id']])->select('other_user_id')->get();
			foreach($followList as $follow){
			    	$posts->orwhere(['user_id'=>$follow['other_user_id']]);
			}
			$posts = $posts->orderBy('_id','DESC')->get()->toarray();
	
			if($posts){
				
				foreach($posts as $posts){
					$tagArray = [];
					$tagged_arr = explode (",", $posts['hash_tag']);
					$getUsers = User::whereIn('_id', $tagged_arr)->select('first_name','last_name')->get(); 
					
					if($posts['description']==null){
						$posts['description']="";
					}
					if($posts['hash_tag']==null){
						$posts['hash_tag']="";
					}
					if($posts['location']==null){
						$posts['location']="";
					}
					
					if(!empty($posts['image'])){
						
						$posts['image']=url('public/images/image',$posts['image']);
					}else{
						$posts['image']="";
					}
					if(!empty($posts['video'])){
						
						$posts['video']=url('public/images/video',$posts['video']);
					}else{
						$posts['video']="";
					}
				
					$helper = new Helpers();
					$userDetails = User::where(['_id'=>$posts['user_id']])->select('first_name','last_name','profile_image')->first();
					
							$likes = Like::where(['user_id'=> $saveArray['user_id'],'post_id'=> $posts['_id']])->first() ;
			
							if($likes){
								$like_status=$likes->like_status;
							}else{
								
								$like_status='unlike';
							}
							$totalLikes = Like::where(['post_id'=> $posts['_id']])->where(['like_status'=>'like'])->count();
							$totalComment = Comment::where(['post_id'=> $posts['_id']])->count();

							$profile_image = "";
					if(strpos($userDetails['profile_image'], "http://") !== false){
							$profile_image = $userDetails['profile_image'];
						}
						else if(strpos($userDetails['profile_image'], "https://") !== false){
							$profile_image = $userDetails['profile_image'];
						}
						else{
							$profile_image = url('public/images',$userDetails['profile_image']);
						}
							
					$records[]=array(
						'post_id'=>$posts['_id'],
						'user_id'=>$posts['user_id'],
						'first_name'=>$userDetails['first_name']?$userDetails['first_name']:'',
						'last_name'=>$userDetails['last_name']?$userDetails['last_name']:'',
						'profile_image' => $profile_image,
						'post_type'=>$posts['post_type'],
						'description'=>$posts['description'],
						'hash_tag'=>$getUsers,
						'location'=>$posts['location'],
						'image'=>$posts['image'],
						'video'=>$posts['video'],
						'created_at'=>$helper->time_elapsed_string($posts['created_at']),
						'like_status'=>$like_status,
						'totalLikes'=>$totalLikes,
						'totalComment'=>$totalComment,
					);
				
				}
				
				return response()->json([ 
					'status' => true,
					'message' => 'Success',
					'data'=>$records,
				], $this->successCode);
			}
			else{
				return response()->json([ 
					'status' => false,
					'message' => 'Not found',
				], $this->successCode);
			}
		}

	}

#************************************************************************************#	

	public function get_news(Request $request){
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			
			$news = News::orderBy('created_at','DESC')->get()->toarray();
	

			if($news){
				
				foreach($news as $news){
					
					$image = url('public/images',$news['image']);
					
					$helper = new Helpers();
						
					$records[]=array(
					'news_id'=>$news['_id'],
					'user_id'=>$news['user_id'],
					'title'=>$news['title'],
					'description'=>$news['description'],
					'image'=>$image,
					'created_at'=>$helper->time_elapsed_string($news['created_at']),
					);
				
				}
				
				return response()->json([ 
					'status' => true,
					'message' => 'Success',
					'data'=>$records,
				], $this->successCode);
			}
			else{
				return response()->json([ 
					'status' => false,
					'message' => 'Not found',
				], $this->successCode);
			}
		}

	}
	
#*****************************************************************************#

	public function getNewsDetail(Request $request){
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
			'news_id' => 'required',	
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			
			$news = News::where(['_id'=>$saveArray['news_id']])->first()->toarray();

			if($news){
				
					$image = url('public/images',$news['image']);
					
					$helper = new Helpers();
						
					$records[]=array(
					'news_id'=>$news['_id'],
					'user_id'=>$news['user_id'],
					'title'=>$news['title'],
					'description'=>$news['description'],
					'image'=>$image,
					'created_at'=>$helper->time_elapsed_string($news['created_at']),
					);
				
				
				
				return response()->json([ 
					'status' => true,
					'message' => 'Success',
					'data'=>$records,
				], $this->successCode);
			}
			else{
				return response()->json([ 
					'status' => false,
					'message' => 'Not found',
				], $this->successCode);
			}
		}

	}
	
#*****************************************************************************#	
	

	public function follow_user(Request $request){
		
		$saveArray = $request->all();
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',
			'other_user_id' => 'required',
			'follow_status' => 'required'
		]);
		if($validator->fails()) {
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			if($saveArray['follow_status'] == "Y"){
				$datetime = date('Y-m-d H:i:s');
				$already_follow = Follow::where(['user_id'=>$saveArray['user_id']])->where(['other_user_id'=>$saveArray['other_user_id']])->first();
				if(!$already_follow){
					$insertData = [
						'user_id'=>$saveArray['user_id'],
						'other_user_id'=>$saveArray['other_user_id'],
						'follow_status'=>$saveArray['follow_status'],
						'created_at'=>$datetime
					];

					$insert = Follow::insertGetId($insertData);
					if($insert){

						$userdetails = User::where(['_id'=>$saveArray['user_id']])->first();
						$otherUserdetails = User::where(['_id'=>$saveArray['other_user_id']])->first();
						if(empty($otherUserdetails))
						{
							
						$otherUserdetails = Trainer::where(['_id'=>$saveArray['other_user_id']])->first();

						}
						
						$device_type = $otherUserdetails->device_type;
						$device_token = $otherUserdetails->device_token;
						
						$user_image = "";
						if(strpos($userdetails['profile_image'], "http://") !== false){
							$user_image = $userdetails['profile_image'];
						}
						else if(strpos($userdetails['profile_image'], "https://") !== false){
							$user_image = $userdetails['profile_image'];
						}
						else{
							$user_image = url('public/images',$userdetails['profile_image']);
						}

						$message =[
							'message' =>'Started following you',
							'other_user_id' => $saveArray['other_user_id'],
							'user_id' => $saveArray['user_id'],
							'user_name' => $userdetails['first_name']." ".$userdetails['last_name'],
							'user_image' => $user_image,
							'noti_type' => 'follow',
							'datetime' => $datetime
						];

						if($otherUserdetails->device_token != ""){
							if($device_type == "A"){
								ApiController::android_send_notification($device_token,$message);
							}

							$noti_data = [
								'noti_type'=>'follow',
								'notified_id'=>$saveArray['other_user_id'],
								'message'=>'Started following you',
								'msg_json'=>json_encode($message),
								'created_at'=>$datetime
							];
							$notify = Notification::insertGetId($noti_data);

							$noti_read_data = [
								'user_id'=>$saveArray['other_user_id'],
								'noti_id'=>(String)$notify,
								'read_status'=>'0',
								'created_at'=>$datetime
							];
							$notify = readNotify::insert($noti_read_data);
						}

						return response()->json([ 
							'status' => true,
							'message' => 'Your are following this user successfully',
						], $this->successCode);
					}
					else{
						return response()->json([ 
							'status' => false,
							'message' => 'Something went wrong',
						], $this->successCode);
					}
				}
				else{
					return response()->json([ 
						'status' => false,
						'message' => 'Your already following this user',
					], $this->successCode);
				}
			}
			else if($saveArray['follow_status'] == "N"){
				$delete = Follow::where(['user_id'=>$saveArray['user_id']])->where(['other_user_id'=>$saveArray['other_user_id']])->delete();
				if($delete){
					return response()->json([ 
						'status' => true,
						'message' => 'Your are no longer following this user',
					], $this->successCode);
				}
				else{
					return response()->json([ 
						'status' => false,
						'message' => 'Something went wrong',
					], $this->successCode);
				}
			}
		}	
	}

	/* public function get_follow_list(Request $request){
		
		$saveArray = $request->all();
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required'
		]);
		if($validator->fails()) {
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			$followings = Follow::where(['user_id'=> $saveArray['user_id']])->get();
			
			$following = array();
			foreach($followings as $followings){
				// $user_ids = $followings->other_user_id;
				// $isFollowStatus = Follow::where(['user_id'=> $user_ids,'other_user_id'=> $saveArray['user_id']])->first('follow_status');
				$other_user_id = $followings->other_user_id;
				$user_ids = $followings->user_id;
			
				$isFollowStatus = Follow::where(['user_id'=> $user_ids,'other_user_id'=> $other_user_id])->first('follow_status');
				
				
				print_r($isFollowStatus);
				
				die();
				// if($isFollowStatus){
					
					
					// $is_follow="Y";
					
				// }else{
					
					// $is_follow="N";
				// }
				
				$users = User::where(['_id'=>$user_ids])->first();
				$following[]=array(
					'user_id'=>$users->_id?$users->_id:'',
					'first_name'=>$users->first_name?$users->first_name:'',
					'last_name'=>$users->last_name?$users->last_name:'',
					'email'=>$users->email?$users->email:'',
					'profile_image' => ($users)?($users->profile_image?url('public/images',$users->profile_image):''):'',
					'is_follow' => $is_follow,
				);
			}

			$followers = Follow::where(['other_user_id'=> $saveArray['user_id']])->get();
			$follower=array();
			foreach($followers as $followers){
				$user_ids=$followers->user_id;
				
				$isFollowStatus = Follow::where(['user_id'=> $saveArray['user_id'],'other_user_id'=> $user_ids])->first();
				if($isFollowStatus){
					
					$is_follow="Y";
				}else{
					
					$is_follow="N";
				}
				
				
				$users=User::where(['_id'=>$user_ids])->first() ;
				$follower[]=array(
					'user_id'=>$users->_id,
					'first_name'=>$users->first_name,
					'last_name'=>$users->last_name,
					'email'=>$users->email,
					'profile_image' => ($users)?($users->profile_image?url('public/images',$users->profile_image):''):'',
					'is_follow' => $is_follow,
				);
			}
			return response()->json([ 
				'status' => true,
				'message' => 'followers and following list',
				'following'=>$following,
				'follower'=>$follower
			], $this->successCode);
		}	
	}*/



	public function get_follow_list(Request $request){
		
		$saveArray = $request->all();
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required'
		]);
		if($validator->fails()) {
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			$followings = Follow::where(['user_id'=> $saveArray['user_id']])->get();
			
			
			
			$following = array();
			foreach($followings as $followings){
				
				$user_ids = $followings->other_user_id;
				
			$isFollowStatus = Follow::where(['user_id'=>$saveArray['user_id'],'other_user_id'=> $user_ids])->first();
	
				
				$is_follow= $isFollowStatus['follow_status'];
				$users = User::where(['_id'=>$user_ids])->first();
				$following[]=array(
					'user_id'=>$users->_id?$users->_id:'',
					'first_name'=>$users->first_name?$users->first_name:'',
					'last_name'=>$users->last_name?$users->last_name:'',
					'email'=>$users->email?$users->email:'',
					'profile_image' => ($users)?($users->profile_image?url('public/images',$users->profile_image):''):'',
					'is_follow' => $is_follow
				);
			}
			
			

			$followers = Follow::where(['other_user_id'=> $saveArray['user_id']])->get();
			

			$follower=array();
			foreach($followers as $followers){
				$user_ids=$followers->user_id;
				$isFollowStatus = Follow::where(['user_id'=>$user_ids ,'other_user_id'=> $saveArray['user_id']])->first();
				
				$me_status = Follow::where(['user_id'=>$saveArray['user_id'] ,'other_user_id'=> $user_ids])->first();
				
				if(!empty($me_status))
				{
				
				$is_follow= $isFollowStatus['follow_status'];
				}
				
				else
				{
					$is_follow= "N";
					
				}
				
				$users=User::where(['_id'=>$user_ids])->first() ;
				$follower[]=array(
					'user_id'=>$users->_id,
					'first_name'=>$users->first_name,
					'last_name'=>$users->last_name,
					'email'=>$users->email,
					'profile_image' => ($users)?($users->profile_image?url('public/images',$users->profile_image):''):'',
					'is_follow' => $is_follow,
				);
			}
			
			return response()->json([ 
				'status' => true,
				'message' => 'followers and following list',
				'following'=>$following,
				'follower'=>$follower
			], $this->successCode);
		}	
	}


#************************************************************************************#	

	public function addDiet(Request $request){
		
		$saveArray = $request->all();
		
			$insertData = [
				'user_id'=>$saveArray['user_id'],
				'name'=>$saveArray['name'],
				'description'=>$saveArray['description'],
				'image'=>'default.png',
				'period'=>$saveArray['period'],
				'amount'=>$saveArray['amount'],
				'created_at'=>date('Y-m-d H:i:s')
			];
			$diet_id = Diet::insertGetId($insertData);
			$helper = new Helpers();
			
			if($diet_id){
				return response()->json([
					'status' => true,
					'message' => 'Success',
					'diet'=>[
					'user_id'=>$saveArray['user_id'],
					'name'=>$saveArray['name'],
					'description'=>$saveArray['description'],
					//'image'=>$saveArray['image'],
					'period'=>$saveArray['period'],
					'amount'=>$saveArray['amount'],
					'created_at'=>date('Y-m-d H:i:s'),
					]
				], $this->successCode);
			}
			else{
				return response()->json([ 
					'status' => false,
					'message' => 'diet does not exists',
				], $this->successCode);
			}
			
	}

	public function removeDiet(Request $request)
	{
		$saveArray = $request->all();
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
			'diet_id' => 'required',	
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			$diet = DB::table('mydiets')->where('user_id', $saveArray['user_id'])->where('diet_id', $saveArray['diet_id'])->delete();
			if($diet){
				return response()->json([
					'status' => true,
					'message' => 'Success',
				], $this->successCode);
			}
			else{
				return response()->json([ 
					'status' => false,
					'message' => 'diet does not exists',
				], $this->successCode);
			}
		}
	}



public function removeWorkout(Request $request)
	{
		$saveArray = $request->all();
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
			'id' => 'required',
			'type'=>'required'
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
					
				    if($saveArray['type'] == 'running')
					{
					$myworkout = DB::table('myrunning')->where('user_id', $saveArray['user_id'])->where('running_id', $saveArray['id'])->delete();
					}
					
					elseif($saveArray['type'] == 'weightlifting')
					{
					$myworkout = DB::table('myweightlifting')->where('user_id', $saveArray['user_id'])->where('weightlifting_id', $saveArray['id'])->delete();
	
						
					}
					elseif($saveArray['type'] == 'yoga')
					{
					$myworkout = DB::table('myyoga')->where('user_id', $saveArray['user_id'])->where('yoga_id', $saveArray['id'])->delete();
						
					}
			
			
			
			if($myworkout){
				return response()->json([
					'status' => true,
					'message' => 'Success',
				], $this->successCode);
			}
			else{
				return response()->json([ 
					'status' => false,
					'message' => 'workout does not exists',
				], $this->successCode);
			}
		}
	}



#************************************************************************************#	

	public function get_diets(Request $request){
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
			'post_type' => 'required',	
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			
			if($saveArray['post_type']=='my_diets'){
				$diet = MyDiet::where(['user_id'=> $saveArray['user_id']])->get()->toarray();
	
				if($diet){
					
					foreach($diet as $diet){
						
						$diets = Diet::where(['_id'=> $diet['diet_id']])->first() ;
						
						$helper = new Helpers();
						
						$Workout = Workout::where(['_id'=> $diets['workout_id']])->first();
					   
					   if($Workout)
					   {
						   $workout_for = $Workout['page_for'];
					   }
					   else
					   {
						  $workout_for = "dummy diet";

					   }
						
						$userExist = Trainer::where(['_id'=> $diet['user_id']])->first();
						
						if($userExist){
							$by_user = $userExist->name;
						}else{
							$by_user = 'Testing';
						}
							
						$records[]=array(
						'diet_id'=>$diets['_id'],
						'user_id'=>$diets['user_id'],
						'by_user'=>$by_user,
						'name'=>$diets['name'],
						'description'=>$diets['description'],
						'image'=>url('public/images/diet',$diets['image']),
						'period'=>$diets['period'],
						'amount'=>$diets['amount'].' Cal/Day',
						'rating'=>$diets['rating'],
						'price'=>$diets['price'],
						'carb'=>$diets['carb'],
						'workout_for'=> $workout_for, 
						'workout_id'=> $diets['workout_id'], 
						'sugar'=>$diets['sugar'],
						'fat'=>$diets['fat'],
						'protein'=>$diets['protein'],
						'grocery_list'=>$diets['grocery_list'],
						'created_at'=>$helper->time_elapsed_string($diets['created_at']),
						);
					
					}
					
					return response()->json([ 
						'status' => true,
						'message' => 'Success',
						'data'=>$records,
					], $this->successCode);
				}
				else{
					return response()->json([ 
						'status' => false,
						'message' => 'Not found',
					], $this->successCode);
				}
			}else{
				
				$saveArray['status']=1;
				//$diets = Diet::get()->toarray();
				$diets = Diet::where('status', '=', 1)->get()->toarray();
				
				if($diets){
				
					foreach($diets as $diets){
						
                        $paymentdone = Payments::where(['user_id'=> $saveArray['user_id']])->where(['id'=> $diets['_id']])->first();
						$helper = new Helpers();
						
					   $Workout = Workout::where(['_id'=> $diets['workout_id']])->first();
					   
					   if($Workout)
					   {
						   $workout_for = $Workout['page_for'];
					   }
					   else
					   {
						  $workout_for = "dummy diet";

					   }

						$userExist = Trainer::where(['_id'=> $diets['user_id']])->first();
						if($userExist){
							$by_user = $userExist->name;
						}else{
							$by_user = 'Testing';
						}
							
						$records[]=array(
						'diet_id'=>$diets['_id'],
						'user_id'=>$diets['user_id'],
						'by_user'=>$by_user,
						'name'=>$diets['name'],
						'description'=>$diets['description'],
						'image'=>url('public/images/diet',$diets['image']),
						'period'=>$diets['period'],
						'amount'=>$diets['amount'].' Cal/Day',
						'workout_for'=> $workout_for, 
						'workout_id'=> $diets['workout_id'], 
						'price'=>$paymentdone!=null?'Free':$diets['price'],
						'rating'=>$diets['rating']?$diets['rating']:'',
						'created_at'=>$helper->time_elapsed_string($diets['created_at']),
						);
					
					}
					
					return response()->json([ 
						'status' => true,
						'message' => 'Success',
						'data'=>$records,
					], $this->successCode);
				}
				else{
					return response()->json([ 
						'status' => false,
						'message' => 'Not found',
					], $this->successCode);
				}
			}
			
	
			
		}

	}
	
#************************************************************************************#	

	public function my_diets(Request $request){
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			
			$diets = Diet::where(['user_id'=>$saveArray['user_id']])->get()->toarray();
	
			if($diets){
				
				foreach($diets as $diets){
					

					$helper = new Helpers();
						
					$records[]=array(
					'diet_id'=>$diets['_id'],
					'user_id'=>$diets['user_id'],
					'name'=>$diets['name'],
					'description'=>$diets['description'],
					'image'=>url('public/images/diet',$diets['image']),
					'period'=>$diets['period'],
					'amount'=>$diets['amount'].' Cal/Day',
					'created_at'=>$helper->time_elapsed_string($diets['created_at']),
					);
				
				}
				
				return response()->json([ 
					'status' => true,
					'message' => 'Success',
					'data'=>$records,
				], $this->successCode);
			}
			else{
				return response()->json([ 
					'status' => false,
					'message' => 'Not found',
				], $this->successCode);
			}
		}

	}


#************************************************************************************#	

	public function diet_detail(Request $request){
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
			'diet_id' => 'required',	
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			
			$diets = Diet::where(['_id'=>$saveArray['diet_id']])->get()->toarray();

			
			if($diets){
			   $checkdiet = MyDiet::where(['user_id'=>$saveArray['user_id']])->where(['diet_id'=>$saveArray['diet_id']])->where('diet_status','=', 1)->first();

if($checkdiet){
						$added = 1;
					}
					else{
						$added = 0;
					}


				foreach($diets as $diets){

					$comment_count = Comment::where(['post_id'=>$diets['_id']])->count();
					
					$userExist = User::where(['_id'=> $diets['user_id']])->first();
					if($userExist){
						$by_user = $userExist->first_name.' '.$userExist->last_name;
					}else{
							$userExist = Trainer::where(['_id'=> $diets['user_id']])->first();
							if($userExist)
							$by_user = $userExist->name;

					}
					
					$helper = new Helpers();
						
					$records=array(
					'diet_id'=>$diets['_id'],
					'user_id'=>$diets['user_id'],
					'by_user'=>$by_user,
					'name'=>$diets['name'],
					'description'=>$diets['description'],
					'image'=>url('public/images/diet',$diets['image']),
					'period'=>$diets['period'],
					'amount'=>$diets['amount'].' Cal/Day',
					'rating'=>$diets['rating'],
					'carb'=>$diets['carb'],
					'sugar'=>$diets['sugar'],
					'fat'=>$diets['fat'],
					'protein'=>$diets['protein'],
					'grocery_list'=>$diets['grocery_list'],
					
					'comment_count'=>$comment_count,
					'created_at'=>$helper->time_elapsed_string($diets['created_at']),
					'isAdded' => $added,
					);
				
				}
				
				return response()->json([ 
					'status' => true,
					'message' => 'Success',
					'data'=>$records,
				], $this->successCode);
			}
			else{
				return response()->json([ 
					'status' => false,
					'message' => 'Not found',
				], $this->successCode);
			}
		}

	}


#************************************************************************************#	

	public function addComment(Request $request){
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
			'post_id' => 'required',	
			'comment' => 'required',	
			'type' => 'required'
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
		
		$saveArray = $request->all();
		
		if(empty($saveArray['parent_id'])){
			
			$saveArray['parent_id']="";
		}
		
			$insertData = [
				'user_id'=>$saveArray['user_id'],
				'parent_id'=>$saveArray['parent_id'],
				'post_id'=>$saveArray['post_id'],
				'comment'=>$saveArray['comment'],
				'type'=>$saveArray['type'],
				'delete_status'=>'1',
				'created_at'=>date('Y-m-d H:i:s')
			];
			$comment_id = Comment::insertGetId($insertData);
				return response()->json([ 
					'status' => true,
					'message' => 'Success',
				], $this->successCode);
			
		}
	}


#************************************************************************************#	

		public function addLike(Request $request){
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
			'post_id' => 'required',	
			'like_status' => 'required',	
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			
			$likes = Like::where(['user_id'=> $saveArray['user_id'],'post_id'=> $saveArray['post_id']])->first() ;
			
				if($likes){ 
			
					Like::where(['user_id'=> $saveArray['user_id'],'post_id'=> $saveArray['post_id']])->update(['like_status'=>$saveArray['like_status']]);
		        	
	
				}else{
					
					$saveArray = $request->all();
		
					$insertData = [
						'user_id'=>$saveArray['user_id'],
						'post_id'=>$saveArray['post_id'],
						'like_status'=>$saveArray['like_status'],
						'created_at'=>date('Y-m-d H:i:s')
					];
					$like_id = Like::insertGetId($insertData);
			
				}
		
				return response()->json([ 
					'status' => true,
					'message' => 'Success',
					'like_status' => $saveArray['like_status'],
				], $this->successCode);
			
		}
	}


#************************************************************************************#	

	public function getComment(Request $request){
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
			'post_id' => 'required',	
			'type' => 'required'	
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			$comments = Comment::where(['post_id'=>$saveArray['post_id'],'parent_id'=>""])->where(['type'=>$saveArray['type']])->get()->toarray();
	
			if($comments){
				
				$records=array();
				foreach($comments as $comments){
					
					$helper = new Helpers();
					
						$commentUserExist = User::where(['_id'=>$comments['user_id']])->first();
						
						$commentUserImage = "";
						if(strpos($commentUserExist['profile_image'], "http://") !== false){
							$commentUserImage = $commentUserExist['profile_image'];
						}
						else if(strpos($commentUserExist['profile_image'], "https://") !== false){
							$commentUserImage = $commentUserExist['profile_image'];
						}
						else{
							$commentUserImage = url('public/images',$commentUserExist['profile_image']);
						}
					
					
					$reply = Comment::where(['parent_id'=>$comments['_id']])->get()->toarray();
					
					$records1=array();
					
					foreach($reply as $reply){
						
						
							$replyUserExist = User::where(['_id'=>$reply['user_id']])->first();
				
							$replyUserImage = "";
							if(strpos($replyUserExist['profile_image'], "http://") !== false){
								$replyUserImage = $replyUserExist['profile_image'];
							}
							else if(strpos($replyUserExist['profile_image'], "https://") !== false){
								$replyUserImage = $replyUserExist['profile_image'];
							}
							else{
								$replyUserImage = url('public/images',$replyUserExist['profile_image']);
							}
						
						$records1[]=array(
						'comment_id'=>$reply['_id'],
						'user_id'=>$reply['user_id'],
						'userName'=>ucwords($replyUserExist['first_name'].' '.$replyUserExist['last_name']),
						'userImage'=>$replyUserImage,
						'comment'=>$reply['comment'],
						'created_at'=>$helper->time_elapsed_string($reply['created_at']),
						);
						
					}
					
					
						
					$records[]=array(
					'comment_id'=>$comments['_id'],
					'user_id'=>$comments['user_id'],
					'userName'=>ucwords($commentUserExist['first_name'].' '.$commentUserExist['last_name']),
					'userImage'=>$commentUserImage,
					'comment'=>$comments['comment'],
					'created_at'=>$helper->time_elapsed_string($comments['created_at']),
					'reply'=>$records1,
					);
				
				}
				
				return response()->json([ 
					'status' => true,
					'message' => 'Success',
					'data'=>$records,
				], $this->successCode);
			}
			else{
				return response()->json([ 
					'status' => false,
					'message' => 'Not found',
				], $this->successCode);
			}
		}

	}



#************************************************************************************#	

	public function getForYoga(Request $request){
		
			$saveArray = $request->all();
		
			$validator = Validator::make($request->all(), [ 
				'user_id' => 'required',	
				'type' => 'required',	
			]);
			
			if($validator->fails()) {
				
				return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);     
				
			}else{
				
					$saveArray['page_for']='yoga';
					$saveArray['approval']='approved';

					if($saveArray['type'] =='exercise'){
						
						$category = Category::get()->toarray();						
						$records=array();						
						if($category){

							foreach($category as $category){
								
								$workouts = Workout::where(['page_for'=>$saveArray['page_for'],'type'=>$saveArray['type'],'approval'=>$saveArray['approval'],'category'=>$category['_id']])->get()->toarray();

								$record1=array();
								
								foreach($workouts as $workouts){
									
									$trainerDetails = Trainer::where(['_id'=>$workouts['user_id']])->first();
									$rating = RateWorkout::where(['workout_id'=>$workouts['_id']])->avg('rating');
									$image = url('public/images',$workouts['image']);

									$helper = new Helpers();
										
									$records[]=array(
									'cat_name'=>$category['category'],
									'workout_id'=>$workouts['_id'],
									'user_id'=>$workouts['user_id'],
									'by'=>$trainerDetails?ucwords($trainerDetails->name):'Admin',
									'title'=>ucwords($workouts['title']),
									'description'=>$workouts['description'],
									'image'=>$image,
									'exercises'=>$workouts['exercises'],
									'time'=>$workouts['time'],
									'created_at'=>$workouts['created_at'],
									'rating'=>$rating?$rating:0,
									'ago'=>$helper->time_elapsed_string($workouts['created_at']),
									);
								
								}
						
							}
						
						}else{
							
							return response()->json([ 
								'status' => false,
								'message' => 'Not found',
							], $this->successCode);
						}
					}
		
					if( $saveArray['type'] == 'workout' || $saveArray['type'] =='myworkout'){
						if($saveArray['type'] =='workout'){	

							$workouts = Workout::where(['page_for'=>$saveArray['page_for'],'type'=>$saveArray['type'],'approval'=>$saveArray['approval']])->get()->toarray();

							if($workouts){
							
								foreach($workouts as $workouts){
									
									$trainerDetails = Trainer::where(['_id'=>$workouts['user_id']])->first();
									$rating = RateWorkout::where(['workout_id'=>$workouts['_id']])->avg('rating');
									$image = url('public/images',$workouts['image']);

									$helper = new Helpers();
										
									$records[]=array(
										'workout_id'=>$workouts['_id'],
										'user_id'=>$workouts['user_id'],
										'by'=>$trainerDetails?ucwords($trainerDetails->name):'Admin',
										'title'=>ucwords($workouts['title']),
										'description'=>$workouts['description'],
										'price'=>$workouts['amount'],
										'image'=>$image,
										//'exercises'=>$workouts['exercises'],
										'exercises'=>$workouts['exercises']==""?null:$workouts['exercises'],
										'time'=>$workouts['time'],
										'period'=>$workouts['period'],
										'created_at'=>$workouts['created_at'],
										'rating'=>$rating?$rating:0,
										'ago'=>$helper->time_elapsed_string($workouts['created_at']),
									);
									
								}
								
							}else{
								return response()->json([ 
									'status' => false,
									'message' => 'Not found',
								], $this->successCode);
							}	

						}
						
						if($saveArray['type'] =='myworkout'){

							$yogas = MyYoga::where(['user_id'=> $saveArray['user_id']])->get()->toarray();

							if($yogas){
							
								foreach($yogas as $workouts){

									$workouts = Workout::where(['_id'=> $workouts['yoga_id']])->first();
									
									$trainerDetails = Trainer::where(['_id'=>$workouts['user_id']])->first();
									$rating = RateWorkout::where(['workout_id'=>$workouts['yoga_id']])->avg('rating');
									$image = url('public/images',$workouts['image']);
									$helper = new Helpers();
									$records[]=array(
										'workout_id'=>$workouts['_id'],
										'user_id'=>$workouts['user_id'],
										'by'=>$trainerDetails?ucwords($trainerDetails->name):'Admin',
										'title'=>ucwords($workouts['title']),
										'description'=>$workouts['description'],
										'image'=>$image,
										'exercises'=>$workouts['exercises']==""?null:$workouts['exercises'],
										//'exercises'=>$workouts['exercises'],
										'time'=>$workouts['time'],
										'period'=>$workouts['period'],
										'created_at'=>(String)$workouts['created_at'],
										'rating'=>$rating?$rating:0,
										'ago'=>$helper->time_elapsed_string($workouts['created_at']),
									);
								}
								
							}else{
								return response()->json([ 
									'status' => false,
									'message' => 'Not found',
								], $this->successCode);
							}
							
						}
					
						
					}
			
				return response()->json([ 
					'status' => true,
					'message' => 'Success',
					'data'=>$records,
				], $this->successCode);
	
		}

	}




#************************************************************************************#	





	public function getForWeightLifting(Request $request){
		
			$saveArray = $request->all();
		
			$validator = Validator::make($request->all(), [ 
				'user_id' => 'required',	
				'type' => 'required',	
				//'page_for' => 'required',	
			]);
			
			if($validator->fails()) {
				
				return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);     
				
			}else{
			
					$saveArray['page_for']='weightlifting';
					$saveArray['approval']='approved';

					if($saveArray['type'] =='exercise'){

						$category = WlCategory::get()->toarray();
						
						$records=array();
						
						if($category){

							foreach($category as $category){
								
								$workouts = Workout::where(['page_for'=>$saveArray['page_for'],'type'=>$saveArray['type'],'category'=>$category['_id']])->get()->toarray();
								//$workouts = Workout::where(['type'=>$saveArray['type'],'category'=>$category['_id']])->get()->toarray();

								$record1=array();
								//$records=array();
								
								foreach($workouts as $workouts){
									
									$trainerDetails = Trainer::where(['_id'=>$workouts['user_id']])->first();
									$rating = RateWorkout::where(['workout_id'=>$workouts['_id']])->avg('rating');
									$image = url('public/images',$workouts['image']);

									$helper = new Helpers();
										
									$records[]=array(
										//'category'=>$workouts['category'],
										'cat_name'=>$category['category'],
										'workout_id'=>$workouts['_id'],
										'user_id'=>$workouts['user_id'],
										'by'=>$trainerDetails?ucwords($trainerDetails->name):'Admin',
										'title'=>ucwords($workouts['title']),
										'description'=>$workouts['description'],
										'image'=>$image,
										'exercises'=>"",
										'time'=>"",
										'created_at'=>$workouts['created_at'],
										'rating'=>$rating?$rating:0,
										'ago'=>$helper->time_elapsed_string($workouts['created_at']),
									);
								
								}

								/*$records[]=array(
								'cat_id'=>$category['_id'],
								'cat_name'=>$category['name'],
								'records'=>$record1,
								
								);*/
						
							}
						
						}else{
							
							return response()->json([ 
								'status' => false,
								'message' => 'Not found',
							], $this->successCode);
						}
					}
		
					if( $saveArray['type'] == 'workout' || $saveArray['type'] =='myworkout'){
						
						if($saveArray['type'] =='workout'){	

							$workouts = Workout::where(['page_for'=>$saveArray['page_for'],'type'=>$saveArray['type'], 'approval'=>$saveArray['approval']])->get()->toarray();
							if($workouts){
							
								foreach($workouts as $workouts){
									
									$trainerDetails = Trainer::where(['_id'=>$workouts['user_id']])->first();
									
									$rating = RateWorkout::where(['workout_id'=>$workouts['_id']])->avg('rating');
									$image = url('public/images',$workouts['image']);

									$helper = new Helpers();
										
									$records[]=array(
										'workout_id'=>$workouts['_id'],
										'user_id'=>$workouts['user_id'],
										'by'=>$trainerDetails?ucwords($trainerDetails->name):'Admin',
										'title'=>ucwords($workouts['title']),
										'description'=>$workouts['description'],
										'price'=>$workouts['amount'],
										'image'=>$image,
										//'exercises'=>$workouts['exercises'],
										'exercises'=>$workouts['exercises']==""?null:$workouts['exercises'],
										'time'=>$workouts['time'],
										'period'=>$workouts['period'],
										'created_at'=>$workouts['created_at'],
										'rating'=>$rating?$rating:0,
										'ago'=>$helper->time_elapsed_string($workouts['created_at']),
									);
									
								}
								
							}else{
								return response()->json([ 
									'status' => false,
									'message' => 'Not found',
								], $this->successCode);
							}	

						}
						
						if($saveArray['type'] =='myworkout'){	

							$weightliftings = MyWeightLifting::where(['user_id'=> $saveArray['user_id']])->get()->toarray();
							if($weightliftings){
								foreach($weightliftings as $weightlifting){
									$workouts = Workout::where(['_id'=> $weightlifting['weightlifting_id']])->first();
									
									$trainerDetails = Trainer::where(['_id'=>$workouts['user_id']])->first();
									$rating = RateWorkout::where(['workout_id'=>$weightlifting['weightlifting_id']])->avg('rating');
									$image = url('public/images',$workouts['image']);
						
									$helper = new Helpers();
										
									$records[]=array(
										'workout_id'=>$workouts['_id'],
										'user_id'=>$workouts['user_id'],
										'by'=>$trainerDetails?ucwords($trainerDetails->name):'Admin',
										'title'=>ucwords($workouts['title']),
										'description'=>$workouts['description'],
										'image'=>$image,
										'price'=>$workouts['amount'],
										//'exercises'=>$workouts['exercises'],
										'exercises'=>$workouts['exercises']==""?null:$workouts['exercises'],
										'time'=>$workouts['time'],
										'period'=>$workouts['period'],
										'created_at'=>(String)$workouts['created_at'],
										'rating'=>$rating?$rating:0,
										'ago'=>$helper->time_elapsed_string($workouts['created_at']),
									);
								}
							}
							else{
								return response()->json([ 
									'status' => false,
									'message' => 'Not found',
								], $this->successCode);
							}
							
							//$saveArray['type']='workout';
								
							//$workouts = Workout::where(['page_for'=>$saveArray['page_for'],'type'=>$saveArray['type'],'user_id'=>$saveArray['user_id']])->get()->toarray();
							
						}
					
						
					}
			
				return response()->json([ 
					'status' => true,
					'message' => 'Success',
					'data'=>$records,
				], $this->successCode);
	
		}

	}


#************************************************************************************#	

	public function changeAccountType(Request $request){
		$saveArray = $request->all();
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',
			'account_type' => 'required'
		]);
		if($validator->fails()) {
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			$user_exist = User::where(['_id'=>$saveArray['user_id']])->first();
			
			if($user_exist){
				$updateData = [
					'account_type'=>$saveArray['account_type'],
				];

				$update = User::where(['_id'=>$saveArray['user_id']])->update($updateData);
				
			

			if($update){
				$updateSetting= ['make_account_private'=>$saveArray['account_type']=='private'?1:0];
				
				$updateAccountSetting = Setting::where(['user_id'=>$saveArray['user_id']])->update($updateSetting);

				return response()->json([ 
					'status' => true,
					'message' => 'Updated Successfully.',
				], $this->successCode);
			}
			else{
				return response()->json([ 
					'status' => false,
					'message' => 'Error on profile updation',
				], $this->successCode); 
			}
			}else{
				return response()->json([ 
					'status' => false,
					'message' => 'Invalid User.',
				], $this->successCode); 
				
			}
		}
	}




#************************************************************************************#	

	public function AddBlockUnblock(Request $request){
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
			'other_user_id' => 'required',	
			'block_status' => 'required',	
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			
			$block = Block::where(['user_id'=> $saveArray['user_id'],'other_user_id'=> $saveArray['other_user_id']])->first() ;
			
				if($block){ 
			
					Block::where(['user_id'=> $saveArray['user_id'],'other_user_id'=> $saveArray['other_user_id']])->update(['block_status'=>$saveArray['block_status']]);
		        	
	
				}else{
					
					$saveArray = $request->all();
		
					$insertData = [
						'user_id'=>$saveArray['user_id'],
						'other_user_id'=>$saveArray['other_user_id'],
						'block_status'=>$saveArray['block_status'],
						'created_at'=>date('Y-m-d H:i:s')
					];
					$block_id = Block::insertGetId($insertData);
			
				}
		
				return response()->json([ 
					'status' => true,
					'message' => 'Success',
					'block_status' => $saveArray['block_status'],
				], $this->successCode);
			
		}
	}



#************************************************************************************#	


	public function searchUser(Request $request){
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'name' => 'required',	
		//	'other_user_id' => 'required',	
			//'block_status' => 'required',	
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			
			$user_exist = User::where('first_name', 'like', $saveArray["name"].'%')->get()->toarray();
			
			//print_R($users);die;
		
				if($user_exist){ 
				
				foreach($user_exist as $user_exist){
					
					
					$followerCount = Follow::where(['other_user_id'=> $user_exist['_id']])->count();
					$followingCount = Follow::where(['user_id'=> $user_exist['_id']])->count();

					$profile_image = "";
					if(strpos($user_exist['profile_image'], "http://") !== false){
						$profile_image = $user_exist['profile_image'];
					}
					else if(strpos($user_exist['profile_image'], "https://") !== false){
						$profile_image = $user_exist['profile_image'];
					}
					else{
						$profile_image = url('public/images',$user_exist['profile_image']);
					}
			
					$records[]=array(
						'user_id'=>(String)$user_exist['_id'],
						'login_type'=>$user_exist['login_type'],
						'social_id'=>$user_exist['social_id']?$user_exist['social_id']:'',
						'first_name'=>$user_exist['first_name'],
						'last_name'=>$user_exist['last_name'],
						'email'=>$user_exist['email'],
						'location'=>$user_exist['location'],
						'profile_image'=>$profile_image,
						'verified'=>'Y',
						//'age'=>$user_exist->age?$user_exist->age:'',
						//'bio'=>$user_exist->bio?$user_exist->bio:'',
						//'body_mass'=>$user_exist->body_mass?$user_exist->body_mass:'',
						//'goal'=>$goal,
						//'height'=>$user_exist['height']?$user_exist['height']:'',
						//'weight'=>$user_exist['weight']?$user_exist['weight']:'',
						'followerCount'=>$followerCount,
						'followingCount'=>$followingCount,
					);
					
					
				
				}
				
				return response()->json([ 
					'status' => true,
					'message' => 'Success',
					'data'=>$records,
				], $this->successCode);
			}
			else{
				return response()->json([ 
					'status' => false,
					'message' => 'Not found',
				], $this->successCode);
			}
		
				
			
		}
	}
	
#************************************************************************************#	


	public function addFavorite(Request $request){
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
			'other_user_id' => 'required',	
			'favorite_status' => 'required',	
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			
			$favorites = Favorite::where(['user_id'=> $saveArray['user_id'],'other_user_id'=> $saveArray['other_user_id']])->first() ;
			
				if($favorites){ 
			
					Favorite::where(['user_id'=> $saveArray['user_id'],'other_user_id'=> $saveArray['other_user_id']])->update(['favorite_status'=>$saveArray['favorite_status']]);
		        	
	
				}else{
					
					$saveArray = $request->all();
		
					$insertData = [
						'user_id'=>$saveArray['user_id'],
						'other_user_id'=>$saveArray['other_user_id'],
						'favorite_status'=>$saveArray['favorite_status'],
						'created_at'=>date('Y-m-d H:i:s')
					];
					$favorite_id = Favorite::insertGetId($insertData);
			
				}
		
				return response()->json([ 
					'status' => true,
					'message' => 'Success',
					'favorite_status' => $saveArray['favorite_status'],
				], $this->successCode);
			
		}
	}


#************************************************************************************#	


	public function myFavoriteList(Request $request){
	
	$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			
			$favorites = Favorite::where(['user_id'=>$saveArray['user_id'],'favorite_status'=>'favorite'])->get()->toarray();
			//$diets = Diet::get()->toarray();
	
			if($favorites){
				
				foreach($favorites as $favorites){
					
					$user_exist = User::where(['_id'=>$favorites['other_user_id']])->first()->toarray();
					
					//print_R($user_exist);die;
					$profile_image = "";
					if(strpos($user_exist['profile_image'], "http://") !== false){
						$profile_image = $user_exist['profile_image'];
					}
					else if(strpos($user_exist['profile_image'], "https://") !== false){
						$profile_image = $user_exist['profile_image'];
					}
					else{
						$profile_image = url('public/images',$user_exist['profile_image']);
					}
			
					$records[]=array(
						'user_id'=>(String)$user_exist['_id'],
						//'login_type'=>$user_exist['login_type'],
						//'social_id'=>$user_exist['social_id']?$user_exist['social_id']:'',
						'first_name'=>$user_exist['first_name'],
						'last_name'=>$user_exist['last_name'],
						'email'=>$user_exist['email'],
						'location'=>$user_exist['location'],
						'profile_image'=>$profile_image,
						'verified'=>'Y',
						//'age'=>$user_exist->age?$user_exist->age:'',
						//'bio'=>$user_exist->bio?$user_exist->bio:'',
						//'body_mass'=>$user_exist->body_mass?$user_exist->body_mass:'',
						//'goal'=>$goal,
						//'height'=>$user_exist['height']?$user_exist['height']:'',
						//'weight'=>$user_exist['weight']?$user_exist['weight']:'',
						//'followerCount'=>$followerCount,
						//'followingCount'=>$followingCount,
					);
				
				}
				
				return response()->json([ 
					'status' => true,
					'message' => 'Success',
					'data'=>$records,
				], $this->successCode);
			}
			else{
				return response()->json([ 
					'status' => false,
					'message' => 'Not found',
				], $this->successCode);
			}
		}
	}


#************************************************************************************#	

	 public function changePassword(Request $request){
		 
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
			'old_password' => 'required',	
			'new_password' => 'required',	
		]);
		
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode); 
			
		}else{
			
			$userExist = User::where(['_id'=> $saveArray['user_id']])->first();
			
	        if($userExist){ 
			
				if(Hash::check($saveArray['old_password'],$userExist['password'])){
		
					$new_password = Hash::make($saveArray['new_password']);
				
					$update_password = [
					'password' => $new_password,
					'updated_at' => date('Y-m-d h:i:s')];
				
				
					$update = User::where(['_id'=>$saveArray['user_id']])->update($update_password);
					
					if($update){

						return response()->json(['status'=>true,'message'=>'Password Changed Successfully.'], $this->successCode);
					}
			
				}else{
					return response()->json(['message'=>'User and Password is incorrect','status'=>false], $this->successCode); 
				}
			}else{
				return response()->json(['message'=>'Invalid user id','status'=>false], $this->successCode); 
			}

		}
	}

#************************************************************************************#	


	public function getBlockUserList(Request $request){
	
	$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			
			$block = Block::where(['user_id'=>$saveArray['user_id'],'block_status'=>'block'])->get()->toarray();
			//$diets = Diet::get()->toarray();
	
			if($block){
				
				foreach($block as $block){
					
					$user_exist = User::where(['_id'=>$block['other_user_id']])->first()->toarray();
					
					//print_R($user_exist);die;
					$profile_image = "";
					if(strpos($user_exist['profile_image'], "http://") !== false){
						$profile_image = $user_exist['profile_image'];
					}
					else if(strpos($user_exist['profile_image'], "https://") !== false){
						$profile_image = $user_exist['profile_image'];
					}
					else{
						$profile_image = url('public/images',$user_exist['profile_image']);
					}
			
					$records[]=array(
						'user_id'=>(String)$user_exist['_id'],
						//'login_type'=>$user_exist['login_type'],
						//'social_id'=>$user_exist['social_id']?$user_exist['social_id']:'',
						'first_name'=>$user_exist['first_name'],
						'last_name'=>$user_exist['last_name'],
						'email'=>$user_exist['email'],
						'location'=>$user_exist['location'],
						'profile_image'=>$profile_image,
						'verified'=>'Y',
						//'age'=>$user_exist->age?$user_exist->age:'',
						//'bio'=>$user_exist->bio?$user_exist->bio:'',
						//'body_mass'=>$user_exist->body_mass?$user_exist->body_mass:'',
						//'goal'=>$goal,
						//'height'=>$user_exist['height']?$user_exist['height']:'',
						//'weight'=>$user_exist['weight']?$user_exist['weight']:'',
						//'followerCount'=>$followerCount,
						//'followingCount'=>$followingCount,
					);
				
				}
				
				return response()->json([ 
					'status' => true,
					'message' => 'Success',
					'data'=>$records,
				], $this->successCode);
			}
			else{
				return response()->json([ 
					'status' => false,
					'message' => 'Not found',
				], $this->successCode);
			}
		}
	}


#************************************************************************************#	


	public function getWorkoutDeatils(Request $request){
		
			$saveArray = $request->all();
		
			$validator = Validator::make($request->all(), [ 
				'user_id' => 'required',	
				'workout_id' => 'required',		
			]);
			
			if($validator->fails()) {
				
				return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);     
				
			}else{
					$saveArray['approval']="approved";
					
					$workouts = Workout::where(['_id'=>$saveArray['workout_id']])->first()->toarray();
					
				    if($workouts['page_for'] == 'running')
					{
					$checkworkout = MyRunning::where(['user_id'=>$saveArray['user_id']])->where('running_id','=',$saveArray['workout_id'] )->where('running_status','=', 1)->first();
					//print_r($checkworkout);die();
					}
					
					elseif($workouts['page_for'] == 'weightlifting')
					{
										$checkworkout = MyWeightLifting::where(['user_id'=>$saveArray['user_id']])->where('weightlifting_id','=',$saveArray['workout_id'] )->where('weightlifting_status','=', 1)->first();
	
						
					}
										elseif($workouts['page_for'] == 'yoga')
					{
										$checkworkout = MyYoga::where(['user_id'=>$saveArray['user_id']])->where('yoga_id','=',$saveArray['workout_id'] )->where('yoga_status','=', 1)->first();
	
						
					}
					
					if($checkworkout){
						$added = 1;
					}
					else{
						$added = 0;
					}
						if($workouts){

								$rating = RateWorkout::where(['workout_id'=>$workouts['_id']])->avg('rating');
							
								
								$trainerDetails = Trainer::where(['_id'=>$workouts['user_id']])->first();
								
								$image = url('public/images',$workouts['image']);

								$helper = new Helpers();
								
								$totalComment = Comment::where(['post_id'=> $workouts['_id']])->count();
									
								$records=array(
								'workout_id'=>$workouts['_id'],
								'user_id'=>$workouts['user_id'],
								'by'=>ucwords($trainerDetails->name),
								'title'=>ucwords($workouts['title']),
								'description'=>$workouts['description'],
								'image'=>$image,
								//'exercises'=>$workouts['exercises'],
								'exercises'=>$workouts['exercises']==""?null:$workouts['exercises'],
								'time'=>$workouts['time'],
							      'period'=>$workouts['period'],
								 //'period'=>3,
								 'link_workout'=>$workouts['link_workout']?$workouts['link_workout']:'none',
								'created_at'=>$workouts['created_at'],
								'rating'=>$rating?$rating:0,
								'ago'=>$helper->time_elapsed_string($workouts['created_at']),
								'totalComment'=>$totalComment,
								'idAdded' => $added,
								);
								
							return response()->json([ 
							'status' => true,
							'message' => 'Success',
							'data'=>$records,
							], $this->successCode);
							
						}else{
							return response()->json([ 
								'status' => false,
								'message' => 'Not found',
							], $this->successCode);
						}	
					}
			
	}

	
#************************************************************************************#	

	public function getExcerciseDetails(Request $request){
		
			$saveArray = $request->all();
		
			$validator = Validator::make($request->all(), [ 
				'user_id' => 'required',	
				'exercise_id' => 'required',		
			]);
			
			if($validator->fails()) {
				
				return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);     
				
			}else{
			

					$workouts = Workout::where(['_id'=>$saveArray['exercise_id']])->first()->toarray();
					
					//print_R($workouts);die;

						if($workouts){
							
								//$category = Category::get()->toarray();
								
								$category = WlCategory::where(['_id'=>$workouts['category']])->first()->toarray();;
								
								$trainerDetails = Trainer::where(['_id'=>$workouts['user_id']])->first();
								
								$image = url('public/images',$workouts['image']);

								$helper = new Helpers();
									
									$records=array(
									'category'=>$category['category'],
									'workout_id'=>$workouts['_id'],
									'user_id'=>$workouts['user_id'],
									'by'=>ucwords($trainerDetails->name),
									'title'=>ucwords($workouts['title']),
									'description'=>$workouts['description'],
									'image'=>$image,
									'exercises'=>"",
									'time'=>"",
									'created_at'=>$workouts['created_at'],
									'rating'=>$workouts['rating'],
									'ago'=>$helper->time_elapsed_string($workouts['created_at']),
									);
								
							return response()->json([ 
							'status' => true,
							'message' => 'Success',
							'data'=>$records,
							], $this->successCode);
							
						}else{
							return response()->json([ 
								'status' => false,
								'message' => 'Not found',
							], $this->successCode);
						}	
					}
			
	}
	
#************************************************************************************#	


	public function reportUserPost(Request $request){
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
			'post_id' => 'required',		
			'report_text' => 'required',		
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			
			$report = Report::where(['user_id'=> $saveArray['user_id'],'post_id'=> $saveArray['post_id']])->first() ;
			
				if($report){ 
			
							return response()->json([ 
								'status' => false,
								'message' => 'Already Reported',
							], $this->successCode);
	
				}else{
					
					$saveArray = $request->all();
		
					$insertData = [
						'user_id'=>$saveArray['user_id'],
						'post_id'=>$saveArray['post_id'],
						'report_text'=>$saveArray['report_text'],
						'created_at'=>date('Y-m-d H:i:s')
					];
					$report_id = Report::insertGetId($insertData);
			
				}
		
				return response()->json([ 
					'status' => true,
					'message' => 'Success',
				], $this->successCode);
			
		}
	}


#************************************************************************************#	


	 public function makeGymPartnerOnOff(Request $request){
		 
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
			'gym_partner_status' => 'required',	
		]);
		
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode); 
			
		}else{
			
			$userExist = User::where(['_id'=> $saveArray['user_id']])->first();
			
	        if($userExist){ 
	
					$updateData = [
					'gym_partner_status' => $saveArray['gym_partner_status'],
					'updated_at' => date('Y-m-d h:i:s')];
				
				Setting::where(['user_id'=>$saveArray['user_id']])->update([
					'gym_partner_status' => $saveArray['gym_partner_status']]);
					$update = User::where(['_id'=>$saveArray['user_id']])->update($updateData);
					
					if($update){

						return response()->json(['status'=>true,'message'=>'Success'], $this->successCode);
					}
			
				
			}else{
				return response()->json(['message'=>'Invalid User.','status'=>false], $this->successCode); 
			}

		}
	}
	
	
	
	
	public function get_setting(Request $request)
	{
	    $saveArray = $request->all();

		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		
		$user_id = $saveArray['user_id'];
		if(!empty($user_id))
		{
			$setting = Setting::where('user_id','=', $user_id)->first();
			if($setting)
			{
				return response()->json([
					'success' => true,
					'data'=>$setting 
				]);
			}
		}
	}
	
	
	public function updateRunSetting(Request $request)
	{
		
		$saveArray = $request->all();

		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		
		$user_id = $saveArray['user_id'];
		
		$updateData= [

		'unit_run'=>$saveArray['unit_run'],
		'audio_case_run'=>$saveArray['audio_case_run'],
		'run_distance_val'=>$saveArray['run_distance_val']
		];
		
			$setting = Setting::where('user_id','=', $user_id)->update($updateData);
			if($setting)
			{
				return response()->json([
					'success' => true
					
				]);
			}
		
		
		
	}
	
	
	public function updateWeightLiftingSetting(Request $request)
	{
		
		$saveArray = $request->all();

		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		
		$user_id = $saveArray['user_id'];
		
		$updateData= [

		'unit_wl'=>$saveArray['unit_wl'],
		'audio_cues_wl'=>$saveArray['audio_cues_wl']
		];
		
			$setting = Setting::where('user_id','=', $user_id)->update($updateData);
			if($setting)
			{
				return response()->json([
					'success' => true
					
				]);
			}
	}
	
	
	
	
	public function updatePushNotificationSetting(Request $request)
	{
		
		$saveArray = $request->all();

		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		
		$user_id = $saveArray['user_id'];
		
		$updateData= [
		'push_notification'=>$saveArray['push_notification']
		];
		
			$setting = Setting::where('user_id','=', $user_id)->update($updateData);
			if($setting)
			{
				return response()->json([
					'success' => true
					
				]);
			}
	}
	
	
	
	public function updateEmailNotificationSetting(Request $request)
	{
		
		$saveArray = $request->all();

		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		
		$user_id = $saveArray['user_id'];
		
		$updateData= [
		'email_notification'=>$saveArray['email_notification']
		];
		
			$setting = Setting::where('user_id','=', $user_id)->update($updateData);
			if($setting)
			{
				return response()->json([
					'success' => true
					
				]);
			}
	}
	
	
	

#************************************************************************************#	


	public function getGoals(Request $request){
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			
			$goals = Goal::where(['status'=>'1'])->get()->toarray();
	
			if($goals){
				
				foreach($goals as $goals){
					
						
					$records[]=array(
						'goal_id'=>$goals['_id'],
						'goal_name'=>$goals['goal'],
					);
				
				}
				
				return response()->json([ 
					'status' => true,
					'message' => 'Success',
					'data'=>$records,
				], $this->successCode);
			}
			else{
				return response()->json([ 
					'status' => false,
					'message' => 'Not found',
				], $this->successCode);
			}
		}

	}
	
	
#************************************************************************************#	


		public function addStat(Request $request){
		
		$saveArray = $request->all();
		
		
		   /*   "workout_id" : "5c729c1c052fe764fe298b74",
        "day" : "2",
        "daily_desc" : "Sample description will be shown here",
        "sets" : "4",
        "image" : "1.png",
        "hold_stretch_for" : "35",
        "title" : "Demo title"
 */
		
		$insertData = [
				'workout_id'=>'5c729c1c052fe764fe298b74',
				'day'=>'4',
				'daily_desc'=>'Sample description will be shown here',
				'sets'=>'4',
				'image'=>'6.gif',
				'hold_stretch_for'=>'35',
				'title'=>'Demo title',

			];
		
		$UserDetail_id = WorkoutDetail::insertGetId($insertData);
		echo $UserDetail_id;die;
		

			/* $insertData = [
				'user_id'=>$saveArray['user_id'],
				'height'=>'5',
				'weight'=>'180',
				'body_mass_index'=>'25.1',
				'percent_body_fat'=>'9',
				'neck_circumference'=>'9',
				'chest_circumference'=>'40',
				'waist_circumference'=>'40',
				'hip_circumference'=>'40',
				'shoulder_circumference'=>'40',
				'arm_circumference'=>'18',
				'calf_circumference'=>'20',

			]; */
			//$UserDetail_id = UserDetail::insertGetId($insertData);
				//echo $UserDetail_id;die;
				//echo "only for backend use";
		}	
	


#************************************************************************************#	


	public function getUserStat(Request $request){
		 
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
			'date' => 'required',	
		]);
		
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode); 
			
		}else{
				
				$userDetails = UserDetail::where(['user_id'=> $saveArray['user_id']])->first();
				
				$user_data =  User::where(['_id'=> $saveArray['user_id']])->first();
				
				$userSetting = Setting::where('user_id', '=', $saveArray['user_id'])->first();
				
				$userstats = UserStat::where(['user_id'=>$saveArray['user_id']])->where('created_at', 'like',  '%' .$saveArray['date']. '%')->get();
				
				$userstatsCount = UserStat::where(['user_id'=>$saveArray['user_id']])->where('created_at', 'like',  '%' .$saveArray['date']. '%')->get()->count();
					
					
					     $totalDistance  =0;
						 $totalCalories  =0;
						 $totalSpeed  =0;
						 $cal = array();
						 $totalDistanceRunning = 0;
						 $totalDistanceWalking = 0;
						 $totalDistanceCycling = 0;
						 $runningCount = 0;
						 $walkingCount = 0;
						 $cyclingCount = 0;
						 
								$records=array();
								
								if($userstatsCount>0)
								{
								foreach($userstats as $userstat){
									
									$totalDistance +=$userstat['distance'];
									$totalCalories +=$userstat['calories'];
									$totalSpeed +=$userstat['speed'];
									
									if($userstat['type'] == "running"){
										 $totalDistanceRunning +=$userstat['distance'];
										  $runningCount ++;
										$records[]=array(
										    'type'=> $userstat['type'],
											'day'=> $userstat['day'],
											'distance'=>$userstat['distance']?$userstat['distance']:'',
											'time'=>$userstat['time']?$userstat['time']:'',
											'speed'=>$userstat['speed']?$userstat['speed']:'',
											'calories'=>$userstat['calories']?$userstat['calories']:'',
											'ratting'=>$userstat['ratting']?$userstat['ratting']:'',
											'created_at'=>$userstat['created_at']
										);
									}
									
									else if($userstat['type'] == "walking"){
										 $totalDistanceWalking +=$userstat['distance'];
										  $walkingCount++;
						
										$records[]=array(
										    'type'=> $userstat['type'],
											'day'=> $userstat['day'],
											'distance'=>$userstat['distance']?$userstat['distance']:'',
											'time'=>$userstat['time']?$userstat['time']:'',
											'speed'=>$userstat['speed']?$userstat['speed']:'',
											'calories'=>$userstat['calories']?$userstat['calories']:'',
											'ratting'=>$userstat['ratting']?$userstat['ratting']:'',
											'created_at'=>$userstat['created_at']
										);
									}
									
									else if($userstat['type'] == "cycling"){
										 $totalDistanceCycling +=$userstat['distance'];
										  $cyclingCount++;
										$records[]=array(
										    'type'=> $userstat['type'],
											'day'=> $userstat['day'],
											'distance'=>$userstat['distance']?$userstat['distance']:'',
											'time'=>$userstat['time']?$userstat['time']:'',
											'speed'=>$userstat['speed']?$userstat['speed']:'',
											'calories'=>$userstat['calories']?$userstat['calories']:'',
											'ratting'=>$userstat['ratting']?$userstat['ratting']:'',
											'created_at'=>$userstat['created_at']
										);
									}
									
									
									else if($userstat['type'] == "yoga"){
										
								  $yogaWorkout = Workout::where('_id', '=',  $userstat['exercise_id'])->first();
								  
										$records[]=array(
											'time'=> $yogaWorkout['time'],
											'period'=> $yogaWorkout['period'],
											'exercise_id'=>$userstat['exercise_id']?$userstat['exercise_id']:'',
											'ratting'=>$userstat['ratting']?$userstat['ratting']:'',
											'created_at'=>$userstat['created_at']
										);
									}
									

								}

					
				return response()->json([ 
						'status' => true,
						
						//'latest_workouts'=>$records,
						'totalDistance'=> $userSetting->unit_run==0?$totalDistance .' MI':$totalDistance*1.60934 .' KM',
						'totalCalories'=> $totalCalories, 
						'totalSpeed'=> $userstatsCount>0?$userSetting->unit_run==0?$totalSpeed/$userstatsCount.' /MI':($totalSpeed/$userstatsCount)*1.60934
.' /KM':0,
						'totalDistanceRunning'=> $runningCount>0?$userSetting->unit_run==0?$totalDistanceRunning/$runningCount :($totalDistanceRunning/$runningCount)*1.60934:0 ,
						'totalDistanceWalking'=> $walkingCount>0?$totalDistanceWalking/$walkingCount:0,
						'totalDistanceCycling'=> $cyclingCount>0?$totalDistanceCycling/$cyclingCount:0,
						
						
					], $this->successCode);
			
				
			//}

		}
		else{
				return response()->json(['status'=>false], $this->successCode); 
			}
	
	}
}
	
	

	public function getUserStatRunningPeriod(Request $request){
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
			'date' => 'required',
			 'type'=>'required'
		]);
		
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode); 
			
		}else{
                
				$time_input = strtotime($saveArray['date']);  
                $date_input = getDate($time_input);  
				$month = $date_input['mon'];
				$year = $date_input['year'];
				$newdate = strtotime ( '7 day' , strtotime ( $saveArray['date']) ) ;
				$newdate = date ( 'Y-m-j' , $newdate );
				
				$userDetails = UserDetail::where(['user_id'=> $saveArray['user_id']])->first();
				
				$user_data =  User::where(['_id'=> $saveArray['user_id']])->first();
				
				$userSetting = Setting::where('user_id', '=', $saveArray['user_id'])->first();

		        $userstats = UserStat::where(['user_id'=>$saveArray['user_id']])->get();
				
				$carbon = \Carbon\Carbon::now(); 
				$carbon->month = 12;
				$carbon->year = 2019;
				
				// $post = UserStat::whereYear('created_at', '=', $carbon->year)
					  // ->whereMonth('created_at', '=', $carbon->month)
					  // ->get();
					
				
				if($saveArray['type']=='monthly')
				{
					//$date = new Carbon($year.'-'.$month.'-'.'01');
					
					if($month==10||$month==11||$month==12)
					{
						$d= $year.'-'.'1'.$month.'-'.'01';
						
					}
					
					else
					{
						$d= $year.'-'.'0'.$month.'-'.'01';
					}
					
					$newdateWeekOne = strtotime ('7 day' , strtotime($d)) ;
					$newdateWeekOne = date ( 'Y-m-j' , $newdateWeekOne );
					
					$firstWeekData = UserStat::where(['user_id'=>$saveArray['user_id']])->whereBetween('created_at', [$d, $newdateWeekOne])->get();
					
					$newdateWeekTwo = strtotime ('7 day' , strtotime($newdateWeekOne)) ;
					$newdateWeekTwo = date ( 'Y-m-j' , $newdateWeekTwo);
					
					$secondWeekData = UserStat::where(['user_id'=>$saveArray['user_id']])->whereBetween('created_at', [$newdateWeekOne, $newdateWeekTwo])->get();
					
					$newdateWeekThree = strtotime ('7 day' , strtotime($newdateWeekTwo)) ;
					$newdateWeekThree = date ( 'Y-m-j' , $newdateWeekThree);
					
					$thirdWeekData = UserStat::where(['user_id'=>$saveArray['user_id']])->whereBetween('created_at', [$newdateWeekTwo, $newdateWeekThree])->get();
					
					
					$newdateWeekFour = strtotime ('7 day' , strtotime($newdateWeekThree)) ;
					$newdateWeekFour = date ( 'Y-m-j' , $newdateWeekFour);
					
					$fourthWeekData = UserStat::where(['user_id'=>$saveArray['user_id']])->whereBetween('created_at', [$newdateWeekThree, $newdateWeekFour])->get();
				
				}
				elseif($saveArray['type']=='yearly')
				{
				  $userstats = UserStat::where(['user_id'=>$saveArray['user_id']])->where('created_at', 'like',  '%' .$year. '%')->get();

				}
				else
				{
			    $userstats = UserStat::where(['user_id'=>$saveArray['user_id']])->whereBetween('created_at', [$saveArray['date'], $newdate])->get();

				}
				
				// echo "<pre>";
				// print_r($userstats);
				
				// die();
				$userstatsCount = UserStat::where(['user_id'=>$saveArray['user_id']])->where('created_at', 'like',  '%' .$saveArray['date']. '%')->get()->count();
					
					     $totalDistance  =0;
						 $totalCalories  =0;
						 $totalSpeed  =0;
						 $cal = array();
						 $totalDistanceRunning = 0;
						 $totalDistanceWalking = 0;
						 $totalDistanceCycling = 0;
						 $runningCount = 0;
						 $walkingCount = 0;
						 $cyclingCount = 0;
						 
								$records=array();
								
								foreach($userstats as $userstat){
									
									$totalDistance +=$userstat['distance'];
									$totalCalories +=$userstat['calories'];
									$totalSpeed +=$userstat['speed'];
									
									if($userstat['type'] == "running"){
										 $totalDistanceRunning +=$userstat['distance'];
										  $runningCount ++;
										$records[]=array(
										    'type'=> $userstat['type'],
											'day'=> $userstat['day'],
											'distance'=>$userstat['distance']?$userstat['distance']:'',
											'time'=>$userstat['time']?$userstat['time']:'',
											'speed'=>$userstat['speed']?$userstat['speed']:'',
											'calories'=>$userstat['calories']?$userstat['calories']:'',
											'ratting'=>$userstat['ratting']?$userstat['ratting']:'',
											'created_at'=>$userstat['created_at']
										);
									}
									
									else if($userstat['type'] == "walking"){
										 $totalDistanceWalking +=$userstat['distance'];
										  $walkingCount++;
						
										$records[]=array(
										    'type'=> $userstat['type'],
											'day'=> $userstat['day'],
											'distance'=>$userstat['distance']?$userstat['distance']:'',
											'time'=>$userstat['time']?$userstat['time']:'',
											'speed'=>$userstat['speed']?$userstat['speed']:'',
											'calories'=>$userstat['calories']?$userstat['calories']:'',
											'ratting'=>$userstat['ratting']?$userstat['ratting']:'',
											'created_at'=>$userstat['created_at']
										);
									}
									
									else if($userstat['type'] == "cycling"){
										 $totalDistanceCycling +=$userstat['distance'];
										  $cyclingCount++;
										$records[]=array(
										    'type'=> $userstat['type'],
											'day'=> $userstat['day'],
											'distance'=>$userstat['distance']?$userstat['distance']:'',
											'time'=>$userstat['time']?$userstat['time']:'',
											'speed'=>$userstat['speed']?$userstat['speed']:'',
											'calories'=>$userstat['calories']?$userstat['calories']:'',
											'ratting'=>$userstat['ratting']?$userstat['ratting']:'',
											'created_at'=>$userstat['created_at']
										);
									}
									
									
									else if($userstat['type'] == "yoga"){
										
								  $yogaWorkout = Workout::where('_id', '=',  $userstat['exercise_id'])->first();
								  
										$records[]=array(
											'time'=> $yogaWorkout['time'],
											'period'=> $yogaWorkout['period'],
											'exercise_id'=>$userstat['exercise_id']?$userstat['exercise_id']:'',
											'ratting'=>$userstat['ratting']?$userstat['ratting']:'',
											'created_at'=>$userstat['created_at']
										);
									}
									

								}

					
				return response()->json([ 
						'status' => true,
						'data'=>[
						'totalDistance'=> $userSetting->unit_run==0?$totalDistance .' MI':$totalDistance*1.60934 .' KM',
						'totalCalories'=> $totalCalories, 
						'totalSpeed'=> $userSetting->unit_run==0?$totalSpeed/$userstatsCount.' /MI':($totalSpeed/$userstatsCount)*1.60934
 .' /KM']
						
						
						
					], $this->successCode);
			
				
			//}else{
			//	return response()->json(['message'=>'Invalid User.','status'=>false], $this->successCode); 
		//	}

		//}
		
		
	}
	
	}
	
	public function getUserStatRunningMonthly(Request $request){
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
			'date' => 'required',
			 'type'=>'required'
		]);
		
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode); 
			
		}else{
                
				$time_input = strtotime($saveArray['date']);  
                $date_input = getDate($time_input);  
				$month = $date_input['mon'];
				$year = $date_input['year'];
				$newdate = strtotime ( '7 day' , strtotime ( $saveArray['date']) ) ;
				$newdate = date ( 'Y-m-j' , $newdate );
				
				$userDetails = UserDetail::where(['user_id'=> $saveArray['user_id']])->first();
				
				$user_data =  User::where(['_id'=> $saveArray['user_id']])->first();
				
				$userSetting = Setting::where('user_id', '=', $saveArray['user_id'])->first();
				
		        $userstats = UserStat::where(['user_id'=>$saveArray['user_id']])->get();
				
				$checkDurationOfMonth=cal_days_in_month(CAL_GREGORIAN,$month,$year);
				
				if($checkDurationOfMonth ==31)
				{
					
					if($month==10||$month==11||$month==12)
					{
						$lastDate= $year.'-'.$month.'-'.'31';
						
					}
					
					else
					{
						$lastDate= $year.'-'.'0'.$month.'-'.'31';
					}
					
				}
				
				else if($checkDurationOfMonth ==30)
				{
					if($month==10||$month==11||$month==12)
					{
						$lastDate= $year.'-'.$month.'-'.'30';
						
					}
					
					else
					{
						$lastDate= $year.'-'.'0'.$month.'-'.'30';
					}
					
				}
				else
				{
					if($month==10||$month==11||$month==12)
					{
						$lastDate= $year.'-'.$month.'-'.'29';
						
					}
					
					else
					{
						$lastDate= $year.'-'.'0'.$month.'-'.'29';
					}
					
				}
				
				if($saveArray['type']=='monthly')
				{
					//$date = new Carbon($year.'-'.$month.'-'.'01');
					
					if($month==10||$month==11||$month==12)
					{
						$d= $year.'-'.$month.'-'.'01';
						
					}
					
					else
					{
						$d= $year.'-'.'0'.$month.'-'.'01';
					}
					
					$newdateWeekOne = strtotime ('7 day' , strtotime($d)) ;
					$newdateWeekOne = date ( 'Y-m-d' , $newdateWeekOne);
					
					$firstWeekData = UserStat::where(['user_id'=>$saveArray['user_id']])->whereBetween('created_at', [$d, $newdateWeekOne])->get();
					
					     $totalDistance  =0;
						 $totalCalories  =0;
						 $totalSpeed  =0;
						 $cal = array();
						 $totalDistanceRunningWeekOne = 0;
						 $totalDistanceWalkingWeekOne = 0;
						 $totalDistanceCyclingWeekOne = 0;
						 
						 $totalDistanceRunningWeekTwo = 0;
						 $totalDistanceWalkingWeekTwo = 0;
						 $totalDistanceCyclingWeekTwo = 0;
						 
						 $totalDistanceRunningWeekThree = 0;
						 $totalDistanceWalkingWeekThree = 0;
						 $totalDistanceCyclingWeekThree = 0;
						 
						 $totalDistanceRunningWeekFour = 0;
						 $totalDistanceWalkingWeekFour = 0;
						 $totalDistanceCyclingWeekFour = 0;
						 $runningCount = 0;
						 $walkingCount = 0;
						 $cyclingCount = 0;
						 
								foreach($firstWeekData as $userstat){
									
									$totalDistance +=$userstat['distance'];
									$totalCalories +=$userstat['calories'];
									$totalSpeed +=$userstat['speed'];
									
									if($userstat['type'] == "running"){
										 $totalDistanceRunningWeekOne +=$userstat['distance'];
										  $runningCount ++;
										
									}
									
									else if($userstat['type'] == "walking"){
										 $totalDistanceWalkingWeekOne +=$userstat['distance'];
										  $walkingCount++;
						
										
									}
									
									else if($userstat['type'] == "cycling"){
										 $totalDistanceCyclingWeekOne +=$userstat['distance'];
										  $cyclingCount++;
										
									}
									
								}
					
					
					
					$newdateWeekTwo = strtotime ('7 day' , strtotime($newdateWeekOne)) ;
					$newdateWeekTwo = date ( 'Y-m-j' , $newdateWeekTwo);
					
					$secondWeekData = UserStat::where(['user_id'=>$saveArray['user_id']])->whereBetween('created_at', [$newdateWeekOne, $newdateWeekTwo])->get();
					
								foreach($secondWeekData as $userstat){
									
									$totalDistance +=$userstat['distance'];
									$totalCalories +=$userstat['calories'];
									$totalSpeed +=$userstat['speed'];
									
									if($userstat['type'] == "running"){
										 $totalDistanceRunningWeekTwo +=$userstat['distance'];
										  $runningCount ++;
										
									}
									
									else if($userstat['type'] == "walking"){
										 $totalDistanceWalkingWeekTwo +=$userstat['distance'];
										  $walkingCount++;
						
									}
									
									else if($userstat['type'] == "cycling"){
										 $totalDistanceCyclingWeekTwo +=$userstat['distance'];
										  $cyclingCount++;
										
									}
									
								}
					
					
					
					$newdateWeekThree = strtotime ('7 day' , strtotime($newdateWeekTwo)) ;
					$newdateWeekThree = date ( 'Y-m-j' , $newdateWeekThree);
					
					$thirdWeekData = UserStat::where(['user_id'=>$saveArray['user_id']])->whereBetween('created_at', [$newdateWeekTwo, $newdateWeekThree])->get();
					foreach($thirdWeekData as $userstat){
									
									$totalDistance +=$userstat['distance'];
									$totalCalories +=$userstat['calories'];
									$totalSpeed +=$userstat['speed'];
									
									if($userstat['type'] == "running"){
										 $totalDistanceRunningWeekThree +=$userstat['distance'];
										  $runningCount ++;
										
									}
									
									else if($userstat['type'] == "walking"){
										 $totalDistanceWalkingWeekThree +=$userstat['distance'];
										  $walkingCount++;
						
									}
									
									else if($userstat['type'] == "cycling"){
										 $totalDistanceCyclingWeekThree +=$userstat['distance'];
										  $cyclingCount++;
										
									}
									
								}
					
					
					$newdateWeekFour = strtotime ('7 day' , strtotime($newdateWeekThree)) ;
					$newdateWeekFour = date ( 'Y-m-j' , $newdateWeekFour);
					
					$fourthWeekData = UserStat::where(['user_id'=>$saveArray['user_id']])->whereBetween('created_at', [$newdateWeekThree, $newdateWeekFour])->get();
						foreach($fourthWeekData as $userstat){
									
									$totalDistance +=$userstat['distance'];
									$totalCalories +=$userstat['calories'];
									$totalSpeed +=$userstat['speed'];
									
									if($userstat['type'] == "running"){
										 $totalDistanceRunningWeekFour +=$userstat['distance'];
										  $runningCount ++;
										
									}
									
									else if($userstat['type'] == "walking"){
										 $totalDistanceWalkingWeekFour +=$userstat['distance'];
										  $walkingCount++;
						
									}
									
									else if($userstat['type'] == "cycling"){
										 $totalDistanceCyclingWeekFour +=$userstat['distance'];
										  $cyclingCount++;
										
									}
									
								}
				
				
				}
				elseif($saveArray['type']=='yearly')
				{
				  $userstats = UserStat::where(['user_id'=>$saveArray['user_id']])->where('created_at', 'like',  '%' .$year. '%')->get();

				}
				else
				{
			    $userstats = UserStat::where(['user_id'=>$saveArray['user_id']])->whereBetween('created_at', [$saveArray['date'], $newdate])->get();

				}
				
				$userstatsCount = UserStat::where(['user_id'=>$saveArray['user_id']])->where('created_at', 'like',  '%' .$saveArray['date']. '%')->get()->count();
				
				//echo $d;
				//die();
				// if(!empty($lastDate))
				// {
				$userstats = UserStat::where(['user_id'=>$saveArray['user_id']])->whereBetween('created_at', [$d, $lastDate])->get();
				// }
					
					     $totalDistance  =0;
						 $totalCalories  =0;
						 $totalSpeed  =0;
						 $cal = array();
						 $totalDistanceRunning = 0;
						 $totalDistanceWalking = 0;
						 $totalDistanceCycling = 0;
						 $runningCount = 0;
						 $walkingCount = 0;
						 $cyclingCount = 0;
								
								foreach($userstats as $userstat){
									
									$totalDistance +=$userstat['distance'];
									$totalCalories +=$userstat['calories'];
									$totalSpeed +=$userstat['speed'];
									
								}

					
				return response()->json([ 
						'status' => true,
						'data'=>[
						'totalDistance'=> $userSetting->unit_run==0?$totalDistance .' MI':$totalDistance*1.60934 .' KM',
						'totalCalories'=> $totalCalories, 
						'totalSpeed'=> $userSetting->unit_run==0?$totalSpeed/4 .'/MI':($totalSpeed/4)*1.60934
 .' /KM', 'weekOneRunningDistance'=>$totalDistanceRunningWeekOne?$totalDistanceRunningWeekOne/7:0,'weekOneWalkingDistance'=>$totalDistanceWalkingWeekOne?$totalDistanceWalkingWeekOne/7:0, 'weekOneCyclingDistance'=>$totalDistanceCyclingWeekOne?$totalDistanceCyclingWeekOne/7:0,
 'weekTwoRunningDistance'=>$totalDistanceRunningWeekTwo?$totalDistanceRunningWeekTwo/7:0, 'weekTwoWalkingDistance'=>$totalDistanceWalkingWeekTwo?$totalDistanceWalkingWeekTwo/7:0,'weekTwoCyclingDistance'=>$totalDistanceCyclingWeekTwo?$totalDistanceCyclingWeekTwo/7:0, 'weekThreeRunningDistance'=>$totalDistanceRunningWeekThree?$totalDistanceRunningWeekThree/7:0, 'weekThreeWalkingDistance'=>$totalDistanceWalkingWeekThree?$totalDistanceWalkingWeekThree/7:0,'weekThreeCyclingDistance'=>$totalDistanceCyclingWeekThree?$totalDistanceCyclingWeekThree/7:0, 'weekFourRunningDistance'=>$totalDistanceRunningWeekFour?$totalDistanceRunningWeekFour/7:0, 'weekFourWalkingDistance'=>$totalDistanceWalkingWeekFour?$totalDistanceWalkingWeekFour/7:0,'weekFourCyclingDistance'=>$totalDistanceCyclingWeekFour?$totalDistanceCyclingWeekFour/7:0]
						
					], $this->successCode);
			
		
		
	}
	
	}

	
	public function getUserStatRunningYearly(Request $request){
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
			'date' => 'required',
			 'type'=>'required'
		]);
		
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode); 
			
		}else{
                
				$time_input = strtotime($saveArray['date']);  
                $date_input = getDate($time_input);  
				$month = $date_input['mon'];
				$year = $date_input['year'];
				$newdate = strtotime ( '7 day' , strtotime ( $saveArray['date']) ) ;
				$newdate = date ( 'Y-m-d' , $newdate );
				
				
				// $date=date_create($year.'-'.$month.'-'.'23');
                // echo date_format($date,"Y-m-d");
				
				$userDetails = UserDetail::where(['user_id'=> $saveArray['user_id']])->first();
				
				$user_data =  User::where(['_id'=> $saveArray['user_id']])->first();
				
				$userSetting = Setting::where('user_id', '=', $saveArray['user_id'])->first();
				
		        $userstats = UserStat::where(['user_id'=>$saveArray['user_id']])->get();
				
				$data = UserStat::where('created_at','>=', $saveArray['date'])->where('created_at','<=', $newdate)->get();
				// echo "<pre>";
				// print_r($data);
				
				// die();
				
				// $checkDurationOfMonth=cal_days_in_month(CAL_GREGORIAN,$month,$year);
				
				// $newdateWeekOne = strtotime ($checkDurationOfMonth .'days', strtotime($d)) ;
			    // $newdateWeekOne = date ( 'Y-m-d' , $newdateWeekOne);
				
				$totalDistanceRunningJanuary=0;
				$totalDistanceWalkingJanuary=0;
				$totalDistanceCyclingJanuary=0;
				
				$totalDistanceRunningFeb=0;
				$totalDistanceWalkingFeb=0;
				$totalDistanceCyclingFeb=0;
				
				$totalDistanceRunningMarch=0;
				$totalDistanceWalkingMarch=0;
				$totalDistanceCyclingMarch=0;
				
				$totalDistanceRunningApril=0;
				$totalDistanceWalkingApril=0;
				$totalDistanceCyclingApril=0;
				
				$totalDistanceRunningMay=0;
				$totalDistanceWalkingMay=0;
				$totalDistanceCyclingMay=0;
				
				$totalDistanceRunningJune=0;
				$totalDistanceWalkingJune=0;
				$totalDistanceCyclingJune=0;
				
				$totalDistanceRunningJuly=0;
				$totalDistanceWalkingJuly=0;
				$totalDistanceCyclingJuly=0;
				
				$totalDistanceRunningAugust=0;
				$totalDistanceWalkingAugust=0;
				$totalDistanceCyclingAugust=0;
				
				$totalDistanceRunningSep=0;
				$totalDistanceWalkingSep=0;
				$totalDistanceCyclingSep=0;
				
				$totalDistanceRunningOct=0;
				$totalDistanceWalkingOct=0;
				$totalDistanceCyclingOct=0;
				
				$totalDistanceRunningNov=0;
				$totalDistanceWalkingNov=0;
				$totalDistanceCyclingNov=0;
				
				$totalDistanceRunningDec=0;
				$totalDistanceWalkingDec=0;
				$totalDistanceCyclingDec=0;
				
				$record = array();
				if($saveArray['type']=='yearly')
				{
					$januaryFirstDate = $year.'-'.'01'.'-'.'01';
					$checkDurationOfJanuray=cal_days_in_month(CAL_GREGORIAN,01,$year);
					
					$newdateJanuary= strtotime($checkDurationOfJanuray .'days', strtotime($januaryFirstDate)) ;
					$newdateJanuary = date ( 'Y-m-d' , $newdateJanuary);
					
					$janurayMonthData = UserStat::where(['user_id'=>$saveArray['user_id']])->whereBetween('created_at', [$januaryFirstDate, $newdateJanuary])->get();
					
					foreach($janurayMonthData as $userstat)
					{
						if($userstat['type'] == "running"){
							
										 $totalDistanceRunningJanuary +=$userstat['distance'];
										  
									}
									
									else if($userstat['type'] == "walking"){
										 $totalDistanceWalkingJanuary +=$userstat['distance'];
										 
									}
									
									else if($userstat['type'] == "cycling"){
										 $totalDistanceCyclingJanuary +=$userstat['distance'];
									}
									
									
									// $janData=array(
									// "totalDistanceRunningJanuary"=>$totalDistanceRunningJanuary,
									// "totalDistanceWalkingJanuary"=>$totalDistanceWalkingJanuary,
									// "totalDistanceCyclingJanuary"=>$totalDistanceCyclingJanuary
									
									// );
								
					}
					// array_push($record, $janData);
					$checkDurationOfFeb=cal_days_in_month(CAL_GREGORIAN,02,$year);
					
					$newdateFeb= strtotime($checkDurationOfFeb .'days', strtotime($newdateJanuary)) ;
					$newdateFeb = date ( 'Y-m-d' , $newdateFeb);
					
				    $febMonthData = UserStat::where(['user_id'=>$saveArray['user_id']])->whereBetween('created_at', [$newdateJanuary, $newdateFeb])->get();

					foreach($febMonthData as $userstat)
					{
						if($userstat['type'] == "running"){
							
										 $totalDistanceRunningFeb +=$userstat['distance'];
										  
									}
									
									else if($userstat['type'] == "walking"){
										 $totalDistanceWalkingFeb +=$userstat['distance'];
										 
									}
									
									else if($userstat['type'] == "cycling"){
										 $totalDistanceCyclingFeb +=$userstat['distance'];
									}
									
									$febData=array(
									"totalDistanceRunningFeb"=>$totalDistanceRunningFeb,
									"totalDistanceWalkingFeb"=>$totalDistanceWalkingFeb,
									"totalDistanceCyclingFeb"=>$totalDistanceCyclingFeb,
									
									);
									
									
					}
					

					$checkDurationOfMarch=cal_days_in_month(CAL_GREGORIAN,03,$year);
					
					$newdateMarch= strtotime($checkDurationOfMarch .'days', strtotime($newdateFeb)) ;
					$newdateMarch = date ( 'Y-m-d' , $newdateMarch);
					
					$marchMonthData = UserStat::where(['user_id'=>$saveArray['user_id']])->whereBetween('created_at', [$newdateFeb, $newdateMarch])->get();
					
					foreach($marchMonthData as $userstat)
					{
						if($userstat['type'] == "running"){
							
										 $totalDistanceRunningMarch +=$userstat['distance'];
										  
									}
									
									else if($userstat['type'] == "walking"){
										 $totalDistanceWalkingMarch +=$userstat['distance'];
										 
									}
									
									else if($userstat['type'] == "cycling"){
										 $totalDistanceCyclingMarch +=$userstat['distance'];
									}
									
									
									// $febData=array(
									// "totalDistanceRunningFeb"=>$totalDistanceRunningFeb,
									// "totalDistanceWalkingFeb"=>$totalDistanceWalkingFeb,
									// "totalDistanceCyclingFeb"=>$totalDistanceCyclingFeb,
									// "totalDistanceRunningMarch"=>$totalDistanceRunningMarch,
									// "totalDistanceWalkingMarch"=>$totalDistanceWalkingMarch,
									// "totalDistanceCyclingMarch"=>$totalDistanceCyclingMarch,
									
									// );
					}
					
					
					
				    $checkDurationOfApril=cal_days_in_month(CAL_GREGORIAN,04,$year);
					
					$newdateApril= strtotime($checkDurationOfApril .'days', strtotime($newdateMarch)) ;
					$newdateApril = date ( 'Y-m-d' , $newdateApril);
					
				    $aprilMonthData = UserStat::where(['user_id'=>$saveArray['user_id']])->whereBetween('created_at', [$newdateMarch, $newdateApril])->get();

					foreach($aprilMonthData as $userstat)
					{
						if($userstat['type'] == "running"){
							
										 $totalDistanceRunningApril +=$userstat['distance'];
										  
									}
									
									else if($userstat['type'] == "walking"){
										 $totalDistanceWalkingApril +=$userstat['distance'];
										 
									}
									
									else if($userstat['type'] == "cycling"){
										 $totalDistanceCyclingApril +=$userstat['distance'];
									}
									// $febData=array(
									
									// "totalDistanceRunningApril"=>$totalDistanceRunningApril,
									// "totalDistanceWalkingApril"=>$totalDistanceWalkingApril,
									// "totalDistanceCyclingApril"=>$totalDistanceCyclingApril,
									
									// );
									
									
					}
					
					
					$checkDurationOfMay=cal_days_in_month(CAL_GREGORIAN,05,$year);
					
					$newdateMay= strtotime($checkDurationOfMay .'days', strtotime($newdateApril)) ;
					$newdateMay = date ( 'Y-m-d' , $newdateMay);
					
					
				    $mayMonthData = UserStat::where(['user_id'=>$saveArray['user_id']])->whereBetween('created_at', [$newdateApril, $newdateMay])->get();

					foreach($mayMonthData as $userstat)
					{
						if($userstat['type'] == "running"){
							
										 $totalDistanceRunningMay +=$userstat['distance'];
										  
									}
									
									else if($userstat['type'] == "walking"){
										 $totalDistanceWalkingMay +=$userstat['distance'];
										 
									}
									
									else if($userstat['type'] == "cycling"){
										 $totalDistanceCyclingMay +=$userstat['distance'];
									}
									// $febData=array(
									// "totalDistanceRunningFeb"=>$totalDistanceRunningFeb,
									// "totalDistanceWalkingFeb"=>$totalDistanceWalkingFeb,
									// "totalDistanceCyclingFeb"=>$totalDistanceCyclingFeb,
									// "totalDistanceRunningMarch"=>$totalDistanceRunningMarch,
									// "totalDistanceWalkingMarch"=>$totalDistanceWalkingMarch,
									// "totalDistanceCyclingMarch"=>$totalDistanceCyclingMarch,
									// "totalDistanceRunningApril"=>$totalDistanceRunningApril,
									// "totalDistanceWalkingApril"=>$totalDistanceWalkingApril,
									// "totalDistanceCyclingApril"=>$totalDistanceCyclingApril,
									// "totalDistanceRunningMay"=>$totalDistanceRunningMay,
									// "totalDistanceWalkingMay"=>$totalDistanceWalkingMay,
									// "totalDistanceCyclingMay"=>$totalDistanceCyclingMay,
									
									// );
									
									
					}

					$checkDurationOfJune=cal_days_in_month(CAL_GREGORIAN,06,$year);
					
					$newdateJune= strtotime($checkDurationOfJune .'days', strtotime($newdateMay)) ;
					$newdateJune = date ( 'Y-m-d' , $newdateJune);
					
					$juneMonthData = UserStat::where(['user_id'=>$saveArray['user_id']])->whereBetween('created_at', [$newdateMay, $newdateJune])->get();

					foreach($juneMonthData as $userstat)
					{
						if($userstat['type'] == "running"){
							
										 $totalDistanceRunningJune +=$userstat['distance'];
										  
									}
									
									else if($userstat['type'] == "walking"){
										 $totalDistanceWalkingJune +=$userstat['distance'];
										 
									}
									
									else if($userstat['type'] == "cycling"){
										 $totalDistanceCyclingJune +=$userstat['distance'];
									}
									
									// $febData=array(
									
									// "totalDistanceRunningJune"=>$totalDistanceRunningJune,
									// "totalDistanceWalkingJune"=>$totalDistanceWalkingJune,
									// "totalDistanceCyclingJune"=>$totalDistanceCyclingJune,
									
									// );
									
									
					}
					
					
					$checkDurationOfJuly=cal_days_in_month(CAL_GREGORIAN,07,$year);
					
					$newdateJuly= strtotime($checkDurationOfJuly .'days', strtotime($newdateJune)) ;
					$newdateJuly = date ( 'Y-m-d' , $newdateJuly);
					
					$julyMonthData = UserStat::where(['user_id'=>$saveArray['user_id']])->whereBetween('created_at', [$newdateJune, $newdateJuly])->get();

					foreach($julyMonthData as $userstat)
					{
						if($userstat['type'] == "running"){
							
										 $totalDistanceRunningJuly +=$userstat['distance'];
										  
									}
									
									else if($userstat['type'] == "walking"){
										 $totalDistanceWalkingJuly +=$userstat['distance'];
										 
									}
									
									else if($userstat['type'] == "cycling"){
										 $totalDistanceCyclingJuly +=$userstat['distance'];
									}
									
									
					}
					
					
					$checkDurationOfAugust=cal_days_in_month(CAL_GREGORIAN,'08',$year);
					
					$newdateAugust= strtotime($checkDurationOfAugust .'days', strtotime($newdateJuly)) ;
					$newdateAugust = date ( 'Y-m-d' , $newdateAugust);
					
					$augustMonthData = UserStat::where(['user_id'=>$saveArray['user_id']])->whereBetween('created_at', [$newdateJuly, $newdateAugust])->get();
					
					foreach($augustMonthData as $userstat)
					{
						if($userstat['type'] == "running"){
							
										 $totalDistanceRunningAugust +=$userstat['distance'];
										  
									}
									
									else if($userstat['type'] == "walking"){
										 $totalDistanceWalkingAugust +=$userstat['distance'];
										 
									}
									
									else if($userstat['type'] == "cycling"){
										 $totalDistanceCyclingAugust +=$userstat['distance'];
									}
									
									
									
									
									
					}
					
					
					
					$checkDurationOfSep=cal_days_in_month(CAL_GREGORIAN,'09',$year);
					
					$newdateSep= strtotime($checkDurationOfSep .'days', strtotime($newdateAugust)) ;
					$newdateSep = date ( 'Y-m-d' , $newdateSep);
					
					$sepMonthData = UserStat::where(['user_id'=>$saveArray['user_id']])->whereBetween('created_at', [$newdateAugust, $newdateSep])->get();
					
					foreach($sepMonthData as $userstat)
					{
						if($userstat['type'] == "running"){
							
										 $totalDistanceRunningSep +=$userstat['distance'];
										  
									}
									
									else if($userstat['type'] == "walking"){
										 $totalDistanceWalkingSep +=$userstat['distance'];
										 
									}
									
									else if($userstat['type'] == "cycling"){
										 $totalDistanceCyclingSep +=$userstat['distance'];
									}
									
									
									
					}
					
					
					
					
					$checkDurationOfOct=cal_days_in_month(CAL_GREGORIAN,'10',$year);
					
					$newdateOct= strtotime($checkDurationOfOct .'days', strtotime($newdateSep)) ;
					$newdateOct = date ( 'Y-m-d' , $newdateOct);
					
					$octMonthData = UserStat::where(['user_id'=>$saveArray['user_id']])->whereBetween('created_at', [$newdateSep, $newdateOct])->get();
					
					
					foreach($octMonthData as $userstat)
					{
						if($userstat['type'] == "running"){
							
										 $totalDistanceRunningOct +=$userstat['distance'];
										  
									}
									
									else if($userstat['type'] == "walking"){
										 $totalDistanceWalkingOct +=$userstat['distance'];
										 
									}
									
									else if($userstat['type'] == "cycling"){
										 $totalDistanceCyclingOct +=$userstat['distance'];
									}
									
									
					}
					
					
					
					$checkDurationOfNov=cal_days_in_month(CAL_GREGORIAN,'11',$year);
					
					$newdateNov= strtotime($checkDurationOfNov .'days', strtotime($newdateOct)) ;
					$newdateNov = date ( 'Y-m-d' , $newdateNov);
					
					$novMonthData = UserStat::where(['user_id'=>$saveArray['user_id']])->whereBetween('created_at', [$newdateOct, $newdateNov])->get();
					
					foreach($novMonthData as $userstat)
					{
						if($userstat['type'] == "running"){
							
										 $totalDistanceRunningNov +=$userstat['distance'];
										  
									}
									
									else if($userstat['type'] == "walking"){
										 $totalDistanceWalkingNov +=$userstat['distance'];
										 
									}
									
									else if($userstat['type'] == "cycling"){
										 $totalDistanceCyclingNov +=$userstat['distance'];
									}
									
									$febData=array(
									"totalDistanceRunningNov"=>$totalDistanceRunningNov,
									"totalDistanceWalkingNov"=>$totalDistanceWalkingNov,
									"totalDistanceCyclingNov"=>$totalDistanceCyclingNov,
									
									);
									
									
					}
					
					$checkDurationOfDec=cal_days_in_month(CAL_GREGORIAN,'12',$year);
					
					$newdateDec= strtotime($checkDurationOfDec .'days', strtotime($newdateNov)) ;
					$newdateDec = date ( 'Y-m-d' , $newdateDec);
					
					$decMonthData = UserStat::where(['user_id'=>$saveArray['user_id']])->whereBetween('created_at', [$newdateNov, $newdateDec])->get();
					
					foreach($decMonthData as $userstat)
					{
						if($userstat['type'] == "running"){
							
										 $totalDistanceRunningDec +=$userstat['distance'];
										  
									}
									
									else if($userstat['type'] == "walking"){
										 $totalDistanceWalkingDec +=$userstat['distance'];
										 
									}
									
									else if($userstat['type'] == "cycling"){
										 $totalDistanceCyclingDec +=$userstat['distance'];
									}
									
									$febData=array(
									"totalDistanceRunningDec"=>$totalDistanceRunningDec,
									"totalDistanceWalkingDec"=>$totalDistanceWalkingDec,
									"totalDistanceCyclingDec"=>$totalDistanceCyclingDec,
									
									);
									
					}
					
					
					// $checkDurationOfMonth=cal_days_in_month(CAL_GREGORIAN,$month,$year);
				
					// $firstWeekData = UserStat::where(['user_id'=>$saveArray['user_id']])->whereBetween('created_at', [$d, $newdateWeekOne])->get();

				}
				else
				{
			    $userstats = UserStat::where(['user_id'=>$saveArray['user_id']])->whereBetween('created_at', [$saveArray['date'], $newdate])->get();

				}
				
				
				$userstats = UserStat::where(['user_id'=>$saveArray['user_id']])->where('created_at', 'like',  '%' .$year. '%')->get();
					
					     $totalDistance  =0;
						 $totalCalories  =0;
						 $totalSpeed  =0;
						 
						 $totalDistanceRunning = 0;
						 $totalDistanceWalking = 0;
						 $totalDistanceCycling = 0;
						 $runningCount = 0;
						 $walkingCount = 0;
						 $cyclingCount = 0;
								
								foreach($userstats as $userstat){
									
									$totalDistance +=$userstat['distance'];
									$totalCalories +=$userstat['calories'];
									$totalSpeed +=$userstat['speed'];
									
								}
				
				return response()->json([ 
						'status' => true,
						//'latest_records'=>$record,
						"totalDistanceRunningJanuary"=>$totalDistanceRunningJanuary/28,
						"totalDistanceWalkingJanuary"=>$totalDistanceWalkingJanuary/28,
						"totalDistanceCyclingJanuary"=>$totalDistanceCyclingJanuary/28,
					    "totalDistanceRunningFeb"=>$totalDistanceRunningFeb/28,
						"totalDistanceWalkingFeb"=>$totalDistanceWalkingFeb/28,
						"totalDistanceCyclingFeb"=>$totalDistanceCyclingFeb/28,
						"totalDistanceRunningMarch"=>$totalDistanceRunningMarch/28,
						"totalDistanceWalkingMarch"=>$totalDistanceWalkingMarch/28,
						"totalDistanceCyclingMarch"=>$totalDistanceCyclingMarch/28,
					    "totalDistanceRunningApril"=>$totalDistanceRunningApril/28,
						"totalDistanceWalkingApril"=>$totalDistanceWalkingApril/28,
						"totalDistanceCyclingApril"=>$totalDistanceCyclingApril/28,
						"totalDistanceRunningMay"=>$totalDistanceRunningMay/28,
						"totalDistanceWalkingMay"=>$totalDistanceWalkingMay/28,
						"totalDistanceCyclingMay"=>$totalDistanceCyclingMay/28,
						"totalDistanceRunningJune"=>$totalDistanceRunningJune/28,
						"totalDistanceWalkingJune"=>$totalDistanceWalkingJune/28,
						"totalDistanceCyclingJune"=>$totalDistanceCyclingJune/28,
						"totalDistanceRunningJuly"=>$totalDistanceRunningJuly/28,
						"totalDistanceWalkingJuly"=>$totalDistanceWalkingJuly/28,
						"totalDistanceCyclingJuly"=>$totalDistanceCyclingJuly/28,	
						
						
						"totalDistanceRunningJuly"=>$totalDistanceRunningJuly/28,
						"totalDistanceWalkingJuly"=>$totalDistanceWalkingJuly/28,
						"totalDistanceCyclingJuly"=>$totalDistanceCyclingJuly/28,
						"totalDistanceRunningAugust"=>$totalDistanceRunningAugust/28,
						"totalDistanceWalkingAugust"=>$totalDistanceWalkingAugust/28,
					    "totalDistanceCyclingAugust"=>$totalDistanceCyclingAugust/28,
						"totalDistanceRunningSep"=>$totalDistanceRunningSep/28,
						"totalDistanceWalkingSep"=>$totalDistanceWalkingSep/28,
						"totalDistanceCyclingSep"=>$totalDistanceCyclingSep/28,
						
						"totalDistanceRunningOct"=>$totalDistanceRunningOct/28,
						"totalDistanceWalkingOct"=>$totalDistanceWalkingOct/28,
						"totalDistanceCyclingOct"=>$totalDistanceCyclingOct/28,
						
						"totalDistanceRunningNov"=>$totalDistanceRunningNov/28,
						"totalDistanceWalkingNov"=>$totalDistanceWalkingNov/28,
						"totalDistanceCyclingNov"=>$totalDistanceCyclingNov/28,
						"totalDistanceRunningDec"=>$totalDistanceRunningDec/28,
						"totalDistanceWalkingDec"=>$totalDistanceWalkingDec/28,
						"totalDistanceCyclingDec"=>$totalDistanceCyclingDec/28,
						'totalDistance'=> $userSetting->unit_run==0?$totalDistance .' MI':$totalDistance*1.60934 .' KM',
						'totalCalories'=> $totalCalories, 
						'totalSpeed'=> $userSetting->unit_run==0?$totalSpeed/12 .'/MI':($totalSpeed/12)*1.60934
						
						
					], $this->successCode);
			
		
	}
	
	}
	
	
	public function getUserStatRunningWeekly(Request $request){
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
			'date' => 'required',
			 'type'=>'required'
		]);
		
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode); 
			
		}else{
                
				$time_input = strtotime($saveArray['date']);  
                $date_input = getDate($time_input);  
				$month = $date_input['mon'];
				$year = $date_input['year'];
				$newdate = strtotime ( '7 day' , strtotime ( $saveArray['date']) ) ;
				$newdate = date ( 'Y-m-j' , $newdate );
				
				$userDetails = UserDetail::where(['user_id'=> $saveArray['user_id']])->first();
				
				$user_data =  User::where(['_id'=> $saveArray['user_id']])->first();
				
				$userSetting = Setting::where('user_id', '=', $saveArray['user_id'])->first();
				
		        $userstats = UserStat::where(['user_id'=>$saveArray['user_id']])->get();
				
				$checkDurationOfMonth=cal_days_in_month(CAL_GREGORIAN,$month,$year);
				
				
				if($saveArray['type']=='weekly')
				{
					//$date = new Carbon($year.'-'.$month.'-'.'01');
					
					if($month==10||$month==11||$month==12)
					{
						$d= $year.'-'.$month.'-'.'01';
						
					}
					
					else
					{
						$d= $year.'-'.'0'.$month.'-'.'01';
					}
					
					$newdateWeekOne = strtotime ('7 day' , strtotime($d)) ;
					$newdateWeekOne = date ( 'Y-m-d' , $newdateWeekOne);
					
					$newdateWeekTwo = strtotime ('7 day' , strtotime($newdateWeekOne)) ;
					$newdateWeekTwo = date ( 'Y-m-d' , $newdateWeekTwo);
					
					$newdateWeekThree = strtotime ('7 day' , strtotime($newdateWeekTwo)) ;
					$newdateWeekThree = date ( 'Y-m-d' , $newdateWeekThree);
					
					$newdateWeekFour = strtotime ('7 day' , strtotime($newdateWeekThree)) ;
					$newdateWeekFour = date ( 'Y-m-d' , $newdateWeekFour);
					
					// echo $newdateWeekThree ."<br>";
					// echo $newdateWeekFour;
					
					
					$firstWeekData = UserStat::where(['user_id'=>$saveArray['user_id']])->where('created_at','<=', $saveArray['date'])->first();
					

					
					if($saveArray['date']>=$d&&$saveArray['date']<$newdateWeekOne)
					{
					
					
					$firstWeekData = UserStat::where(['user_id'=>$saveArray['user_id']])->whereBetween('created_at', [$saveArray['date'], $newdateWeekOne])->get();
					
						foreach($firstWeekData as $userstats)
						{
							
							print_r($userstats);
							// if($userstats['type']=='running')
							// {
								
							// }
							
							// else if($userstats['type']=='walking')
							// {
								
							// }
							// else if($userstats['type']=='cycling')
							// {
								
							// }
							
							
						}
					
					}
					
					elseif($newdateWeekOne<=$saveArray['date']&&$saveArray['date']<$newdateWeekTwo)
					{
						
					$twoWeekData = UserStat::where(['user_id'=>$saveArray['user_id']])->whereBetween('created_at', [$newdateWeekOne, $newdateWeekTwo])->get();
					print_r($twoWeekData);
					}
					
					elseif($newdateWeekTwo<=$saveArray['date']&&$saveArray['date']<$newdateWeekThree)
					{
					$threeWeekData = UserStat::where(['user_id'=>$saveArray['user_id']])->whereBetween('created_at', [$newdateWeekTwo, $newdateWeekThree])->get();
					print_r($threeWeekData);
					}
					else
					{
						
					$fourWeekData = UserStat::where(['user_id'=>$saveArray['user_id']])->whereBetween('created_at', [$newdateWeekThree, $newdateWeekFour])->get();
					print_r($fourWeekData);
					}
					
					die();
					
					$firstWeekData = UserStat::where(['user_id'=>$saveArray['user_id']])->whereBetween('created_at', [$d, $newdateWeekOne])->get();
					
					     $totalDistance  =0;
						 $totalCalories  =0;
						 $totalSpeed  =0;
						 $cal = array();
						 $totalDistanceRunningWeekOne = 0;
						 $totalDistanceWalkingWeekOne = 0;
						 $totalDistanceCyclingWeekOne = 0;
						 
						 $totalDistanceRunningWeekTwo = 0;
						 $totalDistanceWalkingWeekTwo = 0;
						 $totalDistanceCyclingWeekTwo = 0;
						 
						 $totalDistanceRunningWeekThree = 0;
						 $totalDistanceWalkingWeekThree = 0;
						 $totalDistanceCyclingWeekThree = 0;
						 
						 $totalDistanceRunningWeekFour = 0;
						 $totalDistanceWalkingWeekFour = 0;
						 $totalDistanceCyclingWeekFour = 0;
						 $runningCount = 0;
						 $walkingCount = 0;
						 $cyclingCount = 0;
						 
								foreach($firstWeekData as $userstat){
									
									$totalDistance +=$userstat['distance'];
									$totalCalories +=$userstat['calories'];
									$totalSpeed +=$userstat['speed'];
									
									if($userstat['type'] == "running"){
										 $totalDistanceRunningWeekOne +=$userstat['distance'];
										  $runningCount ++;
										
									}
									
									else if($userstat['type'] == "walking"){
										 $totalDistanceWalkingWeekOne +=$userstat['distance'];
										  $walkingCount++;
						
										
									}
									
									else if($userstat['type'] == "cycling"){
										 $totalDistanceCyclingWeekOne +=$userstat['distance'];
										  $cyclingCount++;
										
									}
									
								}
					
					
					
					$newdateWeekTwo = strtotime ('7 day' , strtotime($newdateWeekOne)) ;
					$newdateWeekTwo = date ( 'Y-m-j' , $newdateWeekTwo);
					
					$secondWeekData = UserStat::where(['user_id'=>$saveArray['user_id']])->whereBetween('created_at', [$newdateWeekOne, $newdateWeekTwo])->get();
					
				}
				
				else
				{
			    $userstats = UserStat::where(['user_id'=>$saveArray['user_id']])->whereBetween('created_at', [$saveArray['date'], $newdate])->get();

				}
				
				$userstatsCount = UserStat::where(['user_id'=>$saveArray['user_id']])->where('created_at', 'like',  '%' .$saveArray['date']. '%')->get()->count();
				
				$userstats = UserStat::where(['user_id'=>$saveArray['user_id']])->whereBetween('created_at', [$d, $lastDate])->get();
					
					     $totalDistance  =0;
						 $totalCalories  =0;
						 $totalSpeed  =0;
						 $cal = array();
						 $totalDistanceRunning = 0;
						 $totalDistanceWalking = 0;
						 $totalDistanceCycling = 0;
						 $runningCount = 0;
						 $walkingCount = 0;
						 $cyclingCount = 0;
								
								foreach($userstats as $userstat){
									
									$totalDistance +=$userstat['distance'];
									$totalCalories +=$userstat['calories'];
									$totalSpeed +=$userstat['speed'];
									
								}

					
				return response()->json([ 
						'status' => true,
						'data'=>[
						'totalDistance'=> $userSetting->unit_run==0?$totalDistance .' MI':$totalDistance*1.60934 .' KM',
						'totalCalories'=> $totalCalories, 
						'totalSpeed'=> $userSetting->unit_run==0?$totalSpeed/4 .'/MI':($totalSpeed/4)*1.60934
 .' /KM', 'weekOneRunningDistance'=>$totalDistanceRunningWeekOne?$totalDistanceRunningWeekOne/7:0,'weekOneWalkingDistance'=>$totalDistanceWalkingWeekOne?$totalDistanceWalkingWeekOne/7:0, 'weekOneCyclingDistance'=>$totalDistanceCyclingWeekOne?$totalDistanceCyclingWeekOne/7:0,
 'weekTwoRunningDistance'=>$totalDistanceRunningWeekTwo?$totalDistanceRunningWeekTwo/7:0, 'weekTwoWalkingDistance'=>$totalDistanceWalkingWeekTwo?$totalDistanceWalkingWeekTwo/7:0,'weekTwoCyclingDistance'=>$totalDistanceCyclingWeekTwo?$totalDistanceCyclingWeekTwo/7:0, 'weekThreeRunningDistance'=>$totalDistanceRunningWeekThree?$totalDistanceRunningWeekThree/7:0, 'weekThreeWalkingDistance'=>$totalDistanceWalkingWeekThree?$totalDistanceWalkingWeekThree/7:0,'weekThreeCyclingDistance'=>$totalDistanceCyclingWeekThree?$totalDistanceCyclingWeekThree/7:0, 'weekFourRunningDistance'=>$totalDistanceRunningWeekFour?$totalDistanceRunningWeekFour/7:0, 'weekFourWalkingDistance'=>$totalDistanceWalkingWeekFour?$totalDistanceWalkingWeekFour/7:0,'weekFourCyclingDistance'=>$totalDistanceCyclingWeekFour?$totalDistanceCyclingWeekFour/7:0]
						
					], $this->successCode);
			
		
		
	}
	
	}
	
	public function getUserStatRunningDaily(Request $request)
	{
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
			'date' => 'required',
			 'type'=>'required'
		]);
		
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode); 
			
		}else{
			
				
			$newdate = strtotime ( '1 day' , strtotime ( $saveArray['date']) ) ;
			$newdate = date ( 'Y-m-d' , $newdate );
		    $dailyData = UserStat::where(['user_id'=>$saveArray['user_id']])->where('created_at','>=', $saveArray['date'])->where('created_at','<=', $newdate)->get();
		   $userSetting = Setting::where('user_id', '=', $saveArray['user_id'])->first();
			
			$totalDistance  =0;
			 $totalCalories  =0;
			 $totalSpeed  =0;
			 $totalDistanceRunningDaily = 0;
		     $totalDistanceWalkingDaily = 0;
			 $totalDistanceCyclingDaily = 0;
			 $runningCount = 0;
			 $walkingCount = 0;
		     $cyclingCount = 0;
			 
			foreach($dailyData as $userstat){
									
									$totalDistance +=$userstat['distance'];
									$totalCalories +=$userstat['calories'];
									$totalSpeed +=$userstat['speed'];
									
									if($userstat['type'] == "running"){
										 $totalDistanceRunningDaily +=$userstat['distance'];
										  $runningCount ++;
										
									}
									
									else if($userstat['type'] == "walking"){
										 $totalDistanceWalkingDaily +=$userstat['distance'];
										  $walkingCount++;
						
										
									}
									
									else if($userstat['type'] == "cycling"){
										 $totalDistanceCyclingDaily +=$userstat['distance'];
										  $cyclingCount++;
										
									}
									
								}
								
					return response()->json([ 
						'status' => true,
						'data'=>[
						'totalDistance'=> $userSetting->unit_run==0?$totalDistance .' MI':$totalDistance*1.60934 .' KM',
						'totalCalories'=> $totalCalories, 
						'totalSpeed'=> $userSetting->unit_run==0?$totalSpeed .'/MI':($totalSpeed)*1.60934
 .' /KM', 'DailyRunningDistance'=>$totalDistanceRunningDaily?$totalDistanceRunningDaily:0,'DailyWalkingDistance'=>$totalDistanceWalkingDaily?$totalDistanceWalkingDaily:0, 'DailyCyclingDistance'=>$totalDistanceCyclingDaily?$totalDistanceCyclingDaily:0,
 ]	
					], $this->successCode);
								
							
			
		}
	
	}

	
	

#************************************************************************************#	

	
	public function getForRunning(Request $request){
		
			$saveArray = $request->all();
		
			$validator = Validator::make($request->all(), [ 
				'user_id' => 'required',	
				'type' => 'required',	
				//'page_for' => 'required',	
			]);
			
			if($validator->fails()) {
				
				return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);     
				
			}else{
			
					$saveArray['page_for']='running';
					$saveArray['approval']="approved";

					$records=array();
		
					if( $saveArray['type'] == 'workout' || $saveArray['type'] =='myworkout'){
						
						if($saveArray['type'] =='workout'){	
							
							// check for approved workout also
							$runnings = Workout::where(['page_for'=>$saveArray['page_for'],'type'=>$saveArray['type'],'approval'=>$saveArray['approval']])->get()->toarray();
							if($runnings){
							
								foreach($runnings as $workouts){
									
									$trainerDetails = Trainer::where(['_id'=>$workouts['user_id']])->first();
									$rating = RateWorkout::where(['workout_id'=>$workouts['_id']])->avg('rating');
									$image = url('public/images',$workouts['image']);
									$helper = new Helpers();
										
									$records[]=array(
										'workout_id'=>$workouts['_id'],
										'user_id'=>$workouts['user_id'],
										'by'=>ucwords($trainerDetails->name)?$trainerDetails->name:'',
										'title'=>ucwords($workouts['title']),
										'description'=>$workouts['description'],
										'image'=>$image,
										'price'=>$workouts['amount'],
										'exercises'=>$workouts['exercises']==""?null:$workouts['exercises'],
										'time'=>$workouts['time'],
										'period'=>$workouts['period'],
										'created_at'=>$workouts['created_at'],
										'rating'=>$rating?$rating:0,
										'ago'=>$helper->time_elapsed_string($workouts['created_at']),
									);
									
								}
								
							}else{
								return response()->json([ 
									'status' => false,
									'message' => 'Not found',
								], $this->successCode);
							}	

						}
						
						if($saveArray['type'] =='myworkout'){	
							
							
							$runnings = MyRunning::where(['user_id'=> $saveArray['user_id']])->get()->toarray();
							if($runnings){
							
								foreach($runnings as $workouts){

									$workouts = Workout::where(['_id'=> $workouts['running_id']])->first();
									
									$trainerDetails = Trainer::where(['_id'=>$workouts['user_id']])->first();
									$rating = RateWorkout::where(['workout_id'=>$workouts['running_id']])->avg('rating');
									$image = url('public/images',$workouts['image']);

									$helper = new Helpers();
										
									$records[]=array(
										'workout_id'=>$workouts['_id'],
										'user_id'=>$workouts['user_id'],
										'by'=>ucwords($trainerDetails->name)?$trainerDetails->name:'',
										'title'=>ucwords($workouts['title']),
										'description'=>$workouts['description'],
										'image'=>$image,
										'price'=>$workouts['amount'],
										//'exercises'=>$workouts['exercises'],
										'exercises'=>$workouts['exercises']==""?null:$workouts['exercises'],
										'time'=>$workouts['time'],
										'period'=>$workouts['period'],
										'created_at'=>(String)$workouts['created_at'],
										'rating'=>$rating?$rating:0,
										'ago'=>$helper->time_elapsed_string($workouts['created_at']),
									);
									
								}
								
							}else{
								return response()->json([ 
									'status' => false,
									'message' => 'Not found',
								], $this->successCode);
							}	
							
							
						}
					
						
					}
			
				return response()->json([ 
					'status' => true,
					'message' => 'Success',
					'data'=>$records,
				], $this->successCode);
	
		}

	}

	public function dietDailyDetails(Request $request){
		
			$saveArray = $request->all();
		
			$validator = Validator::make($request->all(), [ 
				'diet_id' => 'required',	
				'day' => 'required',
				'week'=>'required'
			]);
			
			if($validator->fails()) {
				
				return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);     
				
			}else{
						$details_id=DietDetailWeek::where(['diet_id'=>$saveArray['diet_id']])->where(['week'=>$saveArray['week']])->where(['day'=>$saveArray['day']])->first();

				$details = DietDetail::where(['_id'=>$details_id['DietDetail_id']])->where(['day'=>$saveArray['day']])->first();
				if($details){
					return response()->json([ 
							'status' => true,
							'message' => 'Success',
							'description'=>$details['daily_desc'],
						], $this->successCode);
				}
				else{
					return response()->json([ 
								'status' => false,
								'message' => 'Not found',
							], $this->successCode);
				}
	
		}

	}

	public function getAllWorkoutDetail(Request $request){
		
			$saveArray = $request->all();
		
			$validator = Validator::make($request->all(), [ 
				'workout_id' => 'required',	
				'day' => 'required'
			]);
			
			if($validator->fails()) {
				
				return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);     
				
			}else{
			
				$details = WorkoutDetail::where(['workout_id'=>$saveArray['workout_id']])->where(['day'=>$saveArray['day']])->first();
				if($details){
					return response()->json([ 
							'status' => true,
							'message' => 'Success',
							'description'=>$details['daily_desc'],
						], $this->successCode);
				}
				else{
					return response()->json([ 
								'status' => false,
								'message' => 'Not found',
							], $this->successCode);
				}
	
		}

	}

#************************************************************************************#	


	public function WorkoutDayDetail(Request $request){
		
			$saveArray = $request->all();
		
			$validator = Validator::make($request->all(), [ 
				'workout_id' => 'required',	
				'day' => 'required',
				'type'=>'required',
				//'week'=>'required'
			]);
			
			if($validator->fails()) {
				
				return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);     
				
			}else{
				
				if(!empty($request->week))
				{
				$details = WorkoutDetail::where(['workout_id'=>$saveArray['workout_id']])->where(['day'=>$saveArray['day']])->where(['week'=>$saveArray['week']])->get()->toarray();
				}
				else
				{
					
			   $details = WorkoutDetail::where(['workout_id'=>$saveArray['workout_id']])->where(['day'=>$saveArray['day']])->get()->toarray();

				}
				
				
				// print_r($details);die;
				
				$totalTime = 0;
				
				if($details){
					foreach($details as $details){
						$workout = Workout::where(['_id'=>$details['workout_id']])->select('time')->first();
						
						$time = explode(' ',$workout['time']);
						$totalTime = $totalTime + $time[0];
						//print_R($time);
						
						if($saveArray['type'] == "weightlifting"){
							$record[]=array(
								'workout_id'=>$details['workout_id'],
								'day'=>$details['day'],
								'week'=>$details['week'],
								'title'=>$details['title'],
								'image'=>url('public/images/image',$details['image']),
								'reps'=>$details['reps'],
								'rest_time'=>$details['rest_time'],
								'time'=>$workout['time']
							);
						}
						else if($saveArray['type'] == "yoga"){
							
							$record[]=array(
								'workout_id'=>$details['workout_id'],
								'day'=>$details['day'],
								'title'=>$details['title'],
								'image'=>url('public/images/image',$details['image']),
								'sets'=>$details['sets'],
								'hold_time'=>$details['hold_time'],
								'time'=>$workout['time']
							);
						}
						else if($saveArray['type'] == "running"){
							$record[]=array(
								'workout_id'=>$details['workout_id'],
								'day'=>$details['day'],
								'title'=>$details['title'],
								'image'=>url('public/images/image',$details['image']),
								'sets'=>$details['sets'],
								'hold_stretch_for'=>$details['hold_stretch_for'],
								'time'=>$workout['time']
							);
						}
						
						
						
					}
					// print_r($record);die;
					return response()->json([ 
							'status' => true,
							'message' => 'Success',
							'record'=>$record,
							'totalTime'=>$totalTime.' Min',
						], $this->successCode);
				}
				else{
					return response()->json([ 
								'status' => false,
								'message' => 'Not found',
							], $this->successCode);
				}
	
		}

	}



#*************************************************************************************#	


	public function TrainingDayDetail(Request $request){
		
			$saveArray = $request->all();
		
			$validator = Validator::make($request->all(), [ 
				'training_id' => 'required',	
				'day' => 'required'
			]);
			
			if($validator->fails()) {
				
				return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);     
				
			}else{
			
				$details = TrainingDetail::where(['training_id'=>$saveArray['training_id']])->where(['day'=>$saveArray['day']])->get()->toarray();
				
				$totalTime = 0;
				//print_R($details);die;
				if($details){
					foreach($details as $details){
						
						$time = explode(' ',$details['time']);
						$totalTime = $totalTime + $time[0];
						//print_R($time);

						$record[]=array(
						'training_id'=>$details['training_id'],
						'day'=>$details['day'],
						'title'=>$details['title'],
						'image'=>url('public/images/image',$details['image']),
						'subtitle'=>$details['subtitle'],
						'time'=>$details['time'],
						'miles'=>$details['miles'],
						);
						
					}
					return response()->json([ 
							'status' => true,
							'message' => 'Success',
							'record'=>$record,
							'totalTime'=>$totalTime.' Min',
						], $this->successCode);
				}
				else{
					return response()->json([ 
								'status' => false,
								'message' => 'Not found',
							], $this->successCode);
				}
	
		}

	}



#*************************************************************************************#	


	public function addToMyDiet(Request $request){
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
			'diet_id' => 'required',	
			//'diet_status' => 'required',	
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			
			$diets = MyDiet::where(['user_id'=> $saveArray['user_id'],'diet_id'=> $saveArray['diet_id']])->first() ;
			
				if($diets){ 
			
					//MyDiet::where(['user_id'=> $saveArray['user_id'],'diet_id'=> $saveArray['diet_id']])->update(['diet_status'=>$saveArray['diet_status']]);
					
					return response()->json([ 
					'status' => false,
					'message' => 'Diet Alredy Added',
				], $this->successCode);
		        	
	
				}else{
					
					$saveArray = $request->all();
					
					$saveArray['diet_status']=1;
					
					$insertData = [
						'user_id'=>$saveArray['user_id'],
						'diet_id'=>$saveArray['diet_id'],
						
						'diet_status'=>$saveArray['diet_status'],
						'created_at'=>date('Y-m-d H:i:s')
					];
					$lastInsertId = MyDiet::insertGetId($insertData);
			
				}
		
				return response()->json([ 
					'status' => true,
					'message' => 'Success',
					//'diet_status' => $saveArray['diet_status'],
				], $this->successCode);
			
		}
	}



#*************************************************************************************#	

	public function myDiet(Request $request){
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			
		
			$diet = MyDiet::where(['user_id'=> $saveArray['user_id']])->get()->toarray();
	
			if($diet){
				
				foreach($diet as $diet){
					
					$diets = Diet::where(['_id'=> $diet['diet_id']])->first() ;
					
					$helper = new Helpers();
						
					$records[]=array(
					'diet_id'=>$diets['_id'],
					'user_id'=>$diets['user_id'],
					'name'=>$diets['name'],
					'description'=>$diets['description'],
					'image'=>url('public/images/diet',$diets['image']),
					'period'=>$diets['period'],
					'amount'=>$diets['amount'].' Cal/Day',
					'rating'=>$diets['rating'],
					'created_at'=>$helper->time_elapsed_string($diets['created_at']),
					);
				
				}
				
				return response()->json([ 
					'status' => true,
					'message' => 'Success',
					'data'=>$records,
				], $this->successCode);
			}
			else{
				return response()->json([ 
					'status' => false,
					'message' => 'Not found',
				], $this->successCode);
			}
		}

	}

#*************************************************************************************#	


	public function addWorkout(Request $request){
		
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
			'title' => 'required',	
			'description' => 'required',	
			'type' => 'required',	
			//'exercises' => 'required',	
			//'time' => 'required',	
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			
					$insertData = [
						'user_id'=>$saveArray['user_id'],
						'title'=>$saveArray['title'],
						'description'=>$saveArray['description'],
						'type'=>$saveArray['type'],
						'page_for'=>$saveArray['page_for'],
						'image'=>'default.png',
						//'category'=>$saveArray['category'],
						'exercises'=>$saveArray['exercises'],
						'time'=>$saveArray['time'],
						'rating'=>'',
						'created_at'=>date('Y-m-d H:i:s')
					];
					
					$workout_id = Workout::insertGetId($insertData);
					
				return response()->json([ 
					'status' => true,
					'message' => 'Success',
				], $this->successCode);
		
		}
				
	}


#*************************************************************************************#	



	public function addExercises(Request $request){
		
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
			'title' => 'required',	
			'description' => 'required',	
			'type' => 'required',	
			//'exercises' => 'required',	
			//'time' => 'required',	
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			
					$insertData = [
						'user_id'=>$saveArray['user_id'],
						'title'=>$saveArray['title'],
						'description'=>$saveArray['description'],
						'type'=>$saveArray['type'],
						'page_for'=>$saveArray['page_for'],
						'image'=>'default.png',
						'category'=>$saveArray['category'],
						'exercises'=>'',
						'time'=>'',
						'rating'=>'',
						'created_at'=>date('Y-m-d H:i:s')
					];
					
					$workout_id = Workout::insertGetId($insertData);
					
				return response()->json([ 
					'status' => true,
					'message' => 'Success',
				], $this->successCode);
		
		}
				
	}

#*************************************************************************************#	

	public function addYourComment(Request $request){
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
			'post_id' => 'required',	
			'comment' => 'required',	
			'type' => 'required',	
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
		
		$saveArray = $request->all();
		
		if(empty($saveArray['parent_id'])){
			
			$saveArray['parent_id']="";
		}
		
			$insertData = [
				'user_id'=>$saveArray['user_id'],
				'parent_id'=>$saveArray['parent_id'],
				'post_id'=>$saveArray['post_id'],
				'comment'=>$saveArray['comment'],
				'type'=>$saveArray['type'],
				'delete_status'=>'1',
				'created_at'=>date('Y-m-d H:i:s')
			];
			$comment_id = Comment::insertGetId($insertData);
			//echo  $comment_id;
			
				return response()->json([ 
					'status' => true,
					'message' => 'Success',
				], $this->successCode);
			
		}
	}


#*************************************************************************************#	


	public function getYourComment(Request $request){
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
			'post_id' => 'required',	
			'type' => 'required',	
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			
			//$comments = Comment::where(['user_id'=>$saveArray['user_id'],'post_id'=>$saveArray['post_id'],'parent_id'=>""])->get()->toarray();
			$comments = Comment::where(['post_id'=>$saveArray['post_id'],'parent_id'=>""])->get()->toarray();
	
			if($comments){
				
				$records=array();
				foreach($comments as $comments){
					
					$helper = new Helpers();
					
						$commentUserExist = User::where(['_id'=>$comments['user_id']])->first();
			
						$commentUserImage = "";
						if(strpos($commentUserExist['profile_image'], "http://") !== false){
							$commentUserImage = $commentUserExist['profile_image'];
						}
						else if(strpos($commentUserExist['profile_image'], "https://") !== false){
							$commentUserImage = $commentUserExist['profile_image'];
						}
						else{
							$commentUserImage = url('public/images',$commentUserExist['profile_image']);
						}
					
					
					$reply = Comment::where(['parent_id'=>$comments['_id']])->get()->toarray();
					
					$records1=array();
					
					foreach($reply as $reply){
						
						
							$replyUserExist = User::where(['_id'=>$reply['user_id']])->first();
				
							$replyUserImage = "";
							if(strpos($replyUserExist['profile_image'], "http://") !== false){
								$replyUserImage = $replyUserExist['profile_image'];
							}
							else if(strpos($replyUserExist['profile_image'], "https://") !== false){
								$replyUserImage = $replyUserExist['profile_image'];
							}
							else{
								$replyUserImage = url('public/images',$replyUserExist['profile_image']);
							}
						
						$records1[]=array(
						'comment_id'=>$reply['_id'],
						//'user_id'=>$reply['user_id'],
						//'post_id'=>$reply['post_id'],
						'userName'=>ucwords($replyUserExist['first_name'].' '.$replyUserExist['last_name']),
						'userImage'=>$replyUserImage,
						'comment'=>$reply['comment'],
						'created_at'=>$helper->time_elapsed_string($reply['created_at']),
						);
						
					}
					
					
						
					$records[]=array(
					'comment_id'=>$comments['_id'],
					//'user_id'=>$comments['user_id'],
					//'post_id'=>$comments['post_id'],
					'userName'=>ucwords($commentUserExist['first_name'].' '.$commentUserExist['last_name']),
					'userImage'=>$commentUserImage,
					'comment'=>$comments['comment'],
					'created_at'=>$helper->time_elapsed_string($comments['created_at']),
					'reply'=>$records1,
					);
				
				}//die;
				
				return response()->json([ 
					'status' => true,
					'message' => 'Success',
					'data'=>$records,
				], $this->successCode);
			}
			else{
				return response()->json([ 
					'status' => false,
					'message' => 'Not found',
				], $this->successCode);
			}
		}

	}

	public function rateWorkout(Request $request){
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
			'workout_id' => 'required',	
			'workout_type' => 'required',	
			'rating' => 'required',	
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
		
		$saveArray = $request->all();
		
			$insertData = [
				'user_id'=>$saveArray['user_id'],
				'workout_id'=>$saveArray['workout_id'],
				'workout_type'=>$saveArray['workout_type'],
				'rating'=>(int)$saveArray['rating'],
				'created_at'=>date('Y-m-d H:i:s')
			];
			$rate_id = RateWorkout::insertGetId($insertData);
			//echo  $comment_id;
			
				return response()->json([ 
					'status' => true,
					'message' => 'Success',
				], $this->successCode);
			
		}
	}

	public function nearbyGymPartner(Request $request){
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',
			'lat' => 'required',
			'long' => 'required',
		]);
		if($validator->fails()) {
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			$userdetails = User::where(['_id'=>$saveArray['user_id']])->where(['gym_partner_status'=>'on'])->select('gym_partner_status', 'goal1','goal2','goal3')->first();
			           
					    $goal1 =$userdetails['goal1']; 
						$goal2 =$userdetails['goal2']; 
						$goal3 =$userdetails['goal3'];
						$ids = array($goal1,$goal2 ,$goal3);
		
		 				
			if($userdetails){
				
				$gym_partner_status = $userdetails['gym_partner_status'];
				$lat = floatval($saveArray['lat']);
				$long = floatval($saveArray['long']);
				$nearby_partners = User::where('loc', 'near', [
				    '$geometry' => [
				        'type' => 'Point',
				        'coordinates' => [
				        	$long,
				            $lat
				        ],
				    ],
				    '$maxDistance' => 100000,
				])->where('_id','!=',$saveArray['user_id'])->select('_id','first_name','last_name')->get();
				$main_arr = [];
				$matched = User::where(['goal1'=>$goal1])->where(['goal2'=>$goal2])->where(['goal3'=>$goal3])->get();
				$unmatched = User::where('goal1','!=',$goal1)->where('goal2','!=',$goal2)->where('goal3','!=',$goal3)->get();
				
				$matches = $matched->merge($unmatched);

				foreach($nearby_partners as $partner){
						
					 $user_likes = LikeGymPartner::where('user_id', '=', $saveArray['user_id'])->where('partner_id', '=', $partner['_id'])->where('like_status', '=', 'like')->first();

					if(empty($user_likes))
					{	
				    $userDetails = $matches->whereIn('_id',$partner['_id'])->first();
					if($userDetails['gym_partner_status'] == "on"){
						$user_arr = array();
						$imageDetails = Post::where(['user_id'=>$partner['_id']])->where('image','!=','')->count();
						$videoDetails = Post::where(['user_id'=>$partner['_id']])->where('video','!=','')->count();
						
						$postCount = $imageDetails + $videoDetails;
						if(!empty($userDetails['profile_image']))
						{
							$profile_image = url('public/images',$userDetails['profile_image']);
						}
						else
						{
							$profile_image = '';
						}
						$imageDetails = Post::where(['user_id'=>$partner['_id']])->where('image','!=','')->get();
							$post_arr = array();
							$iCount = 1;
							foreach($imageDetails as $image){
								$post_image = url('public/images/image',$image['image']);
								if($iCount <= 3){
									array_push($post_arr, $post_image);
								}
								$iCount++;
							}

							if($iCount <= 3){
								$videoDetails = Post::where(['user_id'=>$partner['_id']])->where('video','!=','')->get();
								foreach($videoDetails as $video){
									$post_video=url('public/images/video',$video['video']);
									if($iCount <= 3){
										array_push($post_arr, $post_video);
									}
									$iCount++;
								}
							}
							$user_arr = array(
								'user_id'=>$partner['_id'],
								'first_name'=>$userDetails['first_name'],
								'last_name'=>$userDetails['last_name'],
								'profile_image'=>$profile_image,
								'post_arr'=>$post_arr
							);
							array_push($main_arr, $user_arr);
					}
					else{
						$user_arr = array();
						if(!empty($userDetails['profile_image']))
						{
							$profile_image = url('public/images',$userDetails['profile_image']);
						}
						else
						{
							$profile_image = '';
						}
						$user_arr = array(
								'user_id'=>$partner['_id'],
								'first_name'=>$userDetails['first_name'],
								'last_name'=>$userDetails['last_name'],
								'profile_image'=>$profile_image
							);
						array_push($main_arr, $user_arr);
					}
				 }
				}
				if(count($main_arr) > 0){
					return response()->json([ 
						'status' => true,
						'message' => 'Success',
						'list'=>$main_arr
					], $this->successCode);
				}
				else{
					return response()->json([ 
						'status' => true,
						'message' => 'No User Found',
					], $this->successCode);
				}
			}
			else{
				return response()->json(['message'=>'User does not exist','status'=>false], $this->successCode); 
			}
		}
	}

	public function likeGymPartner(Request $request){
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
			'partner_id' => 'required',	
			'like_status' => 'required',	
		]);
		if($validator->fails()) {
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			$likes = LikeGymPartner::where(['user_id'=> $saveArray['user_id'],'partner_id'=> $saveArray['partner_id']])->first() ;
			
				if($likes){ 
			
					LikeGymPartner::where(['user_id'=> $saveArray['user_id'],'partner_id'=> $saveArray['partner_id']])->update(['like_status'=>$saveArray['like_status']]);
				}else{
					$saveArray = $request->all();
					$insertData = [
						'user_id'=>$saveArray['user_id'],
						'partner_id'=>$saveArray['partner_id'],
						'like_status'=>$saveArray['like_status'],
						'created_at'=>date('Y-m-d H:i:s')
					];
					$like_id = LikeGymPartner::insertGetId($insertData);
				}
				$datetime = date('Y-m-d H:i:s');
				if($saveArray['like_status'] == "like"){
					$userdetails = User::where(['_id'=>$saveArray['user_id']])->first();
					$otherUserdetails = User::where(['_id'=>$saveArray['partner_id']])->first();
					$device_type = $otherUserdetails->device_type;
					$device_token = [$otherUserdetails->device_token];

					$user_image = "";
					if(strpos($userdetails['profile_image'], "http://") !== false){
						$user_image = $userdetails['profile_image'];
					}
					else if(strpos($userdetails['profile_image'], "https://") !== false){
						$user_image = $userdetails['profile_image'];
					}
					else{
						$user_image = url('public/images',$userdetails['profile_image']);
					}

					$message =[
						'message' =>'You are liked as Gym Partner by '.$userdetails['first_name'],
						'other_user_id' => $saveArray['partner_id'],
						'user_id' => $saveArray['user_id'],
						'user_name' => $userdetails['first_name']." ".$userdetails['last_name'],
						'user_image' => $user_image,
						'noti_type' => 'gym_partner',
						'datetime' => $datetime
					];
                  
					if($otherUserdetails->device_token != ""){
						if($device_type == "A"){
							ApiController::android_send_notification($device_token,$message);
						}

						$noti_data = [
							'noti_type'=>'gym_partner',
							'notified_id'=>$saveArray['partner_id'],
							'message'=>'You are liked as Gym Partner by '.$userdetails['first_name'],
							'msg_json'=>json_encode($message),
							'created_at'=>$datetime
						];
						$notify = Notification::insertGetId($noti_data);

						$noti_read_data = [
							'user_id'=>$saveArray['partner_id'],
							'noti_id'=>(String)$notify,
							'read_status'=>'0',
							'created_at'=>$datetime
						];
						$notify = readNotify::insert($noti_read_data);
					}
				}
		
				return response()->json([ 
					'status' => true,
					'message' => 'Success',
					'like_status' => $saveArray['like_status'],
				], $this->successCode);
			
		}
	}

	public function favoriteGymPartner(Request $request){
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
			'partner_id' => 'required',	
			'favorite_status' => 'required',	
		]);
		if($validator->fails()) {
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			
			$favorites = FavoriteGymPartner::where(['user_id'=> $saveArray['user_id'],'partner_id'=> $saveArray['partner_id']])->first();
			
				if($favorites){
					FavoriteGymPartner::where(['user_id'=> $saveArray['user_id'],'partner_id'=> $saveArray['partner_id']])->update(['favorite_status'=>$saveArray['favorite_status']]);
				}else{
					$saveArray = $request->all();
		
					$insertData = [
						'user_id'=>$saveArray['user_id'],
						'partner_id'=>$saveArray['partner_id'],
						'favorite_status'=>$saveArray['favorite_status'],
						'created_at'=>date('Y-m-d H:i:s')
					];
					$favorite_id = FavoriteGymPartner::insertGetId($insertData);
			
				}
		
				return response()->json([ 
					'status' => true,
					'message' => 'Success',
					'favorite_status' => $saveArray['favorite_status'],
				], $this->successCode);
			
		}
	}

	public function myMatches(Request $request){
	
	$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			$likes = LikeGymPartner::where(['user_id'=>$saveArray['user_id'],'like_status'=>'like'])->get()->toarray();
			
			if($likes){
				$records = array();
				foreach($likes as $like){
					$check = LikeGymPartner::where(['user_id'=>$like['partner_id']])->where(['partner_id'=>$saveArray['user_id']])->where(['like_status'=>'like'])->first();

					if($check){
						$user_exist = User::where(['_id'=>$like['partner_id']])->first()->toarray();
						$profile_image = "";
						if(strpos($user_exist['profile_image'], "http://") !== false){
							$profile_image = $user_exist['profile_image'];
						}
						else if(strpos($user_exist['profile_image'], "https://") !== false){
							$profile_image = $user_exist['profile_image'];
						}
						else{
							$profile_image = url('public/images',$user_exist['profile_image']);
						}

						$goal = array(
							$user_exist['goal1']?$user_exist['goal1']:'',
							$user_exist['goal2']?$user_exist['goal2']:'',
							$user_exist['goal3']?$user_exist['goal3']:'',
						);
				
						$records[]=array(
							'user_id'=>(String)$user_exist['_id'],
							'first_name'=>$user_exist['first_name'],
							'last_name'=>$user_exist['last_name'],
							'email'=>$user_exist['email'],
							'location'=>$user_exist['location'],
							'profile_image'=>$profile_image,
							'verified'=>'Y',
							'goal'=>$goal
						);
					}
				
				}

				if(count($records)){
					return response()->json([ 
						'status' => true,
						'message' => 'Success',
						'data'=>$records,
					], $this->successCode);
				}
				else{
					return response()->json([ 
						'status' => false,
						'message' => 'Not found',
					], $this->successCode);
				}
			}
			else{
				return response()->json([ 
					'status' => false,
					'message' => 'Not found',
				], $this->successCode);
			}
		}
	}


	public function myGymPartnerFavorites(Request $request){
		
		$saveArray = $request->all();
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required'
		]);
		if($validator->fails()) {
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			$my_favorites = FavoriteGymPartner::where(['user_id'=>$saveArray['user_id'],'favorite_status'=>'favorite'])->get()->toarray();
			$my_favorites_arr = array();
			foreach($my_favorites as $favorites){
					
					$user_exist = User::where(['_id'=>$favorites['partner_id']])->first()->toarray();
					$profile_image = "";
					if(strpos($user_exist['profile_image'], "http://") !== false){
						$profile_image = $user_exist['profile_image'];
					}
					else if(strpos($user_exist['profile_image'], "https://") !== false){
						$profile_image = $user_exist['profile_image'];
					}
					else{
						$profile_image = url('public/images',$user_exist['profile_image']);
					}
			
					$my_favorites_arr[]=array(
						'user_id'=>(String)$user_exist['_id'],
						'first_name'=>$user_exist['first_name'],
						'last_name'=>$user_exist['last_name'],
						'email'=>$user_exist['email'],
						'location'=>$user_exist['location'],
						'profile_image'=>$profile_image,
						'verified'=>'Y'
					);
				
				}

			$favorite_me = FavoriteGymPartner::where(['partner_id'=>$saveArray['user_id'],'favorite_status'=>'favorite'])->get()->toarray();
			$favorite_me_arr = array();
			foreach($favorite_me as $favorites){
					
					$user_exist = User::where(['_id'=>$favorites['user_id']])->first()->toarray();
					$profile_image = "";
					if(strpos($user_exist['profile_image'], "http://") !== false){
						$profile_image = $user_exist['profile_image'];
					}
					else if(strpos($user_exist['profile_image'], "https://") !== false){
						$profile_image = $user_exist['profile_image'];
					}
					else{
						$profile_image = url('public/images',$user_exist['profile_image']);
					}
			
					$favorite_me_arr[]=array(
						'user_id'=>(String)$user_exist['_id'],
						'first_name'=>$user_exist['first_name'],
						'last_name'=>$user_exist['last_name'],
						'email'=>$user_exist['email'],
						'location'=>$user_exist['location'],
						'profile_image'=>$profile_image,
						'verified'=>'Y'
					);
				
				}
			return response()->json([ 
				'status' => true,
				'message' => 'my favorites and favorite me list',
				'my_favorites'=>$my_favorites_arr,
				'favorite_me'=>$favorite_me_arr
			], $this->successCode);
		}	
	}

	public function addWorkoutInterest(Request $request){
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
			'partner_id' => 'required',	
			'interest_status' => 'required',	
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			
			$favorites = WorkoutInterest::where(['user_id'=> $saveArray['user_id'],'partner_id'=> $saveArray['partner_id']])->first() ;
			
				if($favorites){ 
			
					WorkoutInterest::where(['user_id'=> $saveArray['user_id'],'partner_id'=> $saveArray['partner_id']])->update(['interest_status'=>$saveArray['interest_status']]);
		        	
	
				}else{
					
					$saveArray = $request->all();
				
					$insertData = [
						'user_id'=>$saveArray['user_id'],
						'partner_id'=>$saveArray['partner_id'],
						'interest_status'=>$saveArray['interest_status'],
						'created_at'=>date('Y-m-d H:i:s')
					];
					$favorite_id = WorkoutInterest::insertGetId($insertData);
			
				}
				
				
				
				if($saveArray['interest_status'] == "yes"){
					
					$datetime = date('Y-m-d H:i:s');
					
					$userdetails = User::where(['_id'=>$saveArray['user_id']])->first();
					 $otherUserdetails = User::where(['_id'=>$saveArray['partner_id']])->first();
					 if(empty($otherUserdetails))
					    $otherUserdetails = Trainer::where(['_id'=>$saveArray['partner_id']])->first();
					$device_type = $otherUserdetails->device_type;
					// $device_token = [$otherUserdetails->device_token];
					$device_token = $otherUserdetails->device_token;
					
					$user_image = "";
					if(strpos($userdetails['profile_image'], "http://") !== false){
						$user_image = $userdetails['profile_image'];
					}
					else if(strpos($userdetails['profile_image'], "https://") !== false){
						$user_image = $userdetails['profile_image'];
					}
					else{
						$user_image = url('public/images',$userdetails['profile_image']);
					}

					$message =[
						'message' =>'Added you as a Workout Interest',
						'other_user_id' => $saveArray['partner_id'],
						'user_id' => $saveArray['user_id'],
						'user_name' => $userdetails['first_name']." ".$userdetails['last_name'],
						'user_image' => $user_image,
						'noti_type' => 'workout_interest',
						'datetime' => $datetime
					];

					if($otherUserdetails->device_token != ""){
						if($device_type == "A"){
							
							ApiController::android_send_notification($device_token,$message);
						}

						$noti_data = [
							'noti_type'=>'workout_interest',
							'notified_id'=>$saveArray['partner_id'],
							'message'=>'Added you as a Workout Interest.',
							'msg_json'=>json_encode($message),
							'created_at'=>$datetime
						];
						$notify = Notification::insertGetId($noti_data);

						$noti_read_data = [
							'user_id'=>$saveArray['partner_id'],
							'noti_id'=>(String)$notify,
							'read_status'=>'0',
							'created_at'=>$datetime
						];
						$notify = readNotify::insert($noti_read_data);
					}
				}
		
				return response()->json([ 
					'status' => true,
					'message' => 'Success',
					'interest_status' => $saveArray['interest_status'],
				], $this->successCode);
			
		}
	}

		public function removeWorkoutInterest(Request $request){
		
		$saveArray = $request->all();
			$deleteData = DB::table('workout_interest')->where('user_id', $saveArray['user_id'])->where('partner_id', $saveArray['partner_id'])->delete();
			if ($deleteData) {
				return response()->json([ 
				'status' => true,
				'message' => 'sucess'
			], $this->successCode);
			}
			else{
				return response()->json([
					'message'=>'interest does not exists',
					'status'=>false
				],$this->successCode); 
			}
		}

	public function myWorkoutInterest(Request $request){
		
		$saveArray = $request->all();
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required'
		]);
		if($validator->fails()) {
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			$my_interests = WorkoutInterest::where(['user_id'=>$saveArray['user_id'],'interest_status'=>'yes'])->get()->toarray();
			$my_interests_arr = array();
			foreach($my_interests as $interests){
					
					$user_exist = User::where(['_id'=>$interests['partner_id']])->first();
					if(empty($user_exist))
						$user_exist = Trainer::where(['_id'=>$interests['partner_id']])->first();
					$profile_image = "";
					if(strpos($user_exist['profile_image'], "http://") !== false){
						$profile_image = $user_exist['profile_image'];
					}
					else if(strpos($user_exist['profile_image'], "https://") !== false){
						$profile_image = $user_exist['profile_image'];
					}
					else{
						$profile_image = url('public/images',$user_exist['profile_image']);
					}
			
					$my_interests_arr[]=array(
						'user_id'=>(String)$user_exist['_id'],
						'first_name'=>$user_exist['first_name']?$user_exist['first_name']:$user_exist['name'],
						'last_name'=>$user_exist['last_name'],
						'email'=>$user_exist['email'],
						'location'=>$user_exist['location']?$user_exist['location']:'',
						'profile_image'=>$profile_image,
						'verified'=>'Y'
					);
				
				}

			$interests_me = WorkoutInterest::where(['partner_id'=>$saveArray['user_id'],'interest_status'=>'yes'])->get()->toarray();
			$interests_me_arr = array();
			foreach($interests_me as $interests){
					
					$user_exist = User::where(['_id'=>$interests['user_id']])->first();
					if(empty($user_exist))
						$user_exist = Trainer::where(['_id'=>$interests['partner_id']])->first();
					$profile_image = "";
					if(strpos($user_exist['profile_image'], "http://") !== false){
						$profile_image = $user_exist['profile_image'];
					}
					else if(strpos($user_exist['profile_image'], "https://") !== false){
						$profile_image = $user_exist['profile_image'];
					}
					else{
						$profile_image = url('public/images',$user_exist['profile_image']);
					}
			
					$interests_me_arr[]=array(
						'user_id'=>(String)$user_exist['_id'],
						'first_name'=>$user_exist['first_name']?$user_exist['first_name']:$user_exist['name'],
						'last_name'=>$user_exist['last_name'],
						'email'=>$user_exist['email'],
						'location'=>$user_exist['location'],
						'profile_image'=>$profile_image,
						'verified'=>'Y'
					);
				
				}
			return response()->json([ 
				'status' => true,
				'message' => 'my interests and interested me list',
				'my_interests'=>$my_interests_arr,
				'interests_me'=>$interests_me_arr
			], $this->successCode);
		}	
	}

	public function saveState(Request $request){
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
			'distance' => 'required',	
			'time' => 'required',	
			'speed' => 'required',	
			'calories' => 'required',	
			'ratting' => 'required',
			'type'=>'required',
			'day'=>'required'
			
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
		
			$insertData = [
				'user_id'=>$saveArray['user_id'],
				'distance'=>$saveArray['distance'],
				'day'=>$saveArray['day'],
				'time'=>$saveArray['time'],
				'speed'=>$saveArray['speed'],
				'calories'=>$saveArray['calories'],
				'ratting'=>$saveArray['ratting'],
				// 'type'=>"running",
				'type'=>$saveArray['type'],
				'created_at'=>date('Y-m-d H:i:s')
			];
			$insert_id = UserStat::insertGetId($insertData);
				return response()->json([ 
					'status' => true,
					'message' => 'Success'
				], $this->successCode);
			
		}
	}


	public function deletePost(Request $request){
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
			'post_id' => 'required'
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			$post_exist = Post::where(['_id'=>$saveArray['post_id']])->where(['user_id'=>$saveArray['user_id']])->first();
			if($post_exist){
				$delete = Post::where(['_id'=>$saveArray['post_id']])->where(['user_id'=>$saveArray['user_id']])->delete();
				if($delete){
					return response()->json([ 
						'status' => true,
						'message' => 'Success'
					], $this->successCode);
				}
			}
			else{
				return response()->json([ 
					'status' => false,
					'message' => 'Post not exist'
				], $this->successCode);
			}
		}
	}

	public function createChat(Request $request){
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
			'other_user_id' => 'required',
			'chat_type' => 'required'
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			$chat_id = "";
			$chatListDetail = ChatList::where(['user_id'=>$saveArray['user_id']])->where(['other_user_id'=>$saveArray['other_user_id']])->where(['chat_type'=>$saveArray['chat_type']])->first();
			if($chatListDetail){
				$chat_id = (String)$chatListDetail['_id'];
				return response()->json([ 
					'status' => false,
					'message' => 'Chat already created',
					'chat_id' => $chat_id,
				], $this->successCode);
			}
			else{
				$chatListDetail = ChatList::where(['user_id'=>$saveArray['other_user_id']])->where(['other_user_id'=>$saveArray['user_id']])->where(['chat_type'=>$saveArray['chat_type']])->first();
				if($chatListDetail){
					$chat_id = (String)$chatListDetail['_id'];
					return response()->json([ 
						'status' => false,
						'message' => 'Chat already created',
						'chat_id' => $chat_id,
					], $this->successCode);
				}
				else{
					$insertData = [
						'user_id'=>$saveArray['user_id'],
						'other_user_id'=>$saveArray['other_user_id'],
						'chat_type'=>$saveArray['chat_type'],
						'group_name'=>$saveArray['group_name'],
						'created_at'=>date('Y-m-d H:i:s')
					];
					$chat_id = ChatList::insertGetId($insertData);
					return response()->json([ 
						'status' => true,
						'message' => 'Success',
						'chat_id' => (String)$chat_id,
					], $this->successCode);
				}
			}
		}
	}

	public function getChatList(Request $request){
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required'
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			$chatListarr = [];
			//normal chat
			$chatListDetail = ChatList::where(['user_id'=>$saveArray['user_id']])->orwhere(['other_user_id'=>$saveArray['user_id']])->where(['chat_type'=>'normal'])->get();
			if($chatListDetail){
				foreach($chatListDetail as $chatList){
					$chat_id = (String)$chatList['_id'];
					$chatdetails = Chat::where(['chat_id'=>$chat_id])->orderBy('_id','desc')->first();
					$lastMessage = $chatdetails['message'];
					$msgTime = (String)$chatdetails['created_at'];
					if($chatList['user_id'] != $saveArray['user_id']){
						$user_exist = User::where(['_id'=>$chatList['user_id']])->first();
						if($user_exist){
							$profile_image = "";
							if(strpos($user_exist['profile_image'], "http://") !== false){
								$profile_image = $user_exist['profile_image'];
							}
							else if(strpos($user_exist['profile_image'], "https://") !== false){
								$profile_image = $user_exist['profile_image'];
							}
							else{
								$profile_image = url('public/images',$user_exist['profile_image']);
							}

							$chatuser = array(
								'user_id'=>(String)$user_exist['_id'],
								'first_name'=>$user_exist['first_name'],
								'last_name'=>$user_exist['last_name'],
								'profile_image'=>$profile_image,
								'chat_id'=>$chat_id,
								'last_message'=>$lastMessage?$lastMessage:'',
								'msg_time'=>$msgTime?$msgTime:'',
								'chatType'=> "normal",
							);

							$chatListarr[] = $chatuser;
						}
					}
					if($chatList['other_user_id'] != $saveArray['user_id']){
						$user_exist = User::where(['_id'=>$chatList['other_user_id']])->first();
						if($user_exist){
							$profile_image = "";
							if(strpos($user_exist['profile_image'], "http://") !== false){
								$profile_image = $user_exist['profile_image'];
							}
							else if(strpos($user_exist['profile_image'], "https://") !== false){
								$profile_image = $user_exist['profile_image'];
							}
							else{
								$profile_image = url('public/images',$user_exist['profile_image']);
							}

							$chatuser = array(
								'user_id'=>(String)$user_exist['_id'],
								'first_name'=>$user_exist['first_name'],
								'last_name'=>$user_exist['last_name'],
								'profile_image'=>$profile_image,
								'chat_id'=>$chat_id,
								'last_message'=>$lastMessage?$lastMessage:'',
								'msg_time'=>$msgTime?$msgTime:'',
								'chatType'=> "normal",
							);

							$chatListarr[] = $chatuser;
						}
					}
				}
			}

			//group chat
			$chatList1 = ChatList::where(['user_id'=>$saveArray['user_id']])->where(['chat_type'=>'group'])->get();
			if($chatList1){
				foreach($chatList1 as $chatList){
					$chatusers = [];
					$chat_id = (String)$chatList['_id'];
					$chatdetails = Chat::where(['chat_id'=>$chat_id])->orderBy('_id','desc')->first();
					$lastMessage = $chatdetails['message'];
					$msgTime = (String)$chatdetails['created_at'];
					$other_ids = explode(',',$chatList['other_user_id']);
					$chatusers = array(
						'chat_id'=>$chat_id,
						'last_message'=>$lastMessage?$lastMessage:'',
						'msg_time'=>$msgTime?$msgTime:'',
						'chatType'=> "group",
						'group_name'=>$chatList['group_name']
					);
					$users = array();
					foreach($other_ids as $other_id){
						$user_exist = User::where(['_id'=>$other_id])->first();
						if($user_exist){
							$profile_image = "";
							if(strpos($user_exist['profile_image'], "http://") !== false){
								$profile_image = $user_exist['profile_image'];
							}
							else if(strpos($user_exist['profile_image'], "https://") !== false){
								$profile_image = $user_exist['profile_image'];
							}
							else{
								$profile_image = url('public/images',$user_exist['profile_image']);
							}

							$chatuser = array(
								'user_id'=>(String)$user_exist['_id'],
								'first_name'=>$user_exist['first_name'],
								'last_name'=>$user_exist['last_name'],
								'profile_image'=>$profile_image,
							);
							array_push($users, $chatuser);
							$chatusers['users'] = $users;
						}
					}
					$chatListarr[] = $chatusers;
				}
			}

			$chatList2 = ChatList::where('other_user_id','like','%'.$saveArray['user_id'].'%')->where(['chat_type'=>'group'])->get();
			if($chatList2){
				foreach($chatList2 as $chatList){
					$chatusers = [];
					$chat_id = (String)$chatList['_id'];
					$chatdetails = Chat::where(['chat_id'=>$chat_id])->orderBy('_id','desc')->first();
					$lastMessage = $chatdetails['message'];
					$msgTime = (String)$chatdetails['created_at'];
					$other_ids = explode(',',$chatList['other_user_id']);
					$chatusers = array(
						'chat_id'=>$chat_id,
						'last_message'=>$lastMessage?$lastMessage:'',
						'msg_time'=>$msgTime?$msgTime:'',
						'chatType'=> "group",
						'group_name'=>$chatList['group_name']
					);
					$users = array();
					foreach($other_ids as $other_id){
						if($other_id != $saveArray['user_id']){
							$user_exist = User::where(['_id'=>$other_id])->first();
							if($user_exist){
								$profile_image = "";
								if(strpos($user_exist['profile_image'], "http://") !== false){
									$profile_image = $user_exist['profile_image'];
								}
								else if(strpos($user_exist['profile_image'], "https://") !== false){
									$profile_image = $user_exist['profile_image'];
								}
								else{
									$profile_image = url('public/images',$user_exist['profile_image']);
								}

								$chatuser = array(
									'user_id'=>(String)$user_exist['_id'],
									'first_name'=>$user_exist['first_name'],
									'last_name'=>$user_exist['last_name'],
									'profile_image'=>$profile_image,
								);
								array_push($users, $chatuser);
								$chatusers['users'] = $users;
							}
							$user_exist = User::where(['_id'=>$chatList['user_id']])->first();
							if($user_exist){
								$profile_image = "";
								if(strpos($user_exist['profile_image'], "http://") !== false){
									$profile_image = $user_exist['profile_image'];
								}
								else if(strpos($user_exist['profile_image'], "https://") !== false){
									$profile_image = $user_exist['profile_image'];
								}
								else{
									$profile_image = url('public/images',$user_exist['profile_image']);
								}

								$chatuser = array(
									'user_id'=>(String)$user_exist['_id'],
									'first_name'=>$user_exist['first_name'],
									'last_name'=>$user_exist['last_name'],
									'profile_image'=>$profile_image,
								);
								array_push($users, $chatuser);
								$chatusers['users'] = $users;
							}
						}
					}
					$chatListarr[] = $chatusers;
				}
			}

			if(count($chatListarr)){
				//sorting array by latest messages
				
				usort($chatListarr, array($this, "sortFunction"));
				return response()->json([ 
					'status' => true,
					'message' => 'Success',
					'chat_list' => $chatListarr
				], $this->successCode);
			}
			else{
				return response()->json([ 
					'status' => false,
					'message' => 'No list found'
				], $this->successCode);
			}
		}
	}

	public function sortFunction( $a, $b ) {
	    return strtotime($b["msg_time"]) - strtotime($a["msg_time"]);
	}


	public function saveLastMessage(Request $request){
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'chat_id' => 'required',	
			'user_id' => 'required',	
			'other_user_id' => 'required',
			'message' => 'required',
			'type' => 'required',
			'chat_type' => 'required',
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			$datetime = date('Y-m-d H:i:s');
			$insertData = [
				'chat_id'=>$saveArray['chat_id'],
				'user_id'=>$saveArray['user_id'],
				'other_user_id'=>$saveArray['other_user_id'],
				'message'=>$saveArray['message'],
				'type'=>$saveArray['type'],
				'created_at'=>$datetime
			];
			$insert_id = Chat::insertGetId($insertData);

			$userdetails = User::where(['_id'=>$saveArray['user_id']])->first();

			$other_ids = array();

			if($saveArray['chat_type'] == "normal"){
				$other_ids = array($saveArray['other_user_id']);
			}
			else if($saveArray['chat_type'] == "group"){
				$other_ids = explode(',', $saveArray['other_user_id']);
			}

			foreach($other_ids as $other_id){
				$otherUserdetails = User::where(['_id'=>$other_id])->first();
				$device_type = $otherUserdetails->device_type;
				$device_token = [$otherUserdetails->device_token];

				$user_image = "";
				if(strpos($userdetails['profile_image'], "http://") !== false){
					$user_image = $userdetails['profile_image'];
				}
				else if(strpos($userdetails['profile_image'], "https://") !== false){
					$user_image = $userdetails['profile_image'];
				}
				else{
					$user_image = url('public/images',$userdetails['profile_image']);
				}

				$message =[
					'message' => $saveArray['message'],
					'user_id' => $saveArray['user_id'],
					'user_name' => $userdetails['first_name']." ".$userdetails['last_name'],
					'user_image' => $user_image,
					'receiver_id'=>$other_id,
					'chat_id' => $saveArray['chat_id'],
					'noti_type' => 'message',
					'datetime' => $datetime
				];

				if($otherUserdetails->device_token != ""){
					if($device_type == "A"){
						ApiController::android_send_notification($device_token,$message);
					}

					$noti_data = [
						'noti_type'=>'message',
						'notified_id'=>$other_id,
						'message'=>$saveArray['message'],
						'msg_json'=>json_encode($message),
						'created_at'=>$datetime
					];
					$notify = Notification::insertGetId($noti_data);

					$noti_read_data = [
						'user_id'=>$other_id,
						'noti_id'=>(String)$notify,
						'read_status'=>'0',
						'created_at'=>$datetime
					];
					$notify = readNotify::insert($noti_read_data);
				}
			}

			return response()->json([ 
				'status' => true,
				'message' => 'Success'
			], $this->successCode);
		}
	}

	public function search(Request $request){
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			//'keyword' => 'required',
			'type1' => 'required',
			'type2' => 'required',
			'filter_value' => 'required',
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			$details = "";
			if($saveArray['keyword'] == ""){
				$details = Workout::where(['page_for'=>$saveArray['type1']])->where(['type'=>$saveArray['type2']])->get();
			}
			else{
				$details = Workout::where('title','like','%'.$saveArray['keyword'].'%')->where(['page_for'=>$saveArray['type1']])->where(['type'=>$saveArray['type2']])->get();
			}
			
			$main_arr = [];
			if($details){
				foreach($details as $detail){

					$rating = RateWorkout::where(['workout_id'=>$detail['_id']])->avg('rating');
								
					$trainerDetails = Trainer::where(['_id'=>$detail['user_id']])->first();
								
					$image = url('public/images',$detail['image']);

					$helper = new Helpers();
								
					$totalComment = Comment::where(['post_id'=> $detail['_id']])->count();
						
					$arr = array(
						'workout_id'=>$detail['_id'],
						'user_id'=>$detail['user_id'],
						'by'=>ucwords($trainerDetails->name),
						'title'=>ucwords($detail['title']),
						'description'=>$detail['description'],
						'image'=>$image,
						'exercises'=>$detail['exercises'],
						'time'=>$detail['time'],
						'created_at'=>(String)$detail['created_at'],
						'rating'=>$rating?$rating:0,
						'ago'=>$helper->time_elapsed_string($detail['created_at']),
						'totalComment'=>$totalComment,
					);

					$main_arr[] = $arr;
				}

				if($saveArray['filter_value'] == "htl"){
					$main_arr = ApiController::msort($main_arr, array('rating'), 'SORT_NUMERIC', 'DESC');
				}
				else if($saveArray['filter_value'] == "lth"){
					$main_arr = ApiController::msort($main_arr, array('rating'), 'SORT_NUMERIC', 'ASC');
				}
				else{
					return response()->json(['message'=>"Invalid filter value",'status'=>false], $this->badRequestCode);
				}

				return response()->json([ 
					'status' => true,
					'message' => 'Success',
					'data' => $main_arr
				], $this->successCode);
			}
			else{
				return response()->json([ 
					'status' => false,
					'message' => 'No list found'
				], $this->successCode);
			}
		}
	}

	public function searchDiet(Request $request){
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'filter_value' => 'required',	
		]);
		if($validator->fails()) {
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			$details = "";
			if($saveArray['keyword'] == ""){
				$details = Diet::get();
			}
			else{
				$details = Diet::where('name','like','%'.$saveArray['keyword'].'%')->get();
			}
			$main_arr = [];
			if($details){
				foreach($details as $diet){
					
						$Workout = Workout::where(['_id'=> $diet['workout_id']])->first();
					   $workout_for='';
					   if($Workout)
					   {
						   $workout_for = $Workout['page_for'];
					   }
					$helper = new Helpers();
					$arr=array(
						'diet_id'=>$diet['_id'],
						'user_id'=>$diet['user_id'],
						'name'=>$diet['name'],
						'by_user'=>$diet['name'],
						'description'=>$diet['description'],
						'image'=>url('public/images/diet',$diet['image']),
						'period'=>$diet['period'],
						'amount'=>$diet['amount'].' Cal/Day',
						'price'=>$diet['price'],
						'rating'=>$diet['rating'],
						'workout_id'=>$diet['workout_id'],
						'workout_for'=>$workout_for,
						'carb'=>$diet['carb'],
						'fat'=>$diet['fat'],
						'protein'=>$diet['protein'],
						'sugar'=>$diet['sugar'],
						'created_at'=>$helper->time_elapsed_string($diet['created_at']),
					);
					$main_arr[] = $arr;
				}

				if($saveArray['filter_value'] == "htl"){
					$main_arr = ApiController::msort($main_arr, array('rating'), 'SORT_NUMERIC', 'DESC');
				}
				
				else if($saveArray['filter_value'] == "lth"){
					$main_arr = ApiController::msort($main_arr, array('rating'), 'SORT_NUMERIC', 'ASC');
				}
				
				else if($saveArray['filter_value'] == "hc"){
					$main_arr = ApiController::msort($main_arr, array('amount'), 'SORT_NUMERIC', 'DESC');
				}
				
				else if($saveArray['filter_value'] == "lc"){
					$main_arr = ApiController::msort($main_arr, array('amount'), 'SORT_NUMERIC', 'ASC');
				}
				else if($saveArray['filter_value'] == "hca"){
					$main_arr = ApiController::msort($main_arr, array('carb'), 'SORT_NUMERIC', 'DESC');
				}
				
				else if($saveArray['filter_value'] == "lca"){
					$main_arr = ApiController::msort($main_arr, array('carb'), 'SORT_NUMERIC', 'ASC');
				}
				else if($saveArray['filter_value'] == "hf"){
					$main_arr = ApiController::msort($main_arr, array('fat'), 'SORT_NUMERIC', 'DESC');
				}
				
				else if($saveArray['filter_value'] == "lf"){
					$main_arr = ApiController::msort($main_arr, array('fat'), 'SORT_NUMERIC', 'ASC');
				}
				
				else if($saveArray['filter_value'] == "hs"){
					$main_arr = ApiController::msort($main_arr, array('sugar'), 'SORT_NUMERIC', 'DESC');
				}
				else if($saveArray['filter_value'] == "ls"){
					$main_arr = ApiController::msort($main_arr, array('sugar'), 'SORT_NUMERIC', 'ASC');
				}
				
				else if($saveArray['filter_value'] == "hp"){
					$main_arr = ApiController::msort($main_arr, array('protein'), 'SORT_NUMERIC', 'DESC');
				}
				else if($saveArray['filter_value'] == "lp"){
					$main_arr = ApiController::msort($main_arr, array('protein'), 'SORT_NUMERIC', 'ASC');
				}
				
				else{
					return response()->json(['message'=>"Invalid filter value",'status'=>false], $this->badRequestCode);
				}
					
				return response()->json([ 
					'status' => true,
					'message' => 'Success',
					'data'=>$main_arr,
				], $this->successCode);
			}
			else{
				return response()->json([ 
					'status' => false,
					'message' => 'Not found',
				], $this->successCode);
			}
		}
	}

	function msort($array, $key, $sort_flags, $sort_order) {
	    if (is_array($array) && count($array) > 0) {
	        if (!empty($key)) {
	            $mapping = array();
	            foreach ($array as $k => $v) {
	                $sort_key = '';
	                if (!is_array($key)) {
	                    $sort_key = $v[$key];
	                } else {
	                    // @TODO This should be fixed, now it will be sorted as string
	                    foreach ($key as $key_key) {
	                        $sort_key .= $v[$key_key];
	                    }
	                    $sort_flags = SORT_STRING;
	                }
	                $mapping[$k] = $sort_key;
	            }
	            if($sort_order == 'DESC'){
	            	arsort($mapping, $sort_flags);
	            }
	            else if($sort_order == 'ASC'){
	            	asort($mapping, $sort_flags);
	            }
	            
	            $sorted = array();
	            foreach ($mapping as $k => $v) {
	                $sorted[] = $array[$k];
	            }
	            return $sorted;
	        }
	    }
	    return $array;
	}

	public function notification_list(Request $request)
	{
		$saveArray = $request->all();
		$userExist = User::where(['_id'=> $saveArray['user_id']])->first() ;
		if($userExist){
			$notify = Notification::where(['notified_id'=> $saveArray['user_id']])
											->orderBy('_id','DESC')
											->get();
			$arr_nots=array();
			foreach($notify as $noti){
				$read_status = "";
				$noti_id = (String)$noti->_id;
				$cr = readNotify::where(['user_id'=>$saveArray['user_id']])->where(['noti_id'=>$noti_id])->select('read_status')->first();
				if($cr){
					if($cr['read_status'] == "0"){
						$read_status = "N";
					}
					if($cr['read_status'] == "1"){
						$read_status = "Y";
					}
				}
				$msg_json = json_decode($noti->msg_json, true);
				$arr_nots[]=array(
						'noti_id'=>$noti_id,
						'user_id'=>$msg_json['user_id'],
						'user_name'=>$msg_json['user_name'],
						'user_image' => $msg_json['user_image'],
						'message'=>$noti->message,
						'msg_arr'=>json_decode($noti->msg_json, true),
						'noti_type'=>$noti->noti_type,
						'read_status'=>$read_status
				);
				
			}	
			return response()->json(['status'=>true,'message'=>'Success','arr_nots'=>$arr_nots], $this->successCode);
		}
		else{
			return response()->json(['message'=>'User does not exist','status'=>false], $this->successCode); 
		}
	}

	public function saveStateWY(Request $request){
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
			'exercise_id' => 'required',	
			'ratting' => 'required',	
			'type' => 'required'	
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
		
			$insertData = [
				'user_id'=>$saveArray['user_id'],
				'exercise_id'=>$saveArray['exercise_id'],
				'ratting'=>$saveArray['ratting'],
				'type'=>$saveArray['type'],
				'created_at'=>date('Y-m-d H:i:s')
			];
			$insert_id = UserStat::insertGetId($insertData);

			return response()->json([ 
				'status' => true,
				'message' => 'Success'
			], $this->successCode);
			
		}
	}

	public function addToMyRunning(Request $request){

		$saveArray = $request->all();
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
			'running_id' => 'required'	
		]);
		if($validator->fails()) {
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			$runnings = MyRunning::where(['user_id'=> $saveArray['user_id'],'running_id'=> $saveArray['running_id']])->first();
			if($runnings){
				return response()->json([ 
					'status' => false,
					'message' => 'Already Added',
				], $this->successCode);
			}
			else{
				$saveArray['running_status']=1;
				$insertData = [
					'user_id'=>$saveArray['user_id'],
					'running_id'=>$saveArray['running_id'],
					'running_status'=>$saveArray['running_status'],
					'created_at'=>date('Y-m-d H:i:s')
				];
				$lastInsertId = MyRunning::insertGetId($insertData);
			}
			return response()->json([ 
				'status' => true,
				'message' => 'Success',
			], $this->successCode);
		}
	}

	public function addToMyWeightLifting(Request $request){
		
		$saveArray = $request->all();
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
			'weightlifting_id' => 'required'	
		]);
		if($validator->fails()) {
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			$runnings = MyWeightLifting::where(['user_id'=> $saveArray['user_id'],'weightlifting_id'=> $saveArray['weightlifting_id']])->first();
			if($runnings){
				return response()->json([ 
					'status' => false,
					'message' => 'Already Added',
				], $this->successCode);
			}
			else{
				$saveArray['weightlifting_status']=1;
				$insertData = [
					'user_id'=>$saveArray['user_id'],
					'weightlifting_id'=>$saveArray['weightlifting_id'],
					'weightlifting_status'=>$saveArray['weightlifting_status'],
					'created_at'=>date('Y-m-d H:i:s')
				];
				$lastInsertId = MyWeightLifting::insertGetId($insertData);
			}
			return response()->json([ 
				'status' => true,
				'message' => 'Success',
			], $this->successCode);
		}
	}

	public function addToMyYoga(Request $request){
		
		$saveArray = $request->all();
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
			'yoga_id' => 'required'	
		]);
		if($validator->fails()) {
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			$runnings = MyYoga::where(['user_id'=> $saveArray['user_id'],'yoga_id'=> $saveArray['yoga_id']])->first();
			if($runnings){
				return response()->json([ 
					'status' => false,
					'message' => 'Alredy Added',
				], $this->successCode);
			}
			else{
				$saveArray['yoga_status']=1;
				$insertData = [
					'user_id'=>$saveArray['user_id'],
					'yoga_id'=>$saveArray['yoga_id'],
					'yoga_status'=>$saveArray['yoga_status'],
					'created_at'=>date('Y-m-d H:i:s')
				];
				$lastInsertId = MyYoga::insertGetId($insertData);
			}
			return response()->json([ 
				'status' => true,
				'message' => 'Success',
			], $this->successCode);
		}
	}

	public function deleteChat(Request $request){
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',	
			'message_id' => 'required'
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			$chat_exist = ChatList::where(['_id'=>$saveArray['message_id']])->first();
			if($chat_exist){
				$delete = ChatList::where(['_id'=>$saveArray['message_id']])->delete();
				$delete = Chat::where(['chat_id'=>$saveArray['message_id']])->delete();
				if($delete){
					return response()->json([ 
						'status' => true,
						'message' => 'Success'
					], $this->successCode);
				}
			}
			else{
				return response()->json([ 
					'status' => false,
					'message' => 'chat not exist'
				], $this->successCode);
			}
		}
	}

	public function trainerRequest(Request $request){
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			
				
				$findTrainer = TrainerRequest::where(['user_id'=>$saveArray['user_id']])->first();
				$updateData= [
							'gym_partner_status'=>'on'
							];
									
             $setting = Setting::where('user_id','=', $saveArray['user_id'])->update($updateData);
			 
				if(empty($findTrainer))
				{
			$insertData = TrainerRequest::insertGetId([
				'user_id'=>$saveArray['user_id'],
				'status'=>'0',
				'created_at'=>date('Y-m-d H:i:s')
			]);
				}
				$insertData = true;
			

			if($insertData){
				return response()->json([ 
					'status' => true,
					'message' => 'Success'
				], $this->successCode);
			}
			else{
				return response()->json([ 
					'status' => false,
					'message' => 'Request sending failed'
				], $this->successCode);
			}
		}
	}
	
	public function deleteAccount(Request $request)
	{
		
		$saveArray = $request->all();
		
		$validator = Validator::make($request->all(), [ 
			'user_id' => 'required',
		]);
		if($validator->fails()) {
			
			return response()->json(['message'=>$validator->errors()->first(),'status'=>false], $this->badRequestCode);            
		}
		else{
			
			$userDelete = User::where('_id', '=', $saveArray['user_id'])->delete();
			if($userDelete){
				
				$trainerRequestDelete = TrainerRequest::where('user_id', '=', $saveArray['user_id'])->delete();
				$postDelete = Post::where('user_id', '=', $saveArray['user_id'])->delete();
				$deleteSetting = Setting::where('user_id', '=', $saveArray['user_id'])->delete();
				return response()->json([ 
					'status' => true,
					'message' => 'Success'
				], $this->successCode);
			}
			else{
				return response()->json([ 
					'status' => false,
					
				], $this->successCode);
			}
		}
		
		
		
	}
	

	public function android_send_notification($registatoin_ids,$message) {
		
		$device_tokens= array();
	   foreach($registatoin_ids as $registatoin_id)
		{
		$user = User::where('device_token', '=', $registatoin_id)->first();
		
		$setting = Setting::where('user_id', '=', $user->_id)->first();
	
		$push = $setting['push_notification'];
		
		if($push ==1)
		{
			//$device_tokens.push($registatoin_id);
			array_push($device_tokens, $registatoin_id);
		}
		
		}
		
        //$url = 'https://android.googleapis.com/fcm/send';
        $url = 'https://fcm.googleapis.com/fcm/send';
        $fields = array(
            'registration_ids' => $device_tokens,
            'data' => $message,
        );
		
		 if(!defined('GOOGLE_API_KEY')){
			$GOOGLE_API_KEY = 'AAAAVnHQ1es:APA91bGKKCGRBFXXn5aS48MUXPsOEiLG79ey8DVhrbAcIR3oYNO2Xt3A0eOyhenGIw7IitLjAt9t5MYwKZmmC0W5i-rCiTvyWWZZFzdlX9Fi8fE-wO7jiAZTAJpAiGsTOwAhsBFThBre';
		}
        $headers = array(
            'Authorization: key='.$GOOGLE_API_KEY,
            'Content-Type: application/json'
        );
		//pr($headers);die;
        // Open connection
        $ch = curl_init();

        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        $result = curl_exec($ch);
        if ($result === FALSE){
            die('Curl failed: ' . curl_error($ch));
        }

        curl_close($ch);
		return $result;
    }
	
	
	public function addAdmin(Request $request)
	{
		
		$user = new Admin;
		$user->email = 'vikas.auspicioussoft@gmail.com';
		$user->password = Hash::make(12345);
		$user->save();
		return $user;		
		//echo "admin created";		
	}
	
	
	public function addTeams(Request $request)
	{
		
		$teams = new Teams;
		
		$teams->name= $request->name;
	    $teams->intreset= $request->intreset;

		$teams->skill= $request->skill;

		$teams->type= $request->type;
		$teams->save();
		
		return response()->json([ 
					'status' => true,
					'data' =>$teams 
				]);
			
		
	}



#*************************************************************************************#	
	
	
}
