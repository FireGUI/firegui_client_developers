<?php
$expenses = $this->db->query("SELECT SUM(CASE  WHEN expenses_type = 1 THEN expenses_amount ELSE expenses_amount*(-1) END) as s FROM expenses WHERE expenses_project_id = '{$value_id}'")->row()->s;
$hours = $this->db->query("SELECT SUM(timesheet_total_hours) as s FROM timesheet WHERE timesheet_project = '{$value_id}'")->row()->s;
$hours_last_30_days = $this->db->query("SELECT SUM(timesheet_total_hours) as s FROM timesheet WHERE timesheet_project = '{$value_id}' AND timesheet_creation_date > (NOW() - INTERVAL 30 day)")->row()->s;
$hours_perc_30_days = ($hours_last_30_days / $hours) * 100;

$hours_balance = $this->db->query("SELECT SUM(billable_hours_hours) as s FROM billable_hours WHERE billable_hours_project_id = '{$value_id}'")->row()->s;
$hours_balance = (empty($hours_balance)) ? 0.00 : $hours_balance;

$tickets = $this->db->query("SELECT COUNT(*) as s FROM tickets WHERE tickets_project_id = '{$value_id}'")->row()->s;
$tickets_last_30_days = $this->db->query("SELECT COUNT(*) as s FROM tickets WHERE tickets_project_id = '{$value_id}' AND tickets_creation_date > (NOW() - INTERVAL 30 day)")->row()->s;
$tickets_perc_30_days = ($tickets_last_30_days / $hours) * 100;
?>

<div class="row">
    <div class="col-md-6 col-sm-6 col-xs-12">
        <div class="info-box bg-aqua">
            <span class="info-box-icon"><i class="fas fa-hourglass"></i></span>

            <div class="info-box-content">
                <span class="info-box-text">Hours Balance</span>
                <span class="info-box-number"><?php echo $hours_balance; ?></span>

                <div class="progress">
                    <div class="progress-bar" style="width: 100%"></div>
                </div>
                <span class="progress-description">
                </span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <!-- /.col -->
    <div class="col-md-6 col-sm-6 col-xs-12">
        <div class="info-box bg-green">
            <span class="info-box-icon"><i class="fa fa-thumbs-o-up"></i></span>

            <div class="info-box-content">
                <span class="info-box-text">Tickets</span>
                <span class="info-box-number"><?php echo $tickets; ?></span>

                <div class="progress">
                    <div class="progress-bar" style="width: <?php echo $tickets_perc_30_days; ?>%"></div>
                </div>
                <span class="progress-description">
                    <?php echo $tickets_perc_30_days; ?>% Increase in 30 Days
                </span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <!-- /.col -->
    <div class="col-md-6 col-sm-6 col-xs-12">
        <div class="info-box bg-yellow">
            <span class="info-box-icon"><i class="fas fa-stopwatch"></i></span>

            <div class="info-box-content">
                <span class="info-box-text">Worked hours</span>
                <span class="info-box-number"><?php echo number_format($hours, 2, '.', '.'); ?></span>

                <div class="progress">
                    <div class="progress-bar" style="width: <?php echo $hours_perc_30_days; ?>%"></div>
                </div>
                <span class="progress-description">
                    <?php echo $hours_perc_30_days; ?>% Increase in 30 Days
                </span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <!-- /.col -->
    <div class="col-md-6 col-sm-6 col-xs-12">
        <div class="info-box bg-red">
            <span class="info-box-icon"><i class="fas fa-search-dollar"></i></span>

            <div class="info-box-content">
                <span class="info-box-text">Expenses</span>
                <span class="info-box-number">&euro; <?php echo number_format($expenses, 2, '.', ','); ?></span>


            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <!-- /.col -->
</div>