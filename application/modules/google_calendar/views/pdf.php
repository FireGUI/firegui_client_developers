<?php 
$datestart = '01-'.str_pad($dati['rimborso']['refunds_month'], 2, '0', STR_PAD_LEFT).'-'.$dati['rimborso']['refunds_year'];
$dateend = date('t-m-Y', strtotime($dati['rimborso']['refunds_year'].'/'.str_pad($dati['rimborso']['refunds_month'], 2, '0', STR_PAD_LEFT).'/01'));
$fullname = "{$dati['rimborso']['refunds_user_name']} {$dati['rimborso']['refunds_user_surname']}";
?>

<page backtop="5mm" backleft="5mm" backright="5mm" backbottom="5mm">
    
    <page_header>
    </page_header>
    
    <page_footer>
    </page_footer>
    
    
    <style>
        
        h1 { text-align: center; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .grey { color: #999999; }
        table thead th { background-color: #a4a4a4; text-align: center; }
        table th, table td { padding: 4px 10px; }
        table tr.odd td { background-color: #dadada; }
        table tr.even td { background-color: #f1f1f1; }
        
        .trip-table td { font-size: 12px; padding: 6px 10px; }
        
    </style>
    
    
    <h1>NOTA SPESE PER DIPENDENTI E COLLABORATORI</h1>
    <p>
        Io sottoscritto <strong><?php echo $fullname; ?></strong>
        in qualità di dipendente della società <strong><?php echo $dati['company_name']; ?></strong>
    </p>
    <p class="text-center bold">DICHIARO</p>
    <p>di aver sostenuto le seguenti spese per trasferta nel periodo dal <strong><?php echo $datestart; ?></strong> al <strong><?php echo $dateend; ?></strong></p>
    
    
    <div style='padding: 10px 15px;'>
        <table style='border: 1px solid #999999;'>
            <thead>
                <tr>
                    <th width="300">Tipologia spesa</th>
                    <th width="150">Note</th>
                    <th width="100">Importo Euro</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td width="300">Rimborso chilometrico per l'utilizzo del proprio automezzo</td>
                    <td>
                        <?php if($dati['rimborso']['refunds_car_plate']): ?>
                            <strong>Targa:</strong> <?php echo $dati['rimborso']['refunds_car_plate']; ?><br/>
                        <?php endif; ?>
                            
                        <?php if($dati['rimborso']['refunds_car_model']): ?>
                            <strong>Modello:</strong> <?php echo $dati['rimborso']['refunds_car_model']; ?><br/>
                        <?php endif; ?>
                            
                        <?php if($dati['rimborso']['refunds_kilometer_cost']): ?>
                            <strong>Costo/Km:</strong> <?php echo $dati['rimborso']['refunds_kilometer_cost']; ?><br/>
                        <?php endif; ?>
                    </td>
                    <td style='text-align: right;'>€ <?php echo $dati['rimborso']['refunds_price']; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    
    
    
    
    <table style="margin: 20px 0;">
        <tr>
            <td class="text-center">
                Firma del dichiarante<br/>
                <strong><?php echo $fullname; ?></strong><br/>
                <br/>
                <br/>
                <span class="grey">_________________________</span>
            </td>
        </tr>
    </table>
    
    
    
    
    <br/>
    <h1>DETTAGLIO DELLE TRASFERTE EFFETTUATE NEL PERIODO dal <?php echo $datestart; ?> al <?php echo $dateend; ?></h1>
    <table class="trip-table" width="290" cellspacing="2" cellpadding="0" border="0">
        <thead>
            <tr>
                <th>N.</th>
                <th>Data</th>
                <th>Cliente</th>
                <th>Da</th>
                <th>A</th>
                <th>Km</th>
                <th>€</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; ?>
            <?php foreach($dati['trips'] as $date=>$trips): ?>
                <?php foreach($trips as $k=>$trip): ?>
                    <tr class="<?php echo (($i%2 > 0)? 'odd': 'even'); ?>">
                        <td><?php echo ($k? NULL:$i); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($date)); ?></td>
                        <td style="width: 133px"><?php echo $trip['cliente']; ?></td>
                        <td style="width: 122px"><?php echo $trip['indirizzo_from']; ?></td>
                        <td style="width: 122px"><?php echo $trip['indirizzo_to']; ?></td>
                        <td><?php echo $trip['km']; ?></td>
                        <td><?php echo $trip['euro']; ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php $i++; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</page>