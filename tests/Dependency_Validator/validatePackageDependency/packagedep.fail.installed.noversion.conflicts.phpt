--TEST--
Dependency_Validator: package dependency, conflicts, installed failure
--FILE--
<?php
require __DIR__ . '/../setup.registry.php.inc';

$fake = new PEAR2_Pyrus_PackageFile_v2;
$fake->name = 'foo';
$fake->channel = 'pear2.php.net';
$fake->version['release'] = '1.2.3';
$fake->files['foo'] = array('role' => 'php');
$fake->notes = 'hi';
$fake->summary = 'hi';
$fake->description = 'hi';
PEAR2_Pyrus_Config::current()->registry->install($fake);

$foo = $fake->dependencies['required']->package['pear2.php.net/foo']->conflicts(true);

$test->assertEquals(false, $validator->validatePackageDependency($foo, array()), 'foo');
$test->assertEquals(1, count($errs->E_ERROR), 'foo count');
$test->assertEquals(1, count($errs), 'foo count 2');
$test->assertEquals('channel://pear2.php.net/test conflicts with package "channel://pear2.php.net/foo", installed version is 1.2.3', $errs->E_ERROR[0]->getMessage(), 'foo error');

// reset multierrors
$errs = new PEAR2_MultiErrors;
$validator = new test_Validator($package, PEAR2_Pyrus_Validate::DOWNLOADING, $errs);

PEAR2_Pyrus_Config::current()->registry->uninstall('foo', 'pear2.php.net');

$test->assertEquals(false, $validator->validatePackageDependency($foo, array($fake)), 'foo downloading');
$test->assertEquals(1, count($errs->E_ERROR), 'foo downloading count');
$test->assertEquals(1, count($errs), 'foo downloading count 2');
$test->assertEquals('channel://pear2.php.net/test conflicts with package "channel://pear2.php.net/foo", downloaded version is 1.2.3', $errs->E_ERROR[0]->getMessage(), 'foo downloading error');
?>
===DONE===
--CLEAN--
<?php
$dir = dirname(__DIR__) . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===