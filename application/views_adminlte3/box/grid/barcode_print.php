<style media="print">
    @page {
        size: <?php echo $w; ?>mm <?php echo $h; ?>mm landscape;
    }

    * {
        margin: 0;
        padding: 0;

    }

</style>

<div style="width:<?php echo $w; ?>mm; margin-top:<?php echo $top; ?>mm; margin-left:<?php echo $left; ?>mm; padding:1mm;height:<?php echo $h; ?>mm;border: 0px solid #000;overflow:hidden;font-size:11px;font-family: Arial;">
    <div style="text-align: center; width:100%; height:100%">
        <?php
        $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
        $barcode_img = $generator->getBarcode($val, $type);
        ?>

        <img style="width:100%;" src="data:image/png;base64,<?php echo base64_encode($barcode_img); ?>" /><br />
        <p><?php echo $val; ?></p>
    </div>
</div>

<script>
    window.print();
</script>
<?php exit; ?>