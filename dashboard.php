<?php
// Move session_start() and all PHP logic to the very top before any HTML output
session_start();

// Register a shutdown function to clean up the temp file if the session is destroyed
register_shutdown_function(function() {
    if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['csvFile'])) {
        if (!isset($_SESSION['keep_csv']) && file_exists($_SESSION['csvFile'])) {
            @unlink($_SESSION['csvFile']);
            unset($_SESSION['csvFile']);
        }
    }
});

// Parse uploaded CSV file
$packets = [];
if (
    isset($_FILES['csvFile']) && is_uploaded_file($_FILES['csvFile']['tmp_name']) && $_FILES['csvFile']['error'] === UPLOAD_ERR_OK
) {
    // Save uploaded file to a temp location and store path in session
    $tmpName = tempnam(sys_get_temp_dir(), 'pktcsv_');
    move_uploaded_file($_FILES['csvFile']['tmp_name'], $tmpName);
    $_SESSION['csvFile'] = $tmpName;
    $csvFile = $tmpName;
} elseif (isset($_POST['csvFile']) && $_POST['csvFile'] && is_readable($_POST['csvFile'])) {
    $csvFile = $_POST['csvFile'];
    $_SESSION['csvFile'] = $csvFile;
} elseif (isset($_SESSION['csvFile']) && is_readable($_SESSION['csvFile'])) {
    $csvFile = $_SESSION['csvFile'];
} elseif (isset($_GET['csvFile'])) {
    $csvFile = $_GET['csvFile'];
    $_SESSION['csvFile'] = $csvFile;
} else {
    $csvFile = null;
}

// Always parse $packets from the session-stored file if possible
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

// List of available modules for selection
$modules = [
    'packetdetails' => 'Packet Details',
    'option1' => 'Option 1',
    'option2' => 'Option 2',
    'option3' => 'Option 3',
    'option4' => 'Option 4',
    'option5' => 'Option 5',
    'option6' => 'Option 6',
    'option7' => 'Option 7',
    'option8' => 'Option 8',
    'option9' => 'Option 9',
    'option10' => 'Option 10',
];

// Get selected modules for each card (from POST or default to packetdetails)
$selectedModules = [];
for ($i = 0; $i < 6; $i++) {
    $selectedModules[$i] = isset($_POST["module$i"]) ? $_POST["module$i"] : 'packetdetails';
}
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
        <h1 class="mb-4 text-center">PacketProbe - Dashboard</h1>
        <div class="row g-3 h-100" id="dashboard-grid" style="min-height: 0;">
            <?php for ($i = 0; $i < 6; $i++): ?>
            <div class="col-12 col-lg-6 d-flex align-items-stretch" style="min-height: 0;">
                <div class="card h-100 bg-dark text-light border-secondary w-100" style="min-height: 0;">
                    <div class="card-body d-flex flex-column overflow-auto" style="min-height: 0; max-height: 45vh;">
                        <form method="post" id="module-select-form-<?php echo $i; ?>">
                            <input type="hidden" name="csvFile" value="<?php echo htmlspecialchars($csvFile ?? ''); ?>">
                            <?php
                            for ($j = 0; $j < 6; $j++) {
                                if ($j !== $i) {
                                    echo '<input type="hidden" name="module' . $j . '" value="' . htmlspecialchars($selectedModules[$j]) . '">';
                                }
                            }
                            ?>
                            <label for="section-dropdown-<?php echo $i; ?>" class="form-label">Section <?php echo $i+1; ?></label>
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
                            if ($selectedModules[$i] === 'packetdetails') {
                                $packets = $originalPackets;
                                include __DIR__ . '/modules/PacketDetails.php';
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
</body>
</html>
