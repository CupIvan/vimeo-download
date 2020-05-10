#!/bin/env php
<?php

// ссылка на JSON с информацией о сегментах
$url_list = 'https://3vod-adaptive.akamaized.net/...';

@mkdir('./parts/', 0777);

$st = http_get($url_list);
if ($st) file_put_contents('./master.json', $st);
$json = json_decode($st, true);
if (!$json) die("ERROR JSON");

$json['base_url'] = clear_dots(preg_replace('#[^/]+$#', '', $url_list).$json['base_url']);

download('video', $json);
download('audio', $json);



/** скачивание дорожки */
function download($type, $json)
{
	foreach ($json[$type] as $i => $a)
	{
		@mkdir($dir="./parts/$i/$type/", 0777, true);

		$baseUrl = $a['base_url'];

		echo "Save $type$i init.mp4... ";
		$data = base64_decode($a['init_segment']);
		file_put_contents($dir."../$type/init.mp4", $data);

		$iPart = 0; $totalParts = count($a['segments']);
		foreach ($a['segments'] as $a)
		{
			$iPart++;
			$partName = "$type$i $iPart";
			echo "Downloading $partName/$totalParts ".$a['url']."...";
			$url = clear_dots($json['base_url'].$baseUrl.$a['url']);
			$data = http_get($url, $partName);
			if (!$data) echo "FAIL\n";
			else
			{
				if (file_exists($dir.$a['url'])) echo "SKIP\n";
				else
				{
					echo "OK\n";
					file_put_contents($dir.$a['url'], $data);
				}
			}
		}
	}
}


/** раскрываем точки в пути */
function clear_dots($url)
{
	return preg_replace('#/[^/]+/../#', '/', $url);
}


/** скачивание файла с кешированием */
function http_get($url, $fname='')
{
	if (!$fname) $fname = $url;
	$fname = '/tmp/'.md5($fname);
	if (file_exists($fname)) $url = $fname;
	$st = @file_get_contents($url);
	if ($st && $fname != $url) @file_put_contents($fname, $st);
	return $st;
}
