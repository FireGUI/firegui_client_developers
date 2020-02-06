<section class="content-header">
  <h1 class="clearfix">API Manager</h1>
</section>

<section class="content">
  <div class="row">
    <div class="col-lg-9" data-layout-box="58">
      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fas fa-th"></i> <?php e('Tokens list'); ?></h3>
        </div>

        <div class="box-body">
          <table id='elenco_token' default-limit="10" class="table table-striped table-bordered table-hover js_datatable">
            <thead>
              <tr>
                <th><?php e('Creation date'); ?></th>
                <th><?php e('User'); ?></th>
                <th><?php e('Token'); ?></th>
                <th><?php e('Last access'); ?></th>
                <th><?php e('Requests'); ?></th>
                <th><?php e('Errors'); ?></th>
                <th><?php e('Active'); ?></th>
                <th data-prevent-order><?php e('Actions'); ?></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($dati['tokens'] as $token) : ?>
                <?php //debug($token, true); 
                ?>
                <tr class="odd gradeX" data-id="<?php echo $token['api_manager_tokens_id']; ?>">
                  <td><span class='hide'><?php echo $token['api_manager_tokens_creation_date']; ?></span><?php echo dateFormat($token['api_manager_tokens_creation_date']); ?></td>
                  <td><?php echo $token[LOGIN_NAME_FIELD]; ?> <?php echo $token[LOGIN_SURNAME_FIELD]; ?></td>

                  <td><?php echo $token['api_manager_tokens_token']; ?></td>
                  <td><span class='hide'><?php echo $token['api_manager_tokens_last_use_date']; ?></span><?php echo dateFormat($token['api_manager_tokens_last_use_date'], 'd/m/Y H:m:s'); ?></td>
                  <td><?php echo $token['api_manager_tokens_requests']; ?></td>
                  <td><?php echo $token['api_manager_tokens_errors']; ?></td>
                  <td><?php echo ($token['api_manager_tokens_active'] == DB_BOOL_TRUE) ? 'Yes' : 'No'; ?></td>
                  <td>
                    <div class="action-list">
                      <a href="<?php echo base_url("api_manager/permissions/{$token['api_manager_tokens_id']}"); ?>" class="btn btn-xs btn-success js_open_modal" data-csrf="<?php echo base64_encode(json_encode(get_csrf())); ?>">
                        <span class="fas fa-external-link-alt"></span>
                      </a>
                      <a href="<?php echo base_url('api_manager/delete_token/' . $token['api_manager_tokens_id']); ?>" data-confirm-text="<?php e('Are you sure to delete this record?'); ?>" class="btn btn-danger btn-xs js_confirm_button js_link_ajax " data-csrf="<?php echo base64_encode(json_encode(get_csrf())); ?>" data-toggle="tooltip" title="<?php e('Delete'); ?>">
                        <span class="fas fa-times"></span>
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-lg-3">
      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title"><i class=" fas fa-edit"></i> <?php e('Authorize token'); ?></h3>
        </div>

        <div class="box-body">
          <form id='form_token' role="form" method="post" action="<?php echo base_url("api_manager/add_token"); ?>" class="formAjax" enctype="multipart/form-data">
            <?php add_csrf(); ?>
            <div class="form-body">

              <div class="row">
                <div class="col-lg-12">
                  <div class="form-group">
                    <label class="control-label"><?php e('User'); ?></label>
                    <select class="form-control _select2_standard  field_101" name="api_manager_tokens_user" data-source-field="" data-ref="api_manager_tokens_user" data-val="">
                      <?php foreach ($dati['users'] as $id => $name) : ?>
                        <option value="<?php echo $id; ?>"><?php echo $name; ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
              </div>
              <div class="row">

                <div class="col-lg-6">
                  <div class="form-group">
                    <label class="control-label"><?php e('Public token'); ?></label>
                    <input type="text" name="api_manager_tokens_token" placeholder="Autogenerato..." class="form-control" placeholder="1000" value="">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-lg-6">
                  <div class="form-group">
                    <label class="control-label"><?php e('ms between requests'); ?></label>
                    <input type="text" name="api_manager_tokens_ms_between_requests" class="form-control" placeholder="1000" value="">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="form-group">
                    <label class="control-label"><?php e('Requests per minute (max)'); ?></label>
                    <input type="text" name="api_manager_tokens_limit_per_minute" class="form-control" placeholder="50" value="">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-lg-6">
                  <div class="form-group">
                    <label class="control-label"><?php e('Active'); ?></label>
                    <input name="api_manager_tokens_active" type="checkbox" class="_form-control" value="<?php echo DB_BOOL_TRUE; ?>">
                  </div>
                </div>

              </div>

              <div class="row">
                <div class="col-md-12">
                  <div id='msg_form_token' class="alert alert-danger hide"></div>
                </div>
              </div>
            </div>

            <div class="form-actions right">
              <button type="button" class="btn btn-default" data-dismiss="modal"><?php e('Cancel'); ?></button>
              <button type="submit" class="btn btn-success"><?php e('Save'); ?></button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
  $(document).ready(function() {
    $('body').addClass('page-sidebar-closed').find('.page-sidebar-menu').addClass('page-sidebar-menu-closed');
  });
</script>