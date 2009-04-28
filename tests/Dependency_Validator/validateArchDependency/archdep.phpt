--TEST--
Dependency_Validator: arch dependency
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$fake = new PEAR2_Pyrus_PackageFile_v2;
$arch = $fake->dependencies['required']->arch;
$arch->pattern = 'foobar';
$validator->patterns['foobar'] = true;
$test->assertEquals(true, $validator->validateArchDependency($arch), 'foobar pass');

$arch->conflicts = true;
$test->assertEquals(false, $validator->validateArchDependency($arch), 'foobar conflicts fail');
$test->assertEquals(1, count($errs), 'foobar conflicts fail count');
foreach ($errs->E_ERROR as $error) {
    $test->assertEquals('pear2.php.net/test Architecture dependency failed, cannot match ' .
                        '"foobar"', $error->getMessage(),
                        'foobar conflicts fail message');
}

PEAR2_Pyrus_Installer::$options = array('force' => true);
// reset multierrors
$errs = new PEAR2_MultiErrors;
$validator = new test_Validator($package, $state, $errs);
$arch->conflicts = false;
$test->assertEquals(true, $validator->validateArchDependency($arch), 'foobar pass force');

$arch->conflicts = true;
$test->assertEquals(true, $validator->validateArchDependency($arch), 'foobar conflicts fail force');
$test->assertEquals(1, count($errs), 'foobar conflicts fail count');
foreach ($errs->E_WARNING as $error) {
    $test->assertEquals('warning: pear2.php.net/test Architecture dependency failed, does not ' .
                        'match "foobar"', $error->getMessage(),
                        'foobar conflicts fail message force');
}

PEAR2_Pyrus_Installer::$options = array();
// reset multierrors
$errs = new PEAR2_MultiErrors;
$validator = new test_Validator($package, $state, $errs);
$arch->conflicts = true;
$arch->pattern = 'barfoo';
$test->assertEquals(true, $validator->validateArchDependency($arch), 'barfoo conflicts pass');

$arch->conflicts = false;
$test->assertEquals(false, $validator->validateArchDependency($arch), 'barfoo fail');
$test->assertEquals(1, count($errs), 'barfoo conflicts fail count');
foreach ($errs->E_ERROR as $error) {
    $test->assertEquals('pear2.php.net/test Architecture dependency failed, does not match ' .
                        '"barfoo"', $error->getMessage(),
                        'barfoo fail message');
}

PEAR2_Pyrus_Installer::$options = array('nodeps' => true);
// reset multierrors
$errs = new PEAR2_MultiErrors;
$validator = new test_Validator($package, $state, $errs);
$arch->conflicts = true;
$arch->pattern = 'barfoo';
$test->assertEquals(true, $validator->validateArchDependency($arch), 'barfoo conflicts pass nodeps');

$validator->patterns['barfoo'] = true;
$test->assertEquals(true, $validator->validateArchDependency($arch), 'barfoo fail nodeps');
$test->assertEquals(1, count($errs), 'barfoo conflicts fail count');
foreach ($errs->E_WARNING as $error) {
    $test->assertEquals('warning: pear2.php.net/test Architecture dependency failed, cannot match ' .
                        '"barfoo"', $error->getMessage(),
                        'barfoo fail message nodeps');
}
?>
===DONE===
--EXPECT--
===DONE===