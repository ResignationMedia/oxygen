<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
?>
<?php if (count($items)): ?>
<ul id="activity-stream">
    <?php foreach ($items as $item): ?>
    <li>
        <div class="profile-photo">
            <a href="<?php echo $item['actor']['view_url']; ?>"><?php echo $item['actor']['avatar']; ?></a>
        </div>
        <div class="content">
            <?php echo View::factory($item['view'], $item); ?>
        </div>
        <div class="created">
            <?php echo Date::local($item['created'], 'Y-m-d \a\t g:ia'); ?>
        </div>
    </li>
    <?php endforeach; ?>
</ul>
<?php endif; ?>
