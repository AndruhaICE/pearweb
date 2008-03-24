<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2005 The PEAR Group                                    |
   +----------------------------------------------------------------------+
   | This source file is subject to version 3.0 of the PHP license,       |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/3_0.txt.                                  |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Martin Jansen <mj@php.net>                                  |
   +----------------------------------------------------------------------+
   $Id$
*/
response_header("Channels");

$tabs = array("List" => array("url" => "/channels/index.php",
                              "title" => "List Sites."),
              "Add Site" => array("url" => "/channels/add.php",
                                  "title" => "Add your site.")
              );
?>

<h1>Channels</h1>

<?php print_tabbed_navigation($tabs); ?>

<h2>What&#39;s that?</h2>

<p>A number of third-party sites make it possible to install their
software package using the new <a href="/manual/en/guide.migrating.channels.php">channels</a>
feature of PEAR &ge; 1.4.0.  Specific installation instructures are
provided on the individual pages.</p>

<h2>List of Sites</h2>

<ul>
  <li><a href="http://pear.11abacus.com/">11abacus</a></li>
  <li><a href="http://pear.agavi.org/">Agavi</a></li>
  <li><a href="http://pear.crtx.org/">Cerebral Cortex</a></li>
  <li><a href="http://pear.crisscott.com/">Crisscott</a></li>
  <li><a href="http://pear.domain51.com/">Domain51</a></li>
  <li><a href="http://components.ez.no/">eZ components</a></li>
  <li><a href="http://gnope.org/pearfront/">Gnope PHP-GTK2 applications</a></li>
  <li><a href="http://pear.horde.org/">Horde</a></li>
  <li><a href="http://pear.midcom-project.org/">Midgard Project</a></li>
  <li><a href="http://pearified.com/">Pearified</a></li>
  <li><a href="http://pear.php-tools.net/">PHP Application Tools</a></li>
  <li><a href="http://pear.phpkitchen.com/">Seagull</a></li>
  <li><a href="http://pear.si.kz">si.kz</a></li>
  <li><a href="http://pear.symfony-project.com/">Symfony</a></li>
  <li><a href="http://solarphp.com/home/index.php?area=Main&amp;page=DownloadInstall#toc1">Solar</a></li>
  <li><a href="http://pear.funkatron.com/">Edward Finkler</a></li>
</ul>

<p><a href="/channels/add.php">Add your site</a></p>

<?php
response_footer();
?>
