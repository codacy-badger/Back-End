<?php

namespace App\Http\Controllers\Api\File;

use App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Validator;

use App\Http\Controllers\Api\ApiController as ApiController;

use App\Repositories\FileRepository;
use App\Http\Resources\File\File as FileResource;
use App\Http\Resources\File\FileCollection;
use App\Models\{File, User};

class FileController extends ApiController
{
    /**
     * Set User Repository.
     * Constructor
     */

    protected $withs = [];

    protected $fileRepository;

    public function __construct(Request $request, FileRepository $fileRepository)
    {
        $this->fileRepository = $fileRepository;

        if ($request->has('include')) {
            $this->withs = explode(',', $request->include);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function index(Request $request, File $file)
    {
        $this->authorize('adminManage', $file);
        return new FileCollection(File::with($this->withs)->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file_type_id' => 'required|max:255',
            'user_id' => 'required|max:255',
            'file_input' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);
        if($validator->fails()){
            return $this->apiResponse403('Validation Error', $validator->errors());       
        }
        $user_id = $request['user_id'];
        $file_type_id = $request['file_type_id'];
        $user = User::where('id', $user_id)->first();
        $image = $request->file('file_input');
        $name = $file_type_id . '.' . $user['lastName'] . $user['firstName'] . '.' . mt_rand(100000, 999999) . '.'  . $image->getClientOriginalExtension();
        $destinationPath = "storage/user_files_" . $user_id;
        $request['url'] = encrypt($_ENV['APP_URL'] . "/" . $destinationPath . "/" . $name);
        $store = $this->fileRepository->store($request->all());
        $image->move($destinationPath, $name);
        if($request->has('name')) {
        $this->fileRepository->saveOtherFile($store,$request->only('name'));
        }
        return (new FileResource($store))
        ->response()
        ->setStatusCode(201);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function show(File $file)
    {
        $this->authorize('manage', $file);
        return new FileResource(File::with($this->withs)->find($file->id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request, File $file)
    {
        $this->authorize('manage', $file);
        $id = $file->id;
        $update = $this->fileRepository->update($id, $request->all());
        if($request->has('name')) {
        $this->fileRepository->updateOtherFile($id,$request->only('name'));
        }
        return new FileResource(File::find($id));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy(File $file)
    {
        $this->authorize('manage', $file);
        $id = $file->id;
        $file = File::find($id);
        $url = explode($_ENV['APP_URL'] . "/storage/",decrypt($file['url']));
        Storage::disk('public')->delete($url[1]);
        $this->fileRepository->destroy($id);
        return $this->apiResponse204();
    }
    
}
