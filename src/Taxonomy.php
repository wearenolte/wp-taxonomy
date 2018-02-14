<?php

namespace Lean;

/**
 * Class Taxonomy
 *
 * @package Lean
 */
class Taxonomy {

	/**
	 * Name of the taxonomy.
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * Single name of the taxonomy.
	 *
	 * @var string
	 */
	protected $singular = '';

	/**
	 * Plural name of the taxonomy.
	 *
	 * @var string
	 *
	 */
	protected $plural = '';

	/**
	 * Other Arguments for taxonomy found on
	 * https://codex.wordpress.org/Function_Reference/register_taxonomy
	 *
	 * @var array
	 */
	protected $args = [];

	/**
	 * Objects that will be associated to taxonomy.
	 *
	 * @var array
	 */
	protected $objects = [];


	/**
	 * PHP5 Constructor
	 *
	 * @since 0.1.0
	 *
	 * @param array $options {.
	 *     @type string post_type The name for Taxonomy.
	 *     @type string singular Singular name of the Taxonomy.
	 *     @type string plural Plural name of the Taxonomy.
	 *     @type string slug The slug of the Taxonomy.
	 * }
	 */
	public function __construct( $options = array() ) {
		if ( ! is_array( $options ) ) {
			return;
		}

		// Set dynamic values to each instance variable.
		$values = array( 'name', 'singular', 'plural', 'args', 'objects' );
		foreach ( $values as $value ) {
			if ( array_key_exists( $value, $options ) ) {
				$this->$value = $options[ $value ];
			}
		}

		$this->set_default_labels();
		$this->set_default_rewrite();
		$this->set_default_args();

		if ( isset( $options['args'] ) ) {
			$this->args = wp_parse_args( $options['args'], $this->args );
		}
	}

	/**
	 * Register the Taxonomy on the init action.
	 *
	 * @since 0.1.0
	 */
	public function register() {
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Function that register the Taxonomy first tests if there is no such Taxonomy on
	 * the site.
	 *
	 * @since 0.1.0
	 */
	public function init() {
		if ( ! taxonomy_exists( $this->name ) ) {
			if ( empty( $this->objects ) ) {
				return new \WP_Error( 'missing objects', __( 'You are missing objects to associate the taxonomy to', 'lean' ) );
			}
			register_taxonomy( $this->name, $this->objects, $this->args );
		}
	}

	/**
	 * Creates the default group of arguments, all of the arguments can be
	 * overwritten by calling set_args, function with an instance of this object,
	 * the value is stored in the $args variable.
	 *
	 * @since 0.1.0
	 */
	private function set_default_args() {
		$this->set_args(array(
			// The array of labels to use in the UI for this post type.
			'labels' => $this->labels,
			// We use the query var 'store' as opposed to the post type 'acf-store'.
			'query_var' => strtolower( $this->singular ),
			// Triggers the handling of re-writes for this post-type.
			'rewrite' => $this->rewrite,
		));
	}

	/**
	 * Allows to overwrite any of the default arguments for this Taxonomy, just
	 * send an associate array with the value you want to update.
	 *
	 * @since 0.1.0
	 *
	 * @param array $args The arguments to replace.
	 */
	public function set_args( $args ) {
		$this->merge( $this->args, $args );
	}

	/**
	 * Allows to overwrite any of the default labels for this Taxonomy, just
	 * send an associate array with the value you want to update.
	 *
	 * @since 0.1.0
	 *
	 * @param array $labels The group of labels to update.
	 */
	public function set_labels( $labels ) {
		$this->merge( $this->labels, $labels );
		$this->update_arg( 'labels', $this->labels );
	}

	/**
	 * Overwrites the default variable by mergin the default values with
	 * the new ones if the new values are empty the default values keep as they
	 * are.
	 *
	 * @since 0.1.0
	 *
	 * @param mixed $default Reference to the original values.
	 * @param array $new_values The array with the new values to be updated on
	 *							the default variable.
	 */
	public function merge( &$default, $new_values ) {
		if ( is_array( $new_values ) && ! empty( $new_values ) ) {
			$default = array_merge( $default, $new_values );
		}
	}

	/**
	 * Creates the default labels to be used with this Taxonomy.
	 *
	 * @since 0.10
	 */
	private function set_default_labels() {
		$this->labels = array(
			'name' => $this->interpolate( '%s', $this->plural ),
			'singular_name' => $this->interpolate( '%s', $this->singular ),
			'menu_name' => $this->interpolate( '%s', $this->plural ),
			'all_items' => $this->interpolate( 'All %s', $this->plural ),
			'edit_item' => $this->interpolate( 'Edit %s', $this->singular ),
			'view_item' => $this->interpolate( 'View %s', $this->singular ),
			'update_item' => $this->interpolate( 'Update %s', $this->singular ),
			'add_new_item' => $this->interpolate( 'Add New %s', $this->singular ),
			'new_item_name' => $this->interpolate( 'New %s', $this->singular ),
			'parent_item' => $this->interpolate( 'Parent %s', $this->singular ),
			'parent_item_colon' => $this->interpolate( 'Parent %s:', $this->singular ),
			'search_items' => $this->interpolate( 'Search %s', $this->plural ),
			'popular_items' => $this->interpolate( 'Popular %s', $this->plural ),
			'separate_items_with_commas' => $this->interpolate( 'Separate %s with commas', $this->plural ),
			'add_or_remove_items' => $this->interpolate( 'Add or remove %s', $this->plural ),
			'choose_from_most_used' => $this->interpolate( 'Choose from most used', $this->plural ),
			'not_found' => $this->interpolate( 'No %s found.', $this->plural ),
		);
	}

	/**
	 * Uses the sprintf function to create an interpolation of the message and
	 * arguments.
	 *
	 * @since 0.1.0
	 *
	 * @param string $msg The message to be displayed.
	 * @param string $arg The argument to replace insode of the $message.
	 * @return string The message with the interpolation.
	 */
	private function interpolate( $msg = '', $arg = '' ) {
		return $this->label( sprintf( $msg, $arg ) );
	}

	/**
	 * Creates an escaped label
	 *
	 * @since 0.1.0
	 *
	 * @param string $str The string to be used in the label.
	 * @return string The escpaed and translated label.
	 */
	private function label( $str = '' ) {
		if ( is_string( $str ) && ! empty( $str ) ) {
			return esc_html__( $str , 'Lean' );
		} else {
			return '';
		}
	}

	/**
	 * Set default options for the rewrite of the Taxonomy.
	 *
	 * @since 0.1.0
	 */
	private function set_default_rewrite() {
		$this->rewrite = array(
			// Customize the permalink structure slug. Should be translatable.
			'slug' => $this->interpolate( '%s', $this->slug ),

			/*
			 * Do not prepend the front base to the permalink structure.
			 *
			 * For example, if your permalink structure is /blog/, then your links will be:
			 * false->/news/, true->/blog/news/
			 */
			'with_front' => false,
		);
	}

	/**
	 * Updates the default options in the $rewrite variable.
	 *
	 * @since 0.1.0
	 *
	 * @param array $rules The associate array with the new rules.
	 */
	public function set_rewrite( $rules ) {
		$this->merge( $this->rewrite, $rules );
		$this->update_arg( 'rewrite', $this->rewrite );
	}

	/**
	 * Update the args with the latest changes.
	 *
	 * @since 0.1.0
	 *
	 * @param string $name The name of the key to be updated in the $args.
	 * @param mixed  $value The value to be stored inside of $args[ $name ].
	 */
	private function update_arg( $name = '', $value = '' ) {
		if ( ! empty( $name ) ) {
			$this->set_args( array(
				$name => $value,
			));
		}
	}
}