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

require_once 'Damblan/Search.php';

$term = (isset($_GET['q']) ? trim(htmlspecialchars(strip_tags(urldecode($_GET['q'])))) : '');
$in   = (isset($_GET['in']) ? htmlspecialchars(strip_tags($_GET['in'])) : 'packages');
$perP = (isset($_GET['setPerPage'])) ? (int)$_GET['setPerPage'] : 10;

$search = Damblan_Search::factory($in, $dbh);
$search->setPerPage($perP);
$search->search($term);
$pager =& $search->getPager();

response_header('Search: ' . $term);

echo "<h1>Search</h1>\n";
echo "<h2>" . $search->getTitle() . "</h2>\n";

echo "<form method=\"get\" name=\"search\" action=\"search.php\">\n";
echo "<input type=\"text\" name=\"q\" value=\"" . $term . "\" size=\"30\" /> in ";

echo "<select name=\"in\" size=\"1\">\n";
foreach (array("packages" => "Packages", "site" => "This Site (using Yahoo!)", "users" => "Developers") as $key => $value) {
    $selected = ($key == $in) ? " selected=\"yes\" " : "";
    echo "<option value=\"" . $key . "\" " . $selected . ">" . $value . "</option>\n";
}
echo "</select>\n";
if (is_object($pager)) {
    echo $pager->getPerPageSelectBox(10, 90, 10, false, array('optionText' => '%d packages', 'attributes' => 'id="perPage"', 'checkMaxLimit' => true));
}
echo "<input type=\"submit\" value=\"Search\" />\n";
echo "<script language=\"JavaScript\" type=\"text/javascript\">document.forms.search.q.focus();</script>\n";
echo "</form>\n";

$total = $search->getTotal();

if ($total > 0) {
    $start = (($pager->getCurrentPageID() - 1) * $search->getPerPage()) + 1;
    $end = ($start + $search->getPerPage() - 1 < $total ? $start + $search->getPerPage() - 1 : $total);

    echo "<p>Results <strong>" . $start . " - " . $end . "</strong> of <strong>" . $search->getTotal() . "</strong>:</p>\n";

    echo "<ol start=\"" . $start . "\">\n";
    foreach ($search->getResults() as $result) {
        echo "<li>\n";
        echo $result['html'];
        echo "</li>\n";
    }
    echo "</ol>\n";

    echo $pager->links;
} else if (!empty($term)) {
    echo "<p><div class=\"explain\">Sorry, but we didn't find anything that matches &quot;" . $term . "&quot;.</div></p>\n";
}

response_footer();