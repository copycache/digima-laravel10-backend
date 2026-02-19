<?php

namespace App\Http\Controllers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    protected $fillable = ['url', 'name'];

    /**
     * Download image from S3 storage.
     *
     * @param string $s3FilePath The file path in S3 storage.
     * @return mixed The file content.
     */
    public static function downloadFromS3($s3FilePath)
    {
        $parsedUrl = parse_url($s3FilePath);
        $path = $parsedUrl['path'];
        $s3FilePath = ltrim($path, '/');
        // Download the file from S3 storage
        $fileContent = Storage::disk('s3')->get($s3FilePath);

        // Return the file content
        return $fileContent;
    }
}
