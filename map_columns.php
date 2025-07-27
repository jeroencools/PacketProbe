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
<body id="body-root" class="bg-dark text-light<?php
    if (
        (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'light') ||
        (!isset($_COOKIE['theme']) && isset($_SERVER['HTTP_SEC_CH_PREFERS_COLOR_SCHEME']) && $_SERVER['HTTP_SEC_CH_PREFERS_COLOR_SCHEME'] === 'light')
    ) {
        echo ' light-mode';
    }
?>">
    <!-- Dark/Light mode toggle (top left) -->
    <div style="position: absolute; top: 18px; left: 18px; z-index: 10;">
        <label class="form-switch d-flex align-items-center gap-2" style="user-select:none;">
            <input type="checkbox" id="themeToggle" class="form-check-input" style="width:2em;height:1em;">
            <span id="themeLabel" style="font-size:1rem;">🌙</span>
        </label>
    </div>
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
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const body = document.getElementById('body-root');
        const toggle = document.getElementById('themeToggle');
        const label = document.getElementById('themeLabel');
        function setTheme(light, persist) {
            if (light) {
                body.classList.add('light-mode');
                label.textContent = '☀️';
            } else {
                body.classList.remove('light-mode');
                label.textContent = '🌙';
            }
            if (persist) {
                document.cookie = "theme=" + (light ? "light" : "dark") + ";path=/;max-age=31536000";
            }
        }
        function getCookie(name) {
            const v = document.cookie.match('(^|;)\\s*' + name + '\\s*=\\s*([^;]+)');
            return v ? v.pop() : '';
        }
        const saved = getCookie('theme') === 'light';
        setTheme(saved, false);
        if (toggle) toggle.checked = saved;
        if (toggle) {
            toggle.addEventListener('change', function() {
                setTheme(this.checked, true);
            });
        }
    });
    </script>
</body>
</html>
