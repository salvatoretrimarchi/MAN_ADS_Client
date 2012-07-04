<?php
/**
 * Copyright (C) 2008-2012 Ulteo SAS
 * http://www.ulteo.com
 * Author Julien LANGLOIS <julien@ulteo.com> 2008-2012
 * Author Laurent CLOUET <laurent@ulteo.com> 2008-2011
 * Author Jeremy DESVAGES <jeremy@ulteo.com> 2008-2011
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2
 * of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 **/
require_once(dirname(__FILE__).'/../../includes/core-minimal.inc.php');
require_once(dirname(__FILE__).'/functions.inc.php');
require_once(dirname(__FILE__).'/misc.inc.php');

$buf = 'en_GB';
$prefs = Preferences::getInstance();
if (is_object($prefs))
	$buf = $prefs->get('general', 'admin_language');
if ($buf == 'auto') {
	if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
		$buf = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
		$buf = explode(';', $buf[0]);
		$buf = $buf[0];
	} else
		$buf = 'en_GB';
}

Preferences::removeInstance();

$language = locale2unix($buf);
setlocale(LC_ALL, $language.'.UTF-8');
putenv('LANGUAGE='.$language);
$domain = 'uovdsmadmin';
bindtextdomain($domain, LOCALE_DIR);
textdomain($domain);

ErrorManager::setInstance(new AdminErrorManager());

require_once(dirname(__FILE__).'/template_server.inc.php');
