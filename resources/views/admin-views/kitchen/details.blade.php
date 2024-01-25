@extends('layouts.back-end.app')
@section('title', \App\CPU\translate('Kitchens'))
@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a
                            href="{{route('admin.dashboard')}}">{{\App\CPU\translate('Dashboard')}}</a>
                </li>
                <li class="breadcrumb-item" aria-current="page">{{\App\CPU\translate('Kitchen')}}</li>
            </ol>
        </nav>

        <div class="row" style="margin-top: 20px" id="banner-table">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="flex-between row justify-content-between align-items-center flex-grow-1 mx-1">
                            <div class="flex-between">
                                <div><h5>{{ \App\CPU\translate('Kitchens')}}</h5></div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pr-3 pl-3">
                        <div class="row">
                            <div class="col-2 text-primary font-weight-bold">Full Name<hr/></div>
                            <div class="col-4">{{$kitchen->full_name}}</div>
                            <div class="col-2 text-primary font-weight-bold">Service On<hr/></div>
                            <div class="col-4">{{$kitchen->service_on}}</div>
                        </div>
                        <div class="row">
                            <div class="col-2 text-primary font-weight-bold">Phone<hr/></div>
                            <div class="col-4">{{$kitchen->phone}}</div>
                            <div class="col-2 text-primary font-weight-bold">Email<hr/></div>
                            <div class="col-4">{{$kitchen->email}}</div>
                        </div>
                        <div class="row">
                            <div class="col-2 text-primary font-weight-bold">Location<hr/></div>
                            <div class="col-4">{{$kitchen->location}}</div>
                            <div class="col-2 text-primary font-weight-bold">Kitchen Type<hr/></div>
                            <div class="col-4">{{$kitchen->kitchen_type}}</div>
                        </div>
                        <div class="row">
                            <div class="col-2 text-primary font-weight-bold">Floor Type<hr/></div>
                            <div class="col-4">{{$kitchen->floor_type}}</div>
                            <div class="col-2 text-primary font-weight-bold">Surface Type<hr/></div>
                            <div class="col-4">{{$kitchen->surface_type}}</div>
                        </div>
                        <div class="row">
                            <div class="col-2 text-primary font-weight-bold">Kitchen Color<hr/></div>
                            <div class="col-4"
                                 style="background-color: {{$kitchen->kitchen_color}};height: 35px;width: 35px;"></div>
                            <div class="col-2 text-primary font-weight-bold">Additional Color<hr/></div>
                            <div class="col-4"
                                 style="background-color: {{$kitchen->additional_color}};height: 35px;width: 35px;"></div>
                        </div>
                        <div class="row">
                            <div class="col-2 text-primary font-weight-bold">Length<hr/></div>
                            <div class="col-2">{{$kitchen->length}}</div>
                            <div class="col-2 text-primary font-weight-bold">Width<hr/></div>
                            <div class="col-2">{{$kitchen->width}}</div>
                            <div class="col-2 text-primary font-weight-bold">Height<hr/></div>
                            <div class="col-2">{{$kitchen->height}}</div>
                        </div>
                        <div class="row">
                            <div class="col-2 text-primary font-weight-bold">Status<hr/></div>
                            <div class="col-4">
                                <form method="POST" action="{{route('admin.kitchen.status')}}">
                                    @csrf
                                    <input type="hidden" name="id" value="{{$kitchen->id}}">
                                    <select name="status" class="form-control" onchange="this.form.submit();">
                                        <option value="pending" {{$kitchen->status=='pending'?'selected':''}}>Pending</option>
                                        <option value="reviewed" {{$kitchen->status=='reviewed'?'selected':''}}>Reviewed</option>
                                        <option value="in_progress" {{$kitchen->status=='in_progress'?'selected':''}}>In Progress</option>
                                        <option value="rejected" {{$kitchen->status=='rejected'?'selected':''}}>Rejected</option>
                                        <option value="done" {{$kitchen->status=='done'?'selected':''}}>Done</option>
                                    </select>
                                </form>
                            </div>
                            <div class="col-2 text-primary font-weight-bold">Created At<hr/></div>
                            <div class="col-4">{{\Carbon\Carbon::parse($kitchen->created_at)->toDateString()}}</div>
                        </div>
                        <div class="row">
                            <div class="col-12 text-primary font-weight-bold">Images<hr/></div>
                            @foreach(json_decode($kitchen->images) as $image)
                                <div class="col-4 mt-2">
                                    <img src="{{asset('storage/kitchen') . '/' .$image}}" style="width: 100%;">
                                </div>
                            @endforeach
                        </div>
                        <div class="row">
                            <div class="col-12 text-primary font-weight-bold">Diagrams<hr/></div>
                            @foreach(json_decode($kitchen->diagrams) as $image)
                                <div class="col-4 mt-2">
                                    <img src="{{asset('storage/kitchen') . '/' .$image}}" style="width: 100%;">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>

    </script>
@endpush
