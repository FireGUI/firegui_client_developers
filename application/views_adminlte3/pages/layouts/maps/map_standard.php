<?php
$id = "map_standard{$data['maps']['maps_id']}";
$passedId = isset($value_id) ? $value_id : null;
$ajaxURL = base_url("get_ajax/get_map_markers/{$data['maps']['maps_id']}/{$passedId}");
?>
<div class="map-standard js_map" <?php echo sprintf('id="%s"', $id); ?> data-ajaxurl="<?php echo $ajaxURL; ?>" data-initzoom="<?php echo ($data['maps']['maps_init_zoom']) ?: 5; ?>"></div>