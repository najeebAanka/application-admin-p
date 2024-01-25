<!DOCTYPE html>
<html lang="en">

<head>
    <title> ALATAD - Coming Soon </title>
    <meta name="title" content="ALATAD - Coming Soon">
    <meta name="description" content="ALATAD - Coming Soon">
    <meta name="theme-color" content="#a68056">
    <meta name="robots" content="index, follow">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="googlebot" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <meta name="bingbot" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <meta property="og:locale" content="en_US">
    <meta property="og:type" content="website">
    <meta property="og:title" content="ALATAD - Coming Soon">
    <meta property="og:description" content="ALATAD - Coming Soon">
    <meta property="og:url" content="https://alatad.com/">
    <meta property="og:site_name" content="">
    <meta property="article:modified_time" content="2021-10-01T00:00:00+00:00">
    <meta property="og:image:width" content="200">
    <meta property="og:image:height" content="200">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="ALATAD - Coming Soon">
    <meta name="twitter:description" content="ALATAD - Coming Soon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{asset('soon/assets/style.css')}}">
    <link rel="stylesheet" href="{{asset('/assets/back-end')}}/css/toastr.css"/>
    <style>
    </style>
</head>

<body>

<div class="page">
    <div class="countdown-col col">
        <h3> ALATAD </h3>
        <div class="time middle">
        <span>
          <div id="days">12</div> Days
        </span>
            <span>
          <div id="hours">06</div> Hours
        </span>
            <span>
          <div id="minutes">35</div> Minutes
        </span>
            <span>
          <div id="seconds">54</div> Seconds
        </span>
        </div>
        <h3> Coming Soon </h3>

    </div>
    <div class="photo">
        <img src="{{asset('soon/img/horse.png')}}" alt="" srcset="" class="horse">
    </div>
</div>

<script src="{{asset('dist/assets/scripts/jquery-3.6.0.min.js')}}"></script><!-- Slick ( Slide )-->
<script src="{{asset('soon/assets/script.js')}}"></script>
<script src={{asset("assets/back-end/js/toastr.js")}}></script>
{!! Toastr::message() !!}
</body>

</html>