
<?php
$installed_modules = $this->db->order_by('modules_name')->get('modules')->result_array();
$current_client_version = VERSION;
?>
<style>
    .module-manager-container .btn {
        width:100px;
        height: 32px;
    }
    .module-manager-container .loading span{
        display: none;
    }
    .module-manager-container .loading::before {
  content: "";
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px dotted #999;
  border-top-color: transparent;
  border-radius: 50%;
  animation: spin 1s linear infinite;

}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
    </style>
<div class="row module-manager-container">

    	<div class="col-md-12">
		<table class="table js-installed_modules_table">
			<thead>
				<tr>
					<th>Thumbnail</th>
                    <th>Module name</th>
                    <th>Current version</th>
                    <th>Version date</th>

                    <th>Min client version</th>
                    <th>Auto update</th>
                    <th>Last update</th>
                    <th>Actions</th>
</tr>
</thead>
<tbody>
    <tr class="js-check_all_tr">
		<td colspan="7">
        </td>
        <td>

            <button class="btn btn-grey js-module_check_now_all"><span>Check all</span></button>
            <button class="btn btn-success js-module-update_all" style="display:none;">Update all</button>

        </td>
</tr>
	<?php foreach ($installed_modules as $module):?>
	<tr data-module_identifier="<?php echo($module['modules_identifier']); ?>" data-module_version_code="<?php echo $module['modules_version_code']; ?>">
		<td>
            <?php $image = $module['modules_thumbnail']?"https://my.openbuilder.net/uploads/modules_repository/{$module['modules_thumbnail']}":$this->layout->moduleAssets('module-manager', 'falcon.png'); ?>
            <img width="60px" src="<?php echo $image; ?>" alt=" " />
    </td>
		<td>
            <?php echo($module['modules_name']); ?><br />
            <small><?php echo($module['modules_identifier']); ?></small>
        </td>
		<td>
                <?php echo($module['modules_version']); ?><br />
            <small><?php echo($module['modules_version_code']); ?></small>
        </td>
        <td><?php echo(dateFormat($module['modules_version_date'])); ?></td>
        <td><?php echo($module['modules_min_client_version']); ?></td>
        <td>
        <div class="material-switch">
            <span style="display:none"><?php echo ($module['modules_auto_update'] == DB_BOOL_TRUE) ? t('Yes') : t('No'); ?></span>

            <input class="js-switch_auto_update" data-modules_id="<?php echo $module['modules_id']; ?>" value="<?php echo DB_BOOL_TRUE; ?>" id="switch_bool<?php echo $module['modules_id']; ?>" name="someSwitchOption001" type="checkbox" <?php if ($module['modules_auto_update'] == DB_BOOL_TRUE) : ?> checked="checked" <?php endif; ?> />
            <label for="switch_bool<?php echo $module['modules_id']; ?>" class="label-success"></label>
        </div>

        </td>
        <td><?php echo(dateFormat($module['modules_last_update'])); ?></td>
        <td>

            <button class="btn btn-grey js-module_check_now" data-module_identifier="<?php echo $module['modules_identifier']; ?>"><span>Check now</span></button>
            <button class="btn btn-warning js-module_update" data-module_identifier="<?php echo $module['modules_identifier']; ?>" style="display:none;">Update</button>
            <button class="btn btn-success js-module_already_updated" style="display:none;cursor:not-allowed;">Up to date!</button>
        </td>
		
	</tr>
	<?php endforeach;?>
</tbody>
</table>
</div>
</div>


<script>
    $(() => {
        $('.js-module_check_now').on('click', function () {
            loading(1);
            $(this).addClass('loading');
            var module_identifier = $(this).data('module_identifier');
            

             $.ajax({
                dataType:'JSON',
                url: base_url + "module-manager/main/check_module_version/"+module_identifier,
                success: function(data) {
                    //console.log(data);
                    var module_tr = $('tr[data-module_identifier="'+data.modules_repository_identifier+'"]');
                    var current_version_code = module_tr.data('module_version_code');
                    
                    if (current_version_code < data.modules_repository_version_code) {
                         
                        $('.js-module_check_now', module_tr).hide();
                        $('.js-module_update', module_tr).show();
                       
                    } else {
                        
                        $('.js-module_check_now', module_tr).hide();
                        $('.js-module_update', module_tr).hide();
                        $('.js-module_already_updated', module_tr).show();

                       
                        
                    }
                        
                    loading(0);
                },
                error: function() {
                    //alert("There was an error on getting directory size. Contact administrator");
                    loading(0);
                }
            });

            
        });
        $('.js-module_check_now_all').on('click', function() {
            $('.js-check_all_tr').hide();
            $('.js-module_check_now').trigger('click');
        });

        $('.js-module_update').on('click',  function() {
            loading(1);
            $(this).addClass('loading');
            
            var module_identifier = $(this).attr('data-module_identifier');
            
            //location.href=base_url+'module-manager/main/update_module/'+module_identifier;
            window.open(base_url+'module-manager/main/update_module/'+module_identifier, '_blank');
        });

        $('.js-switch_auto_update').on('click', function () {
            loading(1);
            var checked = $(this).attr('checked');
            var val = (checked)?0:1;
            var module_id = $(this).data('modules_id');
            $.ajax({
                //dataType:'JSON',
                url: base_url + "module-manager/main/set_auto_update/"+module_id+'/'+val,
                success: function(data) {
                    loading(0);
                },
                error: function() {
                    alert("An error occurred. Contact administrator");
                    loading(0);
                }
            });
        });
    });
</script>