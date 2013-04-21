CREATE TABLE IF NOT EXISTS `mesaje` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file` varchar(255) NOT NULL,
  `to` varchar(255) NOT NULL,
  `from` varchar(255) NOT NULL,
  `mesaj` varchar(1024) NOT NULL,
  `data_trimitere` date NOT NULL,
  `data_citire` datetime NOT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM