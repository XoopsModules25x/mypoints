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
 * @version         $Id: publisher.php 0 2009-11-14 18:47:04Z trabis $
 */

defined('XOOPS_ROOT_PATH') or die("XOOPS root path not defined");

function publisher_useritems_count($uid, $since)
{
    global $xoopsDB;
    list($ret) = $xoopsDB->fetchRow($xoopsDB->query("SELECT COUNT(*) FROM " . $xoopsDB->prefix("publisher_items") . " WHERE uid='{$uid}' AND datesub > '$since' AND status = 2"));
    return $ret;
}
?>