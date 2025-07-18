<?php
// modules/AnomalyDetection.php
// Simple anomaly detection for network packets

if (!isset($packets) || !is_array($packets) || count($packets) === 0) {
    echo '<div class="alert alert-warning">No packet data available.</div>';
    return;
}

// --- Normalize packet keys to expected names regardless of CSV header order or naming ---
function normalize_packet_keys($packets) {
    // Map possible header variants to canonical keys
    $keymap = [
        'Source' => ['Source', 'Src', 'src'],
        'Destination' => ['Destination', 'Dst', 'Dest', 'destination'],
        'Protocol' => ['Protocol', 'Proto', 'protocol'],
        'Length' => ['Length', 'len', 'length'],
        'Info' => ['Info', 'info', 'Description', 'description'],
        'Source Port' => ['Source Port', 'Src port', 'Src Port', 'src port', 'src_port', 'SrcPort', 'Source port'],
        'Destination Port' => ['Destination Port', 'Dest port', 'Dst port', 'Dest Port', 'dst port', 'dest_port', 'DstPort', 'Destination port'],
        'Time' => ['Time', 'Timestamp', 'time', 'timestamp'],
        'No.' => ['No.', 'No', 'no', '#'],
    ];
    $normalized = [];
    foreach ($packets as $pkt) {
        $newpkt = [];
        foreach ($keymap as $canon => $aliases) {
            foreach ($aliases as $alias) {
                if (array_key_exists($alias, $pkt)) {
                    $newpkt[$canon] = $pkt[$alias];
                    break;
                }
            }
        }
        // Add any other keys as-is
        foreach ($pkt as $k => $v) {
            if (!isset($newpkt[$k]) && !in_array($k, array_keys($keymap))) {
                $newpkt[$k] = $v;
            }
        }
        $normalized[] = $newpkt;
    }
    return $normalized;
}

$packets = normalize_packet_keys($packets);

// --- Modular anomaly detection functions ---
function detect_port_scans($packets) {
    $anomalies = [];
    $seenSrcDst = [];
    // First pass: collect all destination ports per source:port
    foreach ($packets as $pkt) {
        $src = $pkt['Source'] ?? '';
        $srcPort = $pkt['Source Port'] ?? '';
        $dstPort = $pkt['Destination Port'] ?? '';
        if ($src && $srcPort && $dstPort) {
            $scanKey = $src . ':' . $srcPort;
            $seenSrcDst[$scanKey][$dstPort] = true;
        }
    }
    // Second pass: flag all packets from source:port to any of the scanned ports if threshold met
    foreach ($seenSrcDst as $scanKey => $dstPorts) {
        if (count($dstPorts) >= 2) { // threshold
            foreach ($packets as $pkt) {
                $src = $pkt['Source'] ?? '';
                $srcPort = $pkt['Source Port'] ?? '';
                $dstPort = $pkt['Destination Port'] ?? '';
                if ($src && $srcPort && $dstPort && ($src . ':' . $srcPort) === $scanKey && isset($dstPorts[$dstPort])) {
                    $anomalies[] = [
                        'Reason' => 'Possible port scan',
                        'Packet' => $pkt
                    ];
                }
            }
        }
    }
    return $anomalies;
}

function detect_rare_protocols($packets) {
    $anomalies = [];
    $protocolCounts = [];
    foreach ($packets as $pkt) {
        $proto = $pkt['Protocol'] ?? '';
        if ($proto) $protocolCounts[$proto] = ($protocolCounts[$proto] ?? 0) + 1;
    }
    if (count($packets) > 20) {
        foreach ($packets as $pkt) {
            $proto = $pkt['Protocol'] ?? '';
            if ($proto && $protocolCounts[$proto] === 1) {
                $anomalies[] = [
                    'Reason' => 'Rare protocol',
                    'Packet' => $pkt
                ];
            }
        }
    }
    return $anomalies;
}

function detect_large_packets($packets) {
    $anomalies = [];
    foreach ($packets as $pkt) {
        $size = isset($pkt['Length']) ? intval($pkt['Length']) : null;
        if ($size !== null && $size > 1500) {
            $anomalies[] = [
                'Reason' => 'Unusually large packet',
                'Packet' => $pkt
            ];
        }
    }
    return $anomalies;
}

function detect_high_freq($packets) {
    $anomalies = [];
    $timeCounts = [];
    foreach ($packets as $pkt) {
        $time = $pkt['Time'] ?? '';
        if ($time) $timeCounts[$time] = ($timeCounts[$time] ?? 0) + 1;
    }
    foreach ($timeCounts as $t => $cnt) {
        if ($cnt > 50) {
            foreach ($packets as $pkt) {
                if (($pkt['Time'] ?? '') === $t) {
                    $anomalies[] = [
                        'Reason' => 'High-frequency traffic',
                        'Packet' => $pkt
                    ];
                }
            }
        }
    }
    return $anomalies;
}

// Load blacklist from external file
$blacklistFile = __DIR__ . '/blacklist.php';
if (file_exists($blacklistFile)) {
    $blacklist = include $blacklistFile;
    if (!is_array($blacklist)) $blacklist = [];
} else {
    $blacklist = [];
}
function detect_blacklisted_ips($packets, $blacklist) {
    $anomalies = [];
    foreach ($packets as $pkt) {
        $src = $pkt['Source'] ?? '';
        $dst = $pkt['Destination'] ?? '';
        if (in_array($src, $blacklist) || in_array($dst, $blacklist)) {
            $anomalies[] = [
                'Reason' => 'Blacklisted IP',
                'Packet' => $pkt
            ];
        }
    }
    return $anomalies;
}

// Example: malformed packet check (missing key fields)
function detect_malformed_packets($packets) {
    $anomalies = [];
    foreach ($packets as $pkt) {
        if (empty($pkt['Source']) || empty($pkt['Destination']) || empty($pkt['Protocol'])) {
            $anomalies[] = [
                'Reason' => 'Malformed packet (missing key fields)',
                'Packet' => $pkt
            ];
        }
    }
    return $anomalies;
}

// --- Run all detection rules ---
$anomalies = [];
$anomalies = array_merge(
    detect_port_scans($packets),
    detect_rare_protocols($packets),
    detect_large_packets($packets),
    detect_high_freq($packets),
    detect_blacklisted_ips($packets, $blacklist),
    detect_malformed_packets($packets)
);

if (empty($anomalies)) {
    echo '<div class="alert alert-success">No obvious anomalies detected.</div>';
    return;
}

// --- Prepare data for filters and chart ---
$reasons = array_map(fn($a) => $a['Reason'], $anomalies);
$uniqueReasons = array_values(array_unique($reasons));
$reasonCounts = array_count_values($reasons);

// --- Dropdown filter and Pie Chart ---
?>

<!-- Filter on top -->
<div class="mb-3">
  <label for="anomalyReasonFilter" class="form-label mb-1">Filter by Anomaly Type:</label>
  <select id="anomalyReasonFilter" class="form-select form-select-sm" style="max-width: 300px; display: inline-block;">
    <option value="">All</option>
    <?php foreach ($uniqueReasons as $reason): ?>
      <option value="<?= htmlspecialchars($reason) ?>"><?= htmlspecialchars($reason) ?></option>
    <?php endforeach; ?>
  </select>
</div>


<!-- Pie chart and table side by side in a card with p-4 padding -->

<div class="card mb-3" style="height:100%; min-height:0;">
  <div class="card-body p-4">
    <div class="d-flex flex-row flex-wrap gap-4 align-items-start" style="height:100%; min-height:0;">
      <div style="min-width:180px;max-width:200px;display:flex;flex-direction:column;align-items:center;">
        <canvas id="anomalyPieChart" width="180" height="180"></canvas>
        <div id="anomalyPieLegend" style="width:100%;margin-top:8px;"></div>
      </div>
      <div style="flex:1 1 0;min-width:320px;min-height:0;">
        <div class="table-responsive" style="max-height:400px;overflow-y:auto;min-height:0;">
          <table id="anomalyTable" class="table table-dark table-bordered table-sm small mb-0">
            <thead>
              <tr><th>Reason</th><?php
                if (!empty($anomalies[0]['Packet'])) {
                    foreach (array_keys($anomalies[0]['Packet']) as $col) {
                        echo '<th>' . htmlspecialchars($col) . '</th>';
                    }
                }
              ?></tr>
            </thead>
            <tbody>
              <?php foreach ($anomalies as $anom): ?>
                <tr data-reason="<?= htmlspecialchars($anom['Reason']) ?>">
                  <td><?= htmlspecialchars($anom['Reason']) ?></td>
                  <?php foreach ($anom['Packet'] as $val): ?>
                    <td><?= htmlspecialchars($val) ?></td>
                  <?php endforeach; ?>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
// Pie chart data
const pieData = {
  labels: <?= json_encode($uniqueReasons) ?>,
  datasets: [{
    data: <?= json_encode(array_values($reasonCounts)) ?>,
    backgroundColor: [
      '#ff6384','#36a2eb','#ffce56','#4bc0c0','#9966ff','#ff9f40','#c9cbcf','#e377c2','#7f7f7f','#bcbd22','#17becf'
    ]
  }]
};
const ctx = document.getElementById('anomalyPieChart').getContext('2d');
const anomalyPieChart = new Chart(ctx, {
  type: 'pie',
  data: pieData,
  options: {
    plugins: { legend: { position: 'bottom', align: 'center', labels: { boxWidth: 14, padding: 10 } } },
    responsive: false,
    maintainAspectRatio: false
  }
});
// Move legend to custom div below chart
setTimeout(() => {
  const legend = anomalyPieChart.generateLegend ? anomalyPieChart.generateLegend() : '';
  if (legend) {
    document.getElementById('anomalyPieLegend').innerHTML = legend;
  }
}, 100);

// Dropdown filter
document.getElementById('anomalyReasonFilter').addEventListener('change', function() {
  const val = this.value;
  document.querySelectorAll('#anomalyTable tbody tr').forEach(row => {
    if (!val || row.getAttribute('data-reason') === val) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
    }
  });
});
</script>
