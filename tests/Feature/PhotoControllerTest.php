<?php

namespace Tests\Feature;

use App\Models\ProcessedPhoto;
use App\Mail\SharePhotosMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotoControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test photo store endpoint.
     */
    public function test_can_upload_processed_photos(): void
    {
        Storage::fake('public');

        // Simple base64 image strings
        $rawImageBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
        $framedImageBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->postJson(route('photos.store'), [
            'raw_image' => $rawImageBase64,
            'framed_image' => $framedImageBase64,
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'uuid',
                     'share_url',
                     'raw_url',
                     'framed_url',
                 ]);

        $uuid = $response->json('uuid');

        // Assert record exists in database
        $this->assertDatabaseHas('processed_photos', [
            'uuid' => $uuid,
        ]);

        // Assert files exist in public storage
        Storage::disk('public')->assertExists("uploads/{$uuid}_raw.png");
        Storage::disk('public')->assertExists("uploads/{$uuid}_framed.png");
    }

    /**
     * Test sharing page rendering.
     */
    public function test_can_access_share_page(): void
    {
        Storage::fake('public');

        $photo = ProcessedPhoto::create([
            'uuid' => 'test-uuid-1234',
            'raw_path' => 'uploads/test-uuid-1234_raw.png',
            'framed_path' => 'uploads/test-uuid-1234_framed.png',
        ]);

        // Access valid page
        $response = $this->get(route('share.show', 'test-uuid-1234'));
        $response->assertStatus(200)
                 ->assertSee('test-uuid-1234_raw.png')
                 ->assertSee('test-uuid-1234_framed.png');

        // Access invalid UUID
        $invalidResponse = $this->get(route('share.show', 'non-existent-uuid'));
        $invalidResponse->assertStatus(404);
    }

    /**
     * Test email dispatch.
     */
    public function test_can_send_email(): void
    {
        Mail::fake();
        Storage::fake('public');

        $photo = ProcessedPhoto::create([
            'uuid' => 'test-uuid-1234',
            'raw_path' => 'uploads/test-uuid-1234_raw.png',
            'framed_path' => 'uploads/test-uuid-1234_framed.png',
        ]);

        $response = $this->postJson(route('share.mail', 'test-uuid-1234'), [
            'email' => 'user@example.com',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                 ]);

        // Assert email was sent
        Mail::assertSent(SharePhotosMail::class, function ($mail) use ($photo) {
            return $mail->photo->id === $photo->id &&
                   $mail->hasTo('user@example.com');
        });

        // Assert email column was updated in DB
        $this->assertDatabaseHas('processed_photos', [
            'uuid' => 'test-uuid-1234',
            'email' => 'user@example.com',
        ]);
    }

    /**
     * Test photo store endpoint in polaroid mode.
     */
    public function test_can_upload_processed_photos_in_polaroid_mode(): void
    {
        Storage::fake('public');

        $rawImageBase64 = 'data:application/zip;base64,UEsDBAoAAAAAACGP11UAAAAAAAAAAAAAAAAHABwAZm9sZGVyL1VUCQAD57P6Zuez+mZ1eApreFwFAxAHAAADAAAAAFBLAwQKAAAAAAAhj9dVAAAAAAAAAAAAAAAAAQAHAABhLnR4dFVUCQAD57P6Zuez+mZ1eApreFwFAxAHAAADAAAAAGEFBLBQDwUAAAAAAQABAEcAAABAAAAAAA==';
        $framedImageBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->postJson(route('photos.store'), [
            'raw_image' => $rawImageBase64,
            'framed_image' => $framedImageBase64,
            'mode' => 'polaroid',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'uuid',
                     'share_url',
                     'raw_url',
                     'framed_url',
                 ]);

        $uuid = $response->json('uuid');

        // Assert record exists in database
        $this->assertDatabaseHas('processed_photos', [
            'uuid' => $uuid,
        ]);

        // Assert files exist in public storage with correct extensions
        Storage::disk('public')->assertExists("uploads/{$uuid}_raw_photos.zip");
        Storage::disk('public')->assertExists("uploads/{$uuid}_collage.png");
    }
}
