# Lessthan12ms.com website  
This code allowed me to edit posts in pure markdown and push changes to the repo. After that the server will pull down
changes and update the website. Nice :)

Features:
- markdown based posts converted to HTML
- it generates static HTML files (as explained in
  [the post](https://lessthan12ms.com/nginx-to-cache-dynamic-phplaravel-pages-make-your-website-partly-static-and-reduce-response-time/))
- it uses Blade templates

## Locally
- `./develop serve tests/Infrastructure/HTTP/router.php` to start a built in web server
- `./develop test` to run tests