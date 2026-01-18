<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_published_posts()
    {
        $user = User::factory()->create();
        Post::factory()->count(3)->create(['published' => true]);
        Post::factory()->create(['published' => false]); // Unpublished post

        $response = $this->actingAs($user)->getJson('/api/posts');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         '*' => [
                             'id',
                             'title',
                             'content',
                             'excerpt',
                             'slug',
                             'published',
                             'published_at',
                             'created_at',
                             'updated_at',
                             'user' => [
                                 'id',
                                 'name',
                                 'email'
                             ]
                         ]
                     ]
                 ]);
    }

    public function test_show_returns_a_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['published' => true]);

        $response = $this->actingAs($user)->getJson("/api/posts/{$post->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         'id' => $post->id,
                         'title' => $post->title,
                         'published' => $post->published
                     ]
                 ]);
    }

    public function test_store_creates_a_new_post()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                         ->postJson('/api/posts', [
                             'title' => 'Test Post',
                             'content' => 'This is test content',
                             'published' => true
                         ]);

        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Post created successfully'
                 ]);

        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
            'content' => 'This is test content',
            'published' => true,
            'user_id' => $user->id
        ]);
    }
}
