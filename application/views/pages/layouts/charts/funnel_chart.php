<div id="container_hightcharts_<?php echo $chart['charts_id']; ?>" style="min-width: 310px; height: 400px; width: 100%; margin: 0 auto"></div>

<script>
    $(function() {

                // Build the chart
        $('#container_hightcharts_<?php echo $chart['charts_id']; ?>').highcharts({
            chart: {
                   type: 'funnel',
                    marginRight: 150
            },
            title: { text: '<?php echo $chart['charts_title']; ?>',
            x: -50
        },
            subtitle: { text: '<?php echo $chart['charts_subtitle']; ?>' },
            plotOptions: {
            series: {
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b> ({point.y:,.0f})',
                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black',
                    softConnector: true,
                    crop: true
                },
                neckWidth: '30%',
                neckHeight: '25%'
                
                //-- Other available options
                // height: pixels or percent
                // width: pixels or percent
              }
            },
            legend: {
                enabled: false
            },
            series: [{
                    <?php foreach($chart_data[0]['series'] as $name => $data): ?>
                    name: '<?php echo addslashes($name); ?>',
                    data: [<?php foreach($data as $x => $y) {echo "['{$x}', {$y}],";} ?>]
                <?php endforeach; ?>
            }]
        });
    });

</script>

