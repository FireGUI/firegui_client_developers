<?php
//Check per evitare il bug quando max_input_data è minore del numero di checkbox stampati...

$stima_campi = (count($dati['layouts']) * count($dati['users_layout'])) * 2; //Mi tengo un margine doppio, non voglio rischiare...
$max_input_vars = ini_get('max_input_vars');



?>
<section class="content-header">

    <ol class="breadcrumb">
        <li> <?php e('Permissions'); ?></li>
        <li> <small><?php e("Set permissions", 0); ?></small></li>
    </ol>
</section>

<section class="content">
    <?php if ($max_input_vars < $stima_campi) : ?>

        <div class="row">

            <div class="col-md-12">

                <div class=" gren  ">


                    <div class="box-body view ">
                        <div class="callout callout-danger Metronic-alerts alert alert-info">
                            <h4>Attenzione!</h4>

                            <p>
                                max_input_vars troppo basso per utilizzare questa pagina (<?php echo $max_input_vars; ?> &lt; <?php echo $stima_campi; ?>)
                            </p>


                        </div>
                    </div>
                </div>

            </div>


        </div>






    <?php endif; ?>
    <div class="row">

        <div class="col-md-8">

            <div class="box box-primary">
                <div class="box-header">

                    <i class="fas fa-lock"></i>
                    <h5 class="box-title"><?php e("Permissions"); ?></h5>
                </div>
                <div class="box-body form">
                    <form id="permissions_form" role="form" method="post" action="<?php echo base_url('db_ajax/save_permissions'); ?>" class="formAjax">
                        <?php add_csrf(); ?>

                        <div class="form-group">
                            <label class="col-md-3 control-label"><?php e('Users/Groups'); ?></label>
                            <div class="col-md-9">
                                <select class="form-control input-large select2_standard" name="permissions_user_id" onchange="refreshPermissionTable(this.value);">
                                    <option></option>
                                    <optgroup label="<?php e('Groups'); ?>">
                                        <option value="-1">** <?php e('New group'); ?> **</option>
                                        <?php foreach ($dati['groups'] as $group) : ?>
                                            <option value="<?php echo $group; ?>"><?php echo $group; ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                    <optgroup label="<?php e('Users'); ?>">
                                        <?php foreach ($dati['users'] as $id => $name) : ?>
                                            <option value="<?php echo $id; ?>"><?php echo $name . (empty($dati['userGroupsStatus'][$id]) ? '' : ' - ' . $dati['userGroupsStatus'][$id]) ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                </select>
                            </div>
                            <div class="clearfix"></div>
                        </div>


                        <div id="js_permission_table" class="form-group"></div>

                        <div class="form-group">
                            <div id="msg_permissions_form" class="alert"></div>
                        </div>

                        <div class="form-actions fluid">
                            <div class="col-md-12">
                                <div class='pull-right'>
                                    <button id="js-remove-group" type="button" class="btn red"><?php e('Delete group'); ?></button>
                                    <button id="js_form_toggler" type="submit" class="btn btn-primary" disabled><?php e('Save'); ?></button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- END SAMPLE FORM PORTLET-->

        </div>
    </div>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-12">

            <div class="box box-primary">
                <div class="box-header">
                    <div class="caption">
                        <i class="fas fa-check"></i>
                        <h3 class="box-title"><?php e("Layouts settings", 0); ?></h3>
                    </div>
                    <div class="tools"></div>
                </div>
                <div class="portlet-body form">

                    <form id="views_form" role="form" method="post" action="<?php echo base_url('db_ajax/save_views_permissions'); ?>" class="formAjax">
                        <?php add_csrf(); ?>
                        <div class="form-body">
                            <div class="table-scrollable table-scrollable-borderless">
                                <table id="views-permissions-datatable" class="table table-bordered table-condensed table-hover">
                                    <thead>
                                        <tr>
                                            <th><?php e('Layouts'); ?></th>
                                            <?php foreach ($dati['users_layout'] as $userID => $username) : ?>
                                                <th>
                                                    <label>
                                                        <input type="checkbox" data-toggle="tooltip" title="<?php e('Attiva/Disattiva tutti'); ?>" class="js-toggle-all toggle" data-user="<?php echo $userID; ?>" />
                                                        <strong><?php echo (is_numeric($userID) ? '' : '<small class="text-muted fw-normal">Group</small> ') . $username; ?></strong>
                                                    </label>
                                                </th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php foreach ($dati['layouts'] as $layout) : ?>
                                            <tr>
                                                <th>
                                                    <label class="permissions-layout-label" title="<?php echo $layout['layouts_title']; ?>">
                                                        <input type="checkbox" data-toggle="tooltip" title="<?php e('Enable/Disable all'); ?>" class="js-toggle-all-horizontal toggle" data-layout="<?php echo $layout['layouts_id']; ?>" />
                                                        <small class="text-muted"><?php echo $layout['layouts_id']; ?> - </small> <a target="_blank" href="<?php echo base_url(); ?>main/layout/<?php echo $layout['layouts_id']; ?>"><?php echo $layout['layouts_title']; ?></a> <small><?php echo $layout['layouts_module']; ?></small>
                                                    </label>
                                                </th>
                                                <?php foreach ($dati['users_layout'] as $userID => $username) : ?>
                                                    <td>
                                                        <label>
                                                            <input type="checkbox" class="js-toggle-view toggle" data-user="<?php echo $userID; ?>" value="<?php echo $userID; ?>" name="view[<?php echo $layout['layouts_id']; ?>][]" <?php if (!isset($dati['unallowed'][$userID]) || !in_array($layout['layouts_id'], $dati['unallowed'][$userID])) echo 'checked' ?> />
                                                            <small class="text-muted"><?php echo $username; ?></small>
                                                        </label>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="form-group">
                                <div id="msg_views_form" class="alert"></div>
                            </div>

                        </div>

                        <div class="form-actions fluid">
                            <div class="col-md-12">
                                <div class='pull-right'>
                                    <button type="submit" class="btn btn-primary"><?php e('Save'); ?></button>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
            <!-- END SAMPLE FORM PORTLET-->
        </div>

    </div>
</section>


<script>
    var token = JSON.parse(atob($('body').data('csrf')));
    var token_name = token.name;
    var token_hash = token.hash;
    $(document).ready(function() {
        'use strict';
        var numero_di_campi = $('#views_form :input').length;
        if ((numero_di_campi + 20) > <?php echo $max_input_vars; ?>) {
            alert('Il limite max_input_vars (<?php echo $max_input_vars; ?>) è troppo basso rispetto al numero di campi in questa pagina (' + numero_di_campi + ')! Funzionalità disattivata.');
            $('#views_form button').hide();
        }
    });

    function refreshPermissionTable(userId) {
        var jqTableContainer = $('#js_permission_table');
        var formButton = $('#js_form_toggler');

        formButton.attr('disabled', true);

        // Nascondi vista precedente
        $('#js-remove-group').hide().off('click');
        jqTableContainer.fadeTo('fast', 0, function() {
            'use strict';
            $.ajax({
                url: base_url + 'get_ajax/permission_table/',
                type: 'post',
                data: {
                    identifier: userId,
                    [token_name]: token_hash
                },
                success: function(view) {

                    if (isNaN(parseInt(userId))) {
                        // Ho cliccato un gruppo e posso eliminarlo
                        $('#js-remove-group').show().on('click', function() {
                            if (confirm('<?php e('Are you sure to delete group'); ?> ' + userId + '? <?php e('All users associated with it must be manually reassigned to another group'); ?>')) {
                                $.post(base_url + 'db_ajax/delete_permission_group', {
                                    group: userId,
                                    [token_name]: token_hash
                                }, function() {
                                    window.location.reload();
                                });
                            }
                        });
                    }

                    jqTableContainer.html(view);
                    jqTableContainer.fadeTo('fast', 1);
                    formButton.attr('disabled', view ? false : true);
                }
            });
        });
    }

    var ViewPermissionsTable = function() {
        'use strict';
        return {
            tableSel: '#views_form table',
            checkAllSel: '.js-toggle-all',
            checkAllSelHorizontal: '.js-toggle-all-horizontal',
            checkViewSel: '.js-toggle-view',

            table: null,
            checkAll: null,
            checkAllHorizontal: null,
            checkView: null,

            fixCheckboxesOnTop: function() {
                var widget = this;
                widget.checkAll.each(function() {
                    var checkbox = $(this);
                    var allCheckboxes = widget.getCheckboxByUser(checkbox.attr('data-user'));

                    var countAllChecks = allCheckboxes.size();
                    var countChecked = allCheckboxes.filter(':checked').size();

                    checkbox.attr('checked', (countAllChecks === countChecked));
                });
            },

            fixCheckboxesOnLeft: function() {
                var widget = this;
                widget.checkAllHorizontal.each(function() {
                    var checkbox = $(this);
                    var allCheckboxes = widget.getCheckboxByLayout(checkbox.attr('data-layout'));

                    var countAllChecks = allCheckboxes.size();
                    var countChecked = allCheckboxes.filter(':checked').size();
                    var countUnchecked = allCheckboxes.not(':checked').size();

                    checkbox.attr('checked', (countAllChecks === countChecked));
                    if (countUnchecked === countAllChecks) {
                        checkbox.parents('tr').filter(':first').addClass('text-muted');
                    } else {
                        checkbox.parents('tr').filter(':first').removeClass('text-muted');
                    }
                });
            },

            touchCheckbox: function() {
                this.fixCheckboxesOnTop();
                this.fixCheckboxesOnLeft();
            },

            touchCheckAll: function(userId, isChecked) {
                this.getCheckboxByUser(userId).attr('checked', isChecked);
                this.fixCheckboxesOnTop();
            },

            touchCheckAllHorizontal: function(layoutId, isChecked) {
                this.getCheckboxByLayout(layoutId).attr('checked', isChecked);
                this.fixCheckboxesOnLeft();
            },

            getCheckboxByUser: function(userId) {
                return $(this.checkViewSel + '[data-user="' + userId + '"]');
            },

            getCheckboxByLayout: function(layoutId) {
                return $(this.checkViewSel + '[name="view[' + layoutId + '][]"]');
            },

            init: function() {

                this.table = $(this.tableSel);
                this.checkAll = $(this.checkAllSel, this.table);
                this.checkAllHorizontal = $(this.checkAllSelHorizontal, this.table);
                this.checkView = $(this.checkViewSel, this.table);

                this.checkView.on('change', {
                    widget: this
                }, function(event) {
                    event.data.widget.touchCheckbox();
                });

                this.checkAll.on('change', {
                    widget: this
                }, function(event) {
                    var checkbox = $(this);
                    event.data.widget.touchCheckAll(checkbox.attr('data-user'), checkbox.is(':checked'));
                });

                this.checkAllHorizontal.on('change', {
                    widget: this
                }, function(event) {
                    var checkbox = $(this);
                    event.data.widget.touchCheckAllHorizontal(checkbox.attr('data-layout'), checkbox.is(':checked'));
                });

                this.fixCheckboxesOnTop();
                this.fixCheckboxesOnLeft();
            }

        };

    }();

    $(document).ready(function() {
        ViewPermissionsTable.init();
    });


    // Imposta di default i permessi come fullscreen
    $(document).ready(function() {
        $('body').addClass('page-sidebar-closed').find('.page-sidebar-menu').addClass('page-sidebar-menu-closed');
    });
</script>