<?php
/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @copyright       The XUUPS Project http://www.xuups.com
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @package         MyPoints
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @version         $Id: functions.php 0 2009-11-14 18:47:04Z trabis $
 */

defined('XOOPS_ROOT_PATH') or die("XOOPS root path not defined");

function mypoints_pluginExecute($dirname, $items, $since, $func = 'useritems_count')
{
    global $xoopsUser, $xoopsConfig, $xoopsDB;

    $ret = array();
    $plugins_path = XOOPS_ROOT_PATH . '/modules/mypoints/plugins';
    $plugin_info = mypoints_getPluginInfo($dirname , $func) ;

    if (empty($plugin_info) || empty($plugin_info['plugin_path'])) {
        return false;
    }

    include_once $plugin_info['plugin_path'];

    // call the plugin
    if (function_exists(@$plugin_info['func'])) {
        // get the list of items
        $ret = $plugin_info['func']($items, $since);
    }

    return $ret;
}

function mypoints_getPluginInfo($dirname , $func = 'useritems_count')
{
    global $xoopsConfig;
    $language = $xoopsConfig['language'];
    // get $mytrustdirname for D3 modules
    $mytrustdirname = '' ;
    if (defined('XOOPS_TRUST_PATH') && file_exists(XOOPS_ROOT_PATH . "/modules/{$dirname}/mytrustdirname.php")) {
        @include XOOPS_ROOT_PATH . "/modules/{$dirname}/mytrustdirname.php";
        $d3module_plugin_file = XOOPS_TRUST_PATH . "/modules/{$mytrustdirname}/include/mypoints.plugin.php";
    }

    $module_plugin_file  = XOOPS_ROOT_PATH . "/modules/{$dirname}/include/mypoints.plugin.php" ;
    $builtin_plugin_file = XOOPS_ROOT_PATH . "/modules/mypoints/plugins/{$dirname}.php" ;

    if (file_exists($module_plugin_file)) {
        // module side (1st priority)
        $ret = array(
            'plugin_path' => $module_plugin_file,
            'func' => $dirname . '_' . $func,
            'type' => 'module');
    } else if (!empty($mytrustdirname) && file_exists($d3module_plugin_file)) {
        // D3 module's plugin under xoops_trust_path (2nd priority)
        $ret = array(
            'plugin_path' => $d3module_plugin_file,
            'func' => $mytrustdirname . '_' . $func,
            'type' => 'module (D3)');
    } else if (file_exists($builtin_plugin_file)) {
        // built-in plugin under modules/mypoints (3rd priority)
        $ret = array(
            'plugin_path' => $builtin_plugin_file,
            'func' => $dirname . '_' . $func,
            'type' => 'built-in');
    } else {
        $ret = array();
    }

    return $ret;
}

//////
// Update the Users Scores (refresh table)
//////
function mypoints_updatePoints($force = 0)
{
    global $xoopsDB, $xoopsModuleConfig;

    $module_handler   = xoops_gethandler('module');
    $plugin_handler   = xoops_getmodulehandler('plugin');
    $user_handler     = xoops_getmodulehandler('user');
    $relation_handler = xoops_getmodulehandler('relation');

    $refreshtime = $xoopsModuleConfig['refreshtime'];
    $since = strtotime($xoopsModuleConfig['countsince']);
    $countwebm = $xoopsModuleConfig['countadmin'];

    $user = $user_handler->get(0);
    $timestamp = 0;
    if (is_object($user)) {
        $timestamp = $user->getVar('useruname');
    }

    if (((time() - $timestamp) >= $refreshtime) || $force == 1) {
        // Timer expired, update table
        // Set date of update
        $user_handler->deleteAll();
        $relation_handler->deleteAll();

        $user = $user_handler->create();
        $user->setVar('useruid', 0);
        $user->setVar('useruname', time());
        $user->setVar('userpoints', 0);
        $user_handler->insert($user);

        // Prep to calculate user points
        if ($countwebm == 0) {
            $query = $xoopsDB->query("SELECT uid, uname FROM " . $xoopsDB->prefix("users") . " WHERE rank = '0' ORDER BY uid");
        } else {
            $query = $xoopsDB->query("SELECT uid, uname FROM " . $xoopsDB->prefix("users") . " ORDER BY uid");
        }
        $users = array();
        while (list($uid,$uname) = $xoopsDB->fetchRow($query)) {

            // Calculate User Points
            $points = 0;
            $criteria = new CriteriaCompo(new Criteria('pluginisactive', 1));
            //$criteria->add(new Criteria('plugintype', 'items'), 'AND');
            $plugins = $plugin_handler->getObjects($criteria);
            foreach ($plugins as $plugin) {
                $moduleid = $plugin->getVar('pluginmid');
                $module = $module_handler->get($moduleid);
                $count = mypoints_pluginExecute($module->getVar('dirname') , $uid, $since, 'user' . $plugin->getVar('plugintype') . '_count');
                if ($count > 0) {
                    $relation = $relation_handler->create();
                    $relation->setVar('relationuid', $uid);
                    $relation->setVar('relationpid', $plugin->getVar('pluginid'));
                    $relation->setVar('relationpoints', $count);
                    $relation_handler->insert($relation);
                    unset($relation);
                    $points = $points + ($count * $plugin->getVar('pluginmulti'));
                }
                unset($module);
            }

            if ($points > 0) {
                $user = $user_handler->create();
                $user->setVar('useruid', $uid);
                $user->setVar('useruname', $uname);
                $user->setVar('userpoints', $points);
                $user_handler->insert($user);
                unset($user);
            }
        }
    }
}
?>