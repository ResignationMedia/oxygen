<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
$photo = $model->uploaded_photo_url('small');
?>
<div class="elm-block">
	<div style="float:left;margin-right:10px;width:75px">
		<img src="<?php echo Gravatar::url($model->email, '75'); ?>" width="75" height="75" alt="Gravatar" />
	</div>
	<div style="float:left;padding-top:20px">
		<input type="radio" name="photo_type" id="photo_type_gravatar" value="gravatar"<?php Form::checked('photo_type', 'gravatar', $model->photo_type, true); ?> />
		<label for="photo_type_gravatar">Use my Gravatar</label>
		<p>A Gravatar is an image you control that is attached to your email address.
		<a href="http://www.gravatar.com">Get or change your Gravatar</a>.</p>
	</div>
</div>
<div class="elm-block" style="clear:both;">
	<div style="float:left;margin:0 10px 25px 0;width:75px;text-align:right">
		<?php
			echo HTML::image((!empty($photo) ? $photo : OTheme::find_file('img', 'no-profile-photo-75', 'jpg')), array('alt' => 'Uploaded photo'));
		?>
	</div>
	<div style="float:left;padding-top:20px">
		<input type="radio" name="photo_type" id="photo_type_upload" value="upload"<?php Form::checked('photo_type', 'upload', $model->photo_type, true); ?> />
		<label for="photo_type_upload">Use Uploaded Photo</label>
		<p>This is an image you have uploaded yourself, if you wish to change this, use the form below.</p>
	</div>
</div>
<fieldset class="has-legend" style="clear:both">
	<legend>Upload New Photo</legend>
	<div class="elm-block pad-top-single">
		<label for="profile_photo" class="lbl-block">Upload a Photo</label>
		<input type="file" name="profile_photo" id="profile_photo" />
	</div>
</fieldset>
