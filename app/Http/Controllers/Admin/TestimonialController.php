<?php

namespace App\Http\Controllers\Admin;

use App\CPU\Helpers;
use App\CPU\ImageManager;
use App\Http\Controllers\Controller;
use App\Model\Notification;
use App\Model\Testimonial;
use App\Model\Translation;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class TestimonialController extends Controller
{
    public function index(Request $request)
    {
        $query_param = [];
        $search = $request['search'];
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $testimonials = Testimonial::where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->Where('title', 'like', "%{$value}%");
                }
            });
            $query_param = ['search' => $request['search']];
        } else {
            $testimonials = new Testimonial();
        }
        $notifications = $testimonials->latest()->paginate(Helpers::pagination_limit())->appends($query_param);
        return view('admin-views.testimonials.index', compact('notifications', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required'
        ], [
            'title.required' => 'title is required!',
        ]);

        $testimonial = new Testimonial();
        $testimonial->title = $request->title[array_search('en', $request->lang)];
        $testimonial->position = $request->title[array_search('en', $request->lang)];
        $testimonial->description = $request->description[array_search('en', $request->lang)];

        if ($request->has('image')) {
            $testimonial->image = ImageManager::upload('testimonials/', 'png', $request->file('image'));
        } else {
            $testimonial->image = 'null';
        }

        $testimonial->status = 1;
        $testimonial->save();


        $data = [];
        foreach ($request->lang as $index => $key) {
            if ($request->title[$index] && $key != 'en') {
                array_push($data, array(
                    'translationable_type' => 'App\Model\Testimonial',
                    'translationable_id' => $testimonial->id,
                    'locale' => $key,
                    'key' => 'title',
                    'value' => $request->title[$index],
                ));
            }
            if ($request->position[$index] && $key != 'en') {
                array_push($data, array(
                    'translationable_type' => 'App\Model\Testimonial',
                    'translationable_id' => $testimonial->id,
                    'locale' => $key,
                    'key' => 'position',
                    'value' => $request->position[$index],
                ));
            }
            if ($request->description[$index] && $key != 'en') {
                array_push($data, array(
                    'translationable_type' => 'App\Model\Testimonial',
                    'translationable_id' => $testimonial->id,
                    'locale' => $key,
                    'key' => 'description',
                    'value' => $request->description[$index],
                ));
            }
        }
        Translation::insert($data);

//        try {
//            Helpers::send_push_notif_to_topic($data);
//        } catch (\Exception $e) {
//            Toastr::warning('Testimonial failed!');
//        }

        Toastr::success('Testimonial saved successfully!');
        return back();
    }

    public function edit($id)
    {
        $notification = Testimonial::withoutGlobalScopes()->find($id);
//        return $notification;
        return view('admin-views.testimonials.edit', compact('notification'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
            'position' => 'required',
            'description' => 'required',
        ], [
            'title.required' => 'title is required!',
        ]);

        $notification = Testimonial::find($id);
        $notification->title = $request->title[array_search('en', $request->lang)];
        $notification->position = $request->title[array_search('en', $request->lang)];
        $notification->description = $request->description[array_search('en', $request->lang)];
        if($request->has('image')){
            $notification->image = ImageManager::update('testimonials/', $notification->image, 'png', $request->file('image'));
        }
        $notification->save();

        foreach ($request->lang as $index => $key) {
            if ($request->title[$index] && $key != 'en') {
                Translation::updateOrInsert(
                    ['translationable_type' => 'App\Model\Testimonial',
                        'translationable_id' => $notification->id,
                        'locale' => $key,
                        'key' => 'title'],
                    ['value' => $request->title[$index]]
                );
            }if ($request->position[$index] && $key != 'en') {
                Translation::updateOrInsert(
                    ['translationable_type' => 'App\Model\Testimonial',
                        'translationable_id' => $notification->id,
                        'locale' => $key,
                        'key' => 'position'],
                    ['value' => $request->position[$index]]
                );
            }
            if ($request->description[$index] && $key != 'en') {
                Translation::updateOrInsert(
                    ['translationable_type' => 'App\Model\Testimonial',
                        'translationable_id' => $notification->id,
                        'locale' => $key,
                        'key' => 'description'],
                    ['value' => $request->description[$index]]
                );
            }
        }

        Toastr::success('Testimonial updated successfully!');
        return back();
    }

    public function status(Request $request)
    {
        if ($request->ajax()) {
            $notification = Testimonial::find($request->id);
            $notification->status = $request->status;
            $notification->save();
            $data = $request->status;
            return response()->json($data);
        }
    }

    public function delete(Request $request)
    {
        $notification = Testimonial::find($request->id);
        ImageManager::delete('/testimonials/' . $notification['image']);
        $notification->delete();
        return response()->json();
    }
}
