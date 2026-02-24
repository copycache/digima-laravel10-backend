<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ImageController;
use App\Models\Tbl_ai_marketing_tools;
use App\Models\Tbl_marketing_tools_category;
use App\Models\Tbl_marketing_tools_subcategory;
use App\Models\Tbl_slot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class MemberEbooksController extends Controller
{
    public function get_data()
    {
        $category_id = Request::input('category') ?? null;
        $subcategory_id = Request::input('subcategory') ?? null;
        $membership_id = Tbl_slot::where('slot_owner', Request::user()->id)->first()->slot_membership;
        $marketing_tools = Tbl_ai_marketing_tools::where('archived', 0);

        $marketing_tools = $marketing_tools->where(function ($query) use ($membership_id) {
            $query
                ->where('membership_id', $membership_id)
                ->orWhereNull('membership_id');
        });

        if ($category_id == null && $subcategory_id == null) {
            $response = $marketing_tools->get();
        }

        if ($category_id) {
            $response = $marketing_tools->where('category', $category_id)->get();
        }
        if ($subcategory_id) {
            $response = $marketing_tools->where('sub_category', $subcategory_id)->get();
        }

        return $response;
    }

    public function get_category()
    {
        $response = Tbl_marketing_tools_category::where('archived', 0)->get();
        return $response;
    }

    public function get_subcategory()
    {
        $category_id = Request::input('category_id');
        $response = Tbl_marketing_tools_subcategory::where('category_id', $category_id)->where('archived', 0)->get();
        return $response;
    }

    public function get_selected_tools()
    {
        $id = Request::input('id');
        $response = Tbl_ai_marketing_tools::where('id', $id)->where('tbl_ai_marketing_tools.archived', 0)->first();
        $response->image_link = json_decode($response->image_link);
        $response->file_link = json_decode($response->file_link);

        $category = Tbl_marketing_tools_category::where('id', $response->category)->where('archived', 0)->select('image_required', 'video_required', 'file_required')->first();
        if ($category) {
            $response->image_required = $category->image_required;
            $response->video_required = $category->video_required;
            $response->file_required = $category->file_required;
        }

        return $response;
    }

    protected $fillable = ['url', 'name'];

    /**
     * Download image from S3 storage.
     *
     * @param string $s3FilePath The file path in S3 storage.
     * @return mixed The file content.
     */
    public function download_from_s3(Request $request)
    {
        $s3FilePath = Request::input('link');
        // Download the image from S3
        $fileContent = ImageController::downloadFromS3($s3FilePath);

        // You can then return the file content as a response, or manipulate it further
        return response($fileContent)->header('Content-Type', 'image/jpeg');
    }
}
