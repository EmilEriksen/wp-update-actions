<?php

namespace WPUA;

/**
 * Runs a set of actions if the current version is greater than the stored version.
 *
 * Given a key, a current version and a set of actions, this class runs the
 * actions in order if the current version is greater than the version stored
 * with the key. Actions can be grouped by version.
 */
class Updater {
    /**
     * The key to the stored version.
     *
     * @var string
     */
    private $key;

    /**
     * The current version.
     *
     * @var string
     */
    private $current_version;

    /**
     * The set of actions to be run.
     *
     * @var array
     */
    private $actions;

    /**
     * Constructor.
     *
     * @param string $key The key to the stored version.
     * @param string $version The current version.
     */
    public function __construct( string $key, string $version ) {
        $this->key             = $key;
        $this->current_version = $version;
        $this->actions = [];
    }

    /**
     * Register an action.
     *
     * @param string $version The version.
     * @param array  $callbacks The update actions for the version.
     * @return void
     */
    public function register( string $version, array $callbacks ) {
        $this->actions[ $version ] = $callbacks;
    }

    /**
     * Run the updater and any actions and update the stored version.
     *
     * @return void
     */
    public function run() {
        $stored_version = $this->get_stored_version();

        foreach ( $this->actions as $version => $callbacks ) {
			if (
                ( 'install' === $version && empty( $stored_version ) ) ||
                ( version_compare( $stored_version, $version, '<' ) && ! empty( $stored_version ) )
            ) {
				foreach ( $callbacks as $callback ) {
					\call_user_func( $callback );
				}
			}
		}

        $this->update_stored_version();
    }

    /**
     * Get the stored version.
     *
     * @return string
     */
    private function get_stored_version() : string {
        return get_option( $this->key, '' );
    }

    /**
     * Update the stored version to the current version.
     *
     * @return void
     */
    private function update_stored_version() {
        \update_option( $this->key, $this->current_version );
    }
}
