<?php
// Funzione per ordinare le versioni per data decrescente
usort($module['folders'], function ($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});
?>

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Changelog</h1>
</section>
<!-- Main content -->
<section class="content container-fluid">
    <?php foreach ($module['folders'] as $folder):
        $is_current_version = ($folder['name'] == $current_module_version_code);
        ?>
        <div class="box <?php echo $is_current_version ? 'bg-green' : 'box-default'; ?>">
            <div class="box-header with-border">
                <h3 class="box-title <?php echo $is_current_version ? 'text-bold' : ''; ?>">
                    Version code: <?php echo htmlspecialchars($folder['name'], ENT_QUOTES, 'UTF-8'); ?>
                </h3>
            </div>
            <div class="box-body">
                <ul>
                    <li>Date: <?php echo htmlspecialchars($folder['date'], ENT_QUOTES, 'UTF-8'); ?></li>
                </ul>
            </div>
        </div>
    <?php endforeach; ?>
</section>