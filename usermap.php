<?php

/**
 * Copyright (C) 2010 Justgizzmo.com
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

define('PUN_ROOT', './');
require PUN_ROOT.'include/common.php';

if ($pun_user['g_read_board'] == '0')
	message($lang_common['No view']);

if ($pun_user['g_um_view_map'] == '0')
	message($lang_common['No permission']);

$id = isset($_GET['id']) ? intval($_GET['id']) : null;

$page_head = array(
	'css'		=> '<link rel="stylesheet" type="text/css" media="screen" href="usermap/style.css" />',
	'jquery'	=> '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>',
	'googleapi'	=> '<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>',
	'jscript'	=> '<script type="text/javascript" src="usermap/script.js"></script>'."\n".
'<script type="text/javascript">
$(function(){
	UserMap.defaults = {
		latlng:  ['.$pun_config['o_um_default_lat'].','.$pun_config['o_um_default_lng'].'],
		zoom: '.$pun_config['o_um_default_zoom'].',
		height: '.$pun_config['o_um_height'].',
		fitzoom: '.$pun_config['o_um_fit_map'].'
	};'.(isset($id)? '
	UserMap.options = {id:'.$id.'};':'').'
	UserMap.main.init();
});
</script>'
);

$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), 'Usermap');
define('PUN_ACTIVE_PAGE', 'usermap');
require PUN_ROOT.'header.php';

?>
<div class='block2col'>
	<div class='blockmenu' id='usermap_userlist'>
		<h2><span><?php echo $lang_usermap['Userlist']?></span></h2>
		<div class='box'>
			<div class='inbox'>
				<span><?php echo $lang_usermap['No users']?></span>
			</div>
		</div>
	</div>
	<div class='block'>
		<h2><span><?php echo $lang_usermap['Usermap']?></span></h2>
		<div class='box' id='user_map_canvas'></div>
<?php if ($pun_user['g_id'] == PUN_ADMIN): ?>		<div class='box'>
			<div class='inbox'>
				<p><a id="um_admin" href="<?php echo $pun_config['o_base_url'].'/admin_loader.php?plugin=AP_Usermap_Settings.php&lat='.$pun_config['o_um_default_lat'].'&amp;lng='.$pun_config['o_um_default_lng'].'&amp;z='.$pun_config['o_um_default_zoom']?>">Save as default location</a> (this is here until i get a context menu built)</p>
			</div>
		</div>
<?php endif;?>	</div>
</div>
<?php

require PUN_ROOT.'footer.php';
