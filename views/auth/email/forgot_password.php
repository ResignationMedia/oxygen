<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
Hello <?php echo $user['name']; ?>--

We have received a request to reset your password. To create a new password, please click this link:

<?php echo URL::site('new_password/'.$user['password_key']).PHP_EOL; ?>

If you do not want to reset your password, you can ignore this email.

Best regards,
<?php echo Oxygen::config('oxygen')->get('app_name').PHP_EOL; ?>

--
<?php echo URL::site().PHP_EOL; ?>
