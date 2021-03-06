<?php
/**
 * This is the default subcontroller for vulnerability
 * details
 * 
 * @author Josh Bauer <joshbauer3@gmail.com>
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 * @version 2013.08.29
 */

/**
 * Require the Vuln_details class
 */ 
require_once($approot . 'lib/class.Vuln_detail.php');
require_once($approot . 'lib/class.Risk.php');

$vuln_id = isset($_GET['id']) ? intval($_GET['id']) : '';
$vuln_detail= new Vuln_detail($vuln_id);
$risk = new Risk($vuln_detail->get_risk_id());

include_once($templates. 'admin_headers.tpl.php');
include_once($templates . 'vuln_details.tpl.php');

?> 