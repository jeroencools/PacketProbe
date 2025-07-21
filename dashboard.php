<?php
// Move session_start() and all PHP logic to the very top before any HTML output
session_start();

// Register a shutdown function to clean up the temp file if the session is destroyed
register_shutdown_function(function() {
    if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['csvFile'])) {
        // Only remove the temp file and session if the session is destroyed (not on every request)
        if (isset($_SESSION['destroy_csv']) && $_SESSION['destroy_csv'] && file_exists($_SESSION['csvFile'])) {
            @unlink($_SESSION['csvFile']);
            unset($_SESSION['csvFile']);
            unset($_SESSION['originalPackets']);
            unset($_SESSION['csvFileName']);
            unset($_SESSION['destroy_csv']);
        }
    }
});

// Parse uploaded CSV file
$packets = [];
if (
    isset($_FILES['csvFile']) && is_uploaded_file($_FILES['csvFile']['tmp_name']) && $_FILES['csvFile']['error'] === UPLOAD_ERR_OK
) {
    // Remove previous temp file if it exists and is different from the new one
    if (isset($_SESSION['csvFile']) && file_exists($_SESSION['csvFile'])) {
        @unlink($_SESSION['csvFile']);
    }
    $tmpName = tempnam(sys_get_temp_dir(), 'pktcsv_');
    move_uploaded_file($_FILES['csvFile']['tmp_name'], $tmpName);
    $_SESSION['csvFile'] = $tmpName;
    $csvFile = $tmpName;
    $_SESSION['csvFileName'] = $_FILES['csvFile']['name'];

    // Parse the CSV file directly into $originalPackets (not just session)
    $originalPackets = [];
    if (($handle = fopen($csvFile, "r")) !== false) {
        $headers = fgetcsv($handle, 0, ",", '"', "\\");
        if ($headers && isset($headers[0])) {
            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
        }
        while (($data = fgetcsv($handle, 0, ",", '"', "\\")) !== false) {
            if (count($data) !== count($headers)) continue;
            $originalPackets[] = array_combine($headers, $data);
        }
        fclose($handle);
    }
    $_SESSION['originalPackets'] = $originalPackets;
    // Force reload to clear POST and prevent reusing old file on refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
} elseif (
    isset($_POST['csvFile']) && $_POST['csvFile'] && is_readable($_POST['csvFile'])
) {
    $csvFile = $_POST['csvFile'];
    $_SESSION['csvFile'] = $csvFile;
    if (isset($_POST['csvFileName'])) {
        $_SESSION['csvFileName'] = $_POST['csvFileName'];
    }
    // Always re-parse the CSV file on POST to $originalPackets
    $originalPackets = [];
    if ($csvFile && is_readable($csvFile)) {
        if (($handle = fopen($csvFile, "r")) !== false) {
            $headers = fgetcsv($handle, 0, ",", '"', "\\");
            if ($headers && isset($headers[0])) {
                $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
            }
            while (($data = fgetcsv($handle, 0, ",", '"', "\\")) !== false) {
                if (count($data) !== count($headers)) continue;
                $originalPackets[] = array_combine($headers, $data);
            }
            fclose($handle);
        }
    }
    $_SESSION['originalPackets'] = $originalPackets;
} elseif (
    isset($_SESSION['csvFile']) && is_readable($_SESSION['csvFile'])
) {
    $csvFile = $_SESSION['csvFile'];
    // Always re-parse the CSV file from session file if available
    $originalPackets = [];
    if ($csvFile && is_readable($csvFile)) {
        if (($handle = fopen($csvFile, "r")) !== false) {
            $headers = fgetcsv($handle, 0, ",", '"', "\\");
            if ($headers && isset($headers[0])) {
                $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
            }
            while (($data = fgetcsv($handle, 0, ",", '"', "\\")) !== false) {
                if (count($data) !== count($headers)) continue;
                $originalPackets[] = array_combine($headers, $data);
            }
            fclose($handle);
        }
    }
    $_SESSION['originalPackets'] = $originalPackets;
} elseif (
    isset($_GET['csvFile'])
) {
    $csvFile = $_GET['csvFile'];
    $_SESSION['csvFile'] = $csvFile;
    $originalPackets = [];
    if ($csvFile && is_readable($csvFile)) {
        if (($handle = fopen($csvFile, "r")) !== false) {
            $headers = fgetcsv($handle, 0, ",", '"', "\\");
            if ($headers && isset($headers[0])) {
                $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
            }
            while (($data = fgetcsv($handle, 0, ",", '"', "\\")) !== false) {
                if (count($data) !== count($headers)) continue;
                $originalPackets[] = array_combine($headers, $data);
            }
            fclose($handle);
        }
    }
    $_SESSION['originalPackets'] = $originalPackets;
} else {
    $csvFile = null;
    $originalPackets = [];
    // Do not clear session here!
    // $_SESSION['originalPackets'] = [];
    // $_SESSION['csvFileName'] = null;
}

// After parsing $originalPackets, set a variable for the parse status message (for development only):
$parseStatusMsg = '';
if (!empty($originalPackets)) {
    $parseStatusMsg = '<span class="badge bg-success text-light ms-3" style="font-size:1rem;vertical-align:middle;">CSV upload and parse successful. Parsed rows: ' . count($originalPackets) . '</span>';
} elseif (
    isset($_FILES['csvFile']) && is_uploaded_file($_FILES['csvFile']['tmp_name'])
) {
    $parseStatusMsg = '<span class="badge bg-warning text-dark ms-3" style="font-size:1rem;vertical-align:middle;">CSV upload attempted, but no rows parsed.</span>';
} else {
    $parseStatusMsg = '<span class="badge bg-secondary text-light ms-3" style="font-size:1rem;vertical-align:middle;">No CSV parsed yet.</span>';
}

// List of available modules for selection (add 'empty' as the first/default)
$modules = [
    'empty' => 'Please select...',
    'packetdetails' => 'Packet Details',
    'protocolpie' => 'Protocol Pie Chart',
    'toptalkers' => 'Top Talkers',
    'conversationmatrix' => 'Conversation Matrix',
    'networktopology' => 'Network Topology',
    'anomalydetection' => 'Anomaly Detection',
];

// Get selected modules for each card (from POST or default to 'empty')
$selectedModules = [];
for ($i = 0; $i < 6; $i++) {
    $selectedModules[$i] = isset($_POST["module$i"]) ? $_POST["module$i"] : 'empty';
}

// Add layout options
$layoutOptions = [
    '2x3' => ['rows' => 2, 'cols' => 3],
    '3x2' => ['rows' => 3, 'cols' => 2],
    '6x1' => ['rows' => 6, 'cols' => 1],
];
// Preserve layout selection across all POSTs
if (isset($_POST['layout'])) {
    $_SESSION['layout'] = $_POST['layout'];
}
$selectedLayout = isset($_SESSION['layout']) ? $_SESSION['layout'] : '2x3';
$totalCards = 6;
$layout = isset($layoutOptions[$selectedLayout]) ? $layoutOptions[$selectedLayout] : $layoutOptions['2x3'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PacketProbe: Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Use Bootstrap dark theme -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-dark text-light vh-100">
    <div class="container-fluid h-100 py-4">
        
<h1 class="mb-2 text-center d-flex justify-content-center align-items-center gap-3" style="gap: 1rem;">
    <span>PacketProbe - Dashboard</span>
</h1>

<div class="d-flex justify-content-center mb-3">
    <div class="me-3 d-flex align-items-center">
        <?php echo $parseStatusMsg; ?>
    </div>
    <form method="post" class="d-flex align-items-center" id="layout-form" style="font-size: 0.95rem;">
        <!-- Back button to upload page -->
        <a href="index.php" class="btn btn-secondary me-3" style="min-width: 90px;">&larr; Back</a>
        <input type="hidden" name="csvFile" value="<?php echo htmlspecialchars($csvFile ?? ''); ?>">
        <?php
        // Preserve module selections
        for ($j = 0; $j < $totalCards; $j++) {
            if (isset($selectedModules[$j])) {
                echo '<input type="hidden" name="module' . $j . '" value="' . htmlspecialchars($selectedModules[$j]) . '">';
            }
        }
        ?>
        <label for="layout" class="me-2 mb-0">Grid Layout:</label>
        <select name="layout" id="layout" class="form-select w-auto d-inline-block" onchange="this.form.submit()" style="margin-bottom:0;">
            <option value="2x3" <?php if ($selectedLayout === '2x3') echo 'selected'; ?>>Standard (2 rows x 3 columns)</option>
            <option value="3x2" <?php if ($selectedLayout === '3x2') echo 'selected'; ?>>Tall (3 rows x 2 columns)</option>
            <option value="6x1" <?php if ($selectedLayout === '6x1') echo 'selected'; ?>>Single Column (6 rows x 1 column)</option>
        </select>
    </form>
</div>
        <div class="row g-3 h-100" id="dashboard-grid" style="min-height: 0;">
            <?php
            // Determine column class based on layout
            $colClass = '';
            if ($selectedLayout === '2x3') $colClass = 'col-12 col-md-6 col-lg-4';
            elseif ($selectedLayout === '3x2') $colClass = 'col-12 col-md-4 col-lg-6';
            elseif ($selectedLayout === '6x1') $colClass = 'col-12';

            for ($i = 0; $i < $totalCards; $i++): ?>
            <div class="<?php echo $colClass; ?> d-flex align-items-stretch" style="min-height: 0;">
                <div class="card h-100 bg-dark text-light border-secondary w-100" style="min-height: 0;">
                    <div class="card-body d-flex flex-column overflow-auto" style="min-height: 0; height: 100%;">
                        <form method="post" id="module-select-form-<?php echo $i; ?>">
                            <input type="hidden" name="csvFile" value="<?php echo htmlspecialchars($csvFile ?? ''); ?>">
                            <input type="hidden" name="layout" value="<?php echo htmlspecialchars($selectedLayout); ?>">
                            <?php
                            for ($j = 0; $j < $totalCards; $j++) {
                                if ($j !== $i && isset($selectedModules[$j])) {
                                    echo '<input type="hidden" name="module' . $j . '" value="' . htmlspecialchars($selectedModules[$j]) . '">';
                                }
                            }
                            ?>
                            
                            <select class="form-select section-dropdown bg-dark text-light border-secondary mb-3"
                                    id="section-dropdown-<?php echo $i; ?>"
                                    name="module<?php echo $i; ?>"
                                    onchange="this.form.submit()">
                                <?php
                                foreach ($modules as $key => $label) {
                                    $selected = ($selectedModules[$i] === $key) ? 'selected' : '';
                                    echo "<option value=\"$key\" $selected>$label</option>";
                                }
                                ?>
                            </select>
                        </form>
                        <div class="section-content mt-1" id="section-content-<?php echo $i; ?>">
                            <?php
                            if ($selectedModules[$i] === 'empty') {
                                echo '<div class="text-secondary">Please select card content.</div>';
                            } elseif ($selectedModules[$i] === 'packetdetails') {
                                $packets = $originalPackets;
                                include __DIR__ . '/modules/PacketDetails.php';
                            } elseif ($selectedModules[$i] === 'protocolpie') {
                                $packets = $originalPackets;
                                include __DIR__ . '/modules/ProtocolPie.php';
                            } elseif ($selectedModules[$i] === 'toptalkers') {
                                $packets = $originalPackets;
                                include __DIR__ . '/modules/TopTalkers.php';
                            } elseif ($selectedModules[$i] === 'conversationmatrix') {
                                $packets = $originalPackets;
                                include __DIR__ . '/modules/ConversationMatrix.php';
                            } elseif ($selectedModules[$i] === 'networktopology') {
                                $packets = $originalPackets;
                                include __DIR__ . '/modules/NetworkTopology.php';
                            } elseif ($selectedModules[$i] === 'anomalydetection') {
                                $packets = $originalPackets;
                                include __DIR__ . '/modules/AnomalyDetection.php';
                            } else {
                                echo '<div class="text-secondary">Placeholder for ' . htmlspecialchars($modules[$selectedModules[$i]]) . '</div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endfor; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/dashboard.js"></script>
    <!-- Cytoscape.js CDN (only loaded if Network Topology module is present) -->
    <?php if (in_array('networktopology', $selectedModules)): ?>
    <script src="https://unpkg.com/cytoscape@3.24.0/dist/cytoscape.min.js"></script>
    <?php endif; ?>
</body>
</html>