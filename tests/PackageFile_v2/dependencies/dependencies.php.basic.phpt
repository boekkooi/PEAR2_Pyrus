--TEST--
PackageFile v2: test package.xml dependencies property, basic php dep
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$reg = new \PEAR2\Pyrus\PackageFile\v2; // simulate registry package using packagefile
require __DIR__ . '/../../Registry/AllRegistries/package/extended/dependencies.php.basic.template';

?>
===DONE===
--EXPECT--
===DONE===