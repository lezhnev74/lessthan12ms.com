@extends('layout')
@section('content')
    <div class="container">
        <div class="row">
            <div class="col-4">
                @include('menu')
            </div>
            <div class="col-8">
                @yield('post')
                <br>
                <br>
                <br>
                <br>
                <a href="/atom.xml">ATOM feed</a> |
                <a href="https://github.com/lezhnev74/lessthan12ms.com">Website source code</a>
                <br>
                <br>
            </div>
        </div>
    </div>
@endsection