<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PacketProbe: Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container py-4">
        <h1 class="mb-4 text-center">PacketProbe - Dashboard</h1>
        <div class="row g-3" id="dashboard-grid">
            <!-- 6 grid sections -->
            <?php for ($i = 0; $i < 6; $i++): ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <label for="section-dropdown-<?php echo $i; ?>" class="form-label">Section <?php echo $i+1; ?></label>
                        <select class="form-select section-dropdown" id="section-dropdown-<?php echo $i; ?>" data-section="<?php echo $i; ?>">
                            <?php for ($j = 1; $j <= 10; $j++): ?>
                                <option value="option<?php echo $j; ?>">Option <?php echo $j; ?></option>
                            <?php endfor; ?>
                        </select>
                        <div class="section-content mt-3" id="section-content-<?php echo $i; ?>">
                            Placeholder for Option 1
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
