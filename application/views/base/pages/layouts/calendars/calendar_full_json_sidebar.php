<?php

//calendars_where_filter

// Map the calendar fields with the entity fields
$calendar_map = [];
$filterWhere = $filterWhereFilter = null;
foreach ($data['calendars_fields'] as $field) {
    $calendar_map[$field['calendars_fields_type']] = $field['fields_name'];

    // Recupero il campo filtro per applicare la fields_select_where
    if ($field['calendars_fields_type'] == 'filter' && trim($field['fields_select_where'])) {
        $filterWhere = $this->datab->replace_superglobal_data($field['fields_select_where']);
    }
}

if (!isset($calendar_map['id']) || !$calendar_map['id']) {
    $calendar_map['id'] = $data['calendars']['entity_name'] . "_id";
}

$element_id = (isset($value_id) ? $value_id : null);
$calendarId = 'calendar' . $data['calendars']['calendars_id'];
$data['calendars']['calendars_where'] = trim($data['calendars']['calendars_where']);
if (!empty($data['calendars']['calendars_where'])) {
    $add_where = $this->datab->replace_superglobal_data($data['calendars']['calendars_where']);
    if (empty($filterWhere)) {
        $filterWhere = $add_where;
    } else {
        $filterWhere .= ' AND ' . $add_where;
    }
}

// TODO INSERITA CHIOCCIOLA PERCHé DAVA ERRORE E FORSE MANCA PROPRIO IL CAMPO calendars_where_filter SU DB !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

@$data['calendars']['calendars_where_filter'] = trim($data['calendars']['calendars_where_filter']);
if (!empty($data['calendars']['calendars_where_filter'])) {
    $add_where = $this->datab->replace_superglobal_data($data['calendars']['calendars_where_filter']);
    if (empty($filterWhereFilter)) {
        $filterWhereFilter = $add_where;
    } else {
        $filterWhereFilter .= ' AND ' . $add_where;
    }
}
$settings = $this->db->join('languages', 'languages_id = settings_default_language', 'LEFT')->get('settings')->row_array();
$create_permission = (!empty($data['create_form']) && $data['calendars']['calendars_allow_create'] == DB_BOOL_TRUE) ? DB_BOOL_TRUE : DB_BOOL_FALSE;
$edit_permission = (!empty($data['update_form']) && $data['calendars']['calendars_allow_edit'] == DB_BOOL_TRUE) ? DB_BOOL_TRUE : DB_BOOL_FALSE;
$calendars_default_view = (!empty($data['cal_layout']['calendars_default_view'])) ? $data['cal_layout']['calendars_default_view'] : 'timeGridWeek';
$filter_default_view = (!empty($data['cal_layout']) && $data['cal_layout']['calendars_default_sidebar_toggle_all_filters'] == DB_BOOL_TRUE) ? DB_BOOL_TRUE : DB_BOOL_FALSE;
$url_parameters = (!empty($data['create_form'])) ? $data['cal_layout']['calendars_link'] : '';

$attributes = [
    'id' => $calendarId,
    'data-calendar' => base64_encode(json_encode($data['calendars'])),
    'class' => 'has-toolbar calendar_full_json_sidebar',
    'data-view' => $calendars_default_view,
    'data-allow_create' => $create_permission,
    'data-allow_edit' => $edit_permission,
    'data-sourceurl' => base_url("get_ajax/get_calendar_events/{$data['calendars']['calendars_id']}/{$element_id}"),
    'data-mintime' => (array_get($data['calendars'], 'calendars_min_time') ?: '06:00:00'),
    'data-maxtime' => (array_get($data['calendars'], 'calendars_max_time') ?: '22:00:00'),
    'data-language' => (!empty($settings['languages_code'])) ? (explode('-', $settings['languages_code'])[0]) : 'en',
    'data-allday' => array_key_exists('all_day', $calendar_map) ? $calendar_map['all_day'] : false,
    'data-formurl' => base_url("get_ajax/modal_form/{$data['create_form']}"),
    'data-formedit' => base_url("get_ajax/modal_form/{$data['update_form']}"),
    'data-updateurl' => base_url("db_ajax/update_calendar_event/{$data['calendars']['calendars_id']}"),
    'data-fieldid' => $calendar_map['id'],
    'data-url-parameters' => $url_parameters
];

if (array_key_exists('start', $calendar_map)) {
    $attributes['data-start'] = $calendar_map['start'];
    $attributes['data-start-is-datetime'] = true;
} elseif (array_key_exists('date_start', $calendar_map)) {
    $attributes['data-start'] = $calendar_map['date_start'];
    $attributes['data-start-is-datetime'] = false;
    
    if (array_key_exists('hours_start', $calendar_map)) {
        $attributes['data-hours_start'] = $calendar_map['hours_start'];
    }
}

if (array_key_exists('end', $calendar_map)) {
    $attributes['data-end'] = $calendar_map['end'];
    $attributes['data-end-is-datetime'] = true;
} elseif (array_key_exists('date_end', $calendar_map)) {
    $attributes['data-end'] = $calendar_map['date_end'];
    $attributes['data-end-is-datetime'] = false;
    
    if (array_key_exists('hours_end', $calendar_map)) {
        $attributes['data-hours_end'] = $calendar_map['hours_end'];
    }
}

$attributesString = '';
foreach ($attributes as $key => $value) {
    $attributesString .= sprintf('%s="%s" ', $key, $value);
}

?>

<style>
    .scrollable {
        max-height: 600px;
        overflow-y: scroll;
    }
</style>
<style>
    .fc-scroller {
        height: 100% !important;
    }

    .total-label {
        font-size: 1.2rem;
        font-weight: bold;
    }
</style>
<div class="row js_calendar_sidemain_container">
    <div class="col-lg-2 col-md-3">
        <?php if ($data['calendars']['calendars_filter_entity_id']): ?>
            <?php $entity = $this->datab->get_entity($data['calendars']['calendars_filter_entity_id']);?>
            <h3><?php echo ucwords($entity['entity_name']); ?></h3>

            <?php
$main_entity = $data['calendars']['entity_name'];
$where = ($filterWhere) ? "WHERE $filterWhere" : '';
foreach ($data['calendars_fields'] as $_field) {
    if ($_field['calendars_fields_type'] == 'filter') {
        $field_filter = $_field['fields_name'];
    }
}
if (!empty($field_filter)) {
    if ($filterWhereFilter) {
        $filterWhereFilter .= "AND {$entity['entity_name']}_id IN (SELECT $field_filter FROM $main_entity $where)";
    } else {
        $filterWhereFilter = "{$entity['entity_name']}_id IN (SELECT $field_filter FROM $main_entity $where)";
    }
}
$filter_data = $this->datab->get_entity_preview_by_name($entity['entity_name'], $filterWhereFilter);

$detailsLink = $this->datab->get_detail_layout_link($data['calendars']['calendars_filter_entity_id']);

natcasesort($filter_data);
?>
            <div class="scrollable js_sidebar_filter_container">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="cal_filter[]" class="js_check_filter_all" id="select-all" <?php if ($filter_default_view == DB_BOOL_TRUE): ?>checked<?php endif;?> value="0"/>
                        <b><?php e('Select All');?></b>
                    </label>
                </div>
                <?php foreach ($filter_data as $id => $nome): ?>
                    <?php if ($detailsLink): ?>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="cal_filter[]" class="js_check_filter" value="<?php echo $id; ?>" <?php if (($entity['entity_name'] == LOGIN_ENTITY && $id == $this->auth->get(LOGIN_ENTITY . "_id")) or $filter_default_view == DB_BOOL_TRUE) {
    echo 'checked';
}
?> />
                                <?php echo anchor($detailsLink . '/' . $id, $nome, ['data-toggle' => 'tooltip', 'title' => 'Visualizza ' . $nome]); ?>
                            </label>
                        </div>
                    <?php else: ?>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="cal_filter[]" class="js_check_filter" value="<?php echo $id; ?>" <?php if ($entity['entity_name'] == LOGIN_ENTITY && $id == $this->auth->get(LOGIN_ENTITY . "_id")) {
    echo 'checked';
}
?> />
                                <?php echo $nome; ?>
                            </label>
                        </div>
                    <?php endif;?>
                <?php endforeach;?>
            </div>
        <?php endif;?>
        <div class="calendar_custom_area"></div>
    </div>

    <div class="col-lg-10 col-md-9">
        <div <?php echo $attributesString; ?>></div>
    </div>
</div>
