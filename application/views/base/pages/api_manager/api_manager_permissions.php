<?php
$token = $dati['token'];

// Funzione helper per generare le opzioni del select per i permessi delle entità
function generate_entity_select_options($selected = '')
{
    $options = [
        //'10' => "All permissions",
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
                action="<?php echo base_url("api_manager/set_permissions/{$token}"); ?>" class="form __formAjax">
                <?php add_csrf(); ?>
                <div class="mb-3">
                    <button type="button" id="grant_all_permissions" class="btn btn-success mr-2">Grant All Permissions</button>
                    <button type="button" id="grant_all_permissions_only_read" class="btn btn-warning mr-2">Grant All Permissions (only read)</button>
                    <button type="button" id="remove_all_permissions" class="btn btn-danger">Remove All Permissions</button>
                </div>
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
        function initializeMultiselect() {
        $('.js_multiselect_over').select2({
            allowClear: true,
            minimumInputLength: 0
        });
    }
    const permissionMap = {
        '': ['1', '2', '3', '4', '5'],  // All permissions
        '0': [], // No permissions
        '1': ['1'], // R (read only)
        '2': ['2'], // RW (update only)
        '3': ['3'], // RW (insert only)
        '4': ['4'], // RW (insert and update)
        '5': ['5']  // RWD (all)
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
            initializeMultiselect();
            bindEntityPermissionEvents();
        }
    });

    

    // Function to update field permissions based on entity permission
    function updateFieldPermissions(entityRow, selectedPermission) {
        
        const permissionsToSelect = permissionMap[selectedPermission];

        $('.js_multiselect_over').find('option').prop('selected', false);
        $('.js_multiselect_over').trigger('change');
        

        // entityRow.find('select.js_multiselect_over').each(function(index) {
        //     const $select = $(this);
        //     const permissionLevel = permissionLevels[index];
        //     console.log(permissionLevel);
        //     if (permissionsToSelect.includes(permissionLevel)) {
        //         $select.find('option').prop('selected', true);
        //         $select.prop('disabled', false);
        //     } else {
        //         $select.find('option').prop('selected', false);
        //         $select.prop('disabled', true);
        //     }

        //     $select.trigger('change');
        // });
    }

    function updateAllPages(permissionValue) {
        var table = $('#permissions-grid').DataTable();
        const totalPages = table.page.info().pages;
        const currentPage = table.page();
        let currentProcessingPage = 0;

        function processPage() {
            table.page(currentProcessingPage).draw('page');

            table.rows({page: 'current'}).nodes().each(function(row) {
                const $row = $(row);
                const $select = $row.find('select[name^="entity_permission"]');
                $select.val(permissionValue).trigger('change');
                //updateFieldPermissions($row, permissionValue);
            });

            initializeMultiselect();

            currentProcessingPage++;

            if (currentProcessingPage < totalPages) {
                setTimeout(processPage, 1);
            } else {
                table.page(currentPage).draw('page');
                initializeMultiselect();
            }
        }

        processPage();
    }

    function bindEntityPermissionEvents() {
        $('select[name^="entity_permission"]').off('change').on('change', function() {
            const selectedPermission = $(this).val();
            const entityRow = $(this).closest('tr');
            updateFieldPermissions(entityRow, selectedPermission);
        });

        // Initial setup for visible rows
        // $('select[name^="entity_permission"]').each(function() {
        //     const selectedPermission = $(this).val();
        //     const entityRow = $(this).closest('tr');
        //     updateFieldPermissions(entityRow, selectedPermission);
        // });
    }

   $('#grant_all_permissions').on('click', function() {
        updateAllPages('5');
    });

    $('#grant_all_permissions_only_read').on('click', function() {
        updateAllPages('1');
    });

    $('#remove_all_permissions').on('click', function() {
        updateAllPages('0');
    });

    $('#form_permessi').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        var table = $('#permissions-grid').DataTable();
        
table.search('').draw();

        // Function to process a single page
        function processPage(pageIndex, callback) {
            table.page(pageIndex).draw('page');
            
            table.rows({page: 'current'}).every(function(rowIdx) {
                var rowData = this.data();
                var entityName = rowData[0]; // Assuming entity name is in the first column
                
                // Add entity permission
                var entityPermission = $('select[name="entity_permission[' + entityName + ']"]').val();
                formData.append('entity_permission[' + entityName + ']', entityPermission || '');
                
                // Add entity where clause
                var entityWhere = $('input[name="entity_where[' + entityName + ']"]').val();
                formData.append('entity_where[' + entityName + ']', entityWhere || '');
                
                // Add field permissions
                $('select[name^="field_permission[' + entityName + ']"]').each(function() {
                    var fieldName = $(this).attr('name');
                    var fieldValue = $(this).val();
                    if (fieldValue && fieldValue.length > 0) {
                        fieldValue.forEach(function(value) {
                            formData.append(fieldName, value);
                        });
                    } else {
                        formData.append(fieldName, '');
                    }
                });
            });
            
            callback();
        }
        
        // Process all pages
        var totalPages = table.page.info().pages;
        var currentPage = 0;
        
        function processNextPage() {
            if (currentPage < totalPages) {
                processPage(currentPage, function() {
                    currentPage++;
                    processNextPage();
                });
            } else {
                // All pages processed, send the AJAX request
                sendAjaxRequest();
            }
        }
        
        function sendAjaxRequest() {
            $.ajax({
                url: $('#form_permessi').attr('action'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function(response) {
                    handleSuccess(response);
                    // Handle success (e.g., close modal, show success message)
                },
                error: function(xhr, status, error) {
                    console.error('Error saving permissions:', error);
                    // Handle error (e.g., show error message)
                }
            });
        }
        
        // Start processing pages
        processNextPage();
    });
});
</script>