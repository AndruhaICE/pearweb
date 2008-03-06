<?php
require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);
$a = PEAR_PackageFileManager2::importOptions(dirname(__FILE__) . '/package-channel.xml',
    array(
        'baseinstalldir' => '/',
        'packagefile' => 'package-channel.xml',
        'filelistgenerator' => 'cvs',
        'roles' => array('*' => 'www'),
        'exceptions' => array('pearweb.php' => 'php'),
        'simpleoutput' => true,
        'include' => array(
            dirname(__FILE__) . '/public_html/channel.xml',
        ),
    ));
$a->setReleaseVersion('1.12.0');
$a->setReleaseStability('stable');
$a->setAPIStability('stable');
$a->setNotes('
jump to 1.12.0 so we can tag
');
$a->resetUsesrole();
$a->clearDeps();
$a->setPhpDep('4.3.0');
$a->setPearInstallerDep('1.4.11');
$a->generateContents();
$a->writePackageFile();