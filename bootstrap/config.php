<?php

// Ubuntu
return array(
    'apache' => '/var/www',
    'apache_sites' => '/etc/apache2/sites-available',

    'bind_folder' => '/etc/bind',
    'bind_hosts_folder' => '/etc/bind/hosts',

    // Every domain zone is added here:
    'named_conf' => '/etc/bind/named.conf.local',
);