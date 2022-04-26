function initCalendars() {
    $(function() {
        'use strict';
        $('.calendar_full_json_sidebar').each(function() {
            var jqCalendarView;

            var jqCalendar = $(this);
            var sourceUrl = $(this).data('sourceurl');
            var minTime = $(this).data('mintime');
            var maxTime = $(this).data('maxtime');
            var language = $(this).data('language');
            var startField = $(this).data('start');
            var endField = $(this).data('end');
            var allday = $(this).data('allday');
            var formurl = $(this).data('formurl');
            var formedit = $(this).data('formedit');
            var fieldid = $(this).data('fieldid');
            var updateurl = $(this).data('updateurl');
            // ============================

            var token = JSON.parse(atob($('body').data('csrf')));
            var token_name = token.name;
            var token_hash = token.hash;

            var date = new Date();
            var d = date.getDate();
            var m = date.getMonth();
            var y = date.getFullYear();

            var updateCalendar = function(evt) {
                var allDay = isAlldayEvent(evt.event.start, evt.event.end);
                var fStart = moment(evt.event.start).format('DD/MM/YYYY HH:mm'); // formatted start
                var fEnd = moment(evt.event.end).format('DD/MM/YYYY HH:mm'); // formatted end
                var data = {
                    [token_name]: token_hash,
                    [fieldid]: evt.event.id,
                    [startField]: fStart,
                    [endField]: fEnd
                        //TODO: manage all days events
                };

                $.ajax({
                    url: updateurl,
                    type: 'POST',
                    dataType: 'json',
                    data: data,
                    success: function(data) {
                        if (parseInt(data.status) < 1) {
                            // revertFunc();
                            alert(data.txt);
                        }
                    },
                    error: function() {
                        // revertFunc();
                        alert('There was an error while saving the event');
                    },
                });
            }

            var calendarEl = document.getElementById(jqCalendar.attr('id'));

            $('#' + jqCalendar.attr('id')).html('');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                plugins: ['interaction', 'dayGrid', 'timeGrid'],
                defaultView: 'timeGridWeek',
                defaultDate: moment().format('YYYY-MM-DD HH:mm'),
                header: {
                    left: 'title',
                    right: 'prev,next,dayGridMonth,timeGridWeek,timeGridDay'
                },

                editable: true,
                selectable: true,
                disableDragging: false,
                height: 'auto',
                locale: language,
                timeFormat: 'H:mm',
                axisFormat: 'H:mm',
                timeFormat: 'H:mm',
                forceEventDuration: true, // @links: https://github.com/fullcalendar/fullcalendar/issues/2655#issuecomment-223838926
                columnFormat: {
                    agendaWeek: 'ddd D MMMM'
                },
                axisFormat: 'H:mm',
                minTime: minTime,
                maxTime: maxTime,
                allDayHtml: "<i class='far fa-clock'></i>",
                // eventRender: function (event, element) {
                //     element.attr('data-id', event.id).css({
                //         'margin-bottom': '1px',
                //         'border': '1px solid #aaa'
                //     });
                // },
                selectHelper: true,
                select: function(date, allDay) {
                    var fStart = moment(date.start).format('DD/MM/YYYY HH:mm'); // formatted start
                    var fEnd = moment(date.end).format('DD/MM/YYYY HH:mm'); // formatted end

                    var allDay = isAlldayEvent(fStart, fEnd, 'DD/MM/YYYY HH:mm');
                    var data = {
                        [token_name]: token_hash,
                        [startField]: fStart,
                        [endField]: fEnd
                            //TODO: manage all days events
                    };

                    loadModal(formurl, data, function() {
                        calendar.refetchEvents();
                    }, 'get');

                    if (allDay) {
                        end.date(end.date() + 1);
                        end.minutes(end.minutes() - 1);
                    }
                },
                eventClick: function(evt, jsEvent, view) {
                    loadModal(formedit + '/' + evt.event.id, {}, function() {
                        calendar.refetchEvents();
                    });
                    return false;
                },

                eventDrop: function(evt) {
                    updateCalendar(evt);
                },

                eventResize: function(evt, delta, revertFunc) {
                    updateCalendar(evt);
                },
                eventSources: [{
                    events: function(fetchInfo, successCallback, failureCallback) {
                        var values = [];
                        $('.js_check_filter').filter('[type=checkbox]:checked').each(function() {
                            values.push($(this).val());
                        });

                        $.ajax({
                            type: 'POST',
                            url: sourceUrl,
                            dataType: 'json',
                            data: {
                                filters: values,
                                [token_name]: token_hash,
                                "start": moment(fetchInfo.start).format('YYYY-MM-DD HH:mm'),
                                "end": moment(fetchInfo.end).format('YYYY-MM-DD HH:mm')
                            },
                            loading: function(bool) {
                                $('#loading').fadeTo(bool ? 1 : 0);
                            },
                            success: function(response) {
                                successCallback(response);
                            },
                            error: function(response) {
                                console.log(response);
                                failureCallback(response);
                            },
                        });
                    },
                    color: '#4B8DF8', // a non-ajax option
                    textColor: 'white' // a non-ajax option
                }],
                viewRender: function(view) {
                    // window.sessionStorage.setItem(sessionStorageKey, JSON.stringify({
                    //     view: view.name,
                    //     date: jqCalendar.fullCalendar('getDate').toISOString()
                    // }));
                }
            });

            calendar.render();

            $('.js_check_filter').on('change', function() {
                calendar.refetchEvents();
            });

            // Ripristina sessione
            var sessionStorageKey = jqCalendar.attr('id');

            // try {
            //     var calendarSession = JSON.parse(window.sessionStorage.getItem(sessionStorageKey));
            //     jqCalendar.fullCalendar('changeView', calendarSession.view);
            //     jqCalendar.fullCalendar('gotoDate', calendarSession.date);
            // } catch (e) {
            //     // ... skip ...
            // }

        });
    });
}