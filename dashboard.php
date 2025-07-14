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
            <?php
            // Parse uploaded CSV file
            $packets = [];
            // Support both POST (file upload) and GET (debug/testing) for CSV
            if (
                isset($_FILES['csvFile']) && is_uploaded_file($_FILES['csvFile']['tmp_name']) && $_FILES['csvFile']['error'] === UPLOAD_ERR_OK
            ) {
                $csvFile = $_FILES['csvFile']['tmp_name'];
            } elseif (isset($_GET['csvFile'])) {
                $csvFile = $_GET['csvFile'];
            } else {
                $csvFile = null;
            }

            if ($csvFile && is_readable($csvFile)) {
                if (($handle = fopen($csvFile, "r")) !== false) {
                    // Read headers (first line)
                    $headers = fgetcsv($handle, 0, ",", '"', "\\");
                    if ($headers && isset($headers[0])) {
                        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
                    }
                    // Read data rows
                    while (($data = fgetcsv($handle, 0, ",", '"', "\\")) !== false) {
                        // Skip empty lines and mismatched rows
                        if (count($data) !== count($headers)) continue;
                        $packets[] = array_combine($headers, $data);
                    }
                    fclose($handle);
                }
            }
            for ($i = 0; $i < 6; $i++): ?>
            <div class="col-12 col-lg-6 d-flex align-items-stretch" style="min-height: 0;">
                <div class="card h-100 bg-dark text-light border-secondary w-100" style="min-height: 0;">
                    <div class="card-body d-flex flex-column overflow-auto" style="min-height: 0; max-height: 45vh;">
                        <?php if ($i === 0): ?>
                            <h5 class="card-title mb-3">Packet Details</h5>
                            <?php include __DIR__ . '/modules/PacketDetails.php'; ?>
                        <?php else: ?>
                            <label for="section-dropdown-<?php echo $i; ?>" class="form-label">Section <?php echo $i+1; ?></label>
                            <select class="form-select section-dropdown bg-dark text-light border-secondary" id="section-dropdown-<?php echo $i; ?>" data-section="<?php echo $i; ?>">
                                <?php for ($j = 1; $j <= 10; $j++): ?>
                                    <option value="option<?php echo $j; ?>">Option <?php echo $j; ?></option>
                                <?php endfor; ?>
                            </select>
                            <div class="section-content mt-3" id="section-content-<?php echo $i; ?>">
                                Placeholder for Option 1
                            </div>
                        <?php endif; ?>
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
</html>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/dashboard.js"></script>
</body>
</html>
