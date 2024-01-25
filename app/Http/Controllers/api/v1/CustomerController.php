<?php

namespace App\Http\Controllers\api\v1;

use App\CPU\BackEndHelper;
use App\CPU\CustomerManager;
use App\CPU\Helpers;
use App\CPU\ImageManager;
use App\Http\Controllers\Controller;
use App\Model\Kitchen;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\Product;
use App\Model\ShippingAddress;
use App\Model\SupportTicket;
use App\Model\SupportTicketConv;
use App\Model\Wishlist;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use function App\CPU\translate;

class CustomerController extends Controller
{
    public function info(Request $request)
    {
        $user = User::where('id', $request->user()->id)->first();
        return Helpers::sendSuccess(translate("Data Got!"), \App\Resources\User::make($user));
    }

    public function create_support_ticket(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required',
            'type' => 'required',
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            return Helpers::sendError(Helpers::error_processor($validator), 403);
        }

        $request['customer_id'] = $request->user()->id;
        $request['priority'] = 'low';
        $request['status'] = 'pending';

        try {
            CustomerManager::create_support_ticket($request);
        } catch (\Exception $e) {
            return Helpers::sendError([['message' => $e->getMessage()]], $e->getCode());
        }
        return Helpers::sendSuccess(translate('Support ticket created successfully.'), '');
    }

    public function reply_support_ticket(Request $request, $ticket_id)
    {
        $support = new SupportTicketConv();
        $support->support_ticket_id = $ticket_id;
        $support->admin_id = 1;
        $support->customer_message = $request['message'];
        $support->save();
        return Helpers::sendSuccess(translate('Support ticket reply sent.'), '');
    }

    public function get_support_tickets(Request $request)
    {
        return Helpers::sendSuccess(translate('Data Got!'), SupportTicket::where('customer_id', $request->user()->id)->get());
    }

    public function get_support_ticket_conv($ticket_id)
    {
        return Helpers::sendSuccess(translate('Data Got!'), SupportTicketConv::where('support_ticket_id', $ticket_id)->get());
    }

    public function add_to_wishlist(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
        ]);

        if ($validator->fails()) {
            return Helpers::sendError(Helpers::error_processor($validator), 403);
        }

        $wishlist = Wishlist::where('customer_id', $request->user()->id)->where('product_id', $request->product_id)->first();

        if (empty($wishlist)) {
            $wishlist = new Wishlist;
            $wishlist->customer_id = $request->user()->id;
            $wishlist->product_id = $request->product_id;
            $wishlist->save();
            return Helpers::sendSuccess(translate('successfully added!'), '');
        }

        return Helpers::sendSuccess(translate('Already in your wishlist'), '');
    }

    public function remove_from_wishlist(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
        ]);

        if ($validator->fails()) {
            return Helpers::sendError(Helpers::error_processor($validator), 403);
        }

        $wishlist = Wishlist::where('customer_id', $request->user()->id)->where('product_id', $request->product_id)->first();

        if (!empty($wishlist)) {
            Wishlist::where(['customer_id' => $request->user()->id, 'product_id' => $request->product_id])->delete();
            return Helpers::sendSuccess(translate('successfully removed!'), '');

        }
        return Helpers::sendError([['message' => translate('No such data found!')]], 404);
    }

    public function wish_list(Request $request)
    {
        $product_ids = Wishlist::where('customer_id', $request->user()->id)->pluck('product_id');
        $products = Product::whereIn('id', $product_ids)->where('status', 1)->get();
        return Helpers::sendSuccess(translate('Data Got!'),
            \App\Resources\ProductBref::collection($products)
        );
    }

    public function address_list(Request $request)
    {
        return Helpers::sendSuccess(translate('Data Got!'), ShippingAddress::where('customer_id', $request->user()->id)->get());
    }


    public function kitchen_list(Request $request)
    {
        return Helpers::sendSuccess(translate('Data Got!'), \App\Resources\Kitchen::collection(Kitchen::where('user_id', $request->user()->id)->get()));
    }

    public function add_new_kitchen(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_on' => 'required',
            'full_name' => 'required',
            'phone' => 'required',
            'email' => 'required',
            'location' => 'required',
            'kitchen_type' => 'required',
            'floor_type' => 'required',
            'surface_type' => 'required',
            'kitchen_color' => 'required',
            'additional_color' => 'required',
            'length' => 'required',
            'width' => 'required',
            'height' => 'required'
        ]);

        if ($validator->fails()) {
            return Helpers::sendError(Helpers::error_processor($validator), 403);
        }

        $image_array = [];
        if (!empty($request->file('images'))) {
            foreach ($request->file('images') as $image) {
                if ($image != null) {
                    array_push($image_array, ImageManager::upload('kitchen/', 'png', $image));
                }
            }
        }

        $diagram_array = [];
        if (!empty($request->file('diagram'))) {
            foreach ($request->file('diagram') as $diagram) {
                if ($diagram != null) {
                    array_push($diagram_array, ImageManager::upload('kitchen/', 'png', $diagram));
                }
            }
        }


        $review = new Kitchen();
        $review->user_id = $request->user()->id;
        $review->service_on = $request->service_on;
        $review->full_name = $request->full_name;
        $review->phone = $request->phone;
        $review->email = $request->email;
        $review->location = $request->location;
        $review->kitchen_type = $request->kitchen_type;
        $review->floor_type = $request->floor_type;
        $review->surface_type = $request->surface_type;
        $review->kitchen_color = $request->kitchen_color;
        $review->additional_color = $request->additional_color;
        $review->length = $request->length;
        $review->width = $request->width;
        $review->height = $request->height;
        $review->images = json_encode($image_array);
        $review->diagrams = json_encode($diagram_array);
        $review->save();
        return Helpers::sendSuccess(translate('successfully request submitted!'), '');
    }


    public function add_new_address(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contact_person_name' => 'required',
            'address_type' => 'required',
            'address' => 'required',
            'city' => 'required',
//            'zip' => 'required',
            'phone' => 'required',
//            'latitude' => 'required',
//            'longitude' => 'required',
            'is_billing' => 'required'
        ]);

        if ($validator->fails()) {
            return Helpers::sendError(Helpers::error_processor($validator), 403);
        }

        $address = [
            'customer_id' => $request->user()->id,
            'contact_person_name' => $request->contact_person_name,
            'address_type' => $request->address_type,
            'address' => $request->address,
            'city' => $request->city,
            'country' => $request->country,
            'zip' => $request->zip,
            'phone' => $request->phone,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'is_billing' => $request->is_billing,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        DB::table('shipping_addresses')->insert($address);
        return Helpers::sendSuccess(translate('successfully added!'), '');
    }

    public function delete_address(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address_id' => 'required',
        ]);

        if ($validator->fails()) {
            return Helpers::sendError(Helpers::error_processor($validator), 403);
        }

        if (DB::table('shipping_addresses')->where(['id' => $request['address_id'], 'customer_id' => $request->user()->id])->first()) {
            DB::table('shipping_addresses')->where(['id' => $request['address_id'], 'customer_id' => $request->user()->id])->delete();
            return Helpers::sendSuccess(translate('successfully removed!'), '');
        }
        return Helpers::sendError([['message' => translate('No such data found!')]], 404);
    }

    public function get_order_list(Request $request)
    {
        $orders = Order::where(['customer_id' => $request->user()->id])->orderBy('id','desc')->get();
        $orders->map(function ($data) {
            $data['shipping_address_data'] = json_decode($data['shipping_address_data']);
            $data['billing_address_data'] = json_decode($data['billing_address_data']);
            return $data;
        });
        return Helpers::sendSuccess(translate("Data Got!"), \App\Resources\Order::collection($orders));
    }

    public function get_order_details(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
        ]);

        if ($validator->fails()) {
            return Helpers::sendError(Helpers::error_processor($validator), 403);
        }

        $details = OrderDetail::where(['order_id' => $request['order_id']])->get();
        $details->map(function ($query) {
            $query['variation'] = json_decode($query['variation'], true);
            $query['product_details'] = Helpers::product_data_formatting(json_decode($query['product_details'], true));
            return $query;
        });
        return Helpers::sendSuccess(translate("Data Got!"), $details);
    }

    public function update_profile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'f_name' => 'required',
            'l_name' => 'required',
            'phone' => 'required',
        ], [
            'f_name.required' => translate('First name is required!'),
            'l_name.required' => translate('Last name is required!'),
        ]);

        if ($validator->fails()) {
            return Helpers::sendError(Helpers::error_processor($validator), 403);
        }

        if ($request->has('image')) {
            $imageName = ImageManager::update('profile/', $request->user()->image, 'png', $request->file('image'));
        } else {
            $imageName = $request->user()->image;
        }

        if ($request['password'] != null && strlen($request['password']) > 5) {
            $pass = bcrypt($request['password']);
        } else {
            $pass = $request->user()->password;
        }

        $userDetails = [
            'f_name' => $request->f_name,
            'l_name' => $request->l_name,
            'phone' => $request->phone,
            'gender' => $request->gender,
            'birthdate' => $request->birthdate,
            'image' => $imageName,
            'password' => $pass,
            'updated_at' => now(),
        ];

        User::where(['id' => $request->user()->id])->update($userDetails);
        return Helpers::sendSuccess(translate('successfully updated!'), '');
    }

    public function update_cm_firebase_token(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cm_firebase_token' => 'required',
        ]);

        if ($validator->fails()) {
            return Helpers::sendError(Helpers::error_processor($validator), 403);
        }

        DB::table('users')->where('id', $request->user()->id)->update([
            'cm_firebase_token' => $request['cm_firebase_token'],
        ]);
        return Helpers::sendSuccess(translate('successfully updated!'), '');
    }
}
