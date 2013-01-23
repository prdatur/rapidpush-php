<?php
// Require needed class.
require 'RapidPush.class.php';

// Creating our API-Object, please set your api key before trying.
$api = new RapidPush('YOUR_API_KEY');

// Example of getting groups.
$response = $api->get_groups();
echo "You have the following groups defined:\n";
foreach ($response['data'] AS $group) {
	echo $group . "\n";
}

// Example of scheduling.
$response = $api->schedule((time()+60), 'test', 'This is the scheduled test content');
if ($response['code'] === 200) {
	echo "Notification scheduled successfully\n";
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

// Example of sending notifications including error handlings.
$response = $api->notify('test', 'This is the test content');
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