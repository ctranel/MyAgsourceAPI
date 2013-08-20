<?php
/**
 * PHP Josso lib.  Include this in all pages you want to use josso.
 *
 * @package  org.josso.agent.php
 *
 * @version $Id: josso.php 340 2006-02-09 17:02:13Z sgonzalez $
 * @author Sebastian Gonzalez Oyuela <sgonzalez@josso.org>
 */

/**
JOSSO: Java Open Single Sign-On

Copyright 2004-2008, Atricore, Inc.

This is free software; you can redistribute it and/or modify it
under the terms of the GNU Lesser General Public License as
published by the Free Software Foundation; either version 2.1 of
the License, or (at your option) any later version.

This software is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this software; if not, write to the Free
Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA
02110-1301 USA, or see the FSF site: http://www.fsf.org.
*/
require_once('../../config.php');
include_once(JOSSO_INCLUDE);

$currentUrl = $_REQUEST['josso_current_url'];

//echo ("File: ".__FILE__ ." line " .__LINE__ ."</br> ");
//var_dump($_SERVER);
//echo ("</br> ");
//var_dump($_REQUEST);
//echo (" ".$currentUrl."</br>");

jossoRequestLoginForUrl($currentUrl, FALSE);
?>
