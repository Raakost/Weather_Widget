<?php
/*
 * Plugin Name: JeSyd Weather Widget
 * Plugin URI: http://no_plugin_page_availible.com
 * Description: This weather widget will show the current weather for Esbjerg, you can search for weather forecast at other locations.
 * Version: 0.8
 * Author: Jeanette Sydbøge
 * Author URI: http://no_author_page_availible.com
 * License: none
*/


// Register widget with wordpress
function register_weather_widget()
{
    register_widget('JeSydWeatherWidget');
}

/* Register and enqueue stylesheet */
function register_weather_stylesheet()
{
    wp_register_style('weather_widget_stylesheet',
                      plugins_url() . '/weather_widget_plugin/weather_widget_layout.css');

    wp_enqueue_style('weather_widget_stylesheet');
}

/* Action hooks */
add_action('widgets_init', 'register_weather_widget');
add_action('wp_enqueue_scripts', 'register_weather_stylesheet');


/* This class extends the Wordpress widget api. */

class JeSydWeatherWidget extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'JeSydWeatherWidget',
            esc_html__('JeSyd Weather Widget', 'text_domain'),
            array('customize_selective_refresh' => true,)
        );
    }

    /* Outputs widget content */
    public function widget($args, $instance)
    {
        $api_url = 'http://api.apixu.com/v1/current.json?key=cef57cc59d9f4148871220732182709&q=esbjerg';

        $response = file_get_contents($api_url);
        $decoded = json_decode($response, true);

        // Location info
        $location_name = $decoded["location"]["name"];
        $location_localtime = $decoded["location"]["localtime"];
        $time_stamp = new DateTime($location_localtime);
        $location_localtime = $time_stamp->format('H:i');

        // Current info
        $current_temp_c = $decoded["current"]["temp_c"];
        $current_wind_mph = $decoded["current"]["wind_mph"];
        $current_wind_ms = (round($current_wind_mph * 0.44704, 0));
        $current_wind_degree = $decoded["current"]["wind_degree"];
        $current_wind_dir = $decoded["current"]["wind_dir"];

        // Condition info
        $condition_text = $decoded["current"]["condition"]["text"];
        $condition_icon = $decoded["current"]["condition"]["icon"];

        if ($response) {
            echo
                "<!DOCTYPE html>
                    <html>
                    <head>
                        <link rel='stylesheet' href='https://use.fontawesome.com/releases/v5.5.0/css/all.css' integrity='sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU' crossorigin='anonymous'>
                    </head>
                    <body>                                                                                                                                                        
                        <div class='widget-container'>                       
                            <div class='widget-content-wrapper'>
                                <input placeholder='Enter city or country..' type='text' id='WwSearch' name='wwSearch' autocomplete='off' />
                                <div style='text-align: center;'>
                                    <button id='SearchBtn' type='button'>
                                        <i class='fas fa-search'></i>
                                    </button>
                                    <h3 class='weather-txt' id='LocationName'>" . ($location_name) . "</h3>                                    
                                </div>                                       
                                <hr class='hr-shadow'>        
                                <div class='weather-txt-container'>                               
                                    <p class='weather-txt txt-medium' id='CurrentTempC'>" . ($current_temp_c) . ' °' . "</p>
                                </div>  
                                <div class='weather-txt-container'>
                                    <p id='CurrentWindMS' class='weather-txt txt-medium' style='margin: 0 15px'>" . ($current_wind_ms) . ' m/s' . "</p>                                   
                                    <p id='CurrentWindDegree' class='weather-txt txt-medium' style='margin: 0 15px'>" . '&nbsp' . ($current_wind_degree . '° ' . $current_wind_dir) . "</p>                                                                                     
                                </div>            
                                <p id='ConditionText' class='weather-txt txt-medium'>" . '&nbsp' . ($condition_text) . "</p>
                                <div class='center-image'>                                    
                                    <img id='ConditionIcon' src='$condition_icon' />                                   
                                </div> 
                                <p class='weather-txt txt-x-small'>" . 'Last updated: ' . ($location_localtime) . "</p>   
                            </div>                                     
                        </div>                      
                        <script>
                            var searchInput = document.querySelector('#WwSearch');
                            var searchBtn = document.getElementById('SearchBtn');
                            
                            searchInput.addEventListener('keypress', function(event) {
                                if(event.keyCode === 13){ // KEY CODE FOR ENTER
                                    searchCity();
                                }
                            });
                            
                            searchBtn.addEventListener('click', function(event) {                               
                               searchInput.classList.add('visible');                               
                               searchInput.focus();                     
                            });
                            
                            function searchCity() {
                                var apiUrl = 'http://api.apixu.com/v1/current.json?key=cef57cc59d9f4148871220732182709&q=[CITY]';
                                
                                fetch(apiUrl.replace('[CITY]', searchInput.value))
                                    .then(function(response) {
                                        return response.json();
                                     })
                                    .then(function(JsonResponse) {
                                        document.querySelector('#LocationName').innerHTML = JsonResponse.location.name;
                                        document.querySelector('#CurrentTempC').innerHTML = JsonResponse.current.temp_c + ' °';
                                        document.querySelector('#CurrentWindMS').innerHTML = (JsonResponse.current.wind_mph * 0.44704).toFixed(0)  +  ' m/s';
                                        document.querySelector('#CurrentWindDegree').innerHTML = JsonResponse.current.wind_degree +  '° ' + JsonResponse.current.wind_dir;
                                        document.querySelector('#ConditionText').innerHTML = JsonResponse.current.condition.text;
                                        document.querySelector('#ConditionIcon').src = JsonResponse.current.condition.icon;                                         
                                        searchInput.classList.remove('visible');                                        
                                    }
                                 );
                            } 
                        </script>                       
                    </body>
                </html>
                ";
        } else {
            echo "<p><strong>Something went wrong! - Cannot display widget at the moment...</strong></p>";
        };
    }
}