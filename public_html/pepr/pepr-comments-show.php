<?php

/**
 * Displays and accepts comments for a given proposal.
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

if (!$proposal =& proposal::get($dbh, (int)@$_GET['id'])) {
    response_header('PEPr :: Comments :: Invalid Request');
    echo "<h1>Comments for</h1>\n";
    report_error('The requested proposal does not exist.');
    response_footer();
    exit;
}

$id = $proposal->id;
response_header('PEPr :: Comments :: ' . htmlspecialchars($proposal->pkg_name));
echo '<h1>Comments for &quot;' . htmlspecialchars($proposal->pkg_name) . "&quot;</h1>\n";
display_pepr_nav($proposal);

if ($auth_user && $proposal->getStatus() == 'proposal') {
    include_once 'HTML/QuickForm.php';
    $form =& new HTML_QuickForm('comment', 'post', 'pepr-comments-show.php?id=' . $id);

    $c = $form->addElement('textarea', 'comment', null,
                      array('cols' => 70,
                            'rows' => 20,
                            'id'   => 'comment_field'));

    $form->addElement('static', '', '',
            '<small>Your comment will also be sent to the'
            . ' <strong>pear-dev</strong> mailing list.<br />'
            . ' <strong>Please do not respond to other developers'
            . ' comments</strong>.<br />'
            . ' The author himself is responsible to reflect comments'
            . ' in an acceptable way.</small>');

    $form->addElement('submit', 'submit', 'Add New Comment');

    $form->applyFilter('comment', 'trim');
    $form->addRule('comment', 'A comment is required', 'required', null, 'client');

    if (isset($_POST['submit'])) {
        if ($form->validate()) {
            $comment = $form->exportValue('comment');
            $proposal->sendActionEmail('proposal_comment', 'user',
                                       $auth_user->handle,
                                       $comment);
            $proposal->addComment($comment, 'package_proposal_comments');
            report_success('Your comment was successfully processed');
            $c->setValue('');
        } else {
            report_error($form->getElementError('comment'));
        }
    }
}
?>

<table border="0" cellspacing="0" cellpadding="2" style="width: 100%">

 <tr>
  <th class="headrow" colspan="2">&raquo; Submit Your Comment</th>
 </tr>
 <tr>
  <td class="textcell" valign="top" colspan="2">

<?php

if ($proposal->getStatus() != 'proposal') {
    echo 'Comments are only accepted during the &quot;Proposal&quot; phase. ';
    echo 'This proposal is currently in the &quot;';
    echo $proposal->getStatus(true) . '&quot; phase.';
} else {
    if ($auth_user) {
        $formArray = $form->toArray();

        echo $form->getValidationScript();

        echo '<form ' . $formArray['attributes'] . ">\n";
        echo '<table class="form-holder" cellspacing="1">' . "\n";

        echo ' <caption class="form-caption">Comment on This';
        echo ' Proposal</caption>' . "\n";

        echo " <tr>\n";
        echo '  <th class="form-label_left">';
        echo '   <label for="comment_field" accesskey="o">C<span';
        echo ' class="accesskey">o</span>mment:</label>';
        echo "  </th>\n";
        echo '  <td class="form-input">';
        echo $formArray['elements'][0]['html'] . "</td>\n";
        echo " </tr>\n";

        echo " <tr>\n";
        echo '  <th class="form-label_left">&nbsp;</th>' . "\n";
        echo '  <td class="form-input">';
        echo $formArray['elements'][1]['html'] . "</td>\n";
        echo " </tr>\n";

        echo " <tr>\n";
        echo '  <th class="form-label_left">&nbsp;</th>' . "\n";
        echo '  <td class="form-input">';
        echo $formArray['elements'][2]['html'] . "</td>\n";
        echo " </tr>\n";

        echo "</table>\n";
        echo "</form>\n";
    } else {
        echo 'Please log in to enter your comment. If you are not a registered';
        echo ' PEAR developer, you can comment by sending an email to ';
        echo '<a href="mailto:' . PEAR_DEV_EMAIL . '">' . PEAR_DEV_EMAIL . '</a>.';
    }
}
?>

  </td>
 </tr>

 <tr>
  <th class="headrow" style="width: 100%">&raquo; Comments</th>
 </tr>
 <tr>

<?php

$comments = ppComment::getAll($id, 'package_proposal_comments');
$userInfos = array();

if (is_array($comments) && (count($comments) > 0)) {
    echo '  <td class="ulcell" valign="top">' . "\n";
    echo '   <ul class="spaced">' . "\n";
    include_once 'pear-database-user.php';
    foreach ($comments as $comment) {
        if (empty($userInfos[$comment->user_handle])) {
            $userInfos[$comment->user_handle] = user::info($comment->user_handle);
        }
        echo '<li><p style="margin: 0em 0em 0.3em 0em;">';
        echo user_link($comment->user_handle, true);
        echo ' [' . format_date($comment->timestamp) . ']</p>';
        echo nl2br(htmlentities(trim($comment->comment))) . "\n</li>";
    }
    echo "   </ul>\n";
} else {
    echo '  <td class="textcell" valign="top">';
    echo 'There are no comments.';
}
?>

  </td>
 </tr>
</table>

<?php
response_footer();