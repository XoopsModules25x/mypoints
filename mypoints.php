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
 * @version         $Id: mypoints.php 0 2009-11-14 18:47:04Z trabis $
 */

include_once dirname(dirname(dirname(__FILE__))) . '/mainfile.php';

$uid = 0;

if (is_object($xoopsUser) && $xoopsUser->getVar('uid') > 0) {
    $uid = $xoopsUser->getVar('uid');
    $thisUser =& $xoopsUser;
}

if (isset($_GET['uid'])){
    $getuid = intval($_GET['uid']);
    $getUser = new xoopsUser($getuid);
    if (is_object($getUser) && $getUser->isactive()) {
        $uid = $getuid;
        $thisUser =& $getUser;
    } else {
        $uid = 0;
    }
}

if ($uid == 0) {
    redirect_header(XOOPS_URL . '/modules/mypoints/index.php', 2, _NOPERM);
    exit();
}

include_once XOOPS_ROOT_PATH . '/modules/mypoints/include/functions.php';

$xoopsOption['template_main'] = 'mypoints_mypoints.html';
include_once XOOPS_ROOT_PATH . '/header.php';

$plugin_handler = xoops_getmodulehandler('plugin');
$relation_handler = xoops_getmodulehandler('relation');
$user_handler = xoops_getmodulehandler('user');

$refreshtime = $xoopsModuleConfig['refreshtime'];
$since = strtotime($xoopsModuleConfig['countsince']);
$countwebm = $xoopsModuleConfig['countadmin'];
$limit = $xoopsModuleConfig['memberstoshow'];

$xoopsTpl->assign('topmessage', sprintf(_MA_MYPOINTS_USERTOPMESSAGE, $thisUser->getVar('uname')));

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
$criteria->setSort('pluginmulti');
$criteria->setOrder('DESC');
$plugins = $plugin_handler->getObjects($criteria);
unset($criteria);

$user = $user_handler->get($uid);
if (is_object($user)) {
    $i = 0;
    foreach ($plugins as $plugin){
        $relation = $relation_handler->getByPluginUid($plugin->getVar('pluginid'), $uid);
        $points = is_object($relation) ? $relation->getVar('relationpoints') : 0;
        $myuser['plugins'][$i]['items']  = $points;
        $myuser['plugins'][$i]['points'] = $points * $plugin->getVar('pluginmulti');
        $myuser['plugins'][$i]['name']   = $plugin->getVar('pluginname');
        $myuser['plugins'][$i]['multi']  = $plugin->getVar('pluginmulti');
        $i++;
    }
    $myuser['points'] = $user->getVar('userpoints');
} else {
    $myuser['points'] = 0;
}

$xoopsTpl->assign('user', $myuser);

$message = '';
foreach ($plugins as $plugin) {
    $message .= $plugin->getVar('pluginname').' : ';
    $points   = $plugin->getVar('pluginmulti') == 1 ? _MA_MYPOINTS_LPOINT : _MA_MYPOINTS_LPOINTS;
    $message .= $plugin->getVar('pluginmulti') . ' ' . $points . '<br />';
}

$xoopsTpl->assign('howtoearnmessage', $message);
mypoints_updatePoints();

include_once XOOPS_ROOT_PATH . '/footer.php';
