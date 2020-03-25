<div class="alert js_setup_db_alert" style="display:none"></div>

<form id="setup_db_form" class="clearfix" method="post">
    <div class="form-group row">
        <div class="col-sm-8 js_dbhost">
            <label for="dbHost">Database Host</label>
            <input type="text" class="form-control" name="dbHost" id="dbHost" placeholder="127.0.0.1">
            <p class="help-block" style="font-size: 0.85em;">Host of your database connection. <i>Usualliy is <code>localhost</code> or <code>127.0.0.1</code>.<br />If you don't know these two details, contact your hosting before continue.</i></p>
        </div>

        <div class="col-sm-4 js_dbport">
            <label for="dbPort">Database Port</label>
            <input type="text" class="form-control" name="dbPort" id="dbPort" placeholder="3306">
            <p class="help-block" style="font-size: 0.85em;">Port of your database connection. <i>Usualliy is <code>3306</code></i></p>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-sm-6 js_dbuser">
            <label for="dbUser">Database User <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="dbUser" id="dbUser" placeholder="my_user" required>
            <p class="help-block" style="font-size: 0.85em;">Login details to access the database</p>
        </div>

        <div class="col-sm-6 js_dbpass">
            <label for="dbPassword">Password <span class="text-danger">*</span></label>
            <input type="password" class="form-control" name="dbPassword" id="dbPassword" placeholder="12345" required>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-sm-12 js_dbname">
            <label for="dbName">Database Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="dbName" id="dbName" placeholder="my_database" required>
            <p class="help-block" style="font-size: 0.85em;">Name of database you created for <b>Fire</b>GUI Client</p>
        </div>
    </div>

    <button type="button" class="btn btn-primary js_test_conn_btn"><i class="fas fa-flask"></i> Test Connection</button>
    <button type="button" class="btn btn-success btn-lg pull-right js_import_db"><i class="fas fa-circle-notch fa-spin js_import_db_loading" style="display:none"></i> Import Database <i class="fas fa-file-import"></i></button>
</form>

<script>
    // window.onbeforeunload = function() {
    //     return 'If you exit now, the database may be corrupt.\n' +
    //         'Are you sure you want to leave the page?';
    // };

    $(document).ready(function() {
        $('.next-btn, .js_import_db', $('#db-setup')).hide();

        $('.js_test_conn_btn').on('click', function(e) {
            e.preventDefault();

            $('.js_setup_db_alert').hide().removeClass('alert-danger alert-success').html('');
            $('.js_dbhost,.js_dbport,.js_dbuser,.js_dbpass,.js_dbname').removeClass('has-error');

            $.post('includes/2_1_setup_db.php', $('#setup_db_form').serialize(), function(response) {
                if (response !== '' && JSON.parse(response)['status'] == 0) {
                    var error = JSON.parse(response)['txt'].split(' ');

                    switch (error[0] + ' ' + error[1]) {
                        case 'Db User':
                            $('.js_dbuser').addClass('has-error');
                            break;
                        case 'Db Pass':
                            $('.js_dbpass').addClass('has-error');
                            break;
                        case 'Db Name':
                            $('.js_dbname').addClass('has-error');
                            break;

                    }

                    $('.js_setup_db_alert').addClass('alert-danger').html(JSON.parse(response)['txt']).show();
                } else if (response == '') {
                    $('.js_setup_db_alert').addClass('alert-success').html('Connection successfull').show();

                    $('.js_import_db', $('#db-setup')).show().on('click', function(e) {
                        e.preventDefault();

                        var js_import_db_btn = $(this);

                        js_import_db_btn.prop('disabled', true);
                        $('.js_test_conn_btn').prop('disabled', true);
                        $('.js_import_db_loading', js_import_db_btn).show();

                        $('.js_setup_db_alert').hide().removeClass('alert-danger alert-success').html('');

                        if ($('#dbImport', $('#setup_db_form')).length == 0) {
                            $('<input>').attr({
                                type: 'hidden',
                                id: 'dbImport',
                                name: 'dbImport',
                                value: '1'
                            }).appendTo('#setup_db_form');
                        }

                        $.post('includes/2_1_setup_db.php', $('#setup_db_form').serialize(), function(response) {
                            var import_data = JSON.parse(response);

                            if (import_data['status'] == 0) {
                                $('.js_setup_db_alert').addClass('alert-danger').html(import_data['txt']).show();



                                setTimeout(function() {
                                    js_import_db_btn.prop('disabled', false);
                                    $('.js_test_conn_btn').prop('disabled', true);
                                    $('.js_import_db_loading', js_import_db_btn).hide();
                                }, 2500);
                            } else {
                                $('.js_import_db_loading', js_import_db_btn).hide();
                                $('.js_setup_db_alert').addClass('alert-success').html(import_data['txt']).show();

                                $('#smartwizard').smartWizard("next");
                            }
                        });
                    });
                }
            });
        });
    });
</script>