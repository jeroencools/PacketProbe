<?php
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="example.csv"');

// Column headers

$headers = ['No.','Time','Source','Source Port','Destination','Destination Port','Protocol','Length','Info'];
echo '"' . implode('","', $headers) . '"' . "\r\n";

// Helper functions
// Only 20 internal IPs
$internal_ips = [];
for ($i = 2; $i <= 21; $i++) {
    $internal_ips[] = "10.30.0.$i";
}

// External IPs for Google, Youtube, Facebook
$external_targets = [
    ['ip' => '142.250.190.78', 'host' => 'google.com'],      // Google
    ['ip' => '142.250.190.206', 'host' => 'youtube.com'],    // Youtube
    ['ip' => '157.240.1.35', 'host' => 'facebook.com']       // Facebook
];
function rand_external_ip() {
    global $external_targets;
    $target = $external_targets[array_rand($external_targets)];
    return $target['ip'];
}
function rand_external_host() {
    global $external_targets;
    $target = $external_targets[array_rand($external_targets)];
    return $target['host'];
}
function rand_ip($private = true) {
    global $internal_ips;
    if ($private) {
        return $internal_ips[array_rand($internal_ips)];
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
    $rows[] = [$line++, inc_time($base_time, $micros), $client, 68, '255.255.255.255', 67, 'DHCP', 342, "DHCP Discover from $client"];
    // Offer (server to client)
    $rows[] = [$line++, inc_time($base_time, $micros), $server, 67, $client, 68, 'DHCP', 342, "DHCP Offer to $client yiaddr=$yiaddr"];
    // Request (broadcast)
    $rows[] = [$line++, inc_time($base_time, $micros), $client, 68, '255.255.255.255', 67, 'DHCP', 342, "DHCP Request from $client for $yiaddr"];
    // ACK (server to client)
    $rows[] = [$line++, inc_time($base_time, $micros), $server, 67, $client, 68, 'DHCP', 342, "DHCP ACK to $client yiaddr=$yiaddr"];
}

// 2. DNS queries/responses for 5 clients
foreach (range(2, 6) as $c) {
    $client = "10.30.0.$c";
    $server = "10.30.0.1";
    $domain = $dns_domains[array_rand($dns_domains)];
    $query_id = dechex(rand(1000,9999));
    $rows[] = [$line++, inc_time($base_time, $micros), $client, 12345, $server, 53, 'DNS', 74, "Standard query 0x$query_id A $domain"];
    $rows[] = [$line++, inc_time($base_time, $micros), $server, 53, $client, 12345, 'DNS', 90, "Standard query response 0x$query_id A $domain A " . rand_ip(false)];
}

// 3. TCP 3-way handshake and HTTP request/response
$client = "10.30.0.10";
$server = rand_ip(false);
$client_port = rand(40000, 60000);
$server_port = 80;
$rows[] = [$line++, inc_time($base_time, $micros), $client, $client_port, $server, $server_port, 'TCP', 66, "$client_port  >  $server_port [SYN] Seq=0 Win=65535 Len=0 MSS=1460"];
$rows[] = [$line++, inc_time($base_time, $micros), $server, $server_port, $client, $client_port, 'TCP', 66, "$server_port  >  $client_port [SYN, ACK] Seq=0 Ack=1 Win=65535 Len=0 MSS=1420"];
$rows[] = [$line++, inc_time($base_time, $micros), $client, $client_port, $server, $server_port, 'TCP', 54, "$client_port  >  $server_port [ACK] Seq=1 Ack=1 Win=65280 Len=0"];
$rows[] = [$line++, inc_time($base_time, $micros), $client, $client_port, $server, $server_port, 'HTTP', 512, "GET /index.html HTTP/1.1 Host: " . $dns_domains[array_rand($dns_domains)]];
$rows[] = [$line++, inc_time($base_time, $micros), $server, $server_port, $client, $client_port, 'HTTP', 1024, "HTTP/1.1 200 OK Content-Type: text/html"];

// 4. UDP traffic (DNS, NTP, mDNS, SSDP)
foreach ($udp_services as $svc) {
    $src = rand_ip(true);
    $dst = rand_ip(true);
    if ($svc['desc'] === 'DNS') {
        $dst = "10.30.0.1";
    }
    $rows[] = [$line++, inc_time($base_time, $micros), $src, rand(1024,65535), $dst, $svc['port'], 'UDP', rand(60,120), $svc['desc'] . " traffic $src > $dst port " . $svc['port']];
}

// 5. ICMP echo request/reply
$src = rand_ip(true);
$dst = rand_ip(false);
$rows[] = [$line++, inc_time($base_time, $micros), $src, 0, $dst, 0, 'ICMP', 98, "Echo (ping) request id=" . rand(1000,9999) . " seq=1"];
$rows[] = [$line++, inc_time($base_time, $micros), $dst, 0, $src, 0, 'ICMP', 98, "Echo (ping) reply id=" . rand(1000,9999) . " seq=1"];

// 6. ARP request/reply
$src = rand_ip(true);
$dst = rand_ip(true);
$rows[] = [$line++, inc_time($base_time, $micros), $src, 0, $dst, 0, 'ARP', 42, "Who has $dst? Tell $src"];
$rows[] = [$line++, inc_time($base_time, $micros), $dst, 0, $src, 0, 'ARP', 42, "Reply $dst is-at " . rand_mac()];

// 7. TLS handshake/application data
$client = "10.30.0.20";
$server = rand_ip(false);
$tcp_client_port = rand(40000,60000);
$rows[] = [$line++, inc_time($base_time, $micros), $client, $tcp_client_port, $server, 443, 'TCP', 66, "$tcp_client_port  >  443 [SYN] Seq=0 Win=65535 Len=0 MSS=1460"];
$rows[] = [$line++, inc_time($base_time, $micros), $server, 443, $client, $tcp_client_port, 'TCP', 66, "443  >  $tcp_client_port [SYN, ACK] Seq=0 Ack=1 Win=65535 Len=0 MSS=1420"];
$rows[] = [$line++, inc_time($base_time, $micros), $client, $tcp_client_port, $server, 443, 'TCP', 54, "$tcp_client_port  >  443 [ACK] Seq=1 Ack=1 Win=65280 Len=0"];
$rows[] = [$line++, inc_time($base_time, $micros), $client, $tcp_client_port, $server, 443, 'TLSv1.3', 571, "Client Hello (SNI=" . $dns_domains[array_rand($dns_domains)] . ")"];
$rows[] = [$line++, inc_time($base_time, $micros), $server, 443, $client, $tcp_client_port, 'TLSv1.3', 2894, "Server Hello, Change Cipher Spec, Application Data"];
$rows[] = [$line++, inc_time($base_time, $micros), $server, 443, $client, $tcp_client_port, 'TLSv1.3', 709, "Application Data, Application Data, Application Data"];

// 8. Add more mixed traffic to reach 250 lines
function is_internal($ip) {
    return preg_match('/^10\\.30\\.0\\.(?:[1-9][0-9]?|1[0-9][0-9]|2[0-4][0-9]|25[0-4])$/', $ip);
}
function is_firewall($ip) {
    return $ip === '10.30.0.1';
}
function is_external($ip) {
    return !is_internal($ip) && !is_firewall($ip);
}
// 350 lines, with bursts and more realism
$total_lines = 500;
$burst_size = 10;
$burst_probability = 20; // percent
while (count($rows) < $total_lines) {
    $burst = rand(1,100) <= $burst_probability;
    $burst_count = $burst ? rand(3, $burst_size) : 1;
    for ($b = 0; $b < $burst_count && count($rows) < $total_lines; $b++) {
        $is_external = rand(1, 100) <= 70;
        $proto_pool = ['UDP','TCP','DNS','HTTP','HTTPS','ICMP','ARP','TLSv1.3','SMTP','POP3','IMAP','FTP','QUIC'];
        $proto = $proto_pool[array_rand($proto_pool)];
        $src = null;
        $dst = null;
        $len = rand(54, 1500);
        $info = '';
        // $comment = '';
        // NAT simulation: for external, show both pre- and post-NAT
        if ($is_external) {
            $internal = rand_ip(true);
            $external = rand_external_ip();
            $host = rand_external_host();
            $client_port = rand(40000, 60000);
            $nat_port = rand(40000, 60000);
            $service = $tcp_services[array_rand($tcp_services)];
            $direction = rand(0,1);
            if ($direction) {
                // Outbound: internal -> firewall (pre-NAT)
                $src = $internal;
                $dst = '10.30.0.1';
                $info = "$client_port  >  {$service['port']} [SYN] Seq=0 Win=65535 Len=0";
                $rows[] = [$line++, inc_time($base_time, $micros), $src, $dst, 'TCP', 66, $info];
                // Outbound: firewall -> external (post-NAT)
                $src = '10.30.0.1';
                $dst = $external;
                $info = "$nat_port  >  {$service['port']} [SYN] Seq=0 Win=65535 Len=0";
                $rows[] = [$line++, inc_time($base_time, $micros), $src, $dst, 'TCP', 66, $info];
                // Optionally add HTTP/HTTPS/QUIC/SMTP/POP3/IMAP/FTP/QUIC
                if (in_array($proto, ['HTTP','HTTPS','QUIC'])) {
                    $method = ['GET','POST','HEAD'][array_rand(['GET','POST','HEAD'])];
                    $path = $http_paths[array_rand($http_paths)];
                    $ua = ['Mozilla/5.0','curl/7.68.0','Wget/1.20.3','Edge/18.18363'][array_rand(['Mozilla/5.0','curl/7.68.0','Wget/1.20.3','Edge/18.18363'])];
                    $info = "$method $path HTTP/1.1 Host: $host User-Agent: $ua";
                    $rows[] = [$line++, inc_time($base_time, $micros), '10.30.0.1', $external, $proto, rand(200,2000), $info];
                }
                if ($proto === 'SMTP') {
                    $info = "MAIL FROM:<user@$host> RCPT TO:<someone@gmail.com>";
                    $rows[] = [$line++, inc_time($base_time, $micros), '10.30.0.1', $external, 'SMTP', 180, $info];
                }
                if ($proto === 'POP3') {
                    $info = "+OK POP3 server ready";
                    $rows[] = [$line++, inc_time($base_time, $micros), $external, '10.30.0.1', 'POP3', 120, $info];
                }
                if ($proto === 'IMAP') {
                    $info = "* OK IMAP4rev1 Service Ready";
                    $rows[] = [$line++, inc_time($base_time, $micros), $external, '10.30.0.1', 'IMAP', 120, $info];
                }
                if ($proto === 'FTP') {
                    $info = "USER anonymous";
                    $rows[] = [$line++, inc_time($base_time, $micros), '10.30.0.1', $external, 'FTP', 90, $info];
                }
            } else {
                // Inbound: external -> firewall (pre-NAT)
                $src = $external;
                $dst = '10.30.0.1';
                $info = "443  >  $nat_port [SYN, ACK] Seq=0 Ack=1 Win=65535 Len=0";
                $rows[] = [$line++, inc_time($base_time, $micros), $src, $dst, 'TCP', 66, $info];
                // Inbound: firewall -> internal (post-NAT)
                $src = '10.30.0.1';
                $dst = $internal;
                $info = "443  >  $client_port [SYN, ACK] Seq=0 Ack=1 Win=65535 Len=0";
                $rows[] = [$line++, inc_time($base_time, $micros), $src, $dst, 'TCP', 66, $info];
            }
            // Simulate some errors/anomalies
            if (rand(1,100) <= 5) {
                $info = "Destination unreachable";
            $rows[] = [$line++, inc_time($base_time, $micros), $external, '10.30.0.1', 'ICMP', 98, $info];
            }
        } else {
            // Other: DNS, ARP, ICMP, etc. (internal <-> firewall)
            switch ($proto) {
                case 'DNS':
                    $domain = $dns_domains[array_rand($dns_domains)];
                    $query_id = dechex(rand(1000,9999));
                    if (rand(0,1)) {
                        $src = rand_ip(true); $dst = "10.30.0.1";
                        $info = "Standard query 0x$query_id A $domain";
                        // $comment = 'DNS query';
                    } else {
                        $src = "10.30.0.1"; $dst = rand_ip(true);
                        $info = "Standard query response 0x$query_id A $domain A " . rand_external_ip();
                        // $comment = 'DNS response';
                    }
                    $len = rand(60,120);
                    break;
                case 'ICMP':
                    $icmp = $icmp_types[array_rand($icmp_types)];
                    if (rand(0,1)) {
                        $src = rand_ip(true); $dst = '10.30.0.1';
                    } else {
                        $src = '10.30.0.1'; $dst = rand_ip(true);
                    }
                    $info = "$icmp id=" . rand(1000,9999) . " seq=" . rand(1,10);
                    // $comment = 'ICMP';
                    $len = rand(60,120);
                    break;
                case 'ARP':
                    if (rand(0,1)) {
                        $src = rand_ip(true); $dst = '10.30.0.1';
                        $info = "Who has $dst? Tell $src";
                        // $comment = 'ARP request';
                    } else {
                        $src = '10.30.0.1'; $dst = rand_ip(true);
                        $info = "Reply $dst is-at " . rand_mac();
                        // $comment = 'ARP reply';
                    }
                    $len = 42;
                    break;
                default:
                    // Default to internal <-> firewall
                    if (rand(0,1)) {
                        $src = rand_ip(true);
                        $dst = '10.30.0.1';
                        // $comment = 'Internal to firewall';
                    } else {
                        $src = '10.30.0.1';
                        $dst = rand_ip(true);
                        // $comment = 'Firewall to internal';
                    }
                    break;
            }
            // Broadcast/multicast: mDNS, SSDP, DHCP, NetBIOS, etc.
            if (rand(1,100) <= 10) {
                $src = rand_ip(true);
                $dst = '224.0.0.251';
                $info = 'mDNS query';
                $rows[] = [$line++, inc_time($base_time, $micros), $src, $dst, 'UDP', 90, $info];
            }
            if (rand(1,100) <= 10) {
                $src = rand_ip(true);
                $dst = '239.255.255.250';
                $info = 'SSDP discovery';
                $rows[] = [$line++, inc_time($base_time, $micros), $src, $dst, 'UDP', 90, $info];
            }
            if (rand(1,100) <= 5) {
                $src = rand_ip(true);
                $dst = '255.255.255.255';
                $info = 'NetBIOS Name Query';
                $rows[] = [$line++, inc_time($base_time, $micros), $src, $dst, 'UDP', 90, $info];
            }
        }
        if ($src && $dst) {
            $src_port = in_array($proto, ['TCP','UDP']) ? rand(1024,65535) : 0;
            $dst_port = in_array($proto, ['TCP','UDP']) ? rand(1,65535) : 0;
            $rows[] = [$line++, inc_time($base_time, $micros), $src, $src_port, $dst, $dst_port, $proto, $len, $info];
        }
    }
}



// Only inject anomalies if requested

$addAnomalies = isset($_GET['anomalies']) && $_GET['anomalies'] == '1';
if ($addAnomalies) {
    $anomaly_rows = [];
    $insert_positions = [];
    $total_rows = count($rows);

    // 1. Port scan: same source, many unique destination ports (to trigger detection)
    $scan_src = '10.30.0.99';
    $num_portscan = rand(2, 5);
    $src_port = 40000;
    for ($i = 0; $i < $num_portscan; $i++) {
        $dst_port = 1000 + $i;
        $anomaly_rows[] = [
            null, // Line number will be set later
            null, // Time will be set later
            $scan_src,
            $src_port,
            '10.30.0.1',
            $dst_port,
            'TCP',
            60,
            "$scan_src:$src_port > 10.30.0.1:$dst_port [SYN] Seq=0 Win=65535 Len=0"
        ];
    }

    // 2. Rare protocol: one-off protocol (random protocol name)
    $num_rare = rand(2, 5);
    $rare_protocols = ['GOPHER', 'XNS', 'DECNET', 'IPX', 'NETBIOS'];
    for ($i = 0; $i < $num_rare; $i++) {
        $proto = $rare_protocols[array_rand($rare_protocols)];
        $anomaly_rows[] = [
            null,
            null,
            '10.30.0.10',
            0,
            '10.30.0.1',
            0,
            $proto,
            100,
            "$proto request"
        ];
    }

    // 3. Unusually large packet
    $num_large = rand(2, 5);
    for ($i = 0; $i < $num_large; $i++) {
        $anomaly_rows[] = [
            null,
            null,
            '10.30.0.11',
            40001,
            '142.250.190.78',
            443,
            'TCP',
            rand(2000, 10000),
            'Large file transfer'
        ];
    }

    // 4. High-frequency: many packets at the same timestamp
    $num_highfreq = rand(2, 5);
    $freq_time = null; // Will be set later
    for ($i = 0; $i < 51 + $num_highfreq; $i++) { // >50 for detection
        $anomaly_rows[] = [
            null,
            null,
            '10.30.0.12',
            50000,
            '142.250.190.206',
            53,
            'UDP',
            120,
            'Burst traffic'
        ];
    }

    // 5. Blacklisted IP (use one from blacklist.php)
    $num_blacklisted = rand(2, 5);
    $blacklisted_ips = ['198.2.175.226', '185.60.170.211', '8.216.34.160', '188.166.225.158', '185.238.231.177'];
    for ($i = 0; $i < $num_blacklisted; $i++) {
        $ip = $blacklisted_ips[$i % count($blacklisted_ips)];
        $anomaly_rows[] = [
            null,
            null,
            $ip,
            6666,
            '10.30.0.13',
            80,
            'TCP',
            100,
            'TCP connection attempt'
        ];
    }

    // 6. Malformed packet (missing key fields)
    $num_malformed = rand(2, 5);
    for ($i = 0; $i < $num_malformed; $i++) {
        $fields = [
            ['', 0, '10.30.0.14', 80, 'TCP', 100, ''],
            ['10.30.0.15', 80, '', 0, 'TCP', 100, ''],
            ['10.30.0.16', 80, '10.30.0.17', 80, '', 100, ''],
        ];
        $f = $fields[$i % count($fields)];
        $anomaly_rows[] = [
            null,
            null,
            $f[0],
            $f[1],
            $f[2],
            $f[3],
            $f[4],
            $f[5],
            $f[6]
        ];
    }

    // Randomly insert anomaly packets into the main rows array
    shuffle($anomaly_rows);
    foreach ($anomaly_rows as $anomaly) {
        // Pick a random position in the rows array
        $pos = rand(0, count($rows));
        // Set line number and time to match the position
        $line_num = $pos < count($rows) ? $rows[$pos][0] : ($rows[count($rows)-1][0] + 1);
        $time_val = $pos < count($rows) ? $rows[$pos][1] : $rows[count($rows)-1][1];
        $anomaly[0] = $line_num;
        $anomaly[1] = $time_val;
        array_splice($rows, $pos, 0, [ $anomaly ]);
    }
}

// Output all rows
foreach ($rows as $row) {
    echo '"' . implode('","', $row) . '"' . "\r\n";
}
