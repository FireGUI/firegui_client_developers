<page backtop="37mm" backleft="20mm" backright="12mm" backbottom="30mm">
    
    <page_header>
        <img src="<?php echo base_url("images/pdf/offer_header.png"); ?>" style="width: 100%" />
    </page_header>
    
    <page_footer>
        <img src="<?php echo base_url("images/pdf/offer_footer.png"); ?>" style="width: 100%" />
    </page_footer>
    
    
    <style>
        
        .offerta_riepilogo {
            margin-left: 95px;
            font-size: 13px;
            color: #333;
        }
        
        
        .data_offerta {
            font-size: 16px;
            font-weight: bold;
        }
        
        .offer_customer {
            font-weight: bold;
        }
        
        .offerta_note {
            margin-top: 55px;
            font-size: 11px;
        }
        
        
        
        .nome_agente {
            font-weight: bold;
            font-size: 17px;
        }
        
        .telefono_agente {
            font-size: 15px;
        }
        
        
        
        hr {
            border: none;
            background: #bf1818
        }
        
        
    </style>
    
    
    
    <table class="offerta_riepilogo">
        <tr>
            <td width="25" style="vertical-align: top;">
                <img src="<?php echo base_url("images/pdf/offer.png"); ?>" style="width: 100%" />
            </td>
            <td width="250" style="vertical-align: top;">
                n. <span class="numero_offerta"><?php echo get_offer_number($dati['offer']['offers_number'], $dati['offer']['offers_date_creation']); ?></span><br/>
                del <span class="data_offerta"><?php echo date('d/m/Y', strtotime($dati['offer']['offers_date_creation'])); ?></span>
            </td>
            <td width="270" style="vertical-align: bottom;">
                <br/><br/><br/>
                Spett.le<br/>
                <span class="offer_customer"><?php echo $dati['offer'][CUSTOMER_NAME]; ?></span><br/>
                <?php echo $dati['offer'][CUSTOMER_ADDR]; ?><br/>
                <?php echo $dati['offer'][CUSTOMER_CAP]; ?> <?php echo $dati['offer'][CITY_NAME]; ?> <?php if($dati['offer'][PROV_NAME]) echo '('.$dati['offer'][PROV_NAME].')'; ?><br/>
            </td>
        </tr>
    </table>
    <br/>
    <br/>
    <br/>
    
    
    <table style="border-collapse: collapse; color: #fff;">
        <tr>
            <td width="100" style="background: #bf1818;">&nbsp;</td>
            <td width="420" style="background: #181818; font-size: 10px; vertical-align: top;">
                &nbsp;
                Contatti diretti
                <?php if($dati['offer'][CUSTOMER_PHONE]) echo 'Tel: '.$dati['offer'][CUSTOMER_PHONE]; ?> &nbsp;
                <?php if($dati['offer'][CUSTOMER_FAX]) echo 'Fax: '.$dati['offer'][CUSTOMER_FAX]; ?> &nbsp;
                <?php if($dati['offer'][CUSTOMER_EMAIL]) echo 'E-mail: '.$dati['offer'][CUSTOMER_EMAIL]; ?> &nbsp;
            </td>
            <td width="100" style="background: #181818; font-size: 17px; vertical-align: middle;"><?php echo get_offer_number($dati['offer']['offers_number'], $dati['offer']['offers_date_creation']); ?></td>
        </tr>
    </table>
    
    <?php foreach($dati['products'] as $product): ?>
        <p>
            <strong>Nr. <?php echo $product['offers_products_quantity']; ?> <?php echo $product['offers_products_name']; ?></strong><br/>
            euro cad. <?php echo number_format($product['offers_products_price'],2); ?>
        </p>
    <?php endforeach; ?>
    
    <p class="offerta_note">
        <?php echo $dati['offer']['offers_notes']; ?>
    </p>
    
    
    <table style="border-collapse: collapse; color: #fff; font-size: 5px;">
        <tr>
            <td width="100" style="background: #bf1818;">&nbsp;</td>
            <td width="420" style="background: #181818; vertical-align: top;">&nbsp;</td>
            <td width="100" style="background: #181818; vertical-align: middle;">&nbsp;</td>
        </tr>
    </table>
    
    
    
    <p style="font-size: 16px; margin-top: 45px;">
        Restando a disposizione, porgo distinti saluti
    </p>
    
    
    <p style="font-size: 16px; text-align: right">
        <span class="nome_agente"><?php echo $dati['offer'][USER_NAME]; ?></span><br/>
        <span class="telefono_agente"><?php echo $dati['offer'][USER_CELL]; ?></span>
    </p>
</page>