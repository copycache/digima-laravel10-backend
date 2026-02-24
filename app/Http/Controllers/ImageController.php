<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public static function downloadFromS3($s3FilePath)
    {
        $path = ltrim(parse_url($s3FilePath, PHP_URL_PATH), '/');
        return Storage::disk('s3')->get($path);
    }
}
