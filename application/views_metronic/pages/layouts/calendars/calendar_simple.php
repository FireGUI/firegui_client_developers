<?php
$staticData = [];
if ($data['calendars']['calendars_method'] === 'static') {

    $data_entity = $this->apilib->index($data['calendars']['entity_name']);
    foreach ($data_entity as $event) {
        $item = [];
        foreach ($data['calendars_fields'] as $field) {
            if (array_key_exists($field['fields_name'], $event)) {
                $item[$field['calendars_fields_type']] = $event[$field['fields_name']];
            }
        }
        $staticData[] = $item;
    }
}
$calendarId = 'calendar' . $data['calendars']['calendars_id'];
?>
<div <?php echo sprintf('id="%s"', $calendarId); ?> class="has-toolbar"></div>


<script>

    $(function () {

        if (!$.fullCalendar) {
            throw Error('Calendar not loaded');
        }

        var jqCalendar = $('#<?php echo $calendarId; ?>');
        var calendarFetchType = <?php echo json_encode($data['calendars']['calendars_method']); ?>;
        var minTime = <?php echo json_encode(array_get($data['calendars'], 'calendars_min_time')?:'06:00:00'); ?>;
        var maxTime = <?php echo json_encode(array_get($data['calendars'], 'calendars_max_time')?:'22:00:00'); ?>;

        var date = new Date();
        var d = date.getDate();
        var m = date.getMonth();
        var y = date.getFullYear();
        var h = {};

        if (jqCalendar.width() <= 400) {
            jqCalendar.addClass("mobile");
            h = {
                left: 'title, prev, next',
                center: '',
                right: 'today,month,agendaWeek,agendaDay'
            };
        } else {
            jqCalendar.removeClass("mobile");
            if (Metronic.isRTL()) {
                h = {
                    right: 'title',
                    center: '',
                    left: 'prev,next,today,month,agendaWeek,agendaDay'
                };
            } else {
                h = {
                    left: 'title',
                    center: '',
                    right: 'prev,next,today,month,agendaWeek,agendaDay'
                };
            }
        }

        //$('#calendar').fullCalendar('destroy'); // destroy the calendar [???]

        switch (calendarFetchType) {
            case 'json':
                jqCalendar.fullCalendar({
                    minTime: minTime,
                    maxTime: maxTime,
                    editable: true,
                    disableDragging: false,
                    header: h,
                    eventSources: [{
                            url: <?php echo json_encode(base_url("get_ajax/get_calendar_events/{$data['calendars']['calendars_id']}/{$data['calendars']['calendars_where']}")) ?>,
                            type: 'POST',
                            error: function () {
                                alert('there was an error while fetching events!');
                            },
                            loading: function (bool) {
                                bool ? $('#loading').show() : $('#loading').hide();
                            },
                            color: 'yellow', // a non-ajax option
                            textColor: 'black' // a non-ajax option
                        }]
                });
                break;

            case 'static':
                jqCalendar.fullCalendar({
                    minTime: minTime,
                    maxTime: maxTime,
                    disableDragging: true,
                    header: h,
                    editable: true,
                    events: <?php echo json_encode($staticData); ?>
                });
                break;

            case 'gcal':
                jqCalendar.fullCalendar({
                    minTime: minTime,
                    maxTime: maxTime,
                    events: <?php echo json_encode($data['calendars']['calendars_method_param']); ?>,
                    eventClick: function (event) {
                        window.open(event.url, 'gcalevent', 'width=700,height=600');
                        return false;
                    },
                    loading: function (bool) {
                        bool ? $('#loading').show() : $('#loading').hide();
                    }

                });
                break;
        }
    });
</script>
