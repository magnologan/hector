<?php
/**
 * This is the default subcontroller.
 * The default is to show a list of hosts.
 * 
 * This page also diverges from the normal use of 
 * Host objects as creating a collection of 254 hosts
 * takes quite a long time.  The direct SQL method
 * is much faster to render.
 * 
 * @author Justin Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 * @version 2013.08.28
 * @todo Move the SQL out of this file and into a utility class
 */

/**
 * Include the DB library
 */
require_once($approot . 'lib/class.Db.php');
require_once($approot . 'lib/class.Host.php');
require_once($approot . 'lib/class.Report.php');
$report = new Report();
$ips_in_ranges = array();

if (isset($_GET['classB'])) {
    $class_C_networks = $report->getClassCinClassB($_GET['classB']);
}
elseif (isset($_GET['classC'])) {
	$hosts = array();
    $host_collection = new Collection('Host', $_GET['classC'], 'get_collection_by_classC');
    if (isset($host_collection->members) && is_array($host_collection->members)) {
    	$hosts = $host_collection->members;
    }
    hector_add_js('browse_classC.js');
}
else {
    $class_Bs = $report->getClassBs();
}

$hosts_by_os = array();
$oses = array();
$hosts_by_os_collection = new Collection('Host', 'AND host_os IS NOT NULL AND host_os != ""');
if (isset($hosts_by_os_collection->members) && is_array($hosts_by_os_collection->members)) {
	$hosts_by_os = $hosts_by_os_collection->members;
	foreach ($hosts_by_os as $host) {
		if (array_key_exists($host->get_os_type(), $oses)) {
			$oses[$host->get_os_type()] += 1;
		}
		elseif (array_key_exists($host->get_os(), $oses)) {
			$oses[$host->get_os()] += 1;
		}
		else {
			if (! $host->get_os_type() == '') $oses[$host->get_os_type()] = 1;
			else $oses[$host->get_os()] = 1;
		}
	}
}

include_once($templates. 'admin_headers.tpl.php');
if (isset($_GET['classC'])) include_once($templates . 'browse_classC.tpl.php');
elseif (isset($_GET['classB'])) include_once($templates . 'browse_classB.tpl.php');
else include_once($templates . 'browse_ip.tpl.php');

?>