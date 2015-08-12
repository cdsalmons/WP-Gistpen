<?php
namespace Intraxia\Gistpen\Adapter;

use Intraxia\Gistpen\Model\Commit\Meta;

/**
 * This is the class description.
 *
 * @package    Intraxia\Gistpen
 * @author     James DiGioia <jamesorodig@gmail.com>
 * @link       http://jamesdigioia.dev/wp-gistpen/
 * @since      0.5.0
 */
class Gist {

	/**
	 * Converts a Zip into API array for Gist create endpoint
	 *
	 * @param  \Gistpen\Model\Commit\Meta $commit History to turn into API data
	 * @return array                      Gist data
	 */
	public function create_by_commit( $commit ) {

		$gist = array(
			'description' => $commit->get_description(),
		);

		$gist = $this->set_status( $gist, $commit );

		$states = $commit->get_states();
		$files = array();

		foreach ( $states as $state ) {
			$files[ $state->get_filename() ] = array( 'content' => $state->get_code() );
		}

		$gist['files'] = $files;

		return $gist;
	}

	/**
	 * Converts a Zip into API array for Gist edit endpoint
	 *
	 * @param  \Gistpen\Model\Commit\Meta $commit
	 * @return array      Gist data
	 */
	public function update_by_commit( $commit ) {
		$gist = array(
			'description' => $commit->get_description(),
		);

		$gist = $this->set_status( $gist, $commit );

		$states = $commit->get_states();
		$files = array();

		foreach ( $states as $state ) {
			switch ( $state->get_status() ) {
				case 'new':
					$files[ $state->get_filename() ] = array(
						'content' => $state->get_code(),
					);
					break;
				case 'updated':
					$files[ $state->get_gist_id() ] = array(
						'content'  => $state->get_code(),
						'filename' => $state->get_filename(),
					);
					break;
				case 'deleted':
					$files[ $state->get_gist_id() ] = null;
					break;
			}
		}

		$gist['files'] = $files;

		return $gist;
	}

	/**
	 * Sets the status on the Gist array based on
	 * the commit's status
	 *
	 * @param  array  $gist   Array of Gist API data
	 * @param  Meta   $commit Commit object
	 * @return array modified array of Gist API data
	 * @since  0.5.0
	 */
	protected function set_status( $gist, $commit ) {
		if ( 'publish' === $commit->get_status() ) {
			$gist['public'] = true;
		} else {
			$gist['public'] = false;
		}

		return $gist;
	}
}
