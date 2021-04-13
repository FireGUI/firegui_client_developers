Changelog version 2.1.6
 
Code formatting
Moved CKEDITOR init/destroy into components.js
Added parameter to initComponents for clean reinitializing components (for now, select2s and ckeditor)
Resetting form in submitajax
Added new form success status 7 with ajax table refresh without refreshing all page
Bugfix for multiselect filter when referring relation
Added js_fg_grid_entityname in ajax grids
Added refreshGridsByEntity(entityname) function
Fix typo in time_elapsed helper function
Fix time_elapsed typos helper function on-the-fly
Bug fix for integer field not null when passing 0 as value
Removed Leaflet inizializing from components.js and moved to map_standard.js
Changed maintenance banner color from red to orange
Update calculateDistance function using project-osrm
Bugfix to remove fields from grids and form when related to an entity that does not exists
decimal fields triggered now on all body content
Delete action in inline edit grid now refresh grid only and not the whole page
Form filters now can refresh only grids without refresh the whole page
