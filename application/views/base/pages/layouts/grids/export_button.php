<div class="col-md-<?php echo $cols; ?>">
    <div class='btn-group pull-right'>
        <button type='button' class='btn btn-fit-height pull-right grey dropdown-toggle export_grid_data' data-toggle='dropdown' data-hover='dropdown' data-delay='1000' data-close-others='true' aria-expanded='false'>
            <?php e('Export'); ?>
            <i class="fas fa-angle-down"></i>
        </button>
        <ul class="dropdown-menu " role="menu">
            <li>
                <a target="_blank" href="<?php echo base_url("export/download_csv/{$grid['grids']['grids_id']}/$value_id"); ?><?php echo ($_SERVER['QUERY_STRING']) ? "/?{$_SERVER['QUERY_STRING']}" : ''; ?>"><?php e('Comma separated values (csv)'); ?></a>
            </li>
            <li>
                <a target="_blank" href="<?php echo base_url("export/download_excel/{$grid['grids']['grids_id']}/$value_id"); ?><?php echo ($_SERVER['QUERY_STRING']) ? "/?{$_SERVER['QUERY_STRING']}" : ''; ?>"><?php e('Excel (xls)'); ?></a>
            </li>
            <li>
                <a target='_blank' href="<?php echo base_url("export/download_pdf/{$grid['grids']['grids_id']}/$value_id"); ?><?php echo ($_SERVER['QUERY_STRING']) ? "/?{$_SERVER['QUERY_STRING']}" : ''; ?>"><?php e('PDF (verticale)'); ?></a>
            </li>
            <li>
                <a target='_blank' href="<?php echo base_url("export/download_pdf/{$grid['grids']['grids_id']}/$value_id"); ?><?php echo ($_SERVER['QUERY_STRING']) ? "/?{$_SERVER['QUERY_STRING']}&orientation=landscape" : '/?orientation=landscape'; ?>"><?php e('PDF (orizzontale)'); ?></a>
            </li>
        </ul>
    </div>
</div>
