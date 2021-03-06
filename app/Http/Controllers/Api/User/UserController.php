<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Validator;

use App\Http\Controllers\Api\ApiController;

use App\Repositories\UserRepository;
use App\Http\Resources\User\User as UserResource;
use App\Http\Resources\User\UserCollection;
use App\Models\User;

class UserController extends ApiController
{
    /**
     * Set User Repository.
     * Constructor
     */

    protected $withs = [];

    protected $userRepository;

    public function __construct(Request $request, UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;

        if ($request->has('include')) {
            $this->withs = explode(',', $request->include);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request, User $user)
    {
        $this->authorize('adminManage', $user);
        return new UserCollection(User::with($this->withs)->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request, User $user)
    {
        $this->authorize('adminManage', $user);
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|max:255',
        ]);
        if($validator->fails()){
            return $this->apiResponse403('Validation Error', $validator->errors());       
        }
        $this->setAdmin($request,$user);
        $store = $this->userRepository->store($request->all());
        $id = $store['id'];
        $directory = "/user_files_" . $id;
        Storage::disk('public')->makeDirectory($directory);
        return (new UserResource($store))
        ->response()
        ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function show(Request $request, User $user)
    {
        $this->authorize('manage', $user);
        return new UserResource(User::with($this->withs)->find($user->id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request, User $user)
    {
        $this->authorize('manage', $user);
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|max:255',
        ]);
        if($validator->fails()){
            return $this->apiResponse403('Validation Error', $validator->errors());       
        }
        $this->setAdmin($request, $user);
        $this->userRepository->update($user->id, $request->all());
        return new UserResource(User::find($user->id));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy(User $user)
    {
        $this->authorize('manage', $user);
        $id = 
        $user = User::find($user->id);
        Storage::deleteDirectory('user_files_' . $user->id);
        $this->userRepository->destroy($user->id);
        return $this->apiResponse204();
    }

    /**
     * Set user as Admin.
     */

    private function setAdmin(Request $request, User $user)
    {
        $this->authorize('adminManage', $user);
        if(!$request->has('admin'))
        {
            $request->merge(['admin' => 0]);
        }       
    }
    
}
