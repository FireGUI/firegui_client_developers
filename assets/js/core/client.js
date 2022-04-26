
$(function () {

    initComponents();

    //MP: ho fatto così anche se non mi piace. Ogni volta che uno clicca il toggle per collassare un layoutbox, scateno il reset component che mette a posto select2, select ajax, wysiwyg.
    $("[data-widget='collapse']").click(function () {
        setTimeout(function () { reset_theme_components(); }, 300);
    });

    //20190610 - MP - On resize recalculate columns width (commented .draw() to avoid ajax multiple requests while resizing)
    $(window).resize(function () {
        //20190610 - MP - On resize recalculate columns width (commented .draw() to avoid ajax multiple requests while resizing)
        if (typeof $.fn.dataTable.tables({ visible: true, api: true }).table().node() !== 'undefined') {
            $.fn.dataTable.tables({ visible: true, api: true }).table().node().style.width = '';
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();//.draw();
        }
    });

});

function reset_theme_components() {
    initComponents();
}