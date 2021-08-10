<?php

// Ubuntu
return array(
    'apache' => '/var/www',
    'apache_sites' => '/etc/apache2/sites-available',

    'nginx' => '/var/www',
    'nginx_sites' => '/etc/nginx/sites-enabled',

    'bind_folder' => '/etc/bind',
    'bind_hosts_folder' => '/etc/bind/hosts',

    // Every domain zone is added here:
    'named_conf' => '/etc/bind/named.conf.local',
);