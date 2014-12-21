<form action="" id="new_user_form" method="post" autocomplete="off">
	<div class="message"><?php echo $message; ?></div>
	<label for="login-user">New User:</label><input id="login-user" name="username" type="text" value="<?php echo $username; ?>" autocomplete="off"><br />
	<label for="login-user">Type:</label><input id="login-user" name="type" type="text" value="<?php echo $type; ?>" autocomplete="off"><br />
	<label for="login-user">First Name:</label><input id="login-user" name="fname" type="text" autocomplete="off"><br />
	<label for="login-user">Last Name:</label><input id="login-user" name="lname" type="text" autocomplete="off"><br />
	<label for="login-new-pass">New Pass:</label><input id="login-new-pass" name="password_new" type="password" value="" autocomplete="off"><br />
	<label for="login-new-pass-conf">Confirm<br />New Pass:</label><input id="login-new-pass-conf" name="password_new_confirmation" type="password" value="" autocomplete="off"><br />
	<input type="submit" value="Ok">
</form>