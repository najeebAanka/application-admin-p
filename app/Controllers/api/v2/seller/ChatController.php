<?php

namespace App\Http\Controllers\api\v2\seller;

use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Chatting;
use App\Model\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use function App\CPU\translate;

class ChatController extends Controller
{
    public function messages(Request $request)
    {
        $data = Helpers::get_seller_by_token($request);

        if ($data['success'] == 1) {
            $seller = $data['data'];
        } else {
            return Helpers::sendError([['message' => translate('Your existing session token does not authorize you any more')]], 401);
        }

        try {
            $messages = Chatting::with(['seller_info', 'customer', 'shop'])->where('seller_id', $seller['id'])->latest()
                ->get();
            return Helpers::sendSuccess("Data Got!", $messages);
        } catch (\Exception $e) {

            return Helpers::sendError([['message' => $e->getMessage()]], 401);
        }
    }

    public function send_message(Request $request)
    {
        $data = Helpers::get_seller_by_token($request);

        if ($data['success'] == 1) {
            $seller = $data['data'];
        } else {
            return Helpers::sendError([['message' => translate('Your existing session token does not authorize you any more')]], 401);
        }

        if ($request->message == '') {
            return Helpers::sendSuccess(translate('type something!'), '');
        } else {
            $shop_id = Shop::where('seller_id', $seller['id'])->first()->id;
            $message = $request->message;
            $time = now();

            DB::table('chattings')->insert([
                'user_id' => $request->user_id, //user_id == seller_id
                'shop_id' => $shop_id,
                'seller_id' => $seller['id'],
                'message' => $request->message,
                'sent_by_seller' => 1,
                'seen_by_seller' => 0,
                'created_at' => now(),
            ]);
            return Helpers::sendSuccess(translate('Data Got!'), ['message' => $message, 'time' => $time]);
        }
    }
}
