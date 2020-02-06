<?php
//Check per evitare il bug quando max_input_data è minore del numero di checkbox stampati...

//$stima_campi = (count($dati['layouts']) * count($dati['users_layout'])) * 2; //Mi tengo un margine doppio, non voglio rischiare...
$max_input_vars = ini_get('max_input_vars');

//if ($max_input_vars < $stima_campi) {
//    throw new Exception("max_input_vars troppo basso per utilizzare questa pagina ($max_input_vars < $stima_campi).");
//}

?>

<h3 class="page-title"><?php e("permessi"); ?> <small><?php e("imposta permessi per agenti", 0); ?></small></h3>
<div class="row">
    <div class="col-md-8">
        <div class="portlet box blue">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fas fa-lock"></i> Permessi
                </div>
                <div class="tools"></div>
            </div>
            <div class="portlet-body form">
                <form id="permissions_form" role="form" method="post" action="<?php echo base_url('db_ajax/save_permissions'); ?>" class="formAjax">
                    <div class="form-body">

                        <div class="form-group">
                            <label class="col-md-3 control-label"><?php e('agente'); ?></label>
                            <div class="col-md-9">
                                <select class="form-control input-large select2me" name="permissions_user_id" onchange="refreshPermissionTable(this.value);">
                                    <option></option>
                                    <optgroup label="Gruppi">
                                        <option value="-1">** Nuovo gruppo **</option>
                                        <?php foreach($dati['groups'] as $group): ?>
                                            <option value="<?php echo $group; ?>"><?php echo $group; ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                    <optgroup label="Utenti">
                                        <?php foreach ($dati['users'] as $id=>$name): ?>
                                            <option value="<?php echo $id; ?>"><?php echo $name . (empty($dati['userGroupsStatus'][$id])? '': ' - ' . $dati['userGroupsStatus'][$id]) ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                </select>
                            </div>
                            <div class="clearfix"></div>
                        </div>


                        <div id="js_permission_table" class="form-group"></div>
                        
                        <div class="form-group"><div id="msg_permissions_form" class="alert"></div></div>

                    </div>

                    <div class="form-actions fluid">
                        <div class="col-md-12">
                            <div class='pull-right'>
                                <button id="js-remove-group" type="button" class="btn red" style="display:none">Elimina gruppo</button>
                                <button id="js_form_toggler" type="submit" class="btn blue" disabled>Salva permessi</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- END SAMPLE FORM PORTLET-->
    </div>
</div>
    
<div class="row">
    <div class="col-md-12">
        <div class="portlet box red">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fas fa-check"></i> Impostazioni viste
                </div>
                <div class="tools"></div>
            </div>
            <div class="portlet-body form">

                <form id="views_form" role="form" method="post" action="<?php echo base_url('db_ajax/save_views_permissions'); ?>" class="formAjax">
                    <div class="form-body">
                        <div class="table-scrollable table-scrollable-borderless">
                            <table id="views-permissions-datatable" class="table table-bordered table-condensed table-hover">
                                <thead>
                                    <tr>
                                        <th>VISTE</th>
                                        <?php foreach($dati['users_layout'] as $userID => $username): ?>
                                            <th>
                                                <label>
                                                    <input type="checkbox" data-toggle="tooltip" title="Attiva/Disattiva Tutti" class="js-toggle-all toggle" data-user="<?php echo $userID; ?>" />
                                                    <strong><?php echo (is_numeric($userID)? '': '<small class="text-muted" style="font-weight:normal">Gruppo</small> ') . $username; ?></strong>
                                                </label>
                                            </th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach($dati['layouts'] as $layoutID => $layout) : ?>
                                        <tr>
                                            <th>
                                                <label style="width: 220px;text-overflow: ellipsis;overflow: hidden;white-space: nowrap;" title="<?php echo $layout; ?>">
                                                    <input type="checkbox" data-toggle="tooltip" title="Attiva/Disattiva Tutti" class="js-toggle-all-horizontal toggle" data-layout="<?php echo $layoutID; ?>" />
                                                    <small class="text-muted"><?php echo $layoutID; ?> - </small> <?php echo $layout; ?>
                                                </label>
                                            </th>
                                            <?php foreach($dati['users_layout'] as $userID => $username): ?>
                                            <td>
                                                <label>
                                                    <input type="checkbox" class="js-toggle-view toggle" data-user="<?php echo $userID; ?>" value="<?php echo $userID; ?>" name="view[<?php echo $layoutID; ?>][]" <?php if(!isset($dati['unallowed'][$userID]) || !in_array($layoutID, $dati['unallowed'][$userID])) echo 'checked' ?> />
                                                    <small class="text-muted"><?php echo $username; ?></small>
                                                </label>
                                            </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="form-group"><div id="msg_views_form" class="alert"></div></div>

                    </div>

                    <div class="form-actions fluid">
                        <div class="col-md-12">
                            <div class='pull-right'>
                                <button type="submit" class="btn blue">Submit</button>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>
        <!-- END SAMPLE FORM PORTLET-->
    </div>
</div>



<script>
    
    $(document).ready(function () {
        var numero_di_campi = $('#views_form :input').length;
        if ((numero_di_campi + 20) > <?php echo $max_input_vars; ?>) {
            alert('Il limite max_input_vars (<?php echo $max_input_vars; ?>) è troppo basso rispetto al numero di campi in questa pagina ('+numero_di_campi+')! Funzionalità disattivata.');
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
            $.ajax({
                url: base_url+'get_ajax/permission_table/',
                type: 'post',
                data: {identifier:userId},
                success: function(view) {
                    
                    if (isNaN(parseInt(userId))) {
                        // Ho cliccato un gruppo e posso eliminarlo
                        $('#js-remove-group').show().on('click', function() {
                            if (confirm('Vuoi davvero eliminare il gruppo ' + userId + '? Tutti gli utenti ad esso associati dovranno essere riassegnati manualmente ad un altro gruppo')) {
                                $.post(base_url + 'db_ajax/delete_permission_group', {group:userId}, function() {
                                    window.location.reload();
                                });
                            }
                        });
                    }
                    
                    jqTableContainer.html(view);
                    jqTableContainer.fadeTo('fast', 1);
                    formButton.attr('disabled', view? false: true);
                }
            });
        });
    }
    
    
    
    
    
    
    
    
    
    var ViewPermissionsTable = function() {
        
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
            
            getCheckboxByUser: function (userId) {
                return $(this.checkViewSel + '[data-user="' + userId + '"]');
            },
            
            getCheckboxByLayout: function (layoutId) {
                return $(this.checkViewSel + '[name="view[' + layoutId + '][]"]');
            },
            
            init: function() {
                
                this.table = $(this.tableSel);
                this.checkAll = $(this.checkAllSel, this.table);
                this.checkAllHorizontal = $(this.checkAllSelHorizontal, this.table);
                this.checkView = $(this.checkViewSel, this.table);
                
                this.checkView.on('change', {widget:this}, function(event) {
                    event.data.widget.touchCheckbox();
                });
                
                this.checkAll.on('change', {widget:this}, function(event) {
                    var checkbox = $(this);
                    event.data.widget.touchCheckAll(checkbox.attr('data-user'), checkbox.is(':checked'));
                });
                
                this.checkAllHorizontal.on('change', {widget:this}, function(event) {
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