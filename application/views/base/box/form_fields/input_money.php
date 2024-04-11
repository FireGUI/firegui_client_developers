<?php
//Do some magic: same input as input_text, then manage this attribute "money" directly on the view...
$this->load->view('box/form_fields/input_text', [
    'field' => $field,
    'label' => $label,
    'class' => "{$class} js_money",
    'placeholder' => $placeholder,
    'value' => r_money($value),
    'onclick' => $onclick,
    'attr' => $attr,
]);
