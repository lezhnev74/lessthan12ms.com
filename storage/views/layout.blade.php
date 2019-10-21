<!doctype html>
<html class="no-js" lang="">

<head>
    <meta charset="utf-8">
    <title>@yield('pageTitle', $pageTitle)</title>
    <link rel="stylesheet" href="/styles.css">
    <link rel="stylesheet" href="/simple-grid.css">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicons/favicon-16x16.png">
    <meta name="theme-color" content="#ffffff">
    <link href="https://fonts.googleapis.com/css?family=Merriweather:300,400,700&display=swap" rel="stylesheet">
</head>

<body>
@include('analytics')

<!--[if IE]>
<p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience and security.</p>
<![endif]-->

<div class="content">@yield('content')</div>

@yield('footer')
</body>

</html>