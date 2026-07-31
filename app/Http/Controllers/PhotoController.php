<?php

namespace App\Http\Controllers;

use App\Models\ProcessedPhoto;
use App\Mail\SharePhotosMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PhotoController extends Controller
{
    /**
     * Store processed photos in public storage and database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'raw_image' => 'required|string',    // base64 data URL
            'framed_image' => 'required|string', // base64 data URL
            'mode' => 'nullable|string|in:default,polaroid',
            'uuid' => 'nullable|string',
        ]);

        $uuid = $request->input('uuid', (string) Str::uuid());
        $mode = $request->input('mode', 'default');

        // Process Raw Image (Image or Zip base64)
        $rawImage = $request->input('raw_image');
        $rawImage = preg_replace('/^data:[^;]+;base64,/', '', $rawImage);
        $rawImage = base64_decode($rawImage);
        
        if ($mode === 'polaroid') {
            $rawPath = "uploads/{$uuid}_raw_photos.zip";
        } else {
            $rawPath = "uploads/{$uuid}_raw.png";
        }
        Storage::disk('public')->put($rawPath, $rawImage);

        // Process Framed Image (Framed single or Collage)
        $framedImage = $request->input('framed_image');
        $framedImage = preg_replace('/^data:[^;]+;base64,/', '', $framedImage);
        $framedImage = base64_decode($framedImage);
        
        if ($mode === 'polaroid') {
            $framedPath = "uploads/{$uuid}_collage.png";
        } else {
            $framedPath = "uploads/{$uuid}_framed.png";
        }
        Storage::disk('public')->put($framedPath, $framedImage);

        // Save metadata to database
        $photo = ProcessedPhoto::create([
            'uuid' => $uuid,
            'raw_path' => $rawPath,
            'framed_path' => $framedPath,
        ]);

        return response()->json([
            'success' => true,
            'uuid' => $uuid,
            'share_url' => route('share.show', $uuid),
            'raw_url' => Storage::url($rawPath),
            'framed_url' => Storage::url($framedPath),
        ]);
    }

    /**
     * Show the share page.
     */
    public function share(string $uuid)
    {
        $photo = ProcessedPhoto::where('uuid', $uuid)->firstOrFail();

        return view('share', [
            'photo' => $photo,
            'raw_url' => Storage::url($photo->raw_path),
            'framed_url' => Storage::url($photo->framed_path),
        ]);
    }

    /**
     * Send photos to user's email.
     */
    public function sendEmail(Request $request, string $uuid)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $photo = ProcessedPhoto::where('uuid', $uuid)->firstOrFail();
        $email = $request->input('email');

        // Update the email field in database
        $photo->update(['email' => $email]);

        // Send Email in the background after the response is sent back to the browser
        dispatch(function () use ($email, $photo) {
            Mail::to($email)->send(new SharePhotosMail($photo));
        })->afterResponse();

        return response()->json([
            'success' => true,
            'message' => 'Foto berhasil diproses! Silakan periksa inbox email ' . $email . ' beberapa saat lagi.',
        ]);
    }
}
