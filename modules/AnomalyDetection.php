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

// --- Only show filter and table ---
?>
<!-- Filter on top -->
<form class="mb-3" id="anomaly-filter-form">
  <label for="anomalyReasonFilter" class="form-label mb-1">Filter by Anomaly Type:</label>
  <select id="anomalyReasonFilter" class="form-select form-select-sm" style="max-width: 300px; display: inline-block;">
    <option value="">All</option>
    <?php foreach ($uniqueReasons as $reason): ?>
      <option value="<?= htmlspecialchars($reason) ?>"><?= htmlspecialchars($reason) ?></option>
    <?php endforeach; ?>
  </select>
</form>

<div class="d-flex flex-column h-100">
  <div class="card mb-3 flex-grow-1" style="min-height:0; max-height: 100%;">
    <div class="card-body p-4 d-flex flex-column h-100" style="min-height:0; max-height: 100%; overflow: hidden;">
      <div class="flex-grow-1 d-flex flex-column min-vh-0" style="min-height:0;">
        <div class="table-responsive flex-grow-1 mb-3" style="height: 0; min-height: 0; flex-basis: 0;">
          <table id="anomalyTable" class="table table-dark table-striped table-bordered table-sm mb-0">
            <thead>
                <tr>
                  <th>Reason</th>
                  <?php
                    if (!empty($anomalies[0]['Packet'])) {
                        foreach (array_keys($anomalies[0]['Packet']) as $col) {
                            echo '<th>' . htmlspecialchars($col) . '</th>';
                        }
                    }
                  ?>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($anomalies as $anom): ?>
                  <tr data-reason="<?= htmlspecialchars($anom['Reason']) ?>">
                    <td><?= htmlspecialchars($anom['Reason']) ?></td>
                    <?php foreach ($anom['Packet'] as $col => $val): ?>
                      <?php if (strtolower($col) === 'protocol'): ?>
                        <td><span class="protocol-link" data-proto="<?= htmlspecialchars($val) ?>"><?= htmlspecialchars($val) ?></span></td>
                      <?php else: ?>
                        <td><?= htmlspecialchars($val) ?></td>
                      <?php endif; ?>
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
<script>
document.addEventListener("DOMContentLoaded", function () {
  // Table filter
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
});
</script>
<style>
/* filepath: c:\Users\Jeroen\OneDrive\ICT\5. Code\Webdev\PacketProbe\modules\AnomalyDetection.php */
.protocol-pie-container { display: none !important; }
</style>