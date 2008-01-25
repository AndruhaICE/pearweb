<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2005 The PEAR Group                                    |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Author: Martin Jansen <mj@php.net>                                   |
   +----------------------------------------------------------------------+
   $Id$
*/

require_once 'Damblan/Search.php';
require_once 'Pager/Pager.php';

define('ITEMS_PER_PAGE', 10);

/**
 * User search class
 *
 * @author Martin Jansen <mj@php.net>
 * @package Damblan
 * @version $Revision$
 * @extends Damblan_Search
 */
class Damblan_Search_Users extends Damblan_Search
{

    var $_where;
    var $_title = 'Developers';

    function search($term)
    {
        if (empty($term)) {
            return;
        }

        $this->_where = $this->getWhere($term);

        $pageID = (isset($_GET['p']) ? (int)$_GET['p'] : 1);
        if ($pageID == 0) {
            $pageID = 1;
        }

        // Select all results
        $query = 'SELECT SQL_CALC_FOUND_ROWS handle, name FROM users WHERE ' . $this->_where . ' ORDER BY name';
        $query .= ' LIMIT ' . (($pageID - 1) * $this->_perPage) . ', ' . $this->_perPage;
        $this->_results = $this->_dbh->getAll($query, null, DB_FETCHMODE_ASSOC);

        // Get number of overall results
        $query = 'SELECT FOUND_ROWS()';
        $this->_total = $this->_dbh->getOne($query);

        $params = array(
                        'mode'       => 'Jumping',
                        'perPage'    => $this->_perPage,
                        'urlVar'     => 'p',
                        'itemData'   => range(1, $this->_total),
                        'extraVars'  => array('q' => $term)
                        );
        $this->_pager =& Pager::factory($params);
    }

    function getResults()
    {
        array_walk($this->_results, array(__CLASS__, 'decorate'));
        return $this->_results;
    }

    function getWhere($term)
    {
        $elements = preg_split("/\s/", $term, -1, PREG_SPLIT_NO_EMPTY);

        // we are only interested in the first 3 search words
        $elements = array_slice($elements, 0, 3);

        foreach ($elements as $t) {
            foreach (array('handle', 'name') as $field) {
                $ors[] = $field . ' LIKE ' . $this->_dbh->quoteSmart('%' . $t . '%');
            }
            $where[] = '(' . implode(' OR ', $ors) . ')';
            $ors = array();
        }

        return implode(' AND ', $where) . ' AND registered = 1';
    }

    function decorate(&$value, $key)
    {
        $value['html'] = '<strong><a href="/user/' . $value['handle'] . '">' . $value['name']  . '</a></strong> (' . $value['handle'] . ")\n";
    }
}
