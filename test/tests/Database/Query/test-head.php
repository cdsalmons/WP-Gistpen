<?php

use WP_Gistpen\Database\Query\Head as Query;
use WP_Gistpen\Model\Zip;
use WP_Gistpen\Model\File;
use WP_Gistpen\Model\Language;

/**
 * @group database
 */
class WP_Gistpen_Database_Query_Test extends WP_Gistpen_UnitTestCase {

	public $query;

	function setUp() {
		parent::setUp();

		$this->create_post_and_children();
		$this->query = new Query( WP_Gistpen::$plugin_name, WP_Gistpen::$version );
	}

	function test_succeeeded_get_by_recent() {
		$results = $this->query->by_recent();

		$this->assertInternalType( 'array', $results );
		$this->assertCount( 4, $results );
	}

	function test_succeeeded_get_by_string() {
		$results = $this->query->by_string( '3' );

		$this->assertInternalType( 'array', $results );
		$this->assertCount( 1, $results );
	}

	function test_succeeded_get_language_by_post_id() {
		$result = $this->query->language_by_post_id( get_post( $this->files[0] )->ID );

		$this->assertInstanceOf( 'WP_Gistpen\Model\Language', $result );
	}

	function test_get_gist_id_by_post_id() {
		update_post_meta( $this->gistpen->ID, '_wpgp_gist_id', '12345' );

		$result = $this->query->gist_id_by_post_id( $this->gistpen->ID );

		$this->assertEquals( '12345', $result );
	}

	function test_succeeded_get_zip_by_post() {
		$post = $this->query->by_post( $this->gistpen );

		$this->assertInstanceOf( 'WP_Gistpen\Model\Zip', $post );
	}

	function test_succeeded_get_zip_by_id() {
		$post = $this->query->by_id( $this->gistpen->ID );

		$this->assertInstanceOf( 'WP_Gistpen\Model\Zip', $post );
	}

	function test_succeeded_get_file_by_post() {
		$file = $this->query->by_post( get_post( $this->files[0] ) );

		$this->assertInstanceOf( 'WP_Gistpen\Model\File', $file );
	}

	function test_succeeded_get_file_by_id() {
		$file = $this->query->by_id( $this->files[0] );

		$this->assertInstanceOf( 'WP_Gistpen\Model\File', $file );
	}

	function test_succeeeded_get_missing_gist_id() {
		$this->create_post_and_children();

		$ids = $this->query->missing_gist_id();

		$this->assertInternalType( 'array', $ids );
		$this->assertCount( 2, $ids );

		foreach ( $ids as $id ) {
			$this->assertInternalType( 'int', $id );
		}
	}

	function test_succeeeded_get_by_gist_id() {
		$this->create_post_and_children();

		update_post_meta( $this->gistpen->ID, '_wpgp_gist_id', '12345' );

		$zip = $this->query->by_gist_id( '12345' );

		$this->assertInstanceOf( 'WP_Gistpen\Model\Zip', $zip );
	}

	function tearDown() {
		parent::tearDown();
	}
}
