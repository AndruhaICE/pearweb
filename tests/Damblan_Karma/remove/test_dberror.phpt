--TEST--
Damblan_Karma->remove() [karma table delete fails]
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/setup.php.inc';
//$d = DB::connect('mysqli://pear:pear@localhost/pear');
//var_export($d->getAll("SELECT * FROM karma WHERE level = 'pear.admin'", array(), DB_FETCHMODE_ASSOC));
$mock->addDataQuery("SELECT * FROM karma WHERE user = 'cellog' AND level IN ('pear.admin','pear.group')", array(
    array('id' => 1, 'user' => 'cellog', 'level' => 'pear.admin', 'granted_by' => 'cellog',
    'granted_at' => '2007-05-24 00:00:00')
), array('id', 'user', 'level', 'granted_by', 'granted_at'));
$mock->addFailingQuery("DELETE FROM karma WHERE user = 'foo' AND level = 'pear.dev'", "oops");
$karma->remove('foo', 'pear.dev');
$phpt->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'DB Error: unknown error')
), 'errors');
?>
===DONE===
--EXPECT--
===DONE===