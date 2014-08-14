<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 *
 * This is a compilation of the Activity Steams 1.0 Specification.
 * @link http://activitystrea.ms/
 */
return array(
	/**
	 * Specification Version
	 */
	'version' => '1.0',

	/**
	 * Base Verbs
	 * @link http://activitystrea.ms/schema/1.0/activity-schema-01.html#anchor4
	 */
	'verbs' => array(
		'favorite' => 'favorite',
		'follow' => 'follow',
		'like' => 'like',
		'make-friend' => 'make-friend',
		'join' => 'join',
		'play' => 'play',
		'post' => 'post',
		'save' => 'save',
		'share' => 'share',
		'tag' => 'tag',
		'update' => 'update',
	),

	/**
	 * Base Object Types
	 * @link http://activitystrea.ms/schema/1.0/activity-schema-01.html#anchor5
	 */
	'types' => array(
		'article' => 'article',
		'audio' => 'audio',
		'bookmark' => 'bookmark',
		'comment' => 'comment',
		'file' => 'file',
		'folder' => 'folder',
		'group' => 'group',
		'list' => 'list',
		'note' => 'note',
		'user' => 'user',
		'photo' => 'photo',
		'photo-album' => 'photo-album',
		'place' => 'place',
		'playlist' => 'playlist',
		'product' => 'product',
		'review' => 'review',
		'service' => 'service',
		'status' => 'status',
		'video' => 'video',
	),
);
