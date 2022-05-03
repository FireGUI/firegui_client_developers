<?php
/*
 * Replace supportati:
 *  - title     Il titolo dell'evento
 *  - text      Il testo dell'evento
 *  - date      Data evento
 */


$id = uniqid('js-timeline-');

$entity = $grid['grids'];
$data = $grid_data['data'];
$replaces = $grid['replaces'];

$dates = [];
$startTime = null;
foreach ($data as $dato) {
    if (!empty($grid['replaces']['date']['fields_name']) && !empty($dato[$grid['replaces']['date']['fields_name']])) {

        $time = strtotime($dato[$grid['replaces']['date']['fields_name']]);

        if (is_null($startTime)) {
            $startTime = $time;
        }

        $dates[] = [
            'headline' => isset($grid['replaces']['title']) ? $this->datab->build_grid_cell($grid['replaces']['title'], $dato) : null,
            'text' => isset($grid['replaces']['text']) ? $this->datab->build_grid_cell($grid['replaces']['text'], $dato) : null,
            'startDate' => date('Y,m,d', $time),
        ];
    }
}

$timeline = [
    'type' => 'default',
    'startDate' => date('Y,m,d', $startTime),
    'date' => $dates
];
?>

<div class="timeline_height">
    <div <?php echo sprintf('id="%s"', $id); ?>></div>
</div>


<script>
    $(function() {

        createStoryJS({
            type: 'timeline',
            lang: 'it',
            source: <?php echo json_encode(['timeline' => $timeline]); ?>,
            embed_id: <?php echo json_encode($id) ?>
        });

    });
</script>