<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as SharedDateHelper;

class Export extends MY_Controller
{
    
    function __construct()
    {
        parent::__construct();
        
        if ($this->auth->guest()) {
            set_status_header(401); // Unauthorized
            die('Non sei loggato nel sistema');
        }
        
        // Qualunque chiamata alle apilib da qua dentro è considerata una
        // chiamata in modalità CRM_FORM
        $this->apilib->setProcessingMode(Apilib::MODE_CRM_FORM);
    }
    
    
    
    public function download_csv($grid_id, $value_id = null)
    {
        error_reporting(0);
        ini_set('display_errors', false);
        ini_set('display_startup_errors', false);
        setlocale(LC_MONETARY, 'it_IT');
        
        if (!is_numeric($grid_id)) {
            $grid_id = $this->datab->get_grid_id_by_identifier($grid_id);
        }
        
        $grids_ajax_params = $this->session->userdata('grids_ajax_params');
        if (!empty($grids_ajax_params[$grid_id])) {
            $params = $grids_ajax_params[$grid_id];
        } else {
            $params = [];
        }
        
        $data = $this->datab->prepareData($grid_id, $value_id, $params);
        
        if (empty($data)) {
            e('No data to export.');  // Display an error message if there is no data to export
            return false;
        }
        
        $csv = $this->datab->arrayToCsv($data, ',');
        
        $filename = t('Export table') . " #{$grid_id}";
        
        if (!empty($this->input->get('filename'))) {
            $filename = $this->input->get('filename');
            $filename = ucfirst($filename);
            $filename = str_ireplace([' ', '-'], '_', $filename);
        }
        
        header('Content-Type: text/csv');
        header('Content-Transfer-Encoding: Binary');
        header("Content-disposition: attachment; filename=\"{$filename}.csv\"");
        echo $csv;
        exit;
    }
    public function download_layout($layout_id)
    {
        //TODO!!!!
    }
    /**
     * Download PDF
     *
     * Downloads a PDF file generated from a table with the specified grid ID and optional value ID.
     *
     * @param int      $grid_id  The ID of the grid.
     * @param int|null $value_id The ID of the value (optional).
     *
     * @return void
     *
     * @uses $_GET['html']        (string)  If set to '1', it displays the generated HTML instead of generating a PDF.
     * @uses $_GET['orientation'] (string)  Specifies the orientation of the PDF ('landscape' or 'portrait').
     * @uses $_GET['filename']    (string)  Specifies the filename for the downloaded PDF. If empty, a default filename is used.
     * @uses $_GET['preview']     (string)  If set, the PDF is displayed inline; otherwise, it is downloaded as an attachment.
     */
    public function download_pdf($grid_id, $value_id = null)
    {
        error_reporting(0);  // Disable error reporting
        ini_set('display_errors', false);
        ini_set('display_startup_errors', false);
        setlocale(LC_MONETARY, 'it_IT');  // Set the locale for monetary formatting
        
        if (!is_numeric($grid_id)) {
            $grid_id = $this->datab->get_grid_id_by_identifier($grid_id);
        }
        
        $this->load->library('table');  // Load the table library
        
        // Set the table base template with css classes
        $template = [
            'table_open' => '<table class="table table-condensed table-bordered table-striped export_pdf_table">',
            
            'thead_open' => '<thead class="export_pdf_thead">',
            
            'heading_row_start' => '<tr class="export_pdf_thead_row">',
            'heading_cell_start' => '<th class="export_pdf_thead_col">',
            
            'tbody_open' => '<tbody class="export_pdf_tbody">',
            
            'row_start' => '<tr class="export_pdf_tbody_row">',
            'cell_start' => '<td class="export_pdf_tbody_col">',
        ];
        
        $this->table->set_template($template);  // Set the template for the table
        
        $grids_ajax_params = $this->session->userdata('grids_ajax_params');
        if (!empty($grids_ajax_params[$grid_id])) {
            $params = $grids_ajax_params[$grid_id];
        } else {
            $params = [];
        }
        
        $grid = $this->datab->get_grid($grid_id);
        
        // debug($grid, true);
        
        $filtri_html = '';
        if (!empty($grid['grids']['grids_filter_session_key'])) {
            $filtri_applicati = $this->session->userdata(SESS_WHERE_DATA)[$grid['grids']['grids_filter_session_key']] ?? [];
            $form_filtro = $this->db
                ->where('forms_entity_id', $grid['grids']['grids_entity_id'])
                ->where('forms_layout', 'filter_select')
                ->where('forms_filter_session_key', $grid['grids']['grids_filter_session_key'])
                ->get('forms')->row_array();
            
            $form_fields = $this->db
                ->where('forms_fields_forms_id', $form_filtro['forms_id'])
                ->get('forms_fields')->result_array();
            
            // assegno alla variabile form_filtro un array con mappatura forms_fields_fields_id => $form_field per avere un array associativo dei fields in base al loro id
            $form_filtro['_fields'] = [];
            foreach ($form_fields as $form_field) {
                $form_filtro['_fields'][$form_field['forms_fields_fields_id']] = $form_field;
            }
            
            // Costruisco uno specchietto di filtri autogenerati leggibile
            $filtri = [];
            
            if (!empty($filtri_applicati)) {
                foreach ($filtri_applicati as $field) {
                    if ($field['value'] == '-1') {
                        continue;
                    }
                    
                    $filter_field = $this->datab->get_field($field["field_id"], true);
                    
                    // calcolo il campo label in base al campo forms_fields_override_label dentro $form_filtro -> _fields se valorizzata, altrimenti vado sul campo fields_draw_label.
                    $label = $form_filtro['_fields'][$field['field_id']]['forms_fields_override_label'] ?? t($filter_field["fields_draw_label"]);
                    
                    // Se ha una entità/support collegata
                    if ($filter_field['fields_ref']) {
                        $entity_data = $this->crmentity->getEntityPreview($filter_field['fields_ref']);
                        
                        $filtri[] = array(
                            "label" => $label,
                            "value" => $entity_data[$field['value']],
                            'raw_data_value' => $field['value'],
                        );
                    } else {
                        $filtri[] = array("label" => $label, "value" => $field['value']);
                    }
                }
            }
            
            if (!empty($filtri)) {
                $filtri = array_map(function ($item) {
                    if (!empty($item['value'])) {
                        return '<div class="col-sm-4"><b>' . $item['label'] . '</b>: ' . $item['value'] . '</div>';
                    }
                }, $filtri);
                
                $filtri_html = '<div class="export_pdf_filtri row"><div class="col-sm-12"><b>'.t('FILTERS APPLIED').':</b></div><br/>' . implode('', $filtri) . '</div>';
            }
        }
        
        $data = $this->datab->prepareData($grid_id, $value_id, $params, false);  // Prepare the data for the table
        
        if (empty($data)) {
            e('No data to export.');  // Display an error message if there is no data to export
            return false;
        }
        
        $header = array_unique(array_merge(...array_map('array_keys', $data)));  // Get unique headers from the data
        
        if (!empty($this->input->get('show_line_number')) && $this->input->get('show_line_number') == '1') {
            array_unshift($header, '#');  // Add an empty header for row numbers
        }
        
        $tpl_folder = $this->db->join('settings_template', 'settings_template_id = settings_template', 'LEFT')->get('settings')->row()->settings_template_folder;  // Get the template folder
        
        $this->table->set_heading($header);  // Set the table heading
        $footer_totals = [];
        foreach ($data as $index => $row) {
            foreach (array_values($row) as $key => $value) {
                if ($grid['grids_fields'][$key]['grids_fields_totalable']) {
                    // verifico se c'è un "data-totalablevalue" se c'è un elemento come '<span data-totalablevalue="0">0</span>', prendo quel valore, altrimenti faccio strip tags del value e verifico poi che sia is_numeric
                    $value = preg_match('/data-totalablevalue="([^"]+)"/', $value, $matches) ? $matches[1] : strip_tags($value);
                    
                    if (is_numeric($value)) {
                        $footer_totals[$key] += $value;
                    } else {
                        $footer_totals[$key] = '';
                    }
                } else {
                    $footer_totals[$key] = '';
                }
            }
            
            if (!empty($this->input->get('show_line_number')) && $this->input->get('show_line_number') == '1') {
                $n_row = $index + 1;
                
                array_unshift($row, $n_row);  // Add row numbers to the beginning of each row
            }
            
            $this->table->add_row(array_values($row));  // Add rows to the table
        }
        
        if (!empty($footer_totals)){
            if (!empty($this->input->get('show_line_number')) && $this->input->get('show_line_number') == '1') {
                array_unshift($footer_totals, '');
            }
            
            $this->table->add_row(array_values($footer_totals));
        }
        
        $html_content = '';  // Initialize the HTML variable
        
        if (!empty($filtri_html)) {
            $html_content .= $filtri_html . '<br/>';  // Add the filters HTML to the HTML variable
        }
        
        $html_content .= $this->table->generate();  // Generate the HTML table
        
        if (file_exists(APPPATH . 'views/custom/layout/pdf_custom.php')) {
            $html = $this->load->view('custom/layout/pdf_custom', ['html' => $html_content], true);  // Load custom layout if available
        } elseif (file_exists(APPPATH . 'views/' . $tpl_folder . '/layout/pdf_custom.php')) {
            $html = $this->load->view($tpl_folder . '/layout/pdf_custom', ['html' => $html_content], true);  // Load template-specific layout if available
        } else {
            $html = $this->load->view('base/layout/pdf_custom', ['html' => $html_content], true);  // Load default layout
        }
        
        if ($this->input->get('html') == '1') { // Display the generated HTML if the 'html' GET parameter is set to '1'
            die($html);
        }
        
        $pdf = $this->layout->generate_pdf($html, ($this->input->get('orientation') == 'landscape' ? 'landscape' : 'portrait'), '', [], false, true);  // Generate the PDF using the specified orientation
        
        $filename = t('Export table') . " #{$grid_id}";  // Set the default filename
        
        if (!empty($this->input->get('filename'))) {
            $filename = $this->input->get('filename');  // Use the specified filename if provided
            $filename = ucfirst($filename);  // Capitalize the filename
            $filename = str_ireplace([' ', '-'], '_', $filename);  // Replace spaces and dashes with underscores
        }
        
        $fp = fopen($pdf, 'rb');  // Open the PDF file in binary mode
        
        header('Content-Type: application/pdf');  // Set the content type header to indicate PDF
        header('Content-Length: ' . filesize($pdf));  // Set the content length header
        header('Content-disposition: ' . ($this->input->get('preview') !== null ? 'inline' : 'attachment') . "; filename=\"{$filename}.pdf\"");  // Set the content disposition header to specify inline or attachment
        
        fpassthru($fp);  // Output the PDF file
    }
    
    private function numeroToLettere($numero)
    {
        $lettere = '';
        
        while ($numero >= 0) {
            $resto = $numero % 26;
            $lettere = chr(65 + $resto) . $lettere;
            $numero = intval($numero / 26) - 1;
        }
        
        return $lettere;
    }
    
    public function download_excel($grid_id, $value_id = null)
    {
        error_reporting(0);
        ini_set('display_errors', false);
        ini_set('display_startup_errors', false);
        setlocale(LC_MONETARY, 'it_IT');
        
        if (!is_numeric($grid_id)) {
            $grid_id = $this->datab->get_grid_id_by_identifier($grid_id);
        }
        
        $grids_ajax_params = $this->session->userdata('grids_ajax_params');
        if (!empty($grids_ajax_params[$grid_id])) {
            $params = $grids_ajax_params[$grid_id];
        } else {
            $params = [];
        }
        
        $grid = $this->datab->get_grid($grid_id);
        $fields = $grid['grids_fields'];
        
        $data = $this->datab->prepareData($grid_id, $value_id, $params);
        
        if (empty($data)) {
            e('No data to export.');  // Display an error message if there is no data to export
            return false;
        }
        
        $objPHPExcel = new Spreadsheet();
        
        $header = array_keys($data[0]);
        
        if (!empty($this->input->get('show_line_number')) && $this->input->get('show_line_number') == '1') {
            array_unshift($header, '#');  // Add an empty header for row numbers
        }
        
        $objPHPExcel->getActiveSheet()->fromArray($header, '', 'A1');
        
        $numeric_cells = [];
        $cells = [];
        foreach ($fields as $key => $field) {
            if ($field['grids_fields_totalable']) {
                $numeric_cells[$key] = $field['grids_fields_column_name'];
            } else {
                $cells[$key] = $field['grids_fields_column_name'];
            }
        }
        /*dump($cells);
        exit;*/
        if (!empty($numeric_cells)) {
            foreach (array_slice($data, 0, 10) as $key => $dato) {
                foreach ($dato as $column => $value) {
                    if (!in_array($column, $numeric_cells)) { // If is already in numeric array, skip
                        continue;
                    }
                    
                    $numberize = preg_replace('/[^0-9.]/', '', $value);
                    
                    // Try casing as number type, else remove it.
                    $possible_number = filter_var($numberize, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_THOUSAND | FILTER_FLAG_ALLOW_FRACTION);
                    
                    if ($possible_number === '') {
                        $numeric_cells = array_diff($numeric_cells, [$column]);
                    } elseif ((float) $possible_number !== (float) $numberize) {
                        $numeric_cells = array_diff($numeric_cells, [$column]);
                    }
                }
            }
        }
        /*dump($numeric_cells);
        exit;*/
        $cells = array_merge($cells, $numeric_cells);
        /* In questo modo le chiavi non vengono mantenute ma le numeriche saranno alla fine dell'array cells. Con la riga qua sotto, invece, tiene le chiavi. */
        /*$cells = $cells + $numeric_cells;
        ksort($cells);*/
        $cells_date_type = [];
        foreach ($data as $key => $dato) {
            foreach ($cells as $col_pos => $cell) {
                if (ctype_digit($dato[$cell])) {
                    $data[$key][$cell] = (float) tofloat($dato[$cell]);
                } else {
                    // Controlla se il valore è una data e formattalo
                    $isdmyDate = DateTime::createFromFormat('d/m/Y', $dato[$cell]);
                    
                    if ($isdmyDate !== false) {
                        $data[$key][$cell] = $isdmyDate->format('Y-m-d');
                    }
                    
                    // michael - 20/02/2024 - disattivo questa parte in quanto crea problemi nel caso di celle con slash
                    //                    if ((substr_count($dato[$cell], '/') == 2)) {
                    //                        $expl = explode('/', $dato[$cell]);
                    //                        $dato[$cell] = "{$expl['2']}-{$expl['1']}-{$expl['0']}";
                    //                    }
                    ///////////////////
                    /* Per decodificare correttamente le date, se un cliente aveva due // nell'indirizzo, veniva interpretato come data */
                    /*$dateObject = DateTime::createFromFormat('d/m/Y', $dato[$cell]);
                    if ($dateObject !== false && $dateObject->format('d/m/Y') == $dato[$cell]) {
                        $formattedDate = $dateObject->format('Y-m-d');
                        $dato[$cell] = $formattedDate;
                    }*/
                    
                    if (substr_count($dato[$cell], '-') == 2 && strtotime($dato[$cell])) {
                        // $dato[$cell] contiene la data in formato stringa, ad esempio '2023-11-20'
                        $cells_date_type[] = $cell;
                        $dataAsString = $dato[$cell];
                        
                        
                        // Converti la data in formato stringa in un oggetto DateTime
                        $dateTime = new DateTime($dataAsString);
                        
                        // Utilizza SharedDateHelper per convertire l'oggetto DateTime in un numero di serie Excel
                        $excelDate = SharedDateHelper::dateTimeToExcel($dateTime);
                        // debug($dataAsString);
                        if (substr($dataAsString, 0, 10) == '2023-11-03') {
                            //debug($dateTime, true);
                        }
                        // Assegna il numero di serie Excel alla cella
                        $data[$key][$cell] = $excelDate;
                        //debug($data[$key][$cell],true);
                    } else {
                        $data[$key][$cell] = $dato[$cell];
                    }
                }
            }
            
            if (!empty($this->input->get('show_line_number')) && $this->input->get('show_line_number') == '1') {
                array_unshift($data[$key], $key + 1);  // Add row numbers to the beginning of each row
            }
        }
        
        // dd($cells, $data);
        
        // Dopo aver convertito le date e prima di scrivere i dati nel foglio di calcolo
        $columnIndex = 0; // Inizia dall'indice 0 per la prima colonna (A)
        foreach ($cells as $col_pos => $cell) {
            $isDateColumn = in_array($cell, $cells_date_type);
            
            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(++$columnIndex);
            
            // Controlla se la colonna contiene date
            if ($isDateColumn) {
                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);
                //debug($columnLetter,true);
                $objPHPExcel->getActiveSheet()->getStyle($columnLetter)->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_YYYYMMDD2);
                $objPHPExcel->getActiveSheet()->getStyle($columnLetter)->getAlignment()->setHorizontal('right');
            }
        }
        
        $objPHPExcel->getActiveSheet()->fromArray($data, '', 'A2');
        
        $filename = t('Export table') . " #{$grid_id}";
        
        if (!empty($this->input->get('filename'))) {
            $filename = $this->input->get('filename');
            $filename = ucfirst($filename);
            $filename = str_ireplace([' ', '-'], '_', $filename);
        }
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"{$filename}.xlsx\"");
        header('Cache-Control: max-age=0');
        
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
        
        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
        
        $objWriter = IOFactory::createWriter($objPHPExcel, 'Xlsx');
        $objWriter->setPreCalculateFormulas(true);
        $objWriter->save('php://output');
    }
}
