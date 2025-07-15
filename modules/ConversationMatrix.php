<?php
// Conversation Matrix: Show communication pairs (src/dst) and packet counts

if (empty($packets) || !is_array($packets)) {
    echo '<div class="text-warning">No packet data available.</div>';
    return;
}

// Try to detect source/destination IP columns
$srcKeys = ['src', 'source', 'ip.src', 'Source', 'Src', 'Source IP', 'ip source'];
$dstKeys = ['dst', 'destination', 'ip.dst', 'Destination', 'Dst', 'Destination IP', 'ip destination'];

// Avoid function redeclaration if this module is included multiple times
if (!function_exists('conversationmatrix_findKey')) {
    function conversationmatrix_findKey($headers, $candidates) {
        foreach ($candidates as $cand) {
            foreach ($headers as $h) {
                if (strcasecmp($h, $cand) === 0) return $h;
            }
        }
        return null;
    }
}

$headers = array_keys($packets[0]);
$srcCol = conversationmatrix_findKey($headers, $srcKeys);
$dstCol = conversationmatrix_findKey($headers, $dstKeys);

if (!$srcCol || !$dstCol) {
    echo '<div class="text-danger">Could not detect source/destination IP columns in CSV.</div>';
    return;
}

// Build conversation matrix
$conversations = [];
foreach ($packets as $pkt) {
    $src = $pkt[$srcCol];
    $dst = $pkt[$dstCol];
    if (!isset($conversations[$src])) $conversations[$src] = [];
    if (!isset($conversations[$src][$dst])) $conversations[$src][$dst] = 0;
    $conversations[$src][$dst]++;
}

// Flatten to array for sorting
$matrixRows = [];
foreach ($conversations as $src => $dsts) {
    foreach ($dsts as $dst => $count) {
        $matrixRows[] = [
            'src' => $src,
            'dst' => $dst,
            'count' => $count
        ];
    }
}

// Handle sort order (default: descending)
$sortOrder = isset($_POST['conversationmatrix_sort']) && $_POST['conversationmatrix_sort'] === 'asc' ? 'asc' : 'desc';
usort($matrixRows, function($a, $b) use ($sortOrder) {
    if ($a['count'] === $b['count']) return 0;
    return ($sortOrder === 'asc')
        ? $a['count'] - $b['count']
        : $b['count'] - $a['count'];
});

// Output filter and table, use flex utilities for full height
?>
<div class="d-flex flex-column h-100">
    <form method="post" class="mb-2">
        <div class="d-flex align-items-center gap-2">
            <label for="conversationmatrix_sort" class="form-label mb-0">Sort by packets:</label>
            <select name="conversationmatrix_sort" id="conversationmatrix_sort" class="form-select form-select-sm w-auto"
                onchange="this.form.submit()">
                <option value="desc" <?php if ($sortOrder === 'desc') echo 'selected'; ?>>Descending</option>
                <option value="asc" <?php if ($sortOrder === 'asc') echo 'selected'; ?>>Ascending</option>
            </select>
            <?php
            // Preserve other POST fields (csvFile, layout, module selections)
            foreach ($_POST as $k => $v) {
                if ($k === 'conversationmatrix_sort') continue;
                if (is_array($v)) continue;
                echo '<input type="hidden" name="' . htmlspecialchars($k) . '" value="' . htmlspecialchars($v) . '">';
            }
            ?>
        </div>
    </form>
    <div class="flex-grow-1 overflow-auto">
        <table class="table table-sm table-dark table-bordered align-middle mb-0">
            <thead>
                <tr>
                    <th>Source</th>
                    <th>Destination</th>
                    <th class="text-end">Packets</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($matrixRows as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['src']); ?></td>
                    <td><?php echo htmlspecialchars($row['dst']); ?></td>
                    <td class="text-end"><?php echo $row['count']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
