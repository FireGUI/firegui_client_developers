
$(function () {
    
    initComponents();

    //Matteo: ho fatto così anche se non mi piace. Ogni volta che uno clicca il toggle per collassare un layoutbox, scateno il reset component che mette a posto select2, select ajax, wysiwyg.
    $("[data-widget='collapse']").click(function() {
       setTimeout(function () {reset_theme_components();}, 300);
    });
    
});

function reset_theme_components() {
    initComponents();
}