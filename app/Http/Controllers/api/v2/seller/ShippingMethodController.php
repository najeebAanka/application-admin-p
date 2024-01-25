<?php

namespace App\Http\Controllers\api\v2\seller;

use App\CPU\BackEndHelper;
use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Model\ShippingMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use function App\CPU\translate;

class ShippingMethodController extends Controller
{
    public function store(Request $request)
    {
        $data = Helpers::get_seller_by_token($request);

        if ($data['success'] == 1) {
            $seller = $data['data'];
        } else {
            return Helpers::sendError([['message' =>translate('Your existing session token does not authorize you any more') ]], 401);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|max:200',
            'duration' => 'required',
            'cost' => 'numeric'
        ]);

        if ($validator->errors()->count() > 0) {
            return Helpers::sendError(Helpers::error_processor($validator), 403);
        }

        DB::table('shipping_methods')->insert([
            'creator_id' => $seller['id'],
            'creator_type' => 'seller',
            'title' => $request['title'],
            'duration' => $request['duration'],
            'cost' => BackEndHelper::currency_to_aed($request['cost']),
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return Helpers::sendSuccess(translate('successfully_added!'), '');
    }

    public function list(Request $request)
    {
        $data = Helpers::get_seller_by_token($request);

        if ($data['success'] == 1) {
            $seller = $data['data'];
        } else {
            return Helpers::sendError([['message' =>translate('Your existing session token does not authorize you any more') ]], 401);
        }

        return Helpers::sendSuccess(translate('Data Got!'), ShippingMethod::where(['creator_type' => 'seller', 'creator_id' => $seller['id']])->get());
    }

    public function status_update(Request $request)
    {
        $data = Helpers::get_seller_by_token($request);

        if ($data['success'] == 1) {
            $seller = $data['data'];
        } else {
            return Helpers::sendError([['message' =>translate('Your existing session token does not authorize you any more') ]], 401);
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'status' => 'required|in:1,0',
        ]);

        if ($validator->errors()->count() > 0) {
            return Helpers::sendError(Helpers::error_processor($validator), 403);
        }

        ShippingMethod::where(['id' => $request['id'], 'creator_id' => $seller['id']])->update([
            'status' => $request['status']
        ]);
        return Helpers::sendSuccess( translate('successfully_status_updated'),'');
    }

    public function edit(Request $request, $id)
    {
        $data = Helpers::get_seller_by_token($request);

        if ($data['success'] == 1) {
            $seller = $data['data'];
        } else {
            return Helpers::sendError([['message' =>translate('Your existing session token does not authorize you any more') ]], 401);
        }
        $method = ShippingMethod::where(['id' => $id, 'creator_id' => $seller['id']])->first();
        if (isset($method)) {
            return Helpers::sendSuccess(translate("Data Got!"),$method);
        }
        return Helpers::sendSuccess( translate('data_not_found'),'');
    }

    public function update(Request $request, $id)
    {
        $data = Helpers::get_seller_by_token($request);

        if ($data['success'] == 1) {
            $seller = $data['data'];
        } else {
            return Helpers::sendError([['message' =>translate('Your existing session token does not authorize you any more') ]], 401);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|max:200',
            'duration' => 'required',
            'cost' => 'numeric'
        ]);

        if ($validator->errors()->count() > 0) {
            return Helpers::sendError(Helpers::error_processor($validator), 403);
        }

        DB::table('shipping_methods')->where(['id' => $id, 'creator_id' => $seller['id']])->update([
            'title' => $request['title'],
            'duration' => $request['duration'],
            'cost' => BackEndHelper::currency_to_aed($request['cost']),
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return Helpers::sendSuccess(translate('successfully_updated'),'');
    }

    public function delete(Request $request)
    {
        $data = Helpers::get_seller_by_token($request);

        if ($data['success'] == 1) {
            $seller = $data['data'];
        } else {
            return Helpers::sendError([['message' =>translate('Your existing session token does not authorize you any more') ]], 401);
        }

        ShippingMethod::where(['id' => $request->id, 'creator_id' => $seller['id']])->delete();
        return Helpers::sendSuccess(translate('successfully_deleted'),'');
    }
}
