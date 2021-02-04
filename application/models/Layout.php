<?php


class Layout extends CI_Model
{

    /**
     * @var array
     */
    private $structure = [];
    public $current_module_identifier = false;

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


    public function generate_pdf($view, $orientation = "landscape", $relative_path = "", $extra_data = [], $module = false, $content_html = false)
    {

        $this->load->library('parser');

        $physicalDir = __DIR__ . "/../../uploads";
        $filename = date('Ymd-H-i-s');
        $pdfFile = "{$physicalDir}/{$filename}.pdf";

        if ($content_html === true) {
            ob_start();
            extract($extra_data);
            eval('?>' . $view);

            $content = ob_get_clean();
        } else {
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
                $options .= "-{$key} '{$value}' ";
            }
        } else {
            $options = "-T '5mm' -B '5mm'";
        }


        exec("wkhtmltopdf {$options} -O {$orientation} --viewport-size 1024 {$tmpHtml} {$pdfFile}");

        return $pdfFile;
    }
    public function getLayout($layoutId)
    {
        $layout = $this->db->get_where('layouts', array('layouts_id' => $layoutId))->row_array();
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

    public function getBoxes($layoutId)
    {
        $queriedBoxes = $this->db->order_by('layouts_boxes_row, layouts_boxes_position, layouts_boxes_cols')
            ->join('layouts', 'layouts.layouts_id = layouts_boxes.layouts_boxes_layout', 'left')
            ->get_where('layouts_boxes', ['layouts_id' => $layoutId])->result_array();

        // Rimappo i box in modo tale da avere i parent che contengono i sub
        $boxes = $allSubboxes = $lboxes = [];
        foreach ($queriedBoxes as $box) {
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
        $path = base_url("modulesbridge/loadAssetFile/{$module_identifier}?file=$file");
        return $path;
    }

    public function addModuleStylesheet($module_identifier, $file)
    {
        $path = $this->moduleAssets($module_identifier, $file);
        echo '<link rel="stylesheet" type="text/css" href="' . $path . '&v=' . VERSION . '" />';
    }
    public function addModuleJavascript($module_identifier, $file)
    {
        $path = $this->moduleAssets($module_identifier, $file);
        echo '<script src="' . $path . '&v=' . VERSION . '" ></script>';
    }


    //Functions to include dinamic generate css or js
    public function addDinamicStylesheet($data, $file)
    {
        $file = "template/build/{$file}";
        if (!file_exists($file) || !$this->apilib->isCacheEnabled()) {
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
                        log_message('error', "Key '$key' not recognized for dinamic stylesheet");
                        break;
                }
            }
            fclose($fp);
        }

        echo '<link rel="stylesheet" type="text/css" href="' . base_url($file) . '?v=' . VERSION . '" />';
    }

    //Functions to include dinamic generate css or js
    public function addDinamicJavascript($data, $file)
    {
        $file = "template/build/{$file}";
        if (!file_exists($file) || !$this->apilib->isCacheEnabled()) {
            $fp = fopen($file, 'w+');
            foreach ($data as $key => $val) {
                fwrite($fp, $val . PHP_EOL);
            }
            fclose($fp);
        }

        echo '<script src="' . base_url($file) . '?v=' . VERSION . '" ></script>';
    }
}
