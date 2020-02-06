<?php
/*
 * Check che le variabili richieste siano definite
 */
$tabs = empty($tabs) ? [] : $tabs;
$value_id = empty($value_id) ? null : $value_id;
$layout_data_detail = empty($layout_data_detail) ? [] : $layout_data_detail;

$index = 0;
if (isset($_COOKIE['tab-'.$tabs_id])) {
    $index = $_COOKIE['tab-'.$tabs_id];
}

$active = (is_numeric($index) && $index < count($tabs) && $index >= 0)? array_keys($tabs)[$index]: null;
?>
<div class="<?php echo $tabs_id; ?> tabbable-custom">
    <ul class="nav nav-tabs">
        <?php foreach($tabs as $key => $tab): ?>
            <li class="<?php echo $active===$key? 'active': ''; ?>"><a href="#<?php echo $key; ?>" data-toggle="tab"><?php echo $tab['title']; ?></a></li>
        <?php endforeach; ?>
    </ul>
    <div class="tab-content">
        <?php foreach($tabs as $key => $tab): ?>
            <div class="tab-pane <?php echo $active===$key? 'active': ''; ?>" <?php echo sprintf('id="%s"', $key); ?>><?php echo $tab['content']; ?></div>
        <?php endforeach; ?>
    </div>
</div>


<script>
    
    $(function() {
        
        // La stessa tab NON deve mai apparire in una stessa pagina più di una
        // volta
        var tabId = <?php echo json_encode($tabs_id); ?>;
        var tabs = $('.' + tabId).filter(':first');
        var tabToggles = $('> ul > li > a', tabs);

        tabToggles.on('click', function() {
            var clicked = $(this);
            var index = tabToggles.index(clicked);
            
            if (index > -1) {
                $.cookie('tab-'+tabId, index, { path: '/' });
            }
        });
        
        tabToggles.on('shown.bs.tab', function (e) {
            $(window).trigger('resize');
        });
        
        
    });
    
</script>