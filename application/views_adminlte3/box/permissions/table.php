<?php $is_administrator = (isset($dati['permissions']['permissions_admin']) and $dati['permissions']['permissions_admin'] === DB_BOOL_TRUE); ?>

<?php if ($dati['mode'] === 'user') : ?>

    <label class="col-md-3 control-label"><?php e('group'); ?></label>
    <div class="col-md-9">
        <select name="permissions_group" class="form-control input-large js-group">
            <option value=""></option>
            <?php foreach ($dati['groups'] as $group) : ?>
                <option value="<?php echo $group; ?>" <?php if (isset($dati['permissions']['permissions_group']) && $dati['permissions']['permissions_group'] === $group) echo 'selected' ?>><?php echo $group; ?></option>
            <?php endforeach; ?>
        </select>
    </div>

<?php elseif ($dati['mode'] === 'group.create') : ?>

    <label class="col-md-3 control-label"><?php e("name of the group"); ?></label>
    <div class="col-md-9">
        <input type="text" class="form-control input-large" name="permissions_group" value="<?php echo isset($dati['permissions']['permissions_group']) ? $dati['permissions']['permissions_group'] : ''; ?>" />
    </div>

<?php elseif ($dati['mode'] === 'group.edit') : ?>

    <label class="col-md-3 control-label"><?php e("rename group"); ?></label>
    <div class="col-md-9">
        <input type="text" class="form-control input-large" name="permissions_group_rename" />
    </div>
<?php endif; ?>
<div class="clearfix"></div>
<br />

<div id="all-permissions">
    <label class="col-md-3 control-label"><?php e("admin"); ?></label>
    <div class="col-md-9">
        <label class="radio-inline">
            <input type="radio" name="permissions_admin" value="<?php echo DB_BOOL_FALSE; ?>" class="toggle permission_toggle" <?php if (!$is_administrator) echo 'checked'; ?> />
            <?php e("no"); ?>
        </label>
        <label class="radio-inline">
            <input type="radio" name="permissions_admin" value="<?php echo DB_BOOL_TRUE; ?>" class="toggle permission_toggle" <?php if ($is_administrator) echo 'checked'; ?> />
            <?php e("yes"); ?>
        </label>
    </div>

    <div class="clearfix"></div>
    <br />

    <div id="permission_container" class="<?php echo ($is_administrator ? 'collapse' : 'in'); ?>">
        <table class="table table-striped table-condensed table-hover">
            <thead>
                <tr>
                    <th><?php e('entity'); ?></th>
                    <th class="w15" class="text-center"><?php e('none'); ?></th>
                    <th class="w15" class="text-center"><?php e('read'); ?></th>
                    <th class="w15" class="text-center"><?php e('write'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dati['entities'] as $entity) : ?>
                    <tr>
                        <td><?php echo $entity['entity_name']; ?></td>
                        <td class="text-center"><input type="radio" name="entities[<?php echo $entity['entity_id']; ?>]" value="<?php echo PERMISSION_NONE; ?>" class="toggle" <?php if (isset($dati['permissions_entities'][$entity['entity_id']]) and $dati['permissions_entities'][$entity['entity_id']] === PERMISSION_NONE) echo 'checked'; ?> /></td>
                        <td class="text-center"><input type="radio" name="entities[<?php echo $entity['entity_id']; ?>]" value="<?php echo PERMISSION_READ; ?>" class="toggle" <?php if (isset($dati['permissions_entities'][$entity['entity_id']]) and $dati['permissions_entities'][$entity['entity_id']] === PERMISSION_READ) echo 'checked'; ?> /></td>
                        <td class="text-center"><input type="radio" name="entities[<?php echo $entity['entity_id']; ?>]" value="<?php echo PERMISSION_WRITE; ?>" class="toggle" <?php if (!isset($dati['permissions_entities'][$entity['entity_id']]) || $dati['permissions_entities'][$entity['entity_id']] === PERMISSION_WRITE) echo 'checked'; ?> /></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="clearfix"></div>
        <br />

        <?php if (!empty($dati['modules'])) : ?>
            <table class="table table-striped table-condensed table-hover">
                <thead>
                    <tr>
                        <th><?php e('modules'); ?></th>
                        <th class="w15" class="text-center"><?php e('none'); ?></th>
                        <th class="w15" class="text-center"><?php e('access'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dati['modules'] as $mod) : ?>
                        <tr>
                            <td><?php echo $mod['modules_name']; ?></td>
                            <td class="text-center"><input type="radio" name="modules[<?php echo $mod['modules_name']; ?>]" value="<?php echo PERMISSION_NONE; ?>" class="toggle" <?php if (!isset($dati['permissions_modules'][$mod['modules_name']]) || $dati['permissions_modules'][$mod['modules_name']] === PERMISSION_NONE) echo 'checked'; ?> /></td>
                            <td class="text-center"><input type="radio" name="modules[<?php echo $mod['modules_name']; ?>]" value="<?php echo PERMISSION_WRITE; ?>" class="toggle" <?php if (isset($dati['permissions_modules'][$mod['modules_name']]) and $dati['permissions_modules'][$mod['modules_name']] === PERMISSION_WRITE) echo 'checked'; ?> /></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="clearfix"></div>
        <?php endif; ?>
    </div>
</div>

<div class="clearfix"></div>
<br />


<?php if ($dati['mode'] === 'user') : ?>
    <h4><strong><?php e('Set limits'); ?></strong> <small><?php e('Users with a set limit will only be able to see the data included in the subset assigned to them'); ?></small></h4>
    <table id="js_limits" class="table table-striped table-bordered table-condensed table-hover">
        <thead>
            <tr>
                <th><?php e('Entity'); ?></th>
                <th><?php e('Field'); ?></th>
                <th class="hidden"><?php e('Operator'); ?></th>
                <th><?php e('Values'); ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dati['limits'] as $k => $limit) : ?>
                <tr class="<?php if (empty($limit)) echo 'hide' ?>">
                    <td>
                        <select class="form-control input-sm js_limit_entity">
                            <option value=""></option>
                            <?php foreach ($dati['entities'] as $entity) : ?>
                                <option value="<?php echo $entity['entity_id']; ?>" <?php if (isset($limit['entity_id']) && $limit['entity_id'] == $entity['entity_id']) echo 'selected'; ?>><?php echo $entity['entity_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <select class="form-control input-sm js_limit_field" name="<?php echo "limits[{$k}][limits_fields_id]" ?>" data-value="<?php if (isset($limit['limits_fields_id'])) echo $limit['limits_fields_id']; ?>"></select>
                    </td>
                    <td class="hidden">
                        <input type="hidden" name="<?php echo "limits[{$k}][limits_operator]" ?>" value="in" class="js_limit_op" />
                    </td>
                    <td><input type="text" class="form-control input-sm js_limit_val" name="<?php echo "limits[{$k}][limits_value]" ?>" value="<?php if (isset($limit['limits_value'])) echo $limit['limits_value']; ?>" /></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-link" onclick="$(this).parent().parent().remove()" data-id="<?php if (isset($limit['limits_id'])) echo $limit['limits_id']; ?>">Elimina</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-center">
                    <button type="button" id="js_limit_add" class="btn btn-block btn-link"><?php e('Add'); ?></button>
                </td>
            </tr>
        </tfoot>
    </table>
<?php endif; ?>






<script>
    /**
     * Gestione limiti
     */
    $(document).ready(function() {
        'use strict';
        var jqAdd = $('#js_limit_add');
        var jqTable = $('#js_limits');
        var jqFirstRow = $('tbody tr.hide', jqTable);

        // Add a new row
        jqAdd.on('click', function() {
            var iNumRows = $('tbody tr', jqTable).size();
            var jqRow = jqFirstRow.clone().removeClass('hide');

            jqRow.appendTo($('tbody', jqTable));
            $('.js_limit_field', jqRow).attr('name', 'limits[' + iNumRows + '][limits_fields_id]');
            $('.js_limit_op', jqRow).attr('name', 'limits[' + iNumRows + '][limits_operator]');
            $('.js_limit_val', jqRow).attr('name', 'limits[' + iNumRows + '][limits_value]');
        });

        try {
            var token = JSON.parse(atob($(this).data('csrf')));
            var token_name = token.name;
            var token_hash = token.hash;
        } catch (e) {

            var token = JSON.parse(atob($('body').data('csrf')));
            var token_name = token.name;
            var token_hash = token.hash;
        }



        // Update the fields list
        jqTable.on('change', '.js_limit_entity', function() {
            var iValue = $(this).val();
            var jqField = $('.js_limit_field', $(this).parents('tr').filter(':first'));
            $('option', jqField).remove();

            $.ajax(base_url + 'get_ajax/entity_fields', {
                type: 'POST',
                data: {
                    entity_id: iValue,
                    [token_name]: token_hash
                },
                dataType: 'JSON',
                success: function(json) {
                    $.each(json, function(k, value) {
                        var jqOpt = $('<option></option>').val(value.fields_id).html(value.fields_draw_label);
                        if (jqField.attr('data-value') === value.fields_id) {
                            jqOpt.prop('selected', true);
                        }
                        jqField.append(jqOpt);
                    });
                    jqField.prepend($('<option></option>'));
                    jqField.trigger('change');
                }
            });
        });

        // Initialize field lists
        $('.js_limit_entity', jqTable).trigger('change');


        // Initialize multiselect
        jqTable.on('change', '.js_limit_field', function() {
            var iValue = $(this).val();
            if (iValue) {
                var jqObj = $('.js_limit_val', $(this).parents('tr').filter(':first'));
                initMultiselect(jqObj, iValue);
            }
        });





        var initMultiselect = function(jqObj, iFieldID) {
            jqObj.select2({
                multiple: true,
                minimumInputLength: 0,
                ajax: {
                    url: base_url + 'get_ajax/search_field_values',
                    dataType: 'json',
                    type: 'POST',
                    data: function(term, page) {
                        return {
                            q: term,
                            field_id: iFieldID
                        };
                    },
                    results: function(data, page) {
                        return {
                            results: data
                        };
                    }
                },
                initSelection: function(element, callback) {
                    var id = $(element).val();
                    if (id !== "") {
                        $.ajax(base_url + 'get_ajax/search_field_values', {
                            type: 'POST',
                            dataType: "json",
                            data: {
                                field_id: iFieldID,
                                id: id
                            }
                        }).done(function(data) {
                            callback(data);
                        });
                    }
                },
                formatResult: function(rowData) {
                    return rowData.name;
                },
                formatSelection: function(rowData) {
                    return rowData.name;
                }
            });
        };



        // Mostra/nascondi tabelle permessi alla disattivazione/attivazione del campo amministratore
        $('.permission_toggle').on('change', function() {
            'use strict';
            var jqToggle = $(this);
            console.log(jqToggle.val());
            $('#permission_container').collapse(
                (jqToggle.val() === '<?php echo DB_BOOL_TRUE; ?>') ?
                'hide' : // Sto dicendo che l'utente è un admin, quindi non serve mostrare i permessi - li ha già tutti
                'show' // Sto dicendo che l'utente non è admin, quindi mostra selezione permessi
            );
        });


        $('.js-group').on('change', function() {
            'use strict';
            var groupAssigned = $(this).val();

            if (groupAssigned) {
                $('#all-permissions').hide();
            } else {
                $('#all-permissions').show();
            }
        }).trigger('change');


    });
</script>