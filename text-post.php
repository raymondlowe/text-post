<?php
/* 
Plugin Name: Multi-File Text Upload with Post Status
Description: Allows the user to upload multiple .txt and .md files and create new posts from them with different post status.
Version: 2.2
*/

// Add a new admin menu item for the file upload
function mftu_add_menu_item() {
  add_submenu_page(
    'tools.php',
    'Multi-File Text Upload',
    'Multi-File Text Upload',
   'manage_options',
    'multi-file-text-upload',
    'mftu_handle_upload'
  );  
}
add_action( 'admin_menu', 'mftu_add_menu_item' );

// Handle the file upload
function mftu_handle_upload() {
  $num_posts = 0;

  if ( isset( $_FILES['txt_files'] ) ) {
    $uploaded_files = $_FILES['txt_files'];

    foreach ( $uploaded_files['name'] as $index => $file_name ) {
      $file_tmp_name = $uploaded_files['tmp_name'][$index];
      $file_extension = strtolower( pathinfo( $file_name, PATHINFO_EXTENSION ) );   

      if ( $file_extension === 'md' ) {
        $file_contents = file_get_contents( $file_tmp_name );

        // Convert markdown headings to HTML h tags
        $lines = explode( "\n", $file_contents );
        $parsed_content = '';
        
        // Remove the first line from the input file, which will be used as the headline
        $headline = trim( array_shift( $lines ) );  

        // Remove the second line if it is blank
        if ( trim( $lines[0] ) === '' ) {
          array_shift( $lines );
        }

        // Remove the third line if it matches the headline ignoring case and punctuation  
        $headline_cleaned = preg_replace('/\s+|[^A-Za-z0-9]/', '', strtolower($headline));    
        $third_line = isset($lines[0]) ? $lines[0] : '';
        $third_line_cleaned = preg_replace('/\s+|[^A-Za-z0-9]/', '', strtolower($third_line));
        if (preg_match('/\b' . preg_quote($headline_cleaned, '/') . '\b/', $third_line_cleaned)) {
          array_shift( $lines );
        }

        foreach ( $lines as $line ) {
          if ( preg_match( '/^\*+/', $line ) ) {
            // If the line starts with one or more star symbols, treat it as a heading
            $text = str_replace( '*', '', $line );
            $heading_level = min( strlen( $line ) - strlen( ltrim( $line, '*' ) ), 6 );
            $parsed_content .= '<h' . $heading_level . '>' . $text . '</h' . $heading_level . '>';
          } elseif ( strpos( $line, '#' ) === 0 ) {
            // If the line starts with one or more hash symbols, treat it as a heading
            $heading_level = min( substr_count( $line, '#' ), 6 );
            $text = trim( str_replace( '#', '', $line ) );
            $parsed_content .= '<h' . $heading_level . '>' . $text . '</h' . $heading_level. '>';
          } else {
            // Otherwise, treat the line as plain text
            $parsed_content .= $line . "\n";
          }
        }

        // Create a new post for the uploaded file
        $post_title = $headline;
        $post_content = $parsed_content;

        // Create a new post for the uploaded file
        $post_category = get_option( 'mftu_category', 0 );
        $post_author = $_POST['author'];
        $post_status = $_POST['post_status'];

        $post_id = wp_insert_post( array(
          'post_title' => $post_title,
          'post_content' => $post_content,
          'post_category' => array( $post_category ),
          'post_author' => $post_author,
          'post_status' => $post_status
        ) );

        if ( $post_id ) {
          $num_posts++;
        }
      } elseif ( $file_extension === 'txt' ) {
        // ...
      }
    }
  }

  // Display the success message
  if ( $num_posts > 0 ) {
    printf( '<div class="updated"><p>Successfully posted %d posts.</p></div>', $num_posts );
  }

  // Display the file upload form
  ?>
  <div class="wrap">
    <h1>Multi-File Text Upload</h1>
    <form method="post" enctype="multipart/form-data">
      <input type="file" name="txt_files[]" multiple>
      <br><br>
      <label for="category">Category:</label>
      <?php wp_dropdown_categories( array( 'name' => 'category', 'id' => 'mftu-category', 'selected' => get_option( 'mftu_category', 0 ) ) ); ?>
      <br><br>
      <label for="author">Author:</label>
      <?php wp_dropdown_users( array( 'name' => 'author' ) ); ?>
      <br><br> 
      <label for="post_status">Post Status:</label>
      <select name="post_status" id="post-status">
        <option value="draft">Draft</option>
        <option value="publish">Publish</option>
        <option value="private">Private</option>
      </select>
      <br><br>
      <?php submit_button( 'Upload Files' ); ?>
    </form>
  </div>
  <?php
}