Changelog version 2.4.3
 
New features!
Update client, update db,  and update modules are now native
Possibility to release bundle modules
New modules for auto update client and modules
------
Added installModule method in core models
Bugfix for form default value '0'
Add recursive patch install
Fix scandir modules only if directory exists
Fix modules delete temp folder
Fix settings text
Fix core
Changed update reposiroty url
grids_actions_html nullable
Added Templatebridge controller and templateAssets functions to Layout model.
ExecuteMigrations now use model
Added recurse_copy function
Added core functions
Function can_access_layout now validate also layout's identifier
Bugfix with module view
Core functions for events
Added Modules_model in core
New fields for modules
Add patches methods in core updateclient
Add update method in controller install
Add updateclient in controller install
Add security check in import_query method
Change base url to default repo to get client
Improved can_access_layout method in Datab model
Fix drawCallback method in tables.js: lost condition
Fix loading custom view
Changed FireGUI to OpenBuilder in installer
Add last cron cli execution time
Fix drawCallback parameter in tables.js
Fix cron cli and check
Fix autoupdate icon
Fix Model Entities and create migration for settings
Bugfix mysql db driver query override
Added Utils from Builder and fix postgres and mysql driver
Check for custom module view
Add tmp in gitignore
Fix echo_flush helper
New permission page
Fix mail model
Added getLoadedLayoutsIds in model Layout
Min time and max time in vue_calendar
Add generate dump and zip folder methods in general helper
Removed "disconnect after" field in login page
Added custom width to tables columns
Add code to tinymce
Overrided database() function in MY_Loader.php
Added elapsed_time function in general_helper.php
Optimized tableList() function in Apilib.php
Added MY_DB_mysqli_driver class to extend query function
Bugfix in Datab.php for left join conditions
Bugfix calendar events with hours end mapping
Moved dev console from footer template to toolbar module
improve uploads structure
fixed zip method, unlink file if destination already exist
Enabled events on monthly view
Added "tomorrow", "next month" and "next year" date ranges in daterangepicker
Added elementId params in vue_calendar sourceUrl
Vue Calendar now supports events with date and hour/minute separated
Fix chat bug sending multiple messages if page not refreshed
Fix get_detail_layout_link method in datab model
Removed unit tests iframe
Bugfix multiselect with source-field dependance
sess_expire_on_close true
Profiler's secttion always open
Added referer and requested_url in ci_sessions table
Added referer detection in get_datatable_ajax
Added conditions page_id, page_identifier and nested layouts
Bugfix get_detail_layout_link with cache enabled
Added method in Layout model to get all preloaded layouts
Added possibility to pass a form identifier to Db_ajax save_form method
Fix allDay calendar
Bugfix get_detail_layout_link when cache is disabled
Bugfix vue_calendar on all days appointments
Added color to each event
Fix submit ajax show submit button
Added date start/end, time start/end
Bugfix for sublayout data details placeholders
Added condition "is maintenance
