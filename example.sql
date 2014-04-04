CREATE TABLE IF NOT EXISTS `nweather-gerecse` (
  `index` int(11) NOT NULL AUTO_INCREMENT,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `temp-in` float NOT NULL,
  `temp-out` float NOT NULL,
  `hum-in` float NOT NULL,
  `hum-out` float NOT NULL,
  `pres` float NOT NULL,
  `dewpoint` float NOT NULL,
  `rain` float NOT NULL,
  `windspeed` float NOT NULL,
  `winddir` varchar(3) NOT NULL DEFAULT 'N/A',
  PRIMARY KEY (`index`),
  UNIQUE KEY `date` (`date`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=134718 ;
