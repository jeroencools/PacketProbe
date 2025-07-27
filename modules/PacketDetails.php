<?php
// No need to remap or normalize keys here: $packets already uses the expected keys from the mapping in dashboard.php

if (!isset($packets) || !is_array($packets) || count($packets) === 0) {
    echo '<div class="alert alert-warning">No packet data available.</div>';
    return;
}

$headers = array_keys($packets[0]);

// Collect unique values for filters
$unique = [
    'Time' => [],
    'Source' => [],
    'Destination' => [],
    'Protocol' => [],
];
foreach ($packets as $row) {
    foreach ($unique as $key => $_) {
        if (isset($row[$key])) {
            $unique[$key][$row[$key]] = true;
        }
    }
}
foreach ($unique as $key => $vals) {
    $unique[$key] = array_keys($vals);
    sort($unique[$key]);
}
?>
<!-- Filter controls -->
<form id="packet-filters" class="mb-2">
    <div class="row g-2">
        <!-- Time From -->
        <div class="col-auto">
            <label class="form-label mb-0" for="filter-time-from">Time from</label>
            <select class="form-select form-select-sm" id="filter-time-from" data-filter="TimeFrom">
                <option value="">Any</option>
                <?php foreach ($unique['Time'] as $val): ?>
                    <option value="<?php echo htmlspecialchars($val); ?>"><?php echo htmlspecialchars($val); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <!-- Time To -->
        <div class="col-auto">
            <label class="form-label mb-0" for="filter-time-to">Time to</label>
            <select class="form-select form-select-sm" id="filter-time-to" data-filter="TimeTo">
                <option value="">Any</option>
                <?php foreach ($unique['Time'] as $val): ?>
                    <option value="<?php echo htmlspecialchars($val); ?>"><?php echo htmlspecialchars($val); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php foreach (['Source','Destination','Protocol'] as $filter): ?>
            <div class="col-auto">
                <label class="form-label mb-0" for="filter-<?php echo strtolower($filter); ?>"><?php echo $filter; ?></label>
                <select class="form-select form-select-sm" id="filter-<?php echo strtolower($filter); ?>" data-filter="<?php echo $filter; ?>">
                    <option value="">All</option>
                    <?php foreach ($unique[$filter] as $val): ?>
                        <option value="<?php echo htmlspecialchars($val); ?>"><?php echo htmlspecialchars($val); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endforeach; ?>
    </div>
</form>
<div class="table-responsive">
    <table class="table table-dark table-striped table-bordered table-sm mb-0 small" id="packet-table">
        <thead>
            <tr>
                <?php foreach ($headers as $header): ?>
                    <th><?php echo htmlspecialchars($header); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($packets as $row): ?>
                <tr>
                    <?php foreach ($headers as $header): ?>
                        <?php if (strtolower($header) === 'protocol'): ?>
                            <td><span class="protocol-link" data-proto="<?php echo htmlspecialchars($row[$header]); ?>"><?php echo htmlspecialchars($row[$header]); ?></span></td>
                        <?php else: ?>
                            <td><?php echo htmlspecialchars($row[$header]); ?></td>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Adjusted filter list for new time controls
    const selects = [
        document.getElementById('filter-time-from'),
        document.getElementById('filter-time-to'),
        document.getElementById('filter-source'),
        document.getElementById('filter-destination'),
        document.getElementById('filter-protocol')
    ];
    const table = document.getElementById('packet-table');
    const rows = Array.from(table.tBodies[0].rows);

    function filterTable() {
        const timeFrom = selects[0].value;
        const timeTo = selects[1].value;
        const source = selects[2].value;
        const destination = selects[3].value;
        const protocol = selects[4].value;

        // Find column indexes
        const headers = Array.from(table.tHead.rows[0].cells).map(th => th.textContent.trim());
        const idxTime = headers.indexOf('Time');
        const idxSource = headers.indexOf('Source');
        const idxDestination = headers.indexOf('Destination');
        const idxProtocol = headers.indexOf('Protocol');

        rows.forEach(row => {
            let show = true;
            const timeVal = row.cells[idxTime].textContent.trim();
            if (timeFrom && timeVal < timeFrom) show = false;
            if (timeTo && timeVal > timeTo) show = false;
            if (source && row.cells[idxSource].textContent.trim() !== source) show = false;
            if (destination && row.cells[idxDestination].textContent.trim() !== destination) show = false;
            if (protocol && row.cells[idxProtocol].textContent.trim() !== protocol) show = false;
            row.style.display = show ? '' : 'none';
        });
    }
    selects.forEach(sel => sel.addEventListener('change', filterTable));
});
</script>
<style>
  .protocol-link {
    color: #4ea1f7;
    text-decoration: underline;
    cursor: pointer;
    font-weight: 500;
    transition: color 0.15s;
  }
  .protocol-link:hover {
    color: #1d72b8;
    text-decoration: underline;
  }
</style>
<!-- Protocol explanation modal (required for clickable protocol links) -->
<div id="protocol-modal-backdrop" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.75);z-index:9998;"></div>
<div id="protocol-modal" style="display:none;position:fixed;top:20%;left:50%;transform:translate(-50%,0);background:#222;color:#fff;padding:20px;border-radius:8px;z-index:9999;min-width:300px;">
  <div id="protocol-modal-content"></div>
</div>
<!-- Protocol explanations JS (required for clickable protocol links) -->
<script src="assets/js/protocols.js"></script>
