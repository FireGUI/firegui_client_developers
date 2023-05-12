<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

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
    
    private function prepareData($grid_id = null, $value_id = null)
    {
        //prendo tutti i dati della grid (filtri compresi) e li metto in un array associativo, pronto per essere esportato
        $grid = $this->datab->get_grid($grid_id);
        
        $grid_data = $this->datab->get_grid_data($grid, $value_id, '', null, 0, null);
        $out_array = [];
        foreach ($grid_data as $dato) {
            $tr = [];
            
            foreach ($grid['grids_fields'] as $field) {
                $tr[] = trim(strip_tags($this->datab->build_grid_cell($field, $dato, false, true, true)));
                
            }
            
            $out_array[] = $tr;
        }
        
        $columns_names = [];
        
        //Rimpiazzo i nomi delle colonne
        foreach ($grid['grids_fields'] as $key => $field) {
            $columns_names[$key . $field['fields_name']] = $field['grids_fields_column_name'];
        }
        
        array_walk($out_array, function ($value, $key) use ($columns_names, &$out_array) {
            $out_array[$key] = array_combine($columns_names, $value);
        });
        
        return $out_array;
    }
    
    public function download_csv($grid_id, $value_id = null)
    {
        error_reporting(0);
        ini_set('display_errors', false);
        ini_set('display_startup_errors', false);
        setlocale(LC_MONETARY, 'it_IT');
        
        $data = $this->prepareData($grid_id, $value_id);
        $csv = $this->arrayToCsv($data, ',');
        
        $filename = "grid{$grid_id}";
        
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
    
    public function download_pdf($grid_id, $value_id = null)
    {
        error_reporting(0);
        ini_set('display_errors', false);
        ini_set('display_startup_errors', false);
        setlocale(LC_MONETARY, 'it_IT');
        
        $this->load->library('table');
        
        $template = [
            'table_open' => '<table class="table table-condensed table-bordered table-striped export_pdf_table">',
            
            'thead_open' => '<thead class="export_pdf_thead">',
            
            'heading_row_start' => '<tr class="export_pdf_thead_row">',
            'heading_cell_start' => '<th class="export_pdf_thead_col">',
            
            'tbody_open' => '<tbody class="export_pdf_tbody">',
            
            'row_start' => '<tr class="export_pdf_tbody_row">',
            'cell_start' => '<td class="export_pdf_tbody_col">',
        ];
        
        $this->table->set_template($template);
        
        $data = $this->prepareData($grid_id, $value_id);
        $header = array_unique(array_merge(...array_map('array_keys', $data)));
        
        $tpl_folder = $this->db->join('settings_template', 'settings_template_id = settings_template', 'LEFT')->get('settings')->row()->settings_template_folder;
        
        $this->table->set_heading($header);
        foreach ($data as $row) {
            $this->table->add_row(array_values($row));
        }
        
        $html_table = $this->table->generate();
        
        if (file_exists(APPPATH . 'views/custom/layout/pdf_custom.php')) {
            $html = $this->load->view('custom/layout/pdf_custom', ['html' => $html_table], true);
        } elseif (file_exists(APPPATH . 'views/' . $tpl_folder . '/layout/pdf_custom.php')) {
            $html = $this->load->view($tpl_folder . '/layout/pdf_custom', ['html' => $html_table], true);
        } else {
            $html = $this->load->view('base/layout/pdf_custom', ['html' => $html_table], true);
        }
        
        if ($this->input->get('html') == '1') {
            die($html);
        }
        
        $pdf = $this->layout->generate_pdf($html, ($this->input->get('orientation') == 'landscape' ? 'landscape' : 'portrait'), '', [], false, true);
        
        $filename = "grid{$grid_id}";
        
        if (!empty($this->input->get('filename'))) {
            $filename = $this->input->get('filename');
            $filename = ucfirst($filename);
            $filename = str_ireplace([' ', '-'], '_', $filename);
        }
        
        $fp = fopen($pdf, 'rb');
        
        header('Content-Type: application/pdf');
        header('Content-Length: ' . filesize($pdf));
        header('Content-disposition: ' . ($this->input->get('preview') !== null ? 'inline' : 'attachment') . "; filename=\"{$filename}.pdf\"");
        
        fpassthru($fp);
    }
    
    public function download_excel($grid_id, $value_id = null)
    {
        error_reporting(0);
        ini_set('display_errors', false);
        ini_set('display_startup_errors', false);
        setlocale(LC_MONETARY, 'it_IT');
        
        $grid = $this->datab->get_grid($grid_id);
        $fields = $grid['grids_fields'];
        
        $data = $this->prepareData($grid_id, $value_id);
        $objPHPExcel = new Spreadsheet();
        
        $objPHPExcel->getActiveSheet()->fromArray(array_keys($data[0]), '', 'A1');
        
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
                    } elseif ((float)$possible_number !== (float)$numberize) {
                        $numeric_cells = array_diff($numeric_cells, [$column]);
                    }
                }
            }
        }
        /*dump($numeric_cells);
        exit;*/
        $cells = array_merge($cells, $numeric_cells);
        
        foreach ($data as $key => $dato) {
            foreach ($cells as $col_pos => $cell) {
                if (ctype_digit($dato[$cell])) {
                    $data[$key][$cell] = (float)tofloat($dato[$cell]);
                } else {
                    $data[$key][$cell] = $dato[$cell];
                }
            }
        }
        $objPHPExcel->getActiveSheet()->fromArray($data, '', 'A2');
        
        $filename = "grid{$grid_id}";
        
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
    
    
    private function arrayToCsv(array $data, $delim = ',', $enclosure = '"')
    {
        if (!$data) {
            return '';
        }
        
        // Apri un nuovo file, mi serve per avere un handler per usare
        // nativamente fputcsv che fa gli escape corretti
        $tmp = tmpfile() or show_error('Impossibile creare file temporaneo');
        
        $keys = array_keys(array_values($data)[0]);
        fputcsv($tmp, $keys, $delim, $enclosure);
        
        
        foreach ($data as $row) {
            if (fputcsv($tmp, $row, $delim, $enclosure) === false) {
                show_error('Impossibile scrivere sul file temporaneo');
            }
        }
        
        // Chiudendo il file qua, lo eliminerei completamente, quindi lo leggo
        // per intero e lo muovo in filedata. fseek mi serve perché in questo
        // momento il puntatore si trova alla fine del file e devo resettarlo
        $filedata = '';
        fseek($tmp, 0);
        
        do {
            $buffer = fread($tmp, 8192);
            if ($buffer === false) {
                show_error('Non è stato possibile leggere');
            }
            $filedata .= $buffer;
        } while (strlen($buffer) > 0);
        
        fclose($tmp);   // Rilascia risorsa
        return $filedata;
    }
}
