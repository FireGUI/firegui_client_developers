<?php

class Docs extends CI_Model {

    public function test() {
        die('asdda');
    }
    
    public function getDocumentiPadri($id) {
        $documento = $this->apilib->view('documenti_contabilita', $id);
        
        $return = [];
        
        while ($documento['documenti_contabilita_rif_documento_id'] && $documento['documenti_contabilita_rif_documento_id'] != $id) {
            $return[] = $this->apilib->view('documenti_contabilita', $documento['documenti_contabilita_rif_documento_id']);
            $documento = $this->apilib->view('documenti_contabilita', $documento['documenti_contabilita_rif_documento_id']);
        }
        
        return $return;
    }

    public function generate_xml($documento) {

        $documento_id = $documento['documenti_contabilita_id'];

        if ($this->db->dbdriver != 'postgre') {
            $progressivo_invio = $this->db->query("SELECT MAX(CAST(documenti_contabilita_progressivo_invio AS integer)) as m FROM documenti_contabilita");
        } else {
            $progressivo_invio = $this->db->query("SELECT MAX(documenti_contabilita_progressivo_invio::int4) as m FROM documenti_contabilita");
        }

        if ($progressivo_invio->num_rows() == 0) {
            $progressivo_invio = 1;
        } else {
            $progressivo_invio = (int)($progressivo_invio->row()->m) + 1;
        }

        $this->db->where('documenti_contabilita_id', $documento_id)->update('documenti_contabilita', ['documenti_contabilita_progressivo_invio' => $progressivo_invio]);

        $pdf_b64 = base64_encode(file_get_contents(base_url('contabilita/documenti/xml_fattura_elettronica/' . $documento_id)));
        //die(file_get_contents(base_url('contabilita/documenti/xml_fattura_elettronica/' . $documento_id)));
        $this->apilib->edit("documenti_contabilita", $documento_id, ['documenti_contabilita_file' => $pdf_b64]);

        // Storicizzo comunque un pdf dato il mio template
        // Storicizzo PDF
        if ($documento['documenti_contabilita_template_pdf']) {
            //die('test2');
            $content_html = $this->apilib->view('documenti_contabilita_template_pdf', $documento['documenti_contabilita_template_pdf']);
            $pdfFile = $this->layout->generate_pdf($content_html['documenti_contabilita_template_pdf_html'], "portrait", "", ['documento_id' => $documento_id], 'contabilita', TRUE);
        } else {
            //die('test');
            $pdfFile = $this->layout->generate_pdf("documento_pdf", "portrait", "", ['documento_id' => $documento_id], 'contabilita');
        }
        
        if (file_exists($pdfFile)) {
            $contents = file_get_contents($pdfFile, true);
            $pdf_b64 = base64_encode($contents);
            $this->apilib->edit("documenti_contabilita", $documento_id, ['documenti_contabilita_file_preview' => $pdf_b64]);
        }
    }
}

