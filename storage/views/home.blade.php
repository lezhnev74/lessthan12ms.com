@extends('sidebar')
@section('pageTitle', $title)
@section('post')
    <div class="post">
        Welcome to my blog.

        <br><br><br>
        <img src="https://media.giphy.com/media/13HgwGsXF0aiGY/source.gif" />
        <br><br><br>

        <blockquote>
            There is no way to avoid or replace the hard work of thinking. When you write a test you are thinking about how to specify behavior. When you make the test pass you are thinking about how to implement that specification. When you refactor you are thinking about how to communicate both the specification and implementation to others.
            <br><br>
            You cannot replace any of these thought processes with tools. You cannot generate the code from tests, or the tests from code, because that would cause you to abandon a critical thought process.   And may God help you if you use a tool to do the refactoring for you.
            <br><br>
            The purpose of a tool is to enable and facilitate thought; not to replace it.
        </blockquote>
        <a href="https://twitter.com/unclebobmartin/status/1707725163498799404">R. Martin</a>

    </div>
@endsection