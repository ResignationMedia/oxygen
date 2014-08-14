<?php defined('SYSPATH') || die('No direct script access.');
$group_stats = Profiler::group_stats();
$application_cols = array('min', 'max', 'average', 'current');

$stats = Profiler::application();

echo "\n\n";
foreach ($application_cols as $key) {
	echo str_pad(__($key), 15).': '.number_format($stats[$key]['time'], 6).'s ('.number_format($stats[$key]['memory'] / 1048576, 4).' mB)'."\n";
}
echo "\n\n";
