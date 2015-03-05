<div id="container_hightcharts_<?php echo $chart['charts_id']; ?>" style="min-width: 310px; height: 400px; width: 100%; margin: 0 auto"></div>

<script>
    $(function() {

                // Build the chart
        $('#container_hightcharts_<?php echo $chart['charts_id']; ?>').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                text: '<?php echo $chart['charts_title']; ?>'
            },
            subtitle: {
                text: '<?php echo $chart['charts_subtitle']; ?>'
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.y}</b>'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: false,
                        //format: '<b>{point.name}</b>: {point.y}',
                        crop:true,
                         style: {
                            fontWeight: 'bold',
                            color: 'white',
                            textShadow: '0px 1px 2px black'
                        }
                    },
                    showInLegend: true,
                    startAngle: -90,
                    endAngle: 90,
                    center: ['50%', '75%']
                }
            },
            series: [{
                type: 'pie',
                innerSize: '50%',
                <?php foreach($chart_data[0]['series'] as $name => $data): ?>
                    name: '<?php echo $name; ?>',
                    data: [<?php foreach($data as $x => $y) {echo "['{$x}', {$y}],";} ?>]
                <?php endforeach; ?>
            }]
        });
    });

</script>

