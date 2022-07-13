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
            eval('?>' . $view);

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
$content_html = false, $options = [])
{

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
//debug($boxes, true);
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
echo '
<link rel="stylesheet" type="text/css" href="' . $path . '&v=' . VERSION . '" />';
}
public function addModuleJavascript($module_identifier, $file)
{
$path = $this->moduleAssets($module_identifier, $file);
echo '<script src="' . $path . '&v=' . VERSION . '"></script>';
}


//Functions to include dinamic generate css or js
public function addDinamicStylesheet($data, $file, $clear = false)
{
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
<link rel="stylesheet" type="text/css" href="' . base_url($file) . '?v=' . VERSION . '" />';
}

//Functions to include dinamic generate css or js
public function addDinamicJavascript($data, $file)
{
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

public function addRelatedEntity($entity_name) {
if (!in_array($entity_name, $this->related_entities)) {
$this->related_entities[] = $entity_name;
}

}
public function getRelatedEntities() {
return $this->related_entities;
}
}