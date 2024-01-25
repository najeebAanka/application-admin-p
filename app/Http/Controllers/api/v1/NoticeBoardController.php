<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Model\NoticeBoard;
use App\Model\Notification;

class NoticeBoardController extends Controller
{
    public function index()
    {
        try {
            return response()->json(['message' => 'Data Got!',
                'data' => NoticeBoard::active()->orderBy('id', 'DESC')->get()
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Exception', 'errors' => $e], 403);
        }
    }
}
