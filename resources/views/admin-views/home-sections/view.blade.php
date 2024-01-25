@extends('layouts.back-end.app')
@section('title', \App\CPU\translate('Home Section'))
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
                <li class="breadcrumb-item" aria-current="page">{{\App\CPU\translate('Home Section')}}</li>
            </ol>
        </nav>
        <!-- Page Heading -->
        <div class="row">
            <div class="col-md-12" id="home-sectionbtn">
                <button id="home-section-add" class="btn btn-primary"><i
                            class="tio-add-circle"></i> {{ \App\CPU\translate('add_home_section')}}</button>
            </div>
        </div>
        <!-- Content Row -->
        <div class="row pt-4" id="home-section"
             style="display: none;text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        {{ \App\CPU\translate('home_section_form')}}
                    </div>
                    <div class="card-body">
                        <form action="{{route('admin.home-sections.store')}}" method="post"
                              enctype="multipart/form-data"
                              class="home_section_form">
                            @csrf
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <input type="hidden" id="title" name="title">
                                            <label for="name">{{ \App\CPU\translate('title')}}</label>
                                            <input type="text" name="title" class="form-control" id="title">
                                        </div>
                                        <div class="form-group">
                                            <input type="hidden" id="position" name="position">
                                            <label for="name">{{ \App\CPU\translate('position')}}</label>
                                            <input type="number" name="position" class="form-control" id="position">
                                        </div>
                                        <div class="form-group">
                                            <label for="name">{{ \App\CPU\translate('color')}}</label>
                                            <input type="color" name="color" class="form-control" id="color">
                                        </div>
                                        <div class="form-group">
                                            <label for="name">{{\App\CPU\translate('home_section_type')}}</label>
                                            <select style="width: 100%"
                                                    class="js-example-responsive form-control"
                                                    name="home_section_type" required>
                                                <option value="1">Vertical</option>
                                                <option value="2">Horizontal</option>
                                                <option value="5">2 Per Line</option>
                                                <option value="3">3 Per Line</option>
                                                <option value="4">4 Per line</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="resource_type">{{\App\CPU\translate('resource_type')}}</label>
                                            <select style="width: 100%" onchange="display_data(this.value)"
                                                    class="js-example-responsive form-control"
                                                    name="resource_type" required>
                                                <option value="Best Sell">Best Sell</option>
                                                <option value="category">Category</option>
                                                <option value="banner">Banner</option>
                                                <option value="offers">Offers</option>
                                            </select>
                                        </div>

                                        <div class="form-group" id="resource-product" style="display: none;">
                                            <label for="product_id">{{\App\CPU\translate('product')}}</label>
                                            <select style="width: 100%"
                                                    class="js-example-responsive form-control"
                                                    name="product_id">
                                                @foreach(\App\Model\Product::active()->get() as $product)
                                                    <option value="{{$product['id']}}">{{$product['name']}}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group" id="resource-category" style="display: none">
                                            <label for="name">{{\App\CPU\translate('category')}}</label>
                                            <select style="width: 100%"
                                                    class="js-example-responsive form-control"
                                                    name="category_id">
                                                @foreach(\App\CPU\CategoryManager::parents() as $category)
                                                    <option value="{{$category['id']}}">{{$category['name']}}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group" id="resource-shop" style="display: none">
                                            <label for="shop_id">{{\App\CPU\translate('shop')}}</label>
                                            <select style="width: 100%"
                                                    class="js-example-responsive form-control"
                                                    name="shop_id">
                                                @foreach(\App\Model\Shop::active()->get() as $shop)
                                                    <option value="{{$shop['id']}}">{{$shop['name']}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group" id="resource-banner" style="display: none">
                                            <label for="banner_id">{{\App\CPU\translate('Banner')}}</label>
                                            <select style="width: 100%"
                                                    class="js-example-responsive form-control"
                                                    name="banner_id">
                                                @foreach(\App\Model\Banner::where('published',1)->where('banner_type','Home Section')->get() as $banner)
                                                    <option value="{{$banner['id']}}">{{$banner['title']}}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group" id="resource-brand" style="display: none">
                                            <label for="brand_id">{{\App\CPU\translate('brand')}}</label>
                                            <select style="width: 100%"
                                                    class="js-example-responsive form-control"
                                                    name="brand_id">
                                                @foreach(\App\Model\Brand::all() as $brand)
                                                    <option value="{{$brand['id']}}">{{$brand['name']}}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <label for="name">{{ \App\CPU\translate('Image')}}</label><span
                                                class="badge badge-soft-danger">( {{\App\CPU\translate('ratio')}}
                                            4:1 )</span>
                                        <br>
                                        <div class="custom-file" style="text-align: left">
                                            <input type="file" name="image" id="mbimageFileUploader"
                                                   class="custom-file-input"
                                                   accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                            <label class="custom-file-label"
                                                   for="mbimageFileUploader">{{\App\CPU\translate('choose')}} {{\App\CPU\translate('file')}}</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <center>
                                            <img
                                                    style="width: auto;border: 1px solid; border-radius: 10px; max-width:400px;"
                                                    id="mbImageviewer"
                                                    src="{{asset('assets\back-end\img\400x400\img1.jpg')}}"
                                                    alt="home section image"/>
                                        </center>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer">
                                <a class="btn btn-secondary text-white cancel">{{ \App\CPU\translate('Cancel')}}</a>
                                <button id="add" type="submit"
                                        class="btn btn-primary">{{ \App\CPU\translate('save')}}</button>
                                <a id="update" class="btn btn-primary"
                                   style="display: none; color: #fff;">{{ \App\CPU\translate('update')}}</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" style="margin-top: 20px" id="home-sectiontable">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="flex-between row justify-content-between align-items-center flex-grow-1 mx-1">
                            <div class="flex-between">
                                <div><h5>{{ \App\CPU\translate('home_section_table')}}</h5></div>
                                <div class="mx-1"><h5 style="color: red;">({{ $home_sections->total() }})</h5></div>
                            </div>
                            <div style="width: 30vw">
                                <!-- Search -->
                                <form action="{{ url()->current() }}" method="GET">
                                    <div class="input-group input-group-merge input-group-flush">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                <i class="tio-search"></i>
                                            </div>
                                        </div>
                                        <input id="datatableSearch_" type="search" name="search" class="form-control"
                                               placeholder="{{ \App\CPU\translate('Search_by_home_section_Type')}}"
                                               aria-label="Search orders" value="{{ $search }}" required>
                                        <button type="submit"
                                                class="btn btn-primary">{{ \App\CPU\translate('Search')}}</button>
                                    </div>
                                </form>
                                <!-- End Search -->
                            </div>
                        </div>
                    </div>
                    <div class="card-body" style="padding: 0">
                        <div class="table-responsive">
                            <table id="columnSearchDatatable"
                                   style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};"
                                   class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                <thead class="thead-light">
                                <tr>
                                    <th>{{\App\CPU\translate('sl')}}</th>
                                    <th>{{\App\CPU\translate('title')}}</th>
                                    <th>{{\App\CPU\translate('image')}}</th>
                                    <th>{{\App\CPU\translate('home_section_type')}}</th>
                                    <th>{{\App\CPU\translate('published')}}</th>
                                    <th>{{\App\CPU\translate('is_mad_test')}}</th>
                                    <th style="width: 50px">{{\App\CPU\translate('action')}}</th>
                                </tr>
                                </thead>
                                @foreach($home_sections as $key=>$home_section)
                                    <tbody>

                                    <tr>
                                        <th scope="row">{{$home_sections->firstItem()+$key}}</th>
                                        <td>{{$home_section->title}}</td>
                                        <td>
                                            <img width="80"
                                                 onerror="this.src='{{asset('assets/front-end/img/image-place-holder.png')}}'"
                                                 src="{{asset('storage/home-section')}}/{{$home_section['photo']}}">
                                        </td>
                                        <td>
                                            @if($home_section->home_section_type==1)
                                                <option value="1">Vertical</option>
                                            @elseif($home_section->home_section_type==2)
                                                <option value="2">Horizontal</option>
                                            @elseif($home_section->home_section_type==3)
                                                <option value="3">3 Per Line</option>
                                            @elseif($home_section->home_section_type==4)
                                                <option value="4">4 Per line</option>
                                            @endif

                                        </td>
                                        <td><label class="switch"><input type="checkbox" class="status"
                                                                         id="{{$home_section->id}}" <?php if ($home_section->published == 1) echo "checked" ?>><span
                                                        class="slider round"></span></label></td>
                                        <td><label class="switch"><input type="checkbox" class="is_mad_test"
                                                                         id="{{$home_section->id}}is_mad_test"
                                                <?php if ($home_section->is_mad_test == 1) echo "checked" ?>><span
                                                        class="slider round"></span></label></td>

                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                                        id="dropdownMenuButton" data-toggle="dropdown"
                                                        aria-haspopup="true"
                                                        aria-expanded="false">
                                                    <i class="tio-settings"></i>
                                                </button>
                                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                    <a class="dropdown-item"
                                                       href="{{route('admin.home-sections.edit',[$home_section['id']])}}"
                                                       style="cursor: pointer;"> {{ \App\CPU\translate('Edit')}}</a>
                                                    <a class="dropdown-item delete" style="cursor: pointer;"
                                                       id="{{$home_section['id']}}"> {{ \App\CPU\translate('Delete')}}</a>
                                                </div>
                                            </div>

                                        </td>
                                    </tr>

                                    </tbody>
                                @endforeach
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        {{$home_sections->links()}}
                    </div>
                    @if(count($home_sections)==0)
                        <div class="text-center p-4">
                            <img class="mb-3" src="{{asset('assets/back-end')}}/svg/illustrations/sorry.svg"
                                 alt="Image Description" style="width: 7rem;">
                            <p class="mb-0">{{ \App\CPU\translate('No_data_to_show')}}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        $(".js-example-theme-single").select2({
            theme: "classic"
        });

        $(".js-example-responsive").select2({
            // dir: "rtl",
            width: 'resolve'
        });

        function display_data(data) {

            $('#resource-product').hide();
            $('#resource-brand').hide();
            $('#resource-category').hide();
            $('#resource-shop').hide();
            $('#resource-banner').hide();

            if (data === 'product') {
                $('#resource-product').show()
            } else if (data === 'brand') {
                $('#resource-brand').show()
            } else if (data === 'category') {
                $('#resource-category').show()
            } else if (data === 'shop') {
                $('#resource-shop').show()
            } else if (data === 'banner') {
                $('#resource-banner').show()
            }
        }
    </script>
    <script>
        function mbimagereadURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#mbImageviewer').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#mbimageFileUploader").change(function () {
            mbimagereadURL(this);
        });

        function fbimagereadURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#fbImageviewer').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#fbimageFileUploader").change(function () {
            fbimagereadURL(this);
        });

        function pbimagereadURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#pbImageviewer').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#pbimageFileUploader").change(function () {
            pbimagereadURL(this);
        });

    </script>
    <script>
        $('#home-section-add').on('click', function () {
            $('#home-section').show();
        });

        $('.cancel').on('click', function () {
            $('.home_section_form').attr('action', "{{route('admin.home-sections.store')}}");
            $('#home-section').hide();
        });

        $(document).on('change', '.status', function () {
            var id = $(this).attr("id");
            if ($(this).prop("checked") == true) {
                var status = 1;
            } else if ($(this).prop("checked") == false) {
                var status = 0;
            }

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{route('admin.home-sections.status')}}",
                method: 'POST',
                data: {
                    id: id,
                    status: status
                },
                success: function (data) {
                    if (data == 1) {
                        toastr.success('{{\App\CPU\translate('home_section_published_successfully')}}');
                    } else {
                        toastr.success('{{\App\CPU\translate('home_section_unpublished_successfully')}}');
                    }
                }
            });
        });
        $(document).on('change', '.is_mad_test', function () {
            var id = $(this).attr("id");
            if ($(this).prop("checked") == true) {
                var is_mad_test = 1;
            } else if ($(this).prop("checked") == false) {
                var is_mad_test = 0;
            }

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{route('admin.home-sections.is_mad_test')}}",
                method: 'POST',
                data: {
                    id: id.replaceAll('is_mad_test'),
                    is_mad_test: is_mad_test
                },
                success: function (data) {
                    if (data == 1) {
                        toastr.success('{{\App\CPU\translate('Is mad_test Changed')}}');
                    } else {
                        toastr.success('{{\App\CPU\translate('Is mad_test Changed')}}');
                    }
                }
            });
        });

        $(document).on('click', '.delete', function () {
            var id = $(this).attr("id");
            Swal.fire({
                title: "{{\App\CPU\translate('Are_you_sure_delete_this_home_section')}}?",
                text: "{{\App\CPU\translate('You_will_not_be_able_to_revert_this')}}!",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '{{\App\CPU\translate('Yes')}}, {{\App\CPU\translate('delete_it')}}!'
            }).then((result) => {
                if (result.value) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                        }
                    });
                    $.ajax({
                        url: "{{route('admin.home-sections.delete')}}",
                        method: 'POST',
                        data: {id: id},
                        success: function () {
                            toastr.success('{{\App\CPU\translate('home_section_deleted_successfully')}}');
                            location.reload();
                        }
                    });
                }
            })
        });
    </script>
    <!-- Page level plugins -->
@endpush
