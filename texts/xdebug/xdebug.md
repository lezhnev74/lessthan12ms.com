slug: docker-php-xdebug-phpstorm-good-developer-experience 
date: 2019-07-09 14:52:16  
# Docker + PHP + Xdebug + PHPStorm = Good Developer Experience
Nowadays we move from using the local environment or virtual machines to Docker-based development. It is much easier to spin up a docker container
rather than starting a new virtual machine (ie Vagrant). Docker also helps you keep your system dependencies under
control. You can have any version of any service in the container which won\'t affect each other.

In PHP
development flow, Docker is my everyday tool. It works just fine. However, I wish MacOS performance was better for
docker engine. Daily work with PHP includes debugging, yeah. A lot of debugging. And Xdebug is one of the most known
tools for PHP debugging.

But how to use it inside of the docker container? Down below I will describe my normal
daily setup:
- Docker container with PHP+XDebug installed
- PHPStorm IDE on my host machine
- Debugging PHP
scripts

## Configure docker container
I am using `docker-compose` to manage my containers. So let\'s start
with the `docker-compose.yml` that I put in the root of my PHP project:
```
version: "3.7"
services: 
    php7.3:
        container_name: myapp # pick a name for the container
        build:
          context: docker/php # here is the Docker file for this container
        init: true # allows sending signals to the PHP process, very helpful to stop process in the middle with Ctrl+C or Cmd+C
        volumes:
          - .:/var/www # map the current folder to the container"s filesystem (so PHP scripts are injected to the container)
```
This file describes the `php` service which is based on a Dockerfile located at `docker/php/Dockerfile`. The file
contains all the configuration you need for your PHP service - dependencies, libraries, packages, etc. In my simple case
the Dockerfile looks like this:
```
FROM php:7.3

WORKDIR /var/www

# Install dependencies
RUN apt-get update

# Install extensions
RUN docker-php-ext-install mbstring bcmath

# XDEBUG
RUN pecl install xdebug
RUN docker-php-ext-enable xdebug
# This needs in order to run xdebug from PhpStorm
ENV PHP_IDE_CONFIG 'serverName=DockerApp'

ADD php.ini /usr/local/etc/php
```

I'd like to point at two things:
1. Docker file installs Xdebug extension with `pecl` command
2. It imports our special
`php.ini` to the container filesystem. We can tweak our PHP settings whatever we like for our development environment.
It is important as we will add special Xdebug settings to the `php.ini` file.

Now let's see how it all works together. We can run this command: `docker-compose run --rm php7.3 php -v` which will
start a container for `php7.3` service described in `docker-compose.yml`, run command `php -v` and stop the container.

It should build up the container and output something like this:
```
PHP 7.3.6 (cli) (built: Jul 3 2019 20:46:48) ( NTS )
Copyright (c) 1997-2018 The PHP Group
Zend Engine v3.3.6, Copyright (c) 1998-2018 Zend Technologies
with Xdebug v2.7.2, Copyright (c) 2002-2019, by Derick Rethans
```

If so, good! It works as expected. Now let's
tweak our `php.ini` file a bit and configure XDebug extension. Php ini file allows you to configure xdebug settings
([as explained here](https://xdebug.org/docs/all_settings)).

For the time being, add these lines at the end of
the `php.ini` file:
```ini
[xdebug]
; allow xdebug connecting to the listening client (in our case, PHPStorm)
xdebug.remote_enable=1
; this special hostname means our host machine, it allows you to address the host machine from inside of the container
xdebug.remote_host=docker.for.mac.localhost
; define a port for the PHPStorm to listen (see below)
xdebug.remote_port=9001
; log what is happening to the file (just in case something goes wrong)
xdebug.remote_log=/var/log/xdebug.log
; Normally you need to use a specific HTTP GET/POST variable to start remote debugging (see Remote Debugging). When this setting is set to 1, Xdebug will always attempt to start a remote debugging session and try to connect to a client, even if the GET/POST/COOKIE variable was not present.
xdebug.remote_autostart=1
```

## Configure PhpStorm
PHPStorm acts as a debugging client. It
accepts connections from xDebug extension and allows you to set breaking point, evaluate variables and other good
stuff.

First of all, you need to configure the xDebug: 
1. Open your PHPStorm settings and navigate here:
`Preferences | Languages & Frameworks | PHP | Debug`
2. Set the same port as in php.ini file in the `XDebug / Debug
port`.
3. Navigate to `Preferences | Languages & Frameworks | PHP | Servers`.
4. Add a new PHP server with these
settings:
- Name: `DockerApp` (the same as in Dockerfile)
- Host: localhost
- Port: 80
- Debugger:
Xdebug
- check `Use path mappings` and map your local project folder to the server path `/var/www`

## Debugging PHP scripts
Now that we have everything in place we can actually set breakpoints and debug the execution
step-by-step: 
1. put a breakpoint where you need it in the PHPStorm.
2. start listening for xdebug connection in
phpstorm (<img src="https://lessthan12ms.com/wp-content/uploads/2019/07/2019-07-09_14-48-11.jpg" alt="" width="20"
class="" />).
3. run your PHP scripts normally (via phpunit or HTTP request).
4. see the Debug panel pops up in
PHPStorm.

<img src="https://lessthan12ms.com/wp-content/uploads/2019/07/2019-07-09_14-40-45.jpg" alt="" width="1024" height="404" class="aligncenter size-large wp-image-1026" />