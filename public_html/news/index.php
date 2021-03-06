<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2005 The PHP Group                                |
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


$extraHeaders = '<link rel="alternate" href="http://blog.pear.php.net/feed/" type="application/rss+xml" title="PEAR News" />';

response_header("News", false, $extraHeaders);
?>

<h1>PEAR News</h1>
<p>Hey you! You probably want to look at the <a href="http://blog.pear.php.net/">PEAR blog</a> to find the current news.</p>

<h2>Historical news</h2>

<h3>&raquo; <a name="yr2007" id="yr2007">Year 2007</a></h3>
<ul>
 <li><?php echo make_link('newpresident-2007.php', 'PEAR has a new President!'); ?> (April) </li>
 <li><?php echo make_link('newgroup-2007.php', 'A new PEAR Group has been elected'); ?> (April) </li>
 <li><?php echo make_link('package.xml.1.0.php', 'Innovating the future: Package.xml 1.0 and PEAR 1.3.6 are officially deprecated'); ?> (January)</li>
</ul>
<h3>&raquo; <a name="yr2005" id="yr2005">Year 2005</a></h3>
<ul>
 <li><?php echo make_link('vulnerability.php', 'Serious vulnerability in the PEAR installer'); ?> (November)</li>
</ul>

<h3>&raquo; <a name="yr2004" id="yr2004">Year 2004</a></h3>
<ul>
 <li><?php echo make_link('nm-guide.php', 'New Maintainer&#39;s Guide'); ?> (August)</li>
 <li><?php echo make_link('weekly-summaries.php', 'Weekly Summaries'); ?> (April)</li>
 <li><?php echo make_link('pepr.php', 'Announcing PEPr'); ?> (January)</li>
</ul>

<h3>&raquo; <a name="yr2003" id="yr2003">Year 2003</a></h3>
<ul>
 <li><?php echo make_link('pecl-split.php', 'Own infrastructure for PECL'); ?> (October)</li>
 <li><?php echo make_link('group-announce.php', 'Announcing the PEAR Group'); ?> (August)</li>
 <li><?php echo make_link('activestate-award-ssb.php', 'ActiveState Active Award for Stig Bakken'); ?> (July)</li>
 <li><?php echo make_link('meeting-2003-summary.php', 'Summary of the PEAR Meeting'); ?> (May)</li>
 <li><?php echo make_link('meeting-2003.php', 'PEAR Meeting in Amsterdam'); ?> (March)</li>
 <li><?php echo make_link('release-1.0.php', 'PEAR 1.0 is released!'); ?> (January)</li>
</ul>

<?php
response_footer();