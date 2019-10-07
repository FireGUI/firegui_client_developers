<?php
    // The base filters
    $filters = [
        'user' => 1,
        'ignore_my_ip' => 1,
        'agenzia' => null,
        'actions' => [
            Apilib::LOG_LOGIN,
            Apilib::LOG_LOGIN_FAIL,
            Apilib::LOG_LOGOUT,
            Apilib::LOG_ACCESS,
            Apilib::LOG_DELETE,
        ]
    ];
    
    if ($this->input->post('reset')) {
        $this->session->unset_userdata('log.filters');
        $_POST = [];
    }
    
    if (($session = $this->session->userdata('log.filters'))) {
        // If we have session info, merge with defaults
        $filters = array_merge($filters, $session);
    }
    
    if (($post = $this->input->post())) {
        // Did we passed the post? Then save it to the session
        $filters = array_merge($filters, $post);
        $this->session->set_userdata('log.filters', $post);
    }
    
    /*
     * Filtering
     */
    $this->db->start_cache();
    switch ($filters['user']) {
        case 1:
            $this->db->where('log_crm_user_id IS NOT NULL');
            break;
        
        case 2:
            $this->db->where('log_crm_user_id IS NULL');
            break;
        
        default:
            // By default do not filter
    }
    
    if ($filters['ignore_my_ip'] && filter_input(INPUT_SERVER, 'REMOTE_ADDR')) {
        $this->db->where('log_crm_ip_addr <>', filter_input(INPUT_SERVER, 'REMOTE_ADDR'));
    }
    
    if ($filters['actions']) {
        $this->db->where_in('log_crm_type', $filters['actions']);
    }
    
    /*
     * Join tables to logs
     */
    $this->db->stop_cache();
    
    /*
     * Ordering + Limiting + Query execution
     */
    $limit = 50;
    $page = $this->input->get('page')?:1;
    $this->db->offset(($page-1)*$limit);
    $this->db->limit($limit)->order_by('log_crm_time', 'desc');
    $logs = $this->db->get('log_crm')->result_array();
    $count = $this->db->count_all_results('log_crm');
    $this->db->flush_cache();
    
    /*
     * Prendo tutte le agenzie loggate
     * da mettere nel filtro
     */
    $agenzie = $this->db->query("SELECT * FROM users WHERE users_id IN (SELECT log_crm_user_id FROM log_crm) ORDER BY LOWER(users_first_name)")->result_array();
    
    /* Pagination */
    $pmax = round($count/$limit, 0, PHP_ROUND_HALF_UP);
    $pnext = $page<$pmax ? $page+1: null;
    $pprev = $page-1;
?>

<section class="content-header">
    <h1 class="clearfix">System Log</h1>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            Filtri
        </div>

        <form method="POST">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="user_radio">Visualizza</label>
                            
                            <div class="radio">
                                <label>
                                    <input type="radio" id="user_radio" name="user" value="1" <?php echo $filters['user'] == 1 ? 'checked' : ''; ?> />
                                    Utente Rilevato
                                </label>
                            </div>

                            <div class="radio">
                                <label>
                                    <input type="radio" id="user_radio" name="user" value="2" <?php echo $filters['user'] == 2 ? 'checked' : ''; ?> />
                                    Utente Non Rilevato
                                </label>
                            </div>

                            <div class="radio">
                                <label>
                                    <input type="radio" id="user_radio" name="user" value="" <?php echo!$filters['user'] ? 'checked' : ''; ?> />
                                    Tutti
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class='control-label'></label>

                            <input type="hidden" name="ignore_my_ip" value="0" />
                            <label for="ignore_my_ip">IP</label>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" id="ignore_my_ip" name="ignore_my_ip" value="1" <?php echo $filters['ignore_my_ip'] ? 'checked' : ''; ?> />
                                    Ignora questo IP
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="utente">Utente</label>
                            <select name="agenzia" id="utente" class="form-control select2me">
                                <option></option>
                                <?php foreach($agenzie as $agenzia): ?>
                                    <option value="<?php echo $agenzia['users_id']; ?>" <?php echo ($filters['agenzia']==$agenzia['users_id']) ? 'selected': ''; ?>><?php echo $agenzia['users_first_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-8">
                        <div class='form-group'>
                            <label for="actions">Tipo di azione</label>
        
                            <?php
                                $tipi = [
                                    Apilib::LOG_LOGIN => 'Login da form',
                                    Apilib::LOG_LOGIN_FAIL => 'Login fallito',
                                    Apilib::LOG_LOGOUT => 'Logout',
                                    Apilib::LOG_ACCESS => 'Accesso giornaliero',
                                    Apilib::LOG_CREATE => 'Creazione record',
                                    Apilib::LOG_CREATE_MANY => 'Creazione record (bulk)',
                                    Apilib::LOG_EDIT => 'Modifica record',
                                    Apilib::LOG_DELETE => 'Eliminazione di un record',
                                ];
                            ?>

                            <div class="row">
                                <?php foreach ($tipi as $id => $name): ?>
                                    <div class="col-xs-3">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="actions" name="actions[]" value="<?php echo $id; ?>" <?php echo in_array($id, $filters['actions']) ? 'checked' : ''; ?> />
                                            <?php echo $name; ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-8">
                    
                    </div>
                </div>
                
                <div class="box-footer">
                    <button class='btn btn-sm btn-primary'>Filtra</button>&nbsp;<button name="reset" value="1" class='btn btn-sm btn-danger'>Reset</button>
                </div>
            </div>
        </form>
    </div>

    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Elenco Log</h3>

            <div class="box-tools pull-right">
                <div class="col-xs-6">
                    Pagina <?php echo $page; ?> di <?php echo $pmax; ?>.<br/>Mostrati <?php echo $limit; ?> su <?php echo $count; ?>
                </div>
                <div class="col-xs-6">
                    <nav>
                        <ul class="pagination">
                            <?php if ($pprev): ?>
                                <li>
                                    <a href="?page=<?php echo $pprev ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo; Prev.</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                            &nbsp;
                            <?php if ($pnext): ?>
                                <li>
                                    <a href="?page=<?php echo $pnext ?>" aria-label="Next">
                                        <span aria-hidden="true">Next &raquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

        <table class="table table-striped table-condensed">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Utente</th>
                    <th>Titolo azione</th>
                    <th>IP</th>
                    <th>Extra-Data</th>
                    <th>Browser</th>
                </tr>
            </thead>

            <tbody>
                <?php $curdate = null; ?>
                <?php foreach ($logs as $log): ?>
                    <?php if ($curdate != ($tmp = dateFormat($log['log_crm_time']))): ?>
                        <tr class="bg-blue">
                            <td colspan="6"><i><?php echo ($curdate = $tmp); ?></i></td>
                        </tr>
                    <?php endif; ?>

                    <tr>
                        <td>
                            <?php echo dateFormat($log['log_crm_time'], 'H:i') ?>
                            <small class="text-muted"><?php echo dateFormat($log['log_crm_time'], 's'); ?></small>
                        </td>
                        <td>
                            <?php if (!$log['log_crm_user_id']): ?>
                                <em class="font-red-thunderbird">*** Utente non rilevato ***</em>
                            <?php else: ?>
                                <strong><?php echo $log['log_crm_user_name']; ?></strong>
                                <!--<small class="text-muted"><?php /*echo $log['users_first_name']; */?></small>-->
                            <?php endif; ?>
                        </td>
                        <td><?php echo $log['log_crm_title']; ?></td>
                        <td><?php echo $log['log_crm_ip_addr']; ?></td>
                        <td><?php
                                if ($log['log_crm_extra']) {
                                    foreach (json_decode($log['log_crm_extra'], true) as $key => $val) {
                                        if (is_array($val)) {
                                            $val = implode(', ', $val);
                                        }
                                        
                                        echo "<strong>{$key}</strong>: {$val}<br/>";
                                    }
                                }
                            ?></td>
                        <td><small class="text-muted"><?php echo $log['log_crm_user_agent']; ?></small></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="box-footer">
            <div class="box-tools pull-right">
                <nav>
                    <ul class="pagination">
                        <?php if ($pprev): ?>
                            <li>
                                <a href="?page=<?php echo $pprev ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo; Prev</span>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if ($pnext): ?>
                            <li>
                                <a href="?page=<?php echo $pnext ?>" aria-label="Next">
                                    <span aria-hidden="true">Next &raquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>

            <div class="clearfix"></div>
        </div>
    </div>
</section>