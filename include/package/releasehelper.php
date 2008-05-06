<?php
class package_releasehelper
{
    var $_dbh;
    var $_info;
    var $_states = array();
    var $_lastversion;
    var $_laststate;
    var $_lastid;
    var $_pkgid;

    function __construct($package)
    {
        $this->_dbh = &$GLOBALS['dbh'];

        include_once 'pear-database-package.php';
        $this->_info  = package::info($package, 'releases');
        $this->_pkgid = package::info($package, 'id');
        if (is_array($this->_info) && $this->_info) {
            foreach ($this->_info as $ver => $release) {
                if (!isset($this->_lastversion)) {
                    $this->_lastversion = $ver;
                    $this->_laststate   = $release['state'];
                    $this->_lastid      = $release['id'];
                }
                $this->_states[$release['state']] = 1;
            }
        }
    }

    function hasOldPackagexml()
    {
        if (!isset($this->_lastid)) {
            return false;
        }

        $sql  = 'SELECT packagexml FROM files WHERE `release` = ? AND `package` = ?';
        $info = $this->_dbh->getOne($sql, array($this->_lastid, $this->_pkgid));
        return !preg_match('/<package[^>]+version\s*=\s*"2.0"/', $info);
    }

    function hasReleases()
    {
        return (bool) $this->_info;
    }

    function nextCanBeStable()
    {
        if ($this->_laststate == 'stable'
            || ($this->_laststate == 'beta' && strpos($this->_lastversion, 'RC'))
        ) {
            return true;
        }

        return false;
    }

    function canAddFeatures()
    {
        if ($this->_laststate == 'beta') {
            return false;
        }

        return true;
    }

    function lastWasReleaseCandidate()
    {
        return (bool) strpos($this->_lastversion, 'RC');
    }

    function getNextBugfixVersion()
    {
        $version = explode('.', $this->_lastversion);
        $last    = array_pop($version);
        if (!$version[0]) {
            if (isset($version[1]) && !$version[1]) {
                return array('0.1.0', $this->_laststate);
            }
        }

        if (count($version) == 1) {
            return array($version[0] . '.' . $last . '.1', $this->_laststate);
        }

        if (strpos($last, 'RC') !== false) {
            // release candidate
            if (!$version[0]) {
                return array('1.0.0RC1', 'beta');
            }

            if (preg_match('/RC([1-9][0-9]?)/', $last, $numbah)) {
                $last = str_replace($numbah[0], 'RC' . ($numbah[1] + 1), $last);
                return array(implode('.', $version) . '.' . $last, 'beta');
            }

            // crap version number "X.Y.ZRC", convert to good one "X.Y.ZRC1"
            return array(implode('.', $version) . '.' . $last[0] . 'RC1', 'beta');
        }

        if (strpos($last, 'a') !== false) {
            // alpha version
            if (preg_match('/a(?:lpha)?([1-9][0-9]?)?/', $last, $numbah)) {
                if (!$version[0]) {
                    // no need for 0.3.4a1 or any of that junk
                    return array(implode('.', $version) . '.' .
                        (str_replace($numbah[0], '', $last) + 1), $this->_laststate);
                }

                if (strlen($numbah[1])) {
                    $last = str_replace($numbah[0], 'a' . ($numbah[1] + 1), $last);
                    return array(implode('.', $version) . '.' . $last, 'alpha');
                }

                return array(implode('.', $version) . '.a2', 'alpha');
            }
        }

        if (strpos($last, 'b') !== false) {
            // beta version
            if (preg_match('/b(?:eta)?([1-9][0-9]?)/', $last, $numbah)) {
                if (!$version[0]) {
                    // no need for 0.3.4b1 or any of that junk
                    return array(implode('.', $version) . '.' .
                        (str_replace($numbah[0], '', $last) + 1), $this->_laststate);
                }
                if (strlen($numbah[1])) {
                    $last = str_replace($numbah[0], 'b' . ($numbah[1] + 1), $last);
                    return array(implode('.', $version) . '.' . $last, 'beta');
                }

                return array(implode('.', $version) . '.b' . $last, 'beta');
            }
        }

        return array(implode('.', $version) . '.' . ($last + 1), $this->_laststate);
    }

    function getNextBetaRelease()
    {
        $version = explode('.', $this->_lastversion);
        $last    = array_pop($version);
        if ($this->_laststate != 'alpha' && $this->_laststate != 'beta') {
            return false;
        }

        if (!$version[0]) {
            $newfeature = $this->getNewFeatureVersion();
            return array($newfeature[0] . ' or 1.0.0RC1', 'beta');
        }

        return array($version[0] . '.' . $version[1] . '.0RC1', 'beta');
    }

    function getNewFeatureVersion()
    {
        if (strpos($this->_lastversion, 'RC')) {
            return array(preg_replace('/RC.*\z/', '', $this->_lastversion), 'stable');
        }

        $version = explode('.', $this->_lastversion);
        $last    = array_pop($version);
        if (strpos($last, 'a') !== false) {
            if (!$version[0]) {
                // no need for 0.3.4a1 or any of that junk
                return array(implode('.', $version) . '.' .
                    (str_replace($numbah[0], '', $last) + 1), 'alpha');
            }

            // alpha version
            if (preg_match('/a(?:lpha)?([1-9][0-9]?)?/', $last, $numbah)) {
                if (strlen($numbah[1])) {
                    $last = str_replace($numbah[0], 'a' . ($numbah[1] + 1), $last);
                    return array(implode('.', $version) . '.' . $last, 'alpha');
                }

                return array(implode('.', $version) . '.a2', 'alpha');
            }
        }

        if (strpos($last, 'b') !== false) {
            if (!$version[0]) {
                // no need for 0.3.4b1 or any of that junk
                return array(implode('.', $version) . '.' .
                    (str_replace($numbah[0], '', $last) + 1), 'alpha');
            }

            // beta version
            if (preg_match('/b(?:eta)?([1-9][0-9]?)/', $last, $numbah)) {
                if (strlen($numbah[1])) {
                    $last = str_replace($numbah[0], 'b' . ($numbah[1] + 1), $last);
                    return array(implode('.', $version) . '.' . $last, 'beta');
                }

                return array(implode('.', $version) . '.b' . $last, 'beta');
            }
        }

        $version = explode('.', $this->_lastversion);
        if ($this->_laststate == 'stable') {
            return array($version[0] . '.' . ($version[1] + 1) . '.0a1', 'alpha');
        }

        if (!$version[0]) {
            return array($version[0] . '.' . ($version[1] + 1) . '.0', 'alpha');
        }
    }
}