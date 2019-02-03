<?php

namespace App\Http\Controllers\Api\Folder;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\User as UserResource;
use App\Http\Resources\File\FileCollection;

use App\Models\Folder;

class FolderRelationshipController extends Controller
{
    public function user(Folder $folder)
    {
        return new UserResource($folder->user);
    }

    public function files(Folder $folder)
    {
        return new FileCollection($folder->files);
    }
}