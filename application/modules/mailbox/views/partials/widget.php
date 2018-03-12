


<div class="row inbox">
    <div class="col-md-2">
        <ul class="inbox-nav margin-bottom-10">
            <?php if (MAILBOX_COMPOSE): ?>
                <li class="compose-btn">
                    <a href="javascript:;" data-title="Compose" class="btn green"> 
                        <i class="fa fa-edit"></i> Compose
                    </a>
                </li>
            <?php endif; ?>
            <li class="inbox active">
                <a href="javascript:;" class="btn" data-title="Inbox">Inbox</a>
                <b></b>
            </li>
            <li class="sent"><a class="btn" href="javascript:;"  data-title="Sent">Sent</a><b></b></li>
            <?php /*
            <li class="draft"><a class="btn" href="javascript:;" data-title="Draft">Draft</a><b></b></li>
            <li class="trash"><a class="btn" href="javascript:;" data-title="Trash">Trash</a><b></b></li>
             */ ?>
            
            <?php $filters = array_keys(unserialize(MAILBOX_FLAG_FILTERS)); ?>
            <?php if ($filters): ?>
                <li class="md-shadow-z-1 filters-box">
                    <p class="title">Filtri</p>
                    <ul class="filters-list text-left">
                        <?php foreach($filters as $label): ?>
                            <li class="mailbox-filter ">
                                <label class="checkbox">
                                    <input type="checkbox" name="filters[]" value="<?php echo $label; ?>" />
                                    <?php echo $label; ?>
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            <?php endif; ?>
        </ul>
    </div>
    <div class="col-md-10">
        <div class="inbox-header">
            <h1 class="pull-left">Inbox</h1>
            <form class="js-search-mail form-inline pull-right" action="index.html">
                <div class="input-group input-medium">
                    <input type="text" class="form-control" name="q" placeholder="Cerca e-mail">
                    <span class="input-group-btn">                   
                        <button type="submit" class="btn green"><i class="fa fa-search"></i></button>
                    </span>
                </div>
            </form>
        </div>
        <div class="inbox-loading">Loading...</div>
        <div class="inbox-content" style="background-color:#fff"></div>
    </div>
</div>
