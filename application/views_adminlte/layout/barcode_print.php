<style media="print">
    @page {
        size: 60mm 28mm landscape;
        margin: 10mm 0mm 0mm 0mm;
    }

    * {
        margin: 0;
        padding: 0;

    }
</style>



<div style="width:58mm;padding:1mm;height:26mm;border: 0px solid #000;overflow:hidden;font-size:11px;font-family: Arial;">

    <div style="text-align: center;">
        <?php
        $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
        $barcode_img = $generator->getBarcode($val, $type);
        ?>


        <img style="width: 42mm;height: 20mm; margin: 0 10px 2px 0;" src="data:image/png;base64,<?php echo base64_encode($barcode_img); ?>" /><br />





    </div>
</div>






<script>
    window.print();
</script>
<?php die(); ?>