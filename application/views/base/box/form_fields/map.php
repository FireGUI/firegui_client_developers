<?php
$settings = $this->apilib->searchFirst('settings');

$company_lat = (!empty($settings['settings_company_position']['lat'])) ? $settings['settings_company_position']['lat'] : 40.725249;
$company_lng = (!empty($settings['settings_company_position']['lng'])) ? $settings['settings_company_position']['lng'] : -74.140363;

$map = "js_map_container_{$field['fields_id']}_map" . ($lang ? "_{$lang}" : '');
$input = "js_map_container_{$field['fields_id']}_input" . ($lang ? "_{$lang}" : '');

if ($value) {
    if (is_array($value)) {

        $value_latlon = $value;
        $lat = $value_latlon['lat'];
        $lon = isset($value_latlon['lon']) ? $value_latlon['lon'] : $value_latlon['lng'];
        $value = trim($lat . ';' . $lon, ';') ?: null;
    } else {
        if ($this->db->dbdriver == 'postgre') {
            $value_latlon = $this->db->query("SELECT ST_Y('{$value}'::geometry) AS lat, ST_X('{$value}'::geometry) AS lon")->row_array();
            if (!empty($value_latlon)) {
                $lat = $value_latlon['lat'];
                $lon = isset($value_latlon['lon']) ? $value_latlon['lon'] : $value_latlon['lng'];
                $value = trim($lat . ';' . $lon, ';') ?: null;
            }
        } else {
            $exploded = explode(';', $value);
            $lat = $exploded[0];
            $lon = $exploded[1];
        }
    }
}
?>
<?php echo $label; ?>

<input <?php echo "id='{$input}'"; ?> type="hidden" name="<?php echo $field['fields_name']; ?>" class="<?php echo $class ?>" value="<?php echo $value; ?>" data-dependent_on="<?php echo $field['forms_fields_dependent_on']; ?>" />
<div class="location-search-container">
    <div class="input-group">
        <input type="text" class="form-control js_map_search" placeholder="<?php e('find localtion') ?>" />
        <span class="input-group-btn">
            <button class="btn btn-default btn-search" type="button"><i class="fas fa-search"></i></button>
        </span>
    </div>
    <div class="location-map-container" <?php echo "id='{$map}'"; ?> <?php echo $onclick; ?>></div>
</div>
<?php echo $help; ?>

<script>
    $(document).ready(function() {
        'use strict';

        var map = null;

        var lat = <?php echo $company_lat; ?>;
        var lng = <?php echo $company_lng; ?>;

        $('#<?php echo $map; ?>').on('resize', function() {
            'use strict';
            if (map !== null) {
                map.invalidateSize();
            }
        });

        $('.js_map_search').on('keyup', function(e) {
            e.preventDefault();
            if (e.keyCode == 32) { // pressed spacebar
                $('.btn-search').trigger('click');
            }
        });

        setTimeout(function() {
            'use strict';
            var w = $('#<?php echo $map; ?>').width();
            if (w > 0) {
                $('#<?php echo $map; ?>').height(w);
            }
        }, 1000);

        map = L.map('<?php echo $map; ?>', {
            center: new L.LatLng(lat, lng),
            zoom: 14,
            layers: [
                L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
                })
            ],
            minZoom: 5,
        });

        L.maps[<?php echo json_encode($map); ?>] = map;
        map.marker = null;

        $(window).on('resize', function() {
            'use strict';
            map.invalidateSize();
        });

        map.on('click', function(e) {
            var clickPosition = e.latlng;
            if (map.marker === null) {
                createMarker(clickPosition);
            } else {
                moveMarker(clickPosition);
            }

        });

        function updateLatlngInput() {
            var input = $('#<?php echo $input; ?>');
            var str = '';
            if (map.marker !== null) {
                str = map.marker.getLatLng().lat + ";" + map.marker.getLatLng().lng;
            }
            input.val(str);
        }

        /*
         * Geocoding
         */
        var searchInput = $('.js_map_search', $('#<?php echo $map; ?>').parent());
        var btnSearch = $('.btn-search', $('#<?php echo $map; ?>').parent());
        var geocoding = new L.Geocoding({
            providers: {
                custom: function(arg) {
                    var that = this,
                        query = arg.query,
                        cb = arg.cb;
                    $.ajax({
                        url: 'https://nominatim.openstreetmap.org/search',
                        dataType: 'jsonp',
                        jsonp: 'json_callback',
                        data: {
                            q: query,
                            format: 'json'
                        }
                    }).done(function(data) {
                        if (data.length > 0) {
                            var res = data[0];
                            if (map.marker === null) {
                                createMarker(new L.LatLng(res.lat, res.lon));
                            } else {
                                moveMarker(new L.LatLng(res.lat, res.lon));
                            }
                        } else {
                            destroyMarker();
                        }
                    });
                }
            }
        });

        // Set custom provider default
        geocoding.setOptions({
            provider: 'custom'
        });
        map.addControl(geocoding);

        // Geocoding

        btnSearch.on('click', function() {
            'use strict';
            geocoding.geocode(searchInput.val());
        });

        function createMarker(latlng) {
            if (map !== null) {
                map.marker = L.marker(latlng, {
                    draggable: true
                }).on('dragend', function(e) {
                    updateLatlngInput();
                }).on('click', function(e) {
                    var result = confirm('<?php e('Remove marker?'); ?>');
                    if (result) {
                        destroyMarker();
                    }
                }).addTo(map);

                //Center the map on the marker
                map.setView(latlng, 17, {
                    animate: true
                });
                updateLatlngInput();
            }
        }

        function moveMarker(latlng) {
            if (map !== null && map.marker !== null) {
                map.marker.setLatLng(latlng);
                map.setView(latlng, 17, {
                    animate: true
                });
                updateLatlngInput();
            }
        }

        function destroyMarker() {
            if (map !== null && map.marker !== null) {
                map.removeLayer(map.marker);
                map.marker = null;
                updateLatlngInput();
            }
        }

        setTimeout(function() {
            'use strict';
            map.invalidateSize(true)
        }, 2000);

        <?php if (isset($lat) && isset($lon)) : ?>
            setTimeout(function() {
                'use strict';
                createMarker([<?php echo $lat ?>, <?php echo $lon ?>]);
            }, 1000);
        <?php endif; ?>

    });
</script>