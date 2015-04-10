<?php

function insert_feature_image__and_kisok_end_date(){
  $numargs = func_num_args();
  if ( 7 != $numargs ){
      return 'Not valid input';
  }
  $arg_list           = func_get_args();
  $post_title         = $arg_list[0];
  $post_content       = $arg_list[1];
  $post_type          = $arg_list[2];
  $post_tags          = $arg_list[3];
  $kiosk_end_date     = $arg_list[4];
  $page_feature_image = $arg_list[5];
  $feature_image_path = $arg_list[6];
  $post = array(
   'post_title'    => $post_title,
   'post_name'     => $post_title,
   'post_content'  => $post_content,
   'post_type'     => $post_type,
   'tags_input'    => $post_tags,
  );
  $new_post_id = wp_insert_post( $post );
  wp_set_object_terms( $new_post_id, array( 'dummy_terms' ), 'dummy' );
  wp_publish_post( $new_post_id );
  $wp_filetype = wp_check_filetype( $feature_image_path, null );
  $attachment = array(
    'post_mime_type'  => $wp_filetype['type'],
    'post_title'      => $feature_image_path,
    'post_content'    => '',
    'post_status'     => 'inherit',
  );
  $attach_id = wp_insert_attachment( $attachment, $feature_image_path, $new_post_id );

  // you must first include the image.php file
  // for the function wp_generate_attachment_metadata() to work
  require_once( ABSPATH . 'wp-admin/includes/image.php' );
  $attach_data = wp_generate_attachment_metadata( $attach_id, $feature_image_path );
  wp_update_attachment_metadata( $attach_id, $attach_data );

  // add featured image to post
  add_post_meta( $new_post_id, '_thumbnail_id', $attach_id );
  add_post_meta( $new_post_id, 'kiosk-end-date', $kiosk_end_date );
  add_post_meta( $new_post_id, 'page_feature_image', $page_feature_image );
  $attach_id = wp_insert_attachment( $attachment, $feature_image_path, $new_post_id );

}
// Use date format d-m-y
insert_feature_image__and_kisok_end_date( 'Title 1', 'Content 1', 'post', 'Kiosk', '12-12-2016', '/wp-content/uploads/Desert.jpg', '/wp-content/uploads/Desert.jpg' );
insert_feature_image__and_kisok_end_date( 'Title 2', 'Content 2 <img src="/wp-content/uploads/Desert.jpg">', 'post', 'Kiosk', '30-04-2016', '', '' );
insert_feature_image__and_kisok_end_date( 'Title 3', 'Content 3', 'post', 'SSS', '30-04-2016', '/wp-content/uploads/Desert.jpg', '' );
insert_feature_image__and_kisok_end_date( 'Title 4', 'Content 4', 'post', 'Kiosk', '30-04-2016', '', '/wp-content/uploads/Desert.jpg', '' );