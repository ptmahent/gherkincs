#!/usr/bin/env php
<?php
/**
 * Cuke - Coding Standard Checker and Semantic Analyzer for Gherkin
 *
 * @copyright 2013 Instaclick Inc.
 *
 * @author Juti Noppornpitak <jnopporn@shiroyuki.com>
 */

require_once 'vendor/autoload.php';

$basePath = dirname(__FILE__);

function __autoload($className)
{
    global $basePath;

    $className = preg_replace('@\\\\@', '/', $className);

    require_once "$basePath/lib/$className.php";
}

spl_autoload_register('__autoload');

use IC\Gherkinics\AnalyzerManager;
use IC\Gherkinics\Analyzer;
use IC\Gherkinics\Core;
use IC\Gherkinics\Lexer;
use IC\Gherkinics\Util\Output;
use IC\Gherkinics\Printer;

function main($argumentList)
{
    global $basePath;

    $output         = new Output();
    $manager        = new AnalyzerManager();
    $cuke           = new Core();
    $argumentLength = count($argumentList);

    if ($argumentLength < 2) {
        $output->writeln('USAGE: cuke [--html /path/to/directory/for/report] /path/to/config_file /path/pattern/to/scan');

        exit(1);
    }

    $configPath = $argumentList[$argumentLength - 2];
    $targetPath = $argumentList[$argumentLength - 1];
    $optionMap  = array(
        'html' => array(
            'index' => in_array('--html', $argumentList) ? array_search('--html', $argumentList) : null,
            'value' => null,
        ),
    );

    if ($optionMap['html']['index'] !== null && $optionMap['html']['index'] >= 0) {
        $optionMap['html']['value'] = $argumentList[$optionMap['html']['index'] + 1];
    }

    // Set up the analyzer manager.
    $manager->setLexer(new Lexer());

    $config = simplexml_load_file($configPath);

    if ( ! isset($config->analyzers)) {
        $output->writeln('Notice: the configuration file is invalid.');

        exit(1);
    }

    if ( ! isset($config->analyzers->analyzer)) {
        $output->writeln('Terminated due to that no analyzers are found.');

        exit(1);
    }

    foreach ($config->analyzers->analyzer as $analyzer) {
        $analyzerClass = '\\'.$analyzer['class'];
        $output->write('       Registering analyzer: ' . $analyzerClass);
        $manager->registerAnalyzer(new $analyzerClass());
        $output->writeln("\r[DONE]");
    }

    // Set up the core object.
    $cuke->setBasePath($targetPath);
    $cuke->setAnalyzerManager($manager);

    $output->writeln(PHP_EOL . 'Analyzing feature files...');

    $pathToFeedbackMap = $cuke->scan(is_dir($targetPath) ? $targetPath . '/*' : $targetPath);

    $output->writeln('');

    switch (true) {
        case $optionMap['html']['index'] !== null:
            $printer = new Printer\HtmlPrinter(
                $basePath . '/view',   # template pool
                $basePath . '/static', # static pool
                $basePath . '/' . $optionMap['html']['value'],
                $targetPath
            );

            break;
        default:
            $printer = new Printer\TerminalPrinter($output, $basePath);

            break;
    }

    $printer->doPrint($pathToFeedbackMap);

    $output->writeln('Analysis complete.');
    $output->writeln(PHP_EOL . 'Please note that this tool only detects classic errors.');
    $output->writeln('Bye bye!');

    if (count($pathToFeedbackMap) > 0) {
        exit(1);
    }
}

main(array_slice($argv, 1));