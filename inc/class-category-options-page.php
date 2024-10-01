<?php
if ( ! class_exists( 'Category_Options_Page' ) ) {
    class Category_Options_Page {
        private static $instance = null;

        // Private constructor to prevent multiple instances
        private function __construct() {
            // Hook to add the options page
            add_action( 'admin_menu', [ $this, 'add_options_page_category_set' ] );
            // Hook to register the settings
            add_action( 'admin_init', [ $this, 'register_settings' ] );

			add_action('rest_api_init', [ $this, 'rest_api_category_values' ] );
        }

        // Method to get the single instance of the class
        public static function get_instance() {
            if ( self::$instance == null ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        // Method to add the options page in the WordPress admin menu
        public function add_options_page_category_set() {
            add_options_page(
                'Category Options',
                'Category Options',
                'manage_options',
                'category-options',
                [ $this, 'create_category_page' ]
            );
        }

        // Method to register the settings
        public function register_settings() {
            // Register a new setting for each field with sanitization
            register_setting( 'category_options_group', 'category_post', [
                'sanitize_callback' => [ $this, 'sanitize_category_post' ]
            ]);
            register_setting( 'category_options_group', 'category_date', [
                'sanitize_callback' => [ $this, 'sanitize_date' ]
            ]);
            register_setting( 'category_options_group', 'category_limit', [
                'sanitize_callback' => [ $this, 'sanitize_limit' ]
            ]);

            // Add the section to hold the fields
            add_settings_section(
                'category_options_section',    // Section ID
                'Category Settings',           // Section title
                null,                          // Callback for description
                'category-options'             // Page slug where the section will be displayed
            );

            add_settings_field(
                'category_post',
                'Post Category',
                [ $this, 'category_post_field' ],
                'category-options',
                'category_options_section'
            );
            add_settings_field(
                'category_date',
                'Date',
                [ $this, 'category_date_field' ],
                'category-options',
                'category_options_section'
            );
            add_settings_field(
                'category_limit',
                'Limit',
                [ $this, 'category_limit_field' ],
                'category-options',
                'category_options_section'
            );
        }

        // Callback
        public function create_category_page() {
            ?>
            <div class="wrap">
                <h1>Category Options Page</h1>
                <form method="post" action="options.php">
                    <?php
                    settings_fields( 'category_options_group' );
                    do_settings_sections( 'category-options' );
                    submit_button();
                    ?>
                </form>
            </div>
            <?php
        }

        // Sanitization callback for 'category_post'
        public function sanitize_category_post( $input ) {
            $categories = get_categories( array( 'hide_empty' => false ) );
            $category_ids = wp_list_pluck( $categories, 'term_id' );

            // Check if the selected category ID is valid
            if ( in_array( intval( $input ), $category_ids, true ) ) {
                return intval( $input );
            }

            return ''; // Return empty if invalid
        }

        // Sanitization callback for 'category_date'
        public function sanitize_date( $input ) {
            $date = date_create( $input );
            if ( $date ) {
                return date_format( $date, 'Y-m-d' );
            }
            return ''; // Return empty if invalid date
        }

        // Sanitization callback for 'category_limit'
        public function sanitize_limit( $input ) {
            $limit = intval( $input );
            if ( $limit > 0 ) {
                return $limit;
            }
            return 5; // Default to 5 if invalid
        }

        // Callback
        public function category_post_field() {
            $selected_category = get_option( 'category_post', '' );
            $categories = get_categories( array( 'hide_empty' => false ) );
            ?>
            <select name="category_post">
                <option value="">Select a category</option>
                <?php
                foreach ( $categories as $category ) {
                    ?>
                    <option value="<?php echo esc_attr( $category->term_id ); ?>" <?php selected( $selected_category, $category->term_id ); ?>>
                        <?php echo esc_html( $category->name ); ?>
                    </option>
                    <?php
                }
                ?>
            </select>
            <?php
        }

        // Callback to render the 'Date' input
        public function category_date_field() {
            $value = get_option( 'category_date', '' );
            ?>
            <input type="date" name="category_date" value="<?php echo esc_attr( $value ); ?>" />
            <?php
        }

        // Callback to render the 'Limit' input with a default value of 5
        public function category_limit_field() {
            $value = get_option( 'category_limit', 5 ); // Default value is 5 if not set
            ?>
            <input type="number" name="category_limit" value="<?php echo esc_attr( $value ); ?>" min="1" />
            <?php
        }

		/**
		 * The function `rest_api_category_values` registers a custom REST route for fetching custom
		 * settings.
		 */
		public function rest_api_category_values() {
			register_rest_route('custom/v1', '/settings', [
				'methods' => 'GET',
				'callback' => [ $this, 'get_custom_settings' ],
				'permission_callback' => '__return_true',
			]);
		}

		public function get_custom_settings() {
			return [
				'category_post' => get_option('category_post', ''),
				'category_date' => get_option('category_date', ''),
				'category_limit' => get_option('category_limit', 5),
			];
		}

    }
    // Instantiate the class using the Singleton pattern
    Category_Options_Page::get_instance();
}
