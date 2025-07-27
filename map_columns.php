<?php
// map_columns.php: Lets user map CSV columns to expected fields after upload
session_start();

// Expected fields for the dashboard/modules
$expectedFields = [
    'No.',
    'Time',
    'Source',
    'Source Port',
    'Destination',
    'Destination Port',
    'Protocol',
    'Length',
    'Info',
    // Add more if needed
];

// Handle file upload if coming directly from index.php
if (
    isset($_FILES['csvFile']) &&
    is_uploaded_file($_FILES['csvFile']['tmp_name']) &&
    $_FILES['csvFile']['error'] === UPLOAD_ERR_OK
) {
    // Remove previous temp file if it exists
    if (isset($_SESSION['csvFile']) && file_exists($_SESSION['csvFile'])) {
        @unlink($_SESSION['csvFile']);
    }
    $tmpName = tempnam(sys_get_temp_dir(), 'pktcsv_');
    move_uploaded_file($_FILES['csvFile']['tmp_name'], $tmpName);
    $_SESSION['csvFile'] = $tmpName;
    $_SESSION['csvFileName'] = $_FILES['csvFile']['name'];
}

// Get uploaded CSV file from session
$csvFile = isset($_SESSION['csvFile']) ? $_SESSION['csvFile'] : null;
if (!$csvFile || !is_readable($csvFile)) {
    echo '<div class="alert alert-danger">No uploaded CSV file found. <a href="index.php">Go back</a>.</div>';
    exit;
}

// Read headers and a few rows for preview
$headers = [];
$previewRows = [];
if (($handle = fopen($csvFile, "r")) !== false) {
    $headers = fgetcsv($handle, 0, ",", '"', "\\");
    if ($headers && isset($headers[0])) {
        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
    }
    $rowCount = 0;
    while (($data = fgetcsv($handle, 0, ",", '"', "\\")) !== false && $rowCount < 5) {
        $previewRows[] = $data;
        $rowCount++;
    }
    fclose($handle);
}

// Handle form submission (mapping)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mapping'])) {
    $mapping = $_POST['mapping'];
    $_SESSION['csv_mapping'] = $mapping;
    header('Location: dashboard.php');
    exit;
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Map CSV Columns - PacketProbe</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-dark text-light vh-100">
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Map CSV Columns</h2>
        <form onsubmit="return false;" class="m-0 p-0">
            <button type="submit" class="btn btn-primary" onclick="document.getElementById('mappingForm').submit();">Continue to Dashboard</button>
        </form>
    </div>
    <div class="mb-4">
        <div class="row">
            <div class="col-md-6 mb-2">
                <div class="card bg-secondary text-light">
                    <div class="card-header py-2">
                        <strong>CSV Columns Detected</strong>
                    </div>
                    <div class="card-body py-2">
                        <?php
                        // Move 'No.' to the front if present
                        $headersOrdered = $headers;
                        $noIndex = array_search('No.', $headersOrdered);
                        if ($noIndex !== false) {
                            array_unshift($headersOrdered, array_splice($headersOrdered, $noIndex, 1)[0]);
                        }
                        foreach ($headersOrdered as $h): ?>
                            <span class="badge bg-light text-dark me-1 mb-1"><?php echo htmlspecialchars($h); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-2">
                <div class="card bg-dark border-secondary text-light">
                    <div class="card-header py-2">
                        <strong>Fields Required by App</strong>
                    </div>
                    <div class="card-body py-2">
                        <?php foreach ($expectedFields as $field): ?>
                            <span class="badge bg-info text-dark me-1 mb-1"><?php echo htmlspecialchars($field); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <p class="mb-3">Please match each <span class="fw-bold text-info">expected field</span> to the correct <span class="fw-bold text-light bg-secondary px-1 rounded">CSV column</span> from your uploaded file.</p>
    <form method="post" id="mappingForm" action="dashboard.php">
        <div class="table-responsive mb-4">
            <table class="table table-dark table-bordered align-middle w-100" style="min-width: 400px; table-layout: fixed;">
                <colgroup>
                    <col style="width: 60%;">
                    <col style="width: 40%;">
                </colgroup>
                <thead>
                    <tr>
                        <th class="text-center">CSV Column</th>
                        <th class="text-center">Expected Field (App)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($expectedFields as $field): ?>
                        <tr>
                            <td>
                                <select name="mapping[<?php echo htmlspecialchars($field); ?>]" class="form-select mapping-dropdown" data-field="<?php echo htmlspecialchars($field); ?>" required>
                                    <option value="">-- Select CSV column --</option>
                                    <?php
                                    // Use $headersOrdered for dropdowns
                                    foreach ($headersOrdered as $h): ?>
                                        <option value="<?php echo htmlspecialchars($h); ?>"
                                            <?php if (strtolower($h) === strtolower($field)) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($h); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <span class="fw-bold text-info"><?php echo htmlspecialchars($field); ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="mb-4">
            <label class="form-label fw-bold">CSV Preview (first 5 rows):</label>
            <div class="table-responsive">
                <table class="table table-dark table-bordered mb-0">
                    <thead>
                        <tr>
                            <?php
                            // Use $headersOrdered for preview
                            foreach ($headersOrdered as $h): ?>
                                <th><?php echo htmlspecialchars($h); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($previewRows as $row): ?>
                            <tr>
                                <?php
                                // Reorder row to match $headersOrdered
                                $rowAssoc = array_combine($headers, $row);
                                foreach ($headersOrdered as $h):
                                    $cell = $rowAssoc[$h] ?? '';
                                ?>
                                    <td><?php echo htmlspecialchars($cell); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</div>
</body>
</html>
</div>
<script>
document.getElementById('continueForm').addEventListener('submit', function(e) {
    // Copy values from mappingForm dropdowns to hidden fields in continueForm
    var ok = true;
    document.querySelectorAll('.mapping-dropdown').forEach(function(sel) {
        var field = sel.getAttribute('data-field');
        var val = sel.value;
        var hidden = document.getElementById('hidden_' + md5(field));
        if (hidden) hidden.value = val;
        if (!val) ok = false;
    });
    if (!ok) {
        alert('Please map all fields before continuing.');
        e.preventDefault();
        return false;
    }
    // Submit to dashboard.php
    this.action = 'dashboard.php';
    this.method = 'post';
});

// Simple JS md5 for field names (to match hidden input IDs)
function md5(str) {
    // Minimal hash for field names (not cryptographically secure, just for unique IDs)
    var hash = 0, i, chr;
    if (str.length === 0) return hash;
    for (i = 0; i < str.length; i++) {
        chr   = str.charCodeAt(i);
        hash  = ((hash << 5) - hash) + chr;
        hash |= 0;
    }
    return Math.abs(hash);
}
</script>
</body>
</html>
