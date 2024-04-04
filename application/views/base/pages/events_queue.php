<section class="content-header">
    <h1>
        <?php e('Events Queue'); ?>
        <small>
            <?php e('Configure events queue to get records here'); ?> -
            <?php e('Copy this link to force queue: '); ?>
            <?php echo base_url('cron/runBackgroundProcesses/20'); ?>
        </small>
    </h1>
</section>


<section class="content">
    <div class="row">

        <div class="col-md-12 js_container_layout_box ">

            <div class="js_layout_box box box box-primary ">
                <div class="box-header with-border  ">

                    <div class="box-title">
                        <i class="fas fa-bars"></i>
                        <span data-layou_box_id="247" class="js_layouts_boxes_title  ">
                            Not executed PP
                        </span>
                    </div>

                </div>
                <table class="table js_datatable">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Date</th>
                            <th>Exec. Date</th>
                            <th>Event id</th>
                            <th>Referer</th>
                            <th>Code</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dati['events_not_executed'] as $event): ?>
                            <tr>
                                <td>
                                    <?php echo $event['_queue_pp_id']; ?>
                                </td>

                                <td>
                                    <?php echo $event['_queue_pp_date']; ?>
                                </td>
                                <td>
                                    <?php echo $event['_queue_pp_execution_date']; ?>
                                </td>
                                <td>
                                    <a target="_blank"
                                        href="https://my.openbuilder.net/main/events_builder/<?php echo $event['_queue_pp_event_id']; ?>">
                                        <small>
                                            <?php echo $event['_queue_pp_event_id']; ?> -
                                            <?php echo $event['fi_events_title']; ?>
                                        </small>
                                    </a>
                                </td>
                                <td>
                                    <?php echo str_ireplace(base_url(), '', $event['_queue_pp_referer']); ?>
                                </td>
                                <td>
                                    <?php
                                    $textContainerID = $event['_queue_pp_id'];
                                    $javascript = "event.preventDefault();$(this).parent().hide(); $('.text_{$textContainerID}').show();";

                                    ?>
                                    <div>
                                        <div onclick="<?php echo $javascript; ?>" style="cursor:pointer;">
                                            <?php echo nl2br(character_limiter(strip_tags($event['_queue_pp_code']), 20)); ?>
                                        </div>
                                        <a onclick="<?php echo $javascript; ?>" href="#">Vedi tutto</a>



                                    </div>
                                    <div class="text_<?php echo $textContainerID; ?>" style="display:none;">
                                        <?php echo strip_tags($event['_queue_pp_code']); ?>
                                    </div>


                                </td>
                                <td>
                                    <?php
                                    $textContainerID = $event['_queue_pp_id'];
                                    $javascript = "event.preventDefault();$(this).parent().hide(); $('.text_{$textContainerID}2').show();";

                                    ?>
                                    <div>
                                        <div onclick="<?php echo $javascript; ?>" style="cursor:pointer;">
                                            <?php echo nl2br(character_limiter(strip_tags($event['_queue_pp_data']), 20)); ?>
                                        </div>
                                        <a onclick="<?php echo $javascript; ?>" href="#">Vedi tutto</a>
                                    </div>
                                    <div class="text_<?php echo $textContainerID; ?>2" style="display:none;">
                                        <?php echo strip_tags($event['_queue_pp_data']); ?>
                                    </div>


                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>






    <div class="row">

        <div class="col-md-12 js_container_layout_box ">

            <div class="js_layout_box box box box-primary ">
                <div class="box-header with-border  ">

                    <div class="box-title">
                        <i class="fas fa-bars"></i>
                        <span data-layou_box_id="247" class="js_layouts_boxes_title  ">
                            Last 1000 executed PP
                        </span>
                    </div>

                </div>
                <table class="table js_datatable">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Date</th>
                            <th>Exec. Date</th>
                            <th>Event id</th>
                            <th>Referer</th>
                            <th>Code</th>
                            <th>Data</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dati['events_executed'] as $event): ?>
                            <tr>
                                <td>
                                    <?php echo $event['_queue_pp_id']; ?>
                                </td>

                                <td>
                                    <?php echo $event['_queue_pp_date']; ?>
                                </td>
                                <td>
                                    <?php echo $event['_queue_pp_execution_date']; ?>
                                </td>
                                <td>
                                    <a target="_blank"
                                        href="https://my.openbuilder.net/main/events_builder/<?php echo $event['_queue_pp_event_id']; ?>">
                                        <small>
                                            <?php echo $event['_queue_pp_event_id']; ?> -
                                            <?php echo $event['fi_events_title']; ?>
                                        </small>
                                    </a>
                                </td>
                                <td>

                                    <?php echo str_ireplace(base_url(), '', $event['_queue_pp_referer']); ?>
                                </td>
                                <td>
                                    <?php
                                    $textContainerID = $event['_queue_pp_id'];
                                    $javascript = "event.preventDefault();$(this).parent().hide(); $('.text_{$textContainerID}').show();";

                                    ?>
                                    <div>
                                        <div onclick="<?php echo $javascript; ?>" style="cursor:pointer;">
                                            <?php echo nl2br(character_limiter(strip_tags($event['_queue_pp_code']), 20)); ?>
                                        </div>
                                        <a onclick="<?php echo $javascript; ?>" href="#">Vedi tutto</a>
                                    </div>
                                    <div class="text_<?php echo $textContainerID; ?>" style="display:none;">
                                        <?php echo strip_tags($event['_queue_pp_code']); ?>
                                    </div>


                                </td>
                                <td>
                                    <?php
                                    $textContainerID = $event['_queue_pp_id'];
                                    $javascript = "event.preventDefault();$(this).parent().hide(); $('.text_{$textContainerID}2').show();";

                                    ?>
                                    <div>
                                        <div onclick="<?php echo $javascript; ?>" style="cursor:pointer;">
                                            <?php echo nl2br(character_limiter(strip_tags($event['_queue_pp_data']), 20)); ?>
                                        </div>
                                        <a onclick="<?php echo $javascript; ?>" href="#">Vedi tutto</a>
                                    </div>
                                    <div class="text_<?php echo $textContainerID; ?>2" style="display:none;">
                                        <?php echo strip_tags($event['_queue_pp_data']); ?>
                                    </div>


                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</section>

<!-- <div class="modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modal Title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <textarea class="php_shell_code form-control js_code_php_html">

                                                            </textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div> -->


<script>
    // $(document).ready(function () {

    //     var CodeMirrorEditor = CodeMirror.fromTextArea(document.getElementsByClassName("php_shell_code")[0], {
    //         lineNumbers: true,
    //         matchBrackets: true,
    //         mode: "text/x-php",
    //         indentUnit: 4,
    //         autoCloseTags: true,
    //         autoCloseBrackets: true,
    //         autoRefresh: true
    //     });
    // });
</script>