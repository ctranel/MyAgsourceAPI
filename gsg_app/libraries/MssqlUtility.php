<?php
/**
 * Created by PhpStorm.
 * User: ctranel
 * Date: 2/15/2016
 * Time: 3:35 PM
 */

namespace myagsource;


class MssqlUtility
{
    /*
	 * escape
	 * @param string value
	 * @return string escaped value
	 * @author ctranel
     */
    public static function escape($value)
    {
        if(is_numeric($value))
            return $value;
        $unpacked = unpack('H*hex', $value);
        return '0x' . $unpacked['hex'];
    }

}