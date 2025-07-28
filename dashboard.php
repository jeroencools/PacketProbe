<?php
// Move session_start() and all PHP logic to the very top before any HTML output
session_start();

// Require column mapping before showing dashboard
if (!isset($_SESSION['csvFile']) || !is_readable($_SESSION['csvFile'])) {
    header('Location: index.php');
    exit;
}
if (!isset($_SESSION['csv_mapping'])) {
    header('Location: map_columns.php');
    exit;
}
$csv_mapping = $_SESSION['csv_mapping'];

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
}

// Accept mapping from POST (from map_columns.php) and update session
if (isset($_POST['mapping']) && is_array($_POST['mapping'])) {
    $_SESSION['csv_mapping'] = $_POST['mapping'];
    $csv_mapping = $_SESSION['csv_mapping'];
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

// Remap $originalPackets to use expected keys (Time, Source, etc) using mapping
if (!empty($originalPackets) && isset($csv_mapping) && is_array($csv_mapping)) {
    $remappedPackets = [];
    foreach ($originalPackets as $row) {
        $remapped = [];
        foreach ($csv_mapping as $expected => $actual) {
            $remapped[$expected] = isset($row[$actual]) ? $row[$actual] : '';
        }
        $remappedPackets[] = $remapped;
    }
    $originalPackets = $remappedPackets;
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

// Add card height options
$cardHeightOptions = [
    'standard' => ['label' => 'Standard (65vh)', 'value' => '65vh'],
    'tall' => ['label' => 'Tall (80vh)', 'value' => '80vh'],
];
// Preserve card height selection across POSTs
if (isset($_POST['cardHeight']) && isset($cardHeightOptions[$_POST['cardHeight']])) {
    $_SESSION['cardHeight'] = $_POST['cardHeight'];
}
$selectedCardHeight = isset($_SESSION['cardHeight']) ? $_SESSION['cardHeight'] : 'standard';
$cardHeightValue = $cardHeightOptions[$selectedCardHeight]['value'];
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
    <style>
    /* Inline override for card height based on dropdown */
    #dashboard-grid .col-12,
    #dashboard-grid .col-lg-6 {
        height: <?php echo htmlspecialchars($cardHeightValue); ?> !important;
    }
    .dashboard-controls {
        background: none !important;
        margin-top: 0.5rem !important;
        margin-bottom: 0.5rem !important;
        padding-top: 1.5rem !important;
        padding-bottom: 1.5rem !important;
    }
    </style>
</head>
<body class="bg-dark text-light vh-100<?php
    // Add light-mode class server-side if needed
    if (
        (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'light') ||
        (!isset($_COOKIE['theme']) && isset($_SERVER['HTTP_SEC_CH_PREFERS_COLOR_SCHEME']) && $_SERVER['HTTP_SEC_CH_PREFERS_COLOR_SCHEME'] === 'light')
    ) {
        echo ' light-mode';
    }
?>" id="body-root">
    <!-- Dark/Light mode toggle (top left) -->
    <div style="position: absolute; top: 18px; left: 18px; z-index: 10;">
        <label class="form-switch d-flex align-items-center gap-2" style="user-select:none;">
            <input type="checkbox" id="themeToggle" class="form-check-input" style="width:2em;height:1em;">
            <span id="themeLabel" style="font-size:1rem;">🌙</span>
        </label>
    </div>
    <div class="container min-vh-100 d-flex flex-column justify-content-center align-items-center py-4">
        <div class="card shadow-lg p-4 bg-dark text-light border-secondary w-100" style="max-width: 1400px;">
            <div class="d-flex flex-column flex-md-row align-items-center justify-content-between mb-3 gap-2">
                <h1 class="mb-0 text-center flex-grow-1">PacketProbe - Dashboard</h1>
                <a href="index.php" class="btn btn-secondary ms-md-3" style="min-width: 90px;">&larr; Back</a>
            </div>
            <div class="dashboard-controls d-flex flex-wrap align-items-center gap-2 py-3 px-2 rounded-3 mx-auto flex-row"
                 style="margin-top: 0.5rem; margin-bottom: 0.5rem; max-width: 100%; background: none !important;">
                <div class="me-md-3 d-flex align-items-center mb-0 justify-content-center flex-shrink-0">
                    <?php echo $parseStatusMsg; ?>
                </div>
                <form method="post" class="d-flex flex-wrap align-items-center gap-2 m-0 p-0 justify-content-center" id="layout-form" style="font-size: 0.95rem;">
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
                    <select name="layout" id="layout" class="form-select w-auto d-inline-block me-2" onchange="this.form.submit()" style="margin-bottom:0;">
                        <option value="2x3" <?php if ($selectedLayout === '2x3') echo 'selected'; ?>>Standard (2 rows x 3 columns)</option>
                        <option value="3x2" <?php if ($selectedLayout === '3x2') echo 'selected'; ?>>Wide (3 rows x 2 columns)</option>
                        <option value="6x1" <?php if ($selectedLayout === '6x1') echo 'selected'; ?>>Single Column (6 rows x 1 column)</option>
                    </select>
                    <label for="cardHeight" class="me-2 mb-0">Card Height:</label>
                    <select name="cardHeight" id="cardHeight" class="form-select w-auto d-inline-block" onchange="this.form.submit()" style="margin-bottom:0;">
                        <?php foreach ($cardHeightOptions as $key => $opt): ?>
                            <option value="<?php echo htmlspecialchars($key); ?>" <?php if ($selectedCardHeight === $key) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($opt['label']); ?>
                            </option>
                        <?php endforeach; ?>
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
    <div class="card h-100 bg-dark text-light border-secondary w-100" style="min-height: 0; ">
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
                    // Build a set of modules already selected in other cards
                    $usedModules = [];
                    for ($j = 0; $j < $totalCards; $j++) {
                        if ($j !== $i && isset($selectedModules[$j]) && $selectedModules[$j] !== 'empty') {
                            $usedModules[$selectedModules[$j]] = true;
                        }
                    }
                    foreach ($modules as $key => $label) {
                        // Always allow 'empty', otherwise skip if used in another card
                        if ($key !== 'empty' && isset($usedModules[$key])) continue;
                        $selected = ($selectedModules[$i] === $key) ? 'selected' : '';
                        echo "<option value=\"$key\" $selected>$label</option>";
                    }
                    ?>
                </select>
            </form>
            <div class="section-content mt-1" id="section-content-<?php echo $i; ?>">
                <?php

// Remap $originalPackets to use expected keys (Time, Source, etc) using mapping
if (!empty($originalPackets) && isset($csv_mapping) && is_array($csv_mapping)) {
    $remappedPackets = [];
    foreach ($originalPackets as $row) {
        $remapped = [];
        foreach ($csv_mapping as $expected => $actual) {
            $remapped[$expected] = isset($row[$actual]) ? $row[$actual] : '';
        }
        $remappedPackets[] = $remapped;
    }
    $originalPackets = $remappedPackets;
}
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
        <footer class="mt-4 text-center text-secondary small">
            <a href="https://jeroenict.be" target="_blank" rel="noopener">
                <img src="assets/img/logo-transp-green.png" alt="Jeroen ICT Logo" style="height: 40px; opacity: 0.9; border-radius: 0.5rem;">
            </a>
        </footer>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/dashboard.js"></script>
    <!-- Cytoscape.js CDN (only loaded if Network Topology module is present) -->
    <?php if (in_array('networktopology', $selectedModules)): ?>
    <script src="https://unpkg.com/cytoscape@3.24.0/dist/cytoscape.min.js"></script>
    <?php endif; ?>

    <!-- Dark/Light mode toggle logic -->
    <script>
    // Theme toggle logic
    document.addEventListener("DOMContentLoaded", function() {
        const body = document.getElementById('body-root');
        const toggle = document.getElementById('themeToggle');
        const label = document.getElementById('themeLabel');
        // Use cookie for persistence across reloads
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
        // Load theme from cookie
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