<?php
/**
 * This file generates Documentation.md for API endpoints
 */

require_once 'Autoloader.php';
Autoloader::register();

function buildData()
{
    $endpoints = [];

    $directory = __DIR__.'/classes';
    $files = array_diff(scandir($directory), array('..', '.'));

    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            $className = str_replace('.php', '', $file);

            try {
                $class = new ReflectionClass($className);

                if (str_contains($class->getDocComment(), '@api')) {
                    $classMethods = $class->getMethods();

                    foreach ($classMethods as $classMethod) {
                        $methodDocs = phpdocMethodParams($classMethod);
                        $params = array_keys($methodDocs);
                        if (in_array('@apiRoute', $params)) {
                            $props = [];

                            if (isset($methodDocs['@apiRoute'][0])) {
                                $props['route'] = $methodDocs['@apiRoute'][0];
                            }

                            if (isset($methodDocs['@description'])) {
                                $props['description'] = $methodDocs['@description'];
                            }

                            if (in_array('@requestMethod', $params) && isset($methodDocs['@requestMethod'][0])) {
                                $props['method'] = $methodDocs['@requestMethod'][0];
                            }

                            if (in_array('@apiParam', $params) && isset($methodDocs['@apiParam']) && !empty($methodDocs['@apiParam'])) {
                                $props['params'] = $methodDocs['@apiParam'];
                            }

                            $endpoints[$className][] = $props;
                        }
                    }
                }
            } catch (Exception) {
                // Do nothing
            }
        }
    }

    return $endpoints;
}
function phpdocMethodParams(ReflectionMethod $method) : array
{
    $doc = $method->getDocComment();

    $lines = array_map(function($line){
        return trim($line, " *");
    }, explode("\n", $doc));

    $linesWithParams = array_filter($lines, function($line){
        return str_starts_with($line, "@");
    });

    $description = array_diff($lines, $linesWithParams);

    foreach ($description as $key => $value) {
        if (empty(trim($value)) || $value == '/' || str_contains($value, '/**')) {
            unset($description[$key]);
        }
    }

    $args = [];

    if (isset($description) && ! empty($description)) {
        $args['@description'] = str_replace("\r", '', implode(' ', $description));
    }

    foreach($linesWithParams as $line)
    {
        list($param, $value) = explode(' ', $line, 2);
        $args[$param][] = str_replace("\r", '', $value);
    }

    return $args;
}

function generateMd($data) {
    $line = '';

    foreach ($data as $class => $methods) {
        $line.= '### '.$class."\r\n"."\r\n"."---"."\r\n"."\r\n";

        foreach ($methods as $method) {
            if (isset($method['method']) && $method['route']) {
                $line.= "`".$method['method']."` `".$method['route']."`"."\r\n"."\r\n";
            }

            if (isset($method['description'])) {
                $line.=$method['description']."\r\n"."\r\n";
            }

            if (isset($method['params'])) {
                $line.= "##### Parameters"."\r\n"."\r\n";

                foreach ($method['params'] as $methodParam) {
                    $line.= $methodParam."\r\n"."\r\n";
                }

                $line.="\r\n";
            }

            $line .= "---"."\r\n"."\r\n";
        }
    }

    $content = $line;
    $fp = fopen($_SERVER['DOCUMENT_ROOT'] . "/Documentation.md","wb");
    fwrite($fp,$content);
    fclose($fp);
}

generateMd(buildData());