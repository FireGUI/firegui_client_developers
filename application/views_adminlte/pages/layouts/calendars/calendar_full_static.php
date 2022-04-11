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
$settings = $this->db->join('languages', 'languages_id = settings_default_language', 'LEFT')->get('settings')->row_array();
?>
<div <?php echo sprintf('id="%s"', $calendarId); ?> class="has-toolbar"></div>
<style>
    .fc-scroller {
        height: 100% !important;
    }

    .total-label {
        font-size: 1.2rem;
        font-weight: bold;
    }
</style>

<script>
    $(function() {
        'use strict';
        if (!jQuery().fullCalendar) {
            throw Error('Calendar not loaded');
        }

        var jqCalendar = $('#<?php echo $calendarId; ?>');
        var calendarFetchType = <?php echo json_encode($data['calendars']['calendars_method']); ?>;
        var minTime = <?php echo json_encode(array_get($data['calendars'], 'calendars_min_time') ?: '06:00:00'); ?>;
        var maxTime = <?php echo json_encode(array_get($data['calendars'], 'calendars_max_time') ?: '22:00:00'); ?>;

        var token = JSON.parse(atob($('body').data('csrf')));
        var token_name = token.name;
        var token_hash = token.hash;

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
        }

        jqCalendar.fullCalendar('destroy'); // destroy the calendar


        switch (calendarFetchType) {
            case 'json':
                jqCalendar.fullCalendar({
                    editable: true,
                    disableDragging: false,
                    header: h,
                    minTime: minTime,
                    maxTime: maxTime,
                    lang: '<?php echo (!empty($settings['languages_code'])) ? (explode('-', $settings['languages_code'])[0]) : 'en'; ?>',
                    eventSources: [{
                        url: <?php echo json_encode(base_url("get_ajax/get_calendar_events/{$data['calendars']['calendars_id']}")) ?>,
                        type: 'POST',
                        data: {
                            [token_name]: token_hash,
                        },
                        error: function() {
                            alert('there was an error while fetching events!');
                        },
                        loading: function(bool) {
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
                    disableDragging: false,
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
                    eventClick: function(event) {
                        window.open(event.url, 'gcalevent', 'width=700,height=600');
                        return false;
                    },
                    loading: function(bool) {
                        bool ? $('#loading').show() : $('#loading').hide();
                    }
                });
                break;
        }
    });
</script>