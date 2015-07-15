<?php $chartId = "container_hightcharts_{$chart['charts_id']}"; ?>
<div <?php echo sprintf('id="%s"', $chartId); ?> style="min-width: 310px; height: 400px; width: 100%; margin: 0 auto;overflow: hidden"></div>

<script>
    $(function() {

        $('#<?php echo $chartId; ?>').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: { text: '<?php echo $chart['charts_title']; ?>' },
            subtitle: { text: '<?php echo $chart['charts_subtitle']; ?>' },
            tooltip: { pointFormat: '{series.name}: <b>{point.y}</b>' },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b>: {point.y}',
                        crop:true,
                        style: {
                            color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                        },
                        connectorColor: 'silver'
                    }
                }
            },
            series: [{
                type: 'pie',
                <?php foreach($chart_data[0]['series'] as $name => $data): ?>
                    name: '<?php echo addslashes($name); ?>',
                    data: [<?php foreach($data as $x => $y) {echo sprintf("['%s', %s],", json_encode($x), $y);} ?>]
                <?php endforeach; ?>
            }]
        });
    });

</script>

