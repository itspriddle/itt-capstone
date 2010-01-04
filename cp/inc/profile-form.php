		$edit_form = <<<EOT
<h2>Your Profile and Personal Options</h2>
<form name="profile" id="profile" action="" method="post">
	<input type="hidden" name="user_id" value="$user_id" />
	<fieldset>
	<legend>Name</legend>
	<p>
		<label>First name:<br />
		<input type="text" name="first_name" value="" />
		</label>
	</p>
	<p>
		<label>Last name:<br />
		<input type="text" name="last_name"  value="" />
		</label>
	</p>
	<p>
		<label>Nickname:<br />
		<input type="text" name="nickname" value="" />
		</label>
	</p>

	<!--
	<p>
		<label>Display name publicly as: <br />
		<select name="display_name">
			<option value="Josh">Josh</option>
			<option value="admin">admin</option>
			<option value="admin">admin</option>
			<option value="Josh">Josh</option>
			<option value="Priddle">Priddle</option>
			<option value="Josh Priddle">Josh Priddle</option>
			<option value="Priddle Josh">Priddle Josh</option>
		</select>
		</label>
	</p>
	-->

	</fieldset>
	<fieldset>
	<legend>Contact Info</legend>
	<p>
		<label>E-mail: (required)<br />
		<input type="text" name="email" value="$user_email" />
		</label>
	</p>

	<!--
	<p>
		<label>Website:<br />
		<input type="text" name="url" value="http://" />
		</label>
	</p>
	<p>
		<label>AIM:<br />
		<input type="text" name="aim" value="" />
		</label>
	</p>
	<p>
		<label>Yahoo IM:<br />
		<input type="text" name="yim" value="" />
		</label>
	</p>
	<p>
		<label>Jabber / Google Talk:
		<input type="text" name="jabber" value="" />
		</label>
	</p>
	-->

	</fieldset>

	<!--
	<fieldset>
	<legend>About yourself</legend>
	<p class="desc">Share a little biographical information to fill out your profile. This may be shown publicly.</p>
	<p>
		<textarea name="description" rows="5" cols="30"></textarea>
	</p>
	</fieldset>
	-->

	<fieldset>
	<legend>Update Your Password</legend>
	<p class="desc">If you would like to change your password type a new one twice below. Otherwise leave this blank.</p>
	<p>
		<label>New Password:<br />
		<input type="password" name="pass1" size="16" value="" />
		</label>
	</p>
	<p>
		<label>Type it one more time:<br />
		<input type="password" name="pass2" size="16" value="" />
		</label>
	</p>
	</fieldset>
	<p class="submit">
		<input type="submit" value="Update Profile &raquo;" name="submit" />
	</p>
</form>
EOT;
