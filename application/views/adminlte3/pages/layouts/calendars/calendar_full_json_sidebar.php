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

$element_id = (isset($value_id) ? $value_id : NULL);
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



?>

<style>
    .scrollable {
        max-height: 600px;
        overflow-y: scroll;
    }
</style>
<div class="row">
    <div class="col-lg-2 col-md-3">
        <?php if ($data['calendars']['calendars_filter_entity_id']) : ?>
            <?php $entity = $this->datab->get_entity($data['calendars']['calendars_filter_entity_id']); ?>
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
            <div class="scrollable">
                <?php foreach ($filter_data as $id => $nome) : ?>
                    <?php if ($detailsLink) : ?>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="cal_filter[]" class="js_check_filter" value="<?php echo $id; ?>" <?php if ($entity['entity_name'] == LOGIN_ENTITY && $id == $this->auth->get(LOGIN_ENTITY . "_id")) echo 'checked'; ?> />
                                <?php echo anchor($detailsLink . '/' . $id, $nome, ['data-toggle' => 'tooltip', 'title' => 'Visualizza ' . $nome]); ?>
                            </label>
                        </div>
                    <?php else : ?>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="cal_filter[]" class="js_check_filter" value="<?php echo $id; ?>" <?php if ($entity['entity_name'] == LOGIN_ENTITY && $id == $this->auth->get(LOGIN_ENTITY . "_id")) echo 'checked'; ?> />
                                <?php echo $nome; ?>
                            </label>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="calendar_custom_area"></div>
    </div>

    <div class="col-lg-10 col-md-9">
        <div <?php echo sprintf('id="%s"', $calendarId); ?> class="has-toolbar calendar_full_json_sidebar" data-sourceurl="<?php echo base_url("get_ajax/get_calendar_events/{$data['calendars']['calendars_id']}/{$element_id}"); ?>" data-mintime="<?php echo (array_get($data['calendars'], 'calendars_min_time') ?: '06:00:00'); ?>" data-maxtime="<?php echo (array_get($data['calendars'], 'calendars_max_time') ?: '22:00:00'); ?>" data-language="<?php echo (!empty($settings['languages_code'])) ? (explode('-', $settings['languages_code'])[0]) : 'en'; ?>" data-start="<?php echo ($calendar_map['start']); ?>" data-end="<?php echo ($calendar_map['end']); ?>" data-allday="<?php echo $calendar_map['all_day']; ?>" data-formurl="<?php echo base_url("get_ajax/modal_form/{$data['create_form']}"); ?>" data-formedit="<?php echo base_url("get_ajax/modal_form/{$data['update_form']}"); ?>" data-updateurl="<?php echo base_url("db_ajax/update_calendar_event/{$data['calendars']['calendars_id']}"); ?>" data-fieldid="<?php echo $calendar_map['id']; ?>">
        </div>
    </div>
</div>