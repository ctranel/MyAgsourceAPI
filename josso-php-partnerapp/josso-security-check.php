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

require_once('/srv/www/htdocs/josso-php-inc/logging.php');

require_once(JOSSO_INCLUDE);

// echo("<br> Request <br>");
// var_dump($_REQUEST);
// echo("<br> Session <br>");
// var_dump($_SESSION);
// echo("<br> josso_agent <br>");
// var_dump($josso_agent);
// echo("<br>");
//
//  exit();

if( !isset($log) )
{
	$log = new Logging();
}

if ( is_null($josso_agent) )
{
	$log->lwrite(' josso_agent is null ' );
}

// Resolve the assertion :
if (isset($_REQUEST['josso_assertion_id']))
{
  $assertionId = $_REQUEST['josso_assertion_id'];

  $log->lwrite(' assertionId '.$assertionId );

  $ssoSessionId = $josso_agent->resolveAuthenticationAssertion($assertionId);

  $log->lwrite(' ssoSessionId '.$ssoSessionId );
  $log->lwrite(' josso agent Error '.$josso_agent->getError() );
  $log->lwrite(' josso agent Fault '.$josso_agent->getFault() );

  setcookie("JOSSO_SESSIONID", $ssoSessionId, 0, "/"); // session cookie ...
  $_COOKIE['JOSSO_SESSIONID'] = $ssoSessionId;
}

if (isset($_SESSION['JOSSO_ORIGINAL_URL']))
{
  $backToUrl = $_SESSION['JOSSO_ORIGINAL_URL'];
  unset($_SESSION['JOSSO_ORIGINAL_URL']);
  $log->lwrite(' backToUrl from session '.$backToUrl );
}
else if (isset($josso_defaultResource))
{
  //echo("Setting back to url ".$backToUrl."</br>" );
  $backToUrl = $josso_defaultResource;
  $log->lwrite(' backToUrl default '.$backToUrl );
}

if (isset($backToUrl))
{
//  echo("back to url ".$backToUrl."</br>" );
//  var_dump($_SESSION); echo("</br>")
//  var_dump($_REQUEST); echo("</br>") exit;
  $log->lwrite(' forceRedirect to backToUrl ' );
  forceRedirect($backToUrl, true);
}
// No page is stored or no session was found, just display an error one ...
?>
<!doctype html public "-//w3c//dtd html 4.0 transitional//en">
<html>
<head>
<title>JOSSO - PHP Problem</title>
<meta
  name="description"
  content="Java Open Single Signon">
</head>

<body>
<h1>JOSSO Encountered a Problem!</h1>
<h2>Either you accessed this page directly or no PHP Session support is available!</h2>
</body>
</html>
