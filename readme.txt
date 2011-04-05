********************************************************************
              F L U X B B     M O D I F I C A T I O N
********************************************************************
Name:          Usermap
Author:        Gizzmo <justgiz@gmail.com>
Version:       1.1
Release date:  April 5th 2011
Works on:      1.4.5
********************************************************************
DISCLAIMER:
	Mods are not officially supported by FluxBB. Installation
	of this modification is done at your own risk. Backup your
	forum database and any and all applicable files before
	proceeding.

DESCRIPTION:
	This modification allows users on your site to share their
	location on a global map. Other users can view the global map to
	see everyone's location.

AFFECTED FILES:
	include/common.php
	include/functions.php
	header.php
	profile.php

AFFECTED DATABASE:
	config
	users
	groups

NOTES:
	- All options and group permissions settings are found through
	  the "Usermap Settings" plugin, link found at the bottom of the
	  admin menu.

	- Each group has a set of permissions. They have permission to
	  view the map (good for not allowing guests viewing the map).
	  They also have permission to add their location to the map
	  (good for new members, so the map doesn't get spammed).

	- Each group also can have an icon for the map, so they stand out
	  when others view the map.

	- If you want to you can view your users location in Google Earth,
	  by downloading the file here: <YourSiteUrl>/usermap/list.php?kml
	  Access to this is also protected by the group permissions.


********************************************************************
INSTALLATION:

1. Upload the following files to the root directory of your forum.
	usermap/	(folder)
	plugins/	(folder)
	install_mod.php
	usermap.php

2. Run and then delete the 'install_mod.php' file to install the
   database changes.

4. Follow the following steps to make the changes to files.

********************************************************************
#-------[ 1. Open ]

include/common.php


********************************************************************
#-------[ 2. Place at end of the file ]

// Usermap by Gizzmo - START
// Require things like functions or language files
require PUN_ROOT.'usermap/include.php';
// Usermap by Gizzmo - END


********************************************************************
#-------[ 3. Open ]

include/functions.php


********************************************************************
#-------[ 4. Find (line: 516) ]

		<div class="box">
			<div class="inbox">
				<ul>
					<li<?php if ($page == 'essentials') echo ' class="isactive"'; ?>><a href="profile.php?section=essentials&amp;id=<?php echo $id ?>"><?php echo $lang_profile['Section essentials'] ?></a></li>
					<li<?php if ($page == 'personal') echo ' class="isactive"'; ?>><a href="profile.php?section=personal&amp;id=<?php echo $id ?>"><?php echo $lang_profile['Section personal'] ?></a></li>


********************************************************************
#-------[ 5. After Add ]

<?php
// Usermap by Gizzmo - START
	global $lang_usermap;
	if ($pun_user['g_um_add_to_map'] == '1')
		echo "\t\t\t\t\t".'<li'.($page == 'usermap'? ' class="isactive"': '').'><a href="profile.php?section=usermap&amp;id='.$id.'">'.$lang_usermap['User map'].'</a></li>'."\n";
// Usermap by Gizzmo - END
?>

********************************************************************
#-------[ 6. Open ]

header.php


********************************************************************
#-------[ 7. Find (line: 185) ]

// Index should always be displayed
$links[] = '<li id="navindex"'.((PUN_ACTIVE_PAGE == 'index') ? ' class="isactive"' : '').'><a href="index.php">'.$lang_common['Index'].'</a></li>';

if ($pun_user['g_read_board'] == '1' && $pun_user['g_view_users'] == '1')
	$links[] = '<li id="navuserlist"'.((PUN_ACTIVE_PAGE == 'userlist') ? ' class="isactive"' : '').'><a href="userlist.php">'.$lang_common['User list'].'</a></li>';


********************************************************************
#-------[ 8. After Add ]

// Usermap by Gizzmo - START
if ($pun_user['g_um_view_map'] == '1')
	$links[] = '<li id="navusermap'.((PUN_ACTIVE_PAGE == 'usermap') ? ' class="isactive"': '').'"><a href="usermap.php">'.$lang_usermap['User map'].'</a></li>';
// Usermap by Gizzmo - END


********************************************************************
#-------[ 9. Open ]

profile.php


********************************************************************
#-------[ 10. Find (line: 866) ]

			if ($form['email_setting'] < 0 || $form['email_setting'] > 2)
				$form['email_setting'] = $pun_config['o_default_email_setting'];

			break;
		}


********************************************************************
#-------[ 11. After Add ]

// Usermap by Gizzmo - START
		case 'usermap':
		{
			if ($pun_user['g_um_add_to_map'] == '0')
				message($lang_common['No permission']);

			$form = array(
				'um_lat'			=> pun_trim($_POST['form']['um_lat']),
				'um_lng'			=> pun_trim($_POST['form']['um_lng']),
				'um_scrollwheel'	=> isset($_POST['form']['um_scrollwheel']) ? '1' : '0',
			);

			// if any of them are not numeric, or they are empty, they all are set to NULL
			if (!is_numeric($form['um_lat']) || !is_numeric($form['um_lng']) || (empty($form['um_lat']) && empty($form['um_lng'])))
				$form['um_lat'] = $form['um_lng'] = '';

			break;
		}
// Usermap by Gizzmo - END


********************************************************************
#-------[ 12. Find (line: 953) ]

$result = $db->query('SELECT u.username, u.email, u.title, u.realname, u.url, u.jabber, u.icq, u.msn, u.aim, u.yahoo, u.location, u.signature, u.disp_topics, u.disp_posts, u.email_setting, u.notify_with_post, u.auto_notify, u.show_smilies, u.show_img, u.show_img_sig, u.show_avatars, u.show_sig, u.timezone, u.dst, u.language, u.style, u.num_posts, u.last_post, u.registered, u.registration_ip, u.admin_note, u.date_format, u.time_format, u.last_visit, g.g_id, g.g_user_title, g.g_moderator FROM '.$db->prefix.'users AS u LEFT JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id WHERE u.id='.$id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());


********************************************************************
#-------[ 13. Replace With ]

// Usermap by Gizzmo - CHANGED
// NOTE: only added 'u.um_lat, u.um_lng, u.um_scrollwheel,'
$result = $db->query('SELECT u.um_lat, u.um_lng, u.um_scrollwheel, u.username, u.email, u.title, u.realname, u.url, u.jabber, u.icq, u.msn, u.aim, u.yahoo, u.location, u.signature, u.disp_topics, u.disp_posts, u.email_setting, u.notify_with_post, u.auto_notify, u.show_smilies, u.show_img, u.show_img_sig, u.show_avatars, u.show_sig, u.timezone, u.dst, u.language, u.style, u.num_posts, u.last_post, u.registered, u.registration_ip, u.admin_note, u.date_format, u.time_format, g.g_id, g.g_user_title, g.g_moderator FROM '.$db->prefix.'users AS u LEFT JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id WHERE u.id='.$id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());


********************************************************************
#-------[ 14. Find (line 1770) ]

		</div>
	</div>
<?php

	}


********************************************************************
#-------[ 15. After Add ]

// Usermap by Gizzmo - START
	else if ($section == 'usermap')
	{
		if ($pun_user['g_um_view_map'] == '0' || $pun_user['g_um_add_to_map'] == '0')
			message($lang_common['Bad request']);

		$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), $lang_common['Profile'], $lang_usermap['User map']);
		$page_head = array(
			// The Libs
			'jquery'	=> '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>',
			'googleapi'	=> '<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>',

			// Context Menu
			'contextmenuJS' => '<script src="usermap/contextMenu/code.js"></script>',
			'contextmenuCSS' => '<link href="usermap/contextMenu/style.css" rel="stylesheet" type="text/css">',

			// The Core
			'css'	=> '<link rel="stylesheet" type="text/css" media="screen" href="usermap/style.css" />',
			'core'	=> '<script type="text/javascript" src="usermap/script.js"></script>'
		);

		// code
		ob_start();
?>
<script type="text/javascript">
$(function(){
	UserMap.defaults = {
		center:  [<?php echo $pun_config['o_um_default_lat'].','.$pun_config['o_um_default_lng']?>],
		zoom: <?php echo $pun_config['o_um_default_zoom']?>,
		height: <?php echo $pun_config['o_um_height']?>,
		scrollwheel: <?php echo ($pun_user['um_scrollwheel'])? 'true': 'false'?>,
	};
<?php
	$options = array();

	if ($user['um_lat'] !='' && $user['um_lng'] !='')
	{
		$options['center'] = array($user['um_lat'], $user['um_lng']);
		$options['zoom'] = 14;
	}
?>

	new UserMap(<?php if (!empty($options)) echo json_encode($options)?>).profile();
});
</script>

<?php
		$page_head[] = trim(ob_get_contents());
		ob_end_clean();

		define('PUN_ACTIVE_PAGE', 'profile');
		require PUN_ROOT.'header.php';

		generate_profile_menu('usermap');

?>
	<div class='blockform'>
		<h2><span><?php echo pun_htmlspecialchars($user['username']).' - '.$lang_usermap['User map']?></span></h2>

		<div class='box' id='user_map_canvas'></div>

		<div class='box'>
			<form id='profile_usermap' method='post' action='profile.php?section=usermap&amp;id=<?php echo $id ?>'>
				<div class='inform'>
					<input type='hidden' name='form_sent' value='1' />
					<input type='hidden' id='um_lat' name='form[um_lat]' value='<?php echo $user['um_lat'] ?>'/>
					<input type='hidden' id='um_lng' name='form[um_lng]' value='<?php echo $user['um_lng']?>' />

					<fieldset>
						<legend><?php echo $lang_usermap['User map legend']?></legend>
						<div class='infldset'>
							<p><?php echo $lang_usermap['User map help']?></p>
							<div class='rbox'>
								<label><input type='checkbox' id='mouse_zoom' name='form[um_scrollwheel]' value='1'<?php if ($user['um_scrollwheel'] == '1') echo ' checked="checked"' ?> /> <?php echo $lang_usermap['Scrollwheel zoom help']?><br/></label>
							</div>
						</div>
					</fieldset>
				</div>
<?php if ($user['um_lat'] =='' && $user['um_lng'] ==''): ?>

				<div class='inform'>
					<fieldset>
						<legend><?php echo $lang_usermap['Find location']?></legend>
						<div class='infldset'>
							<p><?php echo $lang_usermap['Find location help']?></p>
							<p class='clearb actions'><span><a id='findLocation' href='#'><?php echo $lang_usermap['Find location']?></a></span></p>
						</div>
					</fieldset>
				</div>
<?php endif; ?>
				<p class='buttons'><input type='submit' name='update' value='<?php echo $lang_common['Submit'] ?>' /> <?php echo $lang_profile['Instructions'] ?></p>
			</form>
		</div>
	</div>
<?php
	}
// Usermap by Gizzmo - END


********************************************************************
#-------[ 16. Save and Upload ]

