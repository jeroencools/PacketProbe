<?php
// modules/AnomalyDetection.php
// Simple anomaly detection for network packets

if (!isset($packets) || !is_array($packets) || count($packets) === 0) {
    echo '<div class="alert alert-warning">No packet data available.</div>';
    return;
}


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
<div class="mb-3 d-flex flex-wrap align-items-end gap-3">
  <div>
    <label for="anomalyReasonFilter" class="form-label mb-1">Filter by Anomaly Type:</label>
    <select id="anomalyReasonFilter" class="form-select form-select-sm">
      <option value="">All</option>
      <?php foreach ($uniqueReasons as $reason): ?>
        <option value="<?= htmlspecialchars($reason) ?>"><?= htmlspecialchars($reason) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div>
    <canvas id="anomalyPieChart" width="240" height="240"></canvas>
  </div>
</div>

<div class="table-responsive mb-3">
  <table id="anomalyTable" class="table table-dark table-bordered table-sm small">
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
new Chart(ctx, {
  type: 'pie',
  data: pieData,
  options: {
    plugins: { legend: { position: 'left', align: 'start', labels: { boxWidth: 18, padding: 18 } } },
    responsive: false,
    maintainAspectRatio: false
  }
});

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
