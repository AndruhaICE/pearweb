<?php

require_once 'PEAR.php';
require_once 'pear-format-html.php';
require_once 'pear-database.php';

require_once 'HTML/QuickForm.php';
require_once 'Mail.php';


function shorten_string ( $string ) {
    if (strlen($string) < 80) {
        return $string;
    }
    $string_new = substr($string, 0, 20);
    $string_new .= "..." . substr($string, (strlen($string) - 60));
    return $string_new;
}

	
global $proposalStatiMap;
$proposalStatiMap = array(
                          'draft' 	=> 'Draft',
                          'proposal'	=> 'Proposed',
                          'vote'		=> 'Called for votes',
                          'finished'	=> 'Finished'
                          );
	
class proposal {
		
    var $id;
		
    var $pkg_category;
	 
    var $pkg_name;
	 
    var $pkg_describtion;
	 
    var $pkg_deps;
	 	
    var $draft_date;
	 	
    var $proposal_date;
	 
    var $vote_date;
	 	
    var $longened_date;
	 
    var $status = 'draft';
	 	
    var $user_handle;
	 	
    var $links;
	 	
    var $votes;
	 	
    function proposal ( $dbhResArr ) {
        $this->fromArray($dbhResArr);
    }
	 	
    function fromArray( $dbhResArr ) {
        if (!is_array($dbhResArr)) {
            return false;
        }
        foreach ($dbhResArr as $name => $value) {
            $value = (is_string($value)) ? stripslashes($value) : $value;
            $this->$name = $value;
        }	
        return true;
    }
	 	
    function &get ( &$dbh, $id ) {
        $sql = "SELECT *, UNIX_TIMESTAMP(draft_date) as draft_date,
						UNIX_TIMESTAMP(proposal_date) as proposal_date,
						UNIX_TIMESTAMP(vote_date) as vote_date,
						UNIX_TIMESTAMP(longened_date) as longened_date
	 				FROM package_proposals WHERE id = ".$id;
        $res = $dbh->getRow($sql, null, DB_FETCHMODE_ASSOC);
        if (DB::isError($res)) {
            return $res;
        }
        return new proposal($res);	 		
    }
		
    function &getAll ( &$dbh, $status = null, $limit = null ) {
        $sql = "SELECT *, UNIX_TIMESTAMP(draft_date) as draft_date,
						UNIX_TIMESTAMP(proposal_date) as proposal_date,
						UNIX_TIMESTAMP(vote_date) as vote_date,
						UNIX_TIMESTAMP(longened_date) as longened_date
					FROM package_proposals";
        if (!empty($status)) {
            $sql .= " WHERE status = '".$status."'";
        }
        $sql .= " ORDER BY status ASC, draft_date DESC";
        if (!empty($limit)) {
            $sql .= " LIMIT $limit";
        }
        $res = $dbh->query($sql);
        if (DB::isError($res)) {
            return $res;
        }
        $result = array();
        while ($set = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $result[$set['id']] =& new proposal($set);
        }
        return $result;
    }
		
    function getLinks ( &$dbh ) {
        if (empty($this->id)) {
            return PEAR::raiseError("Not initialized");
        }
        $this->links = & ppLink::getAll($dbh, $this->id);
        return true;
    }
		
    function getVotes ( &$dbh ) {
        if (empty($this->id)) {
            return PEAR::raiseError("Not initialized");
        }
        $this->votes = & ppVote::getAll($dbh, $this->id);
        return true;
    }
			
    function store ( $dbh ) {
        if (isset($this->id)) {
            $sql = "UPDATE package_proposals SET
					pkg_category = ".$dbh->quote($this->pkg_category).",
					pkg_name = ".$dbh->quote($this->pkg_name).",
					pkg_describtion = ".$dbh->quote($this->pkg_describtion).",
					pkg_deps = ".$dbh->quote($this->pkg_deps).",
					draft_date = FROM_UNIXTIME({$this->draft_date}),
					proposal_date = FROM_UNIXTIME({$this->proposal_date}),
					vote_date = FROM_UNIXTIME({$this->vote_date}),
					longened_date = FROM_UNIXTIME({$this->longened_date}),
					status = ".$dbh->quote($this->status).",
					user_handle = ".$dbh->quote($this->user_handle)."
					WHERE id = ".$this->id;
            $res = $dbh->query($sql);
            if (DB::isError($dbh)) {
                return $res;
            }
        } else {
            $sql = "INSERT INTO package_proposals (pkg_category, pkg_name, pkg_describtion,
						pkg_deps, draft_date, status, user_handle) VALUES (
						".$dbh->quote($this->pkg_category).",
						".$dbh->quote($this->pkg_name).",
						".$dbh->quote($this->pkg_describtion).",
						".$dbh->quote($this->pkg_deps).",
						FROM_UNIXTIME(".time()."),
						".$dbh->quote($this->status).",
						".$dbh->quote($this->user_handle).")";
            $res = $dbh->query($sql);
            if (DB::isError($dbh)) {
                return $res;
            }
            $this->id = mysql_insert_id($dbh->connection);
        }
        ppLink::deleteAll($dbh, $this->id);
        foreach ($this->links as $link) {
            if (!empty($link->url)) {
                $res = $link->store($dbh, $this->id);
                if (DB::isError($res)) {
                    return $res;
                }
            }
        }
        if (!empty($this->comment)) {
            $this->comment->store($dbh, $this->id);
            unset($this->comment);
        }
        return true;			
    }
		
    function addVote ( $dbh, $vote ) {
        if (!empty($this->votes[$vote->user_handle])) {
            return PEAR::raiseError("You already voted!");
        }
        $vote->pkg_propop_id = $this->id;
        $this->votes[$vote->user_handle] =& $vote;
        $vote->store($dbh, $this->id);
        return true;
    }
		
    function addComment ( $comment, $table = 'package_proposal_changelog' ) {
        $commentData = array("pkg_prop_id" => $this->id,
                             "user_handle" => $_COOKIE['PEAR_USER'],
                             "comment" 	   => $comment);
        $comment = new ppComment( $commentData, $table );
        $comment->store($this->id);
        return true;
    }
		
    function addLink ( $dbh, $link ) {
        $link->pkg_prop_id = $this->id;
        $this->links[] =& $link;
        return true;
    }
		
    function isFromUser ( $handle ) {
        if (strtolower($this->user_handle) != strtolower($handle)) {
            return false;
        }
        return true;
    }
		
    function getStatus ( $humanReadable = false ) {
        if ($humanReadable) {
            return $GLOBALS['proposalStatiMap'][$this->status];
        }
        return $this->status;
    }
		
    function isEditable ( ) {
        switch ($this->status) {
        case 'draft':
        case 'proposal': return true;
        }
        return false;
    }
		
			
    function checkTimeline( ) {
        switch ($this->status) {
        case 'draft': return true;
            break;
				
        case 'proposal': if (($this->proposal_date + PROPOSAL_STATUS_PROPOSAL_TIMELINE) < time()) {
            return true;
        }
            return (int)($this->proposal_date + PROPOSAL_STATUS_PROPOSAL_TIMELINE);
            break;
				
        case 'vote': if (!empty($this->longened_date)) {
            if (($this->longened_date + PROPOSAL_STATUS_VOTE_TIMELINE) > time()) {
                return (int)($this->longened_date + PROPOSAL_STATUS_VOTE_TIMELINE);
            }
        } else {
            if (($this->vote_date + PROPOSAL_STATUS_VOTE_TIMELINE) > time()) {
                return (int)($this->vote_date + PROPOSAL_STATUS_VOTE_TIMELINE);
            }
        }
            return false;
            break;
        }
    }
		
    function delete ( &$dbh ) {
        if (empty($this->id)) {
            return PEAR::raiseError("Proposal does not exist!");
        }
        $sql = "DELETE FROM package_proposals WHERE id = ".$this->id;
        $res = $dbh->query($sql);
        if (DB::isError($res)) {
            return $res;
        }
        $sql = "DELETE FROM package_proposal_votes WHERE pkg_prop_id = ".$this->id;
        $res = $dbh->query($sql);
        if (DB::isError($res)) {
            return $res;
        }
        $sql = "DELETE FROM package_proposal_links WHERE pkg_prop_id = ".$this->id;
        $res = $dbh->query($sql);
        if (DB::isError($res)) {
            return $res;
        }
        $sql = "DELETE FROM package_proposal_changelog WHERE pkg_prop_id = ".$this->id;
        $res = $dbh->query($sql);
        if (DB::isError($res)) {
            return $res;
        }
        $sql = "DELETE FROM package_proposal_comments WHERE pkg_prop_id = ".$this->id;
        $res = $dbh->query($sql);
        if (DB::isError($res)) {
            return $res;
        }
        return true;
    }
				
    function sendActionEmail($event, $userType, $user_handle = null, $comment = "") {

        global $dbh;
        
        $karma = new Damblan_Karma($dbh);
        
        require 'pepr/pepr-emails.php';
        $email = $proposalEmailTexts[$event];
        if (empty($email)) {
            return PEAR::raiseError("Email template for $event not found");
        }
        switch ($userType) {
        case 'admin':
            $prefix = "[ADMIN]";
            break;
        case 'mixed':
            if ($karma->has($user_handle, "pear.pepr.admin") && ($this->user_handle != $user_handle)) {
                $prefix = "[ADMIN]";
            } else {
                $prefix = "";
            }
            break;
        default:
            $prefix = "";
        }
        $prefix = PROPOSAL_EMAIL_PREFIX . $prefix . " ";
        $actorinfo = user::info($user_handle);
        $ownerinfo = user::info($this->user_handle);
        $this->getVotes($dbh);
        $vote = @$this->votes[$user_handle];
        if (isset($vote)) {
            $vote->value = ($vote->value > 0) ? "+".$vote->value : $vote->value;
            if ($vote->is_conditional) {
            	$vote_conditional = "\n\nThis vote is conditional. The condition is:\n\n".$vote->comment;
            } else {
            	$vote_conditional = "";
            }
            
            $vote_url = "http://pear.php.net/pepr/pepr-vote-show.php?id=".$this->id."&handle=".$user_handle;
        }
        $proposal_url = "http://pear.php.net/pepr/pepr-proposal-show.php?id=".$this->id;
        $end_voting_time = (@$this->longened_date > 0) ? $this->longened_date + PROPOSAL_STATUS_VOTE_TIMELINE : @$this->vote_date + PROPOSAL_STATUS_VOTE_TIMELINE;
        if (!isset($user_handle)) {
            $email['to'] = $email['to']['pearweb'];
        } else if ($karma->has($user_handle, "pear.pepr.admin")) {
            $email['to'] = $email['to']['admin'];
        } else {
            $email['to'] = $email['to']['user'];
        }
        $email['subject'] = $prefix . $email['subject'];
        $replace = array(
                         "/\{pkg_category\}/", 
                         "/\{pkg_name\}/", 
                         "/\{owner_name\}/",	
                         "/\{owner_email\}/",	
                         "/\{owner_link\}/",	
                         "/\{actor_name\}/",
                         "/\{actor_email\}/",	
                         "/\{actor_link\}/",
                         "/\{proposal_url\}/", 
                         "/\{end_voting_time\}/", 
                         "/\{vote_value\}/", 
                         "/\{vote_url\}/",
                         "/\{email_pear_dev\}/",
                         "/\{email_pear_group\}/",
                         "/\{comment\}/",
                         "/\{vote_conditional\}/"
                         );
        $replacements = array(
                              $this->pkg_category,
                              $this->pkg_name, 
                              (isset($ownerinfo['name'])) ? $ownerinfo['name'] : "", 
                              (isset($ownerinfo['email'])) ? $ownerinfo['email'] : "", 
                              (isset($ownerinfo['handle'])) ? user_link($ownerinfo['handle']) : "",
                              (isset($actorinfo['name'])) ? $actorinfo['name'] : "", 
                              (isset($actorinfo['email'])) ? $actorinfo['email'] : "", 
                              (isset($actorinfo['handle'])) ? "http://pear.php.net/user/".$actorinfo['handle'] : "",
                              $proposal_url, 
                              date("Y-m-d", $end_voting_time), 
                              (isset($vote)) ? $vote->value : 0, 
                              (isset($vote)) ? $vote_url : "",
                              PROPOSAL_MAIL_PEAR_DEV,
                              PROPOSAL_MAIL_PEAR_GROUP,
                              (isset($comment)) ? stripslashes($comment) : "",
                              (isset($vote_conditional)) ? $vote_conditional : ""
                              );
        $email = preg_replace($replace, $replacements, $email);
        $email['text'] .= PROPOSAL_EMAIL_POSTFIX;
        $to = explode(", ", $email['to']);
        $email['to'] = array_shift($to);
        $headers = "CC: ". implode(", ", $to) . "\n";
        $headers .= "From: " . PROPOSAL_MAIL_FROM . "\n";
        $headers .= "Reply-To: " . $actorinfo['email'] . "\n";
        $headers .= "X-Mailer: " . "PEPr, PEAR Proposal System" . "\n";
        $headers .= "X-PEAR-Category: " . $this->pkg_category . "\n";
        $headers .= "X-PEAR-Package: " . $this->pkg_name . "\n";
        $headers .= "X-PEPr-Status: " . $this->getStatus() . "\n";

        $res = mail($email['to'], $email['subject'], $email['text'], $headers, "-f pear-sys@php.net");
        if (!$res) {
            return PEAR::raiseError("Could not send notification email.");
        }
        return true;
    }
}

	
	
class ppComment {

    var $pkg_prop_id;
	 
    var $user_handle;
	 
    var $timestamp;
		
    var $comment;
    
    var $table;
		
    function ppComment ( $dbhResArr, $table = 'package_proposal_changelog' ) {
        foreach ($dbhResArr as $name => $value) {
            $value = (is_string($value)) ? stripslashes($value) : $value;
            $this->$name = $value;
        }
        $this->table = $table;
    }
		
    function get ( $proposalId, $handle, $timestamp, $table = 'package_proposal_changelog') {
        global $dbh;
        $sql = "SELECT *, timestamp FROM ".$table." WHERE pkg_prop_id = ".$proposalId." AND user_handle='".$handle."' AND timestamp = FROM_UNIXTIME(".$timestamp.")";
        $res = $dbh->query($sql);
        if (DB::isError($res)) {
            return $res;
        }
        $set['comment'] = stripslashes($set['comment']);
        $set = $res->fetchRow(DB_FETCHMODE_ASSOC);
        $comment =& new ppComment($set);
        return $comment;
    }
		
    function &getAll ( $proposalId, $table = 'package_proposal_changelog' ) {
        global $dbh;
        $sql = "SELECT *, timestamp FROM ".$table." WHERE pkg_prop_id = ".$proposalId." ORDER BY timestamp";
        $res = $dbh->query($sql);
        if (DB::isError($res)) {
            return $res;
        }
        $comments = array();
        while ($set = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $set['comment'] = stripslashes($set['comment']);
            $comments[] =& new ppVote($set);
        }
        return $comments;
    }
		
    function store ( $proposalId ) {
        global $dbh;
        if (empty($this->user_handle)) {
            return PEAR::raiseError("Not initialized");
        }
        $sql = "INSERT INTO ".$this->table." (pkg_prop_id, user_handle, comment, timestamp)
					VALUES (".$proposalId.", ".$dbh->quote($this->user_handle).", ".$dbh->quote($this->comment).", ".time().")";
        $res = $dbh->query($sql);
        return $res;
    }
    
    function delete ( ) {
        global $dbh;
        if (empty($this->table) || empty($this->user_handle) || empty($this->pkg_prop_id) || empty($this->timestamp)) {
            return PEAR::raiseError("Inconsistant comment data. Can not delete comment.");
        }
        $sql = "DELETE FROM ".$this->table." WHERE user_handle = '".$this->user_handle."' AND pkg_prop_id = ".$this->pkg_prop_id." AND timestamp = ".$this->timestamp;
        $res = $dbh->query($sql);
        return true;
    }
}

global $proposalReviewsMap;
$proposalReviewsMap = array(
                            'cursory'	=> 'Cursory source review',
                            'deep'		=> 'Deep source review',
                            'test'		=> 'Run examples');

class ppVote {

    var $pkg_prop_id;
	 
    var $user_handle;
		
    var $value;
	 
    var $reviews;
	 
    var $is_conditional;
	 
    var $comment;
	 
    var $timestamp;
		
    function ppVote ( $dbhResArr ) {
        foreach ($dbhResArr as $name => $value) {
        	$value = (is_string($value)) ? stripslashes($value) : $value;
            $this->$name = $value;
        }
    }
		
    function get ( &$dbh, $proposalId, $handle ) {
        $sql = "SELECT *, UNIX_TIMESTAMP(timestamp) AS timestamp FROM package_proposal_votes WHERE pkg_prop_id = ".$proposalId." AND user_handle='".$handle."'";
        $res = $dbh->query($sql);
        if (DB::isError($res)) {
            return $res;
        }
        $set = $res->fetchRow(DB_FETCHMODE_ASSOC);
        $set['reviews'] = unserialize($set['reviews']);
        $vote =& new ppVote($set);
        return $vote;
    }
		
    function &getAll ( &$dbh, $proposalId ) {
        $sql = "SELECT *, UNIX_TIMESTAMP(timestamp) AS timestamp FROM package_proposal_votes WHERE pkg_prop_id = ".$proposalId." ORDER BY timestamp ASC";
        $res = $dbh->query($sql);
        if (DB::isError($res)) {
            return $res;
        }
        $votes = array();
        while ($set = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $set['reviews'] = unserialize($set['reviews']);
            $votes[$set['user_handle']] =& new ppVote($set);
        }
        return $votes;
    }
		
    function store ( $dbh, $proposalId ) {
        if (empty($this->user_handle)) {
            return PEAR::raiseError("Not initialized");
        }
        $sql = "INSERT INTO package_proposal_votes (pkg_prop_id, user_handle, value, is_conditional, comment, reviews)
					VALUES (".$proposalId.", ".$dbh->quote($this->user_handle).", ".$this->value.", ".(int)$this->is_conditional.", ".$dbh->quote($this->comment).", ".$dbh->quote(serialize($this->reviews)).")";
        $res = $dbh->query($sql);
        return $res;
    }
		
    function getReviews ( $humanReadable = false ) {
        if ($humanReadable) {
            foreach ($this->reviews as $review) {
                $res[] = $GLOBALS['proposalReviewsMap'][$review];
            }
            return $res;
        }
        return $this->reviews;
    }
		
    function getSum ( $dbh, $proposalId ) {
        $sql = "SELECT SUM(value) FROM package_proposal_votes WHERE pkg_prop_id = ".$proposalId." GROUP BY pkg_prop_id";
        $result = $dbh->getOne($sql);
        $res['all'] = (is_numeric($result)) ? $result : 0;
        $sql = "SELECT SUM(value) FROM package_proposal_votes WHERE pkg_prop_id = ".$proposalId." AND is_conditional = 1 GROUP BY pkg_prop_id";
        $result = $dbh->getOne($sql);
        $res['conditional'] = (is_numeric($result)) ? $result : 0;
        return $res;
    }
		
    function getCount ( $dbh, $proposalId ) {
        $sql = "SELECT COUNT(user_handle) FROM package_proposal_votes WHERE pkg_prop_id = ".$proposalId." GROUP BY pkg_prop_id";
        $res = $dbh->getOne($sql);
        return (!empty($res)) ? $res: " 0";
    }
		
    function hasVoted ( $dbh, $userHandle, $proposalId ) {
        $sql = "SELECT count(pkg_prop_id) as votecount FROM package_proposal_votes
					WHERE pkg_prop_id = ".$proposalId." AND user_handle = '".$userHandle."' 
					GROUP BY pkg_prop_id";
        $votes = $dbh->query($sql);
        return (bool)($votes->numRows());
    }
		
}
	
global $proposalTypeMap;
$proposalTypeMap = array(
                         'pkg_file'				=> "PEAR package file (.tgz)", 
                         'pkg_source' 			=> "Package source file (.phps/.htm)",
                         'pkg_example'			=> "Package example (.php)", 
                         'pkg_example_source'	=> "Package example source (.phps/.htm)", 
                         'pkg_doc'				=> "Package documentation");
	
class ppLink {
		
    var $pkg_prop_id;
	 
    var $type;
	 
    var $url;
		
    function ppLink ( $dbhResArr ) {
        foreach ($dbhResArr as $name => $value) {
        	$value = (is_string($value)) ? stripslashes($value) : $value;
            $this->$name = $value;
        }
    }
		
    function &getAll ( &$dbh, $proposalId ) {
        $sql = "SELECT * FROM package_proposal_links WHERE pkg_prop_id = ".$proposalId." ORDER BY type";
        $res = $dbh->query($sql);
        if (DB::isError($res)) {
            return $res;
        }
        $links = array();
        while ($set = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $links[] =& new ppLink($set);
        }
        return $links;
    }
		
    function deleteAll ( $dbh, $proposalId) {
        $sql = "DELETE FROM package_proposal_links WHERE pkg_prop_id = ".$proposalId;
        $res = $dbh->query($sql);
        return $res;
    }
		
    function store ( $dbh, $proposalId ) {
        $sql = "INSERT INTO package_proposal_links (pkg_prop_id, type, url)
					VALUES (".$proposalId.", ".$dbh->quote($this->type).", ".$dbh->quote($this->url).")";
        $res = $dbh->query($sql);
        return $res;
    }
		
    function getType ( $humanReadable = false ) {
        if ($humanReadable) {
            return $GLOBALS['proposalTypeMap'][$this->type];
        }
        return $this->type;
    }
}

?>
