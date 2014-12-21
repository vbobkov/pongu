<form action="" id="delete_user_form" method="post" autocomplete="off">
	<div class="message"><?php echo $message; ?></div>
	<label for="login-user">User:</label><input id="login-user" name="username" type="text" value="<?php echo $username; ?>" autocomplete="off"><br />
	<input type="submit" value="Ok">
</form>