#!/usr/bin/php
<?php
// Require needed class.
require 'RapidPush.class.php';

// Define cli options.
$options = getopt('a:t:m:c::g::p::', array(
	'apikey:',
	'title:',
	'message:',
	'priority::',
	'category::',
	'group::',
));

// Overwrite short options with long options (long options has higher priorities)
foreach ($options AS $k => $v) {
	$ok = '';
	switch ($k) {
		case 'apikey:':		$ok = 'a'; break;
		case 'title':		$ok = 't'; break;
		case 'message':		$ok = 'm'; break;
		case 'priority':	$ok = 'p'; break;
		case 'category':	$ok = 'c'; break;
		case 'group':		$ok = 'g'; break;
	}
	if (!empty($ok)) {
		$options[$ok] = $v;
	}
}

// Check if all required parameters are present.
if (empty($options['a']) || empty($options['t']) || empty($options['m'])) {
	display_help('Missing parameters');
}

// Fill empty options.
if (!isset($options['p']) || empty($options['p'])) {
	$options['p'] = '';
}
if (!isset($options['c']) || empty($options['c'])) {
	$options['c'] = '';
}
if (!isset($options['g']) || empty($options['g'])) {
	$options['g'] = '';
}

// Validates the parameter.
if (!empty($options['p']) && ((int)$options['p'] < 0 || ($options['p']) > 6)) {
	display_help('Priority can only between 0 and 6');
}

// Creating our API-Object, please set your api key before trying.
$api = new RapidPush($options['a']);

// Example of sending notifications including error handlings.
$response = $api->notify($options['t'], $options['m'], $options['p'], $options['c'], $options['g']);
if ($response['code'] === 200) {
	echo "Notification send successfully\n";
}
else {
	switch($response['code']) {
		case 405:
			echo "Invalid parameter\n";
			break;
		case 407:
			echo "Could not insert notification\n";
			break;
		case 408:
			echo "Invalid API-Key\n";
			break;
		case 409:
			echo "Invalid command\n";
			break;
		case 410:
			echo "API rate limit exceeded\n";
			break;
		default:
			echo "Could not send notification, unknown error.\n";
			break;
	}
}

/**
 * Displays the help.
 * 
 * @param string $error_message
 *   A additional message to be displayed. (optional, default = '')
 */
function display_help($error_message = '') {
	echo "Usage: ./example_cli.php [options]\n\n";
	if (!empty($error_message)) {
		echo "Error: " . $error_message . "\n\n";
	}
	
	echo "Required options:\n";
	echo "-a key, --apikey=\"key\"\t\t\tThe API-Key\n";
	echo "-t title, --title=\"title\"\t\tThe notification title\n";
	echo "-m message, --message=\"message\"\t\tThe notification message\n";
	echo "\n";
	echo "Optional options:\n";
	echo "-p priority, --priority=\"priority\"\tThe priority, valid values are integers from 0 to 6 (optional, default = 2).\n";
	echo "-g group, --group=\"group\"\t\tThe device group, which you have configurated at our website (optional, default = '').\n";
	echo "-c category, --category=\"category\"\tThe category (default = 'default').\n";
	exit;
}