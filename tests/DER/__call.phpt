--TEST--
Pyrus DER: __call edge cases
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
try {
    $der->oops();
    throw new Exception('worked and should not');
} catch (PEAR2\Pyrus\DER\Exception $e) {
    $test->assertEquals('Unknown type oops',
                        $e->getMessage(), 'error');
}
$der->boolean();
$test->assertEquals('boolean(FALSE)', (string) $der[0], '__call with no args');
$test->assertEquals('Boolean', $der[0]->type(), 'type');
$test->assertEquals(true, $der[0]->isType(PEAR2\Pyrus\DER\Boolean::TAG), 'isType');
?>
===DONE===
--EXPECT--
===DONE===