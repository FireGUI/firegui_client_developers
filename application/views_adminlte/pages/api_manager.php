<section class="content-header">
  <h1 class="clearfix">API Manager</h1>
</section>

<section class="content">
  <div class="row">
    <div class="col-lg-9" data-layout-box="58">
      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fas fa-th"></i> Elenco Token</h3>
        </div>
        
        <div class="box-body">
          <table id='elenco_token' default-limit="10" class="table table-striped table-bordered table-hover js_datatable" >
            <thead>
              <tr>
                <th >Data creazione</th>
                <th >Utente</th>
                <th >Token</th>
                
                <th >Ultimo utilizzo</th>
                <th >Richieste</th>
                <th >Errori</th>
                <th >Attivo</th>
                <th data-prevent-order>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($dati['tokens'] as $token) : ?>
                <?php //debug($token, true); ?>
                <tr class="odd gradeX" data-id="<?php echo $token['api_manager_tokens_id']; ?>">
                  <td><span class='hide'><?php echo $token['api_manager_tokens_creation_date']; ?></span><?php echo dateFormat($token['api_manager_tokens_creation_date']); ?></td>
                  <td><?php echo $token[LOGIN_NAME_FIELD]; ?> <?php echo $token[LOGIN_SURNAME_FIELD]; ?></td>
                  
                  <td><?php echo $token['api_manager_tokens_token']; ?></td>
                  <td><span class='hide'><?php echo $token['api_manager_tokens_last_use_date']; ?></span><?php echo dateFormat($token['api_manager_tokens_last_use_date'], 'd/m/Y H:m:s'); ?></td>
                  <td><?php echo $token['api_manager_tokens_requests']; ?></td>
                  <td><?php echo $token['api_manager_tokens_errors']; ?></td>
                  <td><?php echo ($token['api_manager_tokens_active']==DB_BOOL_TRUE)?'Sì':'No'; ?></td>
                  <td>
                    <div class="action-list">
                      <a href="<?php echo base_url("api_manager/permissions/{$token['api_manager_tokens_id']}"); ?>" class="btn btn-xs green js_open_modal">
                        <span class="fas fa-external-link-alt"></span>
                      </a>
                      <a href="<?php echo base_url('api_manager/delete_token/'.$token['api_manager_tokens_id']); ?>" data-confirm-text="Sei sicuro di voler eliminare questo record" class="btn btn-danger btn-xs js_confirm_button js_link_ajax " data-toggle="tooltip" title="Elimina">
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
          <h3 class="box-title"><i class=" fas fa-edit"></i> Autorizza nuovo token</h3>
        </div>
        
        <div class="box-body">
          <form id='form_token' role="form" method="post" action="<?php echo base_url("api_manager/add_token"); ?>" class="formAjax" enctype="multipart/form-data">
            <div class="form-body">
              
              <div class="row">
                <div class="col-lg-12">
                  <div class="form-group" >
                    <label class="control-label">Utente</label>
                    <select class="form-control _select2_standard  field_101" name="api_manager_tokens_user" data-source-field="" data-ref="api_manager_tokens_user" data-val="" >
                      <?php foreach ($dati['users'] as $id => $name) : ?>
                        <option value="<?php echo $id; ?>" ><?php echo $name; ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
              </div>
              <div class="row">
                
                <div class="col-lg-6">
                  <div class="form-group" >
                    <label class="control-label">Token pubblico</label>
                    <input type="text" name="api_manager_tokens_token" placeholder="Autogenerato..." class="form-control" placeholder="1000" value="">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-lg-6">
                  <div class="form-group" >
                    <label class="control-label">ms tra una richiesta e un'altra</label>
                    <input type="text" name="api_manager_tokens_ms_between_requests" class="form-control" placeholder="1000" value="">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="form-group" >
                    <label class="control-label">Richieste al minuto (max)</label>
                    <input type="text" name="api_manager_tokens_limit_per_minute" class="form-control" placeholder="50" value="">
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-lg-6">
                  <div class="form-group" >
                    <label class="control-label">Attivo</label>
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
              <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
              <button type="submit" class="btn btn-success">Salva</button>
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
