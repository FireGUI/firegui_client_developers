<?php
class MY_Profiler extends CI_Profiler
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function _compile_queries()
    {
        $dbs = array();

        // Let's determine which databases are currently connected to
        foreach (get_object_vars($this->CI) as $name => $cobject) {
            if (is_object($cobject)) {
                if ($cobject instanceof CI_DB) {
                    $dbs[get_class($this->CI) . ':$' . $name] = $cobject;
                } elseif ($cobject instanceof CI_Model) {
                    foreach (get_object_vars($cobject) as $mname => $mobject) {
                        if ($mobject instanceof CI_DB) {
                            $dbs[get_class($cobject) . ':$' . $mname] = $mobject;
                        }
                    }
                }
            }
        }

        if (count($dbs) === 0) {
            return "\n\n"
                . '<fieldset id="ci_profiler_queries" style="border:1px solid #0000FF;padding:6px 10px 10px 10px;margin:20px 0 20px 0;background-color:#eee;">'
                . "\n"
                . '<legend style="color:#0000FF;">&nbsp;&nbsp;' . $this->CI->lang->line('profiler_queries') . '&nbsp;&nbsp;</legend>'
                . "\n\n\n<table style=\"border:none; width:100%;\">\n"
                . '<tr><td style="width:100%;color:#0000FF;font-weight:normal;background-color:#eee;padding:5px;">'
                . $this->CI->lang->line('profiler_no_db')
                . "</td></tr>\n</table>\n</fieldset>";
        }

        // Load the text helper so we can highlight the SQL
        $this->CI->load->helper('text');

        // Key words we want bolded
        $highlight = array('SELECT', 'DISTINCT', 'FROM', 'WHERE', 'AND', 'LEFT&nbsp;JOIN', 'ORDER&nbsp;BY', 'GROUP&nbsp;BY', 'LIMIT', 'INSERT', 'INTO', 'VALUES', 'UPDATE', 'OR&nbsp;', 'HAVING', 'OFFSET', 'NOT&nbsp;IN', 'IN', 'LIKE', 'NOT&nbsp;LIKE', 'COUNT', 'MAX', 'MIN', 'ON', 'AS', 'AVG', 'SUM', '(', ')');

        $output  = "\n\n";
        $count = 0;

        foreach ($dbs as $name => $db) {

            uksort($db->queries, function ($key1, $key2) use ($db) {
                return ($db->query_times[$key1] > $db->query_times[$key2]) ? -1 : 1;;
            });
            uksort($db->query_times, function ($key1, $key2) use ($db) {
                return ($db->query_times[$key1] > $db->query_times[$key2]) ? -1 : 1;;
            });

            $hide_queries = (count($db->queries) > $this->_query_toggle_count) ? ' display:none' : '';
            $total_time = number_format(array_sum($db->query_times), 4) . ' ' . $this->CI->lang->line('profiler_seconds');

            $show_hide_js = '(<span style="cursor: pointer;" onclick="var s=document.getElementById(\'ci_profiler_queries_db_' . $count . '\').style;s.display=s.display==\'none\'?\'\':\'none\';this.innerHTML=this.innerHTML==\'' . $this->CI->lang->line('profiler_section_hide') . '\'?\'' . $this->CI->lang->line('profiler_section_show') . '\':\'' . $this->CI->lang->line('profiler_section_hide') . '\';">' . $this->CI->lang->line('profiler_section_hide') . '</span>)';

            if ($hide_queries !== '') {
                $show_hide_js = '(<span style="cursor: pointer;" onclick="var s=document.getElementById(\'ci_profiler_queries_db_' . $count . '\').style;s.display=s.display==\'none\'?\'\':\'none\';this.innerHTML=this.innerHTML==\'' . $this->CI->lang->line('profiler_section_show') . '\'?\'' . $this->CI->lang->line('profiler_section_hide') . '\':\'' . $this->CI->lang->line('profiler_section_show') . '\';">' . $this->CI->lang->line('profiler_section_show') . '</span>)';
            }

            $output .= '<fieldset style="border:1px solid #0000FF;padding:6px 10px 10px 10px;margin:20px 0 20px 0;background-color:#eee;">'
                . "\n"
                . '<legend style="color:#0000FF;">&nbsp;&nbsp;' . $this->CI->lang->line('profiler_database')
                . ':&nbsp; ' . $db->database . ' (' . $name . ')&nbsp;&nbsp;&nbsp;' . $this->CI->lang->line('profiler_queries')
                . ': ' . count($db->queries) . ' (' . $total_time . ')&nbsp;&nbsp;' . $show_hide_js . "</legend>\n\n\n"
                . '<table style="width:100%;' . $hide_queries . '" id="ci_profiler_queries_db_' . $count . "\">\n";

            if (count($db->queries) === 0) {
                $output .= '<tr><td style="width:100%;color:#0000FF;font-weight:normal;background-color:#eee;padding:5px;">'
                    . $this->CI->lang->line('profiler_no_queries') . "</td></tr>\n";
            } else {

                foreach ($db->queries as $key => $val) {
                    $time = number_format($db->query_times[$key], 4);
                    $val = highlight_code($val);

                    foreach ($highlight as $bold) {
                        $val = str_replace($bold, '<strong>' . $bold . '</strong>', $val);
                    }

                    $output .= '<tr><td style="padding:5px;vertical-align:top;width:1%;color:#900;font-weight:normal;background-color:#ddd;">'
                        . $time . '&nbsp;&nbsp;</td><td style="padding:5px;color:#000;font-weight:normal;background-color:#ddd;">'
                        . $val . "</td></tr>\n";
                }
            }

            $output .= "</table>\n</fieldset>";
            $count++;
        }

        return $output;
    }
}
