<style>
.container-border {
    border: 2px solid #c7ccd0;
    border-radius: 6px;
    background-color: white;
}

.warning_sign_container {
    display: flex;
    justify-content: center;
}
</style>


<div class="container">
    <div class="row row-margins container-border">
        <div class="col-md-12">
            <div class="warning_sign_container">
                <img src="<?php echo base_url("images/warning_sign.png"); ?>" />
            </div>
        </div>
        <div class="col-md-12 text-center">
            <h3><?php e('Access Denied'); ?></h3>
            <p class="lead"><?php e('The page you requested does not exist'); ?> <br /> <?php e('or you don\'t have the right permissions.'); ?></p>
        </div>
    </div>
</div>