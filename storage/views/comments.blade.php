{{--
    var embedVars = {
        "disqusConfig":{"integration":"wordpress 3.0.17"},
        "disqusIdentifier":"567 https:\/\/lessthan12ms.com\/?p=567",
        "disqusShortname":"lessthan12ms",
        "disqusTitle":"Authorization and authentication in clean architecture",
        "disqusUrl":"https:\/\/lessthan12ms.com\/authorization-and-authentication-in-clean-architecture\/",
        "postId":"567"
        };
--}}

<div id="disqus_thread"></div>
<script>
    /**
     *  RECOMMENDED CONFIGURATION VARIABLES: EDIT AND UNCOMMENT THE SECTION BELOW TO INSERT DYNAMIC VALUES FROM YOUR PLATFORM OR CMS.
     *  LEARN WHY DEFINING THESE VARIABLES IS IMPORTANT: https://disqus.com/admin/universalcode/#configuration-variables
     */
    var disqus_config = function () {
        this.page.url = '{{$url}}';
        this.page.identifier = '{{$slug}}';
    };

    (function () { // DON'T EDIT BELOW THIS LINE
        var d = document, s = d.createElement('script');
        s.src = 'https://lessthan12ms.disqus.com/embed.js';
        s.setAttribute('data-timestamp', +new Date());
        (d.head || d.body).appendChild(s);
    })();
</script>
<noscript>
    Please enable JavaScript to view the <a href="https://disqus.com/?ref_noscript">comments powered by Disqus.</a>
</noscript>
