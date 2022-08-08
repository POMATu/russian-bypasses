<?php


$SNG = array("RU","AZ","AM","BY","KZ","KG","MD","TJ","TM","UZ"); // contries that most likely need russian ip (banking and etc)

$LOCAL = array(
"0.0.0.0/8",
"10.0.0.0/8",
"100.64.0.0/10",
"127.0.0.0/8",
"169.254.0.0/16",
"172.16.0.0/12",
"192.0.0.0/24",
"192.0.2.0/24",
"192.88.99.0/24",
"192.168.0.0/16",
"198.18.0.0/15",
"198.51.100.0/24",
"203.0.113.0/24",
"224.0.0.0/4",
"233.252.0.0/24",
"240.0.0.0/4",
"255.255.255.255/32"
	); // see https://en.wikipedia.org/wiki/Reserved_IP_addresses


$port = 8081; // port to bind locally
$upstreamhost = "some.ipv4.or.ipv6"; // your upstream socks server that you use to bypass shit (or even tor)
$upstreamport = 1080; // port of upstream socks server (or tor: 9050)

$header = '#!/bin/3proxy
#daemon
#pidfile /var/run/3proxy.pid
#chroot /usr/local/3proxy proxy proxy
maxconn 65535
';


if (file_exists("config.php")) {
	include("config.php"); // your config overrides added to .gitignore
}

$serviceport = $port+1; // needed for self-wrap because 3proxy doesnt support direct command


echo $header;
echo "\n";

echo <<<END
flush
auth iponly
allow *;
socks -p$serviceport -i127.0.0.1;
flush

auth iponly
END;

echo "\n";

foreach ($LOCAL as $local) {
	echo "allow * * ".$local."\n";
}

echo "\n";

$csv = file_get_contents("IP2LOCATION-LITE-DB1.CSV"); // https://download.ip2location.com/lite/
foreach (explode("\n",$csv) as $tline) {
	 $line = trim($tline);
	 $arr = explode(",",$line);
	 foreach ($arr as $key => $value) {
	 	$arr[$key] = trim(trim($value,'"'));
	 }

     foreach ($SNG as $sng) {
		 	if (isset($arr[2]) && $arr[2] == $sng) {
		 		echo "allow * * ". long2ip($arr[0])."-".long2ip($arr[1])."\n";
		 	}
	 }
}
echo "\n";

echo <<<END
parent 1000 socks5 127.0.0.1 $serviceport
allow *
parent 1000 socks5 $upstreamhost $upstreamport
socks -p$port

END;

