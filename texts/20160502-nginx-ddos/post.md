- slug:protect-your-site-from-ddos-with-free-built-in-nginx-feature-ngx-http-limit-req-module
- date:May 2, 2016 05:56
# Protect your site from ddos with free built-in nginx feature ngx_http_limit_req_module
<p>When you expect (or not) your website to be a target of malicious traffic – make some free efforts to protect from it. Use nginx option to limit connections allowed per IP. This is not the only thing you could do but this is the least thing you should do.</p>
<p>Option `<a href="http://nginx.org/en/docs/http/ngx_http_limit_req_module.html#limit_req_zone">ngx_http_limit_req_module</a>` lets you to set limitations of simultaneous connection per IP. </p>
<p><!--more--></p>
<div class="code-embed-wrapper"> 

```nginx
http {
    # define a rule (zone) which should be applied to every IP ($binary_remote_addr)
    limit_req_zone $binary_remote_addr zone=ZONE_NAME:10m rate=2r/s;

    ...

    server {

        ...

        location /search/ {
            # apply rule (zone) to this location
            # also set a safe buffer (burst) for spikes in connections which will queue requests until full
            limit_req zone=ZONE_NAME burst=5;
            ...
        }
        ...
    }
}
```

<div class="code-embed-infos"> <span class="code-embed-name">Limit connections to search page</span> </div> </div>
<p>Also use logging to have a picture of how often this rule is triggered. If your users feel that they see 503 error too often – increase the value and make informative decision about it.</p>
<p>Also make a nice image for the 503 situation. And set nginx to show it so your users will get better UX even when faced with 503 page.</p>
<div class="code-embed-wrapper"> 

```nginx
server {
...
    
    error_page 503 @503;
    location @503 {
       rewrite ^(.*)$ /503.html break;
    }

}
```

<div class="code-embed-infos"> <span class="code-embed-name">set 503 custom page</span> </div> </div>
<p>So User will see nice page instead of standard nginx page:</p>
<figure id="attachment_120" style="width: 591px" class="wp-caption alignnone"><img class="size-full wp-image-120" src="http://lessthan12ms.com/wp-content/uploads/2016/04/503.png" alt="503 standard page" width="591" height="421" /><figcaption class="wp-caption-text">503 standard page</figcaption></figure>
<p> </p>
