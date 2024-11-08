<style>
.changelog-module-list {
    max-height: 500px;
    overflow-y: auto;
}

.changelog-module-item {
    margin-bottom: 10px;
    margin-left: 40px;
    margin-right: 10px;
    position: relative;
}

.changelog-module-item .changelog-module-inner {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 3px;
    padding: 10px;
    position: relative;
}

.changelog-module-item .changelog-module-inner:before {
    border-right: 10px solid #ddd;
    border-style: solid;
    border-width: 10px;
    color: rgba(0, 0, 0, 0);
    content: "";
    display: block;
    height: 0;
    position: absolute;
    left: -20px;
    top: 6px;
    width: 0;
}

.changelog-module-item .changelog-module-inner:after {
    border-right: 10px solid #fff;
    border-style: solid;
    border-width: 10px;
    color: rgba(0, 0, 0, 0);
    content: "";
    display: block;
    height: 0;
    position: absolute;
    left: -18px;
    top: 6px;
    width: 0;
}

.changelog-module-item:before {
    background: #fff;
    border-radius: 2px;
    bottom: -30px;
    box-shadow: 0 0 3px rgba(0, 0, 0, 0.2);
    content: "";
    height: 100%;
    left: -30px;
    position: absolute;
    width: 3px;
}

.changelog-module-item:after {
    background: #fff;
    border: 2px solid #ccc;
    border-radius: 50%;
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
    content: "";
    height: 15px;
    left: -36px;
    position: absolute;
    top: 10px;
    width: 15px;
}

.changelog-clearfix:before, .changelog-clearfix:after {
    content: " ";
    display: table;
}

.changelog-module-item .changelog-module-head {
    border-bottom: 1px solid #eee;
    margin-bottom: 8px;
    padding-bottom: 8px;
}

.changelog-module-item .changelog-module-head .changelog-avatar {
    margin-right: 20px;
}

.changelog-module-item .changelog-module-head .changelog-user-detail {
    overflow: hidden;
}

.changelog-module-item .changelog-module-head .changelog-user-detail h5 {
    font-size: 16px;
    font-weight: bold;
    margin: 0;
}

.changelog-module-item .changelog-module-head .changelog-post-meta {
    float: left;
    padding: 0 15px 0 0;
}

.changelog-module-item .changelog-module-head .changelog-post-meta > div {
    color: #333;
    font-weight: bold;
    text-align: right;
}

.changelog-post-meta > div {
    color: #777;
    font-size: 12px;
    line-height: 22px;
}

.changelog-module-item .changelog-module-head .changelog-post-meta > div {
    color: #333;
    font-weight: bold;
    text-align: right;
}

.changelog-post-meta > div {
    color: #777;
    font-size: 12px;
    line-height: 22px;
}

.changelog-avatar img {
    min-height: 40px;
    max-height: 40px;
}
</style>

<div class="row">
    <div class="col-md-3">
        <img src="<?php echo !empty($module['modules_repository_thumbnail']) ? (OPENBUILDER_BUILDER_BASEURL . 'uploads/modules_repository/' . $module['modules_repository_thumbnail']) : 'https://crm.h2web.it/module_bridge/module-manager/falcon.png'; ?>" alt="<?php echo $module['modules_repository_name']; ?>" class="img-responsive">
    </div>
    <div class="col-md-9">
        <h2 style="margin-top: 0 !important;"><?php echo $module['modules_repository_name']; ?></h2>
        <p><?php echo $module['modules_repository_small_description']; ?></p>
        <div class="row">
            <div class="col-md-6">
                <p><strong>Latest version:</strong> <?php echo $module['modules_repository_version'] ?: '-'; ?></p>
                <p><strong>Status:</strong> <?php echo $module['modules_repository_status'] ?: '-'; ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Price:</strong> &euro; <?php echo $module['modules_repository_price'] ?: '-'; ?></p>
                <p><strong>Created By:</strong> <?php echo $module['modules_repository_developer'] ?: '-'; ?></p>
            </div>
        </div>
    </div>
</div>
<hr>
<div class="panel panel-default">
    <div class="panel-heading">
        <h4 class="panel-title">Description</h4>
    </div>
    <div class="panel-body">
        <?php echo $module['modules_repository_html_description'] ?: '<i>No description found</i>'; ?>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <h4 class="panel-title">
            <a data-toggle="collapse" href="#collapseChangelog">Changelog</a>
        </h4>
    </div>
    <div id="collapseChangelog" class="panel-collapse collapse">
        <div class="panel-body">
            <?php if(!empty($module['releases'])): $releases = $module['releases'] ?>
                <div class="changelog-module-list">
                    <?php foreach($releases as $release): ?>
                        <div class="changelog-module-item" id="<?php echo $release['modules_repository_releases_module'] ?>">
                            <div class="changelog-module-inner">
                                <div class="changelog-module-head changelog-clearfix">
                                    <div class="changelog-user-detail">
                                        <h5 class="changelog-handle">
                                            <?php echo "v{$release['modules_repository_releases_version']}"; ?>
                                            <small>
                                                (<?php echo $release['modules_repository_releases_version_code'] ?>)
                                                - Released from: <?php echo '<b>', $release['projects_title'] ?? '-PROGETTO NON TROVATO-', '</b>' ?>
                                            </small>
                                        </h5>
                                    </div>
                                </div>
                                <div class="changelog-module-content">
                                    <?php echo nl2br($release['modules_repository_releases_changelog']); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No release history found</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <h4 class="panel-title">
            <a data-toggle="collapse" href="#collapseScreenshots">Screenshots</a>
        </h4>
    </div>
    <div id="collapseScreenshots" class="panel-collapse collapse">
        <div class="panel-body">
            <?php if(!empty($module['modules_repository_screenshots'])): ?>
            <div id="screenshots" class="carousel slide" data-ride="carousel">
                <div class="carousel-inner">
                    <?php
                    $screenshots = json_decode($module['modules_repository_screenshots'], true);
                    foreach($screenshots as $index => $screenshot):
                        ?>
                        <div class="item <?php echo $index === 0 ? 'active' : ''; ?>">
                            <img src="<?php echo OPENBUILDER_ADMIN_BASEURL . 'uploads/' . $screenshot['path_local']; ?>" alt="Screenshot <?php echo $index + 1; ?>" class="img-responsive">
                        </div>
                    <?php endforeach; ?>
                </div>
                <a class="left carousel-control" href="#screenshots" data-slide="prev">
                    <span class="glyphicon glyphicon-chevron-left"></span>
                </a>
                <a class="right carousel-control" href="#screenshots" data-slide="next">
                    <span class="glyphicon glyphicon-chevron-right"></span>
                </a>
            </div>
            <?php else: ?>
                <div class="alert alert-info">No screenshots found</div>
            <?php endif; ?>
        </div>
    </div>
</div>
