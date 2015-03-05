


<h3 class="page-title"><?php e("sincronizzazioni google"); ?></h3>


<div class="row">
    <div class="col-md-4">
        <div class="portlet box purple">
            <div class="portlet-title">
                <div class="caption">
                    <i class="icon-plus-sign-alt"></i> <?php e("Aggiungi sincronizzazione"); ?>
                </div>
                <div class="tools">
                    <button id="tutorial" type="button" class="btn btn-xs purple pull-right" data-container="body" data-toggle="popover" data-placement="right" title="Rimborsi chilometrici" data-content="Affinchè vengano conteggiate le trasferte, queste devono essere regolarmente registrate come appuntamenti aventi categoria <strong>Lavoro</strong> o <strong>Riunione</strong>.">
                        <span class="icon-question-sign"></span>
                    </button>
                </div>
            </div>

            <div class="portlet-body">
                <?php if ($dati['sincronizzazione'] != array()) : ?>
                    <?php if ($dati['sincronizzazione']['google_calendar_calendario']) : ?>
                Sincronizzazione del calendario <strong><?php echo $dati['sincronizzazione']['google_calendar_calendario']; ?></strong> attiva (<a href="<?php echo base_url('google_calendar/delete_sincronizzazione/'.$dati['sincronizzazione']['google_calendar_id']); ?>">annulla</a>).
                    <?php else : ?>
                        <?php
                        $dati['client']->setAccessToken($dati['sincronizzazione']['google_calendar_token']);

                        if ($dati['client']->isAccessTokenExpired()) {
                            debug("Token scaduto...", true);
                            //debug($client,true);
                            $calendarList = array();
                        } else {
                            $calendarList = $dati['service']->calendarList->listCalendarList()->getItems();
                        }
                        ?>
                        <form action="<?php echo base_url('google_calendar/save_calendar/'.$dati['sincronizzazione']['google_calendar_id']); ?>" role="form" method="POST" class="form-horizontal formAjax" enctype="multipart/form-data">
                            <div class="form-body">

                                <div class="form-group">
                                    <label class="control-label col-md-3">Scegli quale calendario sincronizzare:</label>
                                    <div class="col-md-9">
                                        <select class="form-control" name="calendario">
                                            <?php foreach ($calendarList as $calendarListEntry) : ?>
                                                <option value="<?php echo $calendarListEntry->getSummary(); ?>"><?php echo $calendarListEntry->getSummary(); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="form-actions fluid">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn blue">Submit</button>
                                        <button type="button" class="btn default">Cancel</button>
                                    </div>
                                </div>
                        </form>
                    <?php endif; ?>
                <?php else : ?>
                    Attualmente non hai nessun calendario sincronizzato. <a href="<?php echo $dati['link_autorizzazione']; ?>">Clicca qui</a> per attivare la sincronizzazione.
                <?php endif; ?>


                <?php /*
                  <form role="form" method="post" action="<?php echo base_url('google_calendar/add_sincronizzazione'); ?>" class="form-horizontal formAjax" enctype="multipart/form-data">
                  <div class="form-body">

                  <div class="form-group">
                  <label class="control-label col-md-3">Entità appuntamenti</label>
                  <div class="col-md-9">
                  <select class="form-control" name="entity_id" id="js_entity_id">
                  <option></option>
                  <?php foreach($dati['entities'] as $e): ?>
                  <option value="<?php echo $e['entity_id'] ?>"><?php echo $e['entity_name'] ?></option>
                  <?php endforeach; ?>
                  </select>
                  </div>
                  <div class="clearfix"></div>
                  </div>
                  <div class="form-group">
                  <span>Configura campi</span>
                  <label class="control-label col-md-3">Titolo</label>
                  <div class="col-md-9">
                  <select class="js_fields form-control" name="gc_titolo">
                  <option></option>
                  <?php foreach ($dati['fields'] as $e_field): ?>
                  <option value="<?php echo $e_field['fields_name']; ?>"><?php echo $e_field['fields_name']; ?></option>
                  <?php endforeach; ?>
                  </select>
                  </div>
                  <div class="clearfix"></div>
                  <label class="control-label col-md-3">Descrizione</label>
                  <div class="col-md-9">
                  <select class="form-control" name="gc_descrizione">
                  <option></option>
                  <?php foreach ($dati['fields'] as $e_field): ?>
                  <option value="<?php echo $e_field['fields_name']; ?>"><?php echo $e_field['fields_name']; ?></option>
                  <?php endforeach; ?>
                  </select>
                  </div>
                  <div class="clearfix"></div>
                  </div>
                  </div>

                  <div class="form-actions fluid">
                  <div class="col-md-12">
                  <button type="submit" class="btn blue">Submit</button>
                  <button type="button" class="btn default">Cancel</button>
                  </div>
                  </div>
                  </form>

                 */ ?>
            </div>
        </div>
    </div>
</div>




<script>


    $(document).ready(function () {
        $('#tutorial').popover({html: true});

        /*$('#js_entity_id').on('change', function() {
         $('.js_fields').html('');
         $.ajax(base_url+'google_calendar/ajax/get_entity_fields_options/'+$(this).val(), {
         dataType: 'html',
         success: function(options_html) {
         $('.js_fields').html(options_html);
         }
         });
         });*/

    });

</script>