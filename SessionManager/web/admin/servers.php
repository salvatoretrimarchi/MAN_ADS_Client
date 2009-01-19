<?php
/**
 * Copyright (C) 2008 Ulteo SAS
 * http://www.ulteo.com
 * Author Laurent CLOUET <laurent@ulteo.com>
 * Author Jeremy DESVAGES <jeremy@ulteo.com>
 * Author Julien LANGLOIS <julien@ulteo.com>
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
require_once(dirname(__FILE__).'/includes/core.inc.php');

if (isset($_GET['mass_action']) && $_GET['mass_action'] == 'register') {
	if (isset($_GET['register_servers']) && is_array($_GET['register_servers'])) {
		foreach ($_GET['register_servers'] as $server) {
			$server = new Server_admin($server);
			$server->register(0);
			$server->updateApplications();
		}
	}

	redirect('servers.php?action=list');
}

if (isset($_GET['mass_action']) && $_GET['mass_action'] == 'maintenance') {
	if (isset($_GET['manage_servers']) && is_array($_GET['manage_servers'])) {
		foreach ($_GET['manage_servers'] as $server) {
			$server = new Server_admin($server);
			if ($server->isOnline()) {
				if (isset($_GET['to_maintenance']))
					$server->setAttribute('locked', '1');
				else
					$server->setAttribute('locked', NULL);
			}
		}
	}

	redirect('servers.php?action=list');
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'install_line' && isset($_REQUEST['fqdn']) && isset($_REQUEST['line'])) {
	$t = new Task_install_from_line(0, $_REQUEST['fqdn'], $_REQUEST['line']);

	$tm = new Tasks_Manager();
	$tm->add($t);

	header('Location: servers.php?action=manage&fqdn='.$_REQUEST['fqdn']);
	die();
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'replication' && isset($_REQUEST['fqdn']) && isset($_REQUEST['servers'])) {
	$server_from = new Server($_REQUEST['fqdn']);
	$applications_from = $server_from->getApplications();

	$servers_fqdn = $_REQUEST['servers'];
	foreach($servers_fqdn as $server_fqdn) {
		$server_to = new Server($server_fqdn);
		$applications_to = $server_to->getApplications();

		$to_delete = array();
		foreach($applications_to as $app) {
			if (! in_array($app, $applications_from))
				$to_delete[]= $app;
		}

		$to_install = array();
		foreach($applications_from as $app) {
			if (! in_array($app, $applications_to))
				$to_install[]= $app;
		}
		/*
		echo 'replicate '.$_REQUEST['fqdn'].' on '.$server_fqdn.'<br>';
		echo 'to_install: ';
		var_dump($to_install);
		echo '<br>to remove: ';
		var_dump($to_delete);
		echo '<hr/>';
		die();
		*/
		$tm = new Tasks_Manager();
		if (count($to_delete) > 0) {
			$t = new Task_remove(0, $server_fqdn, $to_delete);
			$tm->add($t);
		}
		if (count($to_install) > 0) {
			$t = new Task_install(0, $server_fqdn, $to_install);
			$tm->add($t);
		}
	}
	redirect($_SERVER['HTTP_REFERER']);
}



if (isset($_GET['action']) && $_GET['action'] == 'register' && isset($_GET['fqdn'])) {
	$server = new Server_admin($_GET['fqdn']);
	$server->register(0);
	$server->updateApplications();

	redirect('servers.php?action=list');
}

if (isset($_GET['action']) && $_GET['action'] == 'maintenance' && isset($_GET['fqdn'])) {
	$server = new Server_admin($_GET['fqdn']);
	if ($server->isOnline()) {
		if (isset($_GET['maintenance']) && $_GET['maintenance'] == 1)
			$server->setAttribute('locked', '1');
		else
			$server->setAttribute('locked', NULL);
	}

	redirect($_SERVER['HTTP_REFERER']);
}

if (isset($_GET['action']) && $_GET['action'] == 'available_sessions' && isset($_GET['fqdn'])) {
	if (isset($_GET['nb_sessions'])) {
		$server = new Server_admin($_GET['fqdn']);
		$server->setAttribute('nb_sessions', $_GET['nb_sessions']);
	}

	redirect($_SERVER['HTTP_REFERER']);
}

if (isset($_GET['action']) && $_GET['action'] == 'external_name' && isset($_GET['fqdn'])) {
	if (isset($_GET['external_name'])) {
		$server = new Server_admin($_GET['fqdn']);
		$server->setAttribute('external_name', $_GET['external_name']);
	}

	redirect($_SERVER['HTTP_REFERER']);
}

if (isset($_GET['action']) && $_GET['action'] == 'web_port' && isset($_GET['fqdn'])) {
	if (isset($_GET['web_port'])) {
		$server = new Server_admin($_GET['fqdn']);
		$server->setAttribute('web_port', $_GET['web_port']);
	}

	redirect($_SERVER['HTTP_REFERER']);
}

// Seems to be useless, to remove in few time
if (isset($_GET['action']) && $_GET['action'] == 'change' && isset($_GET['fqdn'])) {
	$server = new Server_admin($_GET['fqdn']);
	$server->setAttribute('nb_sessions', $_GET['nb_sessions']);
	if (isset($_GET['maintenance']) && $_GET['maintenance'] == 1)
		$server->setAttribute('locked', '1');
	else
		$server->setAttribute('locked', NULL);

	redirect('servers.php?action=manage&fqdn='.$_GET['fqdn']);
}

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['fqdn'])) {
	$server = new Server_admin($_GET['fqdn']);
	$server->delete();

	redirect('servers.php?action=list');
}

if (isset($_GET['action']) && $_GET['action'] == 'manage' && isset($_GET['fqdn'])) {
  show_manage($_GET['fqdn']);
}

show_default();


function show_default() {
  $servers = new Servers();
  $u_servs = $servers->getUnregistered();
  if (! is_array($u_servs))
    $u_servs = array();

  $a_servs = $servers->getAll();
  if (! is_array($a_servs))
    $a_servs = array();


  include_once('header.php');

  echo '<div id="servers_div">';
  echo '<h1>'._('Servers').'</h1>';

  if (count($u_servs) > 0){
    echo '<div id="servers_list_div">';
    echo '<h2>'._('Unregistered servers').'</h2>';

    if (count($u_servs) > 1) {
      echo '<form action="servers.php" method="get">';
      echo '<input type="hidden" name="mass_action" value="register" />';
    }

    echo '<table id="unregistered_servers_table" class="main_sub sortable" border="0" cellspacing="1" cellpadding="3">';
    echo '<thead>';
    echo '<tr class="title">';
    if (count($u_servs) > 1)
      echo '<th class="unsortable"></th>';
    echo '<th>'._('FQDN').'</th><th>'._('Type').'</th>';
    // echo '<th>'._('Version').'</th>';
    echo '<th>'._('Details').'</th>';
    echo '</tr>';
    echo '</thead>';

    $count = 0;
    foreach($u_servs as $s) {
      $content = 'content'.(($count++%2==0)?1:2);
      $s->getMonitoring();

      echo '<tr class="'.$content.'">';
            if (count($u_servs) > 1)
	echo '<td><input type="checkbox" name="register_servers[]" value="'.$s->fqdn.'" /><form></form>';

      echo '<td>'.$s->fqdn.'</td>';
      echo '<td style="text-align: center;"><img src="media/image/server-'.$s->stringType().'.png" alt="'.$s->stringType().'" title="'.$s->stringType().'" /><br />'.$s->stringType().'</td>';
      //echo '<td>'.$s->stringVersion().'</td>';
      echo '<td>';
      echo _('CPU').': '.$s->getAttribute('cpu_model').' ('.$s->getAttribute('cpu_nb').' ';
      echo ($s->getAttribute('cpu_nb') > 1)?_('cores'):_('core');
      echo ')<br />';
      echo _('RAM').': '.round($s->getAttribute('ram')/1024).' MB';
      echo '</td>';

      echo '<td>';
      echo '<form  method="get">';
      echo '<input type="submit" value="'._('Register').'" />';
      echo '<input type="hidden" name="action" value="register" />';
      echo '<input type="hidden" name="fqdn" value="'.$s->fqdn.'" />';
      echo '</form>';
      echo '</td>';

      echo '</tr>';
    }

    // Mass actions
    if (count($u_servs) > 1) {
      $content = 'content'.(($count++%2==0)?1:2);
      echo '<tfoot>';
      echo '<tr class="'.$content.'">';
      echo '<td colspan="4">';
      echo '<a href="javascript:;" onclick="markAllRows(\'unregistered_servers_table\'); return false">'._('Mark all').'</a>';
      echo '/ <a href="javascript:;" onclick="unMarkAllRows(\'unregistered_servers_table\'); return false">'._('Unmark all').'</a>';
      echo '</td>';
      echo '<td>';
      echo '<input type="submit" name="register" value="'._('Register').'"/><br />';
      echo '</form>';
      echo '</td>';
      echo '</tr>';
      echo '</tfoot>';
    }

    echo '</table>';
    if (count($u_servs) > 1) {
      echo '</form>';
    }
    echo '</div>';
    echo '<br />';
  }


  if (count($a_servs) > 0) {
    echo '<div id="servers_list_div">';
    if (count($a_servs) > 1) {
      echo '<form action="servers.php" method="get">';
      echo '<input type="hidden" name="mass_action" value="maintenance" />';
    }
    echo '<table id="available_servers_table" class="main_sub sortable" border="0" cellspacing="1" cellpadding="3">';
    echo '<thead>';
    echo '<tr class="title">';
    if (count($a_servs) > 1)
      echo '<th class="unsortable"></th>';
    echo '<th>'._('FQDN').'</th><th>'._('Type').'</th>';
    // echo '<th>'._('Version').'</th>';
    echo '<th>'._('Status').'</th><th>'._('Details').'</th><th>'._('Monitoring').'</th>';
    // echo '<th>'._('Applications(physical)'.</th>';
    echo '</tr>';
    echo '</thead>';

    $count = 0;
    foreach($a_servs as $s) {
      $content = 'content'.(($count++%2==0)?1:2);
      $server_online = $s->isOnline();

      if ($server_online) {
	if ($s->hasAttribute('locked')) {
	  $switch_msg = _('Switch to production');
	  $swtich_value = 0;
	}
	else {
	  $switch_msg = _('Switch to maintenance');
	  $swtich_value = 1;
	}
      }


      echo '<tr class="'.$content.'">';
      if (count($a_servs) > 1)
        echo '<td><input type="checkbox" name="manage_servers[]" value="'.$s->fqdn.'" /></td><form></form>';
      echo '<td>';
      echo '<a href="servers.php?action=manage&fqdn='.$s->fqdn.'">'.$s->fqdn.'</a>';
      echo '</td>';
      echo '<td style="text-align: center;"><img src="media/image/server-'.$s->stringType().'.png" alt="'.$s->stringType().'" title="'.$s->stringType().'" /><br />'.$s->stringType().'</td>';
      // echo '<td>'.$s->stringVersion().'</td>';
      echo '<td>'.$s->stringStatus().'</td>';
      echo '<td>';
      echo _('CPU').': '.$s->getAttribute('cpu_model').' ('.$s->getAttribute('cpu_nb').' ';
      echo ($s->getAttribute('cpu_nb') > 1)?_('cores'):_('core');
      echo ')<br />';
      echo _('RAM').': '.round($s->getAttribute('ram')/1024).' '._('MB');
      echo '</td>';
      echo '<td>';
      if ($server_online) {
        $buf = round(($s->getNbUsedSessions()/$s->getNbAvailableSessions())*100);
        echo _('CPU usage').': '.$s->getCpuUsage().'<br />';
        echo display_loadbar($s->getCpuUsage());
        echo _('RAM usage').': '.$s->getRamUsage().'<br />';
        echo display_loadbar($s->getRamUsage());
        echo _('Sessions usage').': '.$buf.'%<br />';
        echo display_loadbar($buf);
      }
      echo '</td>';

      echo '<td>';
      echo '<form action="servers.php" method="get">';
      echo '<input type="submit" value="'._('Manage').'"/>';
      echo '<input type="hidden" name="action" value="manage" />';
      echo '<input type="hidden" name="fqdn" value="'.$s->fqdn.'" />';
      echo '</form>';
      echo '</td>';

      echo '<td>';
      if ($server_online) {
	echo '<form action="servers.php" method="get">';
	echo '<input type="submit" value="'.$switch_msg.'"/>';
	echo '<input type="hidden" name="action" value="maintenance" />';
	echo '<input type="hidden" name="maintenance" value="'.$swtich_value.'" />';
	echo '<input type="hidden" name="fqdn" value="'.$s->fqdn.'" />';
	echo '</form>';
      }
      echo '</td>';
      echo '</tr>';
    }

    if (count($a_servs) > 1) {
      $content = 'content'.(($count++%2==0)?1:2);
      echo '<tfoot>';
      echo '<tr class="'.$content.'">';
      echo '<td colspan="7">';
      echo '<a href="javascript:;" onclick="markAllRows(\'available_servers_table\'); return false">'._('Mark all').'</a>';
      echo '/ <a href="javascript:;" onclick="unMarkAllRows(\'available_servers_table\'); return false">'._('Unmark all').'</a>';
      echo '</td>';
      echo '<td>';
      echo '<input type="submit" name="to_production" value="'._('Switch to production').'"/><br />';
      echo '<input type="submit" name="to_maintenance" value="'._('Switch to maintenance').'"/>';
      echo '</form>';
      echo '</td>';
      echo '</tr>';
      echo '</tfoot>';
    }

    echo '</table>';
    echo '</form>';
  }

  echo '</div>';
  include_once('footer.php');
  die();
}


function show_manage($fqdn) {
  $server = new Server_admin($fqdn);

  $server_online = $server->isOnline();

  if ($server_online) {
    $server->getMonitoring();
  }

  $buf_status = $server->getAttribute('status');
  if ($buf_status == 'down')
    $status_error_msg = _('Warning: server is offline');
  elseif ($buf_status == 'broken')
    $status_error_msg = _('Warning: server is broken');

  $server_lock = $server->hasAttribute('locked');

  if ($server_online) {
    $buf = $server->updateApplications();
    if ($buf === false)
      $_SESSION['errormsg'] = _('Cannot list available applications');
  }

  $tm = new Tasks_Manager();
  $tm->load_from_server($server->fqdn);
  $tm->refresh_all();

  $apps_in_remove = array();
  $apps_in_install = array();

  $tasks = array();
  if ($server_online) {
    foreach($tm->tasks as $task) {
      if (! $task->succeed())
	$tasks[]= $task;
    }

    foreach($tasks as $task) {
      if (get_class($task) == 'Task_install') {
	foreach($task->applications as $app) {
	  if (! in_array($app, $apps_in_install))
	    $apps_in_install[]= $app;
	}
      }
      if (get_class($task) == 'Task_remove') {
	foreach($task->applications as $app) {
	  if (! in_array($app, $apps_in_remove))
	    $apps_in_remove[]= $app;
	}
      }
    }
  }


  $prefs = Preferences::getInstance();
  if (! $prefs)
    die_error('get Preferences failed',__FILE__,__LINE__);

  $mods_enable = $prefs->get('general','module_enable');
  if (! in_array('ApplicationDB',$mods_enable))
    die_error(_('Module ApplicationDB must be enabled'),__FILE__,__LINE__);
  $mod_app_name = 'admin_ApplicationDB_'.$prefs->get('ApplicationDB','enable');
  $applicationDB = new $mod_app_name();

  $applications_all = $applicationDB->getList();
  $applications = $server->getApplications();
  if (! is_array($applications))
    $applications = array();

  $applications_available = array();
  if ($server_online) {
    foreach($applications_all as $app) {
      if (in_array($app, $applications))
	continue;
      if (in_array($app, $apps_in_install))
	continue;

      $applications_available[]= $app;
    }
  }

  if (!$server_online && count($applications) == 0)
    $applications_all = array();


  $servers_all = Servers::getOnline();
  foreach($servers_all as $k => $v) {
    if ($v->fqdn == $server->fqdn)
      unset($servers_all[$k]);
  }

  $sessions = new Sessions();
  $sessions = $sessions->getActivesForServer($_GET['fqdn']);

  if (count($sessions) > 0)
    $has_sessions = true;
  else
    $has_sessions = false;

  if ($server_online) {
    if ($server_lock) {
      $switch_button = _('Switch to production');
      $switch_value = 0;
    }
    else {
      $switch_button = _('Switch to maintenance');
      $switch_value = 1;
    }
  }

  $external_name = $server->getAttribute('external_name');
  $web_port = $server->getAttribute('web_port');

  include_once('header.php');

  echo '<div id="servers_div">';
  echo '<h1>'.$server->fqdn.'</h1>';

//   if ($server_online === false)
//     echo '<h2><p class="msg_error centered">'.$status_error_msg.'</p></h2>';

  echo '<div>';
  echo '<h2>'._('Monitoring').'</h2>';
  echo '<table class="main_sub" border="0" cellspacing="1" cellpadding="3">';
  echo '<tr class="title">';

  echo '<th>'._('Type').'</th><th>'._('Version').'</th><th>'._('Status').'</th>';
  echo '<th>'._('Details').'</th>';
  if ($server_online)
    echo '<th>'._('Monitoring').'</th>';
  echo '</tr>';

  echo '<tr class="content1">';
  echo '<td style="text-align: center;"><img src="media/image/server-'.$server->stringType().'.png" alt="'.$server->stringType().'" title="'.$server->stringType().'" /><br />'.$server->stringType().'</td>';

  echo '<td>'.$server->stringVersion().'</td>';
  echo '<td>'.$server->stringStatus().'</td>';
  echo '<td>'._('CPU').'; : '.$server->getAttribute('cpu_model').' (x'.$server->getAttribute('cpu_nb').')<br />'._('RAM').' : '.round($server->getAttribute('ram')/1024).' '._('MB').'</td>';

  if ($server_online) {
    echo '<td>';
        $buf = round(($server->getNbUsedSessions()/$server->getNbAvailableSessions())*100);
        echo _('CPU usage').': '.$server->getCpuUsage().'<br />';
        echo display_loadbar($server->getCpuUsage());
        echo _('RAM usage').': '.$server->getRamUsage().'<br />';
        echo display_loadbar($server->getRamUsage());
        echo _('Sessions usage').': '.$buf.'%<br />';
        echo display_loadbar($buf);
    echo '</td>';
  }

  echo '</tr>';
  echo '</table>';
  echo '<div>';

  echo '<div>';
  echo '<h2>'._('Configuration').'</h2>';



  echo '<div>';
  echo '<form action="servers.php" method="GET">';
  echo '<input type="hidden" name="fqdn" value="'.$server->fqdn.'" />';
  echo '<input type="hidden" name="action" value="available_sessions" />';

  echo _('Number of available sessions on this server').': ';
  echo '<input type="button" value="-" onclick="field_increase(\'number\', -1);" /> ';
  echo '<input type="text" id="number" name="nb_sessions" value="'.$server->getNbAvailableSessions().'" size="3" onchange="field_check_integer(this);" />';
  echo ' <input type="button" value="+" onclick="field_increase(\'number\', 1);" />';

  echo ' <input type="submit" value="'._('change').'" />';
  echo '</form>';
  echo "</div>\n";

  echo '<div>';
  echo '<form action="servers.php" method="GET">';
  echo '<input type="hidden" name="fqdn" value="'.$server->fqdn.'" />';
  echo '<input type="hidden" name="action" value="external_name" />';

  echo _('Redirection name of this server').': ';
  echo '<input type="text" name="external_name" value="'.$external_name.'" />';

  echo ' <input type="submit" value="'._('change').'" />';
  echo '</form>';
  echo "</div>\n";

  echo '<div>';
  echo '<form action="servers.php" method="GET">';
  echo '<input type="hidden" name="fqdn" value="'.$server->fqdn.'" />';
  echo '<input type="hidden" name="action" value="web_port" />';

  echo _('Web port of this server').': ';
  echo '<input type="text" name="web_port" value="'.$web_port.'" />';

  echo ' <input type="submit" value="'._('change').'" />';
  echo '</form>';
  echo "</div>\n";

  if ($server_online) {
    echo '<form action="servers.php" method="get">';
    echo '<input type="hidden" name="fqdn" value="'.$server->fqdn.'" />';
    echo '<input type="hidden" name="action" value="maintenance" />';
    echo '<input type="hidden" name="maintenance" value="'.$switch_value.'" />';
    echo '<input type="submit" value="'.$switch_button.'"/>';
    echo '</form>';
  }


  if ($server_lock || !$server_online) {
    echo '<form action="servers.php" method="get" onsubmit="return confirm(\''._('Are you sure you want to delete this server?').'\');">';
    echo '<input type="hidden" name="action" value="delete" />';
    echo '<input type="hidden" name="fqdn" value="'.$server->fqdn.'" />';
    echo '<input type="submit" value="'._('delete').'" />';
    echo '</form>';
  }

  if ($server_online) {
    echo '<h3>'._('Install an application from a package name').'</h3>';
    echo '<form>';
    echo '<input type="hidden" name="action" value="install_line">';
    echo '<input type="hidden" name="fqdn" value="'.$server->fqdn.'">';
    echo '<input type="text" name="line"> ';
    echo '<input type="submit" value="'._('Install').'">';
    echo '</form>';
  }
  echo '</div>';


  // Application part
  if (count($applications_all) > 0) {
    $count = 0;
    echo '<div>';
    echo '<h2>'._('Applications available on this server').'</h2>';
    echo '<table border="0" cellspacing="1" cellpadding="3">';

    if (count($applications) > 0) {
      foreach ($applications as $app) {
	$content = 'content'.(($count++%2==0)?1:2);
	$remove_in_progress = in_array($app, $apps_in_remove);
	$icon_id = ($app->haveIcon())?$app->getAttribute('id'):0;

	echo '<tr class="'.$content.'">';
	echo '<td>';
	echo '<img src="media/image/cache.php?id='.$icon_id.'" alt="" title="" /> ';
	echo '<a href="applications.php?action=manage&id='.$app->getAttribute('id').'">';
	echo $app->getAttribute('name').'</a>';
	echo '</td>';
	if ($server_online) {
	  echo '<td>';
	  if ($remove_in_progress)
	    echo 'remove in progress';
	  else {
	    echo '<form action="actions.php" method="post" onsubmit="return confirm(\''._('Are you sure you want to remove this application from this server?').'\');">';
	    echo '<input type="hidden" name="action" value="del" />';
	    echo '<input type="hidden" name="name" value="Application_Server" />';
	    echo '<input type="hidden" name="server" value="'.$server->fqdn.'" />';
	    echo '<input type="hidden" name="application" value="'.$app->getAttribute('id').'" />';
	    echo '<input type="submit" value="'._('Remove from this server').'" />';
	    echo '</form>';
	  }
	}

	echo '</td>';
	echo '</tr>';
      }
    }

    foreach ($apps_in_install as $app) {
      $content = 'content'.(($count++%2==0)?1:2);

      echo '<tr class="'.$content.'">';
      echo '<td>';
      echo '<a href="applications.php?action=manage&id='.$app->getAttribute('id').'">';
      echo $app->getAttribute('name').'</a>';
      echo '</td>';
      echo '<td>'._('install in progress').'</td>';
      echo '</tr>';
    }

    if (count($applications_available) > 0) {
      $content = 'content'.(($count++%2==0)?1:2);
      echo '<tr class="'.$content.'"><form action="actions.php" method="post">';
      echo '<input type="hidden" name="action" value="add" />';
      echo '<input type="hidden" name="name" value="Application_Server" />';
      echo '<input type="hidden" name="server" value="'.$server->fqdn.'" />';
      echo '<td>';
      echo '<select name="application">';
      foreach ($applications_available as $app)
        echo '<option value="'.$app->getAttribute('id').'">'.$app->getAttribute('name').'</option>';
      echo '</select>';
      echo '</td>';
      echo '<td><input type="submit" value="'._('Install on this server').'" /></td>';
      echo '</form></tr>';
    }

    echo '</table>';
    echo "</div>\n";
  }

  // Server Replication part
  if (count($servers_all)>0) {
    echo '<div>';
    echo '<h3>'._('Replication').'</h3>';
    echo '<form action="" method="post">';
    echo '<input type="hidden" name="action" value="replication" />';
    echo '<input type="hidden" name="fqdn" value="'.$server->fqdn.'" />';

    echo '<table border="0" cellspacing="1" cellpadding="3">';
    foreach($servers_all as $server_) {
      echo '<tr>';
      echo '<td><input type="checkbox" name="servers[]" value="'.$server_->fqdn.'" /></td>';
      echo '<td><a href="servers.php?action=manage&fqdn='.$server_->fqdn.'">'.$server_->fqdn.'</a></td></tr>';
    }
    echo '<tr><td></td><td><input type="submit" value="'._('Replicate on those servers').'" /></td></tr>';
    echo '</table>';
    echo "</div>\n";
  }


  // Tasks part
  if (count($tasks) >0) {
    echo '<div>';
    echo '<h2>'._('Active tasks on this server').'</h1>';
    echo '<table border="0" cellspacing="1" cellpadding="3">';
    echo '<tr class="title">';
    echo '<th>'._('ID').'</th>';
    echo '<th>'._('Type').'</th>';
    echo '<th>'._('Status').'</th>';
    echo '<th>'._('Details').'</th>';
    echo '</tr>';

    $count = 0;
    foreach($tasks as $task) {
      $content = 'content'.(($count++%2==0)?1:2);
      if ($task->failed())
	$status = '<span class="msg_error">'._('Error').'</span>';
      else
	$status = '<span class="msg_ok">'.$task->status.'</span>';

      echo '<tr class="'.$content.'">';
      echo '<td><a href="tasks.php?action=manage&id='.$task->id.'">'.$task->id.'</a></td>';
      echo '<td>'.get_class($task).'</td>';
      echo '<td>'.$status.'</td>';
      echo '<td>'.$task->getRequest().', '.$task->status_code.'</td>';
      echo '</tr>';
    }
    echo '</table>';
    echo "</div>\n";
  }


  // Sessions part
  if ($has_sessions) {
    echo '<div>';
    echo '<h2>'._('Active sessions').'</h1>';
    echo '<table border="0" cellspacing="1" cellpadding="3">';
    foreach($sessions as $session) {
      echo '<form action="sessions.php"><tr>';
      echo '<td>'.$session->getStartTime().'</td>';
      echo '<td><a href="users.php?action=manage&id='.$session->getSetting('user_login').'">'.$session->getSetting('user_displayname').'</td>';
      echo '<td>';
      echo '<input type="hidden" name="info" value="'.$session->session.'" />';
      echo '</td><td><input type="submit" value="'._('Informations about this session').'" /></td>';
      echo '</td>';
      echo '</tr></form>';
    }
    echo '</table>';
    echo '</div>';
  }

  include_once('footer.php');
  die();
}

