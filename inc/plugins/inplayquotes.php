<?php
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook("postbit", "inplayquotes_postbit");
$plugins->add_hook("misc_start", "inplayquotes_misc");
$plugins->add_hook("index_start", "inplayquotes_index");
$plugins->add_hook("admin_formcontainer_output_row", "inplayquotes_permission");
$plugins->add_hook("admin_user_groups_edit_commit", "inplayquotes_permission_commit");

function inplayquotes_info()
{
	return array(
		"name"		=> "Zitate auf dem Forenindex",
		"description"	=> "Erlaubt es Mitgliedern, Zitate aus Beiträgen einzufügen, die dann auf dem Index des Forums erscheinen.",
		"website"	=> "https://github.com/its-sparks-fly",
		"author"	=> "sparks fly",
		"authorsite"	=> "https://github.com/its-sparks-fly",
		"version"	=> "2.0",
		"compatibility" => "18*"
	);
}

function inplayquotes_install()
{
	global $db, $cache, $mybb;

	$setting_group = array(
	    'name' => 'inplayquotes',
	    'title' => 'Inplayzitate',
	    'description' => 'Einstellungen für die Inplayzitate',
	    'disporder' => 1,
	    'isdefault' => 0
	);

	$gid = $db->insert_query("settinggroups", $setting_group);

	$setting_array = array(
	    // A text setting
	    'inplay_id' => array(
	        'title' => 'ID der Inplay-Kategorie(n)',
	        'description' => 'Gib die ID der Inplay-Kategorie(n) an (Kategorie = das "höchste" Inplayforum, in dem sich alle anderen Inplayforen befinden) - mehrere Kategorien mit "," voneinander trennen!',
	        'optionscode' => 'text',
	        'value' => '', // Default
	        'disporder' => 1
	    ),
	);

	foreach($setting_array as $name => $setting)
	{
	    $setting['name'] = $name;
	    $setting['gid'] = $gid;

	    $db->insert_query('settings', $setting);
	}

	rebuild_settings();

	if(!$db->field_exists("canquoteinplay", "usergroups"))
	{
		switch($db->type)
		{
			case "pgsql":
				$db->add_column("usergroups", "canquoteinplay", "smallint NOT NULL default '1'");
				break;
			default:
				$db->add_column("usergroups", "canquoteinplay", "tinyint(1) NOT NULL default '1'");
				break;

		}
	}

	$cache->update_usergroups();
	
	$db->query("CREATE TABLE ".TABLE_PREFIX."inplayquotes (
		`qid` int(11) NOT NULL AUTO_INCREMENT,
		`uid` int(11) NOT NULL,
		`tid` int(11) NOT NULL,
		`pid` int(11) NOT NULL,
		`timestamp` int(21) NOT NULL,
		`quote` varchar(500) COLLATE utf8_general_ci NOT NULL,
		PRIMARY KEY (`qid`),
		KEY `qid` (`qid`)
		)
		ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1");


	rebuild_settings();

}

function inplayquotes_activate()
{
	global $db, $post;

	// needed for 1.0 => 2.0 update
	if(!$db->field_exists("timestamp", "inplayquotes"))
	{
		switch($db->type)
		{
			case "pgsql":
				$db->add_column("inplayquotes", "timestamp", "int(21) NOT NULL");
				break;
			default:
				$db->add_column("inplayquotes", "timestamp", "int(21) NOT NULL");
				break;
		}

		// add timestamp to quotes
		$query = $db->query("SELECT qid, pid FROM ".TABLE_PREFIX."inplayquotes");
		while($quote = $db->fetch_array($query)) {
			$post = get_post($quote['pid']);
			$insert_array = array(
				"timestamp" => $post['dateline']
			);
			$db->update_query("inplayquotes", $insert_array, "qid = '$quote[qid]'");
		}
	}

	include MYBB_ROOT."/inc/adminfunctions_templates.php";
	find_replace_templatesets("postbit_classic", "#".preg_quote('{$post[\'button_edit\']}')."#i", '{$post[\'inplayquotes\']}{$post[\'button_edit\']}');
	find_replace_templatesets("postbit", "#".preg_quote('{$post[\'button_edit\']}')."#i", '{$post[\'inplayquotes\']}{$post[\'button_edit\']}');
	find_replace_templatesets("index", "#".preg_quote('{$footer}')."#i", '{$inplayquotes}{$footer}');

	$insert_array = array(
		'title'		=> 'postbit_inplayquotes',
		'template'	=> $db->escape_string('<a href="misc.php?action=add_inplayquotes&pid={$post[\'pid\']}" title="{$lang->inplayquotes}" class="postbit_edit"><span>{$lang->inplayquotes_button}</span></a>'),
		'sid'		=> '-1',
		'version'	=> '',
		'dateline'	=> TIME_NOW
	);
	$db->insert_query("templates", $insert_array);

	$insert_array = array(
		'title'		=> 'misc_inplayquotes_add',
		'template'	=> $db->escape_string('<html>
<head>
<title>{$mybb->settings[\'bbname\']} - {$lang->inplayquotes_add}</title>
{$headerinclude}</head>
<body>
{$header}
    <table style="width: 80%; margin: auto;">
        <tr><td class="thead">{$lang->inplayquotes_add}</td><tr>
        <tr><td class="trow1">
<center>
  <form id="quotes" method="post" action="misc.php?action=add_inplayquotes&pid={$pid}">
{$insert_quote}
         <p>
                <textarea name="zitat" id="zitat" style="width: 300px; height: 100px;"></textarea>
            </p>
            <p>
               <input type="submit" name="submit" value="{$lang->inplayquotes_submit}" id="submit">
            </p>
        </form>
            </center></td></tr></table>

{$footer}
</body>
</html>'),
		'sid'		=> '-1',
		'version'	=> '',
		'dateline'	=> TIME_NOW
	);
	$db->insert_query("templates", $insert_array);

	$insert_array = array(
		'title'		=> 'misc_inplayquotes_overview',
		'template'	=> $db->escape_string('<html>
		<head>
		<title>{$mybb->settings[\'bbname\']} - {$lang->inplayquotes}</title>
		{$headerinclude}</head>
		<body>
		{$header}
			<table style="width: 90%; margin: auto;" class="tborder">
				<tr><td class="thead">{$lang->inplayquotes}</td><tr>
				<tr>
		<td class="trow2" style="padding: 10px; text-align: justify;">
		<div style="width: 95%; margin: auto; padding: 8px;  font-size: 12px; line-height: 1.5em;" class="trow1">
			<center><br />
				<form method="get" id="search_quotes">
					<input type="hidden" name="action" value="inplayquotes_overview" />
				<table style="width: 90%;" class="tborder" cellpadding="2" cellspacing="3">
					<tr><td class="thead" colspan="2">{$lang->inplayquotes_filter}</td></tr>
					<tr><td class="tcat">Charakter</td><td class="tcat">{$lang->inplayquotes_timespan}</td></tr>
					<tr align="center"><td class="trow1"><select name="user" id="users"><option value="">--- {$lang->inplayquotes_choose_character}</option>{$users_bit}</select></td><td class="trow1"><select name="date" id="date"><option value="">--- {$lang->inplayquotes_choose_timespan}</option>{$date_bit}</select></td>
					<tr align="center"><td class="trow1" colspan="2">
						<input type="submit" value="{$lang->inplayquotes_search}" /></td></tr>
				</table>
				</form>
			</center><br />
		{$inplayquotes_bit}
		</div>
		</td>
		</tr>
		</table>
		
		{$footer}
		</body>
		</html>'),
		'sid'		=> '-1',
		'version'	=> '',
		'dateline'	=> TIME_NOW
	);
	$db->insert_query("templates", $insert_array);

	$insert_array = array(
		'title'		=> 'misc_inplayquotes_overview_bit',
		'template'	=> $db->escape_string('<table class="tborder smalltext" style="width: 100%;" cellpadding="5" cellspacing="2">
		<tr>
			<td class="tcat" width="15%;">{$lang->inplayquotes_character}</td>
			<td class="tcat" width="15%">{$lang->inplayquotes_month}</td>
			<td class="tcat">{$lang->inplayquotes_quote}</td>
		</tr>
		<tr align="center">
			<td class="trow2">{$user[\'format_avatar\']}</td>
			<td class="trow2">{$date}</td>
			<td class="trow2"><div style="margin: auto; width: 90%; text-align: justify;">{$quote[\'quote\']}</div></td>
		</tr>
		<tr align="center" style="text-transform: uppercase; font-size: 8.5px;">
			<td class="trow1" colspan="2">{$user[\'username\']}</td>
			<td class="trow1">{$quote[\'thread\']}</td>
		</tr>
		{$delete_quote}
	</table>'),
		'sid'		=> '-1',
		'version'	=> '',
		'dateline'	=> TIME_NOW
	);
	$db->insert_query("templates", $insert_array);

	$insert_array = array(
		'title'		=> 'misc_inplayquotes_overview_bit_delete',
		'template'	=> $db->escape_string('<tr>
		<td colspan="3" class="trow2" align="right">
			<span style="text-transform: uppercase; font-size: 8px; letter-spacing: 2px;">
				<a href="misc.php?action=deletequote&qid={$quote[\'qid\']}">{$lang->inplayquotes_delete}</a>
			</span>
		</td>
	</tr>'),
		'sid'		=> '-1',
		'version'	=> '',
		'dateline'	=> TIME_NOW
	);
	$db->insert_query("templates", $insert_array);

	$insert_array = array(
		'title'		=> 'index_inplayquotes',
		'template'	=> $db->escape_string('<br /><table class="tborder" style="margin: auto;" cellpadding="10" cellspacing="1">
	<tr>
		<td class="thead">{$lang->inplayquotes_by} {$quoted[\'user\']} <a href="misc.php?action=inplayquotes_overview">{$lang->inplayquotes_overview}</a></td>
	</tr>
	<tr>
		<td align="center" class="trow2">{$quoted[\'quote\']}</td>
	</tr>
	<tr>
		<td><center><span class="smalltext">{$lang->inplayquotes_in}: {$quoted[\'scene\']}</span></center></td>
	</tr>
</table>
<br />'),
		'sid'		=> '-1',
		'version'	=> '',
		'dateline'	=> TIME_NOW
	);
	$db->insert_query("templates", $insert_array);
}

function inplayquotes_is_installed()
{
	global $db;
	if($db->table_exists('inplayquotes'))
	{
		return true;
	}
	return false;
}

function inplayquotes_uninstall()
{
	global $db, $cache;

	$db->delete_query('settings', "name IN ('inplay_id')");
	$db->delete_query('settinggroups', "name = 'inplayquotes'");

	rebuild_settings();

	if($db->field_exists("canquoteinplay", "usergroups"))
	{
    	$db->drop_column("usergroups", "canquoteinplay");
	}

  	$cache->update_usergroups();

	if($db->table_exists("inplayquotes"))
  	{
  		$db->drop_table("inplayquotes");
  	}

	rebuild_settings();
}

function inplayquotes_deactivate()
{
	global $db;

	include MYBB_ROOT."/inc/adminfunctions_templates.php";
	find_replace_templatesets("postbit_classic", "#".preg_quote('{$post[\'inplayquotes\']}')."#i", '', 0);
	find_replace_templatesets("postbit", "#".preg_quote('{$post[\'inplayquotes\']}')."#i", '', 0);
	find_replace_templatesets("index", "#".preg_quote('{$inplayquotes}')."#i", '', 0);

	$db->delete_query("templates", "title LIKE '%inplayquotes%'");
}

function inplayquotes_permission($above)
{
	global $mybb, $lang, $form;

	if($above['title'] == $lang->misc && $lang->misc)
	{
		$above['content'] .= "<div class=\"group_settings_bit\">".$form->generate_check_box("canquoteinplay", 1, "Kann aus dem Inplay zitieren?", array("checked" => $mybb->input['canquoteinplay']))."</div>";
	}

	return $above;
}

function inplayquotes_permission_commit()
{
	global $mybb, $updated_group;
	$updated_group['canquoteinplay'] = $mybb->get_input('canquoteinplay', MyBB::INPUT_INT);
}

function inplayquotes_postbit(&$post)
{
	global $lang, $templates, $db, $mybb, $forum;
	$lang->load('inplayquotes');

	// insert inplayquote button to inplay boards
	$quote_forums = $db->fetch_field($db->query("SELECT value FROM ".TABLE_PREFIX."settings WHERE name = 'inplay_id'"), "value");
	$quote_forums = explode(",", $quote_forums);
	$forum['parentlist'] = ",".$forum['parentlist'].",";
	foreach($quote_forums as $quote_forum) {
		if(!empty($quote_forum)) {
			if(preg_match("/,{$quote_forum},/i", $forum['parentlist'])) {
		$post['inplayquotes'] = eval($templates->render("postbit_inplayquotes"));
		return $post;
			}
		}
	}
}

function inplayquotes_misc()
{
	global $lang, $db, $mybb, $templates, $theme, $headerinclude, $header, $footer;
	$lang->load('inplayquotes');

	$mybb->input['action'] = $mybb->get_input('action');

	// Inplayzitat eintragen
	if($mybb->input['action'] == "add_inplayquotes")
	{
		if($mybb->usergroup['canquoteinplay'] != 1) {
			error_no_permission();
		}

		$pid = $mybb->input['pid'];
		if(isset($_POST['submit'])) {
			$tid = $db->fetch_field($db->query("SELECT tid from ".TABLE_PREFIX."posts WHERE pid = '$pid'"), "tid");
			$quote = $_POST['zitat'];
			$uid = $db->fetch_field($db->query("SELECT uid from ".TABLE_PREFIX."posts WHERE pid = '$pid'"), "uid");
			$new_record = array(
				"uid" => $uid,
				"tid" => $tid,
				"pid" => $pid,
				"timestamp" => TIME_NOW,
				"quote" => $db->escape_string($quote)
			);
			$insert_array = $db->insert_query("inplayquotes", $new_record);
			$insert_quote = "<div class=\"pm_alert\">{$lang->inplayquotes_success}</div>";
		}

		
		$query = $db->query("SELECT username, subject FROM ".TABLE_PREFIX."posts
		WHERE ".TABLE_PREFIX."posts.pid = '$pid'");
		$quoted = $db->fetch_array($query);
		$quotename = $quoted['username'];
		$quotethread = $quoted['subject'];
		if(!isset($_POST['submit'])) {
			$insert_quote = "<center>{$lang->inplayquotes_scene}: <strong>{$quotethread}</strong><br /> {$lang->inplayquotes_by}: <strong>{$quotename}</strong></center>";
		}
		eval("\$inplayquotes = \"".$templates->get("misc_inplayquotes_add")."\";");
		output_page($inplayquotes);		  
	}

	if($mybb->input['action'] == "inplayquotes_overview")
	{
		if(!$mybb->user['uid']) {
			error_no_permission();
		}

		// get username dropdown
		$query = $db->query("SELECT DISTINCT ".TABLE_PREFIX."inplayquotes.uid FROM ".TABLE_PREFIX."inplayquotes 
		LEFT JOIN ".TABLE_PREFIX."users 
		ON 		".TABLE_PREFIX."inplayquotes.uid = ".TABLE_PREFIX."users.uid 
		WHERE ".TABLE_PREFIX."inplayquotes.uid IN(SELECT uid FROM ".TABLE_PREFIX."users) 
		ORDER BY username ASC");	
		while($users = $db->fetch_array($query)) {
			$user = get_user($users['uid']);
			$users_bit .= "<option value=\"{$users['uid']}\">{$user['username']}</option>";
		}
		
		// get timestamp dropdown
		$query = $db->query("SELECT DISTINCT from_unixtime(timestamp, '%M %Y') AS quotedate FROM ".TABLE_PREFIX."inplayquotes ORDER by timestamp ASC");
		while($months = $db->fetch_array($query)) {
			$date_bit .= "<option value=\"{$months['quotedate']}\">{$months['quotedate']}</option>";
		}
		
		$quser = $mybb->input['user'];
		if(empty($quser)) {
			$quser = "%";
		}

		$qdate = $mybb->input['date'];
		
		// get quotes fitting filters
		$query = $db->query("SELECT * FROM ".TABLE_PREFIX."inplayquotes 
		WHERE uid LIKE '$quser' 
		AND uid IN(SELECT uid FROM ".TABLE_PREFIX."users) 
		ORDER BY qid DESC");

		while($quote = $db->fetch_array($query)) {
			$date = date('F Y', $quote['timestamp']);
			$post = get_post($quote['pid']);
			$thread = get_thread($post['tid']);
			$user = get_user($quote['uid']);

			// let delete quotes
			if($mybb->usergroup['cancp'] == 1 OR $mybb->user['uid'] == $user['uid']) {
				eval("\$delete_quote = \"".$templates->get("misc_inplayquotes_overview_bit_delete")."\";");
			}

			$user['format_avatar'] = "<img src=\"$user[avatar]\" style=\"width: 50px;\" / >";
			$user['username'] = format_name($user['username'], $user['usergroup'], $user['displaygroup']);
			$user['username'] = build_profile_link($user['username'], $user['uid']);
			$quote['thread'] = "<strong>{$lang->inplayquotes_in}:</strong> <a href=\"showthread.php?tid={$post[tid]}&pid={$quote[pid]}#pid{$quote[pid]}\">$thread[subject]</a>";
			if($date == $qdate OR empty($qdate)) {
				eval("\$inplayquotes_bit .= \"".$templates->get("misc_inplayquotes_overview_bit")."\";");
			}
		}		
		eval("\$inplayquotes = \"".$templates->get("misc_inplayquotes_overview")."\";");
		output_page($inplayquotes);
	}

	if($mybb->input['action'] == "deletequote") {
		$qid = (int)$mybb->get_input('qid');
		$uid = $db->fetch_field($db->query("SELECT uid FROM ".TABLE_PREFIX."inplayquotes WHERE qid = '$qid'"), "uid");
		if($mybb->usergroup['cancp'] == 1 OR $mybb->user['uid'] == $uid) {
			$db->delete_query("inplayquotes", "qid = '$qid'");
		}
		redirect("index.php", $lang->inplayquotes_deleted);
	}
}

function inplayquotes_index()
{
	global $lang, $db, $mybb, $templates, $inplayquotes, $quoted;
	$lang->load('inplayquotes');

	$query = $db->query("SELECT * FROM ".TABLE_PREFIX."inplayquotes
	LEFT JOIN ".TABLE_PREFIX."posts on ".TABLE_PREFIX."inplayquotes.pid = ".TABLE_PREFIX."posts.pid
	WHERE ".TABLE_PREFIX."inplayquotes.uid IN(SELECT uid FROM ".TABLE_PREFIX."users) 	
	ORDER BY rand()
	LIMIT 1");
	$quoted = $db->fetch_array($query);
	$quoted['user'] = build_profile_link($quoted['username'], $quoted['uid']);
	$quoted['scene']= "<a href=\"showthread.php?tid={$quoted[tid]}&pid={$quoted[pid]}#pid{$quoted[pid]}\">$quoted[subject]</a>";
	if(!empty($quoted['quote'])) {
		eval("\$inplayquotes = \"".$templates->get("index_inplayquotes")."\";");
	}
}
?>
