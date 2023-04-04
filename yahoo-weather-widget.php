<?php
/*
Plugin Name: Yahoo Weather Widget
Plugin URI: https://github.com/daniel911texas/yahoo-weather-widget
Description: Adds a Yahoo Weather widget to any page.
Version: 1.0
Author: Daniel Singletary
Author URI: https://github.com/daniel911texas/
License: GPL2
*/
function yahoo_weather_widget_register() {
    register_widget('Yahoo_Weather_Widget');
}
add_action('widgets_init', 'yahoo_weather_widget_register');

function yahoo_weather_widget_enqueue_styles() {
    wp_enqueue_style('yahoo-weather-widget', plugins_url('yahoo-weather-widget.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'yahoo_weather_widget_enqueue_styles');

class Yahoo_Weather_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'yahoo_weather_widget',
            __('Yahoo Weather', 'text_domain'),
            array('description' => __('Displays Yahoo Weather information', 'text_domain'))
        );
    }

    public function widget($args, $instance) {
        echo $args['before_widget'];
    
        $location = urlencode(apply_filters('widget_location', !empty($instance['location']) ? $instance['location'] : 'San Francisco, CA'));
        $yahoo_weather_api = "https://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20weather.forecast%20where%20woeid%20in%20(select%20woeid%20from%20geo.places(1)%20where%20text%3D%22{$location}%22)&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys";
    
        $response = wp_remote_get($yahoo_weather_api);
        if (is_wp_error($response)) {
            echo __('Failed to fetch weather data.', 'text_domain');
            return;
        }
    
        $weather_data = json_decode(wp_remote_retrieve_body($response), true);
        $condition = $weather_data['query']['results']['channel']['item']['condition'];
    
        echo '<div class="yahoo-weather">';
        echo '<div class="yahoo-weather-location">' . esc_html($location) . '</div>';
        echo '<div class="yahoo-weather-condition">' . esc_html($condition['text']) . '</div>';
        echo '<div class="yahoo-weather-temperature">' . esc_html($condition['temp']) . '&deg;F</div>';
        echo '</div>';
    
        echo $args['after_widget'];
    }

    public function form($instance) {
        $location = !empty($instance['location']) ? $instance['location'] : __('San Francisco, CA', 'text_domain');
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('location')); ?>"><?php _e('Location:', 'text_domain'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('location')); ?>" name="<?php echo esc_attr($this->get_field_name('location')); ?>" type="text" value="<?php echo esc_attr($location); ?>">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['location'] = (!empty($new_instance['location'])) ? sanitize_text_field($new_instance['location']) : '';
        return $instance;
    }
}
