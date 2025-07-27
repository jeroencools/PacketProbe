<?php
// Conversation Matrix: Show communication pairs (src/dst) and packet counts

if (empty($packets) || !is_array($packets)) {
    echo '<div class="text-warning">No packet data available.</div>';
    return;
}

// Defensive: ensure at least one row and both keys exist
$headers = array_keys($packets[0]);
if (!in_array('Source', $headers) || !in_array('Destination', $headers)) {
    echo '<div class="text-danger">Could not detect Source/Destination columns in mapped data.</div>';
    return;
}

$srcCol = 'Source';
$dstCol = 'Destination';

// Build conversation matrix
$conversations = [];
foreach ($packets as $pkt) {
    // Defensive: skip rows missing keys
    if (!isset($pkt[$srcCol]) || !isset($pkt[$dstCol])) continue;
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
    <div class="flex-grow-1 overflow-auto table-responsive">
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