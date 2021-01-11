<?PHP
	function init_menu()
	{
		add_menu_page(
			__("Empanada", "empanada-exchange"),
			__("Empanada", "empanada-exchange"),
			"manage_options",
			"e-settings",
			'e_settings_HTML',
			'',
			null
			);
	}
	function e_settings_HTML ()
	{?>
		<div class = "wrap">
			<h1><?PHP echo esc_html(get_admin_page_title()); ?></h1>
			<form action="options.php" method="post">
            <?php 
                // security field
                settings_fields('e-settings');

                // output settings section here
                do_settings_sections('e-settings');

                // save settings button
                submit_button('Save Settings');
            ?>
        </form>
		</div>
	<?PHP
	}
	add_action('admin_menu', 'init_menu');
// --------------------------------------------------------------------- \\

	function init_settings()
	{
		add_settings_section(
			'e_source_section',
			'Exchange sources.',
			'',
			'e-settings'
			);
		
		//Radio button
		register_setting(
			'e-settings',
			'e-radio-url',
			array(
				'type' => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'description' => 'Source URL.',
				'default' => 'http://data.fixer.io/api/latest'
				));
		
		add_settings_field(
			'e-radio-url',
			__('Source', 'empanada-exchange'),
			'e_settings_radio_HTML',
			'e-settings',
			'e_source_section'
			);
		
		//API KEY 
		register_setting(
			'e-settings',
			'e-api-key',
			array(
				'type' => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'description' => 'API Key for the source',
				'default' => ''
				));
		add_settings_field(
			'e-api-key',
			__('API Key', 'empanada-exchange'),
			'e_settings_key_HTML',
			'e-settings',
			'e_source_section'
			);			
	}
	
// .SECTION HTML
	//Radio button (Sources)
	function e_settings_radio_HTML() 
	{
		$radio_field = get_option('e-radio-url');
		$checked_0 = checked('https://www.dolarsi.com/api/api.php', $radio_field, false);
		echo <<<HTML
		<label for="dolarsi">
			<input type="radio" name="e-radio-url" value="https://www.dolarsi.com/api/api.php" $checked_0 />dolarsi.com
		</label>
HTML;
	}
	//Entry for the api key
	function e_settings_key_HTML() 
	{
		$api_key = get_option('e-api-key');
		$url = get_option('e-radio-url');
		$maxlength = 0;
		
	/*  if ($url == "http://data.fixer.io/api/latest")
		{
			$disabled = "";
			$maxlength = 32;
		}
		else
		{
			$disabled = "disabled";
			$api_key = "unused";
			update_option('ae-api-key', $api_key);
		}
	*/
		$disabled = "disabled";
		$api_key = "unused";
		update_option('e-api-key', $api_key);
		echo <<<HTML
		<label for="api_key">
			<input name="ae-api-key" size=40 maxlength=$maxlength value="$api_key" $disabled />
		</label>
HTML;
	}
	
	add_action('admin_init', 'init_settings');
?>