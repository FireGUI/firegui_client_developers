<?php

// Map the calendar fields with the entity fields
$calendar_map = array();
foreach ($data['calendars_fields'] as $field) {
    $calendar_map[$field['calendars_fields_type']] = $field['fields_name'];
}

if (!isset($calendar_map['id']) || !$calendar_map['id']) {
    $calendar_map['id'] = $data['calendars']['entity_name'] . "_id";
}

$element_id = (isset($value_id) ? '/' . $value_id : null);
$calendarId = $data['calendars']['calendars_id'];
$calendars_default_view = (!empty($data['calendars']['calendars_default_view'])) ? $data['calendars']['calendars_default_view'] : 'timeGridWeek';

$settings = $this->db->join('languages', 'languages_id = settings_default_language', 'LEFT')->get('settings')->row_array();

$settings = $this->db->join('languages', 'languages_id = settings_default_language', 'LEFT')->get('settings')->row_array();

$config = [];
//debug($calendar_map, true);
if (!empty($calendar_map['start'])) {
    $config[] = "{$calendar_map['start']}: fStart";
}
if (!empty($calendar_map['end'])) {
    $config[] = "{$calendar_map['end']}: fEnd";
}
if (!empty($calendar_map['date_start'])) {
    $config[] = "{$calendar_map['date_start']}: fDateStart";
}

if (!empty($calendar_map['date_end'])) {
    $config[] = "{$calendar_map['date_end']}: fDateEnd";
}

if (!empty($calendar_map['hours_start'])) {
    $config[] = "{$calendar_map['hours_start']}: fTimeStart";
}

if (!empty($calendar_map['hours_end'])) {
    $config[] = "{$calendar_map['hours_end']}: fTimeEnd";
}

if (!empty($calendar_map['all_day'])) {
    $config[] = "{$calendar_map['all_day']}: allDay";
}

$imploded_config = implode(',', $config);

// $calendar_group_by = $data['calendars']['calendars_group_by'];
// $splits = [];
// if ($calendar_group_by) {
//     //Estraggo tutti i distict $calendar_group_by degli eventi e li metto nell'array
//     $distinct_group_by = $this->db->query("SELECT DISTINCT $calendar_group_by FROM ")->result_array();
// }

?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.6.14/vue.js"></script>
<!-- AXIOS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.1.2/axios.min.js"></script>
<!-- VUE CAL -->
<script src="https://unpkg.com/vue-cal@legacy"></script>
<link href="https://unpkg.com/vue-cal@legacy/dist/vuecal.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/vue-touch-events@1.0.3/vue-touch-events.min.js"></script>

<style>
.vue_cal_container {
    height: 700px;
}

.main-demo {
    font-size: 12px;
}

.main-demo .tagline {
    max-width: 500px;
    margin: 0 auto 5rem;
}

.main-demo .tagline .title1 {
    letter-spacing: normal;
}

.demo {
    border-radius: 4px;
}

.demo.vuecal--date-picker .vuecal__cell-events-count {
    width: 4px;
    height: 4px;
    min-width: 0;
    padding: 0;
    margin-top: 4px;
    color: transparent;
    background-color: #42b983;
}

.demo.vuecal--date-picker .vuecal__cell--selected .vuecal__cell-events-count {
    background-color: #fff;
}

.demo .vuecal__cell--out-of-scope {
    color: rgba(0, 0, 0, 0.15);
}

.demo.full-cal .vuecal__menu {
    background-color: transparent;
}

.demo.full-cal .vuecal__title-bar {
    background: rgba(0, 0, 0, 0.03);
}

.demo .vuecal__view-btn {
    background: none;
    padding: 0 10px;
    margin: 4px 2px;
    border-radius: 30px;
    height: 20px;
    line-height: 20px;
    font-size: 13px;
    text-transform: uppercase;
    border: none;
    color: inherit;
}

.demo .vuecal__view-btn--active {
    background: #42b982;
    color: #fff;
}

.demo.full-cal .weekday-label {
    opacity: 0.4;
    font-weight: 500;
}

.demo .vuecal__header .w-icon {
    color: inherit;
}

.demo:not(.vuecal--day-view) .vuecal__cell--selected {
    background-color: transparent;
}

.demo:not(.vuecal--day-view).full-cal .vuecal__cell--selected:before {
    border: 1px solid rgba(75, 141, 248, 0.8);
}

.demo .vuecal__event-time {
    margin: 3px 0;
    font-size: 12px;
    font-weight: 500;
    line-height: 1.2;
}

.demo .vuecal__header .john {
    color: rgb(75, 141, 248);
}

.demo .vuecal__body .john {
    background-color: rgba(75, 141, 248, 0.08);
}

.demo .john .vuecal__event {
    background-color: rgb(75, 141, 248);
    color: #fff;
    border: 0.5px solid #ffffff;
    border-radius: 4px;
}


.demo .vuecal__header .kate {
    color: rgb(51, 65, 85);
}

.demo .vuecal__body .kate {
    background-color: rgba(51, 65, 85, 0.08);
}

.demo .kate .vuecal__event {
    /*background-color: rgb(51, 65, 85);*/
    color: #fff;
    border: 0.5px solid #ffffff;
    border-radius: 4px;
}


@media screen and (max-width: 499px) {
    .main-demo .day-split-header strong {
        display: none;
    }
}
</style>


<div id="app">
    <vue-cal class="demo full-cal vuecal--full-height-delete" style="height: 700px;" :disable-views="['years']" :selected-date="selectedDate" :show-all-day-events="true" active-view="day" :selected-date="selectedDate" :time-from="6 * 60" :time-to="22 * 60" :editable-events="editable" :split-days="splits" sticky-split-labels="sticky-split-labels" @ready="initCalendar" @view-change="initCalendar" :events="events" @event-drag-create="onEventCreate" @event-drop="onEventDrop" @event-duration-change="onEventResize" :on-event-click="onEventClick" @cell-focus="selectedDate = $event.date || $event" :snap-to-time="30"><template #split-label="{ split, view }">
            <strong :style="`color: ${split.color}`">{{ split.label }}</strong>
        </template>
        <template #event="{ event, view }">
            <div class="event-content" :style="{ backgroundColor: event.backgroundColor }" style="height: 100%; color: #ffffff;">
                <div class="vuecal__event-title" v-html="event.title"></div>
                {{ event.start.formatTime() }} - {{ event.end.formatTime() }}
            </div>
        </template>
    </vue-cal>
</div>


<script>
new Vue({
    el: '#app',
    components: {
        vueCal: vuecal
    },
    data() {
        return {
            //TODO in base a parametro group by
            splits: [],
            editable: {
                title: false,
                drag: true,
                resize: true,
                create: true,
                delete: false
            },
            selectedDate: new Date(), //default calendar date as today
            loadingCalendar: true, //flag to show / hide spinner
            events: [],
            /*sourceUrl: "<?php //echo base_url("get_ajax/get_calendar_events/{$data['calendars']['calendars_id']}" . $element_id);
?> ";*/
            sourceUrl: "<?php echo base_url("get_ajax/get_calendar_events/{$calendarId}"); ?>",
            calendars_group_by: "<?php echo ($calendar_map['group_by']) ?? ''; ?>",
            options: null,
        }
    },
    methods: {
        // onClicCell(event) {
        //     return this.onEventCreate(event);
        //     //alert(1);
        // },
        //TODO: oncreate?
        /**
         * ! Call on event creation
         */
        onEventCreate(event, deleteEventFunction) {
            //console.log(event);
            const self = this;

            <?php if (!empty($data['create_form']) && $data['calendars']['calendars_allow_create'] == DB_BOOL_TRUE): ?>
            var fStart = moment(event.start).format('DD/MM/YYYY HH:mm'); // formatted start
            var fEnd = moment(event.end).format('DD/MM/YYYY HH:mm'); // formatted end




            var fDateStart = moment(event.start).format('DD/MM/YYYY'); // formatted date start
            var fDateEnd = moment(event.end).format('DD/MM/YYYY'); // formatted date end

            var fTimeStart = moment(event.start).format('HH:mm'); // formatted date start
            var fTimeEnd = moment(event.end).format('HH:mm'); // formatted date end

            var allDay = event.allDay;

            var data = {
                [token_name]: token_hash,
                <?php echo $imploded_config; ?>
            };
            //console.log(data)
            loadModal(<?php echo json_encode(base_url("get_ajax/modal_form/{$data['create_form']}")); ?>, data, function() {


                self.initCalendar(self.options);
            }, 'get');
            <?php endif;?>
            //console.log(event);
            //event.class = "hide hidden";
            //alert(1);

            return event;

            // You can modify event here and return it.
            // You can also return false to reject the event creation.
            //return event;
        },

        /**
         * ! Call on event click
         */
        onEventClick(event, e) {
            //console.log(event);
            const self = this;
            // Prevent navigating to narrower view (default vue-cal behavior).
            e.stopPropagation();
            //Open modal form
            <?php if (!empty($data['update_form']) && $data['calendars']['calendars_allow_edit'] == DB_BOOL_TRUE): ?>
            loadModal(<?php echo json_encode(base_url("get_ajax/modal_form/{$data['update_form']}")); ?> + '/' + event.id, {}, function() {
                self.initCalendar(self.options);
            });
            <?php endif;?>
            return false;
        },
        /**
         * ! Update event on event drop and event resize
         */
        async updateEvent(event) {
            //console.log(event);
            const eventId = event.id;
            const start = moment(event.start).format('YYYY-MM-DD HH:mm:ss');
            const end = moment(event.end).format('YYYY-MM-DD HH:mm:ss');
            var fDateStart = moment(event.start).format('DD/MM/YYYY'); // formatted date start
            var fDateEnd = moment(event.end).format('DD/MM/YYYY'); // formatted date end

            var fTimeStart = moment(event.start).format('HH:mm'); // formatted date start
            var fTimeEnd = moment(event.end).format('HH:mm'); // formatted date end


            var allday = 0;
            if(event.allDay === true){
                allday == 1;
            }
            const allDay = allday;
            console.log(`Updating event with id # ${eventId}, start: ${start} - end: ${end}`);
            const formData = new FormData();
            formData.append([token_name], token_hash);
            formData.append("<?php echo $calendar_map['id']; ?>", eventId);
            <?php if (!empty($calendar_map['start'])): ?>
            formData.append("<?php echo $calendar_map['start']; ?>", start);
            <?php endif;?>
            <?php if (!empty($calendar_map['end'])): ?>
                formData.append("<?php echo $calendar_map['end']; ?>", end);
            <?php endif;?>
            <?php if (!empty($calendar_map['all_day'])): ?>
            formData.append("<?php echo $calendar_map['all_day']; ?>", allDay);
            <?php endif;?>
            <?php if (!empty($calendar_map['date_start'])): ?>
            formData.append("<?php echo $calendar_map['date_start']; ?>", fDateStart);
            <?php endif;?>
            <?php if (!empty($calendar_map['date_end'])): ?>
            formData.append("<?php echo $calendar_map['date_end']; ?>", fDateEnd);
            <?php endif;?>
            <?php if (!empty($calendar_map['hours_start'])): ?>
            formData.append("<?php echo $calendar_map['hours_start']; ?>", fTimeStart);
            <?php endif;?>
            <?php if (!empty($calendar_map['hours_end'])): ?>
            formData.append("<?php echo $calendar_map['hours_end']; ?>", fTimeEnd);
            <?php endif;?>

            /**
             * TODO: fare mappatura per avere i campi effettivi da aggiornare con i valori appena estratti
             */

            try {
                //const response = await axios.post("<?php //echo base_url("db_ajax/update_calendar_event/{$data['calendars']['calendars_id']}"); ?>", formData);
                const response = await axios.post("<?php echo base_url("db_ajax/update_calendar_event/{$calendarId}"); ?>", formData);
                console.log(response)
                if (parseInt(response.status) < 1) {
                    alert(response.data.txt);
                }
            } catch (error) {
                console.log(error);
                alert('There was an error while saving the event');
            }

        },
        /**
         * ! Call when an event is dropped
         */
        onEventDrop({
            event,
            originalEvent,
            external
        }) {
            //console.log(event); //updated event
            //console.log(originalEvent); //original event
            <?php if (!empty($data['update_form']) && $data['calendars']['calendars_allow_edit'] == DB_BOOL_TRUE): ?>
            this.updateEvent(event);
            <?php endif;?>
        },
        /**
         * ! Call when an event is resized
         */
        onEventResize({
            event,
            originalEvent,
            oldData
        }) {
            //console.log(event); //updated event
            //console.log(originalEvent); //original event
            //console.log(oldData); //JS Date the event was ending at before resize
            <?php if (!empty($data['update_form']) && $data['calendars']['calendars_allow_edit'] == DB_BOOL_TRUE): ?>
            this.updateEvent(event);
            <?php endif;?>
        },
        /**
         * ! Loads calendar events
         */
        async initCalendar({
            view,
            startDate,
            endDate,
            week
        }) {
            this.options = {
                view,
                startDate,
                endDate,
                week
            };
            //console.log(options);
            //alert(2);
            const self = this;
            this.loadingCalendar = true;

            const formData = new FormData();
            formData.append([token_name], token_hash);
            formData.append("start", moment(startDate).format('YYYY-MM-DD HH:mm'));
            formData.append("end", moment(endDate).format('YYYY-MM-DD HH:mm'));

            try {
                const response = await axios.post(`${self.sourceUrl}`, formData);
                if (response.status === 200) {
                    const data = response.data;

                    //If group_by specified, use it to build splits columns for daily view
                    var _splits = [];
                    /*
[{
//         label: 'John',
//         class: 'john'
//     }, {
//         label: 'Kate',
//         class: 'kate'
//     }]
                    */


                    if (data.length > 0) {

                        self.events = [...data];

                        self.events.forEach((element, index) => {
                            //console.log(element)

                            element.start = moment(element.start).format('YYYY-MM-DD HH:mm');
                            element.end = moment(element.end).format('YYYY-MM-DD HH:mm');
                            if (element.color) {
                                element.backgroundColor = element.color;
                            } else {
                                element.backgroundColor = 'rgb(51, 65, 85)';
                            }

                            if (self.calendars_group_by != '' && view == 'day') {
                                var column = {
                                    label: element.group_by,
                                    class: 'kate', //TODO...
                                };

                                var exists = _splits.findIndex(el => el.label === column.label);

                                if (exists == -1) {
                                    _splits.push(column);
                                }
                                exists = _splits.findIndex(el => el.label === column.label);
                                element.split = exists + 1;

                                // console.log(column);
                                // console . log(_splits);
                                // alert(1);


                            }

                        });
                    }
                    self.splits = _splits;
                    console.log(self.events);
                }
            } catch (error) {
                console.log(error);
                this.loadingCalendar = false;
            }
        },
    },
    mounted() {
        //this.initCalendar();
    },
});
</script>