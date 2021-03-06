<?php

	class Raw {
		
		
		function search($query) {
			$parts = opt_explode(' ', $query);
			$where = array();
			
			for ($p = 0; $p < count($parts); $p++) {
				$components = preg_split("/[:=]/", $parts[$p]);
				$negative = false;
				
				if (substr($components[0], 0, 1) == '-') {
					$negative = true;
					$components[0] = substr($components[0], 1);
				}
				
				if (count($components) > 1) {
					if ($components[0] == 'score') {
						$comparison = $negative ? '!=' : '=';
						
						if (substr($components[1], -1) == '+') {
							$comparison = $negative ? '<=' : '>';
							$components[1] = substr($components[1], 0, -1);
						}
					
						if (substr($components[1], -1) == '-') {
							$comparison = $negative ? '>=' : '<';
							$components[1] = substr($components[1], 0, -1);
						}
					
						$where[] = 'score ' . $comparison . ' ' . intval($components[1]);	
					}
					
					if (in_array($components[0], array('browserVersion', 'engineVersion', 'osVersion'))) {
						if ($components[1] == "") {
							$where[] = $components[0] . ($negative ? ' !=' : ' =') . ' ""';	
						} else {
							$where[] = $components[0] . ($negative ? ' NOT' : '') . ' LIKE "' . mysql_real_escape_string($components[1]) . '%"';	
						}
					}

					if (in_array($components[0], array('browserName', 'engineName', 'osName', 'deviceManufacturer', 'deviceType', 'deviceModel', 'useragent'))) {
						if ($components[1] == "") {
							$where[] = $components[0] . ($negative ? ' !=' : ' =') . ' ""';	
						} else {
							$where[] = $components[0] . ($negative ? ' NOT' : '') . ' LIKE "%' . mysql_real_escape_string($components[1]) . '%"';	
						}
					}
				}
				
				else {
					$where[] = 'humanReadable' . ($negative ? ' NOT' : '') . ' LIKE "%' . mysql_real_escape_string($components[0]) . '%"';
				}
			}
	
			$results = array();
			
			$res = mysql_query('
				SELECT
					timestamp, uniqueid, score, humanReadable
				FROM 
					results
				WHERE
					' . implode(' AND ', $where) . '
				ORDER BY 
					timestamp DESC
				LIMIT 100
			');
			
			echo mysql_error();
			
			while ($row = mysql_fetch_object($res)) {
				$results[] = (object) array(
					'uniqueid'		=>	$row->uniqueid,
					'score'			=>	intval($row->score),
					'humanReadable'	=>	$row->humanReadable,
					'ago'			=>	time_ago(strtotime($row->timestamp))
				);
			}
			
			return $results;
		}
		
		function getAll() {
			$results = array();
			
			$res = mysql_query('
				SELECT
					timestamp, uniqueid, score, humanReadable
				FROM 
					results
				ORDER BY 
					timestamp DESC
				LIMIT 100
			');
			
			echo mysql_error();
			
			while ($row = mysql_fetch_object($res)) {
				$results[] = (object) array(
					'uniqueid'		=>	$row->uniqueid,
					'score'			=>	intval($row->score),
					'humanReadable'	=>	$row->humanReadable,
					'ago'			=>	time_ago(strtotime($row->timestamp))
				);
			}
			
			return $results;
		}
		
		function getMine() {
			$results = array();
			
			$res = mysql_query('
				SELECT
					timestamp, uniqueid, score, humanReadable
				FROM 
					results
				WHERE
					ip = "' . mysql_real_escape_string(get_ip_address()) . '"
				ORDER BY 
					timestamp DESC
				LIMIT 100
			');
			
			echo mysql_error();
			
			while ($row = mysql_fetch_object($res)) {
				$results[] = (object) array(
					'uniqueid'		=>	$row->uniqueid,
					'score'			=>	intval($row->score),
					'humanReadable'	=>	$row->humanReadable,
					'ago'			=>	time_ago(strtotime($row->timestamp))
				);
			}
			
			return $results;
		}
		
		
	}