<?php
/*
	   The location of the profiler bar. Valid locations are:

		   - bottom-left
		   - bottom-right
		   - top-left
		   - top-right
		   - bottom
		   - top
	*/
$bar_location = 'top-right';
function raggruppa_query($queries)
{
	$aggregatedData = [];

	foreach ($queries as $queryInfo) {
		$query = $queryInfo['query'];
		$executionTime = $queryInfo['time'];
		$file = $queryInfo['file'];
		$line = $queryInfo['line'];

		// Se la query non è già nell'array, inizializza i dati aggregati
		if (!isset($aggregatedData[$query])) {
			$aggregatedData[$query] = [
				'totalQueries' => 0,
				'totalExecutionTime' => 0,
				'minExecutionTime' => null,
				'maxExecutionTime' => null,
				'locations' => [],
				// Array per le posizioni (file e linea) della query
			];
		}

		// Aggiorna i dati aggregati per questa query
		$aggregatedData[$query]['totalQueries']++;
		$aggregatedData[$query]['totalExecutionTime'] += $executionTime;

		if ($aggregatedData[$query]['minExecutionTime'] === null || $executionTime < $aggregatedData[$query]['minExecutionTime']) {
			$aggregatedData[$query]['minExecutionTime'] = $executionTime;
		}

		if ($aggregatedData[$query]['maxExecutionTime'] === null || $executionTime > $aggregatedData[$query]['maxExecutionTime']) {
			$aggregatedData[$query]['maxExecutionTime'] = $executionTime;
		}

		// Aggiungi il file e la linea al subarray "locations"
		$aggregatedData[$query]['locations'][] = [
			'file' => $file,
			'line' => $line,
		];
	}
	$total_time = 0;
	// Calcola la media per ciascuna query
	foreach ($aggregatedData as &$data) {
		$data['averageExecutionTime'] = ($data['totalQueries'] > 0) ? ($data['totalExecutionTime'] / $data['totalQueries']) : 0;

		$total_time += $data['totalExecutionTime'];

		// Uniforma i tempi a 4 cifre decimali
		$data['totalExecutionTime'] = number_format($data['totalExecutionTime'], 4);
		$data['minExecutionTime'] = number_format($data['minExecutionTime'], 4);
		$data['maxExecutionTime'] = number_format($data['maxExecutionTime'], 4);
		$data['averageExecutionTime'] = number_format($data['averageExecutionTime'], 4);
	}

	//Aggiungo il totale
	$aggregatedData['Total Query Execution Time'] = [
		'totalExecutionTime' => number_format($total_time, 4),
		'totalQueries' => 99999,
	];

	// Ordina l'array in base al tempo massimo di esecuzione (ordine decrescente)
	uasort($aggregatedData, function ($a, $b) {
		return $b['totalExecutionTime'] <=> $a['totalExecutionTime'];
	});



	return $aggregatedData;
}

function getExistingIndexes()
{
	$indexes = [];
	$CI = &get_instance();

	// Recupera tutte le tabelle del database
	$tablesQuery = $CI->db->query("SHOW TABLES");
	$tables = $tablesQuery->result_array();

	// Itera attraverso tutte le tabelle
	foreach ($tables as $table) {
		$tableName = array_values($table)[0];
		$query = $CI->db->query("SHOW INDEX FROM $tableName");
		$results = $query->result_array();

		foreach ($results as $row) {
			$column = $row['Column_name'];
			$indexes[] = "$column";
		}
	}

	return array_unique($indexes);
}


function suggestIndexes($queries)
{
	$CI = &get_instance();

	$fields = array_key_map($CI->db->query("SELECT fields_name FROM fields")->result_array(), 'fields_name', null);

	$indexSuggestions = [];
	$existingIndexes = getExistingIndexes();
	//debug($queries,true);
	foreach ($queries as $query) {
		if (stripos($query, 'tickets_reports_customer_id') !== false) {
			//debug($query,true);

		}

		// Trova tutte le colonne usate nei WHERE
		if (preg_match_all('/\bWHERE\s+([^\s]+)\s*=/i', $query, $matches)) {
			foreach ($matches[1] as $column) {
				// Escludere le funzioni
				if (preg_match('/\(|\)/', $column))
					continue;
				$indexSuggestions[$column][] = 'WHERE';
			}
		}

		// Trova tutte le colonne usate nei JOIN
		if (preg_match_all('/\bJOIN\s+[^\s]+\s+ON\s+([^\s]+)\s*=\s*([^\s]+)/i', $query, $matches)) {
			foreach ($matches[1] as $column) {
				// Escludere le funzioni
				if (preg_match('/\(|\)/', $column))
					continue;
				$indexSuggestions[$column][] = 'JOIN';
			}
			foreach ($matches[2] as $column) {
				// Escludere le funzioni
				if (preg_match('/\(|\)/', $column))
					continue;
				$indexSuggestions[$column][] = 'JOIN';
			}
		}

		// Trova tutte le colonne usate nei ORDER BY
		if (preg_match_all('/\bORDER\s+BY\s+([^\s,]+)/i', $query, $matches)) {
			foreach ($matches[1] as $column) {
				// Escludere le funzioni
				if (preg_match('/\(|\)/', $column))
					continue;
				$indexSuggestions[$column][] = 'ORDER BY';
			}
		}

		// Trova tutte le colonne usate nei GROUP BY
		if (preg_match_all('/\bGROUP\s+BY\s+([^\s,]+)/i', $query, $matches)) {
			foreach ($matches[1] as $column) {
				// Escludere le funzioni
				if (preg_match('/\(|\)/', $column))
					continue;
				$indexSuggestions[$column][] = 'GROUP BY';
			}
		}
	}

	// Elimina duplicati e crea una lista di suggerimenti
	$suggestions = [];
	foreach ($indexSuggestions as $column => $contexts) {
		$contexts = array_unique($contexts);
		// Rimuovi i backtick dai nomi delle colonne
		$column_expl = explode('.', $column);
		if (count($column_expl) == 2) {
			$column = $column_expl[1];
		} else {
			$column = $column_expl[0];
		}
		$cleanColumn = str_replace('`', '', $column);

		if (is_numeric($cleanColumn) || !in_array($cleanColumn, $fields)) {
			continue;
		}

		// Suggerisci solo se l'indice non esiste già
		if (strpos($cleanColumn, '.') === false) {
			// Non aggiungere prefisso "unknown_table"
			$formattedColumn = $cleanColumn;
		} else {
			$formattedColumn = explode('.', $cleanColumn)[1];
		}

		if (!in_array($formattedColumn, $existingIndexes)) {
			$suggestions[] = [
				'column' => $formattedColumn,
				'contexts' => $contexts
			];
		}
	}

	// Ordina i suggerimenti in base alla priorità dei contesti
	usort($suggestions, function ($a, $b) {
		$priority = ['WHERE' => 1, 'ORDER BY' => 2, 'GROUP BY' => 3, 'JOIN' => 4];
		$aPriority = min(array_map(function ($context) use ($priority) {
			return $priority[$context];
		}, $a['contexts']));
		$bPriority = min(array_map(function ($context) use ($priority) {
			return $priority[$context];
		}, $b['contexts']));
		return $aPriority <=> $bPriority;
	});
	
	//debug($suggestions,true);
	return $suggestions;
}


// debug(suggestIndexes(['SELECT COUNT(*) AS `numrows`
// FROM `tickets_reports`
// LEFT JOIN `customers_shipping_address` ON `customers_shipping_address`.`customers_shipping_address_id` = `tickets_reports`.`tickets_reports_customer_address`
// LEFT JOIN `customers` ON `customers`.`customers_id` = `tickets_reports`.`tickets_reports_customer_id`
// LEFT JOIN `tickets_reports_maggiorazione` ON `tickets_reports_maggiorazione`.`tickets_reports_maggiorazione_id` = `tickets_reports`.`tickets_reports_maggiorazione`
// LEFT JOIN `projects` ON `projects`.`projects_id` = `tickets_reports`.`tickets_reports_project_id`
// LEFT JOIN `tickets_reports_stato_lavori` ON `tickets_reports_stato_lavori`.`tickets_reports_stato_lavori_id` = `tickets_reports`.`tickets_reports_stato_lavori`
// LEFT JOIN `users` ON `users`.`users_id` = `tickets_reports`.`tickets_reports_technician`
// LEFT JOIN `tickets` ON `tickets`.`tickets_id` = `tickets_reports`.`tickets_reports_ticket_id`
// LEFT JOIN `tickets_reports_tipo_intervento` ON `tickets_reports_tipo_intervento`.`tickets_reports_tipo_intervento_id` = `tickets_reports`.`tickets_reports_tipo_intervento`
// LEFT JOIN `tickets_reports_type` ON `tickets_reports_type`.`tickets_reports_type_id` = `tickets_reports`.`tickets_reports_type`
// LEFT JOIN `countries` ON `countries`.`countries_id` = `customers_shipping_address`.`customers_shipping_address_country_id`
// LEFT JOIN `customers_shipping_address_type` ON `customers_shipping_address_type`.`customers_shipping_address_type_id` = `customers_shipping_address`.`customers_shipping_address_type`
// LEFT JOIN `rel_customer_codice_ateco` ON `rel_customer_codice_ateco`.`rel_customer_codice_ateco_id` = `customers`.`customers_altri_codici_ateco`
// LEFT JOIN `rel_customers_categories` ON `rel_customers_categories`.`rel_customers_categories_id` = `customers`.`customers_categories`
// LEFT JOIN `codici_ateco` ON `codici_ateco`.`codici_ateco_id` = `customers`.`customers_codice_ateco`
// LEFT JOIN `documenti_contabilita_conti` ON `documenti_contabilita_conti`.`documenti_contabilita_conti_id` = `customers`.`customers_conto`
// LEFT JOIN `documenti_contabilita_mastri` ON `documenti_contabilita_mastri`.`documenti_contabilita_mastri_id` = `customers`.`customers_contropartita_mastro`
// LEFT JOIN `documenti_contabilita_sottoconti` ON `documenti_contabilita_sottoconti`.`documenti_contabilita_sottoconti_id` = `customers`.`customers_contropartita_sottoconto`
// LEFT JOIN `spese_categorie` ON `spese_categorie`.`spese_categorie_id` = `customers`.`customers_expense_category`
// LEFT JOIN `customers_sotto_tipo` ON `customers_sotto_tipo`.`customers_sotto_tipo_id` = `customers`.`customers_group`
// LEFT JOIN `rel_customers_interests` ON `rel_customers_interests`.`rel_customers_interests_id` = `customers`.`customers_interests`
// LEFT JOIN `iva` ON `iva`.`iva_id` = `customers`.`customers_iva_default`
// LEFT JOIN `listini` ON `listini`.`listini_id` = `customers`.`customers_listino`
// LEFT JOIN `documenti_contabilita_metodi_pagamento` ON `documenti_contabilita_metodi_pagamento`.`documenti_contabilita_metodi_pagamento_id` = `customers`.`customers_payment_method`
// LEFT JOIN `price_list_labels` ON `price_list_labels`.`price_list_labels_id` = `customers`.`customers_price_list`
// LEFT JOIN `customers_source` ON `customers_source`.`customers_source_id` = `customers`.`customers_source`
// LEFT JOIN `customers_status` ON `customers_status`.`customers_status_id` = `customers`.`customers_status`
// LEFT JOIN `rel_customers_tags` ON `rel_customers_tags`.`rel_customers_tags_id` = `customers`.`customers_tags`
// LEFT JOIN `documenti_contabilita_template_pagamenti` ON `documenti_contabilita_template_pagamenti`.`documenti_contabilita_template_pagamenti_id` = `customers`.`customers_template_pagamento`
// LEFT JOIN `customers_type` ON `customers_type`.`customers_type_id` = `customers`.`customers_type`
// LEFT JOIN `vettori` ON `vettori`.`vettori_id` = `customers`.`customers_vettore_default`
// LEFT JOIN `projects_category` ON `projects_category`.`projects_category_id` = `projects`.`projects_category`
// LEFT JOIN `leads` ON `leads`.`leads_id` = `projects`.`projects_lead_id`
// LEFT JOIN `customers_contacts` ON `customers_contacts`.`customers_contacts_id` = `projects`.`projects_referent`
// LEFT JOIN `projects_status` ON `projects_status`.`projects_status_id` = `projects`.`projects_status`
// LEFT JOIN `layouts` ON `layouts`.`layouts_id` = `users`.`users_default_dashboard`
// LEFT JOIN `users_type` ON `users_type`.`users_type_id` = `users`.`users_type`
// LEFT JOIN `tickets_categorie` ON `tickets_categorie`.`tickets_categorie_id` = `tickets`.`tickets_categoria`
// LEFT JOIN `tickets_category` ON `tickets_category`.`tickets_category_id` = `tickets`.`tickets_category`
// LEFT JOIN `clienti` ON `clienti`.`clienti_id` = `tickets`.`tickets_cliente`
// LEFT JOIN `tickets_priority` ON `tickets_priority`.`tickets_priority_id` = `tickets`.`tickets_priority`
// LEFT JOIN `tickets_stati` ON `tickets_stati`.`tickets_stati_id` = `tickets`.`tickets_stato`
// LEFT JOIN `tickets_status` ON `tickets_status`.`tickets_status_id` = `tickets`.`tickets_status`
// LEFT JOIN `tickets_tecnici` ON `tickets_tecnici`.`tickets_tecnici_id` = `tickets`.`tickets_tecnici`
// LEFT JOIN `tecnici` ON `tecnici`.`tecnici_id` = `tickets`.`tickets_tecnico`
// LEFT JOIN `tickets_type` ON `tickets_type`.`tickets_type_id` = `tickets`.`tickets_type`']), true);

$query_logs = $this->db->get_query_logs();
//debug($query_logs,true);
$query_raggruppate = raggruppa_query($query_logs);

$suggested_indexes = suggestIndexes(array_keys($query_raggruppate));


?>

<style type="text/css">
	#codeigniter-profiler {
		clear: both;
		background: #222;
		padding: 0 5px;
		font-family: Helvetica, sans-serif;
		font-size: 10px !important;
		line-height: 12px;
		position: absolute;
		width: auto;
		min-width: 74em;
		max-width: 90%;
		z-index: 1000;
		display: none;
	}

	#codeigniter-profiler.bottom-right {
		position: fixed;
		bottom: 0;
		right: 0;
		-webkit-border-top-left-radius: 7px;
		-moz-border-radius-topleft: 7px;
		border-top-left-radius: 7px;
		-webkit-box-shadow: -1px -1px 10px #999;
		-moz-box-shadow: -1px -1px 10px #999;
		box-shadow: -1px -1px 10px #999;
	}

	#codeigniter-profiler.bottom-left {
		position: fixed;
		bottom: 0;
		left: 0;
		-webkit-border-top-right-radius: 7px;
		-moz-border-radius-topright: 7px;
		border-top-right-radius: 7px;
		-webkit-box-shadow: 1px -1px 10px #999;
		-moz-box-shadow: 1px -1px 10px #999;
		box-shadow: 1px -1px 10px #999;
	}

	#codeigniter-profiler.top-left {
		position: fixed;
		top: 0;
		left: 0;
		-webkit-border-bottom-right-radius: 7px;
		-moz-border-radius-bottomright: 7px;
		border-bottom-right-radius: 7px;
		-webkit-box-shadow: 1px 1px 10px #999;
		-moz-box-shadow: 1px 1px 10px #999;
		box-shadow: 1px 1px 10px #999;
	}

	#codeigniter-profiler.top-right {
		position: fixed;
		top: 0;
		right: 0;
		-webkit-border-bottom-left-radius: 7px;
		-moz-border-radius-bottomleft: 7px;
		border-bottom-left-radius: 7px;
		-webkit-box-shadow: -1px 1px 10px #999;
		-moz-box-shadow: -1px 1px 10px #999;
		box-shadow: -1px 1px 10px #999;
	}

	#codeigniter-profiler.bottom {
		position: fixed;
		bottom: 0;
		left: 0;
		right: 0;
		width: 100%;
		max-width: 99.5%;
		-webkit-box-shadow: 0px 1px 10px #999;
		-moz-box-shadow: 0px 1px 10px #999;
		box-shadow: 0px 1px 10px #999;
	}

	#codeigniter-profiler.top {
		position: fixed;
		top: 0;
		left: 0;
		right: 0;
		width: 100%;
		max-width: 99.5%;
		-webkit-box-shadow: -1px 1px 10px #999;
		-moz-box-shadow: -1px 1px 10px #999;
		box-shadow: -1px 1px 10px #999;
	}

	.ci-profiler-box {
		padding: 10px;
		margin: 0 0 10px 0;
		max-height: 400px;
		overflow: auto;
		color: #fff;
		font-family: Monaco, 'Lucida Console', 'Courier New', monospace;
		font-size: 11px !important;
	}

	.ci-profiler-box h2 {
		font-family: Helvetica, sans-serif;
		font-weight: bold;
		font-size: 16px !important;
		padding: 0;
		line-height: 2.0;
	}

	#ci-profiler-vars a {
		text-decoration: none;
	}

	#ci-profiler-menu a:link,
	#ci-profiler-menu a:visited {
		display: inline-block;
		padding: 7px 0;
		margin: 0;
		color: #ccc;
		text-decoration: none;
		font-weight: lighter;
		cursor: pointer;
		text-align: center;
		width: 13%;
		border-bottom: 4px solid #444;
	}

	#ci-profiler-menu a:hover,
	#ci-profiler-menu a.current {
		background-color: #222;
		border-color: #999;
	}

	#ci-profiler-menu a span {
		display: block;
		font-weight: bold;
		font-size: 16px !important;
		line-height: 1.2;
	}

	#ci-profiler-menu-time span,
	#ci-profiler-benchmarks h2 {
		color: #B72F09;
	}

	#ci-profiler-menu-memory span,
	#ci-profiler-memory h2 {
		color: #953FA1;
	}

	#ci-profiler-menu-queries span,
	#ci-profiler-queries h2 {
		color: #3769A0;
	}

	#ci-profiler-menu-eloquent span,
	#ci-profiler-eloquent h2 {
		color: #f4726d;
	}

	#ci-profiler-menu-vars span,
	#ci-profiler-vars h2 {
		color: #D28C00;
	}

	#ci-profiler-menu-files span,
	#ci-profiler-files h2 {
		color: #5a8616;
	}

	#ci-profiler-menu-console span,
	#ci-profiler-console h2 {
		color: #5a8616;
	}

	#codeigniter-profiler table {
		width: 100%;
	}

	#codeigniter-profiler table.main td {
		padding: 7px 15px;
		text-align: left;
		vertical-align: top;
		color: #aaa;
		border-bottom: 1px dotted #444;
		line-height: 1.5;
		background: #101010 !important;
		font-size: 12px !important;
	}

	#codeigniter-profiler table.main tr:hover td {
		background: #292929 !important;
	}

	#codeigniter-profiler table.main code {
		font-family: inherit;
		padding: 0;
		background: transparent;
		border: 0;
		color: #fff;
	}

	#codeigniter-profiler table .hilight {
		color: #FFFD70 !important;
		width: 250px;
	}

	#codeigniter-profiler table .faded {
		color: #aaa !important;
	}

	#codeigniter-profiler table .small {
		font-size: 10px;
		letter-spacing: 1px;
		font-weight: lighter;
	}

	#ci-profiler-menu-exit {
		_background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAIhSURBVDjLlZPrThNRFIWJicmJz6BWiYbIkYDEG0JbBiitDQgm0PuFXqSAtKXtpE2hNuoPTXwSnwtExd6w0pl2OtPlrphKLSXhx07OZM769qy19wwAGLhM1ddC184+d18QMzoq3lfsD3LZ7Y3XbE5DL6Atzuyilc5Ciyd7IHVfgNcDYTQ2tvDr5crn6uLSvX+Av2Lk36FFpSVENDe3OxDZu8apO5rROJDLo30+Nlvj5RnTlVNAKs1aCVFr7b4BPn6Cls21AWgEQlz2+Dl1h7IdA+i97A/geP65WhbmrnZZ0GIJpr6OqZqYAd5/gJpKox4Mg7pD2YoC2b0/54rJQuJZdm6Izcgma4TW1WZ0h+y8BfbyJMwBmSxkjw+VObNanp5h/adwGhaTXF4NWbLj9gEONyCmUZmd10pGgf1/vwcgOT3tUQE0DdicwIod2EmSbwsKE1P8QoDkcHPJ5YESjgBJkYQpIEZ2KEB51Y6y3ojvY+P8XEDN7uKS0w0ltA7QGCWHCxSWWpwyaCeLy0BkA7UXyyg8fIzDoWHeBaDN4tQdSvAVdU1Aok+nsNTipIEVnkywo/FHatVkBoIhnFisOBoZxcGtQd4B0GYJNZsDSiAEadUBCkstPtN3Avs2Msa+Dt9XfxoFSNYF/Bh9gP0bOqHLAm2WUF1YQskwrVFYPWkf3h1iXwbvqGfFPSGW9Eah8HSS9fuZDnS32f71m8KFY7xs/QZyu6TH2+2+FAAAAABJRU5ErkJggg==) 0% 0% no-repeat;
		padding-left: 20px;
		position: absolute;
		right: 5px;
		top: 10px;
		display: none;
	}

	#ci-profiler-menu-open {
		_background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAc9JREFUeNp8kr9O21AUxr9j06iqhGSBiqKypAMrIjtLBuaWNzBPEPIEJEvHInZEeAMyVJU6VMnUsY3asamULk1L/mAHSGzg3sO5l7hyAsmRvjj2Pb/vu+faYGbMq5sfL+oi76k1w1l2noEBRSyqLjJwML8O7K8mf0EPyLgQ0Wy6L2AVty4QPUNmu09P7cDU0qOtf132EQksIEeu1aKaGuHmy4qP60yVg+fQQQbKKFxqmLXw/Wtv4Qjx55c+lFPlyIGWVB07kk7g2GmrIRWgUNdjqq2++1VKj2AN4g/rOdb4Js2eFgM2cEyBjuBZEyvYqx7hdO2ktTd1BurKLfIteTY9ngB32OVrOhNQTOV+LAYjK7+zs/FbsPL/M1BD960KXZlXDAJJCUU92tJXyKuAGrovb7Mn6srzf2LWRXHqEHXo5JQBJ1IXVoeqQ1g7bhV4gIr+a0FgZAB4UwZKEjkBQ6oliXz50Jj91CpjjAp4zmvUFxSogaQP0JbEXR4iz5eUz35sNZPGV99/llNcLfljD1HSauZweExtm5gCk/qzuZFL3R7N7AAlfU5N7mFrpjFdh5Prnuym8ehDEtDMuy96M2lqptINbNYr8ryd/pDuBRgABwcgCJ3Gp98AAAAASUVORK5CYII%3D) 0% 0% no-repeat;
		z-index: 10000;
	}

	#ci-profiler-menu-open.bottom-right {
		position: fixed;
		right: -2px;
		bottom: 22px;
	}

	#ci-profiler-menu-open.bottom-left {
		position: fixed;
		left: 10px;
		bottom: 22px;
	}

	#ci-profiler-menu-open.top-left {
		position: fixed;
		left: 10px;
		top: 22px;
	}

	#ci-profiler-menu-open.top-right {
		position: fixed;
		right: -2px;
		top: 22px;
	}
</style>

<script type="text/javascript">
	var ci_profiler_bar = {

		// current toolbar section thats open
		current: null,

		// current vars and config section open
		currentvar: null,

		// current config section open
		currentli: null,

		// toggle a toolbar section
		show: function (obj, el) {
			if (obj == ci_profiler_bar.current) {
				ci_profiler_bar.off(obj);
				ci_profiler_bar.current = null;
			} else {
				ci_profiler_bar.off(ci_profiler_bar.current);
				ci_profiler_bar.on(obj);
				ci_profiler_bar.remove_class(ci_profiler_bar.current, 'current');
				ci_profiler_bar.current = obj;
				//ci_profiler_bar.add_class(el, 'current');
			}
		},

		// turn an element on
		on: function (obj) {
			if (document.getElementById(obj) != null)
				document.getElementById(obj).style.display = '';
		},

		// turn an element off
		off: function (obj) {
			if (document.getElementById(obj) != null)
				document.getElementById(obj).style.display = 'none';
		},

		// toggle an element
		toggle: function (obj) {
			if (typeof obj == 'string')
				obj = document.getElementById(obj);

			if (obj)
				obj.style.display = obj.style.display == 'none' ? '' : 'none';
		},

		// open the toolbar
		open: function () {
			document.getElementById('ci-profiler-menu-open').style.display = 'none';
			document.getElementById('codeigniter-profiler').style.display = 'block';
			this.set_cookie('open');
		},

		// close the toolbar
		close: function () {
			document.getElementById('codeigniter-profiler').style.display = 'none';
			document.getElementById('ci-profiler-menu-open').style.display = 'block';
			this.set_cookie('closed');
		},

		// Add class to element
		add_class: function (obj, a_class) {
			alert(obj);
			document.getElementById(obj).className += " " + a_class;
		},

		// Remove class from element
		remove_class: function (obj, r_class) {
			if (obj != undefined) {
				document.getElementById(obj).className = document.getElementById(obj).className.replace(/\bclass\b/, '');
			}
		},

		read_cookie: function () {
			var nameEQ = "Profiler=";
			var ca = document.cookie.split(';');
			for (var i = 0; i < ca.length; i++) {
				var c = ca[i];
				while (c.charAt(0) == ' ') c = c.substring(1, c.length);
				if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
			}
			return null;
		},

		set_cookie: function (value) {
			var date = new Date();
			date.setTime(date.getTime() + (365 * 24 * 60 * 60 * 1000));
			var expires = "; expires=" + date.toGMTString();

			document.cookie = "Profiler=" + value + expires + "; path=/";
		},

		set_load_state: function () {
			var cookie_state = this.read_cookie();

			if (cookie_state == 'open') {
				this.open();
			} else {
				this.close();
			}
		},

		toggle_data_table: function (obj) {
			if (typeof obj == 'string') {
				obj = document.getElementById(obj + '_table');
			}

			if (obj) {
				obj.style.display = obj.style.display == 'none' ? '' : 'none';
			}
		}
	};

	window.onload = function () {
		ci_profiler_bar.set_load_state();
	}
</script>

<!--<a href="#" id="ci-profiler-menu-open" class="<?php echo $bar_location ?>" onclick="ci_profiler_bar.open(); return false;" style="width: 2em">&nbsp;</a>-->

<div id="codeigniter-profiler" class="<?php echo $bar_location ?>">

	<div id="ci-profiler-menu">

		<!-- Console -->
		<?php if (isset($sections['console'])): ?>
			<a href="#" id="ci-profiler-menu-console"
				onclick="ci_profiler_bar.show('ci-profiler-console', 'ci-profiler-menu-console'); return false;">
				<span>
					<?php echo is_array($sections['console']) ? $sections['console']['log_count'] + $sections['console']['memory_count'] : 0 ?>
				</span>
				Console
			</a>
		<?php endif; ?>

		<!-- Benchmarks -->
		<?php if (isset($sections['benchmarks'])): ?>
			<a href="#" id="ci-profiler-menu-time"
				onclick="ci_profiler_bar.show('ci-profiler-benchmarks', 'ci-profiler-menu-time'); return false;">
				<span>
					<?php echo $this->benchmark->elapsed_time('total_execution_time_start', 'total_execution_time_end') ?> s
				</span>
				TIME
			</a>
			<a href="#" id="ci-profiler-menu-memory"
				onclick="ci_profiler_bar.show('ci-profiler-memory', 'ci-profiler-menu-memory'); return false;">
				<span>
					<?php echo (!function_exists('memory_get_usage')) ? '0' : round(memory_get_usage() / 1024 / 1024, 2) . ' Mb' ?>
				</span>
				RAM
			</a>
		<?php endif; ?>

		<!-- Queries -->
		<?php if (isset($sections['queries'])): ?>
			<a href="#" id="ci-profiler-menu-queries"
				onclick="ci_profiler_bar.show('ci-profiler-queries', 'ci-profiler-menu-queries'); return false;">
				<span>
					<?php echo is_array($sections['queries']) ? (count($sections['queries']) - 1) : 0 ?> sql
				</span>
				DBMS
			</a>
		<?php endif; ?>

		<!-- Queries -->
		<?php if (isset($sections['layout_boxes'])): ?>
			<a href="#" id="ci-profiler-menu-layout_boxes"
				onclick="ci_profiler_bar.show('ci-profiler-layout_boxes', 'ci-profiler-menu-layout_boxes'); return false;">
				<span>
					<?php echo is_array($sections['layout_boxes']) ? (count($sections['layout_boxes']) - 1) : 0 ?> BOXES
				</span>
				LAYOUT
			</a>
		<?php endif; ?>

		<!-- Eloquent -->
		<?php if (isset($sections['eloquent'])): ?>
			<a href="#" id="ci-profiler-menu-eloquent"
				onclick="ci_profiler_bar.show('ci-profiler-eloquent', 'ci-profiler-menu-eloquent'); return false;">
				<span>
					<?php echo is_array($sections['eloquent']) ? (count($sections['eloquent']) - 1) : 0 ?> Eloquent
				</span>
				ILLUM\DB
			</a>
		<?php endif; ?>

		<!-- Vars and Config -->
		<?php if (isset($sections['http_headers']) || isset($sections['get']) || isset($sections['config']) || isset($sections['post']) || isset($sections['uri_string']) || isset($sections['controller_info'])): ?>
			<a href="#" id="ci-profiler-menu-vars"
				onclick="ci_profiler_bar.show('ci-profiler-vars', 'ci-profiler-menu-vars'); return false;">
				<span>
					<?php echo is_array($sections['controller_info']) ? (count($sections['controller_info']) - 1) : count($sections['config']) ?>
					vars
				</span>
				DATA
			</a>
		<?php endif; ?>

		<!-- Files -->
		<?php if (isset($sections['files'])): ?>
			<a href="#" id="ci-profiler-menu-files"
				onclick="ci_profiler_bar.show('ci-profiler-files', 'ci-profiler-menu-files'); return false;">
				<span>
					<?php echo is_array($sections['files']) ? count($sections['files']) : 0 ?> php
				</span>
				FILES
			</a>
		<?php endif; ?>

		<a href="#" id="ci-profiler-menu-exit" onclick="ci_profiler_bar.close(); return false;"
			style="width: 2em; height: 2.1em"></a>
	</div>

	<?php if (count($sections) > 0): ?>

		<!-- Console -->
		<?php if (isset($sections['console'])): ?>
			<div id="ci-profiler-console" class="ci-profiler-box" style="display: none">
				<h2>Console</h2>

				<?php if (is_array($sections['console'])): ?>

					<table class="main">
						<?php foreach ($sections['console']['console'] as $log): ?>

							<?php if ($log['type'] == 'log'): ?>
								<tr>
									<td>
										<?php echo $log['type'] ?>
									</td>
									<td class="faded">
										<pre><?php echo $log['data'] ?></pre>
									</td>
									<td></td>
								</tr>
							<?php elseif ($log['type'] == 'memory'): ?>
								<tr>
									<td>
										<?php echo $log['type'] ?>
									</td>
									<td>
										<em>
											<?php echo $log['data_type'] ?>
										</em>:
										<?php echo $log['name']; ?>
									</td>
									<td class="hilight" style="width: 9em">
										<?php echo $log['data'] ?>
									</td>
								</tr>
							<?php endif; ?>
						<?php endforeach; ?>
					</table>

				<?php else: ?>

					<?php echo $sections['console']; ?>

				<?php endif; ?>
			</div>
		<?php endif; ?>

		<!-- Memory -->
		<?php if (isset($sections['console'])): ?>
			<div id="ci-profiler-memory" class="ci-profiler-box" style="display: none">
				<h2>RAM</h2>

				<?php if (is_array($sections['console'])): ?>

					<table class="main">
						<?php foreach ($sections['console']['console'] as $log): ?>

							<?php if ($log['type'] == 'memory'): ?>
								<tr>
									<td>
										<?php echo $log['type'] ?>
									</td>
									<td>
										<em>
											<?php echo $log['data_type'] ?>
										</em>:
										<?php echo $log['name']; ?>
									</td>
									<td class="hilight" style="width: 9em">
										<?php echo $log['data'] ?>
									</td>
								</tr>
							<?php endif; ?>
						<?php endforeach; ?>
					</table>

				<?php else: ?>

					<?php echo $sections['console']; ?>

				<?php endif; ?>
			</div>
		<?php endif; ?>

		<!-- Benchmarks -->
		<?php if (isset($sections['benchmarks'])): ?>
			<div id="ci-profiler-benchmarks" class="ci-profiler-box" style="display: none">
				<h2>Benchmarks</h2>

				<?php if (is_array($sections['benchmarks'])): ?>

					<table class="main">
						<?php foreach ($sections['benchmarks'] as $key => $val): ?>
							<tr>
								<td>
									<?php echo $key ?>
								</td>
								<td class="hilight">
									<?php echo $val ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</table>

				<?php else: ?>

					<?php echo $sections['benchmarks']; ?>

				<?php endif; ?>
			</div>
		<?php endif; ?>

		<!-- Queries -->
		<?php if (isset($sections['queries'])): ?>
			<div id="ci-profiler-queries" class="ci-profiler-box" style="display: none">
				<h2>SQL</h2>

				<?php if (is_array($sections['queries'])): ?>

					<!-- <table class="main" cellspacing="0">
				<?php foreach ($sections['queries'] as $key => $queries): ?>
					<?php foreach ($queries as $time => $query): ?>
						<tr><td class="hilight"><?php echo $time ?></td><td><?php echo $query ?></td></tr>
					<?php endforeach; ?>
				<?php endforeach; ?>
				</table> -->
					<table class="main" cellspacing="0">
						<tr>
							<td class="hilight">Indici mancanti e suggeriti: </td>
							<td><?php
							echo implode(', ', array_map(function ($s) {
								return $s['column'] . " (<a class=\"js_link_ajax\" href=\"" . base_url('core-entities/field/create_index/' . $s['column']) . "\">add</a>)";
							}, $suggested_indexes));
							?></td>
							<td>(<?php echo count($suggested_indexes); ?>)</td>
						</tr>
						<?php foreach ($query_raggruppate as $query => $data): ?>

							<tr>
								<td class="hilight">
									<?php if ('Total Query Execution Time' == trim($query)): ?>
										tot.
										<?php echo $data['totalExecutionTime'] ?>

									<?php else: ?>
										tot.
										<?php echo $data['totalExecutionTime'] ?> / avg.
										<?php echo $data['averageExecutionTime'] ?> / max
										<?php echo $data['maxExecutionTime'] ?>
									<?php endif; ?>

								</td>
								<td>
									<?php echo $query ?>
								</td>
								<td>
									<?php if ('Total Query Execution Time' != trim($query)): ?>
										<button class="show-locations">Mostra backtrace (
											<?php echo count($data['locations']); ?>)
										</button>
										<ul class="locations-list" style="display: none;">
											<?php foreach ($data['locations'] as $location): ?>
												<li>File:
													<?php echo $location['file'] ?>, Linea:
													<?php echo $location['line'] ?>
												</li>
											<?php endforeach; ?>
										</ul>
									<?php endif; ?>
								</td>
							</tr>

						<?php endforeach; ?>
					</table>

				<?php else: ?>

					<?php echo $sections['queries']; ?>

				<?php endif; ?>
			</div>
		<?php endif; ?>
		<?php if (isset($sections['layout_boxes'])): ?>
			<div id="ci-profiler-layout_boxes" class="ci-profiler-box" style="display: none">
				<h2>LAYOUT BOXES</h2>


				<table class="main" cellspacing="0">
					<?php foreach ($sections['layout_boxes'] as $title => $lb_data): ?>
						<tr>
							<td class="hilight">
								<?php echo number_format($lb_data['time'], 5) ?>
							</td>
							<td>
								<strong>
									<?php echo $title ?>
								</strong> (
								<?php echo $lb_data['container']; ?>, id:
								<?php echo $lb_data['layouts_id']; ?>) -
								<a
									href="<?php echo base_url('main/layout/' . $lb_data['layouts_id'] . '/' . $value_id . '?_profiler=1'); ?>">View</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>


			</div>
		<?php endif; ?>

		<!-- Eloquent -->
		<?php if (isset($sections['eloquent'])): ?>
			<div id="ci-profiler-eloquent" class="ci-profiler-box" style="display: none">
				<h2>Eloquent</h2>

				<?php if (is_array($sections['eloquent'])): ?>

					<table class="main" cellspacing="0">
						<?php foreach ($sections['eloquent'] as $key => $queries): ?>
							<?php foreach ($queries as $time => $query): ?>
								<tr>
									<td class="hilight">
										<?php echo $time ?>
									</td>
									<td>
										<?php echo $query ?>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endforeach; ?>
					</table>

				<?php else: ?>

					<?php echo $sections['eloquent']; ?>

				<?php endif; ?>
			</div>
		<?php endif; ?>

		<!-- Vars and Config -->
		<?php if (isset($sections['http_headers']) || isset($sections['get']) || isset($sections['config']) || isset($sections['post']) || isset($sections['uri_string']) || isset($sections['controller_info']) || isset($sections['userdata'])): ?>
			<div id="ci-profiler-vars" class="ci-profiler-box" style="display: none">

				<!-- View Data -->
				<?php if (isset($sections['view_data'])): ?>
					<a href="#" onclick="ci_profiler_bar.toggle_data_table('view_data'); return false;">
						<h2>DATA</h2>
					</a>

					<?php if (is_array($sections['view_data'])): ?>

						<table class="main" id="view_data_table">
							<?php foreach ($sections['view_data'] as $key => $val): ?>
								<?php
								// Rimuovi i tag <script> e <style> dal contenuto
								$val = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $val);
								$val = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $val);
								// // Esegui l'escape del contenuto rimanente
								// $val = htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
								?>
								<tr>
									<td class="hilight">
										<?php echo $key ?>
									</td>
									<td>
										<code><?php echo ($val); ?></code>
									</td>
								</tr>
							<?php endforeach; ?>
						</table>

					<?php endif; ?>
				<?php endif; ?>

				<!-- User Data -->
				<?php if (isset($sections['userdata'])): ?>
					<a href="#" onclick="ci_profiler_bar.toggle_data_table('userdata'); return false;">
						<h2>SESSION</h2>
					</a>

					<?php if (is_array($sections['userdata']) && count($sections['userdata'])): ?>

						<table class="main" id="userdata_table">
							<?php foreach ($sections['userdata'] as $key => $val): ?>
								<tr>
									<td class="hilight">
										<?php echo $key ?>
									</td>
									<td>
										<?php echo $val ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</table>
					<?php endif; ?>
				<?php endif; ?>

				<!-- The Rest -->
				<?php foreach (array('get', 'post', 'uri_string', 'controller_info', 'headers', 'config') as $section): ?>

					<?php if (isset($sections[$section])): ?>

						<?php $append = ($section == 'get' || $section == 'post') ? '_data' : '' ?>
						<a href="#" onclick="ci_profiler_bar.toggle_data_table('<?php echo $section ?>'); return false;">
							<h2>
								<?php echo lang('profiler_' . $section . $append) ?>
							</h2>
						</a>



						<table class="main" id="<?php echo $section ?>_table">
							<?php if (is_array($sections[$section])): ?>
								<?php foreach ($sections[$section] as $key => $val): ?>
									<tr>
										<td class="hilight">
											<?php echo $key ?>
										</td>
										<td>
											<?php echo htmlspecialchars($val) ?>
										</td>
									</tr>
								<?php endforeach; ?>
							<?php else: ?>
								<tr>
									<td>
										<?php echo $sections[$section]; ?>
									</td>
								</tr>
							<?php endif; ?>
						</table>
					<?php endif; ?>

				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<!-- Files -->
		<?php if (isset($sections['files'])): ?>
			<div id="ci-profiler-files" class="ci-profiler-box" style="display: none">
				<h2>LOAD</h2>

				<?php if (is_array($sections['files'])): ?>

					<table class="main">
						<?php foreach ($sections['files'] as $key => $val): ?>
							<tr>
								<td class="hilight">
									<?php echo preg_replace("/\/.*\//", "", $val) ?>
									<br /><span class="faded small">
										<?php echo str_replace(FCPATH, '', $val) ?>
									</span>
								</td>
							</tr>
						<?php endforeach; ?>
					</table>

				<?php else: ?>

					<?php echo $sections['files']; ?>

				<?php endif; ?>
			</div>
		<?php endif; ?>


	<?php else: ?>

		<p class="ci-profiler-box">
			<?php echo lang('profiler_no_profiles') ?>
		</p>

	<?php endif; ?>

</div>
<script>
	$(document).ready(function () {
		$(".show-locations").on("click", function () {
			var locationsList = $(this).next(".locations-list");
			var tdElement = $(this).closest('td');

			if (locationsList.is(":visible")) {
				// Nascondi l'elenco delle locations e rimuovi la larghezza
				locationsList.hide();
				tdElement.css('width', ''); // Rimuovi la larghezza
				$(this).text("Mostra backtrace");
			} else {
				// Mostra l'elenco delle locations e impostala a 600px
				locationsList.show();
				tdElement.css('width', '600px');
				$(this).text("Nascondi");
			}
		});
	});
</script>


<!-- /codeigniter_profiler -->