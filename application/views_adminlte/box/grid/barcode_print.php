<style media="print">
    @page {
        size: <?php echo $w; ?>mm <?php echo $h; ?>mm landscape;
        margin: <?php echo $left; ?>px <?php echo $top; ?>px 2px 0;
    }

    * {
        margin: 0;
        padding: 0;

    }
</style>

<div style="width:<?php echo $w; ?>;padding:1mm;height:<?php echo $h; ?>mm;border: 0px solid #000;overflow:hidden;font-size:11px;font-family: Arial;">
    <div style="text-align: center;">
        <?php
        $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
        $barcode_img = $generator->getBarcode($val, $type);
        ?>

        <img style="width: 100%;height: 100%;" src="data:image/png;base64,<?php echo base64_encode($barcode_img); ?>" /><br />
        <?php echo $val; ?>
    </div>
</div>

<script>
    window.print();
</script>
<?php exit; ?>