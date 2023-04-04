import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import { TextControl } from '@wordpress/components';
import { useState } from '@wordpress/element';

registerBlockType('yahoo-weather-widget/yahoo-weather-block', {
    apiVersion: 2,
    title: 'Yahoo Weather Block',
    icon: 'cloud',
    category: 'widgets',
    attributes: {
        location: {
            type: 'string',
            default: 'San Francisco, CA'
        }
    },
    edit: ({ attributes, setAttributes }) => {
        const blockProps = useBlockProps();
        const [weatherData, setWeatherData] = useState(null);

        const fetchData = async () => {
            const location = encodeURIComponent(attributes.location);
            const yahoo_weather_api = `https://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20weather.forecast%20where%20woeid%20in%20(select%20woeid%20from%20geo.places(1)%20where%20text%3D%22${location}%22)&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys`;

            const response = await fetch(yahoo_weather_api);
            const weather_data = await response.json();
            setWeatherData(weather_data);
        };

        return (
            <div {...blockProps}>
                <TextControl
                    label="Location"
                    value={attributes.location}
                    onChange={(newLocation) => {
                        setAttributes({ location: newLocation });
                        fetchData();
                    }}
                />
                {weatherData && (
                    <div className="yahoo-weather">
                        <div className="yahoo-weather-location">
                            {attributes.location}
                        </div>
                        <div className="yahoo-weather-condition">
                            {weatherData.query.results.channel.item.condition.text}
                        </div>
                        <div className="yahoo-weather-temperature">
                            {weatherData.query.results.channel.item.condition.temp}°F
                        </div>
                    </div>
                )}
            </div>
        );
    },
    save: () => {
        return null;
    }
});
