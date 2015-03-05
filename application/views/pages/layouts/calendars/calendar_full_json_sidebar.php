<div class="row">
    <div class="col-md-3">
        <?php if($data['calendars']['calendars_filter_entity_id']): ?>
            <?php $entity = $this->datab->get_entity($data['calendars']['calendars_filter_entity_id']); ?>
            <h3><?php echo ucwords($entity['entity_name']); ?></h3>
            
            <?php $filter_data = $this->datab->get_entity_preview_by_name($entity['entity_name']); ?>
            <?php foreach($filter_data as $id => $nome): ?>
            <label class="checkbox">
                <input type="checkbox" name="cal_filter[]" class="js_check_filter" value="<?php echo $id; ?>" <?php if($entity['entity_name']==LOGIN_ENTITY && $id == $this->auth->get(LOGIN_ENTITY."_id")) echo 'checked'; ?> />
                <?php echo $nome; ?>
            </label>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <div class="col-md-9">
        <div id="calendar" class="has-toolbar"></div>
    </div>
</div>

<?php 
// Map the calendar fields with the entity fields
$calendar_map = array();
foreach ($data['calendars_fields'] as $field) {
    $calendar_map[$field['calendars_fields_type']] = $field['fields_name'];
}

if(!isset($calendar_map['id']) || !$calendar_map['id']) {
    $calendar_map['id'] = $data['calendars']['entity_name']."_id";
}

$element_id = (isset($value_id)? $value_id: NULL);
?>

<script>
    
    function load_calendar() {
        if (!jQuery().fullCalendar) {
            return;
        }
        
        var sourceUrl = "<?php echo base_url("get_ajax/get_calendar_events/{$data['calendars']['calendars_id']}/{$element_id}"); ?>";

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
        $('#calendar').fullCalendar({
            defaultView: 'agendaWeek',
            editable: true,
            selectable: true,
            disableDragging: false,
            header: h,
            
            monthNames: ['Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'],
            monthNamesShort: ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'],
            dayNames: ['Domenica', 'Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato'],
            dayNamesShort: ['Dom', 'Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab'],
            firstDay: 1,
            buttonText: {
                today:    'Mostra oggi',
                month:    'Mese',
                week:     'Sett.',
                day:      'Giorno'
            },
            
            selectHelper: true,
            select: function(start, end, allDay) {
                
                if (allDay) {
                    end.setDate(end.getDate() + 1);
                    end.setMinutes(end.getMinutes() - 1);
                }
                
                data = {
                    "<?php echo $calendar_map['start'] ?>": formatDate(start),
                    "<?php echo $calendar_map['end'] ?>": formatDate(end),
                    <?php if(isset($calendar_map['all_day'])): ?> "<?php echo $calendar_map['all_day'] ?>": (allDay? 1:0), <?php endif; ?>
                };

                loadModal('<?php echo base_url("get_ajax/modal_form/" . $this->datab->get_default_form($data['calendars']['calendars_entity_id'])); ?>', data, function() {
                    $('#calendar').fullCalendar('refetchEvents');
                });

                return;
            },
            eventClick: function( event, jsEvent, view ) {
                loadModal('<?php echo base_url("get_ajax/modal_form/" . $this->datab->get_default_form($data['calendars']['calendars_entity_id'])); ?>/'+event.id, {}, function() {
                    $('#calendar').fullCalendar('refetchEvents');
                });
                return false;
            },
            eventDrop: function( event, dayDelta, minuteDelta, allDay, revertFunc, jsEvent, ui, view ) {
                
                var oStart = new Date(event.start);
                var oEnd = new Date(event.end);
        
                
                if (allDay) {
                    oStart.setDate(event.start.getDate());
                    oStart.setMinutes(0);
                    oStart.setHours(0);
                    
                    oEnd.setDate(event.start.getDate());
                    oEnd.setHours(23);
                    oEnd.setMinutes(59);
                }
                
                
                $.ajax({
                    url: "<?php echo base_url("db_ajax/update_calendar_event/{$data['calendars']['calendars_id']}"); ?>",
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        "<?php echo $calendar_map['id'] ?>": event.id,
                        "<?php echo $calendar_map['start'] ?>": formatDate(oStart),
                        "<?php echo $calendar_map['end'] ?>": formatDate(oEnd),
                        <?php if(isset($calendar_map['all_day'])): ?> "<?php echo $calendar_map['all_day'] ?>": (allDay? 1:0), <?php endif; ?>
                    },
                    success: function(data) {
                        if(parseInt(data.status) < 1) {
                            revertFunc();
                            alert(data.txt);
                        }
                    },
                    error: function() {
                        revertFunc();
                        alert('There was an error while saving the event');
                    },
                });
            },
            eventResize: function(event,dayDelta,minuteDelta,revertFunc) {
                
                $.ajax({
                    url: "<?php echo base_url("db_ajax/update_calendar_event/{$data['calendars']['calendars_id']}"); ?>",
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        "<?php echo $calendar_map['id'] ?>": event.id,
                        "<?php echo $calendar_map['start'] ?>": formatDate(event.start),
                        "<?php echo $calendar_map['end'] ?>": formatDate(event.end),
                        <?php if(isset($calendar_map['all_day'])): ?> "<?php echo $calendar_map['all_day'] ?>": 0, <?php endif; ?>
                    },
                    success: function(data) {
                        if(parseInt(data.status) < 1) {
                            revertFunc();
                            alert(data.txt);
                        }
                    },
                    error: function() {
                        revertFunc();
                        alert('There was an error while saving the event');
                    },
                });
            },
            eventSources: [
                {
                    url: sourceUrl,
                    type: 'POST',
                    data: {filters: [<?php echo $this->auth->get(LOGIN_ENTITY."_id"); ?>]},
                    error: function(error) {
                        console.log(error.responseText);
                    },
                    loading: function(bool) {
                        $('#loading').fadeTo(bool? 1: 0);
                    },
                    color: '#4B8DF8', // a non-ajax option
                    textColor: 'white' // a non-ajax option
                }
            ]
        });
        
        
        
        $('.js_check_filter').on('change', function() {
            var checkboxes = $('.js_check_filter').filter('[type=checkbox]');
            
            var values = [];
            checkboxes.each(function() {
                if($(this).is(':checked')) {
                    values.push($(this).val());
                }
            });
            
            $('#calendar').fullCalendar('removeEventSource', sourceUrl);
            $('#calendar').fullCalendar('addEventSource', {
                url: sourceUrl,
                type: 'POST',
                data: {filters: values},
                error: function(error) {
                    console.log(error.responseText);
                },
                loading: function(bool) {
                    $('#loading').fadeTo(bool? 1: 0);
                },
                color: '#4B8DF8', // a non-ajax option
                textColor: 'white' // a non-ajax option
            });
        });
    }
</script>
