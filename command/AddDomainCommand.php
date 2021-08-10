<?php

namespace Vortex\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class AddDomainCommand extends Command
{

    public function configure()
    {
        $this->setName('add:domain')
            ->setDescription('Add a domain name')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the domain')
            ->addArgument('ip', InputArgument::REQUIRED, 'The IP of the domain')
            ->addArgument('nameservers', InputArgument::OPTIONAL, 'The nameservers')
            ->addOption('--with-nginx', null, InputOption::VALUE_NONE, 'Add nginx server')
            ->addOption('--with-apache', null, InputOption::VALUE_NONE, 'Add apache2 server');

    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $data = null;
        // load the variables from config
        $config = require __DIR__ . '/../bootstrap/config.php';

        $name = $input->getArgument('name');
        $ip = $input->getArgument('ip');
        $nameservers = $input->getArgument('nameservers');

//        echo $name.' - '. $ip . ' - '.$nameservers;

        // validate the IP
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return $output->writeln('<bg=red>Not a valid ip: ' . $ip . '</bg=red>');
        }

        // Load the templates
        $zone = file_get_contents(__DIR__ . '/templates/zone.txt');
        if (!$nameservers) {
            $bind = file_get_contents(__DIR__ . '/templates/bind.txt');
        } else {
            $bind = file_get_contents(__DIR__ . '/templates/bind2.txt');
            $ns = explode(',', $nameservers);

            $search[] = '{ns}';
            $replace[] = $ns[0];

            foreach ($ns as $key) {
                $nameserver = trim($key);
                $data .= $name . '.    IN  NS  ' . $nameserver . '.' . "\n";
            }

            $search[] = '{nameservers}';
            $replace[] = $data;
        }

        $apache = file_get_contents(__DIR__ . '/templates/apache.txt');
        $nginx = file_get_contents(__DIR__ . '/templates/nginx.txt');

        $search[] = '{domain}';
        $replace[] = $name;

        $search[] = '{IP}';
        $replace[] = $ip;

        $zone = str_replace($search, $replace, $zone);
        $bind = str_replace($search, $replace, $bind);
        $apache = str_replace($search, $replace, $apache);
        $nginx = str_replace($search, $replace, $nginx);

        // The bind host file
        $bindHostFile = $config['bind_hosts_folder'] . '/' . $name . '.hosts';
        // named.conf.local file
        $namedConf = $config['named_conf'];


        // check if the hosts folder is not found
        if (!is_dir($config['bind_hosts_folder'])) {
            mkdir($config['bind_hosts_folder'], 0755, true);
            $output->writeln('<bg=red>Created folder: '.$config['bind_hosts_folder'].'</bg=red>');
        }

        if (!file_exists($bindHostFile)) {
            if (!file_put_contents($bindHostFile, $bind)) {
                $output->writeln('<bg=red>Could not put contents to file: ' . $bindHostFile . '</bg=red>');
            } else {
                $output->writeln('<bg=red>Bind host file added: ' . $bindHostFile . '</bg=red>');
            }

            if (!file_put_contents($namedConf, $zone, FILE_APPEND | LOCK_EX)) {
                $output->writeln('<bg=red>Could not append zone file!</bg=red>');
            } else {
                $output->writeln("<bg=red>Zone added to file {$namedConf}</bg=red>");
            }
        } else {
            $output->writeln('<bg=red>Domain file already exists!</bg=red>');
        }


        // Apache and bind can be restarted > Copy Paste
        $process = new Process('service named restart');
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }
        echo $process->getOutput();

        if ($input->getOption('with-apache')) {
            if ($this->addVhostApache($output, $config, $name, $apache)) {
                $this->restartApache($name);
            } else {
                $output->writeln('<bg=red>Could not add virtual host files!</bg=red>');
            }
        }

        if ($input->getOption('with-nginx')) {
            if ($this->addVhostNginx($output, $config, $name, $nginx)) {
                $this->restartNginx();
            } else {
                $output->writeln('<bg=red>Could not add virtual host files!</bg=red>');
            }
        }

        return 0;
    }

    private function addVhostNginx($output, $config, $name, $nginx)
    {
        if (!is_dir($config['nginx_sites'])) {
            $output->writeln('<bg=red>Could not find folder: ' . $config['nginx_sites'] . '</bg=red>');
            return false;
        } else {
            if (file_put_contents($config['nginx_sites'] . '/' . $name . '.conf', $nginx)) {
                $output->writeln('<bg=red>Nginx Virtual Host created.</bg=red>');
            }

            if (!is_dir($config['nginx'] . '/' . $name . '/public')) {
                mkdir($config['nginx'] . '/' . $name . '/public', 0755, true);
                $output->writeln('<bg=red>Created the domain folders.</bg=red>');
            }
        }

        return true;
    }

    private function addVhostApache($output, $config, $name, $apache)
    {
        // Add apache vhost
        if (!is_dir($config['apache_sites'])) {
            $output->writeln('<bg=red>Could not find folder: ' . $config['apache_sites'] . '</bg=red>');
            return false;
        } else {
            if (file_put_contents($config['apache_sites'] . '/' . $name . '.conf', $apache)) {
                $output->writeln('<bg=red>Apache Virtual Host created.</bg=red>');
            }

            if (!is_dir($config['apache'] . '/' . $name . '/public')) {
                mkdir($config['apache'] . '/' . $name . '/public', 0755, true);
            }
        }

        return true;
    }

    private function restartNginx()
    {
        $process = new Process('service nginx restart');
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        echo $process->getOutput();
    }

    private function restartApache($name)
    {
        $process = new Process('a2ensite ' . $name);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        echo $process->getOutput();

        $process = new Process('service apache2 restart');
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        echo $process->getOutput();
    }
}

