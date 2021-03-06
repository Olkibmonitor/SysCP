<?php

/**
 * This file is part of the SysCP project.
 * Copyright (c) 2003-2009 the SysCP Team (see authors).
 *
 * For the full copyright and license information, please view the COPYING
 * file that was distributed with this source code. You can also view the
 * COPYING file online at http://files.syscp.org/misc/COPYING.txt
 *
 * @copyright  (c) the authors
 * @author     Florian Lippert <flo@syscp.org>
 * @author     Martin Burchert <eremit@syscp.org>
 * @license    GPLv2 http://files.syscp.org/misc/COPYING.txt
 * @package    System
 * @version    $Id$
 */

/**
 * STARTING REDUNDANT CODE, WHICH IS SOME KINDA HEADER FOR EVERY CRON SCRIPT.
 * When using this "header" you have to change $lockFilename for your needs.
 * Don't forget to also copy the footer which closes database connections
 * and the lockfile!
 */

include (dirname(__FILE__) . '/../lib/cron_init.php');

/**
 * END REDUNDANT CODE (CRONSCRIPT "HEADER")
 */

/**
 * LOOK INTO TASKS TABLE TO SEE IF THERE ARE ANY UNDONE JOBS
 */

fwrite($debugHandler, '  cron_tasks: Searching for tasks to do' . "\n");
$cronlog->logAction(CRON_ACTION, LOG_INFO, "Searching for tasks to do");
$result_tasks = $db->query("SELECT `id`, `type`, `data` FROM `" . TABLE_PANEL_TASKS . "` ORDER BY `id` ASC");
$resultIDs = array();

while($row = $db->fetch_array($result_tasks))
{
	$resultIDs[] = $row['id'];

	if($row['data'] != '')
	{
		$row['data'] = unserialize($row['data']);
	}

	/**
	 * TYPE=1 MEANS TO REBUILD APACHE VHOSTS.CONF
	 */

	if($row['type'] == '1')
	{
		if(!isset($webserver))
		{
			if($settings['system']['webserver'] == "apache2")
			{
				if($settings['system']['mod_fcgid'] == 1)
				{
					$webserver = new apache_fcgid($db, $cronlog, $debugHandler, $settings);
				}
				else
				{
					$webserver = new apache($db, $cronlog, $debugHandler, $settings);
				}
			}
			elseif($settings['system']['webserver'] == "lighttpd")
			{
				$webserver = new lighttpd($db, $cronlog, $debugHandler, $settings);
			}
		}

		if(isset($webserver))
		{
			$webserver->createIpPort();
			$webserver->createVirtualHosts();
			$webserver->createFileDirOptions();
			$webserver->writeConfigs();
			$webserver->reload();
		}
		else
		{
			echo "Please check you Webserver settings\n";
		}
	}

	/**
	 * TYPE=2 MEANS TO CREATE A NEW HOME AND CHOWN
	 */
	elseif ($row['type'] == '2')
	{
		fwrite($debugHandler, '  cron_tasks: Task2 started - create new home' . "\n");
		$cronlog->logAction(CRON_ACTION, LOG_INFO, 'Task2 started - create new home');

		if(is_array($row['data']))
		{
			$cronlog->logAction(CRON_ACTION, LOG_NOTICE, 'Running: mkdir -p ' . escapeshellarg($settings['system']['documentroot_prefix'] . $row['data']['loginname'] . '/webalizer'));
			safe_exec('mkdir -p ' . escapeshellarg($settings['system']['documentroot_prefix'] . $row['data']['loginname'] . '/webalizer'));
			$cronlog->logAction(CRON_ACTION, LOG_NOTICE, 'Running: mkdir -p ' . escapeshellarg($settings['system']['vmail_homedir'] . $row['data']['loginname']));
			safe_exec('mkdir -p ' . escapeshellarg($settings['system']['vmail_homedir'] . $row['data']['loginname']));

			//check if admin of customer has added template for new customer directories

			$result = $db->query("SELECT `t`.`value`, `c`.`email` AS `customer_email`, `a`.`email` AS `admin_email`, `c`.`loginname` AS `customer_login`, `a`.`loginname` AS `admin_login` FROM `" . TABLE_PANEL_CUSTOMERS . "` AS `c` INNER JOIN `" . TABLE_PANEL_ADMINS . "` AS `a` ON `c`.`adminid` = `a`.`adminid` INNER JOIN `" . TABLE_PANEL_TEMPLATES . "` AS `t` ON `a`.`adminid` = `t`.`adminid` WHERE `varname` = 'index_html' AND `c`.`loginname` = '" . $db->escape($row['data']['loginname']) . "'");

			if($db->num_rows($result) > 0)
			{
				$template = $db->fetch_array($result);
				$replace_arr = array(
					'SERVERNAME' => $settings['system']['hostname'],
					'CUSTOMER' => $template['customer_login'],
					'ADMIN' => $template['admin_login'],
					'CUSTOMER_EMAIL' => $template['customer_email'],
					'ADMIN_EMAIL' => $template['admin_email']
				);
				$htmlcontent = replace_variables($template['value'], $replace_arr);
				$indexhtmlpath = $settings['system']['documentroot_prefix'] . $row['data']['loginname'] . '/index.' . $settings['system']['index_file_extension'];
				$index_html_handler = fopen($indexhtmlpath, 'w');
				fwrite($index_html_handler, $htmlcontent);
				fclose($index_html_handler);
				$cronlog->logAction(CRON_ACTION, LOG_NOTICE, 'Creating \'index.' . $settings['system']['index_file_extension'] . '\' for Customer \'' . $template['customer_login'] . '\' based on template in directory ' . escapeshellarg($indexhtmlpath));
			}
			else
			{
				$cronlog->logAction(CRON_ACTION, LOG_NOTICE, 'Running: cp -a ' . $pathtophpfiles . '/templates/misc/standardcustomer/* ' . escapeshellarg($settings['system']['documentroot_prefix'] . $row['data']['loginname'] . '/'));
				safe_exec('cp -a ' . $pathtophpfiles . '/templates/misc/standardcustomer/* ' . escapeshellarg($settings['system']['documentroot_prefix'] . $row['data']['loginname'] . '/'));
			}

			$cronlog->logAction(CRON_ACTION, LOG_NOTICE, 'Running: chown -R ' . (int)$row['data']['uid'] . ':' . (int)$row['data']['gid'] . ' ' . escapeshellarg($settings['system']['documentroot_prefix'] . $row['data']['loginname']));
			safe_exec('chown -R ' . (int)$row['data']['uid'] . ':' . (int)$row['data']['gid'] . ' ' . escapeshellarg($settings['system']['documentroot_prefix'] . $row['data']['loginname']));
			$cronlog->logAction(CRON_ACTION, LOG_NOTICE, 'Running: chown -R ' . (int)$settings['system']['vmail_uid'] . ':' . (int)$settings['system']['vmail_gid'] . ' ' . escapeshellarg($settings['system']['vmail_homedir'] . $row['data']['loginname']));
			safe_exec('chown -R ' . (int)$settings['system']['vmail_uid'] . ':' . (int)$settings['system']['vmail_gid'] . ' ' . escapeshellarg($settings['system']['vmail_homedir'] . $row['data']['loginname']));
		}
	}

	/**
	 * TYPE=3 MEANS TO DO NOTHING
	 */
	elseif ($row['type'] == '3')
	{
	}

	/**
	 * TYPE=4 MEANS THAT SOMETHING IN THE BIND CONFIG HAS CHANGED. REBUILD syscp_bind.conf
	 */
	elseif ($row['type'] == '4')
	{
		if(!isset($nameserver))
		{
			$nameserver = new bind($db, $cronlog, $debugHandler, $settings);
		}

		if($settings['dkim']['use_dkim'] == '1')
		{
			$nameserver->writeDKIMconfigs();
		}

		$nameserver->writeConfigs();
	}

	/**
	 * TYPE=5 MEANS THAT A NEW FTP-ACCOUNT HAS BEEN CREATED, CREATE THE DIRECTORY
	 */
	elseif ($row['type'] == '5')
	{
		$cronlog->logAction(CRON_ACTION, LOG_INFO, 'Creating new FTP-home');
		$result_directories = $db->query('SELECT `f`.`homedir`, `f`.`uid`, `f`.`gid`, `c`.`documentroot` AS `customerroot` FROM `' . TABLE_FTP_USERS . '` `f` LEFT JOIN `' . TABLE_PANEL_CUSTOMERS . '` `c` USING (`customerid`) ');

		while($directory = $db->fetch_array($result_directories))
		{
			mkDirWithCorrectOwnership($directory['customerroot'], $directory['homedir'], $directory['uid'], $directory['gid']);
		}
	}
}

if($db->num_rows($result_tasks) != 0)
{
	$where = array();
	foreach($resultIDs as $id)
	{
		$where[] = '`id`=\'' . (int)$id . '\'';
	}

	$where = implode($where, ' OR ');
	$db->query('DELETE FROM `' . TABLE_PANEL_TASKS . '` WHERE ' . $where);
	unset($resultIDs);
	unset($where);
}

$db->query('UPDATE `' . TABLE_PANEL_SETTINGS . '` SET `value` = UNIX_TIMESTAMP() WHERE `settinggroup` = \'system\'   AND `varname`      = \'last_tasks_run\' ');

/**
 * STARTING CRONSCRIPT FOOTER
 */

include ($pathtophpfiles . '/lib/cron_shutdown.php');

/**
 * END CRONSCRIPT FOOTER
 */

?>
