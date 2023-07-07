<?php

$map = "js_map_container_{$field['fields_id']}_map" . ($lang ? "_{$lang}" : '');
$input = "js_map_container_{$field['fields_id']}_input" . ($lang ? "_{$lang}" : '');
$polygons = false;
if ($value) {
    $json_data = $this->db->query("SELECT ST_AsGeoJSON('{$value}'::geography) AS json_data")->row()->json_data;

    $geometries = json_decode($json_data, true)['geometries'];
    $polygons = $circles = [];
    foreach ($geometries as $geometry) {
        switch ($geometry['type']) {
            case 'MultiPolygon':
                foreach ($geometry['coordinates'][0] as $polygon) {
                    $polygons[] = array_map(function ($lonlat) { //Inverto lon/lat in lat/lon
                        return array($lonlat[1], $lonlat[0]);
                    }, $polygon);
                }
                break;
            default:
                debug($geometry, true);
                break;
        }
    }
    $polygons_str = [];
    foreach ($polygons as $polygon) {
        $polygons_str[] = implode(',', array_map(function ($latlon) {
            return $latlon[0] . ' ' . $latlon[1];
        }, $polygon));
    }
}
?>
<?php echo $label; ?>

<div id="inputs_container_<?php echo $input; ?>"></div>

<div class="location-search-container">
    <div class="input-group"></div>
    <br />
    <div class="location-map-container" <?php echo "id='{$map}'"; ?> <?php echo $onclick; ?>></div>
</div>
<?php echo $help; ?>

<script>
    $(document).ready(function() {
        'use strict';

        function savePolygons() {
            var bounds = [];
            $('#inputs_container_<?php echo $input; ?>').html('');
            var layers = drawnItems.getLayers();
            for (var i in layers) {
                if (layers[i] instanceof L.Polygon) {
                    var layerBounds = layers[i].getLatLngs();

                    for (var j in layerBounds) {
                        bounds.push(layerBounds[j]);
                    }

                    //popolo gli input
                    var latlng = layers[i].getLatLngs();
                    var lnglatstrs = [];
                    console.log(latlng);
                    for (var j in latlng) {
                        lnglatstrs.push(latlng[j].lng + ' ' + latlng[j].lat);
                    }
                    var val = lnglatstrs.join();
                    $('#inputs_container_<?php echo $input; ?>').append('<input type="input" name="<?php echo $field['fields_name']; ?>[polygons][]" class="<?php echo $class; ?> <?php echo $input; ?>" value="' + val + '" />');
                } else if (layers[i] instanceof L.Circle) {
                    console.log(layers[i]);
                    var circleBounds = layers[i].getBounds();
                    for (var j in circleBounds) {
                        bounds.push(circleBounds[j]);
                    }
                    var center = layers[i].getLatLng();
                    var radius = layers[i].getRadius();
                    var val = center.lng + ' ' + center.lat + ',' + radius;
                    $('#inputs_container_<?php echo $input; ?>').append('<input type="input" name="<?php echo $field['fields_name']; ?>[circles][]" class="<?php echo $class; ?> <?php echo $input; ?>" value="' + val + '" />');
                    continue;
                } else {
                    alert('<?php e('Polygon type not managed yet') ?>');
                    continue;
                }
            }

            map.fitBounds(bounds);
        }

        var map = null;

        $('#<?php echo $map; ?>').on('resize', function() {
            if (map !== null) {
                map.invalidateSize();
            }
        });

        setTimeout(function() {
            var w = $('#<?php echo $map; ?>').width();
            if (w > 0) {
                $('#<?php echo $map; ?>').height(w);
            }
        }, 1000);

        map = L.map('<?php echo $map; ?>', {
            center: new L.LatLng(46.0649520, 13.2374247),
            zoom: 14,
            layers: [
                L.tileLayer('https://{s}.tile.osm.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
                })
            ],
            minZoom: 5,

        });

        L.maps[<?php echo json_encode($map); ?>] = map;

        var drawnItems = L.featureGroup().addTo(map);

        var polygonOpt = {
            allowIntersection: false,
            shapeOptions: {
                color: '#0000FF',
            },
            drawError: {
                color: '#990000', // Color the shape will turn when intersects
                message: '<?php e('<strong>Attention!<strong> you can\'t intersect the lines!') ?>' // Message that will show when intersect
            },
            guidelineDistance: 10,
            metric: true,
            selectedPathOptions: {
                maintainColor: true,
                opacity: 0.3,
            },
            showArea: true,
        };
        var circleOpt = {
            shapeOptions: {
                color: '#0000FF',
            },
        };

        map.addControl(new L.Control.Draw({
            draw: {
                featureGroup: drawnItems,
                polygon: polygonOpt,
                circle: circleOpt,
                polyline: false,
                marker: false,
            },
            edit: {
                featureGroup: drawnItems,
                polygon: polygonOpt,
                circle: circleOpt,
                polyline: false,
                marker: false,
            }
        }));

        map.on('draw:created', function(event) {
            var layer = event.layer;
            drawnItems.addLayer(layer);
            savePolygons();
        });
        map.on('draw:edited', function(event) {
            savePolygons();
        });

        map.on('draw:deleted', function() {
            'use strict';
            savePolygons();
        });

        <?php if ($value) : ?>
            <?php foreach ($polygons as $polygon) : ?>
                var saved_polygon = L.polygon(<?php echo json_encode($polygon); ?>, polygonOpt);
                drawnItems.addLayer(saved_polygon);
                map.addLayer(saved_polygon);
            <?php endforeach; ?>
            savePolygons();
        <?php endif; ?>

        $(window).on('resize', function() {
            'use strict';
            map.invalidateSize();
            savePolygons();
        });

    });
</script>
