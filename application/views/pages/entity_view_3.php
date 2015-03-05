<div class="row">
    <div class="col-md-12">
        <!-- BEGIN PAGE TITLE & BREADCRUMB-->
        <h3 class="page-title">
            <?php echo $dati['entity']['entity_name']; ?><small>managed table</small>
        </h3>
        <ul class="page-breadcrumb breadcrumb">
            <li class="btn-group">
                <button type="button" class="btn blue dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true">
                    <span>Actions</span> <i class="icon-angle-down"></i>
                </button>
                <ul class="dropdown-menu pull-right" role="menu">
                    <li><a href="#">Action</a></li>
                    <li><a href="#">Another action</a></li>
                    <li><a href="#">Something else here</a></li>
                    <li class="divider"></li>
                    <li><a href="#">Separated link</a></li>
                </ul>
            </li>
            <li>
                <i class="icon-home"></i>
                <a href="index.html">Home</a> 
                <i class="icon-angle-right"></i>
            </li>
            <li>
                <a href="#">Data Tables</a>
                <i class="icon-angle-right"></i>
            </li>
            <li><a href="#">Managed Tables</a></li>
        </ul>
        <!-- END PAGE TITLE & BREADCRUMB-->
    </div>
</div>





<!-- BEGIN PAGE CONTENT-->
<div class="row">
    <div class="col-md-12">
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="portlet box light-grey">
            <div class="portlet-title">
                <div class="caption"><i class="icon-globe"></i>Managed Table</div>
                <div class="tools">
                    <a href="javascript:;" class="collapse"></a>
                    <a href="#portlet-config" data-toggle="modal" class="config"></a>
                    <a href="javascript:;" class="reload"></a>
                    <a href="javascript:;" class="remove"></a>
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-toolbar">
                    <div class="btn-group">
                        <button id="sample_editable_1_new" class="btn green">
                            Add New <i class="icon-plus"></i>
                        </button>
                    </div>
                    <div class="btn-group pull-right">
                        <button class="btn dropdown-toggle" data-toggle="dropdown">Tools <i class="icon-angle-down"></i>
                        </button>
                        <ul class="dropdown-menu pull-right">
                            <li><a href="#">Print</a></li>
                            <li><a href="#">Save as PDF</a></li>
                            <li><a href="#">Export to Excel</a></li>
                        </ul>
                    </div>
                </div>

                <?php $default_grid_id = $this->datab->get_default_grid($dati['entity_id']); ?>
                <?php $grid = $this->datab->get_grid($default_grid_id); ?>
                <?php $grid_data = $this->datab->get_data_entity($grid['grids']['grids_entity_id']); ?>

                <table class="table table-striped table-bordered table-hover" id="sample_1">
                    <thead>
                        <tr>
                            <?php foreach ($grid['grids_fields'] as $field): ?>
                                <th><?php echo $field['fields_name']; ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(isset($grid_data['data'])): ?>
                            <?php foreach ($grid_data['data'] as $dato): ?>
                                <tr class="odd gradeX">
                                    <?php foreach ($grid['grids_fields'] as $field): ?>
                                        <?php // Se il campo è un joinato ciclo i campi PREVIEW per quella tabella ?>
                                            <?php if ($field['fields_ref']): ?>
                                               <td><?php foreach ($grid['grids_support_fields'] as $support_field): ?>
                                                        <?php echo $dato[$support_field['fields_name']]; ?>
                                                    <?php endforeach; ?></td>
                                            <?php else: ?>
                                                <td><?php echo $dato[$field['fields_name']]; ?></td>
                                            <?php endif; ?>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- END EXAMPLE TABLE PORTLET-->
    </div>
</div>





<?php $this->load->view('form/box_col_12'); ?>