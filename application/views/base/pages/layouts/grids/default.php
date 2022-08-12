<?php if (empty($grid_data['data'])) : ?>
<p>No records found</p>
<?php else : ?>

<table class="table table-condensed table-striped nowrap table-bordered table-hover <?php echo $grid['grids']['grids_append_class']; ?>">
    <thead>
        <tr>
            <?php foreach ($grid['grids_fields'] as $field) : ?>
            <th><?php e($field['grids_fields_column_name']);  ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($grid_data['data'] as $dato) : ?>
        <tr class="odd gradeX" data-id="<?php echo $dato[$grid['grids']['entity_name'] . "_id"]; ?>">
            <?php foreach ($grid['grids_fields'] as $field) : ?>
            <td><?php echo $this->datab->build_grid_cell($field, $dato); ?></td>
            <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>