<?php
// Expects $packets (array of associative arrays) to be available

if (!isset($packets) || !is_array($packets) || count($packets) === 0) {
    echo '<div class="alert alert-warning">No packet data available.</div>';
    return;
}

$headers = array_keys($packets[0]);
?>
<div class="table-responsive">
    <table class="table table-dark table-striped table-bordered table-sm mb-0 small">
        <thead>
            <tr>
                <?php foreach ($headers as $header): ?>
                    <th><?php echo htmlspecialchars($header); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($packets as $row): ?>
                <tr>
                    <?php foreach ($headers as $header): ?>
                        <td><?php echo htmlspecialchars($row[$header]); ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
