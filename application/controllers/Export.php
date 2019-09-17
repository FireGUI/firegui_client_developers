<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require_once APPPATH . 'third_party/PHPExcel.php';
class PHPExcel_Cell_MyColumnValueBinder extends PHPExcel_Cell_DefaultValueBinder implements PHPExcel_Cell_IValueBinder
{
    protected $stringColumns = [];

    public function __construct(array $stringColumnList = []) {
        // Accept a list of columns that will always be set as strings
        $this->stringColumns = $stringColumnList;
    }

    public function bindValue(PHPExcel_Cell $cell, $value = null)
    {
        // If the cell is one of our columns to set as a string...
        if (in_array($cell->getColumn(), $this->stringColumns) || $this->stringColumns == []) {
            // ... then we cast it to a string and explicitly set it as a string
            $cell->setValueExplicit((string) $value, PHPExcel_Cell_DataType::TYPE_STRING);
            return true;
        }
        // Otherwise, use the default behaviour
        return parent::bindValue($cell, $value);
    }
}

class Export extends MY_Controller {
    
    function __construct()
    {
        parent :: __construct();

        // Qualunque chiamata alle apilib da qua dentro è considerata una
        // chiamata in modalità CRM_FORM
        $this->apilib->setProcessingMode(Apilib::MODE_CRM_FORM);
    }
    
    private function prepareData($grid_id = null, $value_id = null) {
        //prendo tutti i dati della grid (filtri compresi) e li metto in un array associativo, pronto per essere esportato
        $grid = $this->datab->get_grid($grid_id);
        
        $grid_data = $this->datab->get_grid_data($grid, $value_id, '', NULL, 0, null);
                            //$this->get_grid_data($grid, empty($layoutEntityData) ? $value_id : ['value_id' => $value_id, 'additional_data' => $layoutEntityData]);
        $out_array = array();
        foreach ($grid_data as $dato) {
            //debug($dato,true);
            $tr = array();
            
            foreach ($grid['grids_fields'] as $field) {
                $tr[] = trim(strip_tags($this->datab->build_grid_cell($field, $dato, false)));
            }

            $out_array[] = $tr;
        }
        
        $columns_names = [];
        //Rimpiazzo i nomi delle colonne
        foreach ($grid['grids_fields'] as $key => $field) {
            $columns_names[$key.$field['fields_name']] = $field['grids_fields_column_name'];
            
        }
        
        //debug($columns_names,true);
        array_walk($out_array, function($value,$key) use ($columns_names, &$out_array){
//            debug($columns_names);
//            debug($value);
            $out_array[$key] = array_combine($columns_names, $value);
        });
//        foreach ($data as $key1 => $row) {
//            foreach ($row as $key => $val) {
//                if (is_array($val)) {
//                    $data[$key1][$key] = implode(';', $val);
//                }
//            }
//        }
        //debug($out_array,true);
        //debug($columns_names,true);
        return $out_array;
    }
    
    public function download_csv($grid_id, $value_id = null) {
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);
        
        $data = $this->prepareData($grid_id, $value_id);
        $csv = $this->arrayToCsv($data, ',');
        header("Content-Type: text/csv");
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"grid{$grid_id}.csv\"");
        echo $csv;
        exit;
        
    }
    public function download_excel($grid_id, $value_id = null) {
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);
        

        $grid = $this->datab->get_grid($grid_id);
        $fields = $grid['grids_fields'];
        
        
        $data = $this->prepareData($grid_id, $value_id);
        
        // Instantiate our custom binder, with a list of columns, and tell PHPExcel to use it
        PHPExcel_Cell::setValueBinder(new PHPExcel_Cell_MyColumnValueBinder([]));

        $objPHPExcel = new PHPExcel();
        
        $objPHPExcel->getActiveSheet()->fromArray(array_keys($data[0]), '','A1');
        
        //Imposto le colonne numeriche (per permettere a formule e altro di funzionare correttamente)
        foreach ($fields as $key => $field) {
            if (in_array(strtoupper($field['fields_type']), ['FLOAT','DOUBLE','INT','INTEGER']) ) {
                $numeric_cells[$key] = $field['grids_fields_column_name'];
            }
        }
        
        foreach ($data as $key => $dato) {
            foreach ($numeric_cells as $col_pos => $numeric_cell) {
                $data[$key][$numeric_cell] = str_replace('.', ',', $dato[$numeric_cell]);
            }            
        }
        
        foreach ($numeric_cells as $col_pos => $numeric_cell) {
            $col_letter = PHPExcel_Cell::stringFromColumnIndex($col_pos);
            //debug($col_letter,true);
            $objPHPExcel->getActiveSheet()->getStyle($col_letter)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $objPHPExcel->getActiveSheet()->getStyle($col_letter)->setQuotePrefix(false);
        }
        //debug($data,true);
        
        $objPHPExcel->getActiveSheet()->fromArray($data, '', 'A2');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"grid{$grid_id}.xlsx\"");
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->setPreCalculateFormulas(true);
        
        $objWriter->save('php://output');
        
    }
    
    
    private function arrayToCsv(array $data, $delim = ",", $enclosure = '"') {
        if (!$data) {
            return '';
        }

        // Apri un nuovo file, mi serve per avere un handler per usare
        // nativamente fputcsv che fa gli escape corretti
        $tmp = tmpfile() OR show_error('Impossibile creare file temporaneo');

        $keys = array_keys(array_values($data)[0]);
        fputcsv($tmp, $keys, $delim, $enclosure);

        
        
        foreach ($data as $row) {
//            debug($row);
//            debug($delim);
//            debug($enclosure);
            
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



/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */