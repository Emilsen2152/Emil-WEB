<?php
$host = 'caboose.proxy.rlwy.net';
$port = 21399;

$start = microtime(true);
$fp = @fsockopen($host, $port, $errno, $errstr, 5);
$ms = (int) ((microtime(true) - $start) * 1000);

header('Content-Type: text/plain; charset=utf-8');

if ($fp) {
    fclose($fp);
    echo "OK: connected to $host:$port in {$ms}ms\n";
} else {
    echo "FAIL: $host:$port ({$ms}ms)\n";
    echo "errno=$errno\n";
    echo "errstr=$errstr\n";
}
