<?php
$token = $dati['token'];

// Funzione helper per generare le opzioni del select per i permessi delle entità
function generate_entity_select_options($selected = '')
{
    $options = [
        '' => "All permissions",
        '0' => "No permissions",
        '1' => "R (read only)",
        '2' => "RW (update only)",
        '3' => "RW (insert only)",
        '4' => "RW (insert and update)",
        '5' => "RWD (all)"
    ];

    $html = '';
    foreach ($options as $value => $text) {
        $html .= '<option value="' . $value . '"' . ($selected == $value ? ' selected' : '') . '>' . $text . '</option>';
    }
    return $html;
}

// Recupera tutte le entità
$entities = $this->apilib->tableList();

// Recupera i permessi per tutte le entità
$all_permissions = [];
foreach ($entities as $entity) {
    $entity_obj = $this->datab->get_entity_by_name($entity['name']);

    // Entity permissions
    $entity_permissions = $this->db->get_where('api_manager_permissions', [
        'api_manager_permissions_token' => $token,
        'api_manager_permissions_entity' => $entity_obj['entity_id'],
    ])->row_array();

    // Fields permissions
    $fields_permissions = $this->db
        ->join('fields', 'fields.fields_id = api_manager_fields_permissions.api_manager_fields_permissions_field', 'LEFT')
        ->where('api_manager_fields_permissions_token', $token)
        ->where("api_manager_fields_permissions_field IN (SELECT fields_id FROM fields WHERE fields_entity_id = '{$entity_obj['entity_id']}')", null, false)
        ->get('api_manager_fields_permissions')->result_array();

    $all_permissions[$entity['name']] = [
        'entity' => $entity_permissions,
        'fields' => array_column($fields_permissions, 'api_manager_fields_permissions_chmod', 'fields_name')
    ];
}

// Definizione dei livelli di permesso
$permission_levels = [
    '1' => "R (read only)",
    '2' => "RW (update only)",
    '3' => "RW (insert only)",
    '4' => "RW (insert and update)",
    '5' => "RWD (all)"
];
?>

<div class="modal fade modal-scroll" tabindex="-1" role="dialog" aria-labelledby="api_permissions_label"
    aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="api_permissions_label"><?php e('Specific permissions'); ?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <form id="form_permessi" role="form" method="post"
                action="<?php echo base_url("api_manager/set_permissions/{$token}"); ?>" class="form formAjax">
                <?php add_csrf(); ?>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="permissions-grid" class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Entity</th>
                                    <th>Entity Permissions</th>
                                    <th>Where Clause</th>
                                    <?php foreach ($permission_levels as $level => $label): ?>
                                        <th><?php echo htmlspecialchars($label); ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($entities as $entity): ?>
                                    <?php $entity_fields = $this->datab->get_entity_by_name($entity['name'])['fields']; ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($entity['name']); ?></td>
                                        <td>
                                            <select class="form-control _select2_standard"
                                                name="entity_permission[<?php echo $entity['name']; ?>]">
                                                <?php echo generate_entity_select_options($all_permissions[$entity['name']]['entity']['api_manager_permissions_chmod'] ?? ''); ?>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control"
                                                name="entity_where[<?php echo $entity['name']; ?>]"
                                                value="<?php echo htmlspecialchars($all_permissions[$entity['name']]['entity']['api_manager_permissions_where'] ?? ''); ?>">
                                        </td>
                                        <?php foreach ($permission_levels as $level => $label): ?>
                                            <td>
                                                <select class="form-control js_multiselect_over" multiple
                                                    name="field_permission[<?php echo $entity['name']; ?>][<?php echo $level; ?>][]">
                                                    <?php foreach ($entity_fields as $field): ?>
                                                        <?php $selected = (isset($all_permissions[$entity['name']]['fields'][$field['fields_name']]) && $all_permissions[$entity['name']]['fields'][$field['fields_name']] == $level) ? 'selected' : ''; ?>
                                                        <option value="<?php echo htmlspecialchars($field['fields_name']); ?>" <?php echo $selected; ?>>
                                                            <?php echo htmlspecialchars($field['fields_name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div id='msg_form_permessi' class="alert alert-danger hide"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-danger"
                        data-dismiss="modal"><?php e('Cancel'); ?></button>
                    <button type="submit" class="btn btn-sm btn-primary"><?php e('Save'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
    const permissionMap = {
        '': ['1', '2', '3', '4', '5'],  // All permissions
        '0': [], // No permissions
        '1': ['1'], // R (read only)
        '2': ['2'], // RW (update only)
        '3': ['3'], // RW (insert only)
        '4': ['2', '3'], // RW (insert and update)
        '5': ['1', '2', '3', '4', '5']  // RWD (all)
    };

    const permissionLevels = ['1', '2', '3', '4', '5'];

    $('#permissions-grid').DataTable({
        "paging": true,
        "scrollY": "500px",
        "scrollX": true,
        "scrollCollapse": false,
        "autoWidth": true,
        "fixedHeader": true,
        "fixedColumns": {
            leftColumns: 3
        },
        "drawCallback": function (settings) {
            $('.js_multiselect_over').select2({
                allowClear: true,
                minimumInputLength: 0
            });
        }
    });

    $(window).on('resize', function () {
        table.columns.adjust().draw();
    });

    // Function to update field permissions based on entity permission
    function updateFieldPermissions(entityRow, selectedPermission) {
        const permissionsToSelect = permissionMap[selectedPermission];
        
        entityRow.find('select.js_multiselect_over').each(function(index) {
            const $select = $(this);
            const columnIndex = index + 1; // +1 because permission levels start from 1
            const permissionLevel = permissionLevels[index];
            
            if (permissionsToSelect.includes(permissionLevel)) {
                $select.find('option').prop('selected', true);
                $select.prop('disabled', false);
            } else {
                $select.find('option').prop('selected', false);
                $select.prop('disabled', true);
            }
            
            $select.trigger('change');
        });
    }

    //TODO: queste tendine vengono rigenerate al volo, quindi il listener deve tener conto di questa cosa
    // Event listener for entity permission changes

    $('select[name^="entity_permission"]').on('change', function() {
        const selectedPermission = $(this).val();
        const entityRow = $(this).closest('tr');
        updateFieldPermissions(entityRow, selectedPermission);
    });

    // Initial setup
    $('select[name^="entity_permission"]').each(function() {
        const selectedPermission = $(this).val();
        const entityRow = $(this).closest('tr');
        updateFieldPermissions(entityRow, selectedPermission);
    });
});
</script>