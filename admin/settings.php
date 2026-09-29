<?php
// Sanitize options: convert display_on array to comma-separated string.
function stpb_sanitize_options( $input ) {
    if ( isset( $input['display_on'] ) && is_array( $input['display_on'] ) ) {
        $input['display_on'] = implode( ',', $input['display_on'] );
    }
    
    // Sanitize numeric values
    $numeric_fields = array( 'height', 'heading_text_size', 'mobile_breakpoint', 'animation_speed', 'z_index' );
    foreach ( $numeric_fields as $field ) {
        if ( isset( $input[$field] ) ) {
            $input[$field] = absint( $input[$field] );
        }
    }
    
    // Sanitize opacity
    if ( isset( $input['opacity'] ) ) {
        $input['opacity'] = floatval( $input['opacity'] );
        if ( $input['opacity'] < 0 ) $input['opacity'] = 0;
        if ( $input['opacity'] > 1 ) $input['opacity'] = 1;
    }
    
    // Sanitize boolean values
    $boolean_fields = array( 'show_on_mobile', 'show_headings_dropdown', 'exclude_sidebar_footer' );
    foreach ( $boolean_fields as $field ) {
        $input[$field] = isset( $input[$field] ) ? true : false;
    }
    
    return $input;
}

function stpb_register_settings() {
    register_setting( 'stpb_settings_group', 'stpb_options', 'stpb_sanitize_options' );
}
add_action( 'admin_init', 'stpb_register_settings' );

function stpb_register_menu() {
    add_menu_page(
        'Scroll Progress Bar',
        'Scroll Progress Bar',
        'manage_options',
        'stpb-settings',
        'stpb_settings_page',
        'dashicons-chart-line'
    );
}
add_action( 'admin_menu', 'stpb_register_menu' );

function stpb_settings_page() {
    $options = get_option( 'stpb_options' );
    ?>
    <div class="wrap">
        <h1>Scroll Title Progress Bar</h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'stpb_settings_group' ); ?>
            
            <div class="stpb-settings-container">
                <!-- Display Settings -->
                <div class="stpb-settings-section">
                    <h2><span class="dashicons dashicons-visibility"></span> Display Settings</h2>
                    <div class="stpb-settings-content">
                        <table class="form-table">
                            <tr>
                                <th>Display On:</th>
                                <td>
                                    <?php
                                    $display_on = isset( $options['display_on'] ) ? $options['display_on'] : 'post,page';
                                    $selected = explode( ',', $display_on );
                                    $post_types = get_post_types( array( 'public' => true ), 'objects' );
                                    foreach ( $post_types as $post_type ) {
                                        ?>
                                        <label class="stpb-checkbox-label">
                                            <input type="checkbox" name="stpb_options[display_on][]" value="<?php echo esc_attr( $post_type->name ); ?>" <?php echo in_array( $post_type->name, $selected ) ? 'checked' : ''; ?> /> 
                                            <?php echo esc_html( $post_type->label ); ?>
                                        </label>
                                        <?php
                                    }
                                    ?>
                                    <p class="description">Select where to display the progress bar.</p>
                                </td>
                            </tr>
                            <tr>
                                <th>Position:</th>
                                <td>
                                    <select name="stpb_options[position]" class="stpb-select">
                                        <option value="top" <?php selected( isset( $options['position'] ) ? $options['position'] : 'top', 'top' ); ?>>Top</option>
                                        <option value="bottom" <?php selected( isset( $options['position'] ) ? $options['position'] : 'top', 'bottom' ); ?>>Bottom</option>
                                    </select>
                                    <p class="description">Choose where to display the progress bar.</p>
                                </td>
                            </tr>
                            <tr>
                                <th>Z-Index:</th>
                                <td>
                                    <input type="number" name="stpb_options[z_index]" value="<?php echo esc_attr( isset( $options['z_index'] ) ? $options['z_index'] : '9999' ); ?>" class="stpb-number" />
                                    <p class="description">Set the stacking order of the progress bar.</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Content Settings -->
                <div class="stpb-settings-section">
                    <h2><span class="dashicons dashicons-editor-textcolor"></span> Content Settings</h2>
                    <div class="stpb-settings-content">
                        <table class="form-table">
                            <tr>
                                <th>Headings (comma separated):</th>
                                <td>
                                    <input type="text" name="stpb_options[headings]" value="<?php echo esc_attr( isset( $options['headings'] ) ? $options['headings'] : 'h2,h3' ); ?>" class="stpb-text" />
                                    <p class="description">Specify the HTML tags to read headings from. (Default: h2,h3)</p>
                                </td>
                            </tr>
                            <tr>
                                <th>Exclude Page IDs (comma separated):</th>
                                <td>
                                    <input type="text" name="stpb_options[exclude_ids]" value="<?php echo esc_attr( isset( $options['exclude_ids'] ) ? $options['exclude_ids'] : '' ); ?>" class="stpb-text" />
                                    <p class="description">Enter page IDs to exclude from display, separated by commas.</p>
                                </td>
                            </tr>
                            <tr>
                                <th>Custom Conditions:</th>
                                <td>
                                    <textarea name="stpb_options[custom_conditions]" rows="3" cols="50" class="stpb-textarea"><?php echo esc_textarea( isset( $options['custom_conditions'] ) ? $options['custom_conditions'] : '' ); ?></textarea>
                                    <p class="description">Enter custom PHP conditions to control when the progress bar should display. Example: is_user_logged_in() && !is_admin()</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Headings Dropdown Settings -->
                <div class="stpb-settings-section">
                    <h2><span class="dashicons dashicons-menu"></span> Headings Dropdown Settings</h2>
                    <div class="stpb-settings-content">
                        <table class="form-table">
                            <tr>
                                <th>Show Headings Dropdown:</th>
                                <td>
                                    <label class="stpb-switch">
                                        <input type="checkbox" name="stpb_options[show_headings_dropdown]" value="1" <?php checked( isset( $options['show_headings_dropdown'] ) ? $options['show_headings_dropdown'] : true ); ?> />
                                        <span class="slider"></span>
                                    </label>
                                    <p class="description">Enable or disable the headings dropdown menu.</p>
                                </td>
                            </tr>
                            <tr>
                                <th>Exclude Sidebar & Footer:</th>
                                <td>
                                    <label class="stpb-switch">
                                        <input type="checkbox" name="stpb_options[exclude_sidebar_footer]" value="1" <?php checked( isset( $options['exclude_sidebar_footer'] ) ? $options['exclude_sidebar_footer'] : true ); ?> />
                                        <span class="slider"></span>
                                    </label>
                                    <p class="description">Exclude headings from sidebar and footer areas.</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Appearance Settings -->
                <div class="stpb-settings-section">
                    <h2><span class="dashicons dashicons-admin-appearance"></span> Appearance Settings</h2>
                    <div class="stpb-settings-content">
                        <table class="form-table">
                            <tr>
                                <th>Bar Color:</th>
                                <td>
                                    <input type="color" name="stpb_options[color]" value="<?php echo esc_attr( isset( $options['color'] ) ? $options['color'] : '#ff5722' ); ?>" class="stpb-color" />
                                </td>
                            </tr>
                            <tr>
                                <th>Bar Height (px):</th>
                                <td>
                                    <input type="number" name="stpb_options[height]" value="<?php echo esc_attr( isset( $options['height'] ) ? $options['height'] : '50' ); ?>" class="stpb-number" />
                                </td>
                            </tr>
                            <tr>
                                <th>Opacity (0-1):</th>
                                <td>
                                    <input type="number" step="0.1" min="0" max="1" name="stpb_options[opacity]" value="<?php echo esc_attr( isset( $options['opacity'] ) ? $options['opacity'] : '0.8' ); ?>" class="stpb-number" />
                                </td>
                            </tr>
                            <tr>
                                <th>Heading Text Size (px):</th>
                                <td>
                                    <input type="number" name="stpb_options[heading_text_size]" value="<?php echo esc_attr( isset( $options['heading_text_size'] ) ? $options['heading_text_size'] : '16' ); ?>" class="stpb-number" />
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Animation Settings -->
                <div class="stpb-settings-section">
                    <h2><span class="dashicons dashicons-performance"></span> Animation Settings</h2>
                    <div class="stpb-settings-content">
                        <table class="form-table">
                            <tr>
                                <th>Animation Speed (ms):</th>
                                <td>
                                    <input type="number" name="stpb_options[animation_speed]" value="<?php echo esc_attr( isset( $options['animation_speed'] ) ? $options['animation_speed'] : '300' ); ?>" class="stpb-number" />
                                    <p class="description">Speed of the progress bar animation in milliseconds.</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Mobile Settings -->
                <div class="stpb-settings-section">
                    <h2><span class="dashicons dashicons-smartphone"></span> Mobile Settings</h2>
                    <div class="stpb-settings-content">
                        <table class="form-table">
                            <tr>
                                <th>Show on Mobile:</th>
                                <td>
                                    <label class="stpb-switch">
                                        <input type="checkbox" name="stpb_options[show_on_mobile]" value="1" <?php checked( isset( $options['show_on_mobile'] ) ? $options['show_on_mobile'] : true ); ?> />
                                        <span class="slider"></span>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th>Mobile Breakpoint (px):</th>
                                <td>
                                    <input type="number" name="stpb_options[mobile_breakpoint]" value="<?php echo esc_attr( isset( $options['mobile_breakpoint'] ) ? $options['mobile_breakpoint'] : '768' ); ?>" class="stpb-number" />
                                    <p class="description">Screen width breakpoint for mobile devices.</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <?php submit_button(); ?>
        </form>
    </div>

    <style>
        .stpb-settings-container {
            max-width: 1200px;
            margin: 20px 0;
        }

        .stpb-settings-section {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            margin-bottom: 20px;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }

        .stpb-settings-section h2 {
            margin: 0;
            padding: 15px 20px;
            border-bottom: 1px solid #ccd0d4;
            font-size: 1.3em;
            display: flex;
            align-items: center;
        }

        .stpb-settings-section h2 .dashicons {
            margin-right: 10px;
            color: #2271b1;
        }

        .stpb-settings-content {
            padding: 20px;
        }

        .stpb-checkbox-label {
            display: inline-block;
            margin-right: 20px;
            margin-bottom: 10px;
        }

        .stpb-text,
        .stpb-number,
        .stpb-textarea,
        .stpb-select {
            width: 100%;
            max-width: 400px;
        }

        .stpb-color {
            width: 100px;
            height: 40px;
            padding: 0;
            border: none;
        }

        .stpb-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .stpb-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .stpb-switch .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .stpb-switch .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        .stpb-switch input:checked + .slider {
            background-color: #2271b1;
        }

        .stpb-switch input:checked + .slider:before {
            transform: translateX(26px);
        }

        @media screen and (max-width: 782px) {
            .stpb-settings-section h2 {
                padding: 10px 15px;
            }

            .stpb-settings-content {
                padding: 15px;
            }

            .stpb-checkbox-label {
                display: block;
                margin-bottom: 15px;
            }
        }
    </style>
    <?php
}
