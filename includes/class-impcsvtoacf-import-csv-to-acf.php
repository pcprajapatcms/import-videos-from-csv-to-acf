<?php
class ImpCSVtoACF_Import_CSV_To_ACF {
    public function init() {
        add_action('admin_menu', array($this, 'impcsvtoacf_custom_tools_menu'));
        add_action('admin_footer', array($this, 'impcsvtoacf_custom_media_popup_link_script'));
        add_action('admin_enqueue_scripts', array($this, 'impcsvtoacf_admin_enqueue_styles'));
    }

    // Step 1: Create a Custom Admin Page
    public function impcsvtoacf_custom_tools_menu() {
        add_submenu_page(
            'tools.php',
            'Import CSV to ACF',
            'Import CSV to ACF',
            'manage_options',
            'import-csv-to-acf',
            array($this, 'impcsvtoacf_import_csv_to_acf_page')
        );
    }

    public function impcsvtoacf_import_csv_to_acf_page() {
        // Initialize variables to store textbox values
        $repeater_field_key = '';
        $sub_field_name = '';
    
        function impcsvtoacf_get_all_repeater_fields() {
            $field_groups = acf_get_field_groups();
        
            if (empty($field_groups)) {
                return;
            }
        
            $repeater_fields = array();
        
            foreach ($field_groups as $group) {
                $fields = acf_get_fields($group['key']);
        
                foreach ($fields as $field) {
                    if ($field['type'] === 'repeater') {
                        $repeater_fields[] = $field;
                    }
                }
            }
        
            return $repeater_fields;
        }
        
        function impcsvtoacf_display_repeater_dropdown() {
            $repeater_field_key = get_option('imp_first_field_key', true);
            $repeater_fields = impcsvtoacf_get_all_repeater_fields();
            if (empty($repeater_fields)) {
                return;
            }
            echo '<select name="repeater_field_key" id="repeater-fields" style="min-width:20%;">';
            echo '<option value="">Select Repeater Field</option>';
    
                foreach ($repeater_fields as $field) {
                    echo '<option value="' . esc_attr($field['name']) . '" data-subfields=\'' . wp_json_encode($field['sub_fields']) . '\'' . ($field['name'] === $repeater_field_key ? ' selected' : '') . '>' . esc_html($field['label']) . '</option>';
                }
            echo '</select>';
        }
        
        // Check if form is submitted
        if (isset($_POST['custom_csv_import_nonce']) && wp_verify_nonce($_POST['custom_csv_import_nonce'], 'custom_csv_import')) {
            // Check if the "Save Details" button was clicked.
            if (isset($_POST['save_details'])) {
                // Get and sanitize the values from the textboxes.
                update_option('imp_first_field_key', sanitize_text_field($_POST['repeater_field_key']));
                update_option('imp_second_field_key', sanitize_text_field($_POST['sub_field_name']));
                update_option('imp_csv_file_url', sanitize_text_field($_POST['csv_file_url']));
                update_option('imp_cpt_key', sanitize_text_field($_POST['cpt_post_type']));
                $repeater_field_key = get_option('imp_first_field_key', true);
                $sub_field_name = get_option('imp_second_field_key', true);
                $csv_file_url = get_option('imp_csv_file_url', true);
                $cpt_key = get_option('imp_cpt_key', true);
    
                // Output a success message.
                echo '<div class="updated"><p>Details saved successfully.</p></div>';
            }
        }
    
        if(get_option('imp_first_field_key') !== ''){
            $repeater_field_key = get_option('imp_first_field_key');
        }else{
            $repeater_field_key = '';
        }
    
        if(get_option('imp_second_field_key') !== ''){
            $sub_field_name = get_option('imp_second_field_key');
        }else{
            $sub_field_name = '';
        }
    
        if(get_option('imp_csv_file_url') !== ''){
            $csv_file_url = get_option('imp_csv_file_url');
        }else{
            $csv_file_url = '';
        }
    
        if(get_option('imp_cpt_key') !== ''){
            $selected_post_type = get_option('imp_cpt_key');
        }else{
            $selected_post_type = '';
        }
    
        // Add a form to enter the repeater field key and video field name.
        echo '<div class="wrap">';
        echo '<h2>CSV Import</h2>';
    
        // Display the form
        echo '<form method="post">
            <h2>Import or Bind Data From CSV to ACF Fields</h2>
            <input type="hidden" name="custom_csv_import_nonce" value="' . wp_create_nonce('custom_csv_import') . '">
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row">
                        <label for="cpt_post_type"><strong>Select Post Type:</strong></label>
                    </th>
                    <td>
                        <select id="cpt_post_type" name="cpt_post_type" style="min-width:20%;">';
                        // Get an array of public post types
                        $post_types = get_post_types(['public' => true], 'objects');
                        foreach ($post_types as $post_type_object) {
                            $selected = selected($selected_post_type, $post_type_object->name, false);
                            echo '<option value="' . esc_attr($post_type_object->name) . '" ' . $selected . '>' . esc_html($post_type_object->label) . '</option>';
                        }
                        echo '</select>
                    </td>
                </tr>
                    <tr>
                        <th scope="row">
                            <label for="repeater_field_key"><strong>Select Repeater Field: </label>
                        </th>
                        <td>';
                            impcsvtoacf_display_repeater_dropdown();
                        echo '</td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="sub_field_name">Select Sub Field: </label></th>
                        <td>
                            <select id="subfields" style="min-width:20%;">
                                <option value="">Select Subfield</option>
                            </select>
                            <input type="hidden" id="sub_field_name" name="sub_field_name" value="' . esc_attr($sub_field_name) . '" required placeholder="Enter repeater child field name(slug)" style="">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="csv_file_url">CSV File URL</label></th>
                        <td>
                            <p><strong>Note:</strong> if CSV file url or path is <code>https://www.example.com/wp-content/uploads/2023/11/video.csv</code> then enter this path <code>2023/11/video.csv</code><p>
                                <p>You can copy csv file url from <a href="'.admin_url('upload.php').'" target="blank">media screen</a> usign <mark>Copy CSV Url For Import</mark> Button</p>
                            <input type="text" id="csv_file_url" name="csv_file_url" value="' . esc_attr($csv_file_url) . '" required placeholder="Enter CSV file URL" style="min-width:20%; margin-top:1rem;">
                        </td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>
                            <input type="submit" class="button button-secondary" name="save_details" value="Save Details">
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>';
    
        // Check if "Import CSV" button is clicked
        if (isset($_POST['import_csv'])) {
            // Call the CSV import function with the provided field values.
            if(!empty($repeater_field_key) && !empty($sub_field_name) && !empty($csv_file_url)){
                $result = $this->impcsvtoacf_import_videos_from_csv($repeater_field_key, $sub_field_name, $csv_file_url);
                if (!$result) {
                    echo '<div class="error"><p>CSV Import failed. Please check the CSV file.</p></div>';
                }
            }else{
                echo '<div class="error"><p>Please enter the ACF fields name(slug).</p></div>';
            }
            
        }
    
        // Add an additional button for the import process
        echo "<hr/>";
        echo '<form method="post">
        <h2>Proceed to bind the csv file data with acf fields</h2>
            <input type="hidden" name="custom_csv_import_nonce" value="' . wp_create_nonce('custom_csv_import') . '">
            <input type="hidden" name="repeater_field_key" value="' . esc_attr($repeater_field_key) . '">
            <input type="hidden" name="sub_field_name" value="' . esc_attr($sub_field_name) . '">
            <input type="hidden" name="csv_file_url" value="' . esc_attr($csv_file_url) . '">
            <input type="submit" class="button button-primary" name="import_csv" value="Proceed">
        </form>';
        echo '</div>';
    }

    // Step 2: Modify the CSV Import Function
    public function impcsvtoacf_import_videos_from_csv($repeater_field_key, $sub_field_name, $csv_file_url) {
        $wp_upload_dir = wp_upload_dir();
        $wp_content_upload_folder_path = $wp_upload_dir['basedir'];
        $csv_file = $wp_content_upload_folder_path.'/'.$csv_file_url;
        if(get_option('imp_cpt_key') !== ''){
            $selected_post_type = get_option('imp_cpt_key');
        }else{
            $selected_post_type = '';
        }
        // Check if the CSV file exists.
        if (file_exists($csv_file)) {
            // Open the CSV file for reading.
            $handle = fopen($csv_file, 'r');
            if (file_exists($csv_file)) {
                // Open the CSV file for reading.
                $handle = fopen($csv_file, 'r');
            
                if ($handle !== false) {
                    while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                        // Extract post title and video URLs from the CSV.
                        $post_title = $data[0]; // Assuming the title is in the first column.
                        $video_urls_csv = $data[1];
            
                        // Transform the URLs
                        $video_urls_array = explode(',', $video_urls_csv);
                        $transformed_urls = array_map(function ($url) {
                            // Replace "original" with "converted"
                            $url = str_replace('/original/', '/converted/', $url);
            
                            // Check if "/original/" or "/converted/" is present
                            if (strpos($url, '/original/') === false && strpos($url, '/converted/') === false) {
                                // If neither is present, add "/converted/"
                                $url = str_replace('sites/default/files/case_studies/videos', 'sites/default/files/case_studies/videos/converted', $url);
                            }
            
                            // Replace the old domain with the new domain
                            $url = str_replace('https://oldsite.sonopath.com', 'http://sonopath-videos.s3.amazonaws.com', $url);
            
                            // Check if the video format is either ".flv" or ".mov"
                            if (strpos($url, '.flv') !== false || strpos($url, '.mov') !== false) {
                                // Modify the file format to ".mp4"
                                $url = str_replace('.flv', '.mp4', $url);
                                $url = str_replace('.mov', '.mp4', $url);
                            }
            
                            return $url;
                        }, $video_urls_array);
            
                        // Look up the post based on the title.
                        $query_args = array(
                            'post_type' => $selected_post_type,
                            'title' => $post_title,
                            'post_status' => 'any',
                            'posts_per_page' => 1,
                        );
            
                        $query = new WP_Query($query_args);
            
                        if ($query->have_posts()) {
                            while ($query->have_posts()) {
                                $query->the_post();
            
                                $existing_videos = get_field($repeater_field_key, get_the_ID());
            
                                if ($existing_videos) {
                                    foreach ($transformed_urls as $url) {
                                        $url_exists = false;
                                        foreach ($existing_videos as $video) {
                                            if ($video[$sub_field_name] === $url) {
                                                $url_exists = true;
                                                break;
                                            }
                                        }
                                        if (!$url_exists) {
                                            $existing_videos[] = array($sub_field_name => $url);
                                        }
                                    }
            
                                    // Update the ACF repeater field with the new values.
                                    update_field($repeater_field_key, $existing_videos, get_the_ID());
                                } else {
                                    // If the repeater field is empty, initialize it with the video URLs.
                                    $videos = array();
                                    foreach ($transformed_urls as $url) {
                                        $videos[] = array($sub_field_name => $url);
                                    }
                                    update_field($repeater_field_key, $videos, get_the_ID());
                                }
                            }
            
                            wp_reset_postdata();
                            echo '<div class="updated"><p>CSV Imported successfully.</p></div>';
                        } else {
                            echo '<div class="error"><p>Post not found!</p></div>';
                        }
                    }
            
                    fclose($handle);
            
                    return true; // Return true to indicate success.
                }
            }
        }
        return false; // Return false to indicate failure.
    }

    // Hook into the admin_footer action
    public function impcsvtoacf_custom_media_popup_link_script() {
        ?>
        <script>
            jQuery(document).ready(function($) {
                if ($('body').hasClass('post-type-attachment')) {
                    // Function to add custom link
                    function addCustomLink() {
                        var customLink = '<button type="button" class="button button-small copy_csv_link" style="margin-left:15px">Copy CSV Url For Import</button><span class="success hidden_url" aria-hidden="true">CSV URL Copied!</span>';
                        $('.copy-attachment-url').after(customLink);
                        $('.hidden_url').hide();
                    }

                    // Attach the function to the 'select' event of the media modal
                    $(document).on('click', 'li .attachment-preview.js--select-attachment', function() {
                        addCustomLink();
                        copyShortUrl();
                    });
                    setTimeout(() => {
                        addCustomLink();
                        copyShortUrl();
                    }, 1000);
                    function copyShortUrl(){
                        if($('.attachment-details-copy-link').length > 0){
                        var fullUrl = $('.attachment-details-copy-link').val();
                            var pathAfterUploads = fullUrl.split("uploads/")[1];
                            $(document).on('click', '.copy_csv_link', function(){
                                navigator.clipboard.writeText(pathAfterUploads).then(function() {
                                    $('.hidden_url').show();
                                    setTimeout(() => {
                                        $('.hidden_url').hide();
                                    }, 1500);
                                })
                            });
                        }
                    }
                }
            });
        </script>
        <?php
    }

    public function impcsvtoacf_admin_enqueue_styles() {
        $plugin_dir = plugin_dir_url(dirname(__FILE__));
        $plugin_file = plugin_dir_path(dirname(__FILE__)) . 'assets/js/impcsvtoacf-csv.js';
        $version = file_exists($plugin_file) ? filemtime($plugin_file) : '1.0';
        wp_enqueue_script('impcsvtoacf-csv', $plugin_dir . 'assets/js/impcsvtoacf-csv.js', array(), $version, true);
    }
}
?>
