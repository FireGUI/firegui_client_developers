<section class="content-header">
    <h1>
        <?php e('Events Queue'); ?>
        <small>
            <?php e('Configure events queue to get records here'); ?> - <?php e('Copy this link to force queue: '); ?> <?php echo base_url('cron/runBackgroundProcesses/20'); ?>
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
                                    <?php echo character_limiter($event['_queue_pp_code'], 20); ?>
                                </td>
                                <td>
                                    <?php echo character_limiter($event['_queue_pp_data'], 20); ?>
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
                                    <?php echo character_limiter($event['_queue_pp_code'], 20); ?>
                                </td>
                                <td>
                                    <?php echo character_limiter($event['_queue_pp_data'], 20); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</section>