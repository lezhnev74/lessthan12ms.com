@if(config('joob_lookup_banner'))
    <div style="max-width:500px; background-color: #f8f9fa; border: 2px solid #007bff; border-radius: 8px; padding: 15px; margin: 20px 0; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        @if(str_contains($_SERVER['QUERY_STRING'], 'thank-you'))
            <div style="background-color: yellow; color: black; padding: 10px; border-radius: 4px; margin-bottom: 15px;">
                Thank you for sharing the job opportunity!
            </div>
        @endif

        <b style="color: #0056b3; font-size: 1.2em;">Is your team hiring?</b><br>
        I am looking for a job in PHP/Go area (remote, b2b)<br>
        My <a href="https://www.linkedin.com/in/dmitriy-lezhnev/" style="color: #0056b3; text-decoration: underline; font-weight: bold;">LinkedIn</a><br>
        <form method="post" action="/job.php">
            <input type="hidden" name="_origin" value="{{$_SERVER['REQUEST_URI']}}">
            <input type="text" required name="job_url" placeholder="A link to your vacancies page"
                   style="width: 90%; padding: 10px; margin: 10px 0; border: 1px solid #007bff; border-radius: 4px; font-size: 14px; outline: none; transition: border-color 0.3s ease;"
            />
            <input type="submit" value="Send"
                   style="background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; transition: background 0.3s ease;"
            />
        </form>
    </div>
@endif