@include('header')
<br>
<br>
<ul class="menu">
    @foreach($postLinks as $link)
        <li>
            <a href="{{$link['url']}}">{!!  $link['title']!!}</a>
            <br>
            <span class="date">{{$link['date']}}</span>
        </li>
    @endforeach
</ul>
@include('footer')