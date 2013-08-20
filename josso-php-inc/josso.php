<?php
/**
 * PHP Josso lib.  Include this in all pages you want to use josso.
 *
 * @package  org.josso.agent.php
 *
 * @version $Id: josso.php 613 2008-08-26 16:42:10Z sgonzalez $
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
require_once('josso-cfg.inc');
require_once('/srv/www/htdocs/josso-php-inc/logging.php');

// Logging class initialization
$log = new Logging();

require_once('class.jossoagent.php');
require_once('class.jossouser.php');
require_once('class.jossorole.php');

$resource_isTested = FALSE;
$josso_isPartnerApp = FALSE;
$josso_agent;


// this is the after the host stuff "/foo/index.html"
$uri = $_SERVER['REQUEST_URI'];

$log->lwrite('Request_URI '.$uri );

if (isset($josso_protectedResources))
{
   foreach ($josso_protectedResources as $josso_protectedResource)
   {
      // Check if this is an ignored resource!
      if (strncmp($uri, $josso_protectedResource, strlen($josso_protectedResource)) == 0)
      {
         $josso_isPartnerApp = TRUE;
         $resource_isTested = TRUE;
			$log->lwrite('josso_isPartnerApp = TRUE');
         break;
      }
   }
}
else
{
  $log->lwrite('josso_protectedResources is NOT set ');
}

if (isset($josso_ignoredResources) and $josso_isPartnerApp == TRUE )
{
  foreach ($josso_ignoredResources as $josso_ignoredResource)
  {
    if (strncmp($uri, $josso_ignoredResource, strlen($josso_ignoredResource)) == 0)
    {
      $josso_isPartnerApp = FALSE;
		$log->lwrite('josso_isPartnerApp = FALSE');
    }
  }
}
else
{
  $log->lwrite('josso_ignoredResources is NOT set ');
}

// If this is a partner application,
if ($josso_isPartnerApp)
{
  $log->lwrite('Is a partner app');
  // Only available when URI is a partner application!

  $retvalue = session_start();

  $josso_agent = jossoagent::getNewInstance();

  //$log->lwrite('josso agent: '. print_r($josso_agent, TRUE). ' return of session_start '.$retvalue );

  $ssoSessionId = $josso_agent->accessSession();
  
  $log->lwrite('ssoSessionId: '. print_r($ssoSessionId, TRUE). '|' );

  // Automatic Login
  if (!isset($jossoSession))
  {
    // Avoid filtering josso resources like 'josso_security_check', 'josso_login', etc

    // If we have an original is because we're already authenticating something
    if (!isset($_SESSION['JOSSO_ORIGINAL_URL']) &&
      strncmp($uri, $josso_agent->getBaseCode().'/josso-security-check.php', strlen($josso_agent->getBaseCode().'/josso-security-check.php')) != 0 &&
      strncmp($uri, $josso_agent->getBaseCode().'/josso-login.php', strlen($josso_agent->getBaseCode().'/josso-login.php'))  != 0 &&
      strncmp($uri, $josso_agent->getBaseCode().'/josso-logout.php', strlen($josso_agent->getBaseCode().'/josso-logout.php')) != 0 )
    {

      // Try to perform an automatic login!
      // If we haven't tried an automatic login before, doit now.
      // Now, work with referer!
      if ($josso_agent->isAutomaticLoginRequired())
      {
		  $log->lwrite('jossoRequestOptionalLogin ');
        jossoRequestOptionalLogin();
      }
    }
  }
  else
  {
  	 $log->lwrite(' jossoSession is set  ');
  	 
    if (isset($_SESSION['JOSSO_AUTOMATIC_LOGIN_REFERER']))
    {
	   $log->lwrite('unset JOSSO_AUTOMATIC_LOGIN_REFERER ');
      unset($_SESSION['JOSSO_AUTOMATIC_LOGIN_REFERER']);
    }
  }
  $log->lwrite('end Is A PARTNER APP!!!!  ');
}
else
{
  $log->lwrite('end Is Not A PARTNER APP!!!! ');
} // END IF : JOSSO IS PARTNER APP


// ---------------------------------------------------------------------------------------------
// Functions that can be used by PHP applications...
// ---------------------------------------------------------------------------------------------
/**
 * Use this function when ever you want to start user authentication.
 */
function jossoRequestLogin()
{
  $currentUrl = $_SERVER['REQUEST_URI'];
  jossoRequestLoginForUrl($currentUrl, FALSE);
}


function jossoRequestOptionalLogin()
{
  $currentUrl = $_SERVER['REQUEST_URI'];
  jossoRequestLoginForUrl($currentUrl, TRUE);
}


/**
 * Use this function when ever you want to logout the current user.
 */
function jossoRequestLogout()
{
  $currentUrl = $_SERVER['REQUEST_URI'];
  jossoRequestLogoutForUrl($currentUrl);
}


/**
 * Creates a login url for the current page, use to create links to JOSSO login page
 */
function jossoCreateAuthenticationUrl()
{
  // Get JOSSO Agent instance
  $josso_agent = & jossoagent::getNewInstance();
  $loginUrl = $josso_agent->getBaseCode().'/josso-authenticate.php';
  return $loginUrl;
}


/**
 * Creates a login url for the current page, use to create links to JOSSO login page
 */
function jossoCreateLoginUrl()
{
  $josso_agent = & jossoagent::getNewInstance();
  
  $currentUrl = $_SERVER['REQUEST_URI'];
  //$loginUrl = $josso_agent->getBaseCode().'/josso-login.php?josso_current_url='.'https://newdata.crinet.com'.$currentUrl;
  $loginUrl = $josso_agent->getBaseCode().'/josso-login.php?josso_current_url='.$currentUrl;

  return $loginUrl;
}

/**
 * Creates a logout url, use to create links to JOSSO logout page.
 * Use null for backToUrl parameter if you want to go back to the current page after logout.
 * For logout url on protected page pass a backToUrl that points to some public page (e.g. home page)
 * in order to avoid immediate redirection to josso login page.
 */
function jossoCreateLogoutUrl($backToUrl)
{
  $josso_agent = & jossoagent::getNewInstance();

  if (is_null($backToUrl))
  {
    $backToUrl = createBaseUrl() . $_SERVER['REQUEST_URI'];
  }

  $logoutUrl = $josso_agent->getBaseCode().'/josso-logout.php?josso_current_url='.$backToUrl;
  
  return $logoutUrl;
}

function jossoRequestLoginForUrl($currentUrl, $optional=FALSE )
{

  $_SESSION['JOSSO_ORIGINAL_URL'] = $currentUrl;

  $josso_agent = jossoagent::getNewInstance();
  
  $securityCheckUrl = createBaseUrl().$josso_agent->getBaseCode().'/josso-security-check.php';

  $loginUrl = $josso_agent->getGatewayLoginUrl(). '?josso_back_to=' . $securityCheckUrl;

  forceRedirect($loginUrl);
}

function jossoSecurityCheckUrl()
{
  $josso_agent = & jossoagent::getNewInstance();
  $securityCheckUrl = createBaseUrl().$josso_agent->getBaseCode().'/josso-security-check.php';
  return $securityCheckUrl;
}

function jossoRequestLogoutForUrl($currentUrl)
{
  $josso_agent = & jossoagent::getNewInstance();
  $logoutUrl = $josso_agent->getGatewayLogoutUrl() . '?josso_back_to=' . $currentUrl;

  $logoutUrl = $logoutUrl . createFrontChannelParams();

  // Clear SSO Cookie
  setcookie("JOSSO_SESSIONID", '', 0, "/"); // session cookie ...
  $_COOKIE['JOSSO_SESSIONID'] = '';

  forceRedirect($logoutUrl);
}

function forceRedirect($url,$die=true)
{
  if (!headers_sent())
  {
    ob_end_clean();
    header("Location: " . $url);
    prepareNonCacheResponse();
  }

  printf('<HTML>');
  printf('<META http-equiv="Refresh" content="0;url=%s">', $url);
  printf('<BODY onload="try {self.location.href="%s" } catch(e) {}"><a href="%s">Redirect </a></BODY>', $url, $url);
  printf('</HTML>');

  if ($die)
  {
    die();
  }
}

function createBaseUrl() {
  // ReBuild securityCheck URL
  $log = new Logging();

  $protocol = 'http';
  $host = $_SERVER['HTTP_HOST'];
  $baseURL = '';

  if (isset($_SERVER['HTTPS']))
  {
    $protocol = 'https';
    if ($_SERVER['SERVER_PORT'] != 443)
    {
      $port = $_SERVER['SERVER_PORT'];
    }
  }
  else
  {
    // This is a NON secure connection, the default PORT is 80
    $protocol = 'http';

    if ($_SERVER['SERVER_PORT'] != 80)
    {
      $port = $_SERVER['SERVER_PORT'];
    }
  }

  $baseURL = $protocol.'://'.$host.(isset($port) ? ':'.$port :'');
  
  $log->lwrite('createBaseUrl '.$baseURL );

  return $baseURL;
}

function prepareNonCacheResponse()
{
  header("Cache-Control", "no-cache");
  header("Pragma", "no-cache");
  header("Expires", "0");
}
?>