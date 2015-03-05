<?php

class Pdf extends MX_Controller {


    public function __construct() {
        parent::__construct();
        if ($this->auth->guest()) {
            redirect('access');
        }

        if (!$this->datab->module_installed(MODULE_NAME)) {
            die('Module not installed');
        }

        if (!$this->datab->module_access(MODULE_NAME)) {
            die('Access forbidden');
        }

        $this->load->helper('offers');
        $this->settings = $this->db->get('settings')->row_array();
    }
    
    
    
    
    

    public function generate($offer_id=null) {

        $dati['offer'] = $this->db->
                        from('offers')->
                        join(ENTITY_CUSTOMERS, 'offers_customer = '.ENTITY_CUSTOMERS.'_id', 'left')->
                        join(ENTITY_USERS, 'offers_user = '.ENTITY_USERS.'_id', 'left')->
                        join(ENTITY_CITY, CUSTOMER_CITY.' = '.CITY_ID, 'left')->
                        join(ENTITY_PROV, CUSTOMER_PROV.' = '.PROV_ID, 'left')->
                        where('offers_id', $offer_id)->
                        order_by('offers_id DESC')->
                        get()->row_array();
        
        $dati['products'] = $this->db->get_where('offers_products', array('offers_products_offer_id'=>$offer_id))->result_array();
        
        
        $this->genera_pdf('pdf_offer', $dati);
    }


    


    private function genera_pdf($vista, $dati = null) {
        ob_end_clean();
        $content = $this->load->view($vista, array('dati' => $dati), true);
        require_once('./class/html2pdf/html2pdf.class.php');
        $html2pdf = new HTML2PDF('P', 'A4', 'it');
        $html2pdf->pdf->SetDisplayMode('fullpage');
        $html2pdf->WriteHTML($content);
        $html2pdf->Output();
    }
}