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

include_once 'pear-database-release.php';
$recent = release::getRecent(5);
if (@sizeof($recent) > 0) {
    $RSIDEBAR_DATA = "<strong>Recent&nbsp;Releases:</strong>\n";
    $RSIDEBAR_DATA .= '<table class="sidebar-releases">' . "\n";
    $today = date("D, jS M y");
    foreach ($recent as $release) {
        $releasedate = format_date(strtotime($release['releasedate']), "D, jS M y");
        if ($releasedate == $today) {
            $releasedate = "today";
        }
        $RSIDEBAR_DATA .= "<tr><td>";
        $RSIDEBAR_DATA .= "<a href=\"/package/" . $release['name'] . "/\">";
        $RSIDEBAR_DATA .= wordwrap($release['name'],25,"\n",1) . ' ' .
                          $release['version'] . '</a><br /> <small>(' .
                          $releasedate . ')</small></td></tr>';
    }
    $feed_link = '<a href="/feeds/" title="Information about XML feeds for the PEAR website"><img src="/gifs/feed.png" width="16" height="16" alt="" border="0" /></a>';
    $RSIDEBAR_DATA .= "<tr><td>&nbsp;</td></tr>\n";
    $RSIDEBAR_DATA .= '<tr><td align="right">' . $feed_link . "</td></tr>\n";
    $RSIDEBAR_DATA .= "</table>\n";
}

$popular = release::getPopular(5);
if (@sizeof($popular) > 0) {
    $RSIDEBAR_DATA .= "<strong>Popular&nbsp;Packages*:</strong>\n";
    $RSIDEBAR_DATA .= '<table class="sidebar-releases">' . "\n";
    foreach ($popular as $package) {
        $RSIDEBAR_DATA .= "<tr><td>";
        $RSIDEBAR_DATA .= "<a href=\"/package/" . $package['name'] . "/\">";
        $RSIDEBAR_DATA .= wordwrap($package['name'],25,"\n",1) . ' ' . $package['version'] . '</a><br /> <small>(' .
                          number_format($package['d'],2) . ')</small></td></tr>';
    }
    $feed_link = '<a href="/feeds/" title="Information about XML feeds for the PEAR website"><img src="/gifs/feed.png" width="16" height="16" alt="" border="0" /></a>';
    $RSIDEBAR_DATA .= "<tr><td><small>* downloads per day</small></td></tr>\n";
    $RSIDEBAR_DATA .= '<tr><td align="right">' . $feed_link . "</td></tr>\n";
    $RSIDEBAR_DATA .= "</table>\n";
}

$self = strip_tags(htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'iso-8859-1'));
response_header();
?>

<h1>PEAR - PHP Extension and Application Repository</h1>

<h2>&raquo; What is it?</h2>

<p><acronym title="PHP Extension and Application Repository">PEAR</acronym> is a framework and distribution system for reusable PHP components.</p>

<p>Sounds good? Perhaps you might want to know about <strong><a href="/manual/en/installation.php">installing PEAR on your system</a></strong> or <a href="/manual/en/guide.users.commandline.cli.php">installing pear packages</a>.</p>

<p>You can find help using PEAR packages in the <a href="/manual/en/">online manual</a> and the <a href="/manual/en/faq.php">FAQ</a>.</p>

<?php
if (!$auth_user) {
?>
<p>If you have been told by other PEAR developers to sign up for a PEAR website account, you can use <a href="/account-request.php"> this interface</a>.</p>
<?php
}
?>

<h2>&raquo; Hot off the Press</h2>
<div id="news">
 <p>
  <strong>[June 19, 2008]</strong>
  This year again, the PEAR community had to elect a president to lead the PEAR project thorough the year 2008 and 2009. David Coallier has accepted his role as
  the president of the project and became Greg Beaver's successor.
  <br /><br />
  You can see the result of the election "<a href="/election/info.php?election=10&results=1">at the election page</a>" or reach him at all times at <?php echo
  make_mailto_link('pear-president@php.net'); ?>
 </p>

 <p>
  <strong>[June 19, 2008]</strong>
  For the second year the elections are now over and a new PEAR Group has been formed. Just like last year and always following the Constitution, the new members
  have been elected by a secret ballot of PEAR Developers. They have chosen:
  <ul>
   <li>Joshua Eichorn</li>
   <li>Helgi &THORN;ormar &THORN;orbjornsson</li>
   <li>Joe Stump</li>
   <li>Christian Weiske</li>
   <li>Chuck Burgess</li>
   <li>Travis Swicegood</li>
   <li>Brett Bieber</li>
  </ul>

  You can see more information about the PEAR Group by clicking <a href="http://pear.php.net/group/"> here</a>
 </p>

 <p>
  <strong>[June 5, 2008]</strong><br />
  It this time of the year, the election for the PEAR group of 2008 - 2009 has begun as
  well as for president of the same time period, please head over to <a href="http://pear.php.net/election/">the election page</a>
  and cast your vote on the people you want to be at the helm of PEAR.
 </p>

 <p>
  <strong>[January 3, 2008]</strong><br />
  As promised, XML-RPC has been disabled at pear.php.net.  Information is now
  served via REST files at pear.php.net/rest.  If you are using
  a PEAR version earlier than 1.4.0, you will need to manually upgrade PEAR using
  direct URLs.  To upgrade to the latest PEAR, you can either use go-pear
  (<a href="http://pear.php.net/go-pear">http://pear.php.net/go-pear</a>) or
  upgrade using direct URLs:
  <pre>
   pear upgrade --force http://pear.php.net/get/Archive_Tar http://pear.php.net/get/XML_Parser http://pear.php.net/get/Console_Getopt-1.2.2
   pear upgrade --force http://pear.php.net/get/PEAR-1.3.3 (_IF_ your existing version is older than 1.3.3)
   pear upgrade --force http://pear.php.net/get/PEAR-1.4.3.tar
   pear upgrade PEAR
  </pre>
 </p>

 <p>
  <strong>[October 19, 2007]</strong><br />
  Following the tradition of internet culture, PEAR now channels blogs
  about PEAR. See it at
  <a href="http://planet.pear.php.net/">Planet PEAR</a>.
 </p>

 <p>
  <strong>[June 20, 2007]</strong><br />
  PEAR is greatly saddened by the loss of
  developer Bertrand Gugger to a heart attack on June 16.  More information is
  available on the official PEAR blog at
  <a href="http://blog.pear.php.net/2007/06/17/the-pear-project-mourns-the-loss-of-bertrand-gugger/">This entry</a>.
 </p>

 <p>
  <strong>[June 1, 2007]</strong><br />
  Welcome to the 7th and final member of the PEAR
  Group, <strong><a href="/user/pmjones">Paul M. Jones</a></strong>!  The newly elected
  <a href="/news/newgroup-2007.php">PEAR Group</a> and
  <a href="/news/newpresident-2007.php">PEAR president</a> have already begun work.
  The PEAR President is <a href="/user/cellog">Gregory Beaver</a>, and the
  PEAR Group
  is <a href="/user/mj">Martin Jansen</a>, <a href="/user/davidc">David Coallier</a>,
  <a href="/user/arnaud">Arnaud Limbourg</a>, <a href="/user/jeichorn">Joshua Eichorn</a>,
  <a href="/user/cweiske">Christian Weiske</a>, <a href="/user/dufuz">Helgi &THORN;ormar</a>,
  and <a href="/user/pmjones">Paul M. Jones</a>.  Official results of the run-off election are
  <a href="/election/info.php?election=9&amp;results=1">here</a>.
  <a href="/manual/en/constitution.php">The Constitution</a> documents the governing
  structure of PEAR.
 </p>

 <p>
  <strong>[May 8, 2007]</strong><br />
  A serious security vulnerability has been discovered in
  the <a href="http://pear.php.net/PEAR">PEAR Installer</a> that affects all released versions.
  PEAR version 1.5.4 has been released to address this security issue.  Further details are
  available <a href="/news/vulnerability2.php">here</a>.
 </p>

 <p>
  <strong>[February 1, 2007]</strong><br />
  As of January 1, 2008, PEAR will be dropping
  support for PEAR versions 1.3.6 and earlier.  If you are using PEAR 1.3.6 or earlier,
  we <em>strongly</em> encourage you to upgrade using these simple steps:
  <code>
   <pre>
    pear upgrade --force PEAR-1.3.6 Archive_Tar-1.3.1 Console_Getopt-1.2
    pear upgrade --force PEAR-1.4.11
    pear upgrade PEAR
   </pre>
  </code>
  The full story on what has changed, and what will change is <a href="/news/package.xml.1.0.php">here</a>.
 </p>
</div>
<?php
response_footer();
