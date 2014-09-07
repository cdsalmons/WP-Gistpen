<?php
/**
 * @package   WP_Gistpen
 * @author    James DiGioia <jamesorodig@gmail.com>
 * @license   GPL-2.0+
 * @link      http://jamesdigioia.com/wp-gistpen/
 * @copyright 2014 James DiGioia
 */

/**
 * This class handles all of the AJAX responses
 *
 * @package WP_Gistpen_AJAX
 * @author  James DiGioia <jamesorodig@gmail.com>
 */
class WP_Gistpen_AJAX {

	/**
	 * Slug for the nonce field
	 *
	 * @var string
	 * @since  0.4.0
	 */
	public static $nonce_field = '_ajax_wp_gistpen';

	/**
	 * Embed the nonce in the head of the editor
	 *
	 * @return string    AJAX nonce
	 * @since  0.2.0
	 */
	public static function embed_nonce() {
		wp_nonce_field( self::$nonce_field, self::$nonce_field, false );
	}

	/**
	 * Checks nonce and user permissions for AJAX reqs
	 *
	 * @return Sends error and halts execution if anything doesn't check out
	 * @since  0.4.0
	 */
	public static function check_security() {
		// Check the nonce
		if ( !isset($_POST['nonce']) || !wp_verify_nonce( $_POST['nonce'], self::$nonce_field ) ) {
			wp_send_json_error( array( 'error' => __( "Nonce check failed.", WP_Gistpen::get_instance()->get_plugin_slug() ) ) );
		}

		// Check if user has proper permisissions
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'error' => __( "User doesn't have proper permisissions.", WP_Gistpen::get_instance()->get_plugin_slug() ) ) );
		}
	}

	/**
	 * Returns all the currently installed languages
	 *
	 * @return string JSON-encoded array of languages
	 * @since 0.4.0
	 */
	public static function get_gistpen_languages() {
		self::check_security();

		$terms = get_terms( 'language', 'hide_empty=0' );
		foreach ($terms as $term) {
			$languages[$term->slug] = $term->name;
		}

		$data = array( 'languages' => $languages );

		wp_send_json_success( $data );
	}

	/**
	 * Returns 5 most recent Gistpens
	 * or Gistpens matching search term
	 *
	 * @return string JSON-encoded array of post objects
	 * @since 0.4.0
	 */
	public static function get_gistpens() {
		self::check_security();

		$args = array(

			'post_type'      => 'gistpen',
			'post_status'    => 'publish',
			'order'          => 'DESC',
			'orderby'        => 'date',
			'posts_per_page' => 5,

		);

		if( isset( $_POST['gistpen_search_term'] ) && $_POST['gistpen_search_term'] !== null ) {
			$args['s'] = $_POST['gistpen_search_term'];
		}

		$recent_gistpens = get_posts( $args );

		$data = array( 'gistpens' => $recent_gistpens );

		wp_send_json_success( $data );
	}

	/**
	 * Responds to AJAX request to create new Gistpen
	 *
	 * @return string $post_id the id of the created Gistpen
	 * @since  0.2.0
	 */
	public static function create_gistpen() {
		self::check_security();

		$file_ids = WP_Gistpen_Saver::save_gistpen();
		$post_id = $file_ids[0];

		if( $post_id === 0 ) {
			wp_send_json_error(array( 'message' => __( "Failed to save Gistpen.", WP_Gistpen::get_instance()->get_plugin_slug() ) ) );
		}

		wp_send_json_success(array( 'id' => $post_id ) );
	}

	/**
	 * Saves the ACE editor theme to the user meta
	 *
	 * @since     0.4.0
	 */
	public static function save_ace_theme() {
		self::check_security();

		$result = update_user_meta( get_current_user_id(), '_wpgp_ace_theme', $_POST['theme'] );

		if ( ! $result ) {
			wp_send_json_error();
		}

		wp_send_json_success();
	}


	/**
	 * AJAX hook to get a new ACE editor
	 *
	 * @since     0.4.0
	 */
	public static function add_gistfile_editor() {
		self::check_security();

		$result = WP_Gistpen_Saver::save_gistfile();

		if( is_wp_error( $result ) ) {
			wp_send_json_error();
		}

		wp_send_json_success( array( 'id' => $result ) );
	}

	/**
	 * AJAX hook to delete an ACE editor
	 *
	 * @since     0.4.0
	 */
	public static function delete_gistfile_editor() {
		self::check_security();

		$result = wp_delete_post( $_POST['fileID'] );

		if( ! $result ) {
			wp_send_json_error();
		}

		wp_send_json_success();
	}
}