<?php
//Check per evitare il bug quando max_input_data è minore del numero di checkbox stampati...

$stima_campi = (count($dati['layouts']) * count($dati['users_layout'])) * 2; //Mi tengo un margine doppio, non voglio rischiare...
$max_input_vars = ini_get('max_input_vars');

$sidebar = $this->db->query("SELECT menu_layout FROM menu WHERE menu_layout <> '' AND menu_layout IS NOT NULL AND menu_position = 'sidebar'")->result_array();
$sidebar_layouts = array();
foreach ($sidebar as $menu_layout) {
    $sidebar_layouts[] = $menu_layout['menu_layout'];
}

function extra_data($layout, $sidebar_layouts)
{
    $extra="";


    if (in_array($layout['layouts_id'], $sidebar_layouts)) {
        $extra .= '<i title="Linked in sidebar" class="extra_icon fas fa-link"></i> ';
    }


    if ($layout['layouts_is_entity_detail']) {
        $extra .= '<i title="Layout Detail" class="extra_icon fas fa-eye"></i> ';
    }

    if ($layout['layouts_is_public']) {
        $extra .= '<i title="Public layout" class="extra_icon fas fa-user"></i> ';
    }


    if ($layout['layouts_settings']) {
        $extra .= '<i title="Settings layout" class="extra_icon fas fa-cogs"></i> ';
    }


    if ($layout['layouts_dashboardable']) {
        $extra .= '<i title="Dashboard" class="extra_icon fas fa-home"></i> ';
    }


    if ($layout['layouts_pdf']) {
        $extra .= '<i title="PDF layout" class="extra_icon fas fa-print"></i> ';
    }



    return $extra;
}
?>
<style>
    a {
        cursor:pointer!important;
    }
    .extra_icon {
        color:#666666;
    }
    </style>
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

        <div class="col-md-12">

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
                
                <div class="portlet-body form">

                    <form id="views_form" role="form" method="post" action="<?php echo base_url('db_ajax/save_views_permissions'); ?>" class="formAjax">
                        <?php add_csrf(); ?>
                        <div class="form-body">
                            <div class="table-scrollable table-scrollable-borderless">
                                <table id="views-permissions-datatable" class="table table-bordered table-condensed table-hover">
                                    <thead>
                                        <tr>
                                            <th><a OnClick="$('.tr-module').toggleClass('hide');">Show/hide all</a> | <a OnClick="show_only_sidebar();">Show/hide only Sidebar layouts</a></th>
                                            <?php foreach ($dati['users_layout'] as $userID => $username) : ?>
                                                <th>
                                                    <label>
                                                        <input type="checkbox" data-toggle="tooltip" title="<?php e('Enable/Disable all'); ?>" class="js-toggle-all toggle" data-user="<?php echo $userID; ?>" />
                                                        <strong><?php echo(is_numeric($userID) ? '' : '<small class="text-muted fw-normal">Group</small> ') . $username; ?></strong>
                                                    </label>
                                                </th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php $module = "";?>
                                        <?php foreach ($dati['layouts'] as $layout) : ?>

                                            <!-- Module header -->
                                            <?php if ($layout['layouts_module'] != $module):?>
                                            <tr>
                                                <th>
                                                    <label class="permissions-layout-label">
                                                        <strong><?php echo ($layout['modules_name']) ? $layout['modules_name'] : "Generic layouts"; ?></strong>
                                                        
                                                        <small><a OnClick="$('tr[data-module=\'<?php echo $layout['layouts_module'];?>\']').toggleClass('hide');">show/hide</a></small>
                                                    </label>
                                                </th>
                                                <?php foreach ($dati['users_layout'] as $userID => $username) : ?>
                                                    <td>
                                                        <label>
                                                            <input type="checkbox" data-toggle="tooltip" title="<?php e('Enable/Disable all'); ?>" class="js-toggle-all-module toggle" data-module="<?php echo $layout['layouts_module'];?>" data-user="<?php echo $userID; ?>" />
                                                            <small class="text-muted">* <?php echo $username; ?> *</small>
                                                        </label>
                                                    </td>
                                                <?php endforeach; ?>
                                            <?php $module = $layout['layouts_module'];?>
                                            <?php endif;?>

                                            <!-- Single layout row -->
                                            <tr data-module="<?php echo $layout['layouts_module'];?>" class="tr-module hide" <?php if (in_array($layout['layouts_id'], $sidebar_layouts)): ?>data-sidebar="true"<?php endif;?>>
                                                <th>
                                                    <label class="permissions-layout-label" title="<?php echo $layout['layouts_title']; ?> ">
                                                        <input type="checkbox" data-toggle="tooltip" title="<?php e('Enable/Disable all'); ?>" class="js-toggle-all-horizontal toggle" data-layout="<?php echo $layout['layouts_id']; ?>" />
                                                        <small class="text-muted"><?php echo $layout['layouts_id']; ?> - </small> <a target="_blank" href="<?php echo base_url(); ?>main/layout/<?php echo $layout['layouts_id']; ?>"><?php echo $layout['layouts_title']; ?></a> <?php echo extra_data($layout, $sidebar_layouts);?> <small><?php echo $layout['layouts_identifier']; ?></small>
                                                    </label>
                                                </th>
                                                <?php foreach ($dati['users_layout'] as $userID => $username) : ?>
                                                    <td>
                                                        <label>
                                                            <input type="checkbox" class="js-toggle-view toggle" data-single_checkbox="1" data-module="<?php echo $layout['layouts_module'];?>" data-user="<?php echo $userID; ?>" value="<?php echo $userID; ?>" name="view[<?php echo $layout['layouts_id']; ?>][]" <?php if (!isset($dati['unallowed'][$userID]) || !in_array($layout['layouts_id'], $dati['unallowed'][$userID])) {
                                                                echo 'checked';
                                                            } ?> />
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
                        <div class="form-actions fluid">
                            <div class="col-md-12">
                                <div class='pull-right'>
                                    &nbsp;
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
    var only_sidebar = false;
    $(document).ready(function() {
        'use strict';
        var numero_di_campi = $('#views_form :input').length;
        if ((numero_di_campi + 20) > <?php echo $max_input_vars; ?>) {
            // $('#views_form button').hide();
        }


        // Select all user module
        $('body').on('change', '.js-toggle-all-module', function() {
            var user = $(this).data('user');
            var module = $(this).data('module');

            if ($(this).is(":checked")) {
                $('input[type="checkbox"][data-single_checkbox="1"][data-module="'+module+'"][data-user="'+user+'"]').prop('checked', true);
            } else {
                $('input[type="checkbox"][data-single_checkbox="1"][data-module="'+module+'"][data-user="'+user+'"]').prop('checked', false);
            }
        })
    });

    function show_only_sidebar() {
        if (only_sidebar == false) {
            $('tr.tr-module').addClass('hide');
            $('tr[data-sidebar="true"]').removeClass('hide');
            only_sidebar = true;
        } else {
            $('tr.tr-module').removeClass('hide');
            only_sidebar = false;
        }
    }
    function refreshPermissionTable(userId) {
        var jqTableContainer = $('#js_permission_table');
        var formButton = $('#js_form_toggler');

        // formButton.attr('disabled', true);

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