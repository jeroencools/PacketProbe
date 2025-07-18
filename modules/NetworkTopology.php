<?php
// Sanitize field names for Source/Destination
function getField($pkt, $name) {
    foreach ($pkt as $k => $v) {
        if (strcasecmp(trim($k), $name) === 0) return $v;
    }
    return null;
}
// Extract nodes and edges from packets
$sources = [];
$destinations = [];
$protocols = [];
foreach ($packets as $pkt) {
    foreach ($pkt as $k => $v) {
        $keyLower = strtolower(trim($k));
        if ($keyLower === 'source') $sources[$v] = true;
        if ($keyLower === 'destination') $destinations[$v] = true;
        if ($keyLower === 'protocol') $protocols[$v] = true;
    }
}

$nodes = [];
$edges = [];
foreach ($packets as $pkt) {
    $src = getField($pkt, 'Source');
    $dst = getField($pkt, 'Destination');
    // Only add if both are valid IPs or non-empty
    if ($src && $dst) {
        if (!isset($nodes[$src])) $nodes[$src] = true;
        if (!isset($nodes[$dst])) $nodes[$dst] = true;
        $edges[] = ['data' => ['source' => $src, 'target' => $dst]];
    }
}

// Count packets per node (as source or destination)
$nodeCounts = [];
foreach ($packets as $pkt) {
    $src = getField($pkt, 'Source');
    $dst = getField($pkt, 'Destination');
    if ($src && $dst) {
        if (!isset($nodeCounts[$src])) $nodeCounts[$src] = 0;
        if (!isset($nodeCounts[$dst])) $nodeCounts[$dst] = 0;
        $nodeCounts[$src]++;
        $nodeCounts[$dst]++;
    }
}
$cyId = 'vis-network-' . uniqid();
$detailsId = 'vis-details-' . uniqid();

// Build edge details mapping: for each edge, collect all packets with matching Source/Destination
$edgeDetails = [];
foreach ($edges as $e) {
    $from = $e['data']['source'];
    $to = $e['data']['target'];
    $key = $from . '->' . $to;
    $edgeDetails[$key] = [];
    foreach ($packets as $pkt) {
        if (getField($pkt, 'Source') === $from && getField($pkt, 'Destination') === $to) {
            $edgeDetails[$key][] = $pkt;
        }
    }
}

// Output all packets as a JS variable
echo '<script>';
echo 'var allPackets = ';
echo json_encode($packets, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
echo ';';
echo '</script>';
?>
<!-- Vis Network CDN -->
<script src="https://unpkg.com/vis-network@9.1.2/dist/vis-network.min.js"></script>
<link href="https://unpkg.com/vis-network@9.1.2/dist/vis-network.min.css" rel="stylesheet" />
<style>
.vis-topology-flex {
    display: flex;
    flex-direction: row;
    height: 100%;
    width: 100%;
}
.vis-topology-flex.narrow {
    flex-direction: column;
}
</style>
<div id="vis-topology-flex-<?php echo $cyId; ?>" class="vis-topology-flex h-100 w-100" style="height:100%;width:100%;">
    <div id="<?php echo $cyId; ?>" class="h-100 w-100 flex-grow-1" style="height:100%;width:0;background:#222;border-radius:8px;min-width:200px;min-height:200px;display:block;"></div>
    <div id="<?php echo $detailsId; ?>" class="ms-3 flex-shrink-1" style="width:340px;max-width:340px;max-height:100%;overflow:auto;"></div>
</div>
<?php if (!empty($nodes) && !empty($edges)): ?>
<div class="text-secondary mb-1" style="font-size:0.95em;">
    Nodes: <?php echo count($nodes); ?>, Edges: <?php echo count($edges); ?>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var container = document.getElementById('<?php echo $cyId; ?>');
    var detailsContainer = document.getElementById('<?php echo $detailsId; ?>');
    var flexContainer = document.getElementById('vis-topology-flex-<?php echo $cyId; ?>');
    if (!container || typeof vis === 'undefined' || typeof vis.Network === 'undefined') {
        console.warn('vis-network not loaded or container missing');
        return;
    }
    // Responsive layout: switch flex direction based on card width
    function updateFlexLayout() {
        if (flexContainer.offsetWidth < 600) {
            flexContainer.classList.add('narrow');
            detailsContainer.classList.remove('ms-3');
            detailsContainer.style.width = '100%';
            detailsContainer.style.maxWidth = '100%';
            detailsContainer.style.marginLeft = '0';
            container.style.width = '100%';
        } else {
            flexContainer.classList.remove('narrow');
            detailsContainer.classList.add('ms-3');
            // Set details and topology to each take 50% of the card width
            var halfWidth = Math.floor(flexContainer.offsetWidth / 2);
            detailsContainer.style.width = '50%';
            detailsContainer.style.maxWidth = '50%';
            detailsContainer.style.marginLeft = '';
            container.style.width = '50%';
            container.style.maxWidth = '50%';
        }
        // Resize network canvas to fit parent
        var parent = container.parentElement;
        if (parent) {
            container.style.height = parent.offsetHeight + 'px';
        }
    }
    updateFlexLayout();
    window.addEventListener('resize', updateFlexLayout);

    // Prepare nodes with dynamic size and color based on packet count
    var nodes = [
        <?php
        // Find min/max for scaling
        $minSize = 12;
        $maxSize = 35;
        $minCount = $nodeCounts ? min($nodeCounts) : 1;
        $maxCount = $nodeCounts ? max($nodeCounts) : 1;
        // Node color and size based on total traffic (in+out)
        function trafficColor($count, $min, $max) {
            if ($max > $min) {
                $ratio = ($count - $min) / ($max - $min);
                if ($ratio > 0.66) return '#dc3545'; // red
                if ($ratio > 0.33) return '#fd7e14'; // orange
                return '#28a745'; // green
            }
            return '#28a745'; // green
        }
        foreach (array_keys($nodes) as $n) {
            $count = $nodeCounts[$n] ?? 1;
            // Node size based on traffic
            $size = $minSize;
            if ($maxCount > $minCount) {
                $size = $minSize + ($maxSize - $minSize) * (($count - $minCount) / ($maxCount - $minCount));
            }
            $color = trafficColor($count, $minCount, $maxCount);
            echo "{ id: \"" . addslashes($n) . "\", label: \"" . addslashes($n) . "\", color: '$color', font: { color: '#fff', size: 14 }, value: " . round($size, 1) . " },";
        }
        ?>
    ];
    // Prepare edges
    var edges = [
        <?php
        $edgeSet = [];
        $edgePacketCounts = [];
        foreach ($edges as $e) {
            $from = $e['data']['source'];
            $to = $e['data']['target'];
            $key = $from . '->' . $to;
            if (!isset($edgeSet[$key])) {
                $edgeSet[$key] = true;
                // Edge traffic is number of packets for this connection
                $count = count($edgeDetails[$key]);
                $edgePacketCounts[$key] = $count;
            }
        }
        $minEdgeWidth = 1;
        $maxEdgeWidth = 6;
        $minEdgeCount = $edgePacketCounts ? min($edgePacketCounts) : 1;
        $maxEdgeCount = $edgePacketCounts ? max($edgePacketCounts) : 1;
        // Edge color and width based on traffic for this connection
        foreach ($edgeSet as $key => $_) {
            list($from, $to) = explode('->', $key, 2);
            $count = $edgePacketCounts[$key];
            $width = $minEdgeWidth;
            if ($maxEdgeCount > $minEdgeCount) {
                $width = $minEdgeWidth + ($maxEdgeWidth - $minEdgeWidth) * (($count - $minEdgeCount) / ($maxEdgeCount - $minEdgeCount));
            }
            $color = trafficColor($count, $minEdgeCount, $maxEdgeCount);
            echo "{ from: \"" . addslashes($from) . "\", to: \"" . addslashes($to) . "\", color: { color: '" . $color . "' }, arrows: 'to', id: \"" . addslashes($key) . "\", width: " . round($width, 1) . " },";
        }
        ?>
    ];
    // Packet details for each edge (from PHP to JS)
    var edgeDetails = {
        <?php
        foreach ($edgeDetails as $key => $packets) {
            echo '"' . addslashes($key) . '": [';
            foreach ($packets as $pkt) {
                echo json_encode($pkt, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ',';
            }
            echo '],';
        }
        ?>
    };

    var data = { nodes: nodes, edges: edges };
    var options = {
        nodes: { shape: 'dot', scaling: { min: 12, max: 30 }, size: 12 },
        edges: {
            smooth: {
                enabled: true,
                type: 'curvedCW', // Use curved lines (clockwise)
                roundness: 0.3    // Increase curvature (0.0 = straight, 1.0 = max curve)
            }
        },
        physics: {
            stabilization: true,
            barnesHut: {
                gravitationalConstant: -20000, // Move nodes further apart (more negative = more repulsion)
                springLength: 120,
                springConstant: 0.04,
                damping: 0.09
            }
        }
    };
    var network = new vis.Network(container, data, options);

    // Edge and node click handler
    network.on("click", function(params) {
        // Prioritize node selection if both node and edge are selected
        if (params.nodes && params.nodes.length > 0) {
            // Node selected: show all packets where node is source or destination
            var nodeId = params.nodes[0];
            var normNodeId = (nodeId + '').trim().toLowerCase();
            console.log('Node selected:', nodeId, 'Normalized:', normNodeId);

            // Filter allPackets in JS
            var filteredPackets = allPackets.filter(function(pkt) {
                var src = pkt['Source'] !== undefined ? pkt['Source'] : pkt['source'];
                var dst = pkt['Destination'] !== undefined ? pkt['Destination'] : pkt['destination'];
                var srcNorm = src ? (src + '').trim().toLowerCase() : '';
                var dstNorm = dst ? (dst + '').trim().toLowerCase() : '';
                return srcNorm === normNodeId || dstNorm === normNodeId;
            });

            console.log('All matching packets:', filteredPackets);
            var html = '<div class="card bg-dark border-secondary mb-2"><div class="card-header text-info">All traffic for <b>' +
                nodeId + '</b></div><div class="card-body p-2">';
            if (filteredPackets.length > 0) {
                html += '<table class="table table-sm table-dark table-bordered mb-0"><thead><tr>';
                var firstPkt = filteredPackets[0];
                for (var k in firstPkt) {
                    html += '<th>' + k + '</th>';
                }
                html += '</tr></thead><tbody>';
                filteredPackets.forEach(function(pkt) {
                    html += '<tr>';
                    for (var k in pkt) {
                        if (k.toLowerCase() === 'protocol') {
                            html += '<td><span class="protocol-link" data-proto="' + pkt[k] + '">' + pkt[k] + '</span></td>';
                        } else {
                            html += '<td>' + pkt[k] + '</td>';
                        }
                    }
                    html += '</tr>';
                });
                html += '</tbody></table>';
            } else {
                html += '<div class="text-secondary">No packet details found for this node.</div>';
            }
            html += '</div></div>';
            detailsContainer.innerHTML = html;
        } else if (params.edges && params.edges.length > 0) {
            // Edge selected: show packets for that connection
            var edgeId = params.edges[0];
            var edgeObj = data.edges.find(function(e) { return e.id === edgeId; });
            if (edgeObj && edgeDetails[edgeObj.id] && edgeDetails[edgeObj.id].length > 0) {
                var packets = edgeDetails[edgeObj.id];
                var html = '<div class="card bg-dark border-secondary mb-2"><div class="card-header text-info">Packets from <b>' +
                    edgeObj.from + '</b> to <b>' + edgeObj.to + '</b></div><div class="card-body p-2">';
                if (packets.length > 0) {
                    html += '<table class="table table-sm table-dark table-bordered mb-0"><thead><tr>';
                    var firstPkt = packets[0];
                    for (var k in firstPkt) {
                        html += '<th>' + k + '</th>';
                    }
                    html += '</tr></thead><tbody>';
                    packets.forEach(function(pkt) {
                        html += '<tr>';
                        for (var k in pkt) {
                            html += '<td>' + pkt[k] + '</td>';
                        }
                        html += '</tr>';
                    });
                    html += '</tbody></table>';
                } else {
                    html += '<div class="text-secondary">No packet details found.</div>';
                }
                html += '</div></div>';
                detailsContainer.innerHTML = html;
            } else {
                detailsContainer.innerHTML = '<div class="text-secondary">No packet details found.</div>';
            }
        } else {
            detailsContainer.innerHTML = '';
        }
    });
});
</script>
<?php else: ?>
<div class="text-secondary mt-2">No network topology data found.</div>
<?php endif; ?>
</script>
<style>
  .protocol-link {
    color: #4ea1f7;
    text-decoration: underline;
    cursor: pointer;
    font-weight: 500;
    transition: color 0.15s;
  }
  .protocol-link:hover {
    color: #1d72b8;
    text-decoration: underline;
  }
</style>
<!-- Protocol explanation modal (required for clickable protocol links) -->
<div id="protocol-modal-backdrop" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.75);z-index:9998;"></div>
<div id="protocol-modal" style="display:none;position:fixed;top:20%;left:50%;transform:translate(-50%,0);background:#222;color:#fff;padding:20px;border-radius:8px;z-index:9999;min-width:300px;">
  <div id="protocol-modal-content"></div>
</div>
<!-- Protocol explanations JS (required for clickable protocol links) -->
<script src="assets/js/protocols.js"></script>
