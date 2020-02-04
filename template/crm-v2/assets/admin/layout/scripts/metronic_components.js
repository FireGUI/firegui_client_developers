

$(function () {
    
    Metronic.init();    // init metronic core components
    Layout.init();      // init current layout
    
    initComponents();
    
});

function reset_theme_components() {
    Metronic.initUniform();
    initComponents();
}
    