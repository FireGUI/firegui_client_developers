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
    
    <div class="row">
        <div class="col-md-offset-8 col-md-4">
            <div class="input-group">
                <input id="<?php echo 'clusered_map_geocoding_'.$data['maps']['maps_id']; ?>" class="form-control" type="text" placeholder="<?php e('cerca località') ?>" />
                <span class="input-group-btn">
                    <button id="<?php echo 'clusered_map_geocoding_toggle_'.$data['maps']['maps_id']; ?>" class="btn btn-default" type="button"> <span class="icon-search"></span> </button>
                </span>
            </div>
        </div>
    </div>
    
    <div class="row" style="margin-top: 10px;">
        <div class="col-md-12">
            <div id="<?php echo ($id = "map_clusters_geocoding{$data['maps']['maps_id']}"); ?>" style="height:680px"></div>
        </div>
    </div>
</div>


<script>
        
        $(function() {
            var markers = null;
            var searchInput = $('#<?php echo 'clusered_map_geocoding_'.$data['maps']['maps_id']; ?>');
            var searchInputToggle = $('#<?php echo 'clusered_map_geocoding_toggle_'.$data['maps']['maps_id']; ?>');
            var geocoding = null;

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



            /** Geocoding start **/
            geocoding = new L.Geocoding({
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
                                    var lat = res.lat,
                                        lng = res.lon;
                                    map.setView(new L.LatLng(res.lat, res.lon), 13);
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

            searchInputToggle.on('click', function() {
                geocoding.geocode(searchInput.val());
            });
            /** geocoding end **/



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
                        if(searchInput.val()) {
                            geocoding.geocode(searchInput.val());
                        } else {
                            map.fitBounds(group);
                        }

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