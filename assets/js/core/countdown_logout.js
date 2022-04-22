
var testo_tempo_rimanente = '';
$(document).ready(function () {
    'use strict';
    var barra = $('#js-countdown-bar');
    var timeout = barra.data('timeout');
    var now = Math.round(new Date().getTime() / 1000);
    var diff = timeout - now;
    if (diff <= 0) {
        window.location = base_url + 'access/logout';
    } else {
        riduci_barra(barra, diff, timeout);
    }

    barra.hover(
        function () {
            $(this).css('height', '45px');
        },
        function () {
            $('span', $(this)).html('');
            $(this).css('height', '2px');
        }
    );
});

function riduci_barra(barra, original_diff, timeout) {
    var now = Math.round(new Date().getTime() / 1000);
    var diff = timeout - now;

    var delta = diff;
    // calculate (and subtract) whole days
    var days = Math.floor(delta / 86400);
    delta -= days * 86400;

    // calculate (and subtract) whole hours
    var hours = Math.floor(delta / 3600) % 24;
    delta -= hours * 3600;

    // calculate (and subtract) whole minutes
    var minutes = Math.floor(delta / 60) % 60;
    delta -= minutes * 60;

    // what's left is seconds
    var seconds = delta % 60;

    testo_tempo_rimanente = 'Verrai disconnesso tra ' + (days ? days : '0') + ' giorni, ' + (hours ? hours : '0') + ' ore, ' + (minutes ? minutes : '0') + ' minuti, ' + (seconds ? seconds : '0') + ' secondi';
    $('span', barra).html(testo_tempo_rimanente);

    var percentuale = (100 * diff) / original_diff;
    barra.css('width', percentuale + '%');

    if (diff < 180) {
        barra.css('height', '45px');
    }

    if (diff <= 0) {
        window.location = base_url + 'access/logout';
    } else {
        setTimeout(function () {
            riduci_barra(barra, original_diff, timeout);
        }, 1000);
    }
}
