<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PacketProbe - Upload</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body id="body-root" class="bg-dark text-light">
    <!-- Dark/Light mode toggle (top left) -->
    <div style="position: absolute; top: 18px; left: 18px; z-index: 10;">
        <label class="form-switch d-flex align-items-center gap-2" style="user-select:none;">
            <input type="checkbox" id="themeToggle" class="form-check-input" style="width:2em;height:1em;">
            <span id="themeLabel" style="font-size:1rem;">🌙</span>
        </label>
    </div>
    <div class="container min-vh-100 d-flex flex-column justify-content-center align-items-center py-4">
        <div class="card shadow-lg p-4 bg-dark text-light border-secondary" style="max-width: 420px; width: 100%;">
            <h1 class="mb-4 text-center">PacketProbe</h1>
            <form action="map_columns.php" method="post" enctype="multipart/form-data" class="mb-3">
                <div class="mb-1 text-center">
                    <button type="button" class="badge text-dark border-0 px-2 py-1 fst-italic" data-bs-toggle="modal" data-bs-target="#csvColumnsModal" style="cursor:pointer; font-size:0.78rem; letter-spacing:0.01em; font-style:italic;">
                        Which columns should my CSV contain?
                    </button>
                </div>
                <div class="mb-3">
                    <label for="csvFile" class="form-label">Upload Network Traffic (.csv)</label>
                    <input class="form-control" type="file" id="csvFile" name="csvFile" accept=".csv" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Upload</button>
            </form>
            <div class="text-center my-3 text-secondary">or</div>
            <form action="randomcsv.php" method="get">
                <input type="hidden" name="download" value="1">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" value="1" id="addAnomalies" name="anomalies">
                    <label class="form-check-label" for="addAnomalies">
                        Add demo anomalies to generated CSV
                    </label>
                </div>
                <button type="submit" class="btn btn-outline-secondary w-100">Create a random CSV</button>
            </form>
        </div>
        <footer class="mt-4 text-center text-secondary small">
            <a href="https://jeroenict.be" target="_blank" rel="noopener">
                <img src="assets/img/logo-transp-green.png" alt="Jeroen ICT Logo" style="height: 40px; opacity: 0.9; border-radius: 0.5rem;">
            </a>
        </footer>
    </div>
    <!-- CSV Columns Modal -->
    <div class="modal fade" id="csvColumnsModal" tabindex="-1" aria-labelledby="csvColumnsModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content bg-dark text-light border-secondary">
          <div class="modal-header">
            <h5 class="modal-title" id="csvColumnsModalLabel">Expected CSV Columns</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Your CSV file should contain the following fields (names do <strong>not</strong> have to match exactly):</p>
            <p class="mb-2"><small class="text-secondary">After upload, you can map your CSV columns to these required fields.</small>
        <small class="text-secondary">Extra columns are allowed, but these are required for full functionality.</small>
        </p>
            
            <ul class="list-group mb-2">
              <li class="list-group-item bg-dark text-info border-info">No.</li>
              <li class="list-group-item bg-dark text-info border-info">Time</li>
              <li class="list-group-item bg-dark text-info border-info">Source</li>
              <li class="list-group-item bg-dark text-info border-info">Source Port</li>
              <li class="list-group-item bg-dark text-info border-info">Destination</li>
              <li class="list-group-item bg-dark text-info border-info">Destination Port</li>
              <li class="list-group-item bg-dark text-info border-info">Protocol</li>
              <li class="list-group-item bg-dark text-info border-info">Length</li>
              <li class="list-group-item bg-dark text-info border-info">Info</li>
            </ul>

          </div>
        </div>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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
