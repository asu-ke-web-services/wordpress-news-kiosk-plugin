<?php
/*
Template Name: Posts Default Template
Description: Simple scaffolding to display the posts content and the sidebar.
*/

// Avoid direct calls to this file
if ( ! defined( 'KIOSK_WP_VERSION' ) ) {
  header( 'Status: 403 Forbidden' );
  header( 'HTTP/1.1 403 Forbidden' );
  exit();
}

get_header(); 

get_footer();