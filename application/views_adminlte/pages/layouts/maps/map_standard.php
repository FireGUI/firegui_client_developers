<?php
$id = "map_standard{$data['maps']['maps_id']}";
$passedId = isset($value_id) ? $value_id : null;
$ajaxURL = base_url("get_ajax/get_map_markers/{$data['maps']['maps_id']}/{$passedId}");
?>
<div <?php echo sprintf('id="%s"', $id); ?> style="height:380px"></div>



<script>
    var token = JSON.parse(atob($('body').data('csrf')));
    var token_name = token.name;
    var token_hash = token.hash;
    $(function() {
        var url = '<?php echo $ajaxURL; ?>';
        var markers = null;
        var map = L.map('<?php echo $id; ?>', {
            scrollWheelZoom: false,
            fullscreenControl: {
                pseudoFullscreen: false
            }
        }).setView([40.730610, -73.935242], <?php echo ($data['maps']['maps_init_zoom']) ?: 5; ?>);

        L.maps[<?php echo json_encode($id); ?>] = map;

        // L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
        //     attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
        // }).addTo(map);



        var osm = L.tileLayer("http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png")
        /*,
                    mqi = L.tileLayer("http://{s}.mqcdn.com/tiles/1.0.0/sat/{z}/{x}/{y}.png", {
                        subdomains: ['otile1', 'otile2', 'otile3', 'otile4']
                    })
                ,
                            googleStreets = L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                                maxZoom: 20,
                                subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
                            }),
                            googleSat = L.tileLayer('http://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
                                maxZoom: 20,
                                subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
                            }),
                            googleHybrid = L.tileLayer('http://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}', {
                                maxZoom: 20,
                                subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
                            }),
                            googleTerrain = L.tileLayer('http://{s}.google.com/vt/lyrs=p&x={x}&y={y}&z={z}', {
                                maxZoom: 20,
                                subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
                            })*/
        ;




        var baseMaps = {
            "OpenStreetMap": osm,
            //"MapQuestImagery": mqi,
            /*"Google Street": googleStreets,
            "Google Satellite": googleSat,
            "Google Hybrid": googleHybrid,
            "Google Terrain": googleTerrain,*/
        };
        var overlays = {};
        L.control.layers(baseMaps, overlays, {
            position: 'bottomleft'
        }).addTo(map);

        //Set default
        osm.addTo(map); //  set as 

        $(window).on('resize', function() {
            map.invalidateSize();
        });


        function load_marker() {
            /***
             * CARICO I MARKER VIA AJAX
             */
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    [token_name]: token_hash
                },
                url: url,
                success: function(data) {

                    // Rimuovo i vecchi marker
                    if (markers !== null) {
                        map.removeLayer(markers);
                        markers = null;
                    }

                    markers = L.layerGroup();

                    // Ciclo i Marker
                    var group = new Array();
                    $.each(data, function(i, val) {
                        var html = '<b>' + val.title + '</b><br />';
                        if (typeof val.description !== "undefined") {
                            html += val.description
                        }
                        html += '<br /><a href="' + val.link + '"><?php e('View details'); ?></a>';
                        var icon;
                        if (val.marker) {
                            icon = L.icon({
                                iconUrl: base_url_uploads + 'uploads/' + val.marker,
                                iconSize: [32, null]
                            });
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
                        //console.log('fitBounds!!!');
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