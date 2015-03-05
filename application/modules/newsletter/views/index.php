<h3 class="page-title">Newsletter</h3>



<div class="row">
    <div class="col-md-3">
        <a href="<?php echo base_url("newsletter/create_template"); ?>">
            <div class="dashboard-stat blue">
                <div class="visual"><i class="icon-plus"></i></div>

                <div class="details">
                    <div class="number"><?php echo count($dati['templates']); ?></div>
                    <div class="desc">Template</div>
                </div>

                <span class="more">Crea template <i class="m-icon-swapright m-icon-white"></i></span>
            </div>
        </a>

        <a href="<?php echo base_url("newsletter/write_mail"); ?>">
            <div class="dashboard-stat green">
                <div class="visual">
                    <i class="icon-pencil"></i>
                </div>

                <div class="details">
                    <div class="number"><?php echo $this->db->count_all('newsletters'); ?></div>
                    <div class="desc">Newsletter create</div>
                </div>

                <span class="more">Nuova newsletter <i class="m-icon-swapright m-icon-white"></i></span>
            </div>
        </a>
    </div>

    <div class="col-md-9">
        <table class="table table-condensed table-bordered table-hover">
            <thead>
                <tr>
                    <th>Oggetto</th>
                    <th>Data invio</th>
                    <th>Mail Inviate / Totali</th>
                    <th>Riproponi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($dati['newsletters'] as $newsletter): ?>
                    <tr>
                        <td><?php echo $newsletter['newsletter_subject']; ?></td>
                        <td><?php echo $newsletter['newsletter_date']; ?></td>
                        <td><?php echo $this->db->where('mail_sent', 't')->where('mail_newsletter', $newsletter['newsletter_id'])->count_all_results('newsletter_mail_queue'); ?> / <?php echo $this->db->where('mail_newsletter', $newsletter['newsletter_id'])->count_all_results('newsletter_mail_queue'); ?></td>
                        <td>
                            <a class="btn btn-xs blue" href="<?php echo base_url("newsletter/riproponi/{$newsletter['newsletter_id']}"); ?>"><i class="icon-share-alt"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>