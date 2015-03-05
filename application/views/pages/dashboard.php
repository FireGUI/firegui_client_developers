
                <!-- BEGIN SAMPLE PORTLET CONFIGURATION MODAL FORM-->               
                <div class="modal fade" id="portlet-config" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                                <h4 class="modal-title">Modal title</h4>
                            </div>
                            <div class="modal-body">
                                Widget settings form goes here
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn blue">Save changes</button>
                                <button type="button" class="btn default" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                        <!-- /.modal-content -->
                    </div>
                    <!-- /.modal-dialog -->
                </div>
                <!-- /.modal -->
                <!-- END SAMPLE PORTLET CONFIGURATION MODAL FORM-->
                <!-- BEGIN STYLE CUSTOMIZER -->
                <div class="theme-panel hidden-xs hidden-sm">
                    <div class="toggler"></div>
                    <div class="toggler-close"></div>
                    <div class="theme-options">
                        <div class="theme-option theme-colors clearfix">
                            <span>THEME COLOR</span>
                            <ul>
                                <li class="color-black current color-default" data-style="default"></li>
                                <li class="color-blue" data-style="blue"></li>
                                <li class="color-brown" data-style="brown"></li>
                                <li class="color-purple" data-style="purple"></li>
                                <li class="color-grey" data-style="grey"></li>
                                <li class="color-white color-light" data-style="light"></li>
                            </ul>
                        </div>
                        <div class="theme-option">
                            <span>Layout</span>
                            <select class="layout-option form-control input-small">
                                <option value="fluid" selected="selected">Fluid</option>
                                <option value="boxed">Boxed</option>
                            </select>
                        </div>
                        <div class="theme-option">
                            <span>Header</span>
                            <select class="header-option form-control input-small">
                                <option value="fixed" selected="selected">Fixed</option>
                                <option value="default">Default</option>
                            </select>
                        </div>
                        <div class="theme-option">
                            <span>Sidebar</span>
                            <select class="sidebar-option form-control input-small">
                                <option value="fixed">Fixed</option>
                                <option value="default" selected="selected">Default</option>
                            </select>
                        </div>
                        <div class="theme-option">
                            <span>Footer</span>
                            <select class="footer-option form-control input-small">
                                <option value="fixed">Fixed</option>
                                <option value="default" selected="selected">Default</option>
                            </select>
                        </div>
                    </div>
                </div>
                <!-- END BEGIN STYLE CUSTOMIZER -->  
                <!-- BEGIN PAGE HEADER-->
                <div class="row">
                    <div class="col-md-12">
                        <!-- BEGIN PAGE TITLE & BREADCRUMB-->
                        <h3 class="page-title">
                            Dashboard <small>statistics and more</small>
                        </h3>
                        <ul class="page-breadcrumb breadcrumb">
                            <li>
                                <i class="icon-home"></i>
                                <a href="index.html">Home</a> 
                                <i class="icon-angle-right"></i>
                            </li>
                            <li><a href="#">Dashboard</a></li>
                            <li class="pull-right">
                                <div id="dashboard-report-range" class="dashboard-date-range tooltips" data-placement="top" data-original-title="Change dashboard date range">
                                    <i class="icon-calendar"></i>
                                    <span></span>
                                    <i class="icon-angle-down"></i>
                                </div>
                            </li>
                        </ul>
                        <!-- END PAGE TITLE & BREADCRUMB-->
                    </div>
                </div>
                <!-- END PAGE HEADER-->
                <!-- BEGIN DASHBOARD STATS -->
                <div class="row">
                    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                        <div class="dashboard-stat blue">
                            <div class="visual">
                                <i class="icon-comments"></i>
                            </div>
                            <div class="details">
                                <div class="number">
                                    1349
                                </div>
                                <div class="desc">                           
                                    New Feedbacks
                                </div>
                            </div>
                            <a class="more" href="#">
                                View more <i class="m-icon-swapright m-icon-white"></i>
                            </a>                 
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                        <div class="dashboard-stat green">
                            <div class="visual">
                                <i class="icon-shopping-cart"></i>
                            </div>
                            <div class="details">
                                <div class="number">549</div>
                                <div class="desc">New Orders</div>
                            </div>
                            <a class="more" href="#">
                                View more <i class="m-icon-swapright m-icon-white"></i>
                            </a>                 
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                        <div class="dashboard-stat purple">
                            <div class="visual">
                                <i class="icon-globe"></i>
                            </div>
                            <div class="details">
                                <div class="number">+89%</div>
                                <div class="desc">Brand Popularity</div>
                            </div>
                            <a class="more" href="#">
                                View more <i class="m-icon-swapright m-icon-white"></i>
                            </a>                 
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                        <div class="dashboard-stat yellow">
                            <div class="visual">
                                <i class="icon-bar-chart"></i>
                            </div>
                            <div class="details">
                                <div class="number">12,5M$</div>
                                <div class="desc">Total Profit</div>
                            </div>
                            <a class="more" href="#">
                                View more <i class="m-icon-swapright m-icon-white"></i>
                            </a>                 
                        </div>
                    </div>
                </div>
                <!-- END DASHBOARD STATS -->
                <div class="clearfix"></div>
                <div class="row">
                    <div class="col-md-6 col-sm-6">
                        <!-- BEGIN PORTLET-->
                        <div class="portlet solid bordered light-grey">
                            <div class="portlet-title">
                                <div class="caption"><i class="icon-bar-chart"></i>Site Visits</div>
                                <div class="tools">
                                    <div class="btn-group" data-toggle="buttons">
                                        <label class="btn default btn-sm active">
                                            <input type="radio" name="options" class="toggle" id="option1">Users
                                        </label>
                                        <label class="btn default btn-sm">
                                            <input type="radio" name="options" class="toggle" id="option2">Feedbacks
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="portlet-body">
                                <div id="site_statistics_loading">
                                    <img src="assets/img/loading.gif" alt="loading"/>
                                </div>
                                <div id="site_statistics_content" class="display-none">
                                    <div id="site_statistics" class="chart"></div>
                                </div>
                            </div>
                        </div>
                        <!-- END PORTLET-->
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <!-- BEGIN PORTLET-->
                        <div class="portlet solid light-grey bordered">
                            <div class="portlet-title">
                                <div class="caption"><i class="icon-bullhorn"></i>Activities</div>
                                <div class="tools">
                                    <div class="btn-group pull-right" data-toggle="buttons">
                                        <a href="" class="btn blue btn-sm active">Users</a>
                                        <a href="" class="btn blue btn-sm">Orders</a>
                                    </div>
                                </div>
                            </div>
                            <div class="portlet-body">
                                <div id="site_activities_loading">
                                    <img src="assets/img/loading.gif" alt="loading"/>
                                </div>
                                <div id="site_activities_content" class="display-none">
                                    <div id="site_activities" style="height: 100px;"></div>
                                </div>
                            </div>
                        </div>
                        <!-- END PORTLET-->
                        <!-- BEGIN PORTLET-->
                        <div class="portlet solid bordered light-grey">
                            <div class="portlet-title">
                                <div class="caption"><i class="icon-signal"></i>Server Load</div>
                                <div class="tools">
                                    <div class="btn-group pull-right" data-toggle="buttons">
                                        <a href="" class="btn red btn-sm active">Database</a>
                                        <a href="" class="btn red btn-sm">Web</a>
                                    </div>
                                </div>
                            </div>
                            <div class="portlet-body">
                                <div id="load_statistics_loading">
                                    <img src="assets/img/loading.gif" alt="loading" />
                                </div>
                                <div id="load_statistics_content" class="display-none">
                                    <div id="load_statistics" style="height: 108px;"></div>
                                </div>
                            </div>
                        </div>
                        <!-- END PORTLET-->
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="row ">
                    <div class="col-md-6 col-sm-6">
                        <div class="portlet box blue">
                            <div class="portlet-title">
                                <div class="caption"><i class="icon-bell"></i>Recent Activities</div>
                                <div class="actions">
                                    <div class="btn-group">
                                        <a class="btn btn-sm default" href="#" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                                            Filter By
                                            <i class="icon-angle-down"></i>
                                        </a>
                                        <div class="dropdown-menu hold-on-click dropdown-checkboxes pull-right">
                                            <label><input type="checkbox" /> Finance</label>
                                            <label><input type="checkbox" checked="" /> Membership</label>
                                            <label><input type="checkbox" /> Customer Support</label>
                                            <label><input type="checkbox" checked="" /> HR</label>
                                            <label><input type="checkbox" /> System</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="portlet-body">
                                <div class="scroller" style="height: 300px;" data-always-visible="1" data-rail-visible="0">
                                    <ul class="feeds">
                                        <li>
                                            <div class="col1">
                                                <div class="cont">
                                                    <div class="cont-col1">
                                                        <div class="label label-sm label-info">                        
                                                            <i class="icon-check"></i>
                                                        </div>
                                                    </div>
                                                    <div class="cont-col2">
                                                        <div class="desc">
                                                            You have 4 pending tasks.
                                                            <span class="label label-sm label-warning ">
                                                                Take action 
                                                                <i class="icon-share-alt"></i>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col2">
                                                <div class="date">
                                                    Just now
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <div class="col1">
                                                    <div class="cont">
                                                        <div class="cont-col1">
                                                            <div class="label label-sm label-success">                        
                                                                <i class="icon-bar-chart"></i>
                                                            </div>
                                                        </div>
                                                        <div class="cont-col2">
                                                            <div class="desc">
                                                                Finance Report for year 2013 has been released.   
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col2">
                                                    <div class="date">
                                                        20 mins
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        <li>
                                            <div class="col1">
                                                <div class="cont">
                                                    <div class="cont-col1">
                                                        <div class="label label-sm label-danger">                      
                                                            <i class="icon-user"></i>
                                                        </div>
                                                    </div>
                                                    <div class="cont-col2">
                                                        <div class="desc">
                                                            You have 5 pending membership that requires a quick review.                       
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col2">
                                                <div class="date">
                                                    24 mins
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="col1">
                                                <div class="cont">
                                                    <div class="cont-col1">
                                                        <div class="label label-sm label-info">                        
                                                            <i class="icon-shopping-cart"></i>
                                                        </div>
                                                    </div>
                                                    <div class="cont-col2">
                                                        <div class="desc">
                                                            New order received with <span class="label label-sm label-success">Reference Number: DR23923</span>             
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col2">
                                                <div class="date">
                                                    30 mins
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="col1">
                                                <div class="cont">
                                                    <div class="cont-col1">
                                                        <div class="label label-sm label-success">                      
                                                            <i class="icon-user"></i>
                                                        </div>
                                                    </div>
                                                    <div class="cont-col2">
                                                        <div class="desc">
                                                            You have 5 pending membership that requires a quick review.                       
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col2">
                                                <div class="date">
                                                    24 mins
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="col1">
                                                <div class="cont">
                                                    <div class="cont-col1">
                                                        <div class="label label-sm label-default">                        
                                                            <i class="icon-bell-alt"></i>
                                                        </div>
                                                    </div>
                                                    <div class="cont-col2">
                                                        <div class="desc">
                                                            Web server hardware needs to be upgraded. 
                                                            <span class="label label-sm label-default ">Overdue</span>             
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col2">
                                                <div class="date">
                                                    2 hours
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <div class="col1">
                                                    <div class="cont">
                                                        <div class="cont-col1">
                                                            <div class="label label-sm label-default">                        
                                                                <i class="icon-briefcase"></i>
                                                            </div>
                                                        </div>
                                                        <div class="cont-col2">
                                                            <div class="desc">
                                                                IPO Report for year 2013 has been released.   
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col2">
                                                    <div class="date">
                                                        20 mins
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        <li>
                                            <div class="col1">
                                                <div class="cont">
                                                    <div class="cont-col1">
                                                        <div class="label label-sm label-info">                        
                                                            <i class="icon-check"></i>
                                                        </div>
                                                    </div>
                                                    <div class="cont-col2">
                                                        <div class="desc">
                                                            You have 4 pending tasks.
                                                            <span class="label label-sm label-warning ">
                                                                Take action 
                                                                <i class="icon-share-alt"></i>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col2">
                                                <div class="date">
                                                    Just now
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <div class="col1">
                                                    <div class="cont">
                                                        <div class="cont-col1">
                                                            <div class="label label-sm label-danger">                        
                                                                <i class="icon-bar-chart"></i>
                                                            </div>
                                                        </div>
                                                        <div class="cont-col2">
                                                            <div class="desc">
                                                                Finance Report for year 2013 has been released.   
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col2">
                                                    <div class="date">
                                                        20 mins
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        <li>
                                            <div class="col1">
                                                <div class="cont">
                                                    <div class="cont-col1">
                                                        <div class="label label-sm label-default">                      
                                                            <i class="icon-user"></i>
                                                        </div>
                                                    </div>
                                                    <div class="cont-col2">
                                                        <div class="desc">
                                                            You have 5 pending membership that requires a quick review.                       
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col2">
                                                <div class="date">
                                                    24 mins
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="col1">
                                                <div class="cont">
                                                    <div class="cont-col1">
                                                        <div class="label label-sm label-info">                        
                                                            <i class="icon-shopping-cart"></i>
                                                        </div>
                                                    </div>
                                                    <div class="cont-col2">
                                                        <div class="desc">
                                                            New order received with <span class="label label-sm label-success">Reference Number: DR23923</span>             
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col2">
                                                <div class="date">
                                                    30 mins
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="col1">
                                                <div class="cont">
                                                    <div class="cont-col1">
                                                        <div class="label label-sm label-success">                      
                                                            <i class="icon-user"></i>
                                                        </div>
                                                    </div>
                                                    <div class="cont-col2">
                                                        <div class="desc">
                                                            You have 5 pending membership that requires a quick review.                       
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col2">
                                                <div class="date">
                                                    24 mins
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="col1">
                                                <div class="cont">
                                                    <div class="cont-col1">
                                                        <div class="label label-sm label-warning">                        
                                                            <i class="icon-bell-alt"></i>
                                                        </div>
                                                    </div>
                                                    <div class="cont-col2">
                                                        <div class="desc">
                                                            Web server hardware needs to be upgraded. 
                                                            <span class="label label-sm label-default ">Overdue</span>             
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col2">
                                                <div class="date">
                                                    2 hours
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <div class="col1">
                                                    <div class="cont">
                                                        <div class="cont-col1">
                                                            <div class="label label-sm label-info">                        
                                                                <i class="icon-briefcase"></i>
                                                            </div>
                                                        </div>
                                                        <div class="cont-col2">
                                                            <div class="desc">
                                                                IPO Report for year 2013 has been released.   
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col2">
                                                    <div class="date">
                                                        20 mins
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="scroller-footer">
                                    <div class="pull-right">
                                        <a href="#">See All Records <i class="m-icon-swapright m-icon-gray"></i></a> &nbsp;
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <div class="portlet box green tasks-widget">
                            <div class="portlet-title">
                                <div class="caption"><i class="icon-check"></i>Tasks</div>
                                <div class="tools">
                                    <a href="#portlet-config" data-toggle="modal" class="config"></a>
                                    <a href="" class="reload"></a>
                                </div>
                                <div class="actions">
                                    <div class="btn-group">
                                        <a class="btn default btn-xs" href="#" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                                            More
                                            <i class="icon-angle-down"></i>
                                        </a>
                                        <ul class="dropdown-menu pull-right">
                                            <li><a href="#"><i class="i"></i> All Project</a></li>
                                            <li class="divider"></li>
                                            <li><a href="#">AirAsia</a></li>
                                            <li><a href="#">Cruise</a></li>
                                            <li><a href="#">HSBC</a></li>
                                            <li class="divider"></li>
                                            <li><a href="#">Pending <span class="badge badge-important">4</span></a></li>
                                            <li><a href="#">Completed <span class="badge badge-success">12</span></a></li>
                                            <li><a href="#">Overdue <span class="badge badge-warning">9</span></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="portlet-body">
                                <div class="task-content">
                                    <div class="scroller" style="height: 305px;" data-always-visible="1" data-rail-visible1="1">
                                        <!-- START TASK LIST -->
                                        <ul class="task-list">
                                            <li>
                                                <div class="task-checkbox">
                                                    <input type="checkbox" class="liChild" value=""  />                                       
                                                </div>
                                                <div class="task-title">
                                                    <span class="task-title-sp">Present 2013 Year IPO Statistics at Board Meeting</span>
                                                    <span class="label label-sm label-success">Company</span>
                                                    <span class="task-bell"><i class="icon-bell"></i></span>
                                                </div>
                                                <div class="task-config">
                                                    <div class="task-config-btn btn-group">
                                                        <a class="btn btn-xs default" href="#" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                                                            <i class="icon-cog"></i><i class="icon-angle-down"></i></a>
                                                        <ul class="dropdown-menu pull-right">
                                                            <li><a href="#"><i class="icon-ok"></i> Complete</a></li>
                                                            <li><a href="#"><i class="icon-pencil"></i> Edit</a></li>
                                                            <li><a href="#"><i class="icon-trash"></i> Cancel</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="task-checkbox">
                                                    <input type="checkbox" class="liChild" value=""/>                                       
                                                </div>
                                                <div class="task-title">
                                                    <span class="task-title-sp">Hold An Interview for Marketing Manager Position</span>
                                                    <span class="label label-sm label-danger">Marketing</span>
                                                </div>
                                                <div class="task-config">
                                                    <div class="task-config-btn btn-group">
                                                        <a class="btn btn-xs default" href="#" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                                                            <i class="icon-cog"></i><i class="icon-angle-down"></i></a>
                                                        <ul class="dropdown-menu pull-right">
                                                            <li><a href="#"><i class="icon-ok"></i> Complete</a></li>
                                                            <li><a href="#"><i class="icon-pencil"></i> Edit</a></li>
                                                            <li><a href="#"><i class="icon-trash"></i> Cancel</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="task-checkbox">
                                                    <input type="checkbox" class="liChild" value=""/>                                       
                                                </div>
                                                <div class="task-title">
                                                    <span class="task-title-sp">AirAsia Intranet System Project Internal Meeting</span>
                                                    <span class="label label-sm label-success">AirAsia</span>
                                                    <span class="task-bell"><i class="icon-bell"></i></span>
                                                </div>
                                                <div class="task-config">
                                                    <div class="task-config-btn btn-group">
                                                        <a class="btn btn-xs default" href="#" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                                                            <i class="icon-cog"></i><i class="icon-angle-down"></i></a>
                                                        <ul class="dropdown-menu pull-right">
                                                            <li><a href="#"><i class="icon-ok"></i> Complete</a></li>
                                                            <li><a href="#"><i class="icon-pencil"></i> Edit</a></li>
                                                            <li><a href="#"><i class="icon-trash"></i> Cancel</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="task-checkbox">
                                                    <input type="checkbox" class="liChild" value=""  />                                       
                                                </div>
                                                <div class="task-title">
                                                    <span class="task-title-sp">Technical Management Meeting</span>
                                                    <span class="label label-sm label-warning">Company</span>
                                                </div>
                                                <div class="task-config">
                                                    <div class="task-config-btn btn-group">
                                                        <a class="btn btn-xs default" href="#" data-toggle="dropdown" data-hover="dropdown" data-close-others="true"><i class="icon-cog"></i><i class="icon-angle-down"></i></a>
                                                        <ul class="dropdown-menu pull-right">
                                                            <li><a href="#"><i class="icon-ok"></i> Complete</a></li>
                                                            <li><a href="#"><i class="icon-pencil"></i> Edit</a></li>
                                                            <li><a href="#"><i class="icon-trash"></i> Cancel</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="task-checkbox">
                                                    <input type="checkbox" class="liChild" value=""  />                                       
                                                </div>
                                                <div class="task-title">
                                                    <span class="task-title-sp">Kick-off Company CRM Mobile App Development</span>
                                                    <span class="label label-sm label-info">Internal Products</span>
                                                </div>
                                                <div class="task-config">
                                                    <div class="task-config-btn btn-group">
                                                        <a class="btn btn-xs default" href="#" data-toggle="dropdown" data-hover="dropdown" data-close-others="true"><i class="icon-cog"></i><i class="icon-angle-down"></i></a>
                                                        <ul class="dropdown-menu pull-right">
                                                            <li><a href="#"><i class="icon-ok"></i> Complete</a></li>
                                                            <li><a href="#"><i class="icon-pencil"></i> Edit</a></li>
                                                            <li><a href="#"><i class="icon-trash"></i> Cancel</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="task-checkbox">
                                                    <input type="checkbox" class="liChild" value=""  />                                       
                                                </div>
                                                <div class="task-title">
                                                    <span class="task-title-sp">
                                                        Prepare Commercial Offer For SmartVision Website Rewamp 
                                                    </span>
                                                    <span class="label label-sm label-danger">SmartVision</span>
                                                </div>
                                                <div class="task-config">
                                                    <div class="task-config-btn btn-group">
                                                        <a class="btn btn-xs default" href="#" data-toggle="dropdown" data-hover="dropdown" data-close-others="true"><i class="icon-cog"></i><i class="icon-angle-down"></i></a>
                                                        <ul class="dropdown-menu pull-right">
                                                            <li><a href="#"><i class="icon-ok"></i> Complete</a></li>
                                                            <li><a href="#"><i class="icon-pencil"></i> Edit</a></li>
                                                            <li><a href="#"><i class="icon-trash"></i> Cancel</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="task-checkbox">
                                                    <input type="checkbox" class="liChild" value=""  />                                       
                                                </div>
                                                <div class="task-title">
                                                    <span class="task-title-sp">Sign-Off The Comercial Agreement With AutoSmart</span>
                                                    <span class="label label-sm label-default">AutoSmart</span>
                                                    <span class="task-bell"><i class="icon-bell"></i></span>
                                                </div>
                                                <div class="task-config">
                                                    <div class="task-config-btn btn-group">
                                                        <a class="btn btn-xs default" href="#" data-toggle="dropdown" data-hover="dropdown" data-close-others="true"><i class="icon-cog"></i><i class="icon-angle-down"></i></a>
                                                        <ul class="dropdown-menu pull-right">
                                                            <li><a href="#"><i class="icon-ok"></i> Complete</a></li>
                                                            <li><a href="#"><i class="icon-pencil"></i> Edit</a></li>
                                                            <li><a href="#"><i class="icon-trash"></i> Cancel</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="task-checkbox">
                                                    <input type="checkbox" class="liChild" value=""  />                                       
                                                </div>
                                                <div class="task-title">
                                                    <span class="task-title-sp">Company Staff Meeting</span>
                                                    <span class="label label-sm label-success">Cruise</span>
                                                    <span class="task-bell"><i class="icon-bell"></i></span>
                                                </div>
                                                <div class="task-config">
                                                    <div class="task-config-btn btn-group">
                                                        <a class="btn btn-xs default" href="#" data-toggle="dropdown" data-hover="dropdown" data-close-others="true"><i class="icon-cog"></i><i class="icon-angle-down"></i></a>
                                                        <ul class="dropdown-menu pull-right">
                                                            <li><a href="#"><i class="icon-ok"></i> Complete</a></li>
                                                            <li><a href="#"><i class="icon-pencil"></i> Edit</a></li>
                                                            <li><a href="#"><i class="icon-trash"></i> Cancel</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="last-line">
                                                <div class="task-checkbox">
                                                    <input type="checkbox" class="liChild" value=""  />                                       
                                                </div>
                                                <div class="task-title">
                                                    <span class="task-title-sp">KeenThemes Investment Discussion</span>
                                                    <span class="label label-sm label-warning">KeenThemes</span>
                                                </div>
                                                <div class="task-config">
                                                    <div class="task-config-btn btn-group">
                                                        <a class="btn btn-xs default" href="#" data-toggle="dropdown" data-hover="dropdown" data-close-others="true"><i class="icon-cog"></i><i class="icon-angle-down"></i></a>
                                                        <ul class="dropdown-menu pull-right">
                                                            <li><a href="#"><i class="icon-ok"></i> Complete</a></li>
                                                            <li><a href="#"><i class="icon-pencil"></i> Edit</a></li>
                                                            <li><a href="#"><i class="icon-trash"></i> Cancel</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                        <!-- END START TASK LIST -->
                                    </div>
                                </div>
                                <div class="task-footer">
                                    <span class="pull-right">
                                        <a href="#">See All Tasks <i class="m-icon-swapright m-icon-gray"></i></a> &nbsp;
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="row ">
                    <div class="col-md-6 col-sm-6">
                        <div class="portlet box purple">
                            <div class="portlet-title">
                                <div class="caption"><i class="icon-calendar"></i>General Stats</div>
                                <div class="actions">
                                    <a href="javascript:;" class="btn btn-sm yellow easy-pie-chart-reload"><i class="icon-repeat"></i> Reload</a>
                                </div>
                            </div>
                            <div class="portlet-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="easy-pie-chart">
                                            <div class="number transactions" data-percent="55"><span>+55</span>%</div>
                                            <a class="title" href="#">Transactions <i class="m-icon-swapright"></i></a>
                                        </div>
                                    </div>
                                    <div class="margin-bottom-10 visible-sm"></div>
                                    <div class="col-md-4">
                                        <div class="easy-pie-chart">
                                            <div class="number visits" data-percent="85"><span>+85</span>%</div>
                                            <a class="title" href="#">New Visits <i class="m-icon-swapright"></i></a>
                                        </div>
                                    </div>
                                    <div class="margin-bottom-10 visible-sm"></div>
                                    <div class="col-md-4">
                                        <div class="easy-pie-chart">
                                            <div class="number bounce" data-percent="46"><span>-46</span>%</div>
                                            <a class="title" href="#">Bounce <i class="m-icon-swapright"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <div class="portlet box blue">
                            <div class="portlet-title">
                                <div class="caption"><i class="icon-calendar"></i>Server Stats</div>
                                <div class="tools">
                                    <a href="" class="collapse"></a>
                                    <a href="#portlet-config" data-toggle="modal" class="config"></a>
                                    <a href="" class="reload"></a>
                                    <a href="" class="remove"></a>
                                </div>
                            </div>
                            <div class="portlet-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="sparkline-chart">
                                            <div class="number" id="sparkline_bar"></div>
                                            <a class="title" href="#">Network <i class="m-icon-swapright"></i></a>
                                        </div>
                                    </div>
                                    <div class="margin-bottom-10 visible-sm"></div>
                                    <div class="col-md-4">
                                        <div class="sparkline-chart">
                                            <div class="number" id="sparkline_bar2"></div>
                                            <a class="title" href="#">CPU Load <i class="m-icon-swapright"></i></a>
                                        </div>
                                    </div>
                                    <div class="margin-bottom-10 visible-sm"></div>
                                    <div class="col-md-4">
                                        <div class="sparkline-chart">
                                            <div class="number" id="sparkline_line"></div>
                                            <a class="title" href="#">Load Rate <i class="m-icon-swapright"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="row ">
                    <div class="col-md-6 col-sm-6">
                        <!-- BEGIN REGIONAL STATS PORTLET-->
                        <div class="portlet">
                            <div class="portlet-title">
                                <div class="caption"><i class="icon-globe"></i>Regional Stats</div>
                                <div class="tools">
                                    <a href="" class="collapse"></a>
                                    <a href="#portlet-config" data-toggle="modal" class="config"></a>
                                    <a href="" class="reload"></a>
                                    <a href="" class="remove"></a>
                                </div>
                            </div>
                            <div class="portlet-body">
                                <div id="region_statistics_loading">
                                    <img src="assets/img/loading.gif" alt="loading"/>
                                </div>
                                <div id="region_statistics_content" class="display-none">
                                    <div class="btn-toolbar margin-bottom-10">
                                        <div class="btn-group" data-toggle="buttons">
                                            <a href="" class="btn default btn-sm active">Users</a>
                                            <a href="" class="btn default btn-sm">Orders</a> 
                                        </div>
                                        <div class="btn-group pull-right">
                                            <a href="" class="btn default btn-sm dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                                                Select Region <span class="icon-angle-down"></span>
                                            </a>
                                            <ul class="dropdown-menu pull-right">
                                                <li><a href="javascript:;" id="regional_stat_world">World</a></li>
                                                <li><a href="javascript:;" id="regional_stat_usa">USA</a></li>
                                                <li><a href="javascript:;" id="regional_stat_europe">Europe</a></li>
                                                <li><a href="javascript:;" id="regional_stat_russia">Russia</a></li>
                                                <li><a href="javascript:;" id="regional_stat_germany">Germany</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div id="vmap_world" class="vmaps display-none"></div>
                                    <div id="vmap_usa" class="vmaps display-none"></div>
                                    <div id="vmap_europe" class="vmaps display-none"></div>
                                    <div id="vmap_russia" class="vmaps display-none"></div>
                                    <div id="vmap_germany" class="vmaps display-none"></div>
                                </div>
                            </div>
                        </div>
                        <!-- END REGIONAL STATS PORTLET-->
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <!-- BEGIN PORTLET-->
                        <div class="portlet paddingless">
                            <div class="portlet-title line">
                                <div class="caption"><i class="icon-bell"></i>Feeds</div>
                                <div class="tools">
                                    <a href="" class="collapse"></a>
                                    <a href="#portlet-config" data-toggle="modal" class="config"></a>
                                    <a href="" class="reload"></a>
                                    <a href="" class="remove"></a>
                                </div>
                            </div>
                            <div class="portlet-body">
                                <!--BEGIN TABS-->
                                <div class="tabbable tabbable-custom">
                                    <ul class="nav nav-tabs">
                                        <li class="active"><a href="#tab_1_1" data-toggle="tab">System</a></li>
                                        <li><a href="#tab_1_2" data-toggle="tab">Activities</a></li>
                                        <li><a href="#tab_1_3" data-toggle="tab">Recent Users</a></li>
                                    </ul>
                                    <div class="tab-content">
                                        <div class="tab-pane active" id="tab_1_1">
                                            <div class="scroller" style="height: 290px;" data-always-visible="1" data-rail-visible="0">
                                                <ul class="feeds">
                                                    <li>
                                                        <div class="col1">
                                                            <div class="cont">
                                                                <div class="cont-col1">
                                                                    <div class="label label-sm label-success">                        
                                                                        <i class="icon-bell"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="cont-col2">
                                                                    <div class="desc">
                                                                        You have 4 pending tasks.
                                                                        <span class="label label-sm label-danger ">
                                                                            Take action 
                                                                            <i class="icon-share-alt"></i>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col2">
                                                            <div class="date">
                                                                Just now
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <a href="#">
                                                            <div class="col1">
                                                                <div class="cont">
                                                                    <div class="cont-col1">
                                                                        <div class="label label-sm label-success">                        
                                                                            <i class="icon-bell"></i>
                                                                        </div>
                                                                    </div>
                                                                    <div class="cont-col2">
                                                                        <div class="desc">
                                                                            New version v1.4 just lunched!   
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col2">
                                                                <div class="date">
                                                                    20 mins
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <div class="col1">
                                                            <div class="cont">
                                                                <div class="cont-col1">
                                                                    <div class="label label-sm label-danger">                      
                                                                        <i class="icon-bolt"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="cont-col2">
                                                                    <div class="desc">
                                                                        Database server #12 overloaded. Please fix the issue.                      
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col2">
                                                            <div class="date">
                                                                24 mins
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="col1">
                                                            <div class="cont">
                                                                <div class="cont-col1">
                                                                    <div class="label label-sm label-info">                        
                                                                        <i class="icon-bullhorn"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="cont-col2">
                                                                    <div class="desc">
                                                                        New order received. Please take care of it.                 
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col2">
                                                            <div class="date">
                                                                30 mins
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="col1">
                                                            <div class="cont">
                                                                <div class="cont-col1">
                                                                    <div class="label label-sm label-success">                        
                                                                        <i class="icon-bullhorn"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="cont-col2">
                                                                    <div class="desc">
                                                                        New order received. Please take care of it.                 
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col2">
                                                            <div class="date">
                                                                40 mins
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="col1">
                                                            <div class="cont">
                                                                <div class="cont-col1">
                                                                    <div class="label label-sm label-warning">                        
                                                                        <i class="icon-plus"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="cont-col2">
                                                                    <div class="desc">
                                                                        New user registered.                
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col2">
                                                            <div class="date">
                                                                1.5 hours
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="col1">
                                                            <div class="cont">
                                                                <div class="cont-col1">
                                                                    <div class="label label-sm label-success">                        
                                                                        <i class="icon-bell-alt"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="cont-col2">
                                                                    <div class="desc">
                                                                        Web server hardware needs to be upgraded. 
                                                                        <span class="label label-sm label-default ">Overdue</span>             
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col2">
                                                            <div class="date">
                                                                2 hours
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="col1">
                                                            <div class="cont">
                                                                <div class="cont-col1">
                                                                    <div class="label label-sm label-default">                       
                                                                        <i class="icon-bullhorn"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="cont-col2">
                                                                    <div class="desc">
                                                                        New order received. Please take care of it.                 
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col2">
                                                            <div class="date">
                                                                3 hours
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="col1">
                                                            <div class="cont">
                                                                <div class="cont-col1">
                                                                    <div class="label label-sm label-warning">                        
                                                                        <i class="icon-bullhorn"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="cont-col2">
                                                                    <div class="desc">
                                                                        New order received. Please take care of it.                 
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col2">
                                                            <div class="date">
                                                                5 hours
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="col1">
                                                            <div class="cont">
                                                                <div class="cont-col1">
                                                                    <div class="label label-sm label-info">                        
                                                                        <i class="icon-bullhorn"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="cont-col2">
                                                                    <div class="desc">
                                                                        New order received. Please take care of it.                 
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col2">
                                                            <div class="date">
                                                                18 hours
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="col1">
                                                            <div class="cont">
                                                                <div class="cont-col1">
                                                                    <div class="label label-sm label-default">                       
                                                                        <i class="icon-bullhorn"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="cont-col2">
                                                                    <div class="desc">
                                                                        New order received. Please take care of it.                 
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col2">
                                                            <div class="date">
                                                                21 hours
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="col1">
                                                            <div class="cont">
                                                                <div class="cont-col1">
                                                                    <div class="label label-sm label-info">                        
                                                                        <i class="icon-bullhorn"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="cont-col2">
                                                                    <div class="desc">
                                                                        New order received. Please take care of it.                 
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col2">
                                                            <div class="date">
                                                                22 hours
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="col1">
                                                            <div class="cont">
                                                                <div class="cont-col1">
                                                                    <div class="label label-sm label-default">                       
                                                                        <i class="icon-bullhorn"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="cont-col2">
                                                                    <div class="desc">
                                                                        New order received. Please take care of it.                 
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col2">
                                                            <div class="date">
                                                                21 hours
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="col1">
                                                            <div class="cont">
                                                                <div class="cont-col1">
                                                                    <div class="label label-sm label-info">                        
                                                                        <i class="icon-bullhorn"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="cont-col2">
                                                                    <div class="desc">
                                                                        New order received. Please take care of it.                 
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col2">
                                                            <div class="date">
                                                                22 hours
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="col1">
                                                            <div class="cont">
                                                                <div class="cont-col1">
                                                                    <div class="label label-sm label-default">                       
                                                                        <i class="icon-bullhorn"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="cont-col2">
                                                                    <div class="desc">
                                                                        New order received. Please take care of it.                 
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col2">
                                                            <div class="date">
                                                                21 hours
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="col1">
                                                            <div class="cont">
                                                                <div class="cont-col1">
                                                                    <div class="label label-sm label-info">                        
                                                                        <i class="icon-bullhorn"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="cont-col2">
                                                                    <div class="desc">
                                                                        New order received. Please take care of it.                 
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col2">
                                                            <div class="date">
                                                                22 hours
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="col1">
                                                            <div class="cont">
                                                                <div class="cont-col1">
                                                                    <div class="label label-sm label-default">                       
                                                                        <i class="icon-bullhorn"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="cont-col2">
                                                                    <div class="desc">
                                                                        New order received. Please take care of it.                 
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col2">
                                                            <div class="date">
                                                                21 hours
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="col1">
                                                            <div class="cont">
                                                                <div class="cont-col1">
                                                                    <div class="label label-sm label-info">                        
                                                                        <i class="icon-bullhorn"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="cont-col2">
                                                                    <div class="desc">
                                                                        New order received. Please take care of it.                 
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col2">
                                                            <div class="date">
                                                                22 hours
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="tab-pane" id="tab_1_2">
                                            <div class="scroller" style="height: 290px;" data-always-visible="1" data-rail-visible1="1">
                                                <ul class="feeds">
                                                    <li>
                                                        <a href="#">
                                                            <div class="col1">
                                                                <div class="cont">
                                                                    <div class="cont-col1">
                                                                        <div class="label label-sm label-success">                        
                                                                            <i class="icon-bell"></i>
                                                                        </div>
                                                                    </div>
                                                                    <div class="cont-col2">
                                                                        <div class="desc">
                                                                            New user registered
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col2">
                                                                <div class="date">
                                                                    Just now
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="#">
                                                            <div class="col1">
                                                                <div class="cont">
                                                                    <div class="cont-col1">
                                                                        <div class="label label-sm label-success">                        
                                                                            <i class="icon-bell"></i>
                                                                        </div>
                                                                    </div>
                                                                    <div class="cont-col2">
                                                                        <div class="desc">
                                                                            New order received 
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col2">
                                                                <div class="date">
                                                                    10 mins
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <div class="col1">
                                                            <div class="cont">
                                                                <div class="cont-col1">
                                                                    <div class="label label-sm label-danger">                      
                                                                        <i class="icon-bolt"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="cont-col2">
                                                                    <div class="desc">
                                                                        Order #24DOP4 has been rejected.    
                                                                        <span class="label label-sm label-danger ">Take action <i class="icon-share-alt"></i></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col2">
                                                            <div class="date">
                                                                24 mins
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <a href="#">
                                                            <div class="col1">
                                                                <div class="cont">
                                                                    <div class="cont-col1">
                                                                        <div class="label label-sm label-success">                        
                                                                            <i class="icon-bell"></i>
                                                                        </div>
                                                                    </div>
                                                                    <div class="cont-col2">
                                                                        <div class="desc">
                                                                            New user registered
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col2">
                                                                <div class="date">
                                                                    Just now
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="#">
                                                            <div class="col1">
                                                                <div class="cont">
                                                                    <div class="cont-col1">
                                                                        <div class="label label-sm label-success">                        
                                                                            <i class="icon-bell"></i>
                                                                        </div>
                                                                    </div>
                                                                    <div class="cont-col2">
                                                                        <div class="desc">
                                                                            New user registered
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col2">
                                                                <div class="date">
                                                                    Just now
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="#">
                                                            <div class="col1">
                                                                <div class="cont">
                                                                    <div class="cont-col1">
                                                                        <div class="label label-sm label-success">                        
                                                                            <i class="icon-bell"></i>
                                                                        </div>
                                                                    </div>
                                                                    <div class="cont-col2">
                                                                        <div class="desc">
                                                                            New user registered
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col2">
                                                                <div class="date">
                                                                    Just now
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="#">
                                                            <div class="col1">
                                                                <div class="cont">
                                                                    <div class="cont-col1">
                                                                        <div class="label label-sm label-success">                        
                                                                            <i class="icon-bell"></i>
                                                                        </div>
                                                                    </div>
                                                                    <div class="cont-col2">
                                                                        <div class="desc">
                                                                            New user registered
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col2">
                                                                <div class="date">
                                                                    Just now
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="#">
                                                            <div class="col1">
                                                                <div class="cont">
                                                                    <div class="cont-col1">
                                                                        <div class="label label-sm label-success">                        
                                                                            <i class="icon-bell"></i>
                                                                        </div>
                                                                    </div>
                                                                    <div class="cont-col2">
                                                                        <div class="desc">
                                                                            New user registered
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col2">
                                                                <div class="date">
                                                                    Just now
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="#">
                                                            <div class="col1">
                                                                <div class="cont">
                                                                    <div class="cont-col1">
                                                                        <div class="label label-sm label-success">                        
                                                                            <i class="icon-bell"></i>
                                                                        </div>
                                                                    </div>
                                                                    <div class="cont-col2">
                                                                        <div class="desc">
                                                                            New user registered
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col2">
                                                                <div class="date">
                                                                    Just now
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="#">
                                                            <div class="col1">
                                                                <div class="cont">
                                                                    <div class="cont-col1">
                                                                        <div class="label label-sm label-success">                        
                                                                            <i class="icon-bell"></i>
                                                                        </div>
                                                                    </div>
                                                                    <div class="cont-col2">
                                                                        <div class="desc">
                                                                            New user registered
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col2">
                                                                <div class="date">
                                                                    Just now
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="tab-pane" id="tab_1_3">
                                            <div class="scroller" style="height: 290px;" data-always-visible="1" data-rail-visible1="1">
                                                <div class="row">
                                                    <div class="col-md-6 user-info">
                                                        <img alt="" src="assets/img/avatar.png" class="img-responsive" />
                                                        <div class="details">
                                                            <div>
                                                                <a href="#">Robert Nilson</a> 
                                                                <span class="label label-sm label-success label-mini">Approved</span>
                                                            </div>
                                                            <div>29 Jan 2013 10:45AM</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 user-info">
                                                        <img alt="" src="assets/img/avatar.png" class="img-responsive" />
                                                        <div class="details">
                                                            <div>
                                                                <a href="#">Lisa Miller</a> 
                                                                <span class="label label-sm label-info">Pending</span>
                                                            </div>
                                                            <div>19 Jan 2013 10:45AM</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 user-info">
                                                        <img alt="" src="assets/img/avatar.png" class="img-responsive" />
                                                        <div class="details">
                                                            <div>
                                                                <a href="#">Eric Kim</a> 
                                                                <span class="label label-sm label-info">Pending</span>
                                                            </div>
                                                            <div>19 Jan 2013 12:45PM</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 user-info">
                                                        <img alt="" src="assets/img/avatar.png" class="img-responsive" />
                                                        <div class="details">
                                                            <div>
                                                                <a href="#">Lisa Miller</a> 
                                                                <span class="label label-sm label-danger">In progress</span>
                                                            </div>
                                                            <div>19 Jan 2013 11:55PM</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 user-info">
                                                        <img alt="" src="assets/img/avatar.png" class="img-responsive" />
                                                        <div class="details">
                                                            <div>
                                                                <a href="#">Eric Kim</a> 
                                                                <span class="label label-sm label-info">Pending</span>
                                                            </div>
                                                            <div>19 Jan 2013 12:45PM</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 user-info">
                                                        <img alt="" src="assets/img/avatar.png" class="img-responsive" />
                                                        <div class="details">
                                                            <div>
                                                                <a href="#">Lisa Miller</a> 
                                                                <span class="label label-sm label-danger">In progress</span>
                                                            </div>
                                                            <div>19 Jan 2013 11:55PM</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 user-info">
                                                        <img alt="" src="assets/img/avatar.png" class="img-responsive" />
                                                        <div class="details">
                                                            <div><a href="#">Eric Kim</a> <span class="label label-sm label-info">Pending</span></div>
                                                            <div>19 Jan 2013 12:45PM</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 user-info">
                                                        <img alt="" src="assets/img/avatar.png" class="img-responsive" />
                                                        <div class="details">
                                                            <div>
                                                                <a href="#">Lisa Miller</a> 
                                                                <span class="label label-sm label-danger">In progress</span>
                                                            </div>
                                                            <div>19 Jan 2013 11:55PM</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 user-info">
                                                        <img alt="" src="assets/img/avatar.png" class="img-responsive" />
                                                        <div class="details">
                                                            <div><a href="#">Eric Kim</a> <span class="label label-sm label-info">Pending</span></div>
                                                            <div>19 Jan 2013 12:45PM</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 user-info">
                                                        <img alt="" src="assets/img/avatar.png" class="img-responsive" />
                                                        <div class="details">
                                                            <div>
                                                                <a href="#">Lisa Miller</a> 
                                                                <span class="label label-sm label-danger">In progress</span>
                                                            </div>
                                                            <div>19 Jan 2013 11:55PM</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 user-info">
                                                        <img alt="" src="assets/img/avatar.png" class="img-responsive" />
                                                        <div class="details">
                                                            <div>
                                                                <a href="#">Eric Kim</a> 
                                                                <span class="label label-sm label-info">Pending</span>
                                                            </div>
                                                            <div>19 Jan 2013 12:45PM</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 user-info">
                                                        <img alt="" src="assets/img/avatar.png" class="img-responsive" />
                                                        <div class="details">
                                                            <div>
                                                                <a href="#">Lisa Miller</a> 
                                                                <span class="label label-sm label-danger">In progress</span>
                                                            </div>
                                                            <div>19 Jan 2013 11:55PM</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--END TABS-->
                            </div>
                        </div>
                        <!-- END PORTLET-->
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="row ">
                    <div class="col-md-6 col-sm-6">
                        <!-- BEGIN PORTLET-->
                        <div class="portlet box blue calendar">
                            <div class="portlet-title">
                                <div class="caption"><i class="icon-calendar"></i>Calendar</div>
                            </div>
                            <div class="portlet-body light-grey">
                                <div id="calendar"></div>
                            </div>
                        </div>
                        <!-- END PORTLET-->
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <!-- BEGIN PORTLET-->
                        <div class="portlet">
                            <div class="portlet-title line">
                                <div class="caption"><i class="icon-comments"></i>Chats</div>
                                <div class="tools">
                                    <a href="" class="collapse"></a>
                                    <a href="#portlet-config" data-toggle="modal" class="config"></a>
                                    <a href="" class="reload"></a>
                                    <a href="" class="remove"></a>
                                </div>
                            </div>
                            <div class="portlet-body" id="chats">
                                <div class="scroller" style="height: 435px;" data-always-visible="1" data-rail-visible1="1">
                                    <ul class="chats">
                                        <li class="in">
                                            <img class="avatar img-responsive" alt="" src="assets/img/avatar1.jpg" />
                                            <div class="message">
                                                <span class="arrow"></span>
                                                <a href="#" class="name">Bob Nilson</a>
                                                <span class="datetime">at Jul 25, 2012 11:09</span>
                                                <span class="body">
                                                    Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.
                                                </span>
                                            </div>
                                        </li>
                                        <li class="out">
                                            <img class="avatar img-responsive" alt="" src="assets/img/avatar2.jpg" />
                                            <div class="message">
                                                <span class="arrow"></span>
                                                <a href="#" class="name">Lisa Wong</a>
                                                <span class="datetime">at Jul 25, 2012 11:09</span>
                                                <span class="body">
                                                    Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.
                                                </span>
                                            </div>
                                        </li>
                                        <li class="in">
                                            <img class="avatar img-responsive" alt="" src="assets/img/avatar1.jpg" />
                                            <div class="message">
                                                <span class="arrow"></span>
                                                <a href="#" class="name">Bob Nilson</a>
                                                <span class="datetime">at Jul 25, 2012 11:09</span>
                                                <span class="body">
                                                    Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.
                                                </span>
                                            </div>
                                        </li>
                                        <li class="out">
                                            <img class="avatar img-responsive" alt="" src="assets/img/avatar3.jpg" />
                                            <div class="message">
                                                <span class="arrow"></span>
                                                <a href="#" class="name">Richard Doe</a>
                                                <span class="datetime">at Jul 25, 2012 11:09</span>
                                                <span class="body">
                                                    Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.
                                                </span>
                                            </div>
                                        </li>
                                        <li class="in">
                                            <img class="avatar img-responsive" alt="" src="assets/img/avatar3.jpg" />
                                            <div class="message">
                                                <span class="arrow"></span>
                                                <a href="#" class="name">Richard Doe</a>
                                                <span class="datetime">at Jul 25, 2012 11:09</span>
                                                <span class="body">
                                                    Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.
                                                </span>
                                            </div>
                                        </li>
                                        <li class="out">
                                            <img class="avatar img-responsive" alt="" src="assets/img/avatar1.jpg" />
                                            <div class="message">
                                                <span class="arrow"></span>
                                                <a href="#" class="name">Bob Nilson</a>
                                                <span class="datetime">at Jul 25, 2012 11:09</span>
                                                <span class="body">
                                                    Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.
                                                </span>
                                            </div>
                                        </li>
                                        <li class="in">
                                            <img class="avatar img-responsive" alt="" src="assets/img/avatar3.jpg" />
                                            <div class="message">
                                                <span class="arrow"></span>
                                                <a href="#" class="name">Richard Doe</a>
                                                <span class="datetime">at Jul 25, 2012 11:09</span>
                                                <span class="body">
                                                    Lorem ipsum dolor sit amet, consectetuer adipiscing elit, 
                                                    sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.
                                                </span>
                                            </div>
                                        </li>
                                        <li class="out">
                                            <img class="avatar img-responsive" alt="" src="assets/img/avatar1.jpg" />
                                            <div class="message">
                                                <span class="arrow"></span>
                                                <a href="#" class="name">Bob Nilson</a>
                                                <span class="datetime">at Jul 25, 2012 11:09</span>
                                                <span class="body">
                                                    Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. sed diam nonummy nibh euismod tincidunt ut laoreet.
                                                </span>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                <div class="chat-form">
                                    <div class="input-cont">   
                                        <input class="form-control" type="text" placeholder="Type a message here..." />
                                    </div>
                                    <div class="btn-cont"> 
                                        <span class="arrow"></span>
                                        <a href="" class="btn blue icn-only"><i class="icon-ok icon-white"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- END PORTLET-->
                    </div>
                </div>
