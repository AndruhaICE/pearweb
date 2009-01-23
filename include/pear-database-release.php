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
   | Authors:  Stig S. Bakken <ssb@fast.no>                               |
   |           Tomas V.V. Cox <cox@php.net>                               |
   |           Martin Jansen <mj@php.net>                                 |
   +----------------------------------------------------------------------+
   $Id$
*/

/**
 * Class to handle releases
 *
 * @class   release
 * @package pearweb
 * @author  Stig S. Bakken <ssb@fast.no>
 * @author  Tomas V.V. Cox <cox@php.net>
 * @author  Martin Jansen <mj@php.net>
 */
class release
{
    /**
     * Get recent releases
     *
     * @static
     * @param  integer Number of releases to return
     * @return array
     */
    static function getRecent($n = 5)
    {
        global $dbh;

        $sql = '
            SELECT
                p.id AS id,
                p.name AS name,
                p.summary AS summary,
                r.version AS version,
                r.releasedate AS releasedate,
                r.releasenotes AS releasenotes,
                r.doneby AS doneby,
                r.state AS state
            FROM packages p, releases r
            WHERE
                p.id = r.package
                AND p.approved = 1
                AND p.package_type = ?
                ORDER BY r.releasedate DESC';
        $sth = $dbh->limitQuery($sql, 0, $n, array(SITE));
        $recent = array();
        // XXX Fixme when DB gets limited getAll()
        while ($sth->fetchInto($row, DB_FETCHMODE_ASSOC)) {
            $recent[] = $row;
        }
        return $recent;
    }

    static function getPopular($n = 5, $rss = false)
    {
        global $dbh;
        if ($rss) {
            $query = '
            SELECT
                packages.name, releases.version, downloads, releasedate,
                    downloads/(CEIL((unix_timestamp(NOW()) - unix_timestamp(releases.releasedate))/86400)) as releasenotes
                FROM releases, packages, aggregated_package_stats a
                WHERE
                    packages.name <> "pearweb" AND
                    packages.name <> "pearweb_phars" AND
                    packages.id = releases.package AND
                    packages.package_type = ? AND
                    a.release_id = releases.id AND
                    a.package_id = packages.id AND
                    packages.newpk_id IS NULL AND
                    packages.unmaintained = 0 AND
                    a.yearmonth = "' . date('Y-m-01 00:00:00', time()) . '"
                ORDER BY releasenotes DESC';
        } else {
            $query = '
            SELECT
                packages.name, releases.version, downloads,
                    downloads/(CEIL((unix_timestamp(NOW()) - unix_timestamp(releases.releasedate))/86400)) as d
                FROM releases, packages, aggregated_package_stats a
                WHERE
                    packages.name <> "pearweb" AND
                    packages.name <> "pearweb_phars" AND
                    packages.id = releases.package AND
                    packages.package_type = ? AND
                    a.release_id = releases.id AND
                    a.package_id = packages.id AND
                    packages.newpk_id IS NULL AND
                    packages.unmaintained = 0 AND
                    a.yearmonth = "' . date('Y-m-01 00:00:00', time()) . '"
                ORDER BY d DESC';
        }
        $sth = $dbh->limitQuery($query, 0, $n, array(SITE));
        $recent = array();
        // XXX Fixme when DB gets limited getAll()
        while ($sth->fetchInto($row, DB_FETCHMODE_ASSOC)) {
            $recent[] = $row;
        }
        return $recent;
    }

    /**
     * Get release in a specific time range
     *
     * @static
     * @param integer Timestamp of start date
     * @param integer Timestamp of end date
     * @return array
     */
    static function getDateRange($start, $end, $site = 'pear')
    {
        global $dbh;
        $recent = array();
        if (!is_numeric($start)) {
            return $recent;
        }
        if (!is_numeric($end)) {
            return $recent;
        }
        $start_f = date('Y-m-d 00:00:00', $start);
        $end_f   = date('Y-m-d 00:00:00', $end);

        $site = strtolower($site);
        if (!in_array($site, array('pear', 'pear2', 'pecl'))) {
            $site = 'pear';
        }

        $sql = '
            SELECT
                p.id AS id,
                p.name AS name,
                p.summary AS summary,
                p.description AS description,
                r.version AS version,
                r.releasedate AS releasedate,
                r.releasenotes AS releasenotes,
                r.doneby AS doneby,
                r.state AS state
            FROM packages p, releases r
            WHERE
                p.id = r.package
                AND r.releasedate > ? AND r.releasedate < ?
                AND p.package_type = ?
                ORDER BY r.releasedate DESC';

        // limited to 50 to stop overkill on the server!
        $sth = $dbh->limitQuery($sql, 0, 50, array($start_f, $end_f, $site));
        while ($sth->fetchInto($row, DB_FETCHMODE_ASSOC)) {
            $recent[] = $row;
        }
        return $recent;
    }

    /**
     * Upload new release
     *
     * @static
     * @param string Name of the package
     * @param string Version string
     * @param string State of the release
     * @param string Release notes
     * @param string Filename of the release tarball
     * @param string MD5 checksum of the tarball
     */
    static function upload($package, $version, $state, $relnotes, $tarball, $md5sum,
                    $pkg_info = false, $packagexml = false, $compatible = false)
    {
        global $auth_user;

        include_once 'pear-database-user.php';
        $role = user::maintains($auth_user->handle, $package);
        if ($role != 'lead' && !$auth_user->isAdmin() && !$auth_user->isQA()) {
            return PEAR::raiseError('release::upload: insufficient privileges');
        }
        $ref = release::validateUpload($package, $version, $state, $relnotes, $tarball, $md5sum);
        if (PEAR::isError($ref)) {
            return $ref;
        }

        return release::confirmUpload($package, $version, $state, $relnotes, $md5sum, $ref['package_id'], $ref['file'], $pkg_info, $packagexml, $compatible);
    }

    /**
     * Determine if uploaded file is a valid release
     *
     * @param string Name of the package
     * @param string Version string
     * @param string State of the release
     * @param string Release notes
     * @param string Filename of the release tarball
     * @param string MD5 checksum of the tarball
     * @return mixed
     */
    static function validateUpload($package, $version, $state, $relnotes, $tarball, $md5sum)
    {
        global $dbh, $auth_user;

        // (1) check if the uploader has permisson to do a new release
        include_once 'pear-database-user.php';
        $role = user::maintains($auth_user->handle, $package);
        if ($role != 'lead' && !$auth_user->isAdmin() && !$auth_user->isQA()) {
            return PEAR::raiseError('release::validateUpload: insufficient privileges');
        }
        // (2) verify that package exists
        include_once 'pear-database-package.php';
        $package_id = package::info($package, 'id');
        if (PEAR::isError($package_id) || empty($package_id)) {
            return PEAR::raiseError("package `$package' must be registered first");
        }

        // (3) verify that version does not exist
        $sql  = 'SELECT version FROM releases WHERE package = ? AND version = ?';
        $test = $dbh->getOne($sql, array($package_id, $version));
        if (PEAR::isError($test)) {
            return $test;
        }

        if ($test) {
            return PEAR::raiseError("already exists: $package $version");
        }

        // (4) store tar ball to temp file
        $tempfile = sprintf("%s/%s%s-%s.tgz",
                            PEAR_TARBALL_DIR, '.new.', $package, $version);
        $file = sprintf("%s/%s-%s.tgz", PEAR_TARBALL_DIR, $package, $version);
        if (!@copy($tarball, $tempfile)) {
            return PEAR::raiseError("writing $tempfile failed: $php_errormsg");
        }

        if (!isset($package_id)) {
            return PEAR::raiseError('bad upload: package_id missing');
        }

        // TODO: do lots of integrity checks on the tarball
        if (!@rename($tempfile, $file)) {
            return PEAR::raiseError("renaming failed: $php_errormsg");
        }

        // (5) verify MD5 checksum
        $testsum = md5_file($file);
        if ($testsum != $md5sum) {
            $bytes = strlen($data);
            return PEAR::raiseError("bad md5 checksum (checksum=$testsum ($bytes bytes: $data), specified=$md5sum)");
        }

        // (6) unpack tarball
        $target = @fopen(PEAR_TARBALL_DIR . '/' . $package . '-' . $version . '.tar', 'w+');
        if ($target) {
            fwrite($target, file_get_contents("compress.zlib://" . $file));
            fclose($target);
        }

        return array('package_id' => $package_id, 'file' => $file);
    }

    /**
     * Confirm release upload
     *
     * @param string Package name
     * @param string Package version
     * @param string Package state
     * @param string Release notes
     * @param string md5
     * @param int    Package id from database
     * @param string package contents
     * @static
     * @return string  the file name of the upload or PEAR_Error object if problems
     */
    static function confirmUpload($package, $version, $state, $relnotes, $md5sum, $package_id, $file,
                           $pkg_info = false, $packagexml = false, $compatible = false)
    {
        require_once 'PEAR/Common.php';

        global $dbh, $auth_user, $_PEAR_Common_dependency_types,
               $_PEAR_Common_dependency_relations;

        if (!$pkg_info) {
            require_once 'Archive/Tar.php';
            $tar = new Archive_Tar($file);

            $oldpackagexml = $tar->extractInString('package.xml');
            if (null === $packagexml = $tar->extractInString('package2.xml')) {
                if ($oldpackagexml === null) {
                    return PEAR::raiseError('Archive uploaded does not appear to contain a package.xml!');
                }

                $packagexml = $oldpackagexml;
            }

            $compatible = $oldpackagexml != $packagexml ? true : false;
        }
        // Update releases table
        $query = "INSERT INTO releases (id,package,version,state,doneby,".
             "releasedate,releasenotes) VALUES(?,?,?,?,?,NOW(),?)";
        $sth = $dbh->prepare($query);
        $release_id = $dbh->nextId('releases');
        $dbh->execute($sth, array($release_id, $package_id, $version, $state,
                                  $auth_user->handle, $relnotes));
        // Update files table
        $query = "INSERT INTO files ".
             "(id,package,release,md5sum,basename,fullpath,packagexml) ".
             "VALUES(?,?,?,?,?,?,?)";
        $sth = $dbh->prepare($query);
        $file_id = $dbh->nextId("files");
        $ok = $dbh->execute($sth, array($file_id, $package_id, $release_id,
                                        $md5sum, basename($file), $file, $packagexml));
        /*
         * Code duplication with deps error
         * Should be droped soon or later using transaction
         * (and add mysql4 as a pe(ar|cl)web requirement)
         */
        if (PEAR::isError($ok)) {
            $dbh->query("DELETE FROM releases WHERE id = $release_id");
            @unlink($file);
            return $ok;
        }

        // Update dependency table
        $query = "INSERT INTO deps " .
            "(package, `release`, type, relation, version, name, optional) " .
            "VALUES (?,?,?,?,?,?,?)";
        $sth = $dbh->prepare($query);

        if (!$pkg_info) {
            require_once 'PEAR/PackageFile.php';
            require_once 'PEAR/Config.php';
            $config   = PEAR_Config::singleton();
            $pf       = new PEAR_PackageFile($config);
            $pkg_info = $pf->fromXmlString($packagexml, PEAR_VALIDATE_DOWNLOADING,
                $compatible ? 'package2.xml' : 'package.xml');
        }

        $deps      = $pkg_info->getDeps(true); // get the package2.xml actual content
        $storedeps = $pkg_info->getDeps(); // get the BC-compatible content
        $pearused  = false;
        if (isset($deps['required']['package'])) {
            if (!isset($deps['required']['package'][0])) {
                $deps['required']['package'] = array($deps['required']['package']);
            }
            foreach ($deps['required']['package'] as $pkgdep) {
                if ($pkgdep['channel'] == 'pear.php.net' && strtolower($pkgdep['name']) == 'pear') {
                    $pearused = true;
                }
            }
        }
        if (is_array($storedeps)) {
            foreach ($storedeps as $dep) {
                $prob = array();

                if (empty($dep['type']) ||
                    !in_array($dep['type'], $_PEAR_Common_dependency_types))
                {
                    $prob[] = 'type';
                }

                if (empty($dep['name'])) {
                    /*
                     * NOTE from pajoye in ver 1.166:
                     * This works for now.
                     * This would require a 'cleaner' InfoFromXXX
                     * which may return a defined set of data using
                     * default values if required.
                     */
                    if (strtolower($dep['type']) == 'php') {
                        $dep['name'] = 'PHP';
                    } else {
                        $prob[] = 'name';
                    }
                } elseif (strtolower($dep['name']) == 'pear') {
                    if (!$pearused && $compatible) {
                        // there is no need for a PEAR dependency here
                        continue;
                    }
                    if (!$pearused && !$compatible) {
                        $dep['name'] = 'PEAR Installer';
                    }
                }

                if (empty($dep['rel']) ||
                    !in_array($dep['rel'], $_PEAR_Common_dependency_relations))
                {
                    $prob[] = 'rel';
                }

                if (empty($dep['optional'])) {
                    $optional = 0;
                } else {
                    if ($dep['optional'] != strtolower($dep['optional'])) {
                        $prob[] = 'optional';
                    }

                    $optional = $dep['optional'] == 'yes' ? 1 : 0;
                }

                if (count($prob)) {
                    $res = PEAR::raiseError('The following attribute(s) ' .
                            'were missing or need proper values: ' .
                            implode(', ', $prob));
                } else {
                    $res = $dbh->execute($sth,
                            array(
                                $package_id,
                                $release_id,
                                $dep['type'],
                                $dep['rel'],
                                @$dep['version'],
                                $dep['name'],
                                $optional));
                }

                if (PEAR::isError($res)) {
                    $dbh->query('DELETE FROM deps WHERE `release` = ' . $release_id);
                    $dbh->query('DELETE FROM releases WHERE id = '  . $release_id);
                    @unlink($file);
                    return $res;
                }
            }
        }

        include_once 'pear-database-package.php';
        $n = package::info($package, 'name');
        if (!in_array($n, array('pearweb', 'pearweb_phars'), true)) {
            // Add release archive file to API documentation queue
            $query = "INSERT INTO apidoc_queue (filename, queued) "
                 . "VALUES ('" . $file. "', NOW())";

            // Don't abort the release if something goes wrong.
            $dbh->pushErrorHandling(PEAR_ERROR_RETURN);
            $sth = $dbh->query($query);
            $dbh->popErrorHandling();
        }

        // Update Cache
        include_once 'pear-rest.php';
        $pear_rest = new pearweb_Channel_REST_Generator(PEAR_REST_PATH, $dbh);
        $pear_rest->saveAllReleasesREST($package);
        $pear_rest->saveReleaseREST($file, $packagexml, $pkg_info, $auth_user->handle, $release_id);
        $pear_rest->savePackagesCategoryREST(package::info($package, 'category'));

        return $file;
    }

    /**
     * Dismiss release upload
     *
     * @param string
     * @return boolean
     */
    static function dismissUpload($upload_ref)
    {
        return (bool)@unlink($upload_ref);
    }

    /**
     * Download release via HTTP
     *
     * @param string Name of the package
     * @param string Version string
     * @param string Filename
     * @param boolean Uncompress file before downloading?
     * @return mixed
     * @static
     */
    static function HTTPdownload($package, $version = null, $file = null, $uncompress = false)
    {
        global $dbh;

        include_once 'pear-database-package.php';
        $package_id = package::info($package, 'packageid', true);

        if (!$package_id) {
            return PEAR::raiseError("release download:: package '".htmlspecialchars($package).
                                    "' does not exist");
        } elseif (PEAR::isError($package_id)) {
            return $package_id;
        }

        if ($file !== null) {
            if (substr($file, -4) == '.tar') {
                $file = substr($file, 0, -4) . '.tgz';
                $uncompress = true;
            }
            $row = $dbh->getRow("SELECT fullpath, `release`, id FROM files ".
                                "WHERE UPPER(basename) = ?", array(strtoupper($file)),
                                DB_FETCHMODE_ASSOC);
            if (PEAR::isError($row)) {
                return $row;
            } elseif ($row === null) {
                return PEAR::raiseError("File '$file' not found");
            }
            $path        = $row['fullpath'];
            $log_release = $row['release'];
            $log_file    = $row['id'];
            $basename    = $file;
        } elseif ($version === null) {
            // Get the most recent version
            $row = $dbh->getRow("SELECT id FROM releases ".
                                "WHERE package = $package_id ".
                                "ORDER BY releasedate DESC", DB_FETCHMODE_ASSOC);
            if (PEAR::isError($row)) {
                return $row;
            }
            $release_id = $row['id'];
        } elseif (release::isValidState($version)) {
            $version = strtolower($version);
            // Get the most recent version with a given state
            $row = $dbh->getRow("SELECT id FROM releases ".
                                "WHERE package = $package_id ".
                                "AND state = '$version' ".
                                "ORDER BY releasedate DESC",
                                DB_FETCHMODE_ASSOC);
            if (PEAR::isError($row)) {
                return $row;
            }
            $release_id = $row['id'];
            if (!isset($release_id)) {
                return PEAR::raiseError("$package does not have any releases with state \"$version\"");
            }
        } else {
            // Get a specific release
            $row = $dbh->getRow("SELECT id FROM releases ".
                                "WHERE package = $package_id ".
                                "AND version = '$version'",
                                DB_FETCHMODE_ASSOC);
            if (PEAR::isError($row)) {
                return $row;
            }
            $release_id = $row['id'];
        }
        if (!isset($path) && isset($release_id)) {
            $sql = "SELECT fullpath, basename, id FROM files WHERE release = ".
                 $release_id;
            $row = $dbh->getRow($sql, DB_FETCHMODE_ORDERED);
            if (PEAR::isError($row)) {
                return $row;
            }
            list($path, $basename, $log_file) = $row;
            if (empty($path) || !@is_file($path)) {
                return PEAR::raiseError("release download:: no version information found");
            }
        }
        if (isset($path)) {
            if (!isset($log_release)) {
                $log_release = $release_id;
            }

            release::logDownload($package_id, $log_release, $log_file);

            header('Last-modified: ' . gmdate('D, d M Y H:i:s \G\M\T', filemtime($path)));
            header('Content-type: application/octet-stream');
            if ($uncompress) {
                $tarname = preg_replace('/\.tgz\z/', '.tar', $basename);
                header('Content-disposition: attachment; filename="'.$tarname.'"');
                readgzfile($path);
            } else {
                header('Content-disposition: attachment; filename="'.$basename.'"');
                header('Content-length: '.filesize($path));
                readfile($path);
            }

            return true;
        }
        header('HTTP/1.0 404 Not Found');
        echo 'File not found';
    }

    /**
     * Determine if release state is valid
     *
     * @static
     * @param string State
     * @return boolean
     */
    static function isValidState($state)
    {
        static $states = array('devel', 'snapshot', 'alpha', 'beta', 'stable');
        return in_array($state, $states);
    }

    /**
     * Convert a state into an array of less stable states
     *
     * @param string Release state
     * @param boolean include the state in the array returned
     * @return boolean
     */
    static function betterStates($state, $include = false)
    {
        static $states = array('snapshot', 'devel', 'alpha', 'beta', 'stable');
        $i = array_search($state, $states);
        if ($include) {
            $i--;
        }
        if ($i === false) {
            return false;
        }
        return array_slice($states, $i + 1);
    }

    /**
     * Log release download
     *
     * @param integer ID of the package
     * @param integer ID of the release
     * @param string Filename
     */
    static function logDownload($package, $release_id, $file = null)
    {
        global $dbh;

        $query = 'SELECT version, name, category FROM releases, packages'
               . ' WHERE package = ? AND releases.id = ? AND packages.id = releases.package';
        $pkginfo = $dbh->getAll($query, array($package, $release_id), DB_FETCHMODE_ASSOC);

        if (PEAR::isError($pkginfo) || !$pkginfo) {
            return PEAR::raiseError('release:: the package you requested'
                                    . ' has no release by that number');
        }

        $sql = '
            INSERT INTO aggregated_package_stats
                (package_id, release_id, yearmonth, downloads)
            VALUES(?, ?, ?, 1)
                ON DUPLICATE KEY UPDATE downloads = downloads + 1';
        $dbh->query($sql, array($package, $release_id, date('Y-m-01')));

        // {{{ Update package_stats table

        $query = '
            INSERT INTO package_stats
                (dl_number, package, `release`, pid, rid, cid, last_dl)
            VALUES (1, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    dl_number = dl_number + 1,
                    last_dl = "' . date('Y-m-d H:i:s') . '"';

        $dbh->query($query, array($pkginfo[0]['name'],
                                  $pkginfo[0]['version'],
                                  $package,
                                  $release_id,
                                  $pkginfo[0]['category'],
                                  date('Y-m-d H:i:s')
                                  )
                    );
        // }}}
    }

    /**
     * Promote new release
     *
     * @param PEAR_PackageFile_v1|PEAR_PackageFile_v2
     * @param string Filename of the new uploaded release
     * @return void
     */
    static function promote($pkginfo, $upload)
    {
        if ($_SERVER['SERVER_NAME'] != PEAR_CHANNELNAME) {
            return;
        }

        $package = $pkginfo->getPackage();
        $notes   = $pkginfo->getNotes();
        $desc    = $pkginfo->getDescription();
        $state   = $pkginfo->getState();

        include_once 'pear-database-package.php';
        $authors = package::info($package, 'authors');
        $txt_authors = '';
        foreach ($authors as $a) {
            if (!$a['active']) {
                continue;
            }
            $txt_authors .= $a['name'];
            if ($a['showemail']) {
                $txt_authors .= " <{$a['email']}>";
            }
            $txt_authors .= " ({$a['role']})\n";
        }

        $upload  = basename($upload);
        $release = "$package-$version ($state)";
        $channel = PEAR_CHANNELNAME;
        $txtanounce = <<<END
The new PEAR package $release has been released at http://$channel/.

Release notes
-------------
$notes

Package Info
------------
$desc

Related Links
-------------
Package home: http://$channel/package/$package
   Changelog: http://$channel/package/$package/download/$version
    Download: http://download.$channel/package/$upload

Authors
-------
$txt_authors
END;

        $to      = '"PEAR general list" <' . PEAR_GENERAL_EMAIL . '>';
        $from    = '"PEAR Announce" <' . PEAR_ANNOUNCE_EMAIL . '>';
        $subject = "[ANNOUNCEMENT] $release Released.";
        if (!DEVBOX) {
            mail($to, $subject, $txtanounce, "From: $from", '-f ' . PEAR_BOUNCE_EMAIL);
        }
    }

    /**
     * Remove release
     *
     * @param integer ID of the package
     * @param integer ID of the release
     * @return boolean
     */
    static function remove($package, $release)
    {
        global $dbh, $auth_user;
        include_once 'pear-database-user.php';
        if (!$auth_user->isAdmin() && !$auth_user->isQA() &&
            !user::maintains($auth_user->handle, $package, 'lead')) {
            return PEAR::raiseError('release::remove: insufficient privileges');
        }

        // get files that have to be removed
        $sql = 'SELECT fullpath FROM files WHERE package = ? AND `release` = ?';
        $sth = $dbh->query($sql, array($package, $release));

        // Should we error out if the removal fails ?
        $success = true;
        while ($row = $sth->fetchRow(DB_FETCHMODE_ASSOC)) {
            if (!@unlink($row['fullpath'])) {
                $success = false;
            }
        }

        $sql = 'DELETE FROM files WHERE package = ? AND `release` = ?';
        $sth = $dbh->query($sql, array($package, $release));

        $sql     = 'SELECT version from releases WHERE package = ? and id = ?';
        $version = $dbh->getOne($sql, array($package, $release));
        $query   = 'DELETE FROM releases WHERE package = ? AND id = ?';

        $sth = $dbh->query($query, array($package, $release));
        // remove statistics on this release
        $dbh->query('DELETE FROM package_stats WHERE pid = ? AND rid = ?', array($package, $release));
        $dbh->query('DELETE FROM aggregated_package_stats WHERE package_id = ? AND release_id = ?', array($package, $release));

        include_once 'pear-database-package.php';
        $pname = package::info($package, 'name');

        include_once 'pear-rest.php';
        $pear_rest = new pearweb_Channel_REST_Generator(PEAR_REST_PATH, $dbh);
        $pear_rest->saveAllReleasesREST($pname);
        $pear_rest->deleteReleaseREST($pname, $version);
        $pear_rest->savePackagesCategoryREST(package::info($pname, 'category'));

        if (PEAR::isError($sth)) {
            return false;
        }

        return true;
    }
}