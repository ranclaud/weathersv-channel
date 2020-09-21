<?php
/*
 Plugin Name: WeatherSV Channel
 Plugin URI: http://ranclaud.com
 Description: Get data for WeatherSV channel
 Author: Perry Ranclaud
 Version: 1.0.1
 */


//if plugin is called directly
defined( 'ABSPATH' ) or die( 'Unauthorised Access!' );

//register shortcode
add_shortcode( 'weathersv', 'callback_weathersv' );

function callback_weathersv( $atts ) {
	$html = '';
	//get attributes
    $atts = shortcode_atts(
        array(
            'id' => 'No Channel Selected',
            'layout' => '1',
        ), $atts, 'weathersv');
		
	$channelID = $atts['id'];
		
	//api address
	$url = 'https://weathersv.app/api/channel/' . $channelID;
	
	
	
	//get channel api
	$arguments = array(
		'method' => 'GET',
	);		
	$response = wp_remote_get($url, $arguments);
	//error check
	if( is_wp_error($response)){
		$error_message = $response->get_error_message();
		echo 'Something went wrong $error_message';
	}
	
		
	//convert to php
	$results = json_decode(wp_remote_retrieve_body($response));
	
	
	$innerResult = $results->location;	
	$name = $innerResult->name;	
	$country = $innerResult->country;
	$lng = $innerResult->lng;
	$innerResult = $results->status;	
	$active = $innerResult->active;
	
	

	foreach($results as $innerResult){		
		$temperature .=  $innerResult->temperature;
		$humidity = $innerResult->humidity;
		$pressure = $innerResult->pressure;
		$cloudiness = $innerResult->cloudiness;
		$wind_speed = $innerResult->wind_speed;
		$wind_direction = $innerResult->wind_direction;	
		$datetime = $innerResult->datetime;	
	}
	
	// Current date and time
	$datetime = date("Y-m-d H:i:s");
	
	// Convert datetime to Unix timestamp
	$timestamp = strtotime($datetime);
	
	//get timezone
	$lngY = -180;
	for($timeX = -12; $timeX < 12; $timeX++){
		$lngY += 15;
		if($lngY>$lng and $lngY-15<$lng){
			$addZ = $timeX;
			continue;
		}
	}
	
	// Subtract time from datetime
	$time = $timestamp - ($addZ * 60 * 60);
	
	// Date and time after subtraction
	$datetime = date("Y-m-d H:i:s", $time);	
	
	//check if day/night
	$dtgH = date('H', strtotime($datetime));	
	if($dtgH>'6' and $dtgH<'18'){
		$day = '1';
	}else{
		$day = '0';
	}
	
	
	//check if cloudy	
	if($cloudiness<'33'){
		$clouds = '0';
	}elseif($cloudiness>='33' and $cloudiness<'66'){
		$clouds = '1';
	}elseif($cloudiness>='66'){
		$clouds = '2';
	}
	
	
	//check if raining
	if($pressure<'1009'){
		$rain = '1';
	}else{
		$rain = '0';
	}
	
	//set pic for weather image
	if($day=='1' and $clouds=='0'){
		$img = '/wp-content/plugins/weathersv-channel/images/sun1.png';
	}elseif($day=='1' and $clouds=='1'){
		$img = '/wp-content/plugins/weathersv-channel/images/cloud5.png';
	}elseif($day=='0' and $clouds=='0'){
		$img = '/wp-content/plugins/weathersv-channel/images/moon1.png';
	}elseif($day=='0' and $clouds=='1'){
		$img = '/wp-content/plugins/weathersv-channel/images/cloud4.png';
	}elseif($clouds=='2'){
		$img = '/wp-content/plugins/weathersv-channel/images/cloud1.png';
	}elseif($rain=='1'){
		$img = '/wp-content/plugins/weathersv-channel/images/cloud2.png';
	}
	

	//set pic for active/inactive channel
	if($active=='1'){		
		$star = '/wp-content/plugins/weathersv-channel/images/active.png';
		$activeText = 'Channel is Active';
	}else{
		$star = '/wp-content/plugins/weathersv-channel/images/inactive.png';
		$activeText = 'Channel is Inactive';
	}
	
	
	
	
	$html .= '<div style="padding-right:2px;width:262px;display:inline-block;font-size:14px">';
	$html .= '<table style="border-style:ridge;border-color:#D6DBDF">';
	$html .= '<tr>';
	$html .= '<td style="padding:0px"><image title="' . $activeText . '" src="' . $star . '" style="width:32px"></td>';	
	$html .= '<td><a href="https://weathersv.app/channel/' . $channelID . '" target="_blank"  rel="noopener">' . $name . ', ' . $country . '</a></td>';
	$html .= '<td style="padding:0px"><image src="' . $img . '" style="width:24px"></td>';
	$html .= '<td>' . $temperature . '<span>&#8451;</span></td>';
	$html .= '</tr>';
	$html .= '</table>';
	$html .= '</div>';

	
	return $html;
	
	
	
}





