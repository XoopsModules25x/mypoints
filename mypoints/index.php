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
 * @package         Mypoints
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @version         $Id: index.php 0 2009-11-14 18:47:04Z trabis $
 */

include_once dirname(dirname(dirname(__FILE__))) . '/mainfile.php';

include_once XOOPS_ROOT_PATH . '/modules/mypoints/include/functions.php';

$xoopsOption['template_main'] = 'mypoints_showall.html';
include_once XOOPS_ROOT_PATH . '/header.php';

$details = isset($_GET['det']) ? intval($_GET['det']) : 0;

$plugin_handler = xoops_getmodulehandler('plugin');
$relation_handler = xoops_getmodulehandler('relation');
$user_handler = xoops_getmodulehandler('user');

$refreshtime = $xoopsModuleConfig['refreshtime'];
$since = strtotime($xoopsModuleConfig['countsince']);
$countwebm = $xoopsModuleConfig['countadmin'];
$limit = $xoopsModuleConfig['memberstoshow'];

$xoopsTpl->assign('topmessage', sprintf(_MA_MYPOINTS_TOPMESSAGE, $limit, $xoopsConfig['sitename']));

if ($refreshtime < 60) {
    $refreshtimes = $refreshtime ;
    $message = $refreshtimes == 1 ? _MA_MYPOINTS_LSECOND : _MA_MYPOINTS_LSECONDS;
} else if ($refreshtime < 3600) {
    $refreshtimes = intval($refreshtime / 60);
    $message = $refreshtimes == 1 ? _MA_MYPOINTS_LMINUTE : _MA_MYPOINTS_LMINUTES;
} else if ($refreshtime < 86400) {
    $refreshtimes = intval($refreshtime / 3600);
    $message = $refreshtimes == 1 ? _MA_MYPOINTS_LHOUR : _MA_MYPOINTS_LHOURS;
} else {
    $refreshtimes = intval($refreshtime / 86400);
    $message = $refreshtimes == 1 ? _MA_MYPOINTS_LDAY : _MA_MYPOINTS_LDAYS;
}

$xoopsTpl->assign('updatemessage', sprintf(_MA_MYPOINTS_UPDATEMESSAGE, $refreshtimes, $message));
$xoopsTpl->assign('sincemessage', sprintf(_MA_MYPOINTS_SINCEMESSAGE, formatTimeStamp($since, "m", $xoopsConfig['server_TZ'])));


$criteria = new CriteriaCompo(new Criteria('pluginisactive', 1));
//$criteria->add(new Criteria('plugintype', 'items'), 'AND');
$criteria->setSort('pluginmulti');
$criteria->setOrder('DESC');
$plugins = $plugin_handler->getObjects($criteria);
unset($criteria);

if ($details == 1) {
    foreach ($plugins as $plugin) {
        $myplugins[]['pluginname'] = $plugin->getVar('pluginname');
    }
    $xoopsTpl->assign('plugins', $myplugins);
}

$criteria = new CriteriaCompo();
$criteria->setSort('userpoints');
$criteria->setOrder('DESC');
$criteria->setLimit($limit);
//$criteria->setStart($start);
$users = $user_handler->getObjects($criteria);
$myusers = array();

$i = 1;
foreach ($users as $user) {
    if ($user->getVar('userpoints') > 0) {
        $myusers[$i]['rank'] = $i;
        $myusers[$i]['link'] = "<a href='" . XOOPS_URL . "/userinfo.php?uid=" . $user->getVar('useruid') . "'>" . $user->getVar('useruname') . "</a>";
        if ($details == 1) {
            foreach ($plugins as $plugin){
                $relation = $relation_handler->getByPluginUid($plugin->getVar('pluginid'), $user->getVar('useruid'));
                $points = is_object($relation) ? $relation->getVar('relationpoints') : 0;
                $myusers[$i]['pluginpoints'][] = $points;
            }
        }
        $myusers[$i]['points'] = $user->getVar('userpoints');
        $i++;
    }
}

$xoopsTpl->assign('users', $myusers);

$detailslink = "<a href='index.php?det=";
if ($details == 1) {
    $detailslink .= "0' title='" . _MA_MYPOINTS_MOREOFF . "'>" . _MA_MYPOINTS_MOREOFF .  "</a>";
} else {
    $detailslink .= "1' title='" . _MA_MYPOINTS_MOREON . "'>" . _MA_MYPOINTS_MOREON . "</a>";
}
$xoopsTpl->assign('detailslink', $detailslink);

$message = '';
foreach ($plugins as $plugin) {
    $message .= $plugin->getVar('pluginname').' : ';
    $points   = $plugin->getVar('pluginmulti') == 1 ? _MA_MYPOINTS_LPOINT : _MA_MYPOINTS_LPOINTS;
    $message .= $plugin->getVar('pluginmulti'). ' ' . $points . '<br />';
}

$xoopsTpl->assign('howtoearnmessage', $message);
mypoints_updatePoints();

include_once XOOPS_ROOT_PATH . '/footer.php';
?>