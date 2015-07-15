<?php 
$id = "map_standard{$data['maps']['maps_id']}";
$passedId = isset($value_id) ? $value_id : null;
$ajaxURL = base_url("get_ajax/get_map_markers/{$data['maps']['maps_id']}/{$passedId}");
?>
<div <?php echo sprintf('id="%s"', $id); ?> style="height:380px"></div>



<script>
    
    $(function() {
        var url = '<?php echo $ajaxURL; ?>';
        var markers = null;
        var map = L.map('<?php echo $id; ?>', {scrollWheelZoom:false}).setView([42.50, 12.90], 5);
        
        
        L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        function load_marker() {
            /***
             * CARICO I MARKER VIA AJAX
             */
            $.ajax({
                type: "POST",
                dataType: "json",
                url: url,
                success: function(data) {

                    // Rimuovo i vecchi marker
                    if(markers !== null) {
                        map.removeLayer(markers);
                        markers = null;
                    }

                    markers = L.layerGroup();

                    // Ciclo i Marker
                    var group = new Array();
                    $.each(data, function(i, val) {
                        var html =
                                '<b>' + val.title + '</b><br />' + val.description + '<br /><a href="' + val.link + '">Visualizza Dettagli</a>';
                        var icon;
                        if (val.marker) {
                            icon = L.icon({ iconUrl: base_url_template + 'uploads/' + val.marker, iconSize: [64, null] });
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
                    
                    if (markers.getLayers().length > 0) {
                        map.addLayer(markers);
                    }
                    
                    if (group.length > 0) {
                        map.fitBounds(group);
                    }
                    
                    map.invalidateSize();
                },
                error: function(data) {
                    console.error('Errore nel caricamento dei marker...');
                }
            });
        }


        setTimeout(load_marker, 500);
        
    });
</script>
