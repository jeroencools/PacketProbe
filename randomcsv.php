<?php
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="example.csv"');

// Column headers
$headers = ['No.','Time','Source','Destination','Protocol','Length','Info'];
echo '"' . implode('","', $headers) . '"' . "\r\n";

// Helper functions
function rand_ip($private = true) {
    if ($private) {
        return "10.30.0." . rand(2, 254);
    } else {
        return rand(31,223) . '.' . rand(0,255) . '.' . rand(0,255) . '.' . rand(1,254);
    }
}
function rand_mac() {
    return strtoupper(implode(':', array_map(fn() => str_pad(dechex(rand(0,255)),2,'0',STR_PAD_LEFT), range(1,6))));
}
function inc_time(&$base, &$micros) {
    $base += rand(0,1); // 0 or 1 second
    $micros += rand(10000, 50000); // 10-50 ms
    if ($micros > 999999) {
        $base += 1;
        $micros -= 1000000;
    }
    return date('Y-m-d H:i:s', $base) . ',' . str_pad($micros, 6, '0', STR_PAD_LEFT);
}

// Data pools
$dns_domains = [
    'api.github.com', 'login.microsoftonline.com', 'example.com', 'microsoft.com', 'google.com', 'facebook.com',
    'yahoo.com', 'amazon.com', 'cloudflare.com', 'github.com', 'apple.com', 'wikipedia.org', 'reddit.com',
    'office.com', 'zoom.us', 'dropbox.com', 'netflix.com', 'spotify.com', 'bbc.co.uk', 'cnn.com'
];
$http_paths = ['/index.html', '/api/data', '/login', '/dashboard', '/favicon.ico', '/style.css', '/script.js', '/home', '/about', '/contact'];
$tcp_flags = ['[SYN]', '[SYN, ACK]', '[ACK]', '[PSH, ACK]', '[FIN, ACK]', '[RST]'];
$icmp_types = ['Echo (ping) request', 'Echo (ping) reply', 'Destination unreachable', 'Time exceeded'];
$udp_services = [
    ['port'=>53, 'desc'=>'DNS'],
    ['port'=>67, 'desc'=>'DHCP'],
    ['port'=>68, 'desc'=>'DHCP'],
    ['port'=>123, 'desc'=>'NTP'],
    ['port'=>1900, 'desc'=>'SSDP'],
    ['port'=>5353, 'desc'=>'mDNS'],
    ['port'=>5000, 'desc'=>'Custom'],
];
$tcp_services = [
    ['port'=>80, 'desc'=>'HTTP'],
    ['port'=>443, 'desc'=>'HTTPS'],
    ['port'=>22, 'desc'=>'SSH'],
    ['port'=>25, 'desc'=>'SMTP'],
    ['port'=>110, 'desc'=>'POP3'],
    ['port'=>143, 'desc'=>'IMAP'],
    ['port'=>21, 'desc'=>'FTP'],
    ['port'=>3306, 'desc'=>'MySQL'],
    ['port'=>8080, 'desc'=>'HTTP-alt'],
];

// Time setup
$base_time = strtotime('2025-07-14 19:36:36');
$micros = 694666;
$line = 1;
$rows = [];

// 1. DHCP DORA for 5 clients
for ($c = 2; $c <= 6; $c++) {
    $client = "10.30.0.$c";
    $server = "10.30.0.1";
    $yiaddr = "10.30.0." . (100+$c);
    // Discover (broadcast)
    $rows[] = [$line++, inc_time($base_time, $micros), $client, '255.255.255.255', 'DHCP', 342, "DHCP Discover from $client"];
    // Offer (server to client)
    $rows[] = [$line++, inc_time($base_time, $micros), $server, $client, 'DHCP', 342, "DHCP Offer to $client yiaddr=$yiaddr"];
    // Request (broadcast)
    $rows[] = [$line++, inc_time($base_time, $micros), $client, '255.255.255.255', 'DHCP', 342, "DHCP Request from $client for $yiaddr"];
    // ACK (server to client)
    $rows[] = [$line++, inc_time($base_time, $micros), $server, $client, 'DHCP', 342, "DHCP ACK to $client yiaddr=$yiaddr"];
}

// 2. DNS queries/responses for 5 clients
foreach (range(2, 6) as $c) {
    $client = "10.30.0.$c";
    $server = "10.30.0.1";
    $domain = $dns_domains[array_rand($dns_domains)];
    $query_id = dechex(rand(1000,9999));
    $rows[] = [$line++, inc_time($base_time, $micros), $client, $server, 'DNS', 74, "Standard query 0x$query_id A $domain"];
    $rows[] = [$line++, inc_time($base_time, $micros), $server, $client, 'DNS', 90, "Standard query response 0x$query_id A $domain A " . rand_ip(false)];
}

// 3. TCP 3-way handshake and HTTP request/response
$client = "10.30.0.10";
$server = rand_ip(false);
$client_port = rand(40000, 60000);
$server_port = 80;
$rows[] = [$line++, inc_time($base_time, $micros), $client, $server, 'TCP', 66, "$client_port  >  $server_port [SYN] Seq=0 Win=65535 Len=0 MSS=1460"];
$rows[] = [$line++, inc_time($base_time, $micros), $server, $client, 'TCP', 66, "$server_port  >  $client_port [SYN, ACK] Seq=0 Ack=1 Win=65535 Len=0 MSS=1420"];
$rows[] = [$line++, inc_time($base_time, $micros), $client, $server, 'TCP', 54, "$client_port  >  $server_port [ACK] Seq=1 Ack=1 Win=65280 Len=0"];
$rows[] = [$line++, inc_time($base_time, $micros), $client, $server, 'HTTP', 512, "GET /index.html HTTP/1.1 Host: " . $dns_domains[array_rand($dns_domains)]];
$rows[] = [$line++, inc_time($base_time, $micros), $server, $client, 'HTTP', 1024, "HTTP/1.1 200 OK Content-Type: text/html"];

// 4. UDP traffic (DNS, NTP, mDNS, SSDP)
foreach ($udp_services as $svc) {
    $src = rand_ip(true);
    $dst = rand_ip(true);
    if ($svc['desc'] === 'DNS') {
        $dst = "10.30.0.1";
    }
    $rows[] = [$line++, inc_time($base_time, $micros), $src, $dst, 'UDP', rand(60,120), $svc['desc'] . " traffic $src > $dst port " . $svc['port']];
}

// 5. ICMP echo request/reply
$src = rand_ip(true);
$dst = rand_ip(false);
$rows[] = [$line++, inc_time($base_time, $micros), $src, $dst, 'ICMP', 98, "Echo (ping) request id=" . rand(1000,9999) . " seq=1"];
$rows[] = [$line++, inc_time($base_time, $micros), $dst, $src, 'ICMP', 98, "Echo (ping) reply id=" . rand(1000,9999) . " seq=1"];

// 6. ARP request/reply
$src = rand_ip(true);
$dst = rand_ip(true);
$rows[] = [$line++, inc_time($base_time, $micros), $src, $dst, 'ARP', 42, "Who has $dst? Tell $src"];
$rows[] = [$line++, inc_time($base_time, $micros), $dst, $src, 'ARP', 42, "Reply $dst is-at " . rand_mac()];

// 7. TLS handshake/application data
$client = "10.30.0.20";
$server = rand_ip(false);
$rows[] = [$line++, inc_time($base_time, $micros), $client, $server, 'TCP', 66, rand(40000,60000) . "  >  443 [SYN] Seq=0 Win=65535 Len=0 MSS=1460"];
$rows[] = [$line++, inc_time($base_time, $micros), $server, $client, 'TCP', 66, "443  >  " . rand(40000,60000) . " [SYN, ACK] Seq=0 Ack=1 Win=65535 Len=0 MSS=1420"];
$rows[] = [$line++, inc_time($base_time, $micros), $client, $server, 'TCP', 54, rand(40000,60000) . "  >  443 [ACK] Seq=1 Ack=1 Win=65280 Len=0"];
$rows[] = [$line++, inc_time($base_time, $micros), $client, $server, 'TLSv1.3', 571, "Client Hello (SNI=" . $dns_domains[array_rand($dns_domains)] . ")"];
$rows[] = [$line++, inc_time($base_time, $micros), $server, $client, 'TLSv1.3', 2894, "Server Hello, Change Cipher Spec, Application Data"];
$rows[] = [$line++, inc_time($base_time, $micros), $server, $client, 'TLSv1.3', 709, "Application Data, Application Data, Application Data"];

// 8. Add more mixed traffic to reach 250 lines
while (count($rows) < 250) {
    $proto = ['UDP','TCP','DNS','HTTP','HTTPS','ICMP','ARP','TLSv1.3'][array_rand(['UDP','TCP','DNS','HTTP','HTTPS','ICMP','ARP','TLSv1.3'])];
    $src = rand_ip(rand(0,1));
    $dst = rand_ip(rand(0,1));
    $len = rand(54, 1500);
    $info = '';
    switch ($proto) {
        case 'UDP':
            $svc = $udp_services[array_rand($udp_services)];
            $info = $svc['desc'] . " traffic $src > $dst port " . $svc['port'];
            break;
        case 'TCP':
            $svc = $tcp_services[array_rand($tcp_services)];
            $sport = rand(40000,60000);
            $info = "$sport  >  {$svc['port']} " . $tcp_flags[array_rand($tcp_flags)] . " Seq=" . rand(0,10000) . " Ack=" . rand(0,10000) . " Win=" . rand(32000,65535) . " Len=" . rand(0,1000);
            break;
        case 'DNS':
            $domain = $dns_domains[array_rand($dns_domains)];
            $query_id = dechex(rand(1000,9999));
            if (rand(0,1)) {
                $src = rand_ip(true); $dst = "10.30.0.1";
                $info = "Standard query 0x$query_id A $domain";
            } else {
                $src = "10.30.0.1"; $dst = rand_ip(true);
                $info = "Standard query response 0x$query_id A $domain A " . rand_ip(false);
            }
            $len = rand(60,120);
            break;
        case 'HTTP':
            $method = ['GET','POST','HEAD'][array_rand(['GET','POST','HEAD'])];
            $path = $http_paths[array_rand($http_paths)];
            $host = $dns_domains[array_rand($dns_domains)];
            $info = "$method $path HTTP/1.1 Host: $host";
            $len = rand(200, 2000);
            break;
        case 'HTTPS':
            $info = "Encrypted Application Data";
            $len = rand(200, 2000);
            break;
        case 'ICMP':
            $icmp = $icmp_types[array_rand($icmp_types)];
            $info = "$icmp id=" . rand(1000,9999) . " seq=" . rand(1,10);
            $len = rand(60,120);
            break;
        case 'ARP':
            if (rand(0,1)) {
                $info = "Who has $dst? Tell $src";
            } else {
                $info = "Reply $dst is-at " . rand_mac();
            }
            $len = 42;
            break;
        case 'TLSv1.3':
            $tls_msgs = [
                "Client Hello (SNI=" . $dns_domains[array_rand($dns_domains)] . ")",
                "Server Hello, Change Cipher Spec, Application Data",
                "Application Data, Application Data, Application Data",
                "Change Cipher Spec, Application Data, Application Data"
            ];
            $info = $tls_msgs[array_rand($tls_msgs)];
            $len = rand(200, 3000);
            break;
    }
    $rows[] = [$line++, inc_time($base_time, $micros), $src, $dst, $proto, $len, $info];
}

// Output all rows
foreach ($rows as $row) {
    echo '"' . implode('","', $row) . '"' . "\r\n";
}
