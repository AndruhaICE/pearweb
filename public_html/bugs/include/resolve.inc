<?php

/**
 * Creates the $RESOLVE_REASONS array
 *
 * This source file is subject to version 3.0 of the PHP license,
 * that is bundled with this package in the file LICENSE, and is
 * available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.
 * If you did not receive a copy of the PHP license and are unable to
 * obtain it through the world-wide-web, please send a note to
 * license@php.net so we can mail you a copy immediately.
 *
 * @category  pearweb
 * @package   Bugs
 * @copyright Copyright (c) 1997-2005 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License
 * @version   $Id$
 */

$RESOLVE_REASONS = array(
/*
  'trysnapshot4' => array(
    'status' => 'Feedback',
    'desc' => 'Try a snapshot (php4)',
    'message' =>
'Please try using this snapshot:

  http://snaps.php.net/php4-STABLE-latest.tar.gz
 
For Windows:
 
  http://snaps.php.net/win32/php4-win32-STABLE-latest.zip',
  ),

  'trysnapshot5' => array(
    'status' => 'Feedback',
    'desc' => 'Try a snapshot (php5)',
    'message' =>
'Please try using this snapshot:

  http://snaps.php.net/php5-latest.tar.gz
 
For Windows:
 
  http://snaps.php.net/win32/php5-win32-latest.zip',
  ),
*/
  'fixedcvs' => array(
    'status' => 'Closed',
    'desc' => 'Fixed in SVN',
    'message' =>
'This bug has been fixed in SVN.

If this was a documentation problem, the fix will appear on ' . PEAR_CHANNELNAME . ' by the end of next Sunday (CET).

If this was a problem with the ' . PEAR_CHANNELNAME . ' website, the change should be live shortly.

Otherwise, the fix will appear in the package\'s next release.

Thank you for the report and for helping us make PEAR better.',
  ),
  'alreadyfixed' => array(
    'status' => 'Closed',
    'desc' => 'Fixed in release',
    'message' =>
'Thank you for your bug report. This issue has been fixed
in the latest released version of the package, which you can download at
http://' . PEAR_CHANNELNAME . '/get/' . urlencode(@txfield('package_name')),
  ),
  'needtrace' => array(
    'status' => 'Feedback',
    'desc' => 'Need backtrace',
    'message' =>
'Thank you for this bug report. To properly diagnose the problem, we
need a backtrace to see what is happening behind the scenes. To
find out how to generate a backtrace, please read
http://bugs.php.net/bugs-generating-backtrace.php

Once you have generated a backtrace, please submit it to this bug
report and change the status back to "Open". Thank you for helping
us make PEAR better.',
  ),
  'oldversion' => array(
    'status' => 'Feedback',
    'desc' => 'Try newer package version',
    'message' =>
'Thank you for taking the time to report a problem with the package.
Unfortunately you are not using a current version of the package -- 
the problem might already be fixed. Please download a new
version from http://' . PEAR_CHANNELNAME . '/packages.php

If you are able to reproduce the bug with one of the latest
versions, please change the package version on this bug report
to the version you tested and change the status back to "Open".
Again, thank you for your continued support of PEAR.',
  ),
  'trycvs' => array(
    'status' => 'Feedback',
    'desc' => 'Try current SVN of package',
    'message' =>
'Thank you for taking the time to report a problem with the package.
This problem may have been already fixed by a previous change that
is in the SVN of the package.  Please checking out the SVN
repository of this package and upgrade

svn checkout svn.php.net/repository/@cvs@
pear upgrade package2.xml

or

pear upgrade package.xml

If you are able to reproduce the bug with the latest SVN,
please change the status back to "Open".
Again, thank you for your continued support of PEAR.',
  ),
  'oldphpversion' => array(
    'status' => 'Bogus',
    'desc' => 'Try newer PHP version',
    'message' =>
'Thank you for taking the time to report a problem with PHP.
Unfortunately you are not using a current version of PHP -- 
the problem might already be fixed. Please download a new
PHP version from http://www.php.net/downloads.php

If you are able to reproduce the bug with one of the latest
versions of PHP, please change the PHP version on this bug report
to the version you tested and change the status back to "Open".
Again, thank you for your continued support of PEAR.'
  ),
  'support' => array(
    'status' => 'Bogus',
    'desc' => 'Not developer issue',
    'message' =>
'Sorry, but your problem does not imply a bug in PEAR itself.  For a
list of more appropriate places to ask for help using PEAR, please
visit http://' . PEAR_CHANNELNAME . '/support/ as this bug system is not the
appropriate forum for asking support questions. 

Thank you for your interest in PEAR.',
  ),
  'nofeedback' => array(
    'status' => 'No Feedback',
    'desc' => 'No feedback',
    'webonly' => 1,
    'message' =>
'No feedback was provided. The bug is being suspended because
we assume that you are no longer experiencing the problem.
If this is not the case and you are able to provide the
information that was requested earlier, please do so and
change the status of the bug back to "Open". Thank you.',
  ),
  'notwrong' => array(
    'status' => 'Bogus',
    'desc' => 'Expected behavior',
    'message' =>
'Thank you for taking the time to write to us, but this is not
a bug.'
  ),
  'notenoughinfo' => array(
    'status' => 'Feedback',
    'desc' => 'Not enough info',
    'message' =>
'Not enough information was provided for us to be able
to handle this bug. Please re-read the instructions at
http://bugs.php.net/how-to-report.php

If you can provide more information, feel free to add it
to this bug and change the status back to "Open".

Thank you for your interest in PEAR.
'),
  'needreproducescript' => array(
    'status' => 'Feedback',
    'desc' => 'Need Reproduce Script',
    'message' =>
'We are unable to reproduce the bug as reported.
Please provide a simple script (10 lines or less)
or a link to an external script that can be used
to reproduce the bug.

If you can provide the script, feel free to add it
to this bug and change the status back to "Open".

Thank you for your interest in PEAR.
'),
  'submittedtwice' => array(
    'status' => 'Bogus',
    'desc' => 'Submitted twice',
    'message' =>
'Please do not submit the same bug more than once. An existing
bug report already describes this very problem. Even if you feel
that your issue is somewhat different, the resolution is likely
to be the same. Because of this, we hope you add your comments
to the existing bug instead.

Thank you for your interest in PEAR.',
  )
);
