<?php
class pear_rest
{
    var $_restdir;
    function pear_rest($base)
    {
        $this->_restdir = $base;
    }

    function saveCategoryREST($category)
    {
        require_once 'System.php';
        global $dbh;
        $extra = '/rest/';
        $cdir = $this->_restdir . DIRECTORY_SEPARATOR . 'c';
        $category = $dbh->getAll('SELECT * FROM categories WHERE name = ?', array($category),
            DB_FETCHMODE_ASSOC);
        $category = $category[0];
        if (!file_exists($cdir . DIRECTORY_SEPARATOR . urlencode($category['name']))) {
            System::mkdir(array('-p', $cdir . DIRECTORY_SEPARATOR . urlencode($category['name'])));
            @chmod($cdir . DIRECTORY_SEPARATOR . urlencode($category['name']), 0777);
        }
        $info = '<?xml version="1.0"?>
<c xmlns="http://pear.php.net/dtd/rest.category
    xsi:schemaLocation="http://pear.php.net/dtd/rest.category
    http://pear.php.net/dtd/rest.category.xsd">
 <n>' . htmlspecialchars($category['name']) . '</n>
 <c>' . PEAR_CHANNELNAME . '</c>
 <a>' . htmlspecialchars($category['name']) . '</a>
 <d>' . htmlspecialchars($category['description']) . '</d>
</c>';
        // category info
        file_put_contents($cdir . DIRECTORY_SEPARATOR . urlencode($category['name']) .
            DIRECTORY_SEPARATOR . 'info.xml', $info);
        @chmod($cdir . DIRECTORY_SEPARATOR . urlencode($category['name']) .
            DIRECTORY_SEPARATOR . 'info.xml', 0666);
        $list = '<?xml version="1.0"?>
<l xmlns="http://pear.php.net/dtd/rest.categorypackages
    xsi:schemaLocation="http://pear.php.net/dtd/rest.categorypackages
    http://pear.php.net/dtd/rest.categorypackages.xsd">
';
        $query = "SELECT p.name AS name " .
            "FROM packages p, categories c " .
            "WHERE p.package_type = 'pear' " .
            "AND p.category = c.id AND c.name = ? AND p.approved = 1";

        $sth = $dbh->getAll($query, array($category['name']), DB_FETCHMODE_ASSOC);
        foreach ($sth as $package) {
            $list .= ' <p xlink:href="' . $extra . 'p/' . strtolower($package['name']) . '">' .
                $package['name'] . '</p>
';
        }
        $list .= '</l>';
        // list packages in a category
        file_put_contents($cdir . DIRECTORY_SEPARATOR . urlencode($category['name']) .
            DIRECTORY_SEPARATOR . 'packages.xml', $list);
        @chmod($cdir . DIRECTORY_SEPARATOR . urlencode($category['name']) .
            DIRECTORY_SEPARATOR . 'packages.xml', 0666);
    }

    function deleteCategoryREST($category)
    {
        require_once 'System.php';
        $cdir = $this->_restdir . DIRECTORY_SEPARATOR . 'c';
        if (!file_exists($cdir . DIRECTORY_SEPARATOR . urlencode($category))) {
            return;
        }
        // remove all category info
        System::rm(array('-r', $this->_restdir . DIRECTORY_SEPARATOR . 'c'
            . DIRECTORY_SEPARATOR . urlencode($category)));
    }

    function saveAllPackagesREST()
    {
        $pdir = $this->_restdir . DIRECTORY_SEPARATOR . 'p';
        if (!file_exists($pdir)) {
            System::mkdir(array('-p', $pdir));
            @chmod($pdir, 0777);
        }

        $info = '<?xml version="1.0" ?>
<a xmlns="http://pear.php.net/dtd/rest.allpackages
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allpackages
    http://pear.php.net/dtd/rest.allpackages.xsd">
<c>' . PEAR_CHANNELNAME . '</c>
';
        foreach (package::listAll(false, false, false) as $package => $gh)
        {
            $info .= ' <p>' . $package . '</p>
';
        }
        $info .= '</a>';
        file_put_contents($pdir . DIRECTORY_SEPARATOR . 'packages.xml', $info);
        @chmod($pdir . DIRECTORY_SEPARATOR . 'packages.xml', 0666);
    }

    function savePackageREST($package)
    {
        require_once 'System.php';
        global $dbh;
        $extra = '/rest/';
        $package = package::info($package);

        $pdir = $this->_restdir . DIRECTORY_SEPARATOR . 'p';
        if (!file_exists($pdir . DIRECTORY_SEPARATOR . strtolower($package['name']))) {
            System::mkdir(array('-p', $pdir . DIRECTORY_SEPARATOR .
                strtolower($package['name'])));
            @chmod($pdir . DIRECTORY_SEPARATOR . strtolower($package['name']), 0777);
        }
        $catinfo = $dbh->getOne('SELECT c.name FROM packages, categories c WHERE 
            c.id = ?', array($package['categoryid']), DB_FETCHMODE_ASSOC);
        if (isset($package['parent']) && $package['parent']) {
            $parent = '
 <pa xlink:href="' . $extra . 'p/' . $package['parent'] . '">' . 
                $package['parent'] . '</pa>';
        } else {
            $parent = '';
        }
        if (isset($package['deprecated_package']) && $package['deprecated_package']) {
            if ($package['deprecated_channel'] == PEAR_CHANNELNAME) {
                $deprecated = '
 <dc>' . $package['deprecated_channel'] . '</dc>
 <dp href="' . $extra . 'p/' . $package['deprecated_package'] . '"> ' .
                $package['deprecated_package'] . '</dp>';
            } else {
                $deprecated = '
 <dc>' . $package['deprecated_channel'] . '</dc>
 <dp> ' . $package['deprecated_package'] . '</dp>';
            }
        } else {
            $deprecated = '';
        }
        $info = '<?xml version="1.0"?>
<p xmlns="http://pear.php.net/dtd/rest.package
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>' . $package['name'] . '</n>
 <c>' . PEAR_CHANNELNAME . '</c>
 <ca xlink:href="' . $extra . 'c/' . htmlspecialchars(urlencode($catinfo)) . '">' .
        htmlspecialchars($catinfo) . '</ca>
 <l>' . $package['license'] . '</l>
 <s>' . htmlspecialchars($package['summary']) . '</s>
 <d>' . htmlspecialchars($package['description']) . '</d>
 <r xlink:href="' . $extra . 'r/' . $package['name'] . '"/>' . $parent . $deprecated . '
</p>';
        // package information
        file_put_contents($pdir . DIRECTORY_SEPARATOR . strtolower($package['name']) .
            DIRECTORY_SEPARATOR . 'info.xml', $info);
        @chmod($pdir . DIRECTORY_SEPARATOR . strtolower($package['name']) .
            DIRECTORY_SEPARATOR . 'info.xml', 0666);
    }

    function deletePackageREST($package)
    {
        require_once 'System.php';
        $pdir = $this->_restdir . DIRECTORY_SEPARATOR . 'p';
        $rdir = $this->_restdir . DIRECTORY_SEPARATOR . 'r';
        // remove all package/release info for this package
        System::rm(array('-r', $pdir . DIRECTORY_SEPARATOR . $package));
        System::rm(array('-r', $rdir . DIRECTORY_SEPARATOR . $package));
    }

    function saveAllReleasesREST($package)
    {
        require_once 'System.php';
        global $dbh;
        $extra = '/rest/';
        $pid = package::info($package, 'id');
        $releases = $dbh->getAll('SELECT * FROM releases WHERE package = ? ORDER BY releasedate DESC',
            array($pid), DB_FETCHMODE_ASSOC);
        $rdir = $this->_restdir . DIRECTORY_SEPARATOR . 'r';
        if (!$releases || !count($releases)) {
            // remove stragglers if no releases are found
            @unlink($rdir . DIRECTORY_SEPARATOR . strtolower($package));
            return;
        }
        $info = '<?xml version="1.0"?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>' . $package . '</p>
 <c>' . PEAR_CHANNELNAME . '</c>
';
        foreach ($releases as $release) {
            if (!isset($latest)) {
                $latest = $release['version'];
            }
            if ($release['state'] == 'stable' && !isset($stable)) {
                $stable = $release['version'];
            }
            if ($release['state'] == 'beta' && !isset($beta)) {
                $beta = $release['version'];
            }
            if ($release['state'] == 'alpha' && !isset($alpha)) {
                $alpha = $release['version'];
            }
            $info .= ' <r><v>' . $release['version'] . '</v><s>' . $release['state'] . '</s></r>
';
        }
        $info .= '</a>';
        if (!file_exists($rdir . DIRECTORY_SEPARATOR . strtolower($package))) {
            System::mkdir(array('-p', $rdir . DIRECTORY_SEPARATOR . strtolower($package)));
            @chmod($rdir . DIRECTORY_SEPARATOR . strtolower($package), 0777);
        }
        file_put_contents($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
            DIRECTORY_SEPARATOR . 'allreleases.xml', $info);
        @chmod($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
            DIRECTORY_SEPARATOR . 'allreleases.xml', 0666);

        file_put_contents($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
            DIRECTORY_SEPARATOR . 'latest.txt', $latest);
        @chmod($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
            DIRECTORY_SEPARATOR . 'latest.txt', 0666);
        if (isset($stable)) {
            file_put_contents($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
                DIRECTORY_SEPARATOR . 'stable.txt', $stable);
            @chmod($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
                DIRECTORY_SEPARATOR . 'stable.txt', 0666);
        }
        if (isset($beta)) {
            file_put_contents($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
                DIRECTORY_SEPARATOR . 'beta.txt', $beta);
            @chmod($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
                DIRECTORY_SEPARATOR . 'beta.txt', 0666);
        }
        if (isset($alpha)) {
            file_put_contents($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
                DIRECTORY_SEPARATOR . 'alpha.txt', $alpha);
            @chmod($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
                DIRECTORY_SEPARATOR . 'alpha.txt', 0666);
        }
    }

    function deleteReleaseREST($package, $version)
    {
        require_once 'System.php';
        $rdir = $this->_restdir . DIRECTORY_SEPARATOR . 'r';
        if (@file_exists($rdir . DIRECTORY_SEPARATOR . strtolower($package))) {
            @unlink($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
                DIRECTORY_SEPARATOR . $version . '.xml');
            @unlink($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
                DIRECTORY_SEPARATOR . 'package.' . $version . '.xml');
            @unlink($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
                DIRECTORY_SEPARATOR . 'deps.' . $version . '.txt');
        }
    }

    function saveReleaseREST($filepath, $packagexml, $pkgobj, $releasedby, $id)
    {
        require_once 'System.php';
        global $dbh;
        $extra = '/rest/';
        $rdir = $this->_restdir . DIRECTORY_SEPARATOR . 'r';

        $package = $pkgobj->getPackage();
        if (!file_exists($rdir . DIRECTORY_SEPARATOR . strtolower($package))) {
            System::mkdir(array('-p', $rdir . DIRECTORY_SEPARATOR . strtolower($package)));
            @chmod($rdir . DIRECTORY_SEPARATOR . strtolower($package), 0777);
        }

        $releasedate = $dbh->getOne('SELECT releasedate FROM releases WHERE id = ?',
            array($id));
        $info = '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="' . $extra . 'p/' . strtolower($package) . '">' . $package . '</p>
 <c>' . PEAR_CHANNELNAME . '</c>
 <v>' . $pkgobj->getVersion() . '</v>
 <st>' . $pkgobj->getState() . '</st>
 <l>' . $pkgobj->getLicense() . '</l>
 <m>' . $releasedby . '</m>
 <s>' . htmlspecialchars($pkgobj->getSummary()) . '</s>
 <d>' . htmlspecialchars($pkgobj->getDescription()) . '</d>
 <da>' . $releasedate . '</da>
 <n>' . htmlspecialchars($pkgobj->getNotes()) . '</n>
 <f>' . filesize($filepath) . '</f>
 <g>http://' . PEAR_CHANNELNAME . '/get/' . $package . '-' . $pkgobj->getVersion() . '</g>
 <x xlink:href="package.' . $pkgobj->getVersion() . '.xml"/>
</r>';
        file_put_contents($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
            DIRECTORY_SEPARATOR . $pkgobj->getVersion() . '.xml', $info);
        @chmod($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
            DIRECTORY_SEPARATOR . $pkgobj->getVersion() . '.xml', 0666);
        file_put_contents($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
            DIRECTORY_SEPARATOR . 'package.' .
            $pkgobj->getVersion() . '.xml', $packagexml);
        @chmod($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
            DIRECTORY_SEPARATOR . 'package.' . $pkgobj->getVersion() . '.xml', 0666);
        file_put_contents($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
            DIRECTORY_SEPARATOR . 'deps.' . $pkgobj->getVersion() . '.txt', serialize($pkgobj->getDeps(true)));
        @chmod($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
            DIRECTORY_SEPARATOR . 'deps.' . $pkgobj->getVersion() . '.txt', 0666);
    }

    function deleteMaintainerREST($handle)
    {
        require_once 'System.php';
        $mdir = $this->_restdir . DIRECTORY_SEPARATOR . 'm';
        if (file_exists($mdir . DIRECTORY_SEPARATOR . $handle)) {
            System::rm(array('-r', $mdir . DIRECTORY_SEPARATOR . $handle));
        }
    }

    function savePackageMaintainerREST($package)
    {
        require_once 'System.php';
        global $dbh;
        $pid = package::info($package, 'id');
        $maintainers = $dbh->getAll('SELECT * FROM maintains WHERE package = ?', array($pid),
            DB_FETCHMODE_ASSOC);
        $extra = '/rest/';
        if (count($maintainers)) {
            $pdir = $this->_restdir . DIRECTORY_SEPARATOR . 'p';
            if (!file_exists($pdir . DIRECTORY_SEPARATOR . strtolower($package))) {
                System::mkdir(array('-p', $pdir . DIRECTORY_SEPARATOR . strtolower($package)));
                @chmod($pdir . DIRECTORY_SEPARATOR . strtolower($package), 0777);
            }
            $info = '<?xml version="1.0"?>
<m xmlns="http://pear.php.net/dtd/rest.packagemaintainers
    xsi:schemaLocation="http://pear.php.net/dtd/rest.packagemaintainers
    http://pear.php.net/dtd/rest.packagemaintainers.xsd">
 <p>' . $package . '</p>
 <c>' . PEAR_CHANNELNAME . '</c>
';
            foreach ($maintainers as $maintainer) {
                $info .= ' <m><h>' . $maintainer['handle'] . '</h><a>' . $maintainer['active'] .
                    '</a></m>';
            }
            $info .= '</m>';
            file_put_contents($pdir . DIRECTORY_SEPARATOR . strtolower($package) .
                DIRECTORY_SEPARATOR . 'maintainers.xml', $info);
            @chmod($pdir . DIRECTORY_SEPARATOR . strtolower($package) .
                DIRECTORY_SEPARATOR . 'maintainers.xml', 0666);
        } else {
            @unlink($pdir . DIRECTORY_SEPARATOR . strtolower($package) .
                DIRECTORY_SEPARATOR . 'maintainers.xml');
        }
    }

    function saveMaintainerREST($maintainer)
    {
        require_once 'System.php';
        global $dbh;
        $maintainer = $dbh->getAll('SELECT * FROM users WHERE handle = ?',
            array($maintainer), DB_FETCHMODE_ASSOC);
        $maintainer = $maintainer[0];
        $extra = '/rest/';
        $mdir = $this->_restdir . DIRECTORY_SEPARATOR . 'm';
        if (!file_exists($mdir . DIRECTORY_SEPARATOR . $maintainer['handle'])) {
            System::mkdir(array('-p', $mdir . DIRECTORY_SEPARATOR . $maintainer['handle']));
            @chmod($mdir . DIRECTORY_SEPARATOR . $maintainer['handle'], 0777);
        }
        if ($maintainer['homepage']) {
            $uri = ' <u>' . htmlspecialchars($maintainer['homepage']) . '</u>
';
        } else {
            $uri = '';
        }
        $info = '<?xml version="1.0"?>
<m xmlns="http://pear.php.net/dtd/rest.maintainer
    xsi:schemaLocation="http://pear.php.net/dtd/rest.maintainer
    http://pear.php.net/dtd/rest.maintainer.xsd">
 <h>' . $maintainer['handle'] . '</h>
 <n>' . htmlentities($maintainer['name']) . '</n>
' . $uri . '</m>';
        // package information
        file_put_contents($mdir . DIRECTORY_SEPARATOR . $maintainer['handle'] .
            DIRECTORY_SEPARATOR . 'info.xml', $info);
        @chmod($mdir . DIRECTORY_SEPARATOR . $maintainer['handle'] .
            DIRECTORY_SEPARATOR . 'info.xml', 0666);
    }
}
if (!function_exists('file_put_contents')) {
    function file_put_contents($fname, $contents)
    {
        $fp = fopen($fname, 'wb');
        fwrite($fp, $contents);
        fclose($fp);
    }
}
?>