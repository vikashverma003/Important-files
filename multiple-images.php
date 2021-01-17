if(!empty($request->image)){
        $img=array();
        foreach($request->image as $file){
                $logoName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
               $file->move('admin/images/owner', $logoName);
               $url= URL::to('/');
               $img[]=  $url.'/admin/images/owner/'.$logoName;
           }
             foreach ($img as $value) {
                 DB::table('taskimage')->insert(['image' => $value, 'task_id' => $request->task_id]);
             }
        }

        if(!empty($request->document)){
        $document=array();
        foreach($request->document as $file){
                $logoName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
               $file->move('admin/images/owner', $logoName);
               $url= URL::to('/');
               $document[]=  $url.'/admin/images/owner/'.$logoName;
           }
             foreach ($document as $documentvalue) {
                 DB::table('taskattachment')->insert(['attachment' => $documentvalue, 'task_id' => $request->task_id]);
             }
        }
		