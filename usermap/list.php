<?php

/**
 * Copyright (C) 2010 Justgizzmo.com
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

define('PUN_ROOT', './../');
require PUN_ROOT.'include/common.php';

// Load the profile.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/profile.php';

// do some checks first
if ($pun_user['g_read_board'] == '0')
	um_error($lang_common['No view']);

if ($pun_user['g_um_view_map'] == '0')
	um_error($lang_common['No permission']);

if (isset($_GET['id']))
{
	$id = intval($_GET['id']);

	if ($id < 2)
		um_error('invalid id');

	$extra_sql = ' u.id='.$id.' AND';
}
else
	$extra_sql = '';

$result = $db->query('SELECT u.*, g.g_um_icon FROM '.$db->prefix.'users AS u LEFT JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id WHERE'.$extra_sql.' u.um_lat IS NOT NULL AND u.um_lng IS NOT NULL AND g.g_um_add_to_map = \'1\' ORDER BY username ASC') or um_error('Unable to marker list.', __FILE__, __LINE__, $db->error());

$json = array();
while ($user = $db->fetch_assoc($result))
{
	if (isset($id) || isset($_GET['kml']))
	{
		if ($pun_config['o_avatars'] == '1')
		{
			$avatar_field = generate_avatar_markup($user['id']);

			if ($avatar_field == '')
				$avatar_field = $lang_profile['No avatar'];
		}

		if ($user['url'] != '')
		{
			$user['url'] = pun_htmlspecialchars($user['url']);

			if ($pun_config['o_censoring'] == '1')
				$user['url'] = censor_words($user['url']);

			$url = '<span class="website"><a href="'.$user['url'].'">'.$user['url'].'</a></span>';
		}
		else
			$url = $lang_profile['Unknown'];

		if ($user['signature'] != '')
		{
			require PUN_ROOT.'include/parser.php';
			$parsed_signature = parse_signature($user['signature']);
		}

		$posts_field = '';
		if ($pun_config['o_show_post_count'] == '1' || $pun_user['is_admmod'])
			$posts_field = forum_number_format($user['num_posts']);

		if ($pun_user['g_search'] == '1')
			$posts_field .= (($posts_field != '') ? ' - ' : '').'<a href="search.php?action=show_user&amp;user_id='.$id.'">'.$lang_profile['Show posts'].'</a>';

		$last_post = format_time($user['last_post']);

		// the html
		ob_start();
?>
<dl>
<?php if ($pun_config['o_avatars'] == '1'): ?>
	<dt><?php echo $lang_profile['Avatar'] ?></dt>
	<dd><?php echo $avatar_field ?></dd>
<?php endif; if ($pun_config['o_signatures'] == '1'): ?>
	<dt><?php echo $lang_profile['Signature'] ?></dt>
	<dd><?php echo isset($parsed_signature) ? '<div class="postsignature postmsg">'.$parsed_signature.'</div>' : $lang_profile['No sig']; ?></dd>
<?php endif;?>
	<dt><?php echo $lang_profile['Website'] ?></dt>
	<dd><?php echo $url ?></dd>
<?php if ($posts_field != ''): ?>
	<dt><?php echo $lang_common['Posts'] ?></dt>
	<dd><?php echo $posts_field ?></dd>
<?php endif; ?>
	<dt><?php echo $lang_common['Last post'] ?></dt>
	<dd><?php echo $last_post ?></dd>
	<dt><?php echo $lang_common['Registered'] ?></dt>
	<dd><?php echo format_time($user['registered'], true) ?></dd>
</dl>
<?php
		$html = str_replace(array("\t","\n"),'',trim(ob_get_contents()));
		ob_end_clean();
	}

	// json for the info window
	$json[]	= array(
		'id' 		=> $user['id'],
		'name'		=> $user['username'],
		'point'		=> array($user['um_lat'],$user['um_lng']),
		'icon'		=> $user['g_um_icon'],
		'html'		=> $html ? $html : ''
	);
}

if (isset($_GET['kml']))
{
	// header('') FIlename?
	header("Content-type: application/vnd.google-earth.kml+xml");
	header('Content-Disposition: attachment; filename="usermap.kml"');

	// xml kml header
	echo '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<kml xmlns="http://www.opengis.net/kml/2.2">'."\n".'<Document>'."\n";

	$saved_icons = array();
	foreach ($json as $cur)
	{
		$icon = substr($cur['icon'],0,-4);
		$saved_icons[$cur['icon']] = $icon;
?>
	<Placemark id='<?php echo $cur['id']?>'>
		<name><?php echo $cur['name']?></name>
		<styleUrl>#style_<?php echo $icon?></styleUrl>
		<description><![CDATA[<?php echo $cur['html']?>]]></description>
		<Point>
			<coordinates><?php echo $cur['point'][1].','.$cur['point'][0] ?>,0</coordinates>
		</Point>
	</Placemark>
<?php

	}

	foreach ($saved_icons as $file => $icon)
	{
?>
	<Style id="style_<?php echo $icon?>">
		<IconStyle>
			<Icon>
				<href><?php echo $pun_config['o_base_url'].'/usermap/img/icons/'.$file?></href>
			</Icon>
		</IconStyle>
	</Style>
<?php
	}

	// dump($saved_icons);

	// the footer
	echo '</Document>'."\n".'</kml>';

}
else
	echo json_encode($json);

function um_error($message, $file = null, $line = null, $db_error = false)
{
	echo json_encode(array(
		'error'		=> array(
			'msg'	=> $message,
			'file'	=> $file,
			'line'	=> $line,
			'db'	=> $db_error
		)
	));

	exit;
}

// for php > 5.2
if (!function_exists('json_encode'))
{
	function json_encode($a = false)
	{
		if (is_null($a)) return 'null';
		if ($a === false) return 'false';
		if ($a === true) return 'true';

		if (is_scalar($a))
		{
			if (is_float($a))
			{
				// Always use "." for floats.
				return floatval(str_replace(",", ".", strval($a)));
			}

			if (is_string($a))
			{
				static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
				return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
			}
			else
				return $a;
		}

		$isList = true;
		for ($i = 0, reset($a); $i < count($a); $i++, next($a))
		{
			if (key($a) !== $i)
			{
				$isList = false;
				break;
			}
		}

		$result = array();
		if ($isList)
		{
			foreach ($a as $v)
				$result[] = json_encode($v);

			return '[' . join(',', $result) . ']';
		}
		else
		{
			foreach ($a as $k => $v)
				$result[] = json_encode($k).':'.json_encode($v);
			return '{' . join(',', $result) . '}';
		}
	}
}