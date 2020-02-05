<?php


class Layout extends CI_Model
{

    /**
     * @var array
     */
    private $structure = [];

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

        //debug("pages/layouts/custom_views/{$module}/{$relative_path}{$view}",true);
        if ($content_html === true) {
            ob_start();
            extract($extra_data);
            eval('?>' . $view);
            //$content = $this->parser->parse_string($view, $extra_data, true);

            $content = ob_get_clean();
            //die($content);
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
                //die("application/views_adminlte/pages/layouts/custom_views/{$module}/{$relative_path}{$view}");
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

        // Exec the command
        $options = "-T '5mm' -B '5mm' -O $orientation";

        exec("wkhtmltopdf {$options} --viewport-size 1024 {$tmpHtml} {$pdfFile}");
        //debug("wkhtmltopdf {$options} --viewport-size 1024 {$tmpHtml} {$pdfFile}",true);


        return $pdfFile;
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
                    $thisSubboxes[$myKey . '-' . $index++] = $lboxes[$subboxId];
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
}
