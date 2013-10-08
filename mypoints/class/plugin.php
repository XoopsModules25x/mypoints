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
 * @version         $Id: plugin.php 0 2009-11-14 18:47:04Z trabis $
 */

defined('XOOPS_ROOT_PATH') or die("XOOPS root path not defined");

class MypointsPlugin extends XoopsObject
{
    /**
     * constructor
     */
    function __construct()
    {
        $this->initVar("pluginid", XOBJ_DTYPE_INT);
        $this->initVar("pluginmid", XOBJ_DTYPE_INT);
        $this->initVar('pluginname', XOBJ_DTYPE_TXTBOX, '');
        $this->initVar('plugintype', XOBJ_DTYPE_TXTBOX, 'items');
        $this->initVar("pluginmulti", XOBJ_DTYPE_INT,1);
        $this->initVar("pluginisactive", XOBJ_DTYPE_INT,1);
    }
}

class MypointspluginHandler extends XoopsPersistableObjectHandler
{
    /**
     * constructor
     */
    function __construct(&$db)
    {
        parent::__construct($db, "mypoints_plugin", 'MypointsPlugin', "pluginid", "pluginmid");
    }

    function getByModuleType($mid, $type)
    {
        $plugin = false;
        $mid = intval($mid);
        if ($mid > 0) {
            $sql = 'SELECT * FROM '.$this->db->prefix('mypoints_plugin')
            . ' WHERE pluginmid=' . $mid
            .' AND plugintype=' . $this->db->quoteString($type);
            if (!$result = $this->db->query($sql)) {
                return $plugin;
            }
            $numrows = $this->db->getRowsNum($result);
            if ($numrows == 1) {
                $plugin = new Mypointsplugin();
                $plugin->assignVars($this->db->fetchArray($result));
            }
        }
        return $plugin;
    }
}
?>