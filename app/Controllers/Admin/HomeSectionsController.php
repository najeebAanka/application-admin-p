<?php

namespace App\Http\Controllers\Admin;

use App\CPU\Helpers;
use App\CPU\ImageManager;
use App\Http\Controllers\Controller;
use App\Model\Banner;
use App\Model\HomeSection;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class HomeSectionsController extends Controller
{
    function list(Request $request)
    {
        $query_param = [];
        $search = $request['search'];
        if ($request->has('search'))
        {
            $key = explode(' ', $request['search']);
            $home_sections = HomeSection::where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->Where('home_section_type', 'like', "%{$value}%");
                }
            })->orderBy('id', 'desc');
            $query_param = ['search' => $request['search']];
        }else{
            $home_sections = HomeSection::orderBy('id', 'desc');
        }
        $home_sections = $home_sections->paginate(Helpers::pagination_limit())->appends($query_param);

        return view('admin-views.home-sections.view', compact('home_sections','search'));
    }

    public function store(Request $request)
    {

        $request->validate([
            'image' => 'required',
        ], [
            'image.required' => 'Image is required!',

        ]);

        $home_section = new HomeSection;
        $home_section->title = $request->title;
        $home_section->home_section_type = $request->home_section_type;
        $home_section->resource_type = $request->resource_type;
        $home_section->resource_id = $request[$request->resource_type.'_id'];
        $home_section->position = $request->position;
        $home_section->photo = ImageManager::upload('home-section/', 'png', $request->file('image'));
        $home_section->save();
        Toastr::success('Home Section added successfully!');
        return back();
    }

    public function status(Request $request)
    {
        if ($request->ajax()) {
            $home_section = HomeSection::find($request->id);
            $home_section->published = $request->status;
            $home_section->save();
            $data = $request->status;
            return response()->json($data);
        }
    }

    public function edit($id)
    {
        $home_section = HomeSection::where('id', $id)->first();
        return view('admin-views.home-sections.edit',compact('home_section'));
    }

    public function update(Request $request,$id)
    {


        $home_section = HomeSection::find($id);
        $home_section->title = $request->title;
        $home_section->home_section_type = $request->home_section_type;
        $home_section->resource_type = $request->resource_type;
        $home_section->resource_id = $request[$request->resource_type.'_id'];
        $home_section->position = $request->position;
        if($request->file('image')) {
            $home_section->photo = ImageManager::update('home-sections/', $home_section['photo'], 'png', $request->file('image'));
        }
        $home_section->save();

        Toastr::success('Home Section updated successfully!');
        return back();
    }

    public function delete(Request $request)
    {
        $br = HomeSection::find($request->id);
        ImageManager::delete('/home-section/' . $br['photo']);
        $br->delete();
        return response()->json();
    }
}
