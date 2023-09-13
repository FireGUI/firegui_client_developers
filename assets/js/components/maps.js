//array store for all maps
L.maps = {};
function load_marker(map, url, clusterize) {
    /***
     * CARICO I MARKER VIA AJAX
     */

    var data = [];
    data.push({ name: token_name, value: token_hash });

    var zoom = map.getZoom();
    if (clusterize) {
        if (zoom < 13) {
            markers = L.markerClusterGroup({
                maxClusterRadius: 25
            });
        } else {
            markers = L.layerGroup();
        }
    } else {
        markers = L.layerGroup();
    }

    $.ajax({
        type: "POST",
        dataType: "json",
        data: data,
        async: true,
        url: url,
        success: function (data) {

            // Ciclo i Marker
            var group = new Array();
            $.each(data, function (i, val) {
                var html = '<b>' + val.title + '</b><br />';
                if (typeof val.description !== "undefined") {
                    html += val.description
                }
                html += '<br /><a href="' + val.link + '">View details</a>';
                var icon;
                if (val.marker) {
                    icon = L.icon({
                        iconUrl: base_url_uploads + 'uploads/' + val.marker,
                        iconSize: [32, null]
                    });
                } else {
                    icon = new L.Icon.Default();
                }
                var color = val.color ? val.color : null;
                if (color) {
                    var markerHtmlStyles = `
                                background-color: ${color};
                                width: 3rem;
                                height: 3rem;
                                display: block;
                                left: -1.5rem;
                                top: -1.5rem;
                                position: relative;
                                border-radius: 3rem 3rem 0;
                                transform: rotate(45deg);
                                border: 1px solid #FFFFFF`;
                    icon = L.divIcon({
                        className: "my-custom-pin",
                        iconAnchor: [0, 24],
                        labelAnchor: [-6, 0],
                        popupAnchor: [0, -36],
                        html: `<span style="${markerHtmlStyles}" />`
                    });
                }
                if ($.isNumeric(val.lat) && $.isNumeric(val.lon)) {
                    var marker = L.marker([val.lat, val.lon], {
                        icon: icon
                    }).bindPopup(html);
                    markers.addLayer(marker);

                    var coor = L.latLng(val.lat, val.lon);
                    group.push(coor);
                }
            });
            //console.log(markers);
            //In questo momento, la mappa potrebbe esser stata distrutta e non più disponibile... controllo...
            //console.log(map._container.id);
            //alert(map._container.id);
            if ($('#' + map._container.id).length > 0) {
                try {
                    map.addLayer(markers);



                    if (group.length > 0) {

                        map.fitBounds(group);

                    }


                    map.invalidateSize();
                } catch (e) {
                    setTimeout(function () { mapsInit(); }, 500);
                }

            }



        },
        error: function (data) {
            console.error('Error loading markers');
        }
    });
}
function mapsInit() {

    $(() => {
        //Destroy all maps
        for (var i in L.maps) {
            //console.log(L.maps[i]);
        }


        var token = JSON.parse(atob($('body').data('csrf')));
        var token_name = token.name;
        var token_hash = token.hash;

        $(function () {
            'use strict';

            $('.js_map:visible').each(function () {
                
                var url = $(this).data('ajaxurl');
                var clusterize = $(this).data('clusters');


                if (L.maps[$(this).attr('id')]) {
                    var map = L.maps[$(this).attr('id')];
                    map.off();
                    map.remove();
                }

                var map = L.map($(this).attr('id'), {
                    scrollWheelZoom: false,
                    fullscreenControl: {
                        pseudoFullscreen: false
                    },
                    maxZoom: 16
                }).setView([40.730610, -73.935242], $(this).data('initzoom'));
                L.maps[$(this).attr('id')] = map;




                var osm = L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png");

                var baseMaps = {
                    "OpenStreetMap": osm,
                };
                var overlays = {};
                L.control.layers(baseMaps, overlays, {
                    position: 'bottomleft'
                }).addTo(map);

                //Set default
                osm.addTo(map); //  set as 



                load_marker(map, url, clusterize);



            });
            $(window).on('resize', function () {
                for (var i in L.maps) {
                    if ($('#' + L.maps[i]._container.id).length > 0) {
                        L.maps[i].invalidateSize();
                    }
                }
            });
        });
    });

}
