# Domain
Generate bind, apache & nginx files easy to host and create virtual hosts (Paths are for Ubuntu)

### First run a composer install to download dependencies
```
composer install
```

### Command without vhost
```
sudo php domain/bin/domain add:domain example.com 192.168.1.1
```

### Command with vhost
```
sudo php domain/bin/domain add:domain example.com 192.168.1.1 --with-nginx
```

```
sudo php domain/bin/domain add:domain example.com 192.168.1.1 --with-apache
```

### Now nameservers can be added instead of creating nameservers for the actual domain
```
sudo php domain/bin/domain add:domain example.com 192.168.1.1 ns1.examplenameserver.com,ns2.examplenameserver.com
```
#### add nginx or apache by using --with-nginx or --with-apache
```
sudo php domain/bin/domain add:domain example.com 192.168.1.1 ns1.examplenameserver.com,ns2.examplenameserver.com --with-nginx
```

You can add as manny nameservers as needed.

### What it does:

(Bind)
It generates folder and host file in /etc/bind/hosts (including nameserver ns1.example.com with the specified IP)
Modifies /etc/bind/named.conf.local and adds domain zone

(Apache & Nginx)
Generates folder /var/www/example.com/public  
Generates virtual host file in /etc/apache/sites-available  
Generates virtual host file in /etc/nginx/sites-enabled  

Restarts Bind at the end and also nginx or apache2  

### Note: This was done so it works on Ubuntu, config files and commands need to be changed if you want it to work on different linux distros.  
### Another Note: You would need to change folder / files ownership using chown
The template for each file can be found in /command/templates  
