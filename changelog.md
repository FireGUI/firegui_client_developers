Changelog version 4.2.0

2024-11-07 Release version: 4.2.0

Alpha CI3.1.13
Excluded ticket planner widget on close modal side
Enviroment date default italian
Add log unallowed layout via ajax request
fix datab grid column type input_money
Bugfix on force module reinstall, rerun migrations '0'
fix build_grid_cell when field is input_money type
add changelog
Bugfix crmentityv2 for php8.1
Bugfix updatePatches recursive
Possibility to search in relation fields
added migration for currencies system entity
fix migrations
Bugfix undefined layouts_boxes_layout
Bugfix undefined fields_ref
removed system_merged folder
added system_old folder
Bugfix MY_Cache_file
Bug fix DB_forge with DEFAULT_STRING parameter

Changelog version 4.2.0

2024-11-07 Release version: 4.2.0

Alpha CI3.1.13
Excluded ticket planner widget on close modal side
Enviroment date default italian
Add log unallowed layout via ajax request
fix datab grid column type input_money
Bugfix on force module reinstall, rerun migrations '0'
fix build_grid_cell when field is input_money type
add changelog
Bugfix crmentityv2 for php8.1
Bugfix updatePatches recursive
Possibility to search in relation fields
added migration for currencies system entity
fix migrations
Bugfix undefined layouts_boxes_layout
Bugfix undefined fields_ref

Changelog version 4.1.9

2024-10-31 Patch version: 4.1.9

Bugfix apilib undefined
To be schedule migration 4.2.0

Changelog version 4.1.8

2024-10-31 Patch version: 4.1.8

Relations searchable in apilib
Bugfix undefined layouts_boxes_layout

Changelog version 4.1.7

2024-10-29 Patch version: 4.1.7

Bugfix migration mail template and user verification code

Changelog version 4.1.6
 
Bugfix apilib accept insert/edit in relatio
migration for relation fields which nullifies migration 4.1.5
improved make_tiny function with timeout

Commit version: 4.1.5

Apilib view now works with pre-search events
added migration to add relations fields to fields table

Commit version: 4.1.4

Added migration for new htaccess
Bugfix queue pp data from TEXT to LONGTEXT
Optimized checkboxes in permissions page
added methods reset_password_request and reset_password_confirm on rest/v1
added migrations for new users_verification_code field and new basic mail templates
changed sendMessage to send with mail template in reset_password_request and reset_password methods in access controller
changed logic in change password to use apilib instead of this->db
closeContainingPopups in case 9 of submitajax.js
Bugfix undefined variable formAjaxShownMessage
New public module assets logics, with htaccess rewrite
Added download and open json link in swagger

Commit version: 4.1.3

fix action url custom in duplicate form
Bugfix for count records with depth > 2
added select ajax custom url data parameter
Bugfix right joined tables in Api V1

Commit version: 4.1.2

Bugfix support data in forms only when necessary
Passing value_id in get_menu function
Working filters with relations on depth 2
Add isRelation method on crmentity model

Commit version: 4.1.1

Bugfix value_id passed to get_menu function

Commit version: 4.1.0

New Rest Api
New swagger for Api
New layout for permissions Api assignments
Bugfix datatables with unique id
Bugfix generate_where with empty value in multiselect
Bugfix menu on install module
Native DB_query_builder modified to pre-validate data before insert or update (columns check)
removed thumb from header user profile image
Bugfix permissions cache
Optimized queuepp counter
New case 13 in submitAjax.js (useful for submit confirm after validations)
Possibility to add scripts in footer (after other scripts)
Added parameter stateDuration: -1 to fix datatable bug
Bugfix date filter when field is of type hidden
Added __MACOSX folder to .gitignore
Migration to remove .MACOSX folder
Added layout_id passed to get_layout_box_content to solve conditions problem
Added prev_row data in table rows
Bugfix for charts_elements missing field
Bugfix layout_container notice
Bugfix module install menu
revert ".one(" change in components.js
Bugfix crm_schema
Bugfix notice $class undefined
Fix export pdf totalables
Depth 2 in select_ajax_search
Fallback layout when layout is not accessible
Bugfix Api for json data request
