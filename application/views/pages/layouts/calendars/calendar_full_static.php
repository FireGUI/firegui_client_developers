
<div id="calendar" class="has-toolbar"></div>


<script>
    function load_calendar() {
    if (!jQuery().fullCalendar) {
    return;
    }

    var date = new Date();
            var d = date.getDate();
            var m = date.getMonth();
            var y = date.getFullYear();
            var h = {};
            if ($('#calendar').width() <= 400) {
    $('#calendar').addClass("mobile");
            h = {
    left: 'title, prev, next',
            center: '',
            right: 'today,month,agendaWeek,agendaDay'
    };
    } else {
    $('#calendar').removeClass("mobile");
            if (App.isRTL()) {
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

    $('#calendar').fullCalendar('destroy'); // destroy the calendar


<?php if ($data['calendars']['calendars_method'] == 'json'): ?>
        // Carico eventi in json
        $('#calendar').fullCalendar({
        editable: true,
                disableDragging: false,
                header: h,
                eventSources: [
                // your event source
                {
                url: "<?php echo base_url(); ?>get_ajax/get_calendar_events/<?php echo $data['calendars']['calendars_id']; ?>/<?php echo $data['calendars']['calendars_where']; ?>",
                        type: 'POST',
                        data: {
                custom_param1: 'something',
                        custom_param2: 'somethingelse'
                },
                        error: function() {
                alert('there was an error while fetching events!');
                },
                        loading: function(bool) {
                if (bool)
                        $('#loading').show();
                        else
                        $('#loading').hide();
                },
                        color: 'yellow', // a non-ajax option
                        textColor: 'black' // a non-ajax option
                }

        // any other sources...

        ]
        });
<?php elseif ($data['calendars']['calendars_method'] == 'static'): ?>

    <?php $data_entity = $this->datab->get_data_entity($data['calendars']['calendars_entity_id']); ?>
        $('#calendar').fullCalendar({//re-initialize the calendar
        disableDragging: false,
                header: h,
                editable: true,
                events: [
    <?php foreach ($data_entity['data'] as $event): ?>
            {
        <?php foreach ($data['calendars_fields'] as $field): ?>
            <?php echo $field['calendars_fields_type']; ?>: '<?php echo $event[$field['fields_name']]; ?>',
        <?php endforeach; ?>
            },
    <?php endforeach; ?>
        ]
        });
<?php elseif ($data['calendars']['calendars_method'] == 'gcal'): ?>
        $('#calendar').fullCalendar({
		
			// US Holidays
			events: '<?php echo $data['calendars']['calendars_method_param']; ?>',
			
			eventClick: function(event) {
				// opens events in a popup window
				window.open(event.url, 'gcalevent', 'width=700,height=600');
				return false;
			},
			
			loading: function(bool) {
				if (bool) {
					$('#loading').show();
				}else{
					$('#loading').hide();
				}
			}
			
		});
<?php endif; ?>
    }
</script>
