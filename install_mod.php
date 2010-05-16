<?php

/***********************************************************************/

// Some info about your mod.
$mod_title			= 'Usermap';
$mod_version		= '1.0';
$release_date		= 'YYYY-MM-DD';
$author				= 'Gizzmo';
$author_email		= 'justgiz@gmail.com';

// Versions of FluxBB this mod was created for. Minor variations (i.e. 1.2.4 vs 1.2.5) will be allowed, but a warning will be displayed.
$fluxbb_versions	= array('1.4-rc3');

// Set this to false if you haven't implemented the restore function (see below)
$mod_restore		= true;

// This following function will be called when the user presses the "Install" button
function install()
{
	global $db, $db_type, $pun_config;

	/* Config*/
	$db->query('INSERT INTO  `'.$db->prefix.'config` (`conf_name` ,`conf_value`) VALUES
		(\'o_um_default_lat\',		\'0\'),
		(\'o_um_default_lng\',		\'0\'),
		(\'o_um_default_zoom\',		\'1\'),
		(\'o_um_height\',			\'500\'),
		(\'o_um_fit_map\',			\'0\'),
		(\'o_um_find_location\',	\'0\')
	') or error('Unable to add config entries to the `'.$db->prefix.'config` table.', __FILE__, __LINE__, $db->error());

	/* Users */
	$db->query('ALTER TABLE `'.$db->prefix.'users`
		ADD `um_lat` DOUBLE NULL DEFAULT NULL,
		ADD `um_lng` DOUBLE NULL DEFAULT NULL,
		ADD `um_scrollwheel` TINYINT(1) NOT NULL DEFAULT  \'0\'
	') or error('Unable to add the `Latitude`, `Longitude`, and `Scrollwheel` fields to the `'.$db->prefix.'user` table.', __FILE__, __LINE__, $db->error());

	/* Group */
	$db->query('ALTER TABLE `'.$db->prefix.'groups`
		ADD `g_um_view_map` TINYINT(1) NOT NULL DEFAULT \'1\',
		ADD `g_um_add_to_map` TINYINT(1) NOT NULL DEFAULT \'1\',
		ADD `g_um_icon` VARCHAR(50) NOT NULL DEFAULT \'white.png\'
	') or error('Unable to add the `g_um_view_map`, `g_um_add_to_map` and `g_um_icon` fields to the `'.$db->prefix.'groups` table.', __FILE__, __LINE__, $db->error());

	// $db->query('') or error('', __FILE__, __LINE__, $db->error());

	// update cache, we added config options
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PUN_ROOT.'include/cache.php';

	generate_config_cache();
}

// This following function will be called when the user presses the 'Restore' button (only if $mod_uninstall is true (see above))
function restore()
{
	global $db, $db_type, $pun_config;

	/* Config */
	$db->query('DELETE FROM `'.$db->prefix.'config` WHERE
		`conf_name` = \'o_um_default_lat\' OR
		`conf_name` = \'o_um_default_lng\' OR
		`conf_name` = \'o_um_default_zoom\' OR
		`conf_name` = \'o_um_height\' OR
		`conf_name` = \'o_um_fit_map\' OR
		`conf_name` = \'o_um_find_location\'
	') or error('Unable to remove config items from the `'.$db->prefix.'config` table', __FILE__, __LINE__, $db->error());

	/* Users */
	$db->query('ALTER TABLE `'.$db->prefix.'users`
		DROP `um_lat`,
		DROP `um_lng`
	') or error('Unable to remove the `um_lat` and `um_lng` fields from the `'.$db->prefix.'user` table.', __FILE__, __LINE__, $db->error());

	/* Group */
	$db->query('ALTER TABLE `'.$db->prefix.'groups`
		DROP `g_um_view_map`,
		DROP `g_um_add_to_map`,
		DROP `g_um_icon`
	') or error('Unable to remove the `g_um_view_map`, `g_um_add_to_map` and `g_um_icon` fields from the `'.$db->prefix.'groups` table.', __FILE__, __LINE__, $db->error());

	// $db->query('') or error('', __FILE__, __LINE__, $db->error());

	// update cache, we removed config items
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PUN_ROOT.'include/cache.php';

	generate_config_cache();
}

/***********************************************************************/

// DO NOT EDIT ANYTHING BELOW THIS LINE!


// Circumvent maintenance mode
define('PUN_TURN_OFF_MAINT', 1);
define('PUN_ROOT', './');
require PUN_ROOT.'include/common.php';

// We want the complete error message if the script fails
if (!defined('PUN_DEBUG'))
	define('PUN_DEBUG', 1);

// Make sure we are running a FluxBB version that this mod works with
$version_warning = false;
if(!in_array($pun_config['o_cur_version'], $fluxbb_versions))
{
	foreach ($fluxbb_versions as $temp)
	{
		if (substr($temp, 0, 3) == substr($pun_config['o_cur_version'], 0, 3))
		{
			$version_warning = true;
			break;
		}
	}

	if (!$version_warning)
		exit('You are running a version of FluxBB ('.$pun_config['o_cur_version'].') that this mod does not support. This mod supports FluxBB versions: '.implode(', ', $fluxbb_versions));
}


$style = (isset($cur_user)) ? $cur_user['style'] : $pun_config['o_default_style'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo $mod_title ?> installation</title>
<link rel="stylesheet" type="text/css" href="style/<?php echo $pun_config['o_default_style'].'.css' ?>" />
</head>
<body>

<div id="punwrap">
	<div id="puninstall" class="pun" style="margin: 0 20% 0 20%">

		<div class="top-box"><div><!-- Top Corners --></div></div>

<?php

if ($pun_user['g_id'] != PUN_ADMIN)
{

?>
		<div class="block">
			<h2><span>Admin Only</span></h2>
			<div class="box">
				<div class="inbox">
					<p>Only a Admin can access this page. If you are the admin, make sure you are <a href="login.php">logged in.</a></p>
				</div>
			</div>
		</div>
<?php

}
else if (isset($_POST['form_sent']))
{
	if (isset($_POST['install']))
	{
		// Run the install function (defined above)
		install();

?>
		<div class="block">
			<h2><span>Installation successful</span></h2>
			<div class="box">
				<div class="inbox">
					<p>Your database has been successfully prepared for <?php echo pun_htmlspecialchars($mod_title) ?>. See readme.txt for further instructions.</p>
				</div>
			</div>
		</div>
<?php

	}
	else
	{
		// Run the restore function (defined above)
		restore();

?>
		<div class="block">
			<h2><span>Restore successful</span></h2>
			<div class="box">
				<div class="inbox">
					<p>Your database has been successfully restored. Dont forget to undo the changes you did.</p>
				</div>
			</div>
		</div>
<?php

	}
}
else
{

?>
		<div class="blockform">
			<h2><span>Mod installation</span></h2>
			<div class="box">
				<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>?foo=bar">
					<div><input type="hidden" name="form_sent" value="1" /></div>
					<div class="inform">
						<p>This script will update your database to work with the following modification:</p>
						<p><strong>Mod title:</strong> <?php echo pun_htmlspecialchars($mod_title).' '.$mod_version ?></p>
						<p><strong>Author:</strong> <?php echo pun_htmlspecialchars($author) ?> (<a href="mailto:<?php echo pun_htmlspecialchars($author_email) ?>"><?php echo pun_htmlspecialchars($author_email) ?></a>)</p>
						<p><strong>Disclaimer:</strong> Mods are not officially supported by FluxBB. Installation of this modification is done at your own risk. Backup your forum database and any and all applicable files before proceeding.</p>
						<p>If you've previously installed this mod and would like to uninstall it, you will need to remove all added and changed code. You can then click the restore button below to restore the database.</p>
<?php if ($version_warning): ?>						<p style="color: #a00"><strong>Warning:</strong> The mod you are about to install was not made specifically to support your current version of FluxBB (<?php echo $pun_config['o_cur_version']; ?>). However, in most cases this is not a problem and the mod will most likely work with your version as well. If you are uncertain about installning the mod due to this potential version conflict, contact the mod author.</p>
<?php endif; ?>					</div>
					<p class='buttons'>
						<input type="submit" name="install" value="Install" />
<?php if ($mod_restore): ?>						<input type="submit" name="restore" value="Restore" />
<?php endif; ?>					</p>

				</form>
			</div>
		</div>
<?php

}

?>

		<div class="end-box"><div><!-- Top Corners --></div></div>
	</div>
</div>

</body>
</html>