<?php
/*
 * Check che le variabili richieste siano definite
 */
$tabs = empty($tabs) ? [] : $tabs;
$value_id = empty($value_id) ? null : $value_id;
$layout_data_detail = empty($layout_data_detail) ? [] : $layout_data_detail;

$index = 0;
if (isset($_COOKIE['tab-' . $tabs_id])) {
    $index = $_COOKIE['tab-' . $tabs_id];
}

$active = (is_numeric($index) && $index < count($tabs) && $index >= 0) ? array_keys($tabs)[$index] : null;

?>
<!-- <div class="<?php echo $tabs_id; ?> tabbable-custom nav-tabs-custom js-tabs" data-tabid="<?php echo $tabs_id; ?>"> -->
<div class="<?php echo $tabs_id; ?> tabbable-custom card card-primary card-tabs js-tabs" data-tabid="<?php echo $tabs_id; ?>">
    <div class="card-header p-0 pt-1">
        <ul class="nav nav-tabs" role="tablist">
            <?php foreach ($tabs as $key => $tab) : ?>
                <li class="nav-item">
                    <a href="#<?php echo $key; ?>" class="nav-link  <?php echo $active === $key ? 'active' : ''; ?>" data-toggle="pill" role="tab">
                        <?php e($tab['title']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">
            <?php foreach ($tabs as $key => $tab) : ?>
                <div class="tab-pane fade <?php echo $active === $key ? 'active' : ''; ?>" <?php echo sprintf('id="%s"', $key); ?>>
                    <?php echo $tab['content']; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>