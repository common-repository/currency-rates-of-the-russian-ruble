<?php
/**
 * Plugin Name:       Currency Rates of the Russian Ruble
 * Description:       Plugin with currency rates of the Russian ruble from the Central Bank. 
 * Version:           1.0.0
 * Author:            Yury Sliznikov
 * Author URI:        https://clck.ru/EhdyJ
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cur-rates-cb
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

load_plugin_textdomain('cur-rates-cb', false, basename( dirname( __FILE__ ) ) . '/languages' );

add_action('admin_enqueue_scripts', 'crrr_currency_rates_register_styles');

function crrr_currency_rates_register_styles() {
   wp_enqueue_style('cur-rates-style', plugins_url('/css/style.css', __FILE__), '1.0', 'all' );
}

/** Admin area ************************************************************************/
add_action('admin_menu', 'crrr_register_cur_rates_submenu');
function crrr_register_cur_rates_submenu() {
	add_submenu_page('options-general.php', __('Currency Rates of Russian Ruble', 'cur-rates-cb'), __('Currency rates of ruble', 'cur-rates-cb'), 'manage_options', 'cur-rates-submenu', 'crrr_cur_rates_submenu_callback');
}
function crrr_cur_rates_submenu_callback() {
	require_once plugin_dir_path( __FILE__ ) . 'cur-rates-admin.php';
}

function crrr_get_last_date() {
	$currency_rates = crrr_get_currency_rates();
	return $currency_rates->Date;
}

function crrr_get_prev_date() {
	$currency_rates = crrr_get_currency_rates();
	return $currency_rates->PreviousDate;
}

function crrr_get_last_rate( $currency ) {
	return crrr_get_currency_rates()->Valute->$currency->Value;
}

function crrr_get_prev_rate( $currency ) {
	return crrr_get_currency_rates()->Valute->$currency->Previous;
}

/** Public area ***********************************************************************/
function crrr_get_currency_rates() {
    $json_daily_file = __DIR__.'/daily.json';
    if ( !is_file($json_daily_file) || filemtime($json_daily_file) < (time() - 3600) ) {
    	if ($json_daily = wp_remote_retrieve_body( wp_remote_get('https://www.cbr-xml-daily.ru/daily_json.js') ) ) {
            file_put_contents($json_daily_file, $json_daily);
        }
    }
    return json_decode(file_get_contents($json_daily_file));
}

add_shortcode( 'rates', 'crrr_set_cur_rates_shortcode');

function crrr_exists_param( $params, $chk_param ) {
	foreach ($params as $param) {
		if ($param == $chk_param) {
			return true;
		}
	}
	return false;
}

function crrr_set_cur_rates_shortcode( $params ) {
	$Currency = strtoupper( isset($params['cur']) ? $params['cur'] : '' );
	$prevCurrency = strtoupper( isset($params['prevcur']) ? $params['prevcur'] : '' );
	$rounding = isset($params['r']) ? $params['r'] : '';
	$atDateFormat = isset($params['date']) ? $params['date'] : '';
	$atPrevDateFormat = isset($params['prevdate']) ? $params['prevdate'] : '';
	$rate = 0;

	if ($atDateFormat != '' || $atPrevDateFormat != '') {
		if ($atDateFormat == true) {
			$result = date_format( date_create(crrr_get_last_date()), $atDateFormat );
		} else {
			$result = date_format( date_create(crrr_get_prev_date()), $atPrevDateFormat );
		}
		return $result;
	}
	
	if ($prevCurrency != '') {
		$rate = crrr_get_prev_rate( $prevCurrency );
	} else {
		$rate = crrr_get_last_rate( $Currency );
	}

	if ($rounding == '') {
		$result = $rate;
	} else {
		$result = round( $rate, $rounding );
	}
	return $result;
}
