<?php if (isset($grid_data['data'])) : ?>
    <?php foreach ($grid_data['data'] as $dato) : ?>

        <div class="col-md-12">
            <?php foreach ($grid['grids_fields'] as $field) : ?>
                <div class="col-md-6">
                    <?php
                    // $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
                    // echo '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode('081231723897', $generator::TYPE_CODE_128)) . '">';
                    ?>
                    <barcode type="qr" mode="H">Hello</barcode>


                    <barcode code="04210000526" type="UPCE"></barcode>
                    <!-- Note the UPC-A code is required which is converted to UPCE -->


                    <barcode code="978-0-9542246-0-8 07" type="ISSNP2" text="1"></barcode>


                    <barcode code="01234567094987654321-01234567891" type="IMB"></barcode>


                    <barcode code="SN34RD1A" type="RM4SCC"></barcode>


                    <barcode code="54321068" type="I25"></barcode>


                    <barcode code="A34698735B" type="CODABAR"></barcode>


                </div>
            <?php endforeach; ?>
        </div>

    <?php endforeach; ?>
<?php endif; ?>