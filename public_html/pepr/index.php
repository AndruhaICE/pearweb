<?php
/**
 * Displays a list of all proposals.
 *
 * The <var>$proposalStatiMap</var> array is defined in
 * pearweb/include/pepr/pepr.php.
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
 * @package   PEPr
 * @author    Tobias Schlitt <toby@php.net>
 * @author    Daniel Convissor <danielc@php.net>
 * @copyright Copyright (c) 1997-2005 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License
 * @version   $Id$
 */

/**
 * Obtain the common functions and classes.
 */
require_once 'pepr/pepr.php';

if (isset($_GET['filter']) && isset($proposalStatiMap[$_GET['filter']])) {
    $selectStatus = $_GET['filter'];
} else {
    $selectStatus = '';
}

if ($selectStatus != '') {
    $order = ' pkg_category ASC, pkg_name ASC';
}

if (isset($_GET['search'])) {
    $searchString = trim($_GET['search']);
    $searchString = preg_replace('/;/', '', $searchString);
    $proposals = proposal::search($searchString);
    $searchPostfix = '_search_'.urlencode($searchString);
} else {
    $proposals =& proposal::getAll($dbh, @$selectStatus, null, @$order);
    $searchPostfix = '';
}

response_header('PEPr :: Package Proposals');

echo '<h1>Package Proposals</h1>' . "\n";
if ($selectStatus == '') {
    echo "<p>";
    echo "PEPr is PEAR's system for managing the process of submitting ";
    echo "new packages. If you would like to submit your own package, ";
    echo "please have a look at the <a href=\"/manual/en/developers-newmaint.php\">New Maintainers' Guide</a>.";
    echo "</p>";
    echo "<p>";
    echo "<a href=\"/feeds/pepr$searchPostfix.rss\"><img src=\"/gifs/feed.png\" width=\"16\" height=\"16\" alt=\"Aggregate this\" border=\"0\" /></a>";
    echo "</p>";
}

display_overview_nav();

$last_status = false;

$finishedCounter = 0;

foreach ($proposals as $proposal) {
    $status      = $proposal->getStatus();
    $status_true = $proposal->getStatus(true);
    if ($status != $last_status) {
        if ($last_status !== false) {
            echo "</ul>\n";
            echo "<p>";
            echo "</p>\n\n";
        }
        echo "<div style=\"float: right\"><a href='/feeds/pepr_".$status.".rss'><img src=\"/gifs/feed.png\" width=\"16\" height=\"16\" alt=\"Aggregate this\" border=\"0\" /></a></div>";
        echo '<h2 name="' . $status . '" id="';
        echo $status . '">';
        echo '&raquo; ' . htmlspecialchars($status_true);
        echo "</h2>\n";
        echo "<ul>\n";
        $last_status = $status;
    }
    $prpCat = $proposal->pkg_category;
    if ($selectStatus != '' && (!isset($lastChar) || $lastChar != $prpCat{0})) {
        $lastChar = $prpCat{0};
        echo "</ul>\n";
        echo "<h3><a id=\"$lastchar\">$lastChar</a></h3>\n";
        echo "<ul>\n";
    }
    if ($status == 'finished' && $selectStatus != 'finished') {
        if (++$finishedCounter == 10) {
            break;
        }
    }

    include_once 'pear-database-user.php';
    if (!isset($users[$proposal->user_handle])) {
        $users[$proposal->user_handle] = user::info($proposal->user_handle);
    }

    $already_voted = false;
    if (isset($auth_user) && $status_true == "Called for Votes") {
        $proposal->getVotes($dbh);

        if (in_array($auth_user->handle, array_keys($proposal->votes))) {
            $already_voted = true;
        }
    }

    echo "  <li>";
    if ($already_voted) {
        echo '(Already voted) ';
    }
    echo make_link('pepr-proposal-show.php?id=' . $proposal->id,
               htmlspecialchars($proposal->pkg_category) . ' :: '
               . htmlspecialchars($proposal->pkg_name));
    echo ' by ';
    echo make_link('/user/' . htmlspecialchars($proposal->user_handle),
               htmlspecialchars($users[$proposal->user_handle]['name']));
    switch ($status) {
        case 'proposal':
            echo ' &nbsp;(<a href="pepr-comments-show.php?id=' . $proposal->id;
            echo '">Comments</a>)';
            break;
        case 'vote':
        case 'finished':
            $voteSums = ppVote::getSum($dbh, $proposal->id);
            echo ' &nbsp;(<a href="pepr-votes-show.php?id=' . $proposal->id;
            echo '">Vote</a> sum: <strong>' . $voteSums['all'] . '</strong>';
            echo '<small>, ' . $voteSums['conditional'];
            echo ' conditional</small>)';
    }
    echo "</li>\n";
}

if ($selectStatus == '' && isset($proposal) && $status == 'finished') {
    echo make_link('/pepr/?filter=finished', 'All finished proposals');
}

echo "</ul>\n";

response_footer();
