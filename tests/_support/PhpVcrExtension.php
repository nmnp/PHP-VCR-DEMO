<?php
use Codeception\Test\Cest;

/**
 * @author Naresh Maharjan <nareshmaharjan@lftechnology.com>
 * @createdOn 09, 03 2016
 * @package
 * @subpackage
 */
class PhpVcrExtension extends \Codeception\Extension
{
    // list events to listen to
    public static $events = array(
        'test.before' => 'startTest',
        'test.start' => 'startTest',
        'test.after' => 'endTest',
        'test.end' => 'endTest',
    );

    /**
     * A test started.
     *
     * @param \Codeception\Event\TestEvent $test
     * @return bool|void
     */
    public function startTest(\Codeception\Event\TestEvent $test)
    {
        $data = $test->getTest();
        $class = get_class($data);
        if ($class == "Codeception\Test\Cest") {
            $data = $test->getTest()->getMetadata();
            $fileName = $data->getFilename();
            include_once $fileName;
            $arr = array_reverse(explode('/', $fileName));
            $classFileName = array_shift($arr);
            $className = basename($classFileName, '.php');
            $class = new $className();
        }
        $method = $data->getName();
        if (!method_exists($class, $method)) {
            return;
        }

        $reflection = new ReflectionMethod($class, $method);
        $doc_block = $reflection->getDocComment();
        // Use regex to parse the doc_block for a specific annotation
        $parsed = self::parseDocBlock($doc_block, '@vcr');
        $cassetteName = array_pop($parsed);

        // If the cassette name ends in .json, then use the JSON storage format
        if (substr($cassetteName, '-5') == '.json') {
            \VCR\VCR::configure()->setStorage('json');
        }

        if (empty($cassetteName)) {
            return true;
        }

        \VCR\VCR::turnOn();
        \VCR\VCR::insertCassette($cassetteName);
    }

    private static function parseDocBlock($doc_block, $tag)
    {
        $matches = array();

        if (empty($doc_block)) {
            return $matches;
        }

        $regex = "/{$tag} (.*)(\\r\\n|\\r|\\n)/U";
        preg_match_all($regex, $doc_block, $matches);

        if (empty($matches[1])) {
            return array();
        }

        // Removed extra index
        $matches = $matches[1];

        // Trim the results, array item by array item
        foreach ($matches as $ix => $match) {
            $matches[$ix] = trim($match);
        }

        return $matches;
    }

    /**
     * A test ended.
     *
     * @param PHPUnit_Framework_Test $test
     * @param float $time
     */
    public function endTest(\Codeception\Event\TestEvent $test, $time)
    {
        \VCR\VCR::turnOff();
    }
}