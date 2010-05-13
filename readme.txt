********************************************************************
              F L U X B B     M O D I F I C A T I O N
********************************************************************
Name:          Usermap
Author:        Gizzmo <justgiz@gmail.com>
Version:       1.0-rc1
Release date:  2010-05-08
Works on:      1.4-rc3
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
	profile.php
	include/functions.php
	include/common.php

AFFECTED DATABASE:
	config
	users
	groups

NOTES:
	- All options and group permissions settings are found though
	  the "Usermap Settings" plugin, link found at the bottom of the
	  admin menu.

	- Each group has a set of permissions. They have permiission to
	  view the map (good for not allowing guests viewing the map).
	  They also have permission to add their location to the map
	  (good for new memebers, so the map doesnt get spamed).

	- Each group also can have a icon for the map, so they stand out
	  when others view the map.

	- If you want to you can view your users location in Google Earch,
	  by downloading the file here: <YourSiteUrl>/usermap/list.php?kml
	  Access to this is also protected by the group permissions.

TODO:
	- Option to enable/disable scroll wheel zoom on the map.
	  Admin and/or user option.
	- Save location when viewing the main map, so when you come back
	  you are where you left it.
	- Make sure install works for other databases.
	- Context menu for the map.

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

   profile.php


********************************************************************
#-------[ 2. Find (line:~857) ]

				$form['email_setting'] = $pun_config['o_default_email_setting'];

			break;
		}


********************************************************************
#-------[ 3. After, Add ]

// Usermap by Gizzmo - START
		case 'usermap':
		{
			if ($pun_user['g_um_add_to_map'] == '0')
				message($lang_common['No permission']);

			$form = array(
				'um_lat'	=> pun_trim($_POST['form']['um_lat']),
				'um_lng'	=> pun_trim($_POST['form']['um_lng'])
			);

			// if any of them are not numeric, or they are empty, they all are set to NULL
			if (!is_numeric($form['um_lat']) || !is_numeric($form['um_lng']) || (empty($form['um_lat']) && empty($form['um_lng'])))
				$form['um_lat'] = $form['um_lng'] = '';

			break;
		}
// Usermap by Gizzmo - END


********************************************************************
#-------[ 4. Find (line: ~941) ]

$result = $db->query('SELECT u.username, u.email, u.title, u.realname, u.url, u.jabber, u.icq, u.msn, u.aim, u.yahoo, u.location, u.signature, u.disp_topics, u.disp_posts, u.email_setting, u.notify_with_post, u.auto_notify, u.show_smilies, u.show_img, u.show_img_sig, u.show_avatars, u.show_sig, u.timezone, u.dst, u.language, u.style, u.num_posts, u.last_post, u.registered, u.registration_ip, u.admin_note, u.date_format, u.time_format, g.g_id, g.g_user_title, g.g_moderator FROM '.$db->prefix.'users AS u LEFT JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id WHERE u.id='.$id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());


********************************************************************
#-------[ 5. Replace With ]

// $result = $db->query('SELECT u.username, u.email, u.title, u.realname, u.url, u.jabber, u.icq, u.msn, u.aim, u.yahoo, u.location, u.signature, u.disp_topics, u.disp_posts, u.email_setting, u.notify_with_post, u.auto_notify, u.show_smilies, u.show_img, u.show_img_sig, u.show_avatars, u.show_sig, u.timezone, u.dst, u.language, u.style, u.num_posts, u.last_post, u.registered, u.registration_ip, u.admin_note, u.date_format, u.time_format, g.g_id, g.g_user_title, g.g_moderator FROM '.$db->prefix.'users AS u LEFT JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id WHERE u.id='.$id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
// Usermap by Gizzmo - CHANGED
$result = $db->query('SELECT u.username, u.email, u.title, u.realname, u.url, u.jabber, u.icq, u.msn, u.aim, u.yahoo, u.location, u.signature, u.disp_topics, u.disp_posts, u.email_setting, u.notify_with_post, u.auto_notify, u.show_smilies, u.show_img, u.show_img_sig, u.show_avatars, u.show_sig, u.timezone, u.dst, u.language, u.style, u.num_posts, u.last_post, u.registered, u.registration_ip, u.admin_note, u.date_format, u.time_format, g.g_id, g.g_user_title, g.g_moderator, u.um_lat, u.um_lng FROM '.$db->prefix.'users AS u LEFT JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id WHERE u.id='.$id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());


********************************************************************
#-------[ 6. Find (line:~1693) ]

			</form>
		</div>
	</div>
<?php

	}


********************************************************************
#-------[ 7. After, Add ]

// Usermap by Gizzmo - START
	else if ($section == 'usermap')
	{
		if ($pun_user['g_um_view_map'] == '0' || $pun_user['g_um_add_to_map'] == '0')
			message($lang_common['Bad request']);

		$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), $lang_common['Profile'], $lang_usermap['User map']);
		$page_head = array(
			'css'		=> '<link rel="stylesheet" type="text/css" media="screen" href="usermap/style.css" />',
			'jquery'	=> '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>',
			'googleapi'	=> '<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>',
			'jscript'	=> '<script type="text/javascript" src="usermap/script.js"></script>',
'<script type="text/javascript">
$(function(){
	UserMap.defaults = {
		latlng:  ['.$pun_config['o_um_default_lat'].','.$pun_config['o_um_default_lng'].'],
		zoom: '.$pun_config['o_um_default_zoom'].',
		height: '.$pun_config['o_um_height'].'
	};'.(($user['um_lat'] !='' && $user['um_lng'] !='')? '
	UserMap.options = {
		latlng: ['.$user['um_lat'].','.$user['um_lng'].'],
		zoom: 14
	};':'').'
	UserMap.profile.init();
});
</script>'
		);
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
<?php if ($user['um_lat'] =='' && $user['um_lng'] =='' && $pun_config['o_um_find_location'] == '1'): ?>
							<p><?php echo $lang_usermap['Find location help']?></p>
							<p class='clearb actions'><span><a href='javascript:UserMap.profile.find_location();'><?php echo $lang_usermap['Find location']?></a></span></p>
<?php endif; ?>
							<p><?php echo $lang_usermap['User map help']?></p>
						</div>
					</fieldset>
				</div>
				<p class='buttons'><input type='submit' name='update' value='<?php echo $lang_common['Submit'] ?>' /> <?php echo $lang_profile['Instructions'] ?></p>
			</form>
		</div>
	</div>
<?php
	}
// Usermap by Gizzmo - END


********************************************************************
#-------[ 7. Open ]

   include/functions.php


********************************************************************
#-------[ 8. Find (line: ~414) ]

	if ($pun_user['g_read_board'] == '1' && $pun_user['g_view_users'] == '1')
		$links[] = '<li id="navuserlist"'.((PUN_ACTIVE_PAGE == 'userlist') ? ' class="isactive"' : '').'><a href="userlist.php">'.$lang_common['User list'].'</a></li>';


********************************************************************
#-------[ 9. After, Add ]

// Usermap by Gizzmo - START
	global $lang_usermap;
	if ($pun_user['g_um_view_map'] == '1')
		$links[] = '<li id="navusermap'.((PUN_ACTIVE_PAGE == 'usermap') ? ' class="isactive"': '').'"><a href="usermap.php">'.$lang_usermap['User map'].'</a></li>';
// Usermap by Gizzmo - END


********************************************************************
#-------[ 10. Find (line:~483) ]

				<ul>
					<li<?php if ($page == 'essentials') echo ' class="isactive"'; ?>><a href="profile.php?section=essentials&amp;id=<?php echo $id ?>"><?php echo $lang_profile['Section essentials'] ?></a></li>
					<li<?php if ($page == 'personal') echo ' class="isactive"'; ?>><a href="profile.php?section=personal&amp;id=<?php echo $id ?>"><?php echo $lang_profile['Section personal'] ?></a></li>



********************************************************************
#-------[ 11. After, Add ]

<?php
// Usermap by Gizzmo - START
	global $lang_usermap;
	if ($pun_user['g_um_add_to_map'] == '1')
		echo "\t\t\t\t\t".'<li'.($page == 'usermap'? ' class="isactive"': '').'><a href="profile.php?section=usermap&amp;id='.$id.'">'.$lang_usermap['User map'].'</a></li>'."\n";
// Usermap by Gizzmo - END
?>


********************************************************************
#-------[ 12. Open ]

	include/common.php


********************************************************************
#-------[ 13. Find (line:~175. The end of the file ) ]

if (!defined('PUN_SEARCH_MAX_WORD'))
	define('PUN_SEARCH_MAX_WORD', 20);


********************************************************************
#-------[ 14. After, Add ]

// Usermap by Gizzmo - START
// Need it here because its uesed in the main menu
if (file_exists(PUN_ROOT.'/usermap/lang/'.$pun_user['language'].'.php'))
	require PUN_ROOT.'usermap/lang/'.$pun_user['language'].'.php';
else
	require PUN_ROOT.'usermap/lang/English.php';
// Usermap by Gizzmo - END


********************************************************************
#-------[ 15. Save and Upload ]
