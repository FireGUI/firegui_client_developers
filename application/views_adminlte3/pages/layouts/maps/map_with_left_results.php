<div id="results">
    <div class="row">
        <div class="col-md-3 map-results">
            <h3><span class="js_results_number"></span> Risultati</h3>
            <ul class="js_result_list media-list">
                <li class="js_result_item media hide">
                    <a class="js_result_link pull-right" href="#">
                        <img class="js_result_image media-object" src="<?php echo base_url_admin('images/no-image-50x50.gif'); ?>" alt="">
                    </a>
                    <div class="media-body">
                        <a class="js_result_link" href="#">
                            <h5 class="media-heading bold js_result_title"></h5>
                        </a>
                        <span class="js_result_description"></span>
                    </div>
                </li>
            </ul>
        </div>

        <div class="col-md-9">
            <div id="<?php echo ($id = "map_sidebar{$data['maps']['maps_id']}"); ?>" class="map-container"></div>
        </div>
    </div>
</div>


<script>
    $(function() {
        'use strict';

        function load_marker() {
            /***
             * CARICO I MARKER VIA AJAX
             */
            $.ajax({
                type: "POST",
                dataType: "json",
                url: '<?php echo base_url("get_ajax/get_map_markers/{$data['maps']['maps_id']}"); ?>/<?php if (isset($value_id)) echo $value_id; ?>',
                success: function(data) {
                    // Ciclo i Marker
                    var group = new Array();
                    var list = $('#results .js_result_list');
                    $('.js_result_item', list).each(function() {
                        if (!$(this).hasClass('hide')) {
                            $(this).remove();
                        }
                    });
                    $('#results .js_results_number').html(data.length);

                    $.each(data, function(i, val) {

                        var html = '<b>' + val.title + '</b><br />';
                        if (typeof val.description !== "undefined") {
                            html += val.description
                        }
                        html += '<br /><a href="' + val.link + '"><?php e('View details'); ?></a>';

                        if (val.marker) {
                            var icon = L.icon({
                                iconUrl: base_url_uploads + 'uploads/' + val.marker,
                                iconSize: [64, null],
                            });
                        } else {
                            var icon = new L.Icon.Default();
                        }


                        marker = L.marker([val.lat, val.lon], {
                            icon: icon
                        }).addTo(map).bindPopup(html);
                        var coor = L.latLng(val.lat, val.lon);
                        group.push(coor);

                        //Inserisco i results
                        var item = $('.js_result_item.hide', list).clone();
                        item.removeClass('hide');

                        // Immagine
                        if (val.thumbnail) {
                            $('.js_result_image', item).attr('src', base_url_uploads + 'uploads/' + val.thumbnail).css({
                                width: '50px',
                                height: 'auto'
                            });
                        }

                        // Link
                        $('.js_result_link', item).attr('href', val.link);

                        // Titolo
                        $('.js_result_title', item).html(val.title);

                        // Descrizione
                        $('.js_result_description', item).html(val.description);

                        item.appendTo(list);
                    });
                    map.fitBounds(group);
                },
                error: function(data) {
                    alert('Errore nel caricamento dei marker...');
                }
            });
        }





        var map = L.map('<?php echo $id; ?>', {
            scrollWheelZoom: false
        }).setView([42.50, 12.90], 5);

        L.maps[<?php echo json_encode($mapId); ?>] = map;

        L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        load_marker();
        $(window).on('resize', function() {
            map.invalidateSize();
        });

    });
</script>