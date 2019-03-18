<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\FileType;
use App\Models\File;
use App\Models\User;

class TypeTest extends TestCase
{
    /**
     * Test if can show a resource.
     *
     * @return void
     */

    public function test_can_show_type()
    {
        $type = FileType::all(['id'])->random();
        $this->get(route('type.show', $type->id))
        ->assertStatus(200)
        ->assertJson([ 'data' => ['id' => (string)$type->id]])
        ->assertJsonStructure([
            'data' => [ 
                'type', 'id', 
                'attributes' => [
                    'title'
                ], 
                'relationships' => [
                    'category' => [
                        'links' => [
                            'self', 'related'
                        ],
                        'data' => []
                    ],
                    'files' => [
                        'links' => [
                            'self', 'related'
                        ],
                        'data' => []
                    ]
                ],
                'links' => ['self']
            ]
        ]);
    } 

    /**
     * Test if can show a collection.
     *
     * @return void
     */

    public function test_can_list_category() 
    {
        $this->get(route('type.index'))
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [ 
                [
                    'type', 'id', 
                    'attributes' => [
                        'title'
                    ], 
                    'relationships' => [
                        'category' => [
                            'links' => [
                                'self', 'related'
                            ],
                            'data' => []
                        ],
                        'files' => [
                            'links' => [
                                'self', 'related'
                            ],
                            'data' => []
                        ]
                    ],
                    'links' => ['self']
                ]
            ],
            'links' => ['self']
        ]);
    }

    /**
     * Test if can update a resource.
     *
     * @return void
     */

    public function test_can_update_category() {
        $type = FileType::all(['id'])->random();
        $data = [
            'title' => 'Test Title',
        ];
        $this->put(route('type.update', $type->id), $data)
            ->assertStatus(200)
            ->assertJson([ 'data' => ['id' => (string)$type->id]])
            ->assertJsonStructure([
                'data' => [ 
                    'type', 'id', 
                    'attributes' => [
                        'title'
                    ], 
                    'relationships' => [
                        'category' => [
                            'links' => [
                                'self', 'related'
                            ],
                            'data' => []
                        ],
                        'files' => [
                            'links' => [
                                'self', 'related'
                            ],
                            'data' => []
                        ]
                    ],
                    'links' => ['self']
                ]
            ]);
    }

    /**
     * Test if can show a relationship resource.
     *
     * @return void
     */

    public function test_can_show_type_category()
    {
        $type = FileType::all(['id'])->random();
        $this->get(route('type.category', $type->id))
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [ 
                'type', 'id', 
                'attributes' => [
                    'title'
                ], 
                'relationships' => [
                    'type' => [
                        'links' => [
                            'self', 'related'
                        ],
                        'data' => []
                    ]
                ],
                'links' => ['self']
            ],
            'links' => ['self']
        ]);
    }

    /**
     * Test if can show a relationship resource.
     *
     * @return void
     */

    public function test_can_show_type_file()
    {
        $type = FileType::all(['id'])->random();
        $file = factory(File::class)->create();
        $file->file_type_id = $type->id;
        $file->save();
        $this->get(route('type.files', $type->id))
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [ 
                'type', 'id', 
                'attributes' => [
                    'title'
                ], 
                'relationships' => [
                    'category' => [
                        'links' => [
                            'self', 'related'
                        ],
                        'data' => []
                    ],
                    'files' => [
                        'links' => [
                            'self', 'related'
                        ],
                        'data' => []
                    ]
                ],
                'links' => ['self']
            ],
            'links' => ['self']
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Security Test
    |--------------------------------------------------------------------------
    */

    /**
     * adminManage Policy test.
     *
     * @return void
     */

    public function test_cant_update_type() {
        $user = factory(User::class)->create();
        $user->admin = 0;
        $user->save();
        $this->actingAs($user, 'api');
        $type = FileType::all(['id'])->random();
        $data = [
            'title' => 'Test Title',
        ];
        $this->put(route('type.update', $type->id), $data)
            ->assertStatus(403);
    }
}
