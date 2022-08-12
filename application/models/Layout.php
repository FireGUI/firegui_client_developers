<?php

class Layout extends CI_Model
{

    /**
     * @var array
     */
    private $structure = [];
    public $current_module_identifier = false;
    public $related_entities = [];

    /**
     * @param int $layoutId
     */
    public function addLayout($layoutId)
    {
        $this->structure[] = (int) $layoutId;
    }

    /**
     * @param int $layoutId
     */
    public function removeLastLayout()
    {
        array_pop($this->structure);
    }

    /**
     * @param int $layoutId
     * @return bool
     */
    public function isCalled($layoutId)
    {
        return in_array((int) $layoutId, $this->structure);
    }

    /**
     * @return int|null
     */
    public function getCurrentLayout()
    {
        $layouts = array_values($this->structure);
        return array_pop($layouts);
    }

    public function getLayoutByIdentifier($layout_identifier)
    {
        $result = $this->db->where('layouts_identifier', $layout_identifier)->get('layouts');

        if ($result->num_rows() == 0) {
            return false;
        } else {
            return $result->row()->layouts_id;
        }
    }
    /**
     * @return string|null
     */
    public function getCurrentLayoutIdentifier()
    {
        $layout_id = $this->getCurrentLayout();
        $layout = $this->db->query("SELECT * FROM layouts WHERE layouts_id = '$layout_id'")->row_array();
        if (!empty($layout)) {
            return $layout['layouts_identifier'];
        } else {
            return false;
        }
    }

    /**
     * @return int|null
     */
    public function getMainLayout()
    {
        $layouts = array_values($this->structure);
        return array_shift($layouts);
    }

    public function generate_html($view, $relative_path = "", $extra_data = [], $module = false, $content_html = false)
    {
        if ($content_html === true) {
            ob_start();
            extract($extra_data);
            eval('?' . '>' . $view);

            $content = ob_get_clean();
        } else {
            $this->load->library('parser');

            if (
                !$module
                || file_exists(FCPATH . "application/views_adminlte/custom/{$module}/{$relative_path}{$view}.php")
                || file_exists(FCPATH . "application/views_metronic/custom/{$module}/{$relative_path}{$view}.php")
            ) {
                if ($module) {
                    $relative_path = $module . '/' . $relative_path;
                }
                $content = $this->parser->parse("custom/{$relative_path}{$view}", $extra_data, true);
            } elseif (
                !$module
                || file_exists(FCPATH . "application/views_adminlte/pages/layouts/custom_views/{$module}/{$relative_path}{$view}.php")
                || file_exists(FCPATH . "application/views_metronic/pages/layouts/custom_views/{$module}/{$relative_path}{$view}.php")

            ) {
                if ($module) {
                    $relative_path = $module . '/' . $relative_path;
                }
                $content = $this->parser->parse("pages/layouts/custom_views/{$relative_path}{$view}", $extra_data, true);
            } else {
                $content = $this->load->module_view($module . "/views/{$relative_path}", $view, $extra_data, true);
            }
        }

        if ($this->input->get('html')) {
            die($content);
        }

        return $content;
    }

    public function generate_pdf($view, $orientation = "landscape", $relative_path = "", $extra_data = [], $module = false,
        $content_html = false, $options = []) {
        $useMpdf = array_get($options, 'useMpdf', false);

        $content = $this->generate_html($view, $relative_path, $extra_data, $module, $content_html);

        if ($useMpdf) {
            $mpdfInit = array_get($options, 'mpdfInit', [
                'mode' => 'utf-8',
                'margin_left' => 0,
                'margin_right' => 0,
                'margin_top' => 0,
                'margin_bottom' => 0,
                'margin_header' => 0,
                'margin_footer' => 0,
            ]);

            $mpdf = new \Mpdf\Mpdf($mpdfInit);

            $header = array_get($options, 'mpdfHeader', '');
            $footer = array_get($options, 'mpdfFooter', '');

            $coverPage = array_get($options, 'mpdfCover', '');
            $conditionsPage = array_get($options, 'mpdfConditions', '');

            $css = array_get($options, 'mpdfCss', '');
            $pdfTitle = array_get($options, 'mpdfTitle', '');
            $filename = '';

            if (!empty($coverPage)) {
                $mpdf->WriteHtml($this->generate_html($coverPage, $relative_path, $extra_data, $module, true), \Mpdf\HTMLParserMode::DEFAULT_MODE);
                $mpdf->AddPage();
            }

            if (!empty($header)) {
                $mpdf->SetHTMLHeader($this->generate_html($header, $relative_path, $extra_data, $module, true));
            }
            if (!empty($footer)) {
                $mpdf->SetHTMLFooter($this->generate_html($footer, $relative_path, $extra_data, $module, true));
            }
            if (!empty($pdfTitle)) {
                $filename = str_ireplace([' ', '.'], '_', $pdfTitle) . '.pdf';

                $mpdf->SetTitle($pdfTitle);
            }
            if (!empty($css)) {
                $mpdf->WriteHtml($css, \Mpdf\HTMLParserMode::HEADER_CSS);
            }

            $mpdf->WriteHtml($content, \Mpdf\HTMLParserMode::DEFAULT_MODE);

            if (!empty($conditionsPage)) {
                $mpdf->AddPage();
                $mpdf->WriteHtml($this->generate_html($conditionsPage, $relative_path, $extra_data, $module, true), \Mpdf\HTMLParserMode::DEFAULT_MODE);
            }

            $mpdf->Output($filename, 'I');

            //TODO: return true?
        } else {
            $physicalDir = FCPATH . "/uploads";
            // 2022-04-19 - Added random_int because it can happen that a generation of pdf deriving from an array,
            // is done in the same second. so this guarantees a little more uniqueness ... Would microseconds be better?
            $filename = date('Ymd_His') . '_' . random_int(1, 100);
            $pdfFile = "{$physicalDir}/{$filename}.pdf";

            // Create a temporary file with the view html
            if (!is_dir($physicalDir)) {
                mkdir($physicalDir, 0755, true);
            }
            $tmpHtml = "{$physicalDir}/{$filename}.html";
            file_put_contents($tmpHtml, $content, LOCK_EX);

            if ($this->input->get('options') !== null) {
                $_options = $this->input->get('options');

                $options = '';
                foreach ($_options as $key => $value) {
                    $options .= "-{$key} {$value} ";
                }
            } else {
                $options = "-T '5mm' -B '5mm'";
            }
            //die("wkhtmltopdf {$options} -O {$orientation} --footer-right \"Page [page] of [topage]\" --viewport-size 1024 {$tmpHtml} {$pdfFile}");
            exec("wkhtmltopdf {$options} -O {$orientation} --viewport-size 1024 {$tmpHtml} {$pdfFile}");

            return $pdfFile;
        }
    }

    public function getLayout($layoutId)
    {
        $layout = $this->db->join('modules', 'layouts_module = modules_identifier', 'LEFT')->get_where('layouts',
            array('layouts_id' => $layoutId))->row_array();
        return $layout;
    }

    public function setLayoutModule($current_module_identifier = false)
    {
        $this->current_module_identifier = $current_module_identifier;
    }
    public function getLayoutModule()
    {
        return $this->current_module_identifier;
    }
    public function getLayoutBox($lb_id)
    {
        if (!$this->conditions->accessible('layouts_boxes', $lb_id)) {
            return null;
        }
        $box = $this->db->order_by('layouts_boxes_row, layouts_boxes_position, layouts_boxes_cols')
            ->join('layouts', 'layouts.layouts_id = layouts_boxes.layouts_boxes_layout', 'left')
            ->get_where('layouts_boxes', ['layouts_boxes_id' => $lb_id])->row_array();

        $allSubboxes = [];
        if ($box['layouts_boxes_content_type'] === 'tabs') {
            $box['subboxes'] = explode(',', $box['layouts_boxes_content_ref']);
            $allSubboxes = array_merge($allSubboxes, $box['subboxes']);
        }

        return $box;
    }
    public function getBoxes($layoutId)
    {

        $queriedBoxes = $this->db->order_by('layouts_boxes_row, layouts_boxes_position, layouts_boxes_cols')
            ->join('layouts', 'layouts.layouts_id = layouts_boxes.layouts_boxes_layout', 'left')
            ->get_where('layouts_boxes', ['layouts_id' => $layoutId])->result_array();

// Rimappo i box in modo tale da avere i parent che contengono i sub
        $boxes = $allSubboxes = $lboxes = [];
        foreach ($queriedBoxes as $key => $box) {
            if (!$this->conditions->accessible('layouts_boxes', $box['layouts_boxes_id'])) {
                unset($queriedBoxes[$key]);
                continue;
            }
            if ($box['layouts_boxes_content_type'] === 'tabs') {
                $box['subboxes'] = explode(',', $box['layouts_boxes_content_ref']);
                $allSubboxes = array_merge($allSubboxes, $box['subboxes']);
            }

            $lboxes[$box['layouts_boxes_id']] = $box;
        }

        foreach ($lboxes as $id => $box) {
            $myKey = uniqid('box-');

            if (!in_array($id, $allSubboxes)) {
                $box['is_subbox'] = false;
                $box['parent_box'] = null;
                $boxes[$myKey] = $box;
            }

            if (isset($box['subboxes']) && is_array($box['subboxes'])) {
                $thisSubboxes = [];
                $index = 0;
                foreach ($box['subboxes'] as $subboxId) {
                    if (array_key_exists($subboxId, $lboxes)) {
                        $thisSubboxes[$myKey . '-' . $index++] = $lboxes[$subboxId];
                    }
                }

                $boxes[$myKey]['subboxes'] = $thisSubboxes;
            }
        }

        return $boxes;
    }

    public function moduleAssets($module_identifier, $file)
    {
        $file_cache = "template/build/{$module_identifier}/assets/$file";
        $current_config = $this->mycache->getCurrentConfig();
        if (file_exists($file_cache) && $this->mycache->isCacheEnabled() && !empty($current_config['template_assets']['active'])) {
            $path = base_url($file_cache);
        } else {
            if ($this->mycache->isCacheEnabled() && !empty($current_config['template_assets']['active'])) {
                $modules_path = APPPATH . 'modules';
                $assets_folder = "{$modules_path}/{$module_identifier}/assets";
                $asset_file = "$assets_folder/$file";
                copy_file($asset_file, $file_cache);
                $path = base_url($file_cache);

            } else {
                $path = base_url("modulesbridge/loadAssetFile/{$module_identifier}?file=$file");

            }
        }

        return $path;
    }

    public function addModuleStylesheet($module_identifier, $file)
    {
        $path = $this->moduleAssets($module_identifier, $file);
        echo '
<link rel="stylesheet" type="text/css" href="' . $path . '" />';
    }
    public function addModuleJavascript($module_identifier, $file)
    {
        $path = $this->moduleAssets($module_identifier, $file);
        echo '<script src="' . $path . '"></script>';
    }

//Functions to include dinamic generate css or js
    public function addDinamicStylesheet($data, $file, $clear = false)
    {
        if (!file_exists('template/build')) {
            mkdir('template/build', 0777, true);
        }

        $file = "template/build/{$file}";
        if (!file_exists($file) || !$this->mycache->isCacheEnabled() || $clear) {
            $fp = fopen($file, 'w+');
            foreach ($data as $key => $vals) {
                switch ($key) {
                    case 'background-colors':
                        foreach ($vals as $md5 => $color) {
                            $str_append = ".bg{$md5} {background-color:{$color};}";
                            fwrite($fp, $str_append);
                        }
                        break;
                    case 'custom':
                        foreach ($vals as $selector => $styles) {
                            fwrite($fp, "$selector {");

                            foreach ($styles as $prop => $val) {
                                fwrite($fp, "$prop: $val;");
                            }

                            fwrite($fp, "}");
                        }
                        break;
                    default:
// debug($vals);
// log_message('error', "Key '$key' not recognized for dinamic stylesheet");
                        break;
                }
            }
            fclose($fp);
        }

        echo '
<link rel="stylesheet" type="text/css" href="' . base_url($file) . '" />';
    }

//Functions to include dinamic generate css or js
    public function addDinamicJavascript($data, $file)
    {
        if (!file_exists('template/build')) {
            mkdir('template/build', 0777, true);
        }

        $file = "template/build/{$file}";
        if (!file_exists($file) || !$this->mycache->isCacheEnabled()) {
            $fp = fopen($file, 'w+');
            foreach ($data as $key => $val) {
                fwrite($fp, $val . PHP_EOL);
            }
            fclose($fp);
        }

        echo '<script src="' . base_url($file) . '?v=' . VERSION . '"></script>';
    }

    public function replaceTemplateHooks($html, $value_id)
    {
//debug($html, true);
        preg_match_all("/\{tpl-[^\}]*\}/", $html, $matches);
        foreach ($matches[0] as $placeholder) {

//debug(strpos($placeholder, '{tpl-'), true);

//if (strpos($placeholder, '{tpl-') === 0) {
            $hook_key = trim($placeholder, "{}");
            $hook_key = str_replace('tpl-', '', $hook_key);
//debug($hook_key, true);

            $hooks_contents = $this->fi_events->getHooksContents($hook_key, $value_id);

            $html = str_replace($placeholder, $hooks_contents, $html);
//}
        }
        return $html;
    }

    public function addRelatedEntity($entity_name, $value_id = null)
    {

        if ($value_id) {
            $entity_name = "{$entity_name}:{$value_id}";
        }
        if (!in_array($entity_name, $this->related_entities)) {
            $this->related_entities[] = $entity_name;
        }

    }
    public function getRelatedEntities()
    {
        return $this->related_entities;
    }
}
