<?php
/**
 * Manage dependencies
 * 
 * To be used like:
 * <code>
 * // reset deps
 * $pf->dependencies = null;
 * // for PHP dep
 * $pf->dependencies->required->php = array('min' => '5.3.0', 'max' => '7.0.0',
 *      'exclude' => array('6.1.2'));
 * // for PEAR Installer dep
 * $pf->dependencies->required->pearinstaller = array('min' => '2.0.0');
 * // for required/optional package deps or subpackage deps
 * $pf->dependencies->required->package['channel/PackageName'] =
 *      array('min' => '1.1.0', 'max' => '1.2.0', 'recommended' => '1.1.1',
 *            'exclude' => array('1.1.0a1', '1.1.0a2'));
 * $pf->dependencies->optional->package['channel/PackageName'] =
 *      array('min' => '1.1.0', 'max' => '1.2.0', 'recommended' => '1.1.1',
 *            'exclude' => array('1.1.0a1', '1.1.0a2'));
 * $pf->dependencies->required->subpackage['channel/PackageName'] =
 *      array('min' => '1.1.0', 'max' => '1.2.0', 'recommended' => '1.1.1',
 *            'exclude' => array('1.1.0a1', '1.1.0a2'));
 * // for conflicting package dep
 * $pf->dependencies->required->package['channel/PackageName']->conflicts = true;
 * // for PECL extension deps (optional or required same as packages)
 * $pf->dependencies->required->package['channel/PackageName'] =
 *      array('min' => '1.1.0', 'max' => '1.2.0', 'recommended' => '1.1.1',
 *            'exclude' => array('1.1.0a1', '1.1.0a2'), 'providesextension' => 'packagename');
 * // for extension deps (required or optional same as packages)
 * $pf->dependencies->required->extension['extension'] =
 *      array('min' => '1.0.0', 'max' => '1.2.0', 'recommended' => '1.1.1');
 * // for regular arch deps
 * $pf->dependencies->required->arch['i386'] = true // only works on i386
 * // for conflicting arch deps
 * $pf->dependencies->required->arch['*(ix|ux)'] = false // doesn't work on unix/linux
 * // for regular OS deps
 * $pf->dependencies->required->os['windows'] = true; // only works on windows
 * // for conflicting OS deps
 * $pf->dependencies->required->os['freebsd'] = false; // doesn't work on FreeBSD
 * 
 * // dependency group setup
 * $group = $pf->dependencies->group['name']->hint('Install optional stuff as a group');
 * $group->package['channel/PackageName1'] = array();
 * $group->package['channel/PackageName2'] = array('min' => '1.2.0');
 * $group->subpackage['channel/PackageName3'] = array('recommended' => '1.2.1');
 * $group->extension['extension'] = array();
 * </code>
 */
class PEAR2_Pyrus_PackageFile_v2_Dependencies implements ArrayAccess
{
    private $_parent;
    private $_packageInfo;
    private $_required;
    private $_group = null;
    private $_package;
    private $_type;
    private $_info = array();
    function __construct(array &$parent, array &$packageInfo, $required = null, $type = null,
                         $package = null, $group = null)
    {
        $this->_parent = &$parent;
        $this->_packageInfo = &$packageInfo;
        if (!$required) return;
        if (!in_array($required, array('required', 'optional', 'group'), true)) {
            throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception(
                'Internal error: $required is not required/optional/group');
        }
        $this->_required = $required;
        if (!isset($parent[$required])) {
            $parent[$required] = array();
        }
        $this->_packageInfo = &$parent[$required];
        if ($this->_required != 'group' && $group) {
            throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception(
                'Internal error: $group passed into required dependency');
        } elseif (!is_string($group)) {
            throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception(
                'Internal error: $group must be a string');
        } elseif ($group) {
            $this->_group = $group;
            // locate group in the xml and initialize if not present
            if (!isset($this->_packageInfo[0])) {
                if ($this->_packageInfo['attribs']['name'] != $group) {
                    $this->_packageInfo = array($this->_packageInfo,
                        array('attribs' => array('name' => $group, 'hint' => '')));
                    $this->_packageInfo = &$this->_packageInfo[1];
                }
            } else {
                foreach ($this->_packageInfo as $i => $g) {
                    if ($g['attribs']['name'] == $group) {
                        $this->_packageInfo = &$this->_packageInfo[$i];
                        break;
                    }
                }
                $this->_packageInfo[$i = count($this->_packageInfo)] =
                    array('attribs' => array('name' => $group, 'hint' => ''));
            }
        }
        if (!$type) return;
        if (!is_string($type)) {
            throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception(
                        'Internal error: $type is not a string, but is a ' . gettype($type));
        }
        if ($required == 'required') {
            switch ($type) {
                case 'php' :
                case 'pearinstaller' :
                case 'package' :
                case 'subpackage' :
                case 'extension' :
                case 'arch' :
                case 'os' :
                    $this->_type = $type;
                    if (!isset($this->_packageInfo[$type])) {
                        $this->_packageInfo[$type] = array();
                    }
                    $this->_packageInfo = &$this->_packageInfo[$type];
                    break;
                default :
                    throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception(
                        'Unknown dependency type ' . $type);
            }
        } else {
            switch ($type) {
                case 'package' :
                case 'subpackage' :
                case 'extension' :
                    $this->_type = $type;
                    if (!isset($this->_packageInfo[$type])) {
                        $this->_packageInfo[$type] = array();
                    }
                    $this->_packageInfo = &$this->_packageInfo[$type];
                    break;
                case 'php' :
                case 'pearinstaller' :
                case 'arch' :
                case 'os' :
                default :
                    throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception(
                        $type . ' dependency cannot be optional');
            }
        }
        if (!$package) return;
        switch ($this->_type) {
            case 'php' :
            case 'pearinstaller' :
                throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception(
                    'Internal error: $package passed into ' . $type . ' dependency');
            case 'package' :
            case 'subpackage' :
                $channel = explode('/', $package);
                $package = array_pop($channel);
                $channel = implode('/', $channel);
                $this->_info['name'] = $package;
                $this->_info['channel'] = $channel;
                $this->_package = true;
                break;
            case 'os' :
            case 'extension' :
                $this->_info['name'] = $package;
                $this->_package = true;
                break;
            case 'arch' :
                $this->_info['pattern'] = $package;
                $this->_package = true;
                break;
        }
        if (!isset($this->_packageInfo[0])) {
            $name = ($this->_type == 'arch') ? 'pattern' : 'name';
            if ($this->_packageInfo[$name] != $package) {
                $this->_packageInfo = array($this->_packageInfo, array($name => $package));
                $this->_packageInfo = &$this->_packageInfo[1];
            }
        } else {
            foreach ($this->_packageInfo as $i => $dep) {
                if ($dep[$name] == $package) {
                    $this->_packageInfo = &$this->_packageInfo[$i];
                    return;
                }
            }
            $this->_packageInfo[$i = count($this->_packageInfo)] = array($name => $package);
            $this->_packageInfo = &$this->_packageInfo[$i];
        }
    }

    function __get($var)
    {
        if (!isset($this->_required)) {
            return new PEAR2_Pyrus_PackageFile_v2_Dependencies($this->_parent,
                $this->_packageInfo, $var);
        }
        if (!isset($this->_type)) {
            return new PEAR2_Pyrus_PackageFile_v2_Dependencies(
                $this->_parent, $this->$packageInfo[$var], $this->_required,
                $this->_type);
        }
        if ($this->_type == 'group' && !isset($this->_group)) {
            throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception(
                'Dependency group must be accessed like $pf->group[\'groupname\']');
        }
        if (!isset($this->_package) && $this->_type != 'php' && $this->_type != 'pearinstaller') {
            return new PEAR2_Pyrus_PackageFile_v2_Dependencies(
                $this->_parent, $this->$packageInfo[$var], $this->_required,
                $this->_type, $var);
        }
        if (!isset($this->_packageInfo[$var])) {
            return null;
        }
        return $this->_packageInfo[$var];
    }

    function __call($var, $args)
    {
        if (!$this->_package) {
            throw new PEAR2_Pyrus_PackageFile_v2_Compatible_Exception(
                'Cannnot access developer info for unknown developer');
        }
        if ($var == 'min') {
            if (count($args) != 1 || !is_string($args[0])) {
                throw new PEAR2_Pyrus_PackageFile_v2_Compatible_Exception(
                    'Invalid value for min');
            }
            $this->_info['min'] = $args[0];
        } elseif ($var == 'max') {
            if (count($args) != 1 || !is_string($args[0])) {
                throw new PEAR2_Pyrus_PackageFile_v2_Compatible_Exception(
                    'Invalid value for max');
            }
            $this->_info['max'] = $args[0];
        } elseif ($var == 'exclude') {
            foreach ($args as $arg) {
                if (!is_string($arg)) {
                    throw new PEAR2_Pyrus_PackageFile_v2_Compatible_Exception(
                        'Invalid value for exclude');
                }
            }
            $this->_info['exclude'] = (count($args) == 1) ? $args[0] : $args[1];
        } else {
            throw new PEAR2_Pyrus_PackageFile_v2_Compatible_Exception(
                        'Unknown value to set: ' . $var);
        }
        return $this;
    }

    function offsetGet($var)
    {
        if ($this->_type == 'group' && !isset($this->_group)) {
            return new PEAR2_Pyrus_PackageFile_v2_Dependencies(
                $this->_parent, $this->$packageInfo[$var], $this->_required,
                $this->_type, null, $var);
        }
            return new PEAR2_Pyrus_PackageFile_v2_Dependencies(
                $this->_parent, $this->$packageInfo[$var], $this->_required,
                $this->_type, $var, $this->_group);
    }

    function offsetSet($var, $value)
    {
    }

    /**
     * Remove a compatible package from package.xml (by channel/package)
     * @param string $var
     */
    function offsetUnset($var)
    {
    }

    /**
     * Test whether compatible package exists in package.xml (by channel/package)
     * @param string $var
     * @return bool
     */
    function offsetExists($var)
    {
    }

    /**
     * Save changes
     */
    private function _save()
    {
    }
}