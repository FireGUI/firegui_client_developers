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

        // Qualunque chiamata alle apilib da qua dentro è considerata una
        // chiamata in modalità CRM_FORM
        $this->apilib->setProcessingMode(Apilib::MODE_CRM_FORM);
    }

    private function prepareData($grid_id = null, $value_id = null)
    {
        //prendo tutti i dati della grid (filtri compresi) e li metto in un array associativo, pronto per essere esportato
        $grid = $this->datab->get_grid($grid_id);

        $grid_data = $this->datab->get_grid_data($grid, $value_id, '', NULL, 0, null);

        $out_array = array();
        foreach ($grid_data as $dato) {
            $tr = array();

            foreach ($grid['grids_fields'] as $field) {
                $tr[] = trim(strip_tags($this->datab->build_grid_cell($field, $dato, false)));
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

    public function download_excel($grid_id, $value_id = null)
    {
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);
        setlocale(LC_MONETARY, 'it_IT');

        $grid = $this->datab->get_grid($grid_id);
        $fields = $grid['grids_fields'];

        $data = $this->prepareData($grid_id, $value_id);

        $objPHPExcel = new Spreadsheet();

        $objPHPExcel->getActiveSheet()->fromArray(array_keys($data[0]), '', 'A1');

        // Imposto le colonne numeriche (per permettere a formule e altro di funzionare correttamente)
        foreach ($fields as $key => $field) {
            if ($field['grids_fields_totalable']) {
                $numeric_cells[$key] = $field['grids_fields_column_name'];
            }
        }

        //Per gli eval, devo passarmi tutti i dati per capire se qualche cella è anch'essa un numero (ricordarsi che number format torna una stringa e non un numero, quindi crea problemi con le formule excell dopo...)
        foreach (array_slice($data, 0, 10) as $key => $dato) {
            foreach ($dato as $column => $value) {
                if (!in_array($column, $numeric_cells)) { //Se già non è tra le numeric cells, skippo...
                    continue;
                }

                $numberize = preg_replace("/[^0-9.]/", '', $value);

                //Provo a castarlo a numero, se no, tolgo questa colonna tra quelle numeriche
                $possible_number = filter_var($numberize, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_THOUSAND | FILTER_FLAG_ALLOW_FRACTION);

                if ($possible_number === '') {
                    $numeric_cells = array_diff($numeric_cells, [$column]);
                } elseif ((float) $possible_number !== (float) $numberize) {
                    $numeric_cells = array_diff($numeric_cells, [$column]);
                }
            }
        }


        foreach ($data as $key => $dato) {
            foreach ($numeric_cells as $col_pos => $numeric_cell) {
                $data[$key][$numeric_cell] = (float) tofloat($dato[$numeric_cell]);
            }
        }

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

        $objWriter = IOFactory::createWriter($objPHPExcel, 'Xlsx');
        $objWriter->setPreCalculateFormulas(true);
        $objWriter->save('php://output');
    }


    private function arrayToCsv(array $data, $delim = ",", $enclosure = '"')
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



/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
