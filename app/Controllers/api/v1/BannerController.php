<?php

namespace App\Http\Controllers\api\v1;

use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BannerController extends Controller
{
    public function get_banners(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'banner_type' => 'required'
        ]);

        if ($validator->fails()) {
            return Helpers::sendError(Helpers::error_processor($validator), 403);
        }

        try {
            if ($request['banner_type'] == 'all') {
                $banners = Banner::where(['published' => 1])->get();
            } else {
                $banners = Banner::where(['published' => 1, 'banner_type' => $request['banner_type']])->get();
            }
            return Helpers::sendSuccess('Data Got!', \App\Resources\Banner::collection($banners));
        } catch (\Exception $e) {
            return Helpers::sendError([['message' => $e->getMessage()]], 403);
        }


    }
}
