<div id="results">
    <div class="row">
        <form id="<?php echo 'clusered_map_form_'.$data['maps']['maps_id']; ?>">
            <?php foreach($data['maps_fields'] as $map_field): ?>
                <?php if($map_field['maps_fields_type'] !== 'latlng'): ?>
                    <div class="col-md-6">
                        <?php $this->load->view("box/form_fields/{$map_field['fields_draw_html_type']}", array('field' => $map_field, 'value' => NULL)); ?>
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
            <div id="<?php echo ($id = "map_clusters{$data['maps']['maps_id']}"); ?>" style="height:680px"></div>
        </div>
    </div>
</div>


<script>
    
    $(function() {
        
        var markers = null;
        
        var map = L.map('<?php echo $id; ?>', {scrollWheelZoom:false}).setView([42.50, 12.90], 5);
        L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        

        var jqFilterForm = $('#<?php echo 'clusered_map_form_'.$data['maps']['maps_id']; ?>');
        jqFilterForm.on('submit', function(e) {
            e.preventDefault();

            var aoFormData = $(this).serializeArray();
            load_marker(aoFormData);
        });

        function load_marker(where) {
            /***
             * CARICO I MARKER VIA AJAX
             */
            if( ! where) {
                where = {};
            }
            
            if(markers !== null) {
                map.removeLayer(markers);
            }
            
            
            $.ajax({
                type: "POST",
                data: where,
                dataType: "json",
                url: '<?php echo base_url("get_ajax/get_map_markers/{$data['maps']['maps_id']}"); ?>/<?php if(isset($value_id)) echo $value_id; ?>',
                success: function(data) {
                    // Ciclo i Marker
                    var group = new Array();
                    var zoom = map.getZoom();
                    if(zoom<13) {
                        markers = L.markerClusterGroup({maxClusterRadius: 25});
                    } else {
                        markers = L.layerGroup();
                    }
                    
                    
                    $.each(data, function(i, val) {
                        
                        var html = '<b>' + val.title + '</b><br />' + val.description + '<br /><a href="' + val.link + '">Visualizza Dettagli</a>';
                        var icon;
                        if(val.marker) {
                            icon = L.icon({ iconUrl: base_url_template+'images/markers/'+val.marker, iconSize: [32,32], iconAnchor: [16,32] });
                        } else {
                            icon = new L.Icon.Default();
                        }
                        
                        
                        var marker = L.marker([val.lat, val.lon], { icon: icon }).bindPopup(html);
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