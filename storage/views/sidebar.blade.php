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
                <a href="/atom.xml">ATOM feed</a>
            </div>
        </div>
    </div>
@endsection