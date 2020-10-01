var token = JSON.parse(atob($('body').data('csrf')));
var token_name = token.name;
var token_hash = token.hash;
$(function () {
    'use strict';

    $('.map-standard').each(function () {

        var url = $(this).data('ajaxurl');
        var markers = null;
        var map = L.map($(this).attr('id'), {
            scrollWheelZoom: false,
            fullscreenControl: {
                pseudoFullscreen: false
            }
        }).setView([40.730610, -73.935242], $(this).data('initzoom'));

        L.maps[$(this).attr('id')] = map;

        var osm = L.tileLayer("http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png");

        var baseMaps = {
            "OpenStreetMap": osm,
        };
        var overlays = {};
        L.control.layers(baseMaps, overlays, {
            position: 'bottomleft'
        }).addTo(map);

        //Set default
        osm.addTo(map); //  set as 

        $(window).on('resize', function () {
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
                success: function (data) {

                    // Rimuovo i vecchi marker
                    if (markers !== null) {
                        map.removeLayer(markers);
                        markers = null;
                    }

                    markers = L.layerGroup();

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
                error: function (data) {
                    console.error('Error loading markers');
                }
            });
        }

        setTimeout(load_marker, 500);
    });
});