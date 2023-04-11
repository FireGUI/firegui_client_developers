Changelog version 2.9.0
 
Added my_log with scope parameters
Added scope in API, Cron and update methods
Bugfix for error_db page
Changed log_message to my_log in Core models
Fix "undefined variable fd"
Fixed when filters are "-2", which is "Empty field", are returning values because for mysql 0 == ''
Fix calendars
Added log scope in cron controller
Added My_Log class in application/core to overrite write_log function
Added set_log_scope and my_log functions in general helper
Removed modules_raw_data column
Module install now checks for fields draws
Module install bugfix locked grids
Fix t() helper function
Fix tinymce config
Remove .safurai folder from git
Bugfix for install module grids_fields
Fix mail model
Protected export controller
Bugfix core autoupdate modules
Bugfix cron cli
