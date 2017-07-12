<?php


$longOptions = ["dir:"];

$opts = getOpt("", $longOptions);

if (!$opts) {
    throw new Exception("--dir is a required argument");
}

$dir = $opts['dir'];

$collator = new InvoiceCollator($dir);
$collator->run();

