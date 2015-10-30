# Domain
Generate bind and apache files easy to host and create virtual hosts (Set for Ubuntu)

# Command
```
  sudo php domain.php add:domain example.com 192.168.1.1
```

### Now nameservers can be added instead of creating nameservers for the actual domain
```
  sudo php domain.php add:domain example.com 192.168.1.1 ns1.examplenameserver.com,ns2.examplenameserver.com
```
You can add as manny nameservers as needed.

### What it does:

(Bind)
It generates folder and host file in /etc/bind/hosts (including nameserver ns1.example.com with the specified IP)
Modifies /etc/bind/named.conf.local and adds domain zone

(Apache)
Generates folder /var/www/example.com/public
Generates virtual host file in /etc/apache/sites-available

Restarts Bind and Apache at the end

Note: This was done so it works on Ubuntu, config files and commands need to be changed if you want it to work on different linux distros.

The template for each file can be found in /command/templates
