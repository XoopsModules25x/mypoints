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
 * @version         $Id: relation.php 0 2009-11-14 18:47:04Z trabis $
 */

defined('XOOPS_ROOT_PATH') or die("XOOPS root path not defined");

class MypointsRelation extends XoopsObject
{
    /**
     * constructor
     */
    function __construct()
    {
        $this->initVar("relationid", XOBJ_DTYPE_INT);
        $this->initVar("relationuid", XOBJ_DTYPE_INT);
        $this->initVar("relationpid", XOBJ_DTYPE_INT);
        $this->initVar("relationpoints", XOBJ_DTYPE_INT);
    }
}

class MypointsRelationHandler extends XoopsPersistableObjectHandler
{
    /**
     * constructor
     */
    function __construct(&$db)
    {
        parent::__construct($db, "mypoints_relation", 'MypointsRelation', "relationid", "relationpid");
    }

    function getByPluginUid($pid , $uid)
    {
        $relation = false;
        $pid = intval($pid);
        $uid = intval($uid);
        $sql = 'SELECT * FROM '.$this->db->prefix('mypoints_relation')
        . ' WHERE relationpid=' . $pid
        . ' AND relationuid=' . $uid;
        if (!$result = $this->db->query($sql)) {
            return $relation;
        }
        $numrows = $this->db->getRowsNum($result);
        if ($numrows == 1) {
            $relation = new MypointsRelation();
            $relation->assignVars($this->db->fetchArray($result));
        }

        return $relation;
    }
}
