<?php


class Layout extends CI_Model {
    
    /**
     * @var array
     */
    private $structure = [];
    
    /**
     * @param int $layoutId
     */
    public function addLayout($layoutId) {
        $this->structure[] = (int) $layoutId;
    }
    
    /**
     * @param int $layoutId
     */
    public function removeLastLayout() {
        array_pop($this->structure);
    }
    
    /**
     * @param int $layoutId
     * @return bool
     */
    public function isCalled($layoutId) {
        return in_array((int) $layoutId, $this->structure);
    }
    
    /**
     * @return int|null
     */
    public function getCurrentLayout() {
        $layouts = array_values($this->structure);
        return array_pop($layouts);
    }
    
    /**
     * @return int|null
     */
    public function getMainLayout() {
        $layouts = array_values($this->structure);
        return array_shift($layouts);
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
                    $thisSubboxes[$myKey.'-'.$index++] = $lboxes[$subboxId];
                }
                
                $boxes[$myKey]['subboxes'] = $thisSubboxes;
            }

        }
            
        return $boxes;
    }
    
}