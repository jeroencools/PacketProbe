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
    </div>
</body>
</html>
