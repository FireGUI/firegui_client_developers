<?php
$map = "js_map_container_{$field['fields_id']}_map" . ($lang ? "_{$lang}" : '');
$input = "js_map_container_{$field['fields_id']}_input" . ($lang ? "_{$lang}" : '');
$polygons = false;

if ($value) {
    $json_data = $this->db->query("SELECT ST_AsGeoJSON('{$value}'::geography) AS json_data")->row()->json_data;

    $multipolygon = json_decode($json_data, true)['coordinates'];
    $polygons = $circles = [];
    foreach ($multipolygon as $polygon) {
        $polygons[] = array_map(function ($lonlat) { //Inverto lon/lat in lat/lon
            return array($lonlat[1], $lonlat[0]);
        }, $polygon[0]);
    }

    //Ora che ho i poligoni puliti, li ciclo per riconoscere eventuali cerchi (visto che postgis mi passa tutto come poligoni comunque)
    //Per farlo prendo il baricentro del poligono e lo confronto col centro del cerchio circoscritto. Se coincidono (a meno di qualche approssimazione)
    //modifico il poligono per manipolarlo con leaflet come cerchio
    foreach ($polygons as $key => $polygon) {
        $distances = array();
        if (count($polygon) >= 8) {
            $points = array_map(function ($point) {
                return array_reverse($point);
            }, $polygon);

            $polygon_postgis_str = 'POLYGON((' . implode(',', array_map(function ($point) {
                return implode(' ', $point);
            }, $points)) . '))';
            $centroid = $this->db->query("SELECT ST_Centroid(ST_GeographyFromText('$polygon_postgis_str')::geometry) as centroid")->row()->centroid;

            foreach ($points as $point) {
                $distances[] = (int) $this->db->query("SELECT ST_Distance('$centroid'::geography,ST_GeographyFromText('POINT({$point[0]} {$point[1]})')) as distance")->row()->distance;
            }
        }

        if ($distances && count(array_unique($distances)) == 1) {
            //E' un cerchio            
            $polygons[$key] = array();
            $polygons[$key]['type'] = 'circle';
            $center_latlng = json_decode($this->db->query("SELECT ST_AsGeoJSON('$centroid') as json_center")->row()->json_center, true)['coordinates'];
            $polygons[$key]['center'] = implode(',', array_reverse($center_latlng));
            $polygons[$key]['radius'] = $distances[0];
        } else {
            //Non è un cerchio   
            $polygons[$key] = array();
            $polygons[$key]['type'] = 'polygon';
            $polygons[$key]['points'] = $polygon;
        }
    }
}
?>

<?php echo $label; ?>

<div id="inputs_container_<?php echo $input; ?>">

</div>
<div class="location-search-container">
    <div class="input-group">
        <input type="text" class="form-control js_map_search" placeholder="<?php e('find location'); ?>" />
        <span class="input-group-btn">
            <button class="btn btn-default" type="button"><i class="fas fa-search"></i></button>
        </span>
    </div>
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
                    for (var j in latlng) {
                        lnglatstrs.push(latlng[j].lng + ' ' + latlng[j].lat);
                    }
                    var val = lnglatstrs.join();
                    $('#inputs_container_<?php echo $input; ?>').append('<input type="hidden" name="<?php echo $field['fields_name']; ?>[polygons][]" class="<?php echo $class; ?> <?php echo $input; ?>" value="' + val + '" />');
                } else if (layers[i] instanceof L.Circle) {
                    var circleBounds = layers[i].getBounds();
                    for (var j in circleBounds) {
                        bounds.push(circleBounds[j]);
                    }
                    var center = layers[i].getLatLng();
                    var radius = layers[i].getRadius();
                    var val = center.lng + ' ' + center.lat + ',' + radius;
                    $('#inputs_container_<?php echo $input; ?>').append('<input type="hidden" name="<?php echo $field['fields_name']; ?>[circles][]" class="<?php echo $class; ?> <?php echo $input; ?>" value="' + val + '" />');
                    continue;
                } else {
                    alert('Tipo di poligono non ancora gestito');
                    continue;
                }

            }
            if (bounds.length > 0) {
                map.fitBounds(bounds);
            } else {
                //Forzo l'inserimento del campo vuoto, altrimenti se rimuovo tutti i poligono salvati e risalvo, non cambia quel field
                $('#inputs_container_<?php echo $input; ?>').append('<input type="hidden" name="<?php echo $field['fields_name']; ?>" class="<?php echo $class; ?> <?php echo $input; ?>" value="" />');
            }


        }

        var map = null;

        $('#<?php echo $map; ?>').on('resize', function() {
            if (map !== null) {
                map.invalidateSize();
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
                message: '<strong>Attenzione!<strong> non puoi intersecare le linee!' // Message that will show when intersect
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
            savePolygons();
        });

        <?php if ($value) : ?>
            <?php foreach ($polygons as $polygon) : ?>
                <?php if ($polygon['type'] == 'polygon') : ?>
                    var saved_polygon = L.polygon(<?php echo json_encode($polygon['points']); ?>, polygonOpt);
                    drawnItems.addLayer(saved_polygon);
                    map.addLayer(saved_polygon);
                <?php elseif ($polygon['type'] == 'circle') : ?>
                    var saved_circle = L.circle(L.latLng(<?php echo $polygon['center']; ?>), <?php echo $polygon['radius']; ?>, polygonOpt);
                    drawnItems.addLayer(saved_circle);
                    map.addLayer(saved_circle);
                <?php endif; ?>
            <?php endforeach; ?>
            savePolygons();
        <?php endif; ?>

        $(window).on('resize', function() {
            'use strict';
            map.invalidateSize();
            savePolygons();
        });


        /*
         * Geocoding
         */
        var searchInput = $('.js_map_search', $('#<?php echo $map; ?>').parent());
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
                            map.setView(new L.LatLng(res.lat, res.lon), 12, {
                                animate: true
                            });
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

        searchInput.on('blur', function() {
            'use strict';
            geocoding.geocode(searchInput.val());
        });

        // disabilito l'enter
        searchInput.on('keypress', function(e) {
            if (e.keyCode == 13) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                geocoding.geocode(searchInput.val());
            }
        });




    });
</script>
