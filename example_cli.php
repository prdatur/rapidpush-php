#!/usr/bin/php
<?php
// Require needed class.
require 'RapidPush.class.php';

// Define cli options.
$options = getopt('a:t:m:c::g::p::s::', array(
	'apikey:',
	'title:',
	'message:',
	'priority::',
	'channel::',
	'category::',
	'group::',
	'schedule::',
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
		case 'schedule':	$ok = 's'; break;
	}
	if (!empty($ok)) {
		$options[$ok] = $v;
	}
}

// Check if all required parameters are present.
if (empty($options['a']) || empty($options['t']) || empty($options['m'])) {
	display_help('Missing parameters');
}

// Validate and set default params only to non broadcast notifications.
if (!isset($options['channel'])) {
	// Fill empty options.
	if (!isset($options['p']) || (empty($options['p']) && $options['p'] !== "0")) {
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

	// Check for valid time.
	if (!empty($options['s'])) {

		// Check if we can parse the provided time.
		$timestamp = strtotime($options['s']);
		if ($timestamp === false) {
			display_help('Invalid schedule date');
		}
		// Validate that the provided time is in the future.
		if (strtotime(date("Y-m-d H:i:00", $timestamp)) <= strtotime(date("Y-m-d H:i:00"))) {
			display_help('Schedule date needs to be in the future.');
		}
	}
	else {
		$options['s'] = "";
	}
}
else {
	$options['s'] = "";
}

// Creating our API-Object, please set your api key before trying.
$api = new RapidPush($options['a']);

// Send or shedule a normal notification if we have not a channel provided, else send a broadcast notification.
if (!isset($options['channel'])) {
	// Example of sending and scheduling notifications including error handlings.
	if (!empty($options['s'])) {
		// Timestamp is not empty, so we need to schedule.
		$response = $api->schedule($timestamp, $options['t'], $options['m'], $options['p'], $options['c'], $options['g']);
	}
	else {
		// Timestamp not provided, direct notify the devices.
		$response = $api->notify($options['t'], $options['m'], $options['p'], $options['c'], $options['g']);
	}
}
else {
	$response = $api->broadcast($options['t'], $options['m'], $options['channel']);
}
// If we have provided multiple api-keys, we need to handle the reponse different.
if (!isset($response['code'])) {
	// Loop through all provided api keys and get the response for that key.
	foreach ($response AS $response_key => $resp) {
		echo "Response for key \"" . $response_key . "\": " . display_response($resp, $options['s']);
	}
}
// Just single API-Key call.
else {
	echo display_response($response, $options['s']);
}

/**
 * Display the response.
 * 
 * @param array $response
 *   The api response array.
 * @param boolean $scheduled
 *   If the message was scheduled or not (optional, default = false)
 * 
 * @return string The human readable response message.
 */
function display_response($response, $scheduled = false) {
	if ($response['code'] === 200) {
		if (!empty($scheduled)) {
			return "Notification scheduled successfully\n";
		}
		else {
			return "Notification send successfully\n";
		}
	}
	else {
		switch($response['code']) {
			case 405:
				return $response['desc'] . "\n";
				break;
			case 407:
				return "Could not insert notification\n";
				break;
			case 408:
				return "Invalid API-Key\n";
				break;
			case 409:
				return "Invalid command\n";
				break;
			case 410:
				return "API rate limit exceeded\n";
				break;
			default:
				return "Could not send notification, unknown error.\n";
				break;
		}
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
	echo "-a key, --apikey=\"key\"\t\t\tThe API-Key, multiple api keys are comma seperated\n";
	echo "-t title, --title=\"title\"\t\tThe notification title\n";
	echo "-m message, --message=\"message\"\t\tThe notification message\n";
	echo "\n";
	echo "Optional options:\n";
	echo "-p=priority, --priority=\"priority\"\tThe priority, valid values are integers from 0 to 6 (optional, default = 2).\n";
	echo "-g=group, --group=\"group\"\t\tThe device group, which you have configurated at our website (optional, default = '').\n";
	echo "-c=category, --category=\"category\"\tThe category (default = 'default').\n";
	echo "-s=\"schedule time\", --category=\"schedule time\"\tThe schedule time, if provided the notification will be scheduled. format = Y-m-d H:i:00 (ex: 2013-01-25 23:34:00) (default = '').\n";
	echo "--channel=\"the_channel\"\tThe broadcast channel, if provided the notification will be send to the given channel and not to your phones.\n";
	exit;
}