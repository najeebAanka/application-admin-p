<?php

namespace App\Http\Controllers\api\v1;

use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Notification;
use function App\CPU\translate;

class NotificationController extends Controller
{
    public function get_notifications()
    {
        try {
            return Helpers::sendSuccess(translate('Data Got!'),Notification::active()->orderBy('id','DESC')->get());
        } catch (\Exception $e) {
            return Helpers::sendError([['message' => $e->getMessage()]], $e->getCode());
        }
    }
}
