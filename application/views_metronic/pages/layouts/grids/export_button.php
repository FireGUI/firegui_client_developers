<div class="col-md-<?php echo $cols; ?>">
    <div class="btn-group pull-right">
        <?php /*
        <button type="button" class="btn btn-fit-height grey dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true" aria-expanded="false">
        Export <i class="fas fa-angle-down"></i>
        </button>
        <ul class="dropdown-menu " role="menu">
            <li>
                <a target="_blank" href="<?php echo base_url("export/download_csv/{$grid['grids']['grids_id']}/$value_id"); ?>">Comma separated values (csv)</a>
            </li>
            <!--<li class="divider"></li>-->
            <li>
                <a target="_blank" href="<?php echo base_url("export/download_excel/{$grid['grids']['grids_id']}/$value_id"); ?>">Excel (xls)</a>
            </li>
        </ul> */ ?>
        
        <?php //debug($_SERVER["QUERY_STRING"]); ?>
        
        
        <a target="_blank" class="btn default" href="<?php echo base_url("export/download_csv/{$grid['grids']['grids_id']}/$value_id"); ?><?php echo ($_SERVER["QUERY_STRING"])?"/?{$_SERVER["QUERY_STRING"]}":'' ; ?>">Comma separated values (csv)</a>
        <a target="_blank" class="btn default" href="<?php echo base_url("export/download_excel/{$grid['grids']['grids_id']}/$value_id"); ?><?php echo ($_SERVER["QUERY_STRING"])?"/?{$_SERVER["QUERY_STRING"]}":'' ; ?>">Excel (xls)</a>
    </div>
</div>