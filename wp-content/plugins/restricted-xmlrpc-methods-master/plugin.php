<?php
/*
Plugin Name: Restricted XML-RPC Methods
Plugin URI: https://github.com/jeremyfelt/restricted-xmlrpc-methods/
Description: Allow only the XML-RPC methods used by specific applications. (And Pingbacks)
Version: 0.0.1
Author: jeremyfelt
Author URI: https://jeremyfelt.com
License: GPLv2 or later
*/

add_filter( 'xmlrpc_methods', 'rxm_filter_xmlrpc_methods' );

/**
 * Filter the allowed methods handled by WordPress for XML-RPC requests.
 *
 * Restricts allowed methods to those that:
 *   - Are required by the WordPress Mobile Android application (Jetpack disabled)
 *   - Receive pingbacks and pingback URL lookups
 *
 * @since 0.0.1
 *
 * @return array An array of allowed XML-RPC methods.
 */
function rxm_filter_xmlrpc_methods() {
	return array(
		// WordPress API
		//'wp.getUsersBlogs'                 => 'this:wp_getUsersBlogs',
		'wp.newPost'                       => 'this:wp_newPost',
		'wp.editPost'                      => 'this:wp_editPost',
		'wp.deletePost'                    => 'this:wp_deletePost',
		'wp.getPost'                       => 'this:wp_getPost',
		'wp.getPosts'                      => 'this:wp_getPosts',
		'wp.newTerm'                       => 'this:wp_newTerm',
		//'wp.editTerm'                      => 'this:wp_editTerm',
		//'wp.deleteTerm'                    => 'this:wp_deleteTerm',
		'wp.getTerm'                       => 'this:wp_getTerm',
		'wp.getTerms'                      => 'this:wp_getTerms',
		//'wp.getTaxonomy'                   => 'this:wp_getTaxonomy',
		//'wp.getTaxonomies'                 => 'this:wp_getTaxonomies',
		//'wp.getUser'                       => 'this:wp_getUser',
		//'wp.getUsers'                      => 'this:wp_getUsers',
		'wp.getProfile'                    => 'this:wp_getProfile',
		//'wp.editProfile'                   => 'this:wp_editProfile',
		//'wp.getPage'                       => 'this:wp_getPage',
		//'wp.getPages'                      => 'this:wp_getPages',
		//'wp.newPage'                       => 'this:wp_newPage',
		//'wp.deletePage'                    => 'this:wp_deletePage',
		//'wp.editPage'                      => 'this:wp_editPage',
		//'wp.getPageList'                   => 'this:wp_getPageList',
		//'wp.getAuthors'                    => 'this:wp_getAuthors',
		//'wp.getCategories'                 => 'this:mw_getCategories',     // Alias
		//'wp.getTags'                       => 'this:wp_getTags',
		//'wp.newCategory'                   => 'this:wp_newCategory',
		//'wp.deleteCategory'                => 'this:wp_deleteCategory',
		//'wp.suggestCategories'             => 'this:wp_suggestCategories',
		//'wp.uploadFile'                    => 'this:mw_newMediaObject',    // Alias
		//'wp.deleteFile'                    => 'this:wp_deletePost',        // Alias
		//'wp.getCommentCount'               => 'this:wp_getCommentCount',
		//'wp.getPostStatusList'             => 'this:wp_getPostStatusList',
		//'wp.getPageStatusList'             => 'this:wp_getPageStatusList',
		//'wp.getPageTemplates'              => 'this:wp_getPageTemplates',
		//'wp.getOptions'                    => 'this:wp_getOptions',
		//'wp.setOptions'                    => 'this:wp_setOptions',
		//'wp.getComment'                    => 'this:wp_getComment',
		'wp.getComments'                   => 'this:wp_getComments',
		'wp.deleteComment'                 => 'this:wp_deleteComment',
		'wp.editComment'                   => 'this:wp_editComment',
		//'wp.newComment'                    => 'this:wp_newComment',
		//'wp.getCommentStatusList'          => 'this:wp_getCommentStatusList',
		//'wp.getMediaItem'                  => 'this:wp_getMediaItem',
		'wp.getMediaLibrary'               => 'this:wp_getMediaLibrary',
		'wp.getPostFormats'                => 'this:wp_getPostFormats',
		//'wp.getPostType'                   => 'this:wp_getPostType',
		//'wp.getPostTypes'                  => 'this:wp_getPostTypes',
		//'wp.getRevisions'                  => 'this:wp_getRevisions',
		//'wp.restoreRevision'               => 'this:wp_restoreRevision',

		// Blogger API
		'blogger.getUsersBlogs'            => 'this:blogger_getUsersBlogs',
		// 'blogger.getUserInfo'              => 'this:blogger_getUserInfo',
		// 'blogger.getPost'                  => 'this:blogger_getPost',
		// 'blogger.getRecentPosts'           => 'this:blogger_getRecentPosts',
		// 'blogger.newPost'                  => 'this:blogger_newPost',
		// 'blogger.editPost'                 => 'this:blogger_editPost',
		// 'blogger.deletePost'               => 'this:blogger_deletePost',

		// MetaWeblog API (with MT extensions to structs)
		//'metaWeblog.newPost'               => 'this:mw_newPost',
		//'metaWeblog.editPost'              => 'this:mw_editPost',
		//'metaWeblog.getPost'               => 'this:mw_getPost',
		//'metaWeblog.getRecentPosts'        => 'this:mw_getRecentPosts',
		//'metaWeblog.getCategories'         => 'this:mw_getCategories',
		'metaWeblog.newMediaObject'        => 'this:mw_newMediaObject',

		// MetaWeblog API aliases for Blogger API
		// see http://www.xmlrpc.com/stories/storyReader$2460
		//'metaWeblog.deletePost'            => 'this:blogger_deletePost',
		//'metaWeblog.getUsersBlogs'         => 'this:blogger_getUsersBlogs',

		// MovableType API
		//'mt.getCategoryList'               => 'this:mt_getCategoryList',
		//'mt.getRecentPostTitles'           => 'this:mt_getRecentPostTitles',
		//'mt.getPostCategories'             => 'this:mt_getPostCategories',
		//'mt.setPostCategories'             => 'this:mt_setPostCategories',
		//'mt.supportedMethods'              => 'this:mt_supportedMethods',
		//'mt.supportedTextFilters'          => 'this:mt_supportedTextFilters',
		//'mt.getTrackbackPings'             => 'this:mt_getTrackbackPings',
		//'mt.publishPost'                   => 'this:mt_publishPost',

		// PingBack
		'pingback.ping'                    => 'this:pingback_ping',
		'pingback.extensions.getPingbacks' => 'this:pingback_extensions_getPingbacks',

		//'demo.sayHello'                    => 'this:sayHello',
		//'demo.addTwoNumbers'               => 'this:addTwoNumbers',
	);
}
