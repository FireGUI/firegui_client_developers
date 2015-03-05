<?php
$map =  "js_map_container_{$field['fields_id']}_map";
$input = "js_map_container_{$field['fields_id']}_input";

if($value) {
    $value_latlon = $this->db->query("SELECT ST_Y('{$value}'::geometry) AS lat, ST_X('{$value}'::geometry) AS lon")->row();
    if(!empty($value_latlon)) {
        $lat = $value_latlon->lat;
        $lon = $value_latlon->lon;
        $value = $lat.';'.$lon;
    }
}
?>
<div class="form-group" style="<?php if($field['fields_draw_display_none']==='t') echo 'display: none;' ?>">
    <label>
        <?php echo $field['fields_draw_label']; ?>
        <?php if ($field['fields_required'] == 't'): ?><span class="text-danger icon-asterisk"></span><?php endif; ?>
    </label>
    
    <input id="<?php echo $input; ?>" type="hidden" name="<?php echo $field['fields_name']; ?>" class="field_<?php echo $field['fields_id']; ?> form-control <?php echo $field['fields_draw_css_extra']; ?>" placeholder="<?php echo $field['fields_draw_placeholder'] ?>" value="<?php echo $value; ?>" />
    <div style="max-width: 400px;">
        <div class="input-group">
            <input type="text" class="form-control js_map_search" placeholder="<?php e('cerca località') ?>" />
            <span class="input-group-btn">
                <button class="btn btn-default" type="button">
                    <span class="icon-search"></span>
                </button>
            </span>
        </div>
        <br/>
        <div style="max-width: 100%; height: 400px;" id="<?php echo $map; ?>" <?php if($field['fields_draw_onclick']) echo 'onclick="'.$field['fields_draw_onclick'].'"' ?>></div>
    </div>
    <?php if($field['fields_draw_help_text']): ?>
        <span class="help-block"><?php echo $field['fields_draw_help_text']; ?></span>
    <?php endif; ?>
</div>



<script>
    
    $(document).ready(function() {
        
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
                L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
                })
            ],
            minZoom: 5,
        });
        
        var marker = null;
        map.on('click', function(e) {
            var clickPosition = e.latlng;
            if(marker === null) {
                createMarker(clickPosition);
            } else {
                moveMarker(clickPosition);
            }
            
        });
        
        
        function updateLatlngInput() {
            var input = $('#<?php echo $input; ?>');
            var str = '';
            if(marker !== null) {
                str = marker.getLatLng().lat+";"+marker.getLatLng().lng;
            }
            input.val(str);
        }
        
        
        
        
        /*
         * Geocoding
         */
        var searchInput = $('.js_map_search', $('#<?php echo $map; ?>').parent());
        var geocoding = new L.Geocoding({
            providers : {
                custom: function(arg) {
                    var that = this,
                        query = arg.query,
                        cb = arg.cb;
                        $.ajax({
                            url : 'http://nominatim.openstreetmap.org/search',
                            dataType : 'jsonp',
                            jsonp : 'json_callback',
                            data : {q: query, format: 'json'}
                        }).done(function(data){
                            if (data.length>0) {
                                var res=data[0];
                                if(marker === null) {
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
        geocoding.setOptions({provider:'custom'});
        map.addControl(geocoding);

        // Geocoding

        searchInput.on('blur', function() {
            geocoding.geocode(searchInput.val());
        });

        
        
        
        
        
        
        function createMarker(latlng) {
            if(map !== null) {
                marker = L.marker(latlng, {
                    draggable: true
                }).on('dragend', function(e) {
                    updateLatlngInput();
                }).on('click', function(e) {
                    var result = confirm('Rimuovere il marker?');
                    if(result) {
                        destroyMarker();
                    }
                }).addTo(map);

                //Center the map on the marker
                map.setView(latlng, 17, {animate: true});
                updateLatlngInput();
            }
        }
        
        function moveMarker(latlng) {
            if(map !== null && marker !== null) {
                marker.setLatLng(latlng);
                map.setView(latlng, 17, {animate: true});
                updateLatlngInput();
            }
        }
        
        function destroyMarker() {
            if(map !== null && marker !== null) {
                map.removeLayer(marker);
                marker = null;
                updateLatlngInput();
            }
        }
        
        
        
        
        <?php if(isset($lat) && isset($lon)): ?>
            setTimeout(function() {
                createMarker([<?php echo $lat ?>, <?php echo $lon ?>]);
            }, 1000);
        <?php endif; ?>
        
        
        
        
        
        
    });
    
</script>