#!/usr/bin/env php
<?php
/**
 * Utility script to populate the elastic search indexes
 *
 */

// Elastic search config
define('ES_DEFAULT_HOST', 'http://localhost:9200');
define('ES_INDEX', 'bakery');
define('SOURCE_DIR', 'src');


function main($argv) {
	if (!empty($argv[1])) {
		define('ES_HOST', $argv[1]);
	} else {
		define('ES_HOST', ES_DEFAULT_HOST);
	}

	$directory = new RecursiveDirectoryIterator(SOURCE_DIR);
	$recurser = new RecursiveIteratorIterator($directory);
	$matcher = new RegexIterator($recurser, '/\.rst/');

	foreach ($matcher as $file) {
		updateIndex($file);
	}
	echo "\nIndex update complete\n";
}

function updateIndex($file) {
	$fileData = readFileData($file);
	if (!$fileData) {
		echo "[WARNING] Not indexed:" . $file . "\n";
		return false;
	}
	$filename = $file->getPathName();
	list($filename) = explode('.', $filename);

	$base = str_replace(SOURCE_DIR . '/', '', $filename);

	$path = $base . '.html';
	$id = trim($base, '/');
	list($type, $id) = explode('/', $id, 2);

	$id = str_replace('/', '-', $id);
	$id = $type . '/' . trim($id, '-');

	$url = implode('/', array(ES_HOST, ES_INDEX, $id));

	$data = array(
		'contents' => $fileData['contents'],
		'title' => $fileData['title'],
		'category' => $fileData['category'],
		'url' => $path,
	);

	$data = json_encode($data);
	$size = strlen($data);

	$fh = fopen('php://memory', 'rw');
	fwrite($fh, $data);
	rewind($fh);

	echo "Sending request:\n\tfile: $file\n\turl: $url\n";

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_PUT, true);
	curl_setopt($ch, CURLOPT_INFILE, $fh);
	curl_setopt($ch, CURLOPT_INFILESIZE, $size);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$response = curl_exec($ch);
	$metadata = curl_getinfo($ch);

	if ($metadata['http_code'] > 400 || !$metadata['http_code']) {
		echo "[ERROR] Failed to complete request.\n";
		var_dump($response);
		exit(2);
	}

	curl_close($ch);
	fclose($fh);

	echo "Sent $file\n";
}

function readFileData($file) {
	$contents = file_get_contents($file);

	// extract the title and guess that things underlined with # or == and first in the file
	// are the title.
	preg_match('/^(.*)\n[=#]+\n/', $contents, $matches);
	if (empty($matches)) {
		return false;
	}
	$title = $matches[1];

	// Remove the title from the indexed text.
	$contents = str_replace($matches[0], '', $contents);

	// Remove title markers from the text.
	$contents = preg_replace('/\n[-=~]+\n/', '', $contents);

	preg_match('|:category: ([^\n]+)\n|', $contents, $matches);
	$category = null;
	if (!empty($matches[1])) {
		$category = $matches[1];
	}

	return compact('contents', 'title', 'category');
}

main($argv);