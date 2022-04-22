/* Variabile globale per tracciare tutte le mappe create */
var token = JSON.parse(atob($('body').data('csrf')));
var token_name = token.name;
var token_hash = token.hash;

var base_url = $('body').data('base_url');
var base_url_template = $('body').data('base_url_template');
var base_url_scripts = $('body').data('base_url_scripts');
var base_url_uploads = $('body').data('base_url_uploads');
var base_url_builder = $('body').data('base_url_builder');