# vgc-api
Video Game Collector API with Symfony 3.0 (NEEDS TO BE UPDATED)

This is an API that allows you to manage your collection of video games.  
Video games data are provided by [IGDB API](https://www.igdb.com/api).  
An Angular 2 client for this API can be found here: [vgc-client](https://github.com/BenjaminCamus/vgc-client)

## Installation

##### Get Source Code
```
git clone https://github.com/BenjaminCamus/vgc-api.git
cd vgc-api
```

##### Install Vendors & Edit Parameters
```
composer install
```

##### Generate SSH Keys
```
openssl genrsa -out var/jwt/private.pem -aes256 4096
openssl rsa -pubout -in var/jwt/private.pem -out var/jwt/public.pem
```

##### Create Database
```
php bin/console doctrine:database:create
php bin/console doctrine:schema:update --force
```

##### Create Super Admin User
```
php bin/console fos:user:create adminUser admin@user.com adminPassword --super-admin
```

##### Clear Cache
```
php bin/console cache:clear --env=dev
php bin/console cache:clear --env=prod
```

##### Nginx
```
server {
    server_name api.vgc.local admin.vgc.local;
    root /var/www/vgc-api/web;

    location / {
        # try to serve file directly, fallback to app.php
        try_files $uri /app.php$is_args$args;
    }
    # DEV
    # This rule should only be placed on your development environment
    # In production, don't include this and don't deploy app_dev.php or config.php
    location ~ ^/(app_dev|config)\.php(/|$) {
        fastcgi_pass unix:/var/run/php5-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        # When you are using symlinks to link the document root to the
        # current version of your application, you should pass the real
        # application path instead of the path to the symlink to PHP
        # FPM.
        # Otherwise, PHP's OPcache may not properly detect changes to
        # your PHP files (see https://github.com/zendtech/ZendOptimizerPlus/issues/126
        # for more information).
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
    }
    # PROD
    location ~ ^/app\.php(/|$) {
        fastcgi_pass unix:/var/run/php5-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        # When you are using symlinks to link the document root to the
        # current version of your application, you should pass the real
        # application path instead of the path to the symlink to PHP
        # FPM.
        # Otherwise, PHP's OPcache may not properly detect changes to
        # your PHP files (see https://github.com/zendtech/ZendOptimizerPlus/issues/126
        # for more information).
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        # Prevents URIs that include the front controller. This will 404:
        # http://domain.tld/app.php/some-path
        # Remove the internal directive to allow URIs like this
        internal;
    }

    # return 404 for all other php files not matching the front controller
    # this prevents access to other php files you don't want to be accessible.
    location ~ \.php$ {
        return 404;
    }

    error_log /var/www/vgc_api_error.log;
    access_log /var/www/vgc_api_access.log;
}
```

##### Virtual Host
```
<VirtualHost *:80>
    ServerName api.vgc.local

    DocumentRoot /path/to/vgc-api
    <Directory /path/to/vgc-api>
        AllowOverride None
        Order Allow,Deny
        Allow from All

        <IfModule mod_rewrite.c>
            Options -MultiViews
            RewriteEngine On
            RewriteEngine On
            RewriteCond %{HTTP:Authorization} ^(.*)
            RewriteRule .* - [e=HTTP_AUTHORIZATION:%1]
        </IfModule>
    </Directory>

    # uncomment the following lines if you install assets as symlinks
    # or run into problems when compiling LESS/Sass/CoffeeScript assets
    # <Directory /var/www/project>
    #     Options FollowSymlinks
    # </Directory>

    # optionally disable the RewriteEngine for the asset directories
    # which will allow apache to simply reply with a 404 when files are
    # not found instead of passing the request into the full symfony stack
    <Directory c:/wamp64/www/camusme/>
        <IfModule mod_rewrite.c>
            RewriteEngine Off
        </IfModule>
    </Directory>
    ErrorLog logs/project_error.log
    CustomLog logs/project_access.log combined
</VirtualHost>
```