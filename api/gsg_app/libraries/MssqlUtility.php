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
    public static function escape($data)
    {
        if (!isset($data) or empty($data)){
            return '';
        }
        if (is_numeric($data)){
            return $data;
        }
        if(is_array($data)){
            foreach($data as $k => $d){
                $data[$k] = static::escape($d);
            }
            return $data;
        }

        $non_displayables = [
            '/%0[0-8bcef]/',            // url encoded 00-08, 11, 12, 14, 15
            '/%1[0-9a-f]/',             // url encoded 16-31
            '/[\x00-\x08]/',            // 00-08
            '/\x0b/',                   // 11
            '/\x0c/',                   // 12
            '/[\x0e-\x1f]/'             // 14-31
        ];
        foreach ($non_displayables as $regex){
            $data = preg_replace( $regex, '', $data );
        }
        $data = str_replace("'", "''", $data );
        return $data;
    }

}