<?php
require_once './include/prepend.inc';

/* See bugs.php for the table layout of bugdb. */

if (isset($_GET['bug_id'])) {

    mysql_connect("localhost","pear","pear")
        or die("Unable to connect to SQL server. Please try again later.");

    mysql_select_db("pear");

    // Clean up the bug id
    $bug_id = ereg_replace ("[^0-9]+", "", $_GET['bug_id']);

    if ($bug_id != "") {
        // Try to find the email and the password
        $query = "SELECT email, passwd FROM bugdb WHERE id = '" . $bug_id . "'";

        // Run the query
        $result = mysql_query ($query)
            or die ("Sorry. No information could be found for bug report #$bug_id");

        if (mysql_num_rows($result) != 1) {
            $msg = "No password found for #$bug_id bug report, sorry.";
		} else {
			list ($email, $passwd) = mysql_fetch_row ($result);

            if (empty($passwd)) {
                $msg = "No password found for #$bug_id bug report, sorry.";
            } else {
                $passwd = stripslashes ($passwd);

                mail ($email, "Password for PEAR bug report #$bug_id", "The password for PEAR bug report #$bug_id is $passwd.", "From: noreply@php.net")
                    or die ("Sorry. Mail could not be sent at this time. Please try again later.");

                $msg = "The password for bug report #$bug_id has been sent to $email.";
            }
        }

    } else { 
        $msg = "The provided #$bug_id bug id is invalid.";
    }
} else {
    $msg = "";
}

response_header("Bug Report Password Finder");

?>
<h1>Bug Report Password Finder</h1>

<p>
If you need to modify a bug report that you submitted, but have
forgotten what password you used, this utility can help you.
</p>

<p>
Enter in the number of the bug report, press the Send button
and the password will be mailed to the email address specified
in the bug report.
</p>

<?php if ($msg) { echo "<p><font color=\"#cc0000\">$msg</font></p>"; } ?>

<form method="post" action="<?php echo $PHP_SELF;?>">
<p><b>Bug Report ID:</b> #<input type="text" size="20" name="bug_id">
<input type="submit" value="Send"></p>
</form>

<?php response_footer(); ?>
