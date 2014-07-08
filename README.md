nweather-wordpress-plugin
=========================

Wordpress plugin which shows graphs from data uploaded by [nweather-upload](https://github.com/nonoo/nweather-upload).

#### Usage

Edit and rename *upload-config-example.inc.php* to *upload-config.inc.php*,
*nweather-example.css* to *nweather.css*, then enable the plugin on the
Wordpress plugin configuration page. To show the graphs of a context, insert
this to a Wordpress page or post:

```
<nweather context="gerecse" />
```

If you want to modify the displayed data fields and their labels:

```
<nweather context="szentendre" datafields="temp-200cm temp-50cm temp-0cm hum pres windgust windspeed winddir rain10m" labels="°C °C °C % hPa km/h km/h degree min" />
```

You can see a working example [here](http://www.ha5kdr.hu/projektek/idojaras/gerecse).
