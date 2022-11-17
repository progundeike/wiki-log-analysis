<?php

namespace wikipedia_log_analysis;

require_once(__DIR__ . '/src/LogAnalyze.php');

$logAnalysis = new LogAnalyze();
$logAnalysis->start();
