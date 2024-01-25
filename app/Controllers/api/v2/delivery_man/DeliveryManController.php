<?php

namespace App\Http\Controllers\api\v2\delivery_man;

use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Model\DeliveryHistory;
use App\Model\DeliveryMan;
use App\Model\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use function App\CPU\translate;

class DeliveryManController extends Controller
{
    public function info(Request $request)
    {
        return Helpers::sendSuccess(translate('Info'), $request['delivery_man']);
    }

    public function get_current_orders(Request $request)
    {
        $d_man = $request['delivery_man'];
        $orders = Order::with(['shippingAddress', 'customer'])->whereIn('order_status', ['pending', 'processing', 'out_for_delivery', 'confirmed'])
            ->where(['delivery_man_id' => $d_man['id']])->get();
        return Helpers::sendSuccess(translate('Data Got!'), $orders);
    }

    public function record_location_data(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'longitude' => 'required',
            'latitude' => 'required',
            'location' => 'required',
        ]);

        if ($validator->fails()) {
            return Helpers::sendError(Helpers::error_processor($validator), 403);
        }

        $d_man = $request['delivery_man'];
        DB::table('delivery_histories')->insert([
            'order_id' => $request['order_id'],
            'deliveryman_id' => $d_man['id'],
            'longitude' => $request['longitude'],
            'latitude' => $request['latitude'],
            'time' => now(),
            'location' => $request['location'],
            'created_at' => now(),
            'updated_at' => now()
        ]);
        return Helpers::sendSuccess('location recorded', '');
    }

    public function get_order_history(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required'
        ]);
        if ($validator->fails()) {
            return Helpers::sendError(Helpers::error_processor($validator), 403);
        }
        $d_man = $request['delivery_man'];
        $history = DeliveryHistory::where(['order_id' => $request['order_id'], 'deliveryman_id' => $d_man['id']])->get();
        return Helpers::sendSuccess("Data Got!", $history);
    }

    public function update_order_status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'status' => 'required|in:delivered,canceled,returned,out_for_delivery'
        ]);

        if ($validator->fails()) {
            return Helpers::sendError(Helpers::error_processor($validator), 403);
        }

        $d_man = $request['delivery_man'];

        Order::where(['id' => $request['order_id'], 'delivery_man_id' => $d_man['id']])->update([
            'order_status' => $request['status']
        ]);

        $order = Order::where(['delivery_man_id' => $d_man['id'], 'id' => $request['order_id']])->first();
        try {
            $fcm_token = $order->customer->cm_firebase_token;
            if ($request['status'] == 'out_for_delivery') {
                $value = Helpers::order_status_update_message('ord_start');
            } elseif ($request['status'] == 'delivered') {
                $value = Helpers::order_status_update_message('delivered');
            }
            if ($value) {
                $data = [
                    'title' => translate('order'),
                    'description' => $value,
                    'order_id' => $order['id'],
                    'image' => '',
                ];
                Helpers::send_push_notif_to_device($fcm_token, $data);
            }
        } catch (\Exception $e) {
        }
        return Helpers::sendSuccess('Status updated', '');
    }

    public function get_order_details(Request $request)
    {
        $d_man = $request['delivery_man'];
        $order = Order::with(['details'])->where(['delivery_man_id' => $d_man['id'], 'id' => $request['order_id']])->first();
        $details = $order->details;
        foreach ($details as $det) {
            $det['variation'] = json_decode($det['variation']);
            $det['product_details'] = Helpers::product_data_formatting(json_decode($det['product_details'], true));
        }
        return Helpers::sendSuccess("Data Got!", $details);
    }

    public function get_all_orders(Request $request)
    {
        $d_man = $request['delivery_man'];
        $orders = Order::with(['shippingAddress', 'customer'])->where(['delivery_man_id' => $d_man['id']])->get();
        return Helpers::sendSuccess("Data Got!", $orders);
    }

    public function get_last_location(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required'
        ]);
        if ($validator->fails()) {
            return Helpers::sendError(Helpers::error_processor($validator), 403);
        }

        $last_data = DeliveryHistory::where(['order_id' => $request['order_id']])->latest()->first();
        return Helpers::sendSuccess("Data Got!", $last_data);
    }

    public function order_payment_status_update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'payment_status' => 'in:paid,unpaid'
        ]);

        if ($validator->fails()) {
            return Helpers::sendError(Helpers::error_processor($validator), 403);
        }

        $d_man = $request['delivery_man'];
        if (Order::where(['delivery_man_id' => $d_man['id'], 'id' => $request['order_id']])->first()) {
            Order::where(['delivery_man_id' => $d_man['id'], 'id' => $request['order_id']])->update([
                'payment_status' => $request['payment_status']
            ]);
            return Helpers::sendSuccess(translate('Payment status updated'), '');
        }

        return Helpers::sendError([  ['code' => 'order', 'message' => translate('not found!')]], 404);
    }

    public function update_fcm_token(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required'
        ]);

        if ($validator->fails()) {
            return Helpers::sendError(Helpers::error_processor($validator), 403);
        }

        $d_man = $request['delivery_man'];
        DeliveryMan::where(['id' => $d_man['id']])->update([
            'fcm_token' => $request['fcm_token']
        ]);
        return Helpers::sendSuccess('successfully updated!','');
    }
}
