<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
Hello <?php echo $user->name(); ?>--

You account is ready for you to use:

  Username: <?php echo $user->username.PHP_EOL; ?>
  Password: <?php echo $password.PHP_EOL; ?>

You can login here:

  <?php echo URL::site('login').PHP_EOL; ?>

To change your settings and/or managee your preferences, go to:

  <?php echo $user->url('site').PHP_EOL; ?>

If you forget your password, you can reset it here:

  <?php echo URL::site('forgot_password').PHP_EOL; ?>


Kindest regards,
<?php echo Oxygen::config('oxygen')->get('app_name').PHP_EOL; ?>

--
<?php echo URL::site().PHP_EOL; ?>
