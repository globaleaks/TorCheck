<?php
error_reporting(E_ALL);

class TorCheck
{

    public $ip_list_exit_file = "/tmp/Tor_ip_list_EXIT.csv";
    public $ip_list_exit_local = "/var/lib/tor/cached-consensus";
    public $ip_list_exit_url = "http://torstatus.blutmagie.de/ip_list_exit.php/Tor_ip_list_EXIT.csv";
    public $expiry = 3600;
    public $fetchmethod = "local";
    public $exit_list = array();

    function __construct($method) {
        if ( $method) {
        $this->fetchmethod = $method;
        }

        if ( $this->fetchmethod == "localsh" ) {
            system("cat ".$this->ip_list_exit_local."| grep \"^s Exit\" -B1 | grep \"^r \" | cut -d ' ' -f7", $ret);
            $fp = fopen($this->ip_list_exit_file, 'w');
            fwrite($fp, $ret);
            fclose($fp);

        } else {
        if (
           !(file_exists($this->ip_list_exit_file)) ||
            (filemtime($this->ip_list_exit_file) - time()) > $this->expiry
            ) {
            file_put_contents($this->ip_list_exit_file, file_get_contents($this->ip_list_exit_url));
        }

        $exit_list = file($this->ip_list_exit_file);

        foreach ($exit_list as $ip) {
            array_push($this->exit_list, trim($ip));
        }
    }
    }

    function check($ip) {
        if(in_array($ip, $this->exit_list)) {
            return true;
        }
        else {
            return false;
        }
    }

}

# $ip = "10.10.1.1";
# Instantiate with local for local file retrieval
# Default method
$tor = new TorCheck("local");

$ip = $_SERVER['REMOTE_ADDR'];

if($_GET['callback']) {
    $result = array("Tor" => $tor->check($ip));
    echo $_GET['callback'] . '(' . json_encode($result) . ');';
}


/*
if($tor->check($ip)) {
    echo "You are using Tor\n";
} else {
    if($ip == "127.0.0.1") {
        echo "You are conneting through localhost\n";
    } else {
    echo "You are not using Tor\n";
    }
}
*/


?>
