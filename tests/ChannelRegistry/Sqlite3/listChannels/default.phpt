--TEST--
\PEAR2\Pyrus\ChannelRegistry\Sqlite3::listChannels() default channels
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$chans = $creg->listChannels();
sort($chans);

$test->assertEquals(array(
    '__uri',
    'doc.php.net',
    'pear.php.net',
    'pear2.php.net',
    'pecl.php.net',
), $chans, 'listChannels');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===