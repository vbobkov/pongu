<form action="" id="change_password_form" method="post" autocomplete="off">
	<div class="message"><?php echo $message; ?></div>
	<label for="login-user">User:</label><input id="login-user" name="username" type="text" value="<?php echo $username; ?>" autocomplete="off" <?php if($this->session->userdata('type') != 255) { echo 'disabled'; } ?>><br />
	<label for="login-old-pass">Old Pass:</label><input id="login-old-pass" name="password_old" type="password" value="" autocomplete="off"><br />
	<label for="login-new-pass">New Pass:</label><input id="login-new-pass" name="password_new" type="password" value="" autocomplete="off"><br />
	<label for="login-new-pass-conf">Confirm<br />New Pass:</label><input id="login-new-pass-conf" name="password_new_confirmation" type="password" value="" autocomplete="off"><br />
	<input type="submit" value="Ok">
</form>