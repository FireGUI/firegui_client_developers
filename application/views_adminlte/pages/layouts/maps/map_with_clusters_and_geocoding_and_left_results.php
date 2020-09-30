<?php
$mapFormId = "clusered_map_form_{$data['maps']['maps_id']}";
$mapGeocodeInput = "clusered_map_geocoding_{$data['maps']['maps_id']}";
$mapGeocodeToggle = "clusered_map_geocoding_toggle_{$data['maps']['maps_id']}";
$mapId = "map_clusters{$data['maps']['maps_id']}";
?>
<div id="results">
    <div class="row">
        <div class="col-xs-12 mb-10">
            <a href="#" onclick="$('#<?php echo 'clusered_map_form_toggle' . $data['maps']['maps_id']; ?>').fadeIn('slow');" class="btn btn-xs red"><i class="fas fa-times"></i></a>
        </div>
        <form <?php echo sprintf('id="%s"', $mapFormId); ?> class="__collapse">
            <?php add_csrf(); ?>
            <div class="clearfix"></div>

            <?php foreach ($data['maps_fields'] as $map_field) : ?>
                <?php if ($map_field['maps_fields_type'] !== 'latlng') : ?>
                    <div class="col-md-6">
                        <?php echo $this->datab->build_form_input($map_field); ?>
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
        <div class="col-md-8"></div>
        <div class="col-md-4">
            <div class="input-group">
                <input <?php echo sprintf('id="%s"', $mapGeocodeInput); ?> class="form-control" type="text" placeholder="<?php e('cerca località') ?>" />
                <span class="input-group-btn">
                    <button <?php echo sprintf('id="%s"', $mapGeocodeToggle); ?> class="btn btn-default" type="button"> <span class="fas fa-search"></span> </button>
                </span>
            </div>
        </div>
    </div>


    <div class="row mt-10">
        <div class="col-lg-2 col-sm-3 hidden-xs map-results">
            <h3>
                <span class="js_results_number"></span> Risultati<br />
                <small class="js_limited_results"></small>
            </h3>
            <ul class="js_result_list media-list">
                <li class="js_result_item media hide">
                    <a class="js_result_link pull-right" href="#">
                        <img class="js_result_image media-object" src="<?php echo base_url_admin('images/no-image-50x50.gif'); ?>" alt="">
                    </a>
                    <div class="media-body">
                        <h5 class="media-heading bold">
                            <a class="js_result_link js_result_title" href="#"></a>
                        </h5>
                        <span class="js_result_location"><span class="js_result_address"></span> <span class="js_result_city"></span><br /></span>
                        <small class="js_result_description"></small>
                        <div class='clearfix'></div>
                        <button class="pull-right btn btn-link btn-xs js_result_zoom"> Vedi sulla mappa </button>
                    </div>
                </li>
            </ul>
        </div>

        <div class="col-lg-10 col-sm-9">
            <div <?php echo sprintf('id="%s"', $mapId); ?> class="map-container"></div>
        </div>
    </div>
</div>


<script>
    $(function() {
        'use strict';

        function load_marker(fitBoundsToGroup) {
            /***
             * CARICO I MARKER VIA AJAX
             */
            console.debug(new Date());
            var where = jqFilterForm.serializeArray();

            try {
                var bounds = map.getBounds();
                var ne = bounds.getNorthEast();
                var sw = bounds.getSouthWest();
                var oBound = {
                    ne_lat: ne.lat,
                    ne_lng: ne.lng,
                    sw_lat: sw.lat,
                    sw_lng: sw.lng
                };
            } catch (e) {
                var oBound = {};
            }

            // Abort previous ajax call
            if (jqAjax !== null) {
                jqAjax.abort();
            }

            loading(true);
            jqAjax = $.ajax({
                type: "POST",
                data: {
                    where: where,
                    bounds: oBound
                },
                dataType: "json",
                url: '<?php echo base_url("get_ajax/get_map_markers/{$data['maps']['maps_id']}"); ?>/<?php if (isset($value_id)) echo $value_id; ?>',
                complete: function() {
                    jqAjax = null;
                    loading(false);
                },
                success: function(data) {
                    // Rimuovo i vecchi
                    if (markers !== null) {
                        map.removeLayer(markers);
                        markers = null;
                    }


                    // Ciclo i Marker
                    var group = new Array();
                    var zoom = map.getZoom();
                    if (zoom < 13) {
                        markers = L.markerClusterGroup({
                            maxClusterRadius: 25
                        });
                    } else {
                        markers = L.layerGroup();
                    }

                    var list = $('#results .js_result_list');
                    $('.js_result_item:not(.hide)', list).remove();
                    $('#results .js_results_number').html(data.length);
                    $('#results .js_limited_results').html(data.length > 200 ? 'Elencati i primi 200' : '');

                    $.each(data, function(i, val) {

                        var html = '<b>' + val.title + '</b><br />';
                        if (typeof val.description !== "undefined") {
                            html += val.description
                        }
                        html += '<br /><a href="' + val.link + '"><?php e('View details'); ?></a>';
                        var icon;
                        if (val.marker) {
                            icon = L.icon({
                                iconUrl: base_url_admin + 'images/markers/' + val.marker,
                                iconSize: [32, 32],
                                iconAnchor: [16, 32]
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


                        // Voglio mostrare solo i primi 200 risultati
                        if (i < 200) {

                            //Inserisco i results
                            var item = $('.js_result_item.hide', list).clone();
                            item.removeClass('hide');

                            // Immagine
                            if (val.thumbnail) {
                                $('.js_result_image', item).attr('src', base_url_uploads + 'uploads/' + val.thumbnail).css({
                                    width: '50px',
                                    height: 'auto'
                                });
                            } else {
                                $('.js_result_image', item).remove();
                            }

                            // Link
                            $('.js_result_link', item).attr('href', val.link).attr('target', '_blank');

                            // Titolo
                            $('.js_result_title', item).html(val.title);

                            // Descrizione
                            $('.js_result_description', item).html(val.description);


                            if (val.address || val.city) {
                                // Città
                                $('.js_result_address', item).html(val.address);

                                // Indirizzo
                                $('.js_result_city', item).html(val.city);
                            } else {
                                $('.js_result_location', item).remove();
                            }

                            $('.js_result_zoom', item).attr('data-lat', val.lat).attr('data-lon', val.lon);


                            item.appendTo(list);
                        }
                    });

                    map.addLayer(markers);
                    fitBoundsToGroup = (typeof fitBoundsToGroup === 'undefined') ? false : fitBoundsToGroup;
                    if (fitBoundsToGroup === true) {
                        map.fitBounds(group);
                    }

                    $('.js_result_zoom').on('click', function() {
                        map.setView(new L.LatLng($(this).attr('data-lat'), $(this).attr('data-lon')), 20);
                    });
                },
                error: function(data) {
                    console.log('Errore nel caricamento dei marker...');
                }
            });
        }





        var markers = null;
        var searchInput = $('#<?php echo $mapGeocodeInput; ?>');
        var searchInputToggle = $('#<?php echo $mapGeocodeToggle; ?>');
        var geocoding = null;

        var jqFilterForm = $('#<?php echo $mapFormId; ?>');
        var jqAjax = null;

        var map = L.map('<?php echo $mapId; ?>', {
            scrollWheelZoom: false
        }).setView([42.50, 12.90], 5);

        L.maps[<?php echo json_encode($mapId); ?>] = map;

        L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        load_marker();




        jqFilterForm.on('submit', function(e) {
            e.preventDefault();
            load_marker(true);
        });


        map.on('moveend', load_marker);
        $(window).on('resize', function() {
            map.invalidateSize();
        });



        /** Geocoding start **/
        geocoding = new L.Geocoding({
            providers: {
                custom: function(arg) {
                    var that = this,
                        query = arg.query,
                        cb = arg.cb;
                    loading(true);
                    $.ajax({
                        url: 'https://nominatim.openstreetmap.org/search',
                        dataType: 'jsonp',
                        jsonp: 'json_callback',
                        data: {
                            q: query,
                            format: 'json'
                        },
                        complete: function() {
                            loading(false);
                        }
                    }).done(function(data) {
                        if (data.length > 0) {
                            var res = data[0];
                            map.setView(new L.LatLng(res.lat, res.lon), 13);
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

        searchInputToggle.on('click', function() {
            geocoding.geocode(searchInput.val());
        });
        /** geocoding end **/

    });
</script>