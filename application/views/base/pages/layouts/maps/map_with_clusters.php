<?php
$mapFormId = "clusered_map_form_{$data['maps']['maps_id']}";
$mapId = "map_clusters{$data['maps']['maps_id']}";

$passedId = isset($value_id) ? $value_id : null;
$ajaxURL = base_url("get_ajax/get_map_markers/{$data['maps']['maps_id']}/{$passedId}");
$get_parameters = http_build_query($_GET);

//debug($get_parameters, true);

?>
<div id="results">
    <div class="row mt-30">
        <div class="col-md-12">
            <div <?php echo sprintf('id="%s"', $mapId); ?> class="map-container js_map" data-ajaxurl="<?php echo $ajaxURL; ?>" data-initzoom="<?php echo ($data['maps']['maps_init_zoom']) ?: 5; ?>" data-clusters="1" data-get_parameters="<?php echo $get_parameters; ?>"></div>
        </div>
    </div>
</div>