<style>
.container-border {
    border: 2px solid #ccc;
    border-radius: 20px;
    background-color: white;
}

.row-margins {
    margin-top: 50px;
    margin-left: 200px;
    margin-right: 200px;
}

@media screen and (max-width: 992px) {
    .row-margins {
        margin-top: 50px;
        margin-left: 50px;
        margin-right: 50px;
    }
}
</style>


<div class="container margin-x-lg">
    <div class="row row-margins container-border">
        <div class="col-md-12 text-center">
            <img src="<?php echo base_url("images/warning_sign.png"); ?>" />
        </div>
        <div class="col-md-12 text-center">
            <h3>
                <?php e('Access Denied');?>
            </h3>
            <p class="lead"><?php e('The page you requested does not exist'); ?> <br /><?php e('or you don\'t have the right permissions.'); ?></p>
        </div>
    </div>
</div>