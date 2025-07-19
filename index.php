<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PacketProbe - Upload</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container d-flex flex-column justify-content-center align-items-center min-vh-100">
        <h1 class="mb-4">PacketProbe</h1>
        <form action="dashboard.php" method="post" enctype="multipart/form-data" class="w-100" style="max-width:400px;">
            <div class="mb-3">
                <label for="csvFile" class="form-label">Upload Network Traffic (.csv)</label>
                <input class="form-control" type="file" id="csvFile" name="csvFile" accept=".csv" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Upload</button>
        </form>
        <form action="randomcsv.php" method="get" class="w-100 mt-3" style="max-width:400px;">
            <input type="hidden" name="download" value="1">
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" value="1" id="addAnomalies" name="anomalies">
                <label class="form-check-label" for="addAnomalies">
                    Add demo anomalies to generated CSV
                </label>
            </div>
            <button type="submit" class="btn btn-outline-secondary w-100">Create a random CSV</button>
        </form>
<div style="width: 100%; text-align: center; margin-top: 48px; margin-bottom: 16px;">
    <a href="https://jeroenict.be" target="_blank" rel="noopener">
        <img src="assets/img/logo-transp-green.png" alt="Jeroen ICT Logo" style="height: 48px; opacity: 0.85; transition: opacity 0.2s; border-radius: 0.5rem;">
    </a>
</div>
    </div>


</body>
</html>
