<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2003-2005 The PEAR Group                               |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Martin Jansen <mj@php.net>                                  |
   +----------------------------------------------------------------------+
   $Id$
*/
response_header('The PEAR Group');
?>

<h1>The PEAR Group</h1>

<p>The PEAR Group is the governing body of PEAR. It currently consists
of the following individuals (in no particular order):</p>

<ul>
  <li><a href="/user/ashnazg" title="Chuck Burges">Chuck Burges</a></li>
  <li><a href="/user/davidc" title="David Coallier">David Coallier</a> [President]</li>
  <li><a href="/user/jstump" title="Joe Stump">Joe Stump</a></li>
  <li><a href="/user/jeichorn" title="Joshua Eichorn">Joshua Eichorn</a></li>
  <li><a href="/user/cweiske" title="Christian Weiske">Christian Weiske</a> [Vice President]</li>
  <li><a href="/user/dufuz" title="Helgi &THORN;ormar &THORN;orbjoernsson">Helgi &THORN;ormar &THORN;orbjoernsson</a></li>
  <li><a href="/user/bbieber" title="Brett Bieber">Brett Bieber</a></li>
</ul>

<p>
The current PEAR Group membership lasts from June 15th, 2008 until June 15th, 2009, and
is made up of PEAR developers duly elected by the developers of PEAR to serve.
</p>

<p>An archive of older groups can be found <a href="/group/archive.php">here</a></p>

<p>The Group was
<?php echo make_link("http://marc.theaimsgroup.com/?l=pear-dev&m=106073080219083&w=2", "first announced"); ?>
on 12th August 2003 by Stig S. Bakken, and was made into an elected body by the constitution
adopted on 18th March 2007. If you would like to get in
contact with the members, you can write to
<?php echo make_mailto_link("pear-group@php.net"); ?>.
</p>
<h2>&raquo; Administrative Documents</h2>

<ul>
  <li>04th November 2005:  <?php echo make_link("docs/20051104-sa.php", "Security Vulnerability Announcement"); ?></li>
  <li>02nd April 2004:     <?php echo make_link("docs/20040402-la.php", "License Announcement"); ?></li>
  <li>19th March 2004:     <?php echo make_link("docs/20040322-vm.php", "Handling Votings and Membership (II)"); ?></li>
  <li>26th February 2004:  <?php echo make_link("docs/20040226-vn.php", "Version Naming"); ?></li>
  <li>14th November 2003:  <?php echo make_link("docs/20031114-pds.php", "Package Directory Structure"); ?></li>
  <li>14th November 2003:  <?php echo make_link("docs/20031114-pcl.php", "Forming of the PEAR core list"); ?></li>
  <li>14th November 2003:  <?php echo make_link("docs/20031114-bbr.php", "New guidelines for BC breaking releases"); ?></li>
  <li>04th September 2003: <?php echo make_link("docs/20030904-pph.php", "Handling Package Proposals"); ?></li>
  <li>20th August 2003:    <?php echo make_link("docs/20030820-vm.php", "Handling Votings and Membership"); ?></li>
</ul>

<?php
response_footer();
