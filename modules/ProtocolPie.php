<?php
// Expects $packets (array of associative arrays) to be available

// $packets is already mapped to expected keys by dashboard.php, so no further mapping is needed here

if (!isset($packets) || !is_array($packets) || count($packets) === 0) {
    echo '<div class="alert alert-warning">No packet data available.</div>';
    return;
}

// Count protocols
$protocolCounts = [];
foreach ($packets as $row) {
    $proto = isset($row['Protocol']) ? $row['Protocol'] : 'Unknown';
    $protocolCounts[$proto] = isset($protocolCounts[$proto]) ? $protocolCounts[$proto] + 1 : 1;
}
$labels = array_keys($protocolCounts);
$data = array_values($protocolCounts);
$chartId = 'protocolPieChart_' . uniqid();
?>
<div class="protocol-pie-container" style="flex:1 1 auto; display:flex; flex-direction:column; justify-content:stretch; align-items:stretch; min-height:0; height:100%;">
    <canvas id="<?php echo $chartId; ?>" style="width:100% !important; height:100% !important; flex:1 1 auto; min-height:0;"></canvas>
</div>
<?php
if (empty($GLOBALS['chartjs_loaded'])) {
    $GLOBALS['chartjs_loaded'] = true;
    // Load Chart.js and datalabels plugin
    echo '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
    echo '<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>';
} elseif (empty($GLOBALS['chartjs_datalabels_loaded'])) {
    // Only load datalabels plugin if not loaded yet
    echo '<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>';
}
$GLOBALS['chartjs_datalabels_loaded'] = true;
?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    var ctx = document.getElementById('<?php echo $chartId; ?>').getContext('2d');
    // Detect light/dark mode
    function isLightMode() {
        return document.body.classList.contains('light-mode');
    }
    function getLegendColor() {
        return isLightMode() ? '#23272b' : '#f8f9fa';
    }
    function getLabelColor() {
        return isLightMode() ? '#23272b' : '#fff';
    }
    var chart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                data: <?php echo json_encode($data); ?>,
                backgroundColor: [
                    '#4e79a7', '#f28e2b', '#e15759', '#76b7b2', '#59a14f',
                    '#edc949', '#af7aa1', '#ff9da7', '#9c755f', '#bab0ab'
                ]
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            plugins: {
                legend: {
                    labels: { color: getLegendColor() }
                },
                datalabels: {
                    color: getLabelColor(),
                    font: { weight: 'bold' },
                    formatter: function(value, context) {
                        var sum = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                        var pct = sum ? (value / sum * 100) : 0;
                        return pct.toFixed(1) + '%';
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
    });

    // Update chart colors on theme change
    function updateChartColors() {
        chart.options.plugins.legend.labels.color = getLegendColor();
        chart.options.plugins.datalabels.color = getLabelColor();
        chart.update();
    }
    // Listen for theme changes
    var observer = new MutationObserver(updateChartColors);
    observer.observe(document.body, { attributes: true, attributeFilter: ['class'] });
});
</script>
<style>
/* filepath: c:\Users\Jeroen\OneDrive\ICT\5. Code\Webdev\PacketProbe\modules\ProtocolPie.php */
.protocol-pie-container {
    background: transparent;
    color: #f8f9fa;
}
body.light-mode .protocol-pie-container {
    background: transparent;
    color: #23272b;
}
</style>
