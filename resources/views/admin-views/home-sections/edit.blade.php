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
        <!-- Content Row -->
        <div class="row pt-4" style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        {{ \App\CPU\translate('home_section_update_form')}}
                    </div>
                    <div class="card-body">
                        <form action="{{route('admin.home-sections.update',[$home_section['id']])}}" method="post"
                              enctype="multipart/form-data"
                              class="home_section_form">
                            @csrf
                            @method('put')
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <input type="hidden" id="title" name="title">
                                            <label for="name">{{ \App\CPU\translate('title')}}</label>
                                            <input type="text" name="title" class="form-control"
                                                   value="{{$home_section['title']}}">
                                        </div>
                                        <div class="form-group">
                                            <input type="hidden" id="position" name="position">
                                            <label for="name">{{ \App\CPU\translate('Position')}}</label>
                                            <input type="number" name="position" class="form-control"
                                                   value="{{$home_section['position']}}">
                                        </div>
                                        <div class="form-group">
                                            <label for="color">{{ \App\CPU\translate('color')}}</label>
                                            <input type="color" name="color" class="form-control"
                                                   value="{{$home_section['color']}}">
                                        </div>
                                        <div class="form-group">
                                            <label for="name">{{\App\CPU\translate('home_section_type')}}</label>
                                            <select style="width: 100%"
                                                    class="js-example-responsive form-control"
                                                    name="home_section_type" required>
                                                <option value="1" {{$home_section['home_section_type']=='Vertical'?'selected':''}}>
                                                    Vertical
                                                </option>
                                                <option value="2" {{$home_section['home_section_type']=='Horizontal'?'selected':''}}>
                                                    Horizontal
                                                </option>
                                                <option value=5" {{$home_section['home_section_type']=='2 Per Line'?'selected':''}}>
                                                    2 Per Line
                                                </option>
                                                <option value=3" {{$home_section['home_section_type']=='3 Per Line'?'selected':''}}>
                                                    3 Per Line
                                                </option>
                                                <option value=4" {{$home_section['home_section_type']=='4 Per Line'?'selected':''}}>
                                                    4 Per Line
                                                </option>
                                            </select>
                                        </div>


                                        <div class="form-group">
                                            <label for="resource_id">{{\App\CPU\translate('resource_type')}}</label>
                                            <select style="width: 100%" onchange="display_data(this.value)"
                                                    class="js-example-responsive form-control"
                                                    name="resource_type" required>

                                                <option value="Best Sell" {{$home_section['resource_type']=='Best Sell'?'selected':''}}>
                                                    Best Sell
                                                </option>
                                                <option value="category" {{$home_section['resource_type']=='category'?'selected':''}}>
                                                    Category
                                                </option>
                                                <option value="banner" {{$home_section['resource_type']=='banner'?'selected':''}}>
                                                    Banner
                                                </option>
                                                <option value="offers" {{$home_section['resource_type']=='offers'?'selected':''}}>
                                                    Offers
                                                </option>
                                            </select>
                                        </div>

                                        <div class="form-group" id="resource-product"
                                             style="display: {{$home_section['resource_type']=='product'?'block':'none'}}">
                                            <label for="product_id">{{\App\CPU\translate('product')}}</label>
                                            <select style="width: 100%"
                                                    class="js-example-responsive form-control"
                                                    name="product_id">
                                                @foreach(\App\Model\Product::active()->get() as $product)
                                                    <option value="{{$product['id']}}" {{$home_section['resource_id']==$product['id']?'selected':''}}>{{$product['name']}}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group" id="resource-category"
                                             style="display: {{$home_section['resource_type']=='category'?'block':'none'}}">
                                            <label for="name">{{\App\CPU\translate('category')}}</label>
                                            <select style="width: 100%"
                                                    class="js-example-responsive form-control"
                                                    name="category_id">
                                                @foreach(\App\CPU\CategoryManager::parents() as $category)
                                                    <option value="{{$category['id']}}" {{$home_section['resource_id']==$category['id']?'selected':''}}>{{$category['name']}}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group" id="resource-shop"
                                             style="display: {{$home_section['resource_type']=='shop'?'block':'none'}}">
                                            <label for="shop_id">{{\App\CPU\translate('shop')}}</label>
                                            <select style="width: 100%"
                                                    class="js-example-responsive form-control"
                                                    name="shop_id">
                                                @foreach(\App\Model\Shop::active()->get() as $shop)
                                                    <option value="{{$shop['id']}}" {{$home_section['resource_id']==$shop['id']?'selected':''}}>{{$shop['name']}}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group" id="resource-banner"
                                             style="display: {{$home_section['resource_type']=='banner'?'block':'none'}}">
                                            <label for="shop_id">{{\App\CPU\translate('Banner')}}</label>
                                            <select style="width: 100%"
                                                    class="js-example-responsive form-control"
                                                    name="banner_id">
                                                @foreach(\App\Model\Banner::where('published',1)->where('banner_type','Home Section')->get() as $banner)
                                                    <option value="{{$banner['id']}}" {{$home_section['resource_id']==$banner['id']?'selected':''}}>{{$banner['title']}}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group" id="resource-brand"
                                             style="display: {{$home_section['resource_type']=='brand'?'block':'none'}}">
                                            <label for="brand_id">{{\App\CPU\translate('brand')}}</label>
                                            <select style="width: 100%"
                                                    class="js-example-responsive form-control"
                                                    name="brand_id">
                                                @foreach(\App\Model\Brand::all() as $brand)
                                                    <option value="{{$brand['id']}}" {{$home_section['resource_id']==$brand['id']?'selected':''}}>{{$brand['name']}}</option>
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
                                                    src="{{asset('storage/home-section')}}/{{$home_section['photo']}}"
                                                    alt="home section image"/>
                                        </center>
                                    </div>

                                    <div class="col-md-12 mt-3">
                                        <button type="submit"
                                                class="btn btn-primary">{{ \App\CPU\translate('update')}}</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
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
    </script>
@endpush
