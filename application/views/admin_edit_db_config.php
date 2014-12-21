<form action="" id="db_config_form" method="post" autocomplete="off">
	<div class="message">&lt;0x5F3759DF&gt;: tread softly because you tread on my dreams<br />(a.k.a. don't fuck with the options that you don't understand)</div>
	<div class="dev">
		<div class="subtitle">dev</div>
		<label>hostname:</label><input name="__dev__hostname" type="text" value="<?php echo $db_config['dev']['hostname'];?>" autocomplete="off"><br />
		<label>username:</label><input name="__dev__username" type="text" value="<?php echo $db_config['dev']['username'];?>" autocomplete="off"><br />
		<label>password:</label><input name="__dev__password" type="password" value="<?php echo $db_config['dev']['password'];?>" autocomplete="off"><br />
		<label>database:</label><input name="__dev__database" type="text" value="<?php echo $db_config['dev']['database'];?>" autocomplete="off"><br />
		<label>dbdriver:</label><input name="__dev__dbdriver" type="text" value="<?php echo $db_config['dev']['dbdriver'];?>" autocomplete="off"><br />
		<label>dbprefix:</label><input name="__dev__dbprefix" type="text" value="<?php echo $db_config['dev']['dbprefix'];?>" autocomplete="off"><br />
		<input name="__dev__pconnect" type="hidden" value="off">
		<label>pconnect:</label><input name="__dev__pconnect" type="checkbox" <?php if($db_config['dev']['pconnect']) { echo 'checked="checked"'; } ?> autocomplete="off"><br />
		<input name="__dev__db_debug" type="hidden" value="off">
		<label>db_debug:</label><input name="__dev__db_debug" type="checkbox" <?php if($db_config['dev']['db_debug']) { echo 'checked="checked"'; } ?> autocomplete="off"><br />
		<input name="__dev__cache_on" type="hidden" value="off">
		<label>cache_on:</label><input name="__dev__cache_on" type="checkbox" <?php if($db_config['dev']['cache_on']) { echo 'checked="checked"'; } ?> autocomplete="off"><br />
		<label>cachedir:</label><input name="__dev__cachedir" type="text" value="<?php echo $db_config['dev']['cachedir'];?>" autocomplete="off"><br />
		<label>char_set:</label><input name="__dev__char_set" type="text" value="<?php echo $db_config['dev']['char_set'];?>" autocomplete="off"><br />
		<label>dbcollat:</label><input name="__dev__dbcollat" type="text" value="<?php echo $db_config['dev']['dbcollat'];?>" autocomplete="off"><br />
		<label>swap_pre:</label><input name="__dev__swap_pre" type="text" value="<?php echo $db_config['dev']['swap_pre'];?>" autocomplete="off"><br />
		<input name="__dev__autoinit" type="hidden" value="off">
		<label>autoinit:</label><input name="__dev__autoinit" type="checkbox" <?php if($db_config['dev']['autoinit']) { echo 'checked="checked"'; } ?> autocomplete="off"><br />
		<input name="__dev__stricton" type="hidden" value="off">
		<label>stricton:</label><input name="__dev__stricton" type="checkbox" <?php if($db_config['dev']['stricton']) { echo 'checked="checked"'; } ?> autocomplete="off"><br />
	</div>
	<div class="live">
		<div class="subtitle">live</div>
		<label>hostname:</label><input name="__live__hostname" type="text" value="<?php echo $db_config['live']['hostname'];?>" autocomplete="off"><br />
		<label>username:</label><input name="__live__username" type="text" value="<?php echo $db_config['live']['username'];?>" autocomplete="off"><br />
		<label>password:</label><input name="__live__password" type="password" value="<?php echo $db_config['live']['password'];?>" autocomplete="off"><br />
		<label>database:</label><input name="__live__database" type="text" value="<?php echo $db_config['live']['database'];?>" autocomplete="off"><br />
		<label>dbdriver:</label><input name="__live__dbdriver" type="text" value="<?php echo $db_config['live']['dbdriver'];?>" autocomplete="off"><br />
		<label>dbprefix:</label><input name="__live__dbprefix" type="text" value="<?php echo $db_config['live']['dbprefix'];?>" autocomplete="off"><br />
		<input name="__live__pconnect" type="hidden" value="off">
		<label>pconnect:</label><input name="__live__pconnect" type="checkbox" <?php if($db_config['live']['pconnect']) { echo 'checked="checked"'; } ?> autocomplete="off"><br />
		<input name="__live__db_debug" type="hidden" value="off">
		<label>db_debug:</label><input name="__live__db_debug" type="checkbox" <?php if($db_config['live']['db_debug']) { echo 'checked="checked"'; } ?> autocomplete="off"><br />
		<input name="__live__cache_on" type="hidden" value="off">
		<label>cache_on:</label><input name="__live__cache_on" type="checkbox" <?php if($db_config['live']['cache_on']) { echo 'checked="checked"'; } ?> autocomplete="off"><br />
		<label>cachedir:</label><input name="__live__cachedir" type="text" value="<?php echo $db_config['live']['cachedir'];?>" autocomplete="off"><br />
		<label>char_set:</label><input name="__live__char_set" type="text" value="<?php echo $db_config['live']['char_set'];?>" autocomplete="off"><br />
		<label>dbcollat:</label><input name="__live__dbcollat" type="text" value="<?php echo $db_config['live']['dbcollat'];?>" autocomplete="off"><br />
		<label>swap_pre:</label><input name="__live__swap_pre" type="text" value="<?php echo $db_config['live']['swap_pre'];?>" autocomplete="off"><br />
		<input name="__live__autoinit" type="hidden" value="off">
		<label>autoinit:</label><input name="__live__autoinit" type="checkbox" <?php if($db_config['live']['autoinit']) { echo 'checked="checked"'; } ?> autocomplete="off"><br />
		<input name="__live__stricton" type="hidden" value="off">
		<label>stricton:</label><input name="__live__stricton" type="checkbox" <?php if($db_config['live']['stricton']) { echo 'checked="checked"'; } ?> autocomplete="off"><br />
	</div>
	<input type="submit" value="Go">
</form>

<br />
<div id="reset_to_factory">
	<button class="empty">Nuke the database for great justice (reset to empty schema)</button><br />
	<button class="test-entries">Nuke the database for great justice (reset to empty schema but add a few test sites/pages/etc)</button><br /><br />
	<div class="message"></div>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		$(document).delegate('#reset_to_factory button', 'click', function(event) {
			var this_button = $(this)
			vms_dialogYesNo(
				this_button,
				function(element) {
					if(element.attr('class') == 'empty') {
						var post_url = '/login/resetDBToFactorySettings';
					}
					else {
						var post_url = '/login/resetDBToFactorySettings?ate=1';
					}
					$.post(post_url, {}, function(response) {
						$('#reset_to_factory .message').html(response);
					});
				},
				function(element) {
				},
				'<div>Are you sure?  This will destroy everything in the database and cannot be undone!</div>',
				'height:15em',
				'5em',
				'5em'
			);
		});
	});
</script>