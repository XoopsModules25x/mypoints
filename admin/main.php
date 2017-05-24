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
 * @version         $Id: main.php 0 2009-11-14 18:47:04Z trabis $
 */
include_once dirname(__FILE__) . '/admin_header.php';

$choice = isset($_REQUEST['op']) ? $_REQUEST['op'] : '';

function mypoints_index()
{
    global $xoopsUser, $xoopsModule, $xoopsModuleConfig, $indexAdmin;

    $plugin_handler = xoops_getmodulehandler('plugin');
    $indexAdmin = new ModuleAdmin();
    xoops_cp_header();

    echo $indexAdmin->addNavigation('main.php');
    //mypoints_adminmenu(0);
    $module_handler = xoops_gethandler('module');
    $since = $xoopsModuleConfig['countsince'];

    $criteria = new CriteriaCompo(new Criteria('isactive', 1));
    $modules = $module_handler->getObjects($criteria, true);
    unset($criteria);
    //get list of useritems_count plugins
    $items_plugins = array();
    foreach ($modules as $moduleid => $module) {
        $info = array();
        $info = mypoints_getPluginInfo( $module->getVar('dirname') , 'useritems_count');
        if (is_array($info) && count($info) > 0) {
            include_once $info['plugin_path'];
            if (function_exists(@$info['func'])) {
                $items_plugins[$moduleid] = $module;
            }
        }
        unset($info);
    }

    //get list of uservotes_count plugins
    $votes_plugins = array();
    foreach ($modules as $moduleid=>$module) {
        $info = array();
        $info = mypoints_getPluginInfo( $module->getVar('dirname') , 'uservotes_count');
        if (is_array($info) && count($info) > 0) {
            include_once $info['plugin_path'];
            if (function_exists(@$info['func'])) {
                $votes_plugins[$moduleid] = $module;
            }
        }
        unset($info);
    }

    echo "<h3>" . _AM_MYPOINTS_PLUGINS . "</h3>";
    echo "<form action ='main.php?op=submit' method=post>";
    echo "<table border = '0' cellpadding = '2' cellspacing = '1' width=100% class = outer>";
    echo "<tr class = bg3><td>" . _AM_MYPOINTS_MODULENAME . "</td><td>"
    . _AM_MYPOINTS_PLUGINTYPE . "</td><td>"
    . _AM_MYPOINTS_PLUGINNAME . "</td><td>"
    . _AM_MYPOINTS_STATUS . "</td><td>&nbsp;</td><td>"
    . _AM_MYPOINTS_POINTS . "</td></tr>";

    foreach ($items_plugins as $moduleid => $module) {
        $plugin = $plugin_handler->getByModuleType($moduleid, 'items');
        $actif  = is_object($plugin) ? $plugin->getVar('pluginisactive') : 0;
        $multi  = is_object($plugin) ? $plugin->getVar('pluginmulti') : 1;
        $name   = is_object($plugin) ? $plugin->getVar('pluginname') : $module->getVar('name');
        unset($plugin);

        echo "<tr>";
        echo "<td class = head>" . $module->getVar('name') . "</td>";
        echo "<td class = head>" . _AM_MYPOINTS_PLUGINITEMS . "</td>";
        echo "<td class = 'even'><input type='text' name='items_name[" . $module->getVar('mid') . "]' size=20 value='" . $name . "'></td>";
        echo "<td class = 'even'><select name=items_actif[" . $module->getVar('mid') . "]>";

        $sel = "";
        if ($actif == "1") {
            $sel = "SELECTED";
        }
        echo "<option " . $sel . " value=\"1\">" . _AM_MYPOINTS_ACTIVE . "\n</option>\n";
        $sel = "";
        if ($actif == "0") {
            $sel = "SELECTED";
        }
        echo "<option " . $sel . " value=\"0\">" . _AM_MYPOINTS_INACTIVE . "\n</option>\n";
        echo "</select></td>";
        echo "<td class = 'even'>" . _AM_MYPOINTS_MULTI . "</td>";
        echo "<td class = 'even'><input type='text' name='items_multi[" . $module->getVar('mid')."]' size=2 value='" . $multi . "'></td>";
        echo "</tr>";
    }

    foreach ($votes_plugins as $moduleid => $module) {
        $plugin = $plugin_handler->getByModuleType($moduleid, 'votes');
        $actif  = is_object($plugin) ? $plugin->getVar('pluginisactive') : 0;
        $multi  = is_object($plugin) ? $plugin->getVar('pluginmulti') : 1;
        $name   = is_object($plugin) ? $plugin->getVar('pluginname') : $module->getVar('name');
        unset($plugin);

        echo "<tr>";
        echo "<td class = head>" . $module->getVar('name') . "</td>";
        echo "<td class = head>" . _AM_MYPOINTS_PLUGINVOTES . "</td>";
        echo "<td class = 'even'><input type='text' name='votes_name[" . $module->getVar('mid') . "]' size=20 value='" . $name . "'></td>";
        echo "<td class = 'even'><select name=votes_actif[" . $module->getVar('mid') . "]>";
        $sel = "";
        if ($actif == "1") {
            $sel = "SELECTED";
        }
        echo "<option " . $sel . " value=\"1\">" . _AM_MYPOINTS_ACTIVE . "\n</option>\n";
        $sel = "";
        if ($actif == "0") {
            $sel = "SELECTED";
        }
        echo "<option " . $sel . " value=\"0\">" . _AM_MYPOINTS_INACTIVE . "\n</option>\n";
        echo "</select></td>";
        echo "<td class = 'even'>" . _AM_MYPOINTS_MULTI . "</td>";
        echo "<td class = 'even'><input type='text' name='votes_multi[" . $module->getVar('mid') . "]' size=2 value='" . $multi . "'></td>";
        echo "</tr>";
    }

    echo "</table><p>";
    echo "<input type='hidden' name='ok' VALUE='1'>";
    echo "<input type='submit' value='" . _AM_MYPOINTS_GO . "'>";
    echo "</form>";

    xoops_cp_footer();
}

function mypoints_update_plugins()
{
    global $xoopsUser, $xoopsDB;
    $plugin_handler = xoops_getmodulehandler('plugin');
    if (sizeof($_POST) > 0) {
        $plugin_handler->deleteAll();
        if (isset($_POST['items_actif'])) {
            foreach ($_POST['items_actif'] as $moduleid => $value) {
                $criteria = new CriteriaCompo(new Criteria('pluginmid', $moduleid));
                $criteria->add(new Criteria('plugintype', 'items'), 'AND');
                $criteria->setLimit(1);
                $plugins = $plugin_handler->getObjects($criteria);
                unset($criteria);
                $plugin = !empty($plugins) ? $plugins[0] : $plugin_handler->create();
                $plugin->setVar('plugintype', 'items');
                $plugin->setVar('pluginmid', $moduleid);
                $plugin->setVar('pluginisactive', $value);
                if (isset($_POST['items_multi'][$moduleid])) {
                    $plugin->setVar('pluginmulti', $_POST['items_multi'][$moduleid]);
                }
                if (isset($_POST['items_name'][$moduleid])) {
                    $plugin->setVar('pluginname', $_POST['items_name'][$moduleid]);
                }
                $plugin_handler->insert($plugin);
                unset($plugin);
            }
        }
        if (isset($_POST['votes_actif'])) {
            foreach($_POST['votes_actif'] as $moduleid => $value) {
                $criteria = new CriteriaCompo(new Criteria('pluginmid', $moduleid));
                $criteria->add(new Criteria('plugintype', 'votes'), 'AND');
                $criteria->setLimit(1);
                $plugins = $plugin_handler->getObjects($criteria);
                unset($criteria);
                $plugin = !empty($plugins) ? $plugins[0] : $plugin_handler->create();
                $plugin->setVar('plugintype', 'votes');
                $plugin->setVar('pluginmid', $moduleid);
                $plugin->setVar('pluginisactive', $value);
                if (isset($_POST['votes_multi'][$moduleid])) {
                    $plugin->setVar('pluginmulti', $_POST['votes_multi'][$moduleid]);
                }
                if (isset($_POST['votes_name'][$moduleid])) {
                    $plugin->setVar('pluginname', $_POST['votes_name'][$moduleid]);
                }
                $plugin_handler->insert($plugin);
                unset($plugin);
            }
        }
    }

    mypoints_updatePoints(1);
    redirect_header('main.php', 1, _AM_MYPOINTS_DONE);
    exit;
}

switch ($choice) {
    case "submit":
        mypoints_update_plugins();
        break;

    default:
        mypoints_index();
        break;
}
