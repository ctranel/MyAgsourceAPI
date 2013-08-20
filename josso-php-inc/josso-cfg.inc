<?php
/**
 * PHP Josso Agent configuration file.
 *
 * @package  org.josso.agent.php
 *
 * @version $Id: josso-cfg.inc 613 2008-08-26 16:42:10Z sgonzalez $
 *
 */
/**
 Copyright (c) 2004, Novascope S.A. and the JOSSO team
 All rights reserved.
 Redistribution and use in source and binary forms, with or
 without modification, are permitted provided that the following
 conditions are met:

 * Redistributions of source code must retain the above copyright
   notice, this list of conditions and the following disclaimer.

 * Redistributions in binary form must reproduce the above copyright
   notice, this list of conditions and the following disclaimer in
   the documentation and/or other materials provided with the
   distribution.

 * Neither the name of the JOSSO team nor the names of its
   contributors may be used to endorse or promote products derived
   from this software without specific prior written permission.

 THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND
 CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
 MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS
 BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED
 TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 POSSIBILITY OF SUCH DAMAGE.
*/
require_once('/srv/www/htdocs/config.php');

// Josso agent configuration
$josso_gatewayLoginUrl = JOSSO_SERVER. 'josso/signon/login.do';
$josso_gatewayLogoutUrl = JOSSO_SERVER. 'josso/signon/logout.do';

// $josso_gatewayMenuUrl = JOSSO_SERVER. '/WASM-Link/home.seam';
// $josso_gatewayChangePasswordUrl = JOSSO_SERVER. '/WASM-Link/ChangePassword.seam';

// WS client configuration :
$josso_endpoint = JOSSO_SERVER;

// This could be also /, it points to the path where JOSSO code is found, for example the 
// josso-security-check.php page.  relative to the vhost URL
$josso_agentBasecode = "/extranet/josso-php-partnerapp";

// Min. session access interval for each user, in seconds.
$josso_sessionAccessMinInterval=1;

// All requests that start with the following uri strings 
//  (that follow the vhost name)  will be protected!
$josso_protectedResources = array( '/extranet' );

// All requests that start with the following uri strings 
// (that follow the vhost name) will be ignored, 
// would have to be an ignored resource relative to the above protected as in '/extranet/images'
// Cant have /extranet/josso-php-partnerapp in the ignore.. else you can't finish the authenticating
$josso_ignoredResources =
	array('/extranet/css', '/extranet/images');

// Default resource to send the user after login/logout if no saved request is found
// the vhost of where the request is created prepends the host name. stubs action=josso stubs
//$josso_defaultResource = 'https://newdata.crinet.com/extranet/index.php?action=login';
$josso_defaultResource = '/extranet/index.php?action=login';
?>