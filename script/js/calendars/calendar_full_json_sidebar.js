function initCalendars(container) {
    if (typeof container === "undefined") {
        container = $("body");
    }
    $(function () {
        "use strict";
        $(".calendar_full_json_sidebar", container).each(function () {
            var jqCalendarView;

            var jqCalendar = $(this);
            var calendarData = JSON.parse(atob($(this).data("calendar")));
            var calendarId = jqCalendar.attr("id");
            var sourceUrl = $(this).data("sourceurl");
            var minTime = $(this).data("mintime");
            var maxTime = $(this).data("maxtime");
            var language = $(this).data("language");
            var startField = $(this).data("start");
            var endField = $(this).data("end");
            var alldayfield = $(this).data("allday");
            var formurl = $(this).data("formurl");
            var url_parameters = $(this).data("url-parameters");
            var formedit = $(this).data("formedit");
            var fieldid = $(this).data("fieldid");
            var updateurl = $(this).data("updateurl");
            var allow_create = $(this).data("allow_create");
            var allow_edit = $(this).data("allow_edit");
            var calendars_default_view = $(this).data("view");
            var main_container = $(this).closest(".js_calendar_sidemain_container");
            var isStartDateTime = $(this).data("start-is-datetime");
            var isEndDateTime = $(this).data("end-is-datetime");
            // ============================

            var token = JSON.parse(atob($("body").data("csrf")));
            var token_name = token.name;
            var token_hash = token.hash;

            var date = new Date();
            var d = date.getDate();
            var m = date.getMonth();
            var y = date.getFullYear();

            var updateCalendar = function (evt) {
                if (allow_edit) {
                    var allDay = isAlldayEvent(evt.event.start, evt.event.end);
                    var fStart = moment(evt.event.start).format("DD/MM/YYYY HH:mm"); // formatted start
                    var fEnd = moment(evt.event.end).format("DD/MM/YYYY HH:mm"); // formatted end
                    var data = {
                        [token_name]: token_hash,
                        [fieldid]: evt.event.id,
                        [startField]: fStart,
                        [endField]: fEnd,
                        //TODO: manage all days events
                    };

                    $.ajax({
                        url: updateurl,
                        type: "POST",
                        dataType: "json",
                        data: data,
                        success: function (data) {
                            if (parseInt(data.status) < 1) {
                                // revertFunc();
                                alert(data.txt);
                            }
                        },
                        error: function () {
                            // revertFunc();
                            alert("There was an error while saving the event");
                        },
                    });
                }
            };

            var calendarEl = document.getElementById(calendarId);
            if (calendarEl.fullCalendar) {
                return;
            }
            $("#" + calendarId).html("");
            var defaultView =
                typeof localStorage.getItem("fcDefaultView_" + calendarId) !== "undefined" && localStorage.getItem("fcDefaultView_" + calendarId) !== null
                    ? localStorage.getItem("fcDefaultView_" + calendarId)
                    : calendars_default_view;
            var defaultDate =
                typeof localStorage.getItem("fcDefaultDate_" + calendarId) !== "undefined" && localStorage.getItem("fcDefaultDate_" + calendarId) !== null
                    ? localStorage.getItem("fcDefaultDate_" + calendarId)
                    : moment().format("YYYY-MM-DD HH:mm");

            var calendar = new FullCalendar.Calendar(calendarEl, {
                plugins: ["interaction", "dayGrid", "timeGrid"],
                defaultView: defaultView,
                defaultDate: defaultDate,
                header: {
                    left: "title",
                    right: "today,prev,next,dayGridMonth,timeGridWeek,timeGridDay",
                },
                datesRender: function (info) {
                    localStorage.setItem("fcDefaultView_" + calendarId, info.view.type);
                },
                editable: true,
                selectable: true,
                disableDragging: false,
                weekNumbers: true,
                height: "auto",
                locale: language,
                timeFormat: "H:mm",
                axisFormat: "H:mm",
                forceEventDuration: true, // @links: https://github.com/fullcalendar/fullcalendar/issues/2655#issuecomment-223838926
                columnFormat: {
                    agendaWeek: "ddd D MMMM",
                },
                minTime: minTime,
                maxTime: maxTime,
                allDayHtml: '<i class="far fa-clock"></i>',
                selectHelper: true,
                select: function (date) {
                    if (allow_create) {
                        var fStart = moment(date.start); // formatted start
                        var fEnd = moment(date.end);

                        if (date.allDay) {
                            fEnd = moment(date.start).add(1, "hours");
                        }

                        if (isStartDateTime) {
                            fStart = fStart.format("DD/MM/YYYY HH:mm");
                        } else {
                            fStart = fStart.format("DD/MM/YYYY");
                        }

                        if (isEndDateTime) {
                            fEnd = fEnd.format("DD/MM/YYYY HH:mm");
                        } else {
                            fEnd = fEnd.format("DD/MM/YYYY");
                        }

                        var data = {};

                        data[startField] = fStart;

                        if (endField) {
                            data[endField] = fEnd;
                        }

                        if (alldayfield && typeof date.allDay !== "undefined") {
                            data[alldayfield] = date.allDay;
                        }

                        loadModal(
                            formurl + url_parameters,
                            data,
                            function () {
                                calendar.refetchEvents();
                            },
                            "get"
                        );
                    }
                },
                eventClick: function (evt) {
                    if (calendarData.calendars_event_click === "form" && allow_edit) {
                        loadModal(formedit + "/" + evt.event.id, {}, function () {
                            calendar.refetchEvents();
                        });
                    } else if (calendarData.calendars_event_click === "layout" && calendarData.calendars_layout_id.length > 0) {
                        if (calendarData.calendars_layout_modal == true) {
                            loadModal(base_url + "get_ajax/layout_modal/" + calendarData.calendars_layout_id + "/" + evt.event.id, {}, function () {
                                calendar.refetchEvents();
                            });
                        } else {
                            window.location.href = base_url + "main/layout/" + calendarData.calendars_layout_id + "/" + evt.event.id;
                        }
                    } else if (calendarData.calendars_event_click === "link" && calendarData.calendars_link.length > 0) {
                        var link = calendarData.calendars_link;

                        link = link.replace("{base_url}/", base_url);
                        link = link.replace("{value_id}", evt.event.id);

                        window.location.href = link;
                    }

                    return false;
                },

                eventDrop: function (evt) {
                    updateCalendar(evt);
                },

                eventResize: function (evt, delta, revertFunc) {
                    updateCalendar(evt);
                },
                eventSources: [
                    {
                        events: function (fetchInfo, successCallback, failureCallback) {
                            var values = [];
                            $(".js_check_filter", main_container)
                                .filter("[type=checkbox]:checked")
                                .each(function () {
                                    if ($(this).val() != 0) {
                                        values.push($(this).val());
                                    }
                                });

                            var start = moment(fetchInfo.start);
                            var end = moment(fetchInfo.end);
                            // Calcola la data intermedia tra start e end
                            var middleDate = start.clone().add(end.diff(start) / 2, "milliseconds");
                            // Salva la data intermedia in localStorage
                            localStorage.setItem("fcDefaultDate_" + calendarId, middleDate.format("YYYY-MM-DD HH:mm"));

                            $.ajax({
                                type: "POST",
                                url: sourceUrl,
                                dataType: "json",
                                async: true,
                                data: {
                                    filters: values,
                                    [token_name]: token_hash,
                                    start: moment(fetchInfo.start).format("YYYY-MM-DD HH:mm"),
                                    end: moment(fetchInfo.end).format("YYYY-MM-DD HH:mm"),
                                },
                                loading: function (bool) {
                                    $("#loading").fadeTo(bool ? 1 : 0);
                                },
                                success: function (response) {
                                    if (response && response.length != 0) {
                                        response.forEach((el) => {
                                            // Sono su giorni diversi, aumento di un giorno la fine per la visualizzazione corretta
                                            // https://fullcalendar.io/docs/event-parsing
                                            if (el.allDay) {
                                                if (moment(el.end).isAfter(el.start)) {
                                                    const correctEndDate = moment(el.end).add(1, "day").format("YYYY-MM-DD[T]HH:mm:ss");
                                                    el.end = correctEndDate;
                                                }
                                            }
                                        });
                                    }
                                    successCallback(response);
                                },
                                error: function (response) {
                                    console.log(response);
                                    failureCallback(response);
                                },
                            });
                        },
                        color: "#4B8DF8", // a non-ajax option
                        textColor: "white", // a non-ajax option
                    },
                ],
                eventRender: function (info) {
                    if (typeof info.event.extendedProps.description !== "undefined" && info.event.extendedProps.description) {
                        $(info.el).popover({
                            title: info.event.title,
                            content: info.event.extendedProps.description,
                            placement: "top",
                            container: "body",
                            trigger: "hover",
                            html: true,
                        });
                    }
                },
            });

            calendar.render();
            $(".js_check_filter", main_container).on("change", function () {
                calendar.refetchEvents();
            });
            $(".js_check_filter_all", main_container).on("change", function () {
                var $contenitore = $(this).closest(".js_sidebar_filter_container");
                if (this.checked) {
                    // Iterate each checkbox
                    $(":checkbox", $contenitore).each(function () {
                        this.checked = true;
                    });
                } else {
                    $(":checkbox", $contenitore).each(function () {
                        this.checked = false;
                    });
                }

                calendar.refetchEvents();
            });

            //$('.js_check_filter_all', $(this).closest('.js_calendar_sidemain_container')).trigger('change');
        });
    });
}
