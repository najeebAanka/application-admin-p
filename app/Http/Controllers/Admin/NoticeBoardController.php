<?php

namespace App\Http\Controllers\Admin;

use App\CPU\Helpers;
use App\CPU\ImageManager;
use App\Http\Controllers\Controller;
use App\Model\NoticeBoard;
use App\Model\NoticeBoards;
use App\Model\Notification;
use App\Model\Translation;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class NoticeBoardController extends Controller
{
    public function index(Request $request)
    {
        $query_param = [];
        $search = $request['search'];
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $noticeBoards = NoticeBoard::where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->Where('title', 'like', "%{$value}%");
                }
            });
            $query_param = ['search' => $request['search']];
        } else {
            $noticeBoards = new NoticeBoard();
        }
        $notifications = $noticeBoards->latest()->paginate(Helpers::pagination_limit())->appends($query_param);
        return view('admin-views.trents.index', compact('notifications', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required'
        ], [
            'title.required' => 'title is required!',
        ]);

        $notice = new NoticeBoard;
        $notice->title =$request->title[array_search('en', $request->lang)];
        $notice->description = $request->description[array_search('en', $request->lang)];

        if ($request->has('image')) {
            $notice->image = ImageManager::upload('trents/', 'png', $request->file('image'));
        } else {
            $notice->image = 'null';
        }

        $notice->status = 1;
        $notice->save();


        $data = [];
        foreach ($request->lang as $index => $key) {
            if ($request->title[$index] && $key != 'en') {
                array_push($data, array(
                    'translationable_type' => 'App\Model\NoticeBoard',
                    'translationable_id' => $notice->id,
                    'locale' => $key,
                    'key' => 'title',
                    'value' => $request->title[$index],
                ));
            }
            if ($request->description[$index] && $key != 'en') {
                array_push($data, array(
                    'translationable_type' => 'App\Model\NoticeBoard',
                    'translationable_id' => $notice->id,
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
//            Toastr::warning('Notice Board failed!');
//        }

        Toastr::success('Notice Board saved successfully!');
        return back();
    }

    public function edit($id)
    {
        $notification = NoticeBoard::withoutGlobalScopes()->find($id);
//        return $notification;
        return view('admin-views.trents.edit', compact('notification'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
        ], [
            'title.required' => 'title is required!',
        ]);

        $notification = NoticeBoard::find($id);
        $notification->title =$request->title[array_search('en', $request->lang)];
        $notification->description = $request->description[array_search('en', $request->lang)];
        if($request->has('image')){
            $notification->image = ImageManager::update('trents/', $notification->image, 'png', $request->file('image'));
        }
        $notification->save();

        foreach ($request->lang as $index => $key) {
            if ($request->title[$index] && $key != 'en') {
                Translation::updateOrInsert(
                    ['translationable_type' => 'App\Model\NoticeBoard',
                        'translationable_id' => $notification->id,
                        'locale' => $key,
                        'key' => 'title'],
                    ['value' => $request->title[$index]]
                );
            }
            if ($request->description[$index] && $key != 'en') {
                Translation::updateOrInsert(
                    ['translationable_type' => 'App\Model\NoticeBoard',
                        'translationable_id' => $notification->id,
                        'locale' => $key,
                        'key' => 'description'],
                    ['value' => $request->description[$index]]
                );
            }
        }

        Toastr::success('Notice Board updated successfully!');
        return back();
    }

    public function status(Request $request)
    {
        if ($request->ajax()) {
            $notification = NoticeBoard::find($request->id);
            $notification->status = $request->status;
            $notification->save();
            $data = $request->status;
            return response()->json($data);
        }
    }

    public function delete(Request $request)
    {
        $notification = NoticeBoard::find($request->id);
        ImageManager::delete('/trents/' . $notification['image']);
        $notification->delete();
        return response()->json();
    }
}
