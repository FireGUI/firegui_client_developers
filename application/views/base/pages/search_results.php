    <?php if ($dati['count_total'] > 0) : ?>

        <section class="content-header">
            <h1>
                <?php e('Search'); ?>
                <small><?php e('The search for "%s" has returned %s results', 0, array($dati['search_string'], $dati['count_total'])); ?></small>
            </h1>
        </section>
        <section class="content">
            <div class="row">
                <?php /* Cicla tutte le entità su cui ho ottenuto risultati */ ?>
                <?php foreach ($dati['results'] as $entity_result) :
                    $link = $this->datab->get_detail_layout_link($entity_result['entity']['entity_id']);
                    usort($entity_result['visible_fields'], function ($f1, $f2) {
                        if ($f2['fields_preview'] == DB_BOOL_TRUE) {
                            return 1;
                        } elseif ($f1['fields_preview'] == DB_BOOL_TRUE) {
                            return -1;
                        } else {
                            return 0;
                        }
                    });



                    $fields = array_values(array_filter($entity_result['visible_fields'], function ($field) {
                        return $field['fields_draw_label'] && in_array($field['fields_type'], [DB_INTEGER_IDENTIFIER, 'INT', 'VARCHAR', 'varchar', 'FLOAT', 'TIMESTAMP WITHOUT TIME ZONE']);
                    }));
                    foreach ($fields as $i => $field) {

                        // Show only preview fields
                        if ($field['fields_preview'] != DB_BOOL_TRUE) {
                            unset($fields[$i]);
                        }
                    }
                    if (empty($entity_result['data']) || empty($fields) || empty($link)) {
                        continue;
                    };

                ?>
                    <div class="col-md-12">
                        <div class="box box-primary">
                            <div class="box-header">

                                <i class="fas fa-search"></i>
                                <h4 class="box-title"><?php echo ucwords(str_replace('_', ' ', $entity_result['entity']['entity_name'])); ?></h4>

                            </div>
                            <div class="box-body">
                                <?php
                                // Mostro solo i campi che hanno qualcosa da mostrare...


                                ?>
                                <table class="table table-condensed table-bordered table-hover table-scrollable table-scrollable-borderless js_search_datatable">
                                    <thead>
                                        <tr>
                                            <?php foreach ($fields as $i => $field) : ?>
                                                <?php
                                                // Show only preview fields
                                                if ($field['fields_preview'] != DB_BOOL_TRUE) {
                                                    unset($fields[$i]);
                                                    continue;
                                                } ?>
                                                <th><?php
                                                    $label = $field['fields_draw_label'];
                                                    if ($field['fields_entity_id'] != $entity_result['entity']['entity_id']) {
                                                        $ePrefix = ucwords(str_replace('_', ' ', $field['entity_name']));
                                                        // Non voglio aggiungere un eventuale
                                                        // prefisso alla label se questa è
                                                        // già prefissata:
                                                        // caso frequente, le support table
                                                        if (stripos(trim($label), trim($ePrefix)) !== 0) {
                                                            $label = $ePrefix . ' ' . $label;
                                                        }
                                                    }

                                                    echo $label;
                                                    ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($entity_result['data'] as $data) : ?>


                                            <tr>
                                                <?php foreach ($fields as $i => $field) : ?>
                                                    <td>
                                                        <?php
                                                        echo ($link && !$i) ? //Mostro per le prime 4 colonne il link al dettaglio
                                                            anchor($link . '/' . $data[$entity_result['entity']['entity_name'] . '_id'], ($data[$field['fields_name']]) ? $data[$field['fields_name']] : ' ') : $this->datab->build_grid_cell($field, $data);
                                                        ?>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        </section>
    <?php elseif ($dati['count_total'] === 0) : ?>
        <div class="col-md-12">
            <h4 class="box-title"><?php e('Ricerca'); ?> <small><?php e('no results for your keyword: %s', 0, array($dati['search_string'])); ?></small></h4>
        </div>
    <?php else : ?>

        <div class="callout ">

            <h3><?php e('Search'); ?></h3>

            <p><?php e('Search query must be at least 3 characters', 0); ?></p>
        </div>

    <?php endif; ?>





    <script>
        $(document).ready(function() {
            'use strict';
            $.fn.dataTableExt.oApi.fnHideEmptyColumns = function(oSettings, tableObject) {
                /**
                 * This plugin hides the columns that are empty.
                 * If you are using datatable inside jquery tabs
                 * you have to add manually this piece of code
                 * in the tabs initialization
                 * $("#mytable").datatables().fnAdjustColumnSizing();
                 * where #mytable is the selector of table
                 * object pointing to this plugin.
                 * This plugin can be invoked from
                 * <a href="//legacy.datatables.net/ref#fnInitComplete">fnInitComplete</a> callback.
                 * @author John Diaz
                 * @version 1.0
                 * @date 06/28/2013
                 */
                var selector = tableObject.selector;
                var columnsToHide = [];

                $(selector).find('th').each(function(i) {

                    var columnIndex = $(this).index();
                    var rows = $(this).parents('table').find('tr td:nth-child(' + (i + 1) + ')'); //Find all rows of each column 
                    var rowsLength = $(rows).length;
                    var emptyRows = 0;

                    rows.each(function(r) {
                        if (!this.innerHTML.trim() == '') {
                            emptyRows++;
                        } else {
                            console.log(this.innerHTML.trim());
                        }
                    });

                    if (emptyRows == rowsLength) {
                        columnsToHide.push(columnIndex); //If all rows in the colmun are empty, add index to array
                    }
                });
                for (var i = 0; i < columnsToHide.length; i++) {
                    tableObject.fnSetColumnVis(columnsToHide[i], false); //Hide columns by index
                }
                /**
                 * The following line doesn't work when the plugin
                 * is used inside jquery tabs, then you should
                 * add manually this piece of code inside
                 * the tabs initialization where ("#myTable") is
                 * your table id selector
                 * ej: $("#myTable").dataTable().fnAdjustColumnSizing();
                 */

                tableObject.fnAdjustColumnSizing();
            }

            $('.js_search_datatable').each(function() {
                $(this).dataTable({
                    bLengthChange: false,
                    bFilter: false,
                    "oLanguage": {
                        "sUrl": base_url_scripts + "script/datatable.transl.json"
                    },
                    "fnInitComplete": function() {
                        this.fnHideEmptyColumns(this);
                    }
                });
            });
        });
    </script>