<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<?php echo $messages; ?>

<p>Unable to write the configuration file automatically. Please create a <strong>config.php</strong> file in the root
of your application and then paste the following into it.</p>

<textarea rows="20" cols="62" name="file_content" id="file_content">
<?php foreach ($content as $line): ?>
<?php echo htmlentities($line, ENT_COMPAT, 'UTF-8'); ?>
<?php endforeach; ?>
</textarea>

<p>Once you have done the above you may continue.</p>

<?php foreach ($config_items as $name => $value): ?>
<?php echo Form::hidden($name, $value); ?>
<?php endforeach; ?>
