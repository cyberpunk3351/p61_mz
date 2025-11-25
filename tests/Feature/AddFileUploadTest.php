<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AddFileUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_add_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('add.index'));

        $response->assertOk();
    }

    public function test_user_can_upload_supported_file(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $file = UploadedFile::fake()->createWithContent('contacts.csv', <<<'CSV'
name,email,age
Alice,alice@example.com,30
Bob,bob@example.com,28
CSV);

        $response = $this->actingAs($user)->post(route('add.store'), [
            'file' => $file,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success', 'File uploaded successfully!');
        $response->assertSessionHas('csvPreview', function ($preview) {
            $this->assertSame(2, $preview['total_rows']);
            $this->assertSame('name', $preview['columns'][0]['key']);
            $this->assertSame('name', $preview['columns'][0]['label']);
            $this->assertSame('Alice', $preview['rows'][0]['name']);

            return true;
        });

        Storage::disk('public')->assertExists('uploads/'.$file->hashName());
    }
}
