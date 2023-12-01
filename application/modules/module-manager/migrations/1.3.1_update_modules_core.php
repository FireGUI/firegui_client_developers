<?php

$this->db->query("UPDATE modules SET modules_core = 0 WHERE modules_core IS NULL OR modules_core = ''");