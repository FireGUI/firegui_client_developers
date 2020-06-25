<?php
$map = "js_map_container_{$field['fields_id']}_map" . ($lang ? "_{$lang}" : '');
$input = "js_map_container_{$field['fields_id']}_input" . ($lang ? "_{$lang}" : '');
$polygons = false;
//$value='0106000020E61000000200000001030000000100000004000000000000804A752A40CA638907860D4740000000800E842A406CEFC2698C0C474000000080D1752A40003228C3920B4740000000804A752A40CA638907860D474001030000000100000029000000B2D9479D61742A40761695CA2C09474092FFFF3F12752A40344B3ED9630947400D000040FB7C2A40C6BFC8175A0947405BA781B8607D2A4067351423F40847409E000000E7852A40429DFCD2C4084740EEFFFFFF70832A40091A951927074740A8FFFFFFB47B2A40487A7B919A064740862605160A792A40566C1C37E40647400D0000405B772A40BFC0B3F4DB064740DA195FCB3B762A40536C6EAD31074740B97385CA83742A403309E02061074740BC822A89BE732A40D03B6626E6064740FA69D846E1712A4039020E0542064740FBF97355556F2A4061E8903EB105474094B4DBC6336C2A40B077E96139054740715BA9669B682A4076C0ED08DF04474077E08C8BAF642A409FA940ABA50447403002D8BC96602A4080FF637C8F0447401CDD5B39795C2A406FEC31569D0447402CD0CC6D7F582A4051A58CB0CE044740C6D37269D0542A4016D593A6210547402EFFFA5F90512A4034592D09930547407326C946DF4E2A404BDE2E7E1E06474023A8319BD74C2A4090FEFBAABE0647404C8A915D8D4B2A403C5BFD686D074740B70C574B0D4B2A40688FF401240847402F37C45F5C4B2A40489BE271DB0847406C2D99A0774C2A404E9AFDAB8C09474051ABF938544E2A401EAD11E0300A47409E9EE2E1DF502A40A3C4A1BDC10A474049737E9401542A40DF0B30B2390B474070D1B77F9A572A409FEB4220940B47404D60B437875B2A402181018DCD0B4740F80D8213A15F2A4019BD9EC2E30B4740470658ABBF632A40EA4B39E6D50B4740C5806F68BA672A40FEFA5380A40B4740A313BB166A6B2A40E6F09077510B47408755AE68AA6E2A401A6FE4FDDF0A4740CD91DC5D5B712A40ED12FC70540A47404D026D7E62732A40C9CB172FB4094740B2D9479D61742A40761695CA2C094740';
if ($value) {
    $json_data = $this->db->query("SELECT ST_AsGeoJSON('{$value}'::geography) AS json_data")->row()->json_data;

    $multipolygon = json_decode($json_data, true)['coordinates'];
    //debug($multipolygon,true);
    $polygons = $circles = [];
    foreach ($multipolygon as $polygon) {
        //debug($polygon,true);
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
            //debug($points,true);
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

    //debug($polygons,true);
}
?>
<?php echo $label; ?>
<div id="inputs_container_<?php echo $input; ?>">

</div>
<!--<div style="max-width: 400px;">-->
<div style="max-width: 100%;">
    <div class="input-group">
        <input type="text" class="form-control js_map_search" placeholder="<?php e('find location'); ?>" />
        <span class="input-group-btn">
            <button class="btn btn-default" type="button"><i class="fas fa-search"></i></button>
        </span>
    </div>
    <br />
    <div style="max-width: 100%; height: 300px; max-height: 400px;" <?php echo "id='{$map}'"; ?> <?php echo $onclick; ?>></div>
</div>
<?php echo $help; ?>
<script>
    $(document).ready(function() {
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
                    //console.log(latlng);
                    for (var j in latlng) {
                        lnglatstrs.push(latlng[j].lng + ' ' + latlng[j].lat);
                    }
                    var val = lnglatstrs.join();
                    $('#inputs_container_<?php echo $input; ?>').append('<input type="hidden" name="<?php echo $field['fields_name']; ?>[polygons][]" class="<?php echo $class; ?> <?php echo $input; ?>" value="' + val + '" />');
                } else if (layers[i] instanceof L.Circle) {
                    //console.log(layers[i]);
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
                //console.log(bounds);
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
            var w = $('#<?php echo $map; ?>').width();
            if (w > 0) {
                $('#<?php echo $map; ?>').height(w);
            }
        }, 1000);

        map = L.map('<?php echo $map; ?>', {
            center: new L.LatLng(46.0649520, 13.2374247),
            zoom: 14,
            layers: [
                L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
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
                //                weight: 10,
                //                smoothFactor: 1,
                //                noClip: false,
                //                stroke: true,
                //                opacity: 0.5,
                //                fill:true,
                //                fillColor: '#000099',
                //                fillOpacity: 0.5,
                //                fillRule: 'nonzero', //evenodd
                //                dashArray: null, //
                //                lineCap: null,
                //                lineJoin: null,
                //                clickable: true,
                //                pointerEvents: null,
                //                className: '',
            },
            drawError: {
                color: '#990000', // Color the shape will turn when intersects
                message: '<strong>Attenzione!<strong> non puoi intersecare le linee!' // Message that will show when intersect
            },
            guidelineDistance: 10,
            metric: true,
            //            repeatMode: false,
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