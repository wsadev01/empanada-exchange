<?PHP
/**
 * Plugin Name
 *
 * @author		torswq
 * @copyright	2020-2021 Aitor Santoro
 * @license		GPL-3.0
 * @package		Empanada
 *
 * @wordpress-plugin
 * Author Name:			Aitor Santoro.
 * Description: 		Con este plugin podes obtener el valor del dolar oficial con y sin impuestos y convertir pesos a dolares. Compatible con elementor!. This plugin supports elementor.
 * License: 			GPL v3
 * License URI:     	http://www.gnu.org/licenses/gpl-3.0.txt
 * Plugin Name: 		Empanada exchange.
 * Requires PHP: 		7.0
 * Requires at least: 	5.5
 * Version: 			0.1
 */
 
	defined('ABSPATH') or die("You shouldn't be here...");
	
	$INCLUDE_PATH = __DIR__ . '/include';
	$SETTINGS_PATH = $INCLUDE_PATH . '/settings';	
	require_once($SETTINGS_PATH . '/settings.php');
	require_once($INCLUDE_PATH . '/empanada_core.php');
	
	// * ALL the functions from empanada_core.php lives in the namespace Empanada_core.
	// * The function setup_elementor is from elementor-setup.php
	
	//Ok so i am a newbie on this, so i wont make this in the best way,
	//i will rather make it on a little complex but safer way.
	class EmpanadaExchange
	{
		function activation()
		{
			
			global $wpdb;
			$charset_collate = $wpdb->get_charset_collate();
			$table_name = $wpdb->prefix . 'empanada_exchange';
			
			$sql  = "CREATE TABLE IF NOT EXISTS $table_name(
			id INT NOT NULL AUTO_INCREMENT,
			rates BLOB NOT NULL,
			ars FLOAT NOT NULL,
			last_update DATETIME NULL,
            PRIMARY KEY  (id) 
			) $charset_collate;";
					
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta($sql);
			
			$wpdb->insert($table_name, array('ars' => 0.0));
			
			add_option('e-fresh-init', true);
			$status_option = get_option('e-fresh-init');
			if (!$status_option) die('Unable to set the option fresh_init');
		}
		
		function deactivation()
		{
			global $wpdb;
			$table_name = $wpdb->prefix . 'empanada_exchange';
			$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
			delete_option('e-api-key');
			delete_option('e-radio-url');
			delete_option('e-fresh-init');
		}
		
		
		function get_exchange($atts, $content, $tag)
		{
			
			$atts = array_change_key_case((array) $atts, CASE_LOWER);
			
			$atts = shortcode_atts(array(
				'impuesto_pais' => false,
				'retencion_ganancias' => false,
				'precio' => 10,
				'get_precio' => false
				), $atts);
			$impuesto_pais = rest_sanitize_boolean($atts['impuesto_pais']);
			$retencion_ganancias = rest_sanitize_boolean($atts['retencion_ganancias']);
			$get_precio = rest_sanitize_boolean($atts['get_precio']);
			$precio = $atts['precio'];
			if (is_numeric($precio)) $precio = floatval($precio);
			else return "Error: $precio no es un precio valido.";
			
			//For get_ars_value error reference, check the github page.
			$ARS_value = Empanada_core\get_ars_value();
			
			if ($impuesto_pais and $retencion_ganancias) $ARS_value = $ARS_value * 1.65;
			elseif ($impuesto_pais) $ARS_value = $ARS_value * 1.30;
			elseif ($retencion_ganancias) $ARS_value = $ARS_value * 1.35;
			
			if ($get_precio) return $precio * $ARS_value;
			else return $ARS_value;
		}
	}
	
	
	if (class_exists("EmpanadaExchange"))
	{
		$E = new EmpanadaExchange();
		
	}
	
	register_activation_hook(__FILE__, array($E, 'activation'));
	register_deactivation_hook(__FILE__, array($E, 'deactivation'));
	add_shortcode("get_exchange", array($E, 'get_exchange'));
	
	
?>
