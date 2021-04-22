Changelog version 2.1.7
 
Major update: layout boxes now can refresh without reloading entire page
function getBoxContent now check if layoutbox is an integer or a box data
function getBoxContent now is a public function 
Added getLayoutBox function in Layout.php
date and date_time fields are now forced to default today date and time
Isolated layout_box html
Created tabsInit function to initialize tabbed contents on the fly
Major updates to refreshVisibleAjaxGrids and refreshLayoutBox functions
Fix reset form filter
Restored inline datatable hardcoded edit button
Added default success status on db_ajax
Changed startAjaxTables to initialize only visible datatables
Added drawPieCharts and drawPieLegendCharts calls in components.js
Changed submitAjax to refresh only visible layoutboxes
Click on tabs now triggers dataTables init
