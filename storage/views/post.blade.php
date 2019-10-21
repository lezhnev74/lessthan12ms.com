@extends('sidebar')
@section('pageTitle', $title)
@section('post')
    <div class="post">
        <p class="date">{{$date}}</p>
        {!!$body!!}
    </div>
    <br><br>
    @include('comments')
@endsection
@section('footer')
    <link rel="stylesheet" href="/js/highlight/styles/idea.css">
    <script src="/js/highlight/highlight.pack.js"></script>
    <script>hljs.initHighlightingOnLoad();</script>
@append