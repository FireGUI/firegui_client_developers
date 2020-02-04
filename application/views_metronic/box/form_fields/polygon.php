<?php
$map = "js_map_container_{$field['fields_id']}_map" . ($lang ? "_{$lang}" : '');
$input = "js_map_container_{$field['fields_id']}_input" . ($lang ? "_{$lang}" : '');

if ($value) {
    if (is_array($value)) {
        $value_latlon = $value;
    } else {
        $value_latlon = $this->db->query("SELECT ST_Y('{$value}'::geometry) AS lat, ST_X('{$value}'::geometry) AS lon")->row_array();
    }

    if (!empty($value_latlon)) {
        $lat = $value_latlon['lat'];
        $lon = isset($value_latlon['lon']) ? $value_latlon['lon'] : $value_latlon['lng'];
        $value = $lat . ';' . $lon;
    }
}
?>
<?php echo $label; ?>
<input <?php echo "id='{$input}'"; ?> type="hidden" name="<?php echo $field['fields_name']; ?>" class="<?php echo $class ?>" value="<?php echo $value; ?>" />
<div style="max-width: 400px;">
    <div class="input-group">
        TODO
    </div>
    <br/>
    <div style="max-width: 100%; height: 400px;" <?php echo "id='{$map}'"; ?> <?php echo $onclick; ?>></div>
</div>
<?php echo $help; ?>

<script>
    
    $(document).ready(function() {
        function savePolygon(layer) {
            //console.log(layer);    
            map.fitBounds(layer.getBounds());
            drawnItems.addLayer(layer);
        }
        
        var map = null;
        
        $('#<?php echo $map; ?>').on('resize', function() {
            if(map !== null) {
                map.invalidateSize();
            }
        });
        
        setTimeout(function() {
            var w = $('#<?php echo $map; ?>').width();
            if(w > 0) {
                $('#<?php echo $map; ?>').height(w);
            }
        }, 1000);
        
        map = L.map('<?php echo $map; ?>', {
            center: new L.LatLng(46.0649520, 13.2374247),
            zoom: 14,
            layers: [
                L.tileLayer('https://{s}.tile.osm.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors'
                })
            ],
            minZoom: 5,
            
        });
        
        L.maps[<?php echo json_encode($map); ?>] = map;
        
        
        // Set the button title text for the polygon button
        L.drawLocal.draw.toolbar.buttons.polygon = 'Draw a sexy polygon!';

        // Set the tooltip start text for the rectangle
        L.drawLocal.draw.handlers.rectangle.tooltip.start = 'Not telling...';
        
        var drawnItems = L.featureGroup().addTo(map);
        
        var polygonOpt = {
            allowIntersection: false,
            shapeOptions: {
                color: '#0000FF',
                weight: 10,
                smoothFactor: 1,
                noClip: false,
                stroke: true,
                opacity: 0.5,
                fill:true,
                fillColor: '#000099',
                fillOpacity: 0.5,
                fillRule: 'nonzero', //evenodd
                dashArray: null, //
                lineCap: null,
                lineJoin: null,
                clickable: true,
                pointerEvents: null,
                className: '',
            },
            drawError: {
                color: '#990000', // Color the shape will turn when intersects
                message: '<strong>Attenzione!<strong> non puoi intersecare le linee!' // Message that will show when intersect
            },
            guidelineDistance: 10,
            metric: true,
            repeatMode: false,
            selectedPathOptions: {
                maintainColor: true,
                opacity: 0.3,
            },
            showArea: true,
        };
        
        map.addControl(new L.Control.Draw({
            draw: { 
                featureGroup: drawnItems, 
                polygon: polygonOpt,
                //circle: false,
                polyline: false,
                marker: false,
            },
            edit: { 
                featureGroup: drawnItems, 
                polygon: polygonOpt
            },
        }));

        map.on('draw:drawstart', function () {
            drawnItems.clearLayers();
        });
        map.on('draw:created', function(event) {
            drawnItems.clearLayers();    
            var layer = event.layer;
            savePolygon(layer);
        });
        map.on('draw:edited', function(event) {
            drawnItems.clearLayers();
            var layers = event.layers.getLayers();
            console.log(layers);
            for (var i in layers) {
                //do whatever you want, most likely save back to db
                savePolygon(layers[i]);
            });
        });
        
        $(window).on('resize', function() {
            map.invalidateSize();
        });        
        
    });
    
</script>