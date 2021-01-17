<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DataTables;
use App\Models\User;
use App\Models\Study;
use Response;
use DB;
use ZipArchive;
use Illuminate\Support\Facades\Storage;
use File;
//use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\Filesystem;
use Carbon;
class AppUserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private $user_obj;
    public function __construct(User $user){
        $this->user_obj=$user;
    }

    public function index()
    {
       
            $title="User List";
            $user=User::paginate(7);
            foreach($user as $users){
                $study=Study::where('_id','=',$users->study)->first();
                $users_info[]=[
                'studyId'=>$study->studyId,
                'study_id'=>$users->study,
                'userId'=>$users->userId,
                'pin'=>$users->pin,

                'userType'=>$users->userType,
                'options'=>$users->options,
                '_id'=>$users->_id,
                ];
            }
            // echo "<pre>";
            // print_r($users_info);die();
            return view('admin.users.index',compact('title','users_info','user'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $study=Study::get();  
        $Id=$this->generateUniqueId(5);
        $n=$Id;
        return view('admin.users.create', compact('study','n'));
    }

    function generateUniqueId($length)
        {
            $number = '';

            do {
                for ($i=$length; $i--; $i>0) {
                    $number .=mt_rand(0,9);
                }
            } while ( !empty(DB::table('users')->where('userId', 'RR'.$number)->first(['userId'])) );

            return 'RR'.$number;
        }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
         //print_r($_POST);die();
         $data=$request->all();
         $user_info=$this->user_obj->createUser($data);
        if ($user_info) {
                return redirect('admin/users')->with("su_status", "User has been added successfully");                  
                } 
        else {
                return Redirect::back()->with('er_status', 'No user  added!');
            }
            
    }

    public function change_pin(Request $request){
        //echo 232;
                 $data=$request->pin;
                 $user_id=$request->user_id;
                 $user=User::where('_id','=', $user_id)->update(['pin'=>$data]);
              return response()->json(['status'=>true,'data'=>$user,'res'=>$request->all(), 'info'=>$data, 'message'=>' successfully'], 200);
    }

    public function single_user(Request $request){

        $user_id=$request->user_id;                
         $user=User::where('_id','=', $user_id)->first();
         $study=Study::where('_id','=',$user->study)->first();
         return response()->json(['status'=>true,'data'=>$user, 'study'=>$study, 'message'=>' successfully'], 200);

    }

    public function search_users(Request $request){

        $user=$request->userId;
        $users= User::where('userId','LIKE','%'.$user.'%')->get();
         foreach($users as $users){
                $study=Study::where('_id','=',$users->study)->first();
                $users_info[]=[
                'studyId'=>$study->studyId,
                'study_id'=>$users->study,
                'userId'=>$users->userId,
                'userType'=>$users->userType,
                'options'=>$users->options,
                '_id'=>$users->_id,
                ];
            }

         return response()->json(['status'=>true,'data'=>$users_info, 'message'=>' successfully'], 200);

    }

    public function download_csv_single_user(Request $request,$id){

            $table = User::where('_id','=',$id)->first();
            $study=Study::where('_id','=',$table->study)->first();
            $filename = "user_info.csv";
            $handle = fopen($filename, 'w+');
            fputcsv($handle, array( 'userId','Option', 'study', 'pin'));
            //foreach($table as $row) {
                //fputcsv($handle, array($row['userType'], $row['userId'], $row['study'], $row['pin']));
            //}
                fputcsv($handle, array( $table['userId'],$table['options'], $study['studyId'], $table['pin']));
            fclose($handle);
            $headers = array(
                'Content-Type' => 'text/csv',
            );

            return Response::download($filename, 'user_info.csv', $headers);
    }


    public function download_csv_all_user(Request $request){

            $table = User::get();
            $table_count = User::get()->count();
			 //$articles = article::where('volume_id',$volume_id)->get();
			 
			 // $zip = new ZipArchive;
   
				// $fileName = 'myNewFile.zip';
		   
				// if ($zip->open(public_path($fileName), ZipArchive::CREATE) === TRUE)
				// {
					// $files = File::files(public_path('myFiles'));
		   
					// foreach ($files as $key => $value) {
						// $relativeNameInZipFile = basename($value);
						// $zip->addFile($value, $relativeNameInZipFile);
					// }
					 
					// $zip->close();
				// }
			
				// return response()->download(public_path($fileName));
				
				// Get all files in a directory
				
				$dir=public_path()."/myFiles/";				
				$file = new Filesystem;
                $file->cleanDirectory($dir);
				$count_row=1;
				$file_path=public_path()."/myFiles/";
		        foreach($table as $row) {
				$filename = public_path()."/myFiles/user_inffoo".$count_row.".csv";
				$handle = fopen($filename, 'w+');
				fputcsv($handle, array( 'userId','Option', 'study', 'pin'));
					$study=Study::where('_id','=',$row->study)->first();
					fputcsv($handle, array( $row['userId'],$row['options'], $study['studyId'], $row['pin']));
					
				fclose($handle);
					$headers = array(
						'Content-Type' => 'text/csv',
					);
					$count_row++;
				}
				
				$zip = new ZipArchive;
				$current = time(); 
				$fileName ="allUsers".$current.".zip";	
				//$fileName = 'myNewFile.zip';		   
				if ($zip->open(public_path($fileName), ZipArchive::CREATE) === TRUE)
				{
					$files = File::files(public_path('myFiles'));
		   
					foreach ($files as $key => $value) {
						$relativeNameInZipFile = basename($value);
						$zip->addFile($value, $relativeNameInZipFile);
					}
					 
					$zip->close();
				}
				return response()->download(public_path($fileName));
				
			  //return Response::download($filename, 'user_infoo.csv', $headers);

			/*for($i=1;$i<=$table_count;$i++)
			{
            $filename = public_path()."/myFiles/user_inffoo".$i.".csv";
            $handle = fopen($filename, 'w+');
            fputcsv($handle, array( 'userId','Option', 'study', 'pin'));
            foreach($table as $row) {

                $study=Study::where('_id','=',$row->study)->first();
                fputcsv($handle, array( $row['userId'],$row['options'], $study['studyId'], $row['pin']));
            }
            fclose($handle);
            $headers = array(
                'Content-Type' => 'text/csv',
            );
			}*/

           // return Response::download($filename, 'user_infoo.csv', $headers);
    }
	
	public function multiple_download_user(Request $request){
		
		    $data=$request->all();
		    $id=$data['options']; 					
			$table = User::whereIn('userId',$id)->get();
			return response()->json(['status'=>true,'data'=>$table, 'message'=>' successfully'], 200);	
	}
	
	
	public function multiple_download_user1(Request $request,$id){		
		    $data=$request->all();
			$d=explode(',',$id);
			$table = User::whereIn('userId',$d)->get();
				$dir=public_path()."/myFiles/";				
				$file = new Filesystem;
                $file->cleanDirectory($dir);
				$count_row=1;
				$file_path=public_path()."/myFiles/";
		        foreach($table as $row) {
				$filename = public_path()."/myFiles/user_inffoo".$count_row.".csv";
				$handle = fopen($filename, 'w+');
				fputcsv($handle, array( 'userId','Option', 'study', 'pin'));
					$study=Study::where('_id','=',$row->study)->first();
					fputcsv($handle, array( $row['userId'],$row['options'], $study['studyId'], $row['pin']));
					
				fclose($handle);
					$headers = array(
						'Content-Type' => 'text/csv',
					);
					$count_row++;
				}
				
				$zip = new ZipArchive;
				$current = time(); 
				$fileName ="myCheckedUsers".$current.".zip";	   
				if ($zip->open(public_path($fileName), ZipArchive::CREATE) === TRUE)
				{
					$files = File::files(public_path('myFiles'));
		   
					foreach ($files as $key => $value) {
						$relativeNameInZipFile = basename($value);
						$zip->addFile($value, $relativeNameInZipFile);
					}
					 
					$zip->close();
				}
				return response()->download(public_path($fileName));
			
	}




    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    public function usersData()
    {
     return DataTables::of(User::select('userId','userType','study','options')->get()) ->addIndexColumn()
     ->addColumn('report', function($row){

            $btn = '<a href="javascript:void(0)" class="edit btn btn-info btn-sm">Download</a>';
             return $btn;
     })
     ->addColumn('info', function($row){

        $btn = '<a href="javascript:void(0)" class="edit btn btn-primary btn-sm">Info</a>';
         return $btn;
 })
 ->addColumn('changePin', function($row){

    $btn = '<a href="javascript:void(0)" class="edit btn btn-success btn-sm">change Pin</a>';
     return $btn;
})
     ->rawColumns(['report','info','changePin'])
     
     ->make(true);
    }
}
