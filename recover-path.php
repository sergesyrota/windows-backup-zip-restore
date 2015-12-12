<?php

/**
 * Original here: https://github.com/sergesyrota/windows-backup-zip-restore
 *
 * USAGE: php ./recover-path.php "Users\sergey\Ubiquiti UniFi"
 *                               ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^ string to match in all zip files
 * Files will be restored (preserving path) in the __DIR__/restore folder
 * You need ./all-files.txt to list contents of all zip files to be considered for extract.
 * This can be obtained using:
 *      find . -name '*.zip' -exec unzip -l {} \; > ./all-files.txt
 * Make sure that all paths will be resolving from your current run path, as unzip -l produces relative paths to zip files
**/

$fh = fopen('./all-files.txt', 'r');

$restoreFolder = __DIR__ . '/restore';
$currentFile = '';
$zipFiles = [];
$searchString = $argv[1];
$i=0;
printf("Searching for '%s'\n", $searchString);
while ($row = fgets($fh)) {
    // File header starts
    if (preg_match('%^Archive:\s+(.*)%', $row, $matches)) {
        $currentFile = $matches[1];
    }
    // See if this describes the file
    if (preg_match('%^\s+(?P<size>\d+)  (?P<time>\d+\-\d+\-\d+ \d+:\d+)   (?P<filename>.*)%', $row, $matches) && strstr($matches['filename'], $searchString)) {
//        echo sprintf("File %s is in archive %s;\n", $matches['filename'], $currentFile);
        // Add to extract list only if we haven't seen it before, or if it's newer
        if (empty($zipFiles[$matches['filename']]) || $zipFiles[$matches['filename']]['time'] < strtotime($matches['time'])) {
            $zipFiles[$matches['filename']] = ['time' => strtotime($matches['time']), 'zipfile' => $currentFile];
        }
    }
//    if ($i++>10000) die();
}

//print_r($zipFiles);
//die();
foreach ($zipFiles as $file=>$data) {
    `unzip "{$data['zipfile']}" "*{$file}" -d "$restoreFolder"`;
}
