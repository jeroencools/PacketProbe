<?php
// Expects $packets (array of associative arrays) to be available

if (!isset($packets) || !is_array($packets) || count($packets) === 0) {
    echo '<div class="alert alert-warning">No packet data available.</div>';
    return;
}

// Handle selection (default: Top Source, by packet count)
$type = isset($_POST['toptalkers_type']) ? $_POST['toptalkers_type'] : 'source';
$metric = isset($_POST['toptalkers_metric']) ? $_POST['toptalkers_metric'] : 'count';

// Aggregate data
$talkers = [];
foreach ($packets as $row) {
    $key = ($type === 'source')
        ? (isset($row['Source']) ? $row['Source'] : 'Unknown')
        : (isset($row['Destination']) ? $row['Destination'] : 'Unknown');
    $bytes = isset($row['Length']) && is_numeric($row['Length']) ? (int)$row['Length'] : 0;
    if (!isset($talkers[$key])) {
        $talkers[$key] = ['count' => 0, 'bytes' => 0];
    }
    $talkers[$key]['count']++;
    $talkers[$key]['bytes'] += $bytes;
}

// Convert to array for sorting (avoid passing array_map result by reference)
$talkersList = [];
foreach ($talkers as $addr => $vals) {
    $talkersList[] = ['address' => $addr, 'count' => $vals['count'], 'bytes' => $vals['bytes']];
}
usort($talkersList, function($a, $b) use ($metric) {
    return $b[$metric] <=> $a[$metric];
});
$topTalkers = array_slice($talkersList, 0, 10);

// Prepare data for Chart.js
$labels = array_column($topTalkers, 'address');
$data = array_column($topTalkers, $metric);
$chartId = 'topTalkersChart_' . uniqid();
?>
<form method="post" class="mb-2 d-flex flex-wrap gap-2 align-items-center">
    <input type="hidden" name="csvFile" value="<?php echo isset($_POST['csvFile']) ? htmlspecialchars($_POST['csvFile']) : ''; ?>">
    <?php
    // Preserve module selections and layout
    for ($j = 0; $j < 6; $j++) {
        if (isset($_POST["module$j"])) {
            echo '<input type="hidden" name="module' . $j . '" value="' . htmlspecialchars($_POST["module$j"]) . '">';
        }
    }
    if (isset($_POST['layout'])) {
        echo '<input type="hidden" name="layout" value="' . htmlspecialchars($_POST['layout']) . '">';
    }
    ?>
    <label for="toptalkers_type" class="mb-0">Type:</label>
    <select name="toptalkers_type" id="toptalkers_type" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
        <option value="source" <?php if ($type === 'source') echo 'selected'; ?>>Top Source</option>
        <option value="destination" <?php if ($type === 'destination') echo 'selected'; ?>>Top Destination</option>
    </select>
    <label for="toptalkers_metric" class="mb-0">Metric:</label>
    <select name="toptalkers_metric" id="toptalkers_metric" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
        <option value="count" <?php if ($metric === 'count') echo 'selected'; ?>>Packet Count</option>
        <option value="bytes" <?php if ($metric === 'bytes') echo 'selected'; ?>>Total Bytes</option>
    </select>
</form>
<div style="flex:1 1 auto; display:flex; flex-direction:column; justify-content:stretch; align-items:stretch; min-height:0; height:100%;">
    <canvas id="<?php echo $chartId; ?>" style="width:100% !important; height:220px; max-height:220px;"></canvas>
</div>
<div class="table-responsive mt-2">
    <table class="table table-dark table-striped table-bordered table-sm mb-0 small sortable">
        <thead>
            <tr>
                <th>Address</th>
                <th>Packet Count</th>
                <th>Total Bytes</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($topTalkers as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['address']); ?></td>
                    <td><?php echo $row['count']; ?></td>
                    <td><?php echo $row['bytes']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php if (empty($GLOBALS['chartjs_loaded'])): $GLOBALS['chartjs_loaded'] = true; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php endif; ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    var ctx = document.getElementById('<?php echo $chartId; ?>').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                label: <?php echo json_encode($metric === 'count' ? 'Packet Count' : 'Total Bytes'); ?>,
                data: <?php echo json_encode($data); ?>,
                backgroundColor: '#4e79a7'
            }]
        },
        options: {
            indexAxis: 'y',
            maintainAspectRatio: false,
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: true }
            },
            scales: {
                x: {
                    ticks: { color: '#f8f9fa' },
                    grid: { color: '#343a40' }
                },
                y: {
                    ticks: { color: '#f8f9fa' },
                    grid: { color: '#343a40' }
                }
            }
        }
    });
});
</script>
