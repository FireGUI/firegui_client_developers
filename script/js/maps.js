//array store for all maps
L.maps = {};
L.firstLoads = {};

function load_marker(map, url, clusterize) {

    /***
     * CARICO I MARKER VIA AJAX
     */
    if (typeof map.my_markers !== 'undefined') {
        map.removeLayer(map.my_markers);
    }

    if (typeof url === 'undefined') {
        url = map.my_url;
    }
    if (typeof clusterize === 'undefined') {
        clusterize = map.my_clusterize;
    }


    var data = [];
    data.push({ name: token_name, value: token_hash });

    var zoom = map.getZoom();
    if (clusterize) {
        if (zoom < 13) {
            map.my_markers = L.markerClusterGroup({
                maxClusterRadius: 25
            });
        } else {
            map.my_markers = L.layerGroup();
        }
    } else {
        map.my_markers = L.layerGroup();
    }
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

    data.push({ name: 'bounds', value: JSON.stringify(oBound) });

    $.ajax({
        type: "POST",
        dataType: "json",
        data: data,
        async: true,
        url: url,
        success: function (data) {
            map.removeLayer(map.my_markers);

            // Ciclo i Marker
            var bounds = L.latLngBounds();

            $.each(data, function (i, val) {
                var html = '<b>' + val.title + '</b><br />';
                if (typeof val.description !== "undefined") {
                    html += val.description;
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
                    map.my_markers.addLayer(marker);

                    var coor = L.latLng(val.lat, val.lon);
                    bounds.extend(coor);
                }
            });

            if ($('#' + map._container.id).length > 0) {
                try {
                    map.addLayer(map.my_markers);

                    // Verifica se è il primo caricamento per questa mappa
                    if (!L.firstLoads[map._container.id] && bounds && bounds.isValid()) {
                        map.fitBounds(bounds, {
                            padding: [50, 50]
                        });
                        // Segna che questa mappa ha completato il primo caricamento
                        L.firstLoads[map._container.id] = true;
                    } else if (!map.my_markers.getLayers().length && map.getZoom() > 2) {
                        map.setZoom(5);
                    }

                } catch (e) {
                    console.log(e);
                }
            }
        },
        error: function (data) {
            console.error('Error loading markers');
        }
    });
}

var maps_initializing = false
function mapsInit() {
    if (maps_initializing) {
        return false;
    }
    maps_initializing = true;
    $(() => {
        //Reset firstLoads quando si reinizializzano le mappe
        L.firstLoads = {};

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

                var get_parameters = $(this).data('get_parameters');
                var clusterize = $(this).data('clusters');


                if (L.maps[$(this).attr('id')]) {
                    var map = L.maps[$(this).attr('id')];
                    map.off();
                    map.remove();
                }
                var initlatlon = [45.8757041, 13.3470536];
                if ($(this).data('initlatlon')) {
                    initlatlon = $(this).data('initlatlon').split(',');
                }
                var map = L.map($(this).attr('id'), {
                    scrollWheelZoom: false,
                    fullscreenControl: {
                        pseudoFullscreen: false
                    },
                    maxZoom: 16
                }).setView(initlatlon, $(this).data('initzoom'));

                map.my_get_parameters = get_parameters;
                map.my_url = url;
                map.my_clusterize = clusterize;
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

                load_marker(map, url + '?' + get_parameters, clusterize);

                map.on('zoomend', function () {
                    load_marker(map);
                });
                map.on('dragend', function () {
                    load_marker(map);
                });
            });

            $(window).on('resize', function () {
                for (var i in L.maps) {
                    if ($('#' + L.maps[i]._container.id).length > 0) {
                        L.maps[i].invalidateSize();
                    }
                }
            });
            maps_initializing = false;
        });
    });
}