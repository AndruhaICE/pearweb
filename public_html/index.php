<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2003 The PHP Group                                |
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


$recent = release::getRecent();
if (@sizeof($recent) > 0) {
    $RSIDEBAR_DATA = "<strong>Recent&nbsp;Releases:</strong>\n";
    $RSIDEBAR_DATA .= '<table class="sidebar-releases">' . "\n";
    foreach ($recent as $release) {
        extract($release);
        $releasedate = substr($releasedate, 0, 10);
        $desc = substr($releasenotes, 0, 40);
        if (strlen($releasenotes) > 40) {
            $desc .= '...';
        }
        $desc = htmlentities($desc);
        $RSIDEBAR_DATA .= "<tr><td valign=\"top\" class=\"compact\">";
        $RSIDEBAR_DATA .= "<a href=\"/package/" . $name . "/\">";
        $RSIDEBAR_DATA .= "$name $version</a><br /><i>$releasedate:</i> $desc</td></tr>";
    }
    $feed_link = "<a href=\"/feeds/\">Syndicate this</a>";
    $RSIDEBAR_DATA .= "<tr><td>&nbsp;</td></tr>\n";
    $RSIDEBAR_DATA .= '<tr><td align="right">' . $feed_link . "</td></tr>\n";
    $RSIDEBAR_DATA .= "</table>\n";
}

response_header();
?>

<h1>PEAR - PHP Extension and Application Repository</h1>

<p><acronym title="PHP Extension and Application Repository">PEAR</acronym>
is a framework and distribution system for reusable PHP
components. More <b>information</b> about PEAR can be found in the
<a href="/manual/en/">online manual</a> and the
<a href="/manual/en/faq.php">FAQ</a>.</p>

<p>If you are a first time user, you might be especially interested in
the manual chapter &quot;<a href="/manual/en/about-pear.php">About PEAR</a>&quot;.</p>

<p>Recent <b>news</b> about PEAR can be found <a href="/news/">here</a>.</p>

<p>PEAR provides the above mentioned PHP components in the form of so
called &quot;Packages&quot;. If you would like to <b>download</b> PEAR
packages, you can <a href="/packages.php">browse the complete list</a>
here.  Alternatively you  can  search for packages by some keywords
using the search box above. Apart from simply downloading a package,
PEAR also provides a command-line interface that can be used to
automatically <b>install</b> packages. The manual <a href="/manual/en/installation.cli.php">
describes this procedure</a> in detail.</p>

<p>In case you need <b>support</b> for PEAR in general or a package
in special, we have compiled a list of the <a href="/support.php">available
support resources</a>.</p>

<?php
echo hdelim();

if (isset($_COOKIE['PEAR_USER'])) {
    if (auth_check('pear.dev')) {
        echo '<h2>Developers</h2>';
        echo '<div class="indent">';

        echo menu_link("Upload Release", "release-upload.php");
        echo menu_link("New Package", "package-new.php");

        echo '</div>';
    }

    echo '<h2>Package Proposals (PEPr)</h2>';
	echo '<div class="indent">';
	echo menu_link("Browse Proposals", "pepr/pepr-overview.php");
	echo menu_link("New Package Proposal", "pepr/pepr-proposal-edit.php");

    if (user::isAdmin($_COOKIE['PEAR_USER'])) {
        echo '<h2>Administrators</h2>';
        echo '<div class="indent">';
        echo menu_link("Overview", "/admin/");
        echo '</div>';
    }

    echo '</div>';
} else {
?>

<p>If you have been told by other PEAR developers to sign up for a
PEAR website account, you can use <a href="/account-request.php">
this interface</a>.</p>

<?php
}

response_footer();

?>
