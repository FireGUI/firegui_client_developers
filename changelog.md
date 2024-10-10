Changelog version 4.1.4
 
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
