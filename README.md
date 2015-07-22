# domain
Generate bind and apache files easy to host and create virtual hosts (Set for Ubuntu)

# Command
sudo php domain.php add:domain example.com 192.168.1.1

What it does:

(Bind)
It generates folder and host file in /etc/bind/hosts (including nameserver ns1.example.com with the specified IP)
Modifies /etc/bind/named.conf.local and adds domain zone

(Apache)
Generates folder /var/www/example.com/public
Generates virtual host file in /etc/apache/sites-available

Restarts Bind and Apache at the end

Note: This was done so it works on Ubuntu, config files and commands need to be changed if you want it to work on different linux distros.

The template for each file can be found in /boostrap/config.php
