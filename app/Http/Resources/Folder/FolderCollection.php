<?php

namespace App\Http\Resources\Folder;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;
use App\Http\Resources\User\User as UserResource;
use App\Http\Resources\File\File as FileResource;

class FolderCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return[
            'data' => $this->collection,
            'links' => [
                'self' => url()->current(),
            ],
            'included' => $this->when(
                $request->has('include'),
                function () {
                    return $this->withIncluded();
                }
            )
        ];
    }

    /**
     * Retrieve the relationships that have been included.
     *
     * @return array
     */

    private function withIncluded(): array
    {
        $relationships = [];

        foreach ($this->collection as $resource) {

            $relations = $resource->getRelations();

            if (!empty($relations)) {
                foreach ($relations as $nameRelation => $relation) {

                    if ($relation->count()) {
                        $modelRelation = get_class($resource->{$nameRelation}()->getRelated());
                        if($nameRelation == "user") {
                            $relationships[$nameRelation][] = new UserResource($relation);
                        } 
                        elseif ($nameRelation == 'files') {
                            $relationships[$nameRelation][] = $relation->mapInto(FileResource::class);
                        }
                    }
                }
            }
        }
        return $relationships;
    }
}
