<?php
$mapFormId = "clusered_map_form_{$data['maps']['maps_id']}";
$mapId = "map_clusters{$data['maps']['maps_id']}";
?>
<div id="results">
    <div class="row">
        <form <?php echo sprintf('id="%s"', $mapFormId); ?>>
            <?php add_csrf(); ?>
            <?php foreach ($data['maps_fields'] as $map_field) : ?>
                <?php if ($map_field['maps_fields_type'] !== 'latlng') : ?>
                    <div class="col-md-6">
                        <?php echo $this->datab->build_form_input($map_field); ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
            <div class="clearfix"></div>
            <div class="col-md-6">
                <button class="btn btn-primary">Filtra dati</button>
            </div>
        </form>
    </div>

    <div class="row" style="margin-top: 30px;">
        <div class="col-md-12">
            <div <?php echo sprintf('id="%s"', $mapId); ?> style="height:680px"></div>
        </div>
    </div>
</div>


<script>
    var token = JSON.parse(atob($('body').data('csrf')));
    var token_name = token.name;
    var token_hash = token.hash;
    $(function() {
        'use strict';
        var markers = null;

        var map = L.map('<?php echo $mapId; ?>', {
            scrollWheelZoom: false
        }).setView([42.50, 12.90], 5);

        L.maps[<?php echo json_encode($mapId); ?>] = map;

        L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        $(window).on('resize', function() {
            map.invalidateSize();
        });


        var jqFilterForm = $('#<?php echo $mapFormId; ?>');
        jqFilterForm.on('submit', function(e) {
            e.preventDefault();

            var aoFormData = $(this).serializeArray();
            load_marker(aoFormData);
        });

        function load_marker(where) {
            /***
             * CARICO I MARKER VIA AJAX
             */
            if (!where) {
                where = {
                    [token_name]: token_hash
                };
            } else {
                where.push({
                    "name": token_name,
                    "value": token_hash
                });
            }

            if (markers !== null) {
                map.removeLayer(markers);
            }


            $.ajax({
                type: "POST",
                data: where,
                dataType: "json",

                url: '<?php echo base_url("get_ajax/get_map_markers/{$data['maps']['maps_id']}"); ?>/<?php if (isset($value_id)) echo $value_id; ?>',
                success: function(data) {
                    // Ciclo i Marker
                    var group = new Array();
                    var zoom = map.getZoom();
                    if (zoom < 13) {
                        markers = L.markerClusterGroup({
                            maxClusterRadius: 25
                        });
                    } else {
                        markers = L.layerGroup();
                    }


                    $.each(data, function(i, val) {

                        var html = '<b>' + val.title + '</b><br />';
                        if (typeof val.description !== "undefined") {
                            html += val.description
                        }
                        html += '<br /><a href="' + val.link + '"><?php e('View details'); ?></a>';
                        var icon;
                        if (val.marker) {
                            icon = L.icon({
                                iconUrl: base_url_admin + 'images/markers/' + val.marker,
                                iconSize: [32, 32],
                                iconAnchor: [16, 32]
                            });
                        } else {
                            icon = new L.Icon.Default();
                        }


                        var marker = L.marker([val.lat, val.lon], {
                            icon: icon
                        }).bindPopup(html);
                        markers.addLayer(marker);

                        var coor = L.latLng(val.lat, val.lon);
                        group.push(coor);
                    });

                    map.addLayer(markers);
                    map.fitBounds(group);
                },
                error: function(data) {
                    console.log('Errore nel caricamento dei marker...');
                    console.log(data);
                }
            });
        }



        load_marker();

    });
</script>