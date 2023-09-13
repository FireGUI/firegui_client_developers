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
        <input type="text" class="form-control js_map_search" placeholder="<?php e('find a place') ?>" />
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
                    console.log(latlng[0]);
                    for (var j in latlng[0]) {
                        <?php if ($this->db->dbdriver == 'postgre') : ?>
                            lnglatstrs.push(latlng[0][j].lng + ' ' + latlng[0][j].lat);
                        <?php else : ?>
                            lnglatstrs.push(latlng[0][j].lat + ' ' + latlng[0][j].lng);
                        <?php endif; ?>
                    }
                    console.log(lnglatstrs);
                    var val = lnglatstrs.join();
                    console.log(val);
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

        var osmUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            osmAttrib = '&copy; <a href="http://openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            osm = L.tileLayer(osmUrl, {
                maxZoom: 18,
                attribution: osmAttrib
            }),
            map = new L.Map('<?php echo $map; ?>', {
                center: new L.LatLng(51.505, -0.04),
                zoom: 13
            }),
            drawnItems = L.featureGroup().addTo(map);

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
            $('#<?php echo $map; ?>').trigger('resize');
        }, 1000);

        L.control.layers({
            'osm': osm.addTo(map),
            "google": L.tileLayer('http://www.google.cn/maps/vt?lyrs=s@189&gl=cn&x={x}&y={y}&z={z}', {
                attribution: 'google'
            })
        }, {
            'drawlayer': drawnItems
        }, {
            position: 'topleft',
            collapsed: false
        }).addTo(map);
        map.addControl(new L.Control.Draw({
            edit: {
                featureGroup: drawnItems,
                poly: {
                    allowIntersection: false
                }
            },
            draw: {
                polygon: {
                    allowIntersection: false,
                    showArea: true
                }
            }
        }));

        /*
         * Geocoding
         */
        var searchInput = $('.js_map_search');
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
                            map.panTo(new L.LatLng(res.lat, res.lon));


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
        searchInput.on('keyup', function() {
            geocoding.geocode(searchInput.val());
        });

        map.on(L.Draw.Event.CREATED, function(event) {
            var layer = event.layer;
            drawnItems.addLayer(layer);
            savePolygons();
        });
        map.on(L.Draw.Event.EDITED, function(event) {
            savePolygons();
        });

        map.on(L.Draw.Event.DELETED, function() {
            savePolygons();
        });
    });
</script>
