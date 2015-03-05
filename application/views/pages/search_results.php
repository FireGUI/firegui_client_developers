

<?php if ($dati['count_total'] > 0): ?>
    <h3 class="page-title"><?php e('Ricerca'); ?> <small><?php e('la ricerca della parola "%s" ha prodotto %s risultati', 0, array($dati['search_string'], $dati['count_total'])); ?></small></h3>

    <div class="row">
        <?php /* Cicla tutte le entità su cui ho ottenuto risultati */ ?>
        <?php foreach ($dati['results'] as $entity_result): ?>
            <div class="col-md-12">
                <div class="portlet box blue">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="icon-search"></i> <?php echo ucwords(str_replace('_', ' ', $entity_result['entity']['entity_name'])); ?>
                        </div>
                        <div class="tools"></div>
                    </div>
                    <div class="portlet-body">
                        <?php $link = $this->datab->get_detail_layout_link($entity_result['entity']['entity_id']); ?>
                        <table class="table table-condensed table-bordered table-hover table-scrollable js_search_datatable">
                            <thead>
                                <tr>
                                    <?php foreach ($entity_result['visible_fields'] as $field): ?>
                                        <?php if ($field['fields_preview'] == 't' AND $field['fields_draw_label']): ?>
                                            <th><?php echo $field['fields_draw_label']; ?></th>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                            
                                    <?php /*if($link): ?>
                                        <th></th>
                                    <?php endif;*/ ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($entity_result['data'] as $data): ?>
                                    <tr>
                                        <?php $first=true; foreach ($entity_result['visible_fields'] as $field): ?>
                                            <?php if ($field['fields_preview'] == 't' AND $field['fields_draw_label']): ?>
                                                <?php if($first && $link): $first=false; ?>
                                                    <td><a href="<?php echo $link.'/'.$data[$entity_result['entity']['entity_name'] . '_id']; ?>" class="btn btn-link btn-xs"><?php echo $data[$field['fields_name']]; ?></a></td>
                                                <?php else: ?>
                                                    <td><?php echo $data[$field['fields_name']]; ?></td>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                        
                                        <?php /*if($link): ?>
                                            <td><a href="<?php echo $link.'/'.$data[$entity_result['entity']['entity_name'] . '_id']; ?>" class="btn btn-link btn-xs">View</a></td>
                                        <?php endif;*/ ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php // debug($entity_result); ?>
                    </div>
                </div>
                <!-- END SAMPLE FORM PORTLET-->
            </div>
        <?php endforeach; ?>
    </div>





    <script>

        $(document).ready(function() {
            $('.js_search_datatable').each(function() {
                $(this).dataTable({
                    bLengthChange: false,
                    bFilter: false,
                    "oLanguage": {
                        "sUrl": base_url_template+"script/datatable.transl.json"
                    }
                });
            });
        });

    </script>
<?php elseif($dati['count_total'] === 0): ?>
    <h3 class="page-title"><?php e('Ricerca'); ?> <small><?php e('nessun risultato trovato per la ricerca effettuata: %s', 0, array($dati['search_string'])); ?></small></h3>
<?php else: ?>
    <h3 class="page-title"><?php e('Ricerca'); ?> <small><?php e('la stringa di ricerca deve essere di almeno 3 lettere', 0); ?></small></h3>
<?php endif; ?>