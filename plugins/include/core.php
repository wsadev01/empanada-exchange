<?PHP
namespace E_core;

function get_ars_value()
{
	global $wpdb;
	// $table_name = "PREFIX" + "e_sources" (Mostly wp_e_sources).
	$table_name = $wpdb->prefix . 'e_sources';
	
	//Below is the explanation of this line, transpolated to the ARS_value.
	//In essence, we are doing the same for the ARS VALUE and the LAST_UPDATE value.
	$last_update_ondb = strtotime($wpdb->get_var("SELECT last_update FROM $table_name"));
	$current_timestamp = strtotime(current_time('mysql'));
	$last_update = $current_timestamp - $last_update_ondb;
	if ($last_update_ondb === 0 or $last_update >= 3599)
	{
		$status = sync_database();
		if ($status <= 0)
		{
			return $status;
		}
	}
	$ars_value = $wpdb->get_var("SELECT ars FROM $table_name");
	return $ars_value;
}


function sync_database()
{
	global $wpdb;
	$table_name = $wpdb->prefix . 'E_sources';
	$url = get_option('e-radio-url');
	$api_key = get_option('e-api-key');
	
	$fresh_init = get_option('e-fresh-init');
	//Check if the configs are ok or still being a fresh initialization.
	if ($fresh_init)
	{
		if ($url != "" and $api_key != "")
		{
			update_option('fresh-init', false);
		}
		elseif ($url === false or $api_key === false) return -1;
		else
		{
			/* ERRCODE: -1
			 * Still being a fresh initialization of the plugin.
			 * You should setup some parameters like source and api key.
			 */
			return '-1 (Plugin sin configurar)';
		}
	}
	
	
	$source = null;
/*  if ($url == "http://data.fixer.io/api/latest" and $api_key != "")
	{
		if (strlen($api_key) < 32)
		{
			/* ERRCODE: -11
			 * Invalid API key for the first source url.
			 */
/*  		return -11;
		}
		else
		{
			$source = 1;
			$fullurl = "$url?access_key=$api_key";
		}
	} */
	if ($url == "https://www.dolarsi.com/api/api.php")
	{
		$source = 2;
		$fullurl = "{$url}?type=valoresprincipales";
	}
	else 
	{
		/* ERRCODE: -2
		 * Invalid url. You may want to check your settings.
		 */
		return '-2 (URL Invalido)';
	}
	
/*  //Source 1 = fixer.io
	if ($source == 1)
	{
		$data = json_decode(file_get_contents($fullurl), true);
		
		//IF DATA FAILS!.
		if (!$data['success']) 
		{
			$code = $data['error']['code'];
			//echo $code;
			//ERRCODE: -12
			//Request failed, something went wrong, uncomment 'echo $code'
			//and check the code in fixer.io documentation for further information.
			return -12;
		}
		else
		{
			$rates = serialize($data['rates']);    //Rates of other currencies.
			$ars_value = $rates['ARS']; //ARS currency value.
			$update_status = $wpdb->update(
				$table_name,
				array('rates' => $rates,'ars' => $ars_value),
				array('id' => 1)
				);
		
			//ERRCODE: -4
			//Database update failed.
			if ($update_status == 0) return -4;
		}
	} */
	/*else*/if ($source == 2) 
	{
		//This api returns in the index 0 the "Official dollar" for Argentina.
		//The only one that must be of your interest. Mashed potatoes dollar is not allowed.
		$content = json_decode(file_get_contents($fullurl), true)[0];
		$ars_value = floatval(str_replace(',', '.', $content['casa']['compra']));
		$update_status = $wpdb->update(
			$table_name,
			array('ars' => $ars_value),
			array('id' => 1)
			);
		
		//ERRCODE: -4
		//Database update failed.
		if ($update_status === false) return '-4 Fallo la actualizacion del dolar en la base de datos';
	}
	else 
	{
		//ERRCODE: -3
		//For some reason, those up there, were invalid sources.
		//Even when they were checked up there and passed.
		return '-3 Error desconocido';
	}
	$update_status = $wpdb->update(
		$table_name,
		array('last_update' => current_time('mysql')),
		array('id' => 1)
		);
	//ERRCODE: -5
	//Could not set the last time the database was updated.
	if ($update_status === false) return '-5 Fallo la actualizacion de cuando fue la ultima sincronizacion con las fuentes';
	return 1;
	
}



	