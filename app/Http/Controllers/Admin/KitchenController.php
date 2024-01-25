<?php

namespace App\Http\Controllers\Admin;

use App\CPU\Helpers;
use App\CPU\ImageManager;
use App\Http\Controllers\Controller;
use App\Model\Kitchen;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class kitchenController extends Controller
{
    function list(Request $request)
    {
        $query_param = [];
        $search = $request['search'];
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $kitchens = kitchen::where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->Where('full_name', 'like', "%{$value}%");
                }
            })->orderBy('id', 'desc');
            $query_param = ['search' => $request['search']];
        } else {
            $kitchens = kitchen::orderBy('id', 'desc');
        }
        $kitchens = $kitchens->paginate(Helpers::pagination_limit())->appends($query_param);

        return view('admin-views.kitchen.view', compact('kitchens', 'search'));
    }

    public function view($id)
    {
        $kitchen = kitchen::where('id', $id)->first();

        if (!$kitchen) {
            return redirect()->route('admin.kitchen.list');
        }

        return view('admin-views.kitchen.details', [
            'kitchen' => $kitchen
        ]);
    }

    public function status(Request $request)
    {
        $kitchen = kitchen::find($request->id);
        $kitchen->status = $request->status;
        $kitchen->update();
        return redirect()->back();
    }

}
