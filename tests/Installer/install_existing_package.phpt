--TEST--
\PEAR2\Pyrus\Installer::prepare() installed package is the same or newer version
--FILE--
<?php
include __DIR__ . '/../test_framework.php.inc';
$package = new \PEAR2\Pyrus\Package(__DIR__.'/../Mocks/SimpleChannelServer/package.xml');
$c = getTestConfig();

class TestLog implements \PEAR2\Pyrus\LogInterface
{
    protected $messages = array();
    
    function log($level, $message)
    {
        $this->messages[] = $message;
    }
    
    function getMessages()
    {
        return $this->messages;
    }
}

\PEAR2\Pyrus\Installer::begin();
\PEAR2\Pyrus\Installer::prepare($package);
\PEAR2\Pyrus\Installer::commit();
$observer = new TestLog();
\PEAR2\Pyrus\Logger::attach($observer);
\PEAR2\Pyrus\Installer::begin();
\PEAR2\Pyrus\Installer::prepare($package);
\PEAR2\Pyrus\Installer::commit();
$test->assertEquals(true, in_array("Skipping installed package {$package->channel}/{$package->name}", $observer->getMessages()), 'package already installed');

?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===