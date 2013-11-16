-- phpMyAdmin SQL Dump
-- version 4.0.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 14, 2013 at 10:20 PM
-- Server version: 5.5.32-0ubuntu0.13.04.1
-- PHP Version: 5.4.9-4ubuntu2.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `cloneReader_empty`
--
CREATE DATABASE IF NOT EXISTS `cloneReader_empty` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `cloneReader_empty`;

DELIMITER $$
--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `countUnread`(_userId INT, _feedId INT, _tagId INT, _maxCount INT) RETURNS int(11)
BEGIN

#DECLARE total INT;

#SET total = (
RETURN (
        SELECT 
				COUNT(1) AS total FROM ( 
			    	SELECT 1 
			    	FROM users_entries FORCE INDEX (indexUnread)
			    	WHERE feedId 	    = _feedId
					AND   userId	 	    = _userId
					AND   tagId			 = _tagId
			    	AND   entryRead 	 = false 
					LIMIT _maxCount
			) AS tmp); 

#RETURN total;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `controllers`
--

CREATE TABLE IF NOT EXISTS `controllers` (
  `controllerId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `controllerName` char(100) NOT NULL,
  `controllerUrl` char(255) NOT NULL,
  `controllerActive` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`controllerId`) USING BTREE,
  UNIQUE KEY `functionName` (`controllerName`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=24 ;

--
-- Dumping data for table `controllers`
--

INSERT INTO `controllers` (`controllerId`, `controllerName`, `controllerUrl`, `controllerActive`) VALUES
(1, 'login/index', 'login', 1),
(2, 'logout/index', 'logout', 1),
(3, 'users/listing', 'users', 1),
(4, 'users/edit', 'users/edit', 1),
(5, 'controllers/listing', 'controllers', 1),
(6, 'controllers/edit', 'controllers/edit', 1),
(7, 'groups/listing', 'groups', 1),
(8, 'groups/edit', 'groups/edit', 1),
(9, 'menu/edit', 'menu', 1),
(10, 'profile/edit', 'profile', 1),
(11, 'home/index', 'home', 1),
(12, 'register', 'register', 1),
(13, 'feeds/listing', 'feeds/listing', 1),
(14, 'feeds/edit', 'feeds/edit', 1),
(15, 'entries/listing', 'entries/listing', 1),
(16, 'entries/edit', 'entries/edit', 1),
(17, 'tags/edit', 'tags/edit', 1),
(18, 'tags/listing', 'tags/listing', 1),
(19, 'profile/importFeeds', 'profile/importFeeds', 1),
(20, 'profile/importStarred', 'profile/importStarred', 1),
(21, 'news/listing', 'news', 1),
(22, 'news/edit', 'news/edit', 1),
(23, 'rss/index', 'rss', 1);

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE IF NOT EXISTS `countries` (
  `countryId` char(2) NOT NULL,
  `countryName` varchar(100) NOT NULL,
  PRIMARY KEY (`countryId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`countryId`, `countryName`) VALUES
('ad', 'Andorra'),
('ae', 'United Arab Emirates'),
('af', 'Afghanistan'),
('ag', 'Antigua and Barbuda'),
('ai', 'Anguilla'),
('al', 'Albania'),
('am', 'Armenia'),
('an', 'Netherlands Antilles'),
('ao', 'Angola'),
('aq', 'Antarctica'),
('ar', 'Argentina'),
('as', 'American Samoa'),
('at', 'Austria'),
('au', 'Australia'),
('aw', 'Aruba'),
('az', 'Azerbaijan'),
('ba', 'Bosnia and Herzegovina'),
('bb', 'Barbados'),
('bd', 'Bangladesh'),
('be', 'Belgium'),
('bf', 'Burkina Faso'),
('bg', 'Bulgaria'),
('bh', 'Bahrain'),
('bi', 'Burundi'),
('bj', 'Benin'),
('bm', 'Bermuda'),
('bn', 'Brunei'),
('bo', 'Bolivia'),
('br', 'Brasil'),
('bs', 'Bahamas'),
('bt', 'Bhutan'),
('bv', 'Bouvet Island'),
('bw', 'Botswana'),
('by', 'Belarus'),
('bz', 'Belize'),
('ca', 'Canada'),
('cc', 'Cocos (Keeling) Islands'),
('cd', 'Congo, The Democratic Republic of the'),
('cf', 'Central African Republic'),
('cg', 'Congo'),
('ch', 'Switzerland'),
('ci', 'Côte d''Ivoire'),
('ck', 'Cook Islands'),
('cl', 'Chile'),
('cm', 'Cameroon'),
('cn', 'China'),
('co', 'Colombia'),
('cr', 'Costa Rica'),
('cu', 'Cuba'),
('cv', 'Cape Verde'),
('cx', 'Christmas Island'),
('cy', 'Cyprus'),
('cz', 'Czech Republic'),
('de', 'Germany'),
('dj', 'Djibouti'),
('dk', 'Denmark'),
('dm', 'Dominica'),
('do', 'Dominican Republic'),
('dz', 'Algeria'),
('ec', 'Ecuador'),
('ee', 'Estonia'),
('eg', 'Egypt'),
('eh', 'Western Sahara'),
('er', 'Eritrea'),
('es', 'Spain'),
('et', 'Ethiopia'),
('fi', 'Finland'),
('fj', 'Fiji Islands'),
('fk', 'Falkland Islands'),
('fm', 'Micronesia, Federated States of'),
('fo', 'Faroe Islands'),
('fr', 'France'),
('ga', 'Gabon'),
('gb', 'United Kingdom'),
('gd', 'Grenada'),
('ge', 'Georgia'),
('gf', 'French Guiana'),
('gh', 'Ghana'),
('gi', 'Gibraltar'),
('gl', 'Greenland'),
('gm', 'Gambia'),
('gn', 'Guinea'),
('gp', 'Guadeloupe'),
('gq', 'Equatorial Guinea'),
('gr', 'Greece'),
('gs', 'South Georgia and the South Sandwich Islands'),
('gt', 'Guatemala'),
('gu', 'Guam'),
('gw', 'Guinea-Bissau'),
('gy', 'Guyana'),
('hk', 'Hong Kong'),
('hm', 'Heard Island and McDonald Islands'),
('hn', 'Honduras'),
('hr', 'Croatia'),
('ht', 'Haiti'),
('hu', 'Hungary'),
('id', 'Indonesia'),
('ie', 'Ireland'),
('il', 'Israel'),
('in', 'India'),
('io', 'British Indian Ocean Territory'),
('iq', 'Iraq'),
('ir', 'Iran'),
('is', 'Iceland'),
('it', 'Italy'),
('jm', 'Jamaica'),
('jo', 'Jordan'),
('jp', 'Japan'),
('ke', 'Kenya'),
('kg', 'Kyrgyzstan'),
('kh', 'Cambodia'),
('ki', 'Kiribati'),
('km', 'Comoros'),
('kn', 'Saint Kitts and Nevis'),
('kp', 'North Korea'),
('kr', 'South Korea'),
('kw', 'Kuwait'),
('ky', 'Cayman Islands'),
('kz', 'Kazakstan'),
('la', 'Laos'),
('lb', 'Lebanon'),
('lc', 'Saint Lucia'),
('li', 'Liechtenstein'),
('lk', 'Sri Lanka'),
('lr', 'Liberia'),
('ls', 'Lesotho'),
('lt', 'Lithuania'),
('lu', 'Luxembourg'),
('lv', 'Latvia'),
('ly', 'Libyan Arab Jamahiriya'),
('ma', 'Morocco'),
('mc', 'Monaco'),
('md', 'Moldova'),
('mg', 'Madagascar'),
('mh', 'Marshall Islands'),
('mk', 'Macedonia'),
('ml', 'Mali'),
('mm', 'Myanmar'),
('mn', 'Mongolia'),
('mo', 'Macao'),
('mp', 'Northern Mariana Islands'),
('mq', 'Martinique'),
('mr', 'Mauritania'),
('ms', 'Montserrat'),
('mt', 'Malta'),
('mu', 'Mauritius'),
('mv', 'Maldives'),
('mw', 'Malawi'),
('mx', 'Mexico'),
('my', 'Malaysia'),
('mz', 'Mozambique'),
('na', 'Namibia'),
('nc', 'New Caledonia'),
('ne', 'Niger'),
('nf', 'Norfolk Island'),
('ng', 'Nigeria'),
('ni', 'Nicaragua'),
('nl', 'Netherlands'),
('no', 'Norway'),
('np', 'Nepal'),
('nr', 'Nauru'),
('nu', 'Niue'),
('nz', 'New Zealand'),
('om', 'Oman'),
('pa', 'Panama'),
('pe', 'Peru'),
('pf', 'French Polynesia'),
('pg', 'Papua New Guinea'),
('ph', 'Philippines'),
('pk', 'Pakistan'),
('pl', 'Poland'),
('pm', 'Saint Pierre and Miquelon'),
('pn', 'Pitcairn'),
('pr', 'Puerto Rico'),
('ps', 'Palestine'),
('pt', 'Portugal'),
('pw', 'Palau'),
('py', 'Paraguay'),
('qa', 'Qatar'),
('re', 'Réunion'),
('ro', 'Romania'),
('ru', 'Russian Federation'),
('rw', 'Rwanda'),
('sa', 'Saudi Arabia'),
('sb', 'Solomon Islands'),
('sc', 'Seychelles'),
('sd', 'Sudan'),
('se', 'Sweden'),
('sg', 'Singapore'),
('sh', 'Saint Helena'),
('si', 'Slovenia'),
('sj', 'Svalbard and Jan Mayen'),
('sk', 'Slovakia'),
('sl', 'Sierra Leone'),
('sm', 'San Marino'),
('sn', 'Senegal'),
('so', 'Somalia'),
('sr', 'Suriname'),
('st', 'Sao Tome and Principe'),
('sv', 'El Salvador'),
('sy', 'Syria'),
('sz', 'Swaziland'),
('tc', 'Turks and Caicos Islands'),
('td', 'Chad'),
('tf', 'French Southern territories'),
('tg', 'Togo'),
('th', 'Thailand'),
('tj', 'Tajikistan'),
('tk', 'Tokelau'),
('tm', 'Turkmenistan'),
('tn', 'Tunisia'),
('to', 'Tonga'),
('tp', 'East Timor'),
('tr', 'Turkey'),
('tt', 'Trinidad and Tobago'),
('tv', 'Tuvalu'),
('tw', 'Taiwan'),
('tz', 'Tanzania'),
('ua', 'Ukraine'),
('ug', 'Uganda'),
('um', 'United States Minor Outlying Islands'),
('us', 'United States'),
('uy', 'Uruguay'),
('uz', 'Uzbekistan'),
('va', 'Holy See (Vatican City State)'),
('vc', 'Saint Vincent and the Grenadines'),
('ve', 'Venezuela'),
('vg', 'Virgin Islands, British'),
('vi', 'Virgin Islands, U.S.'),
('vn', 'Vietnam'),
('vu', 'Vanuatu'),
('wf', 'Wallis and Futuna'),
('ws', 'Samoa'),
('ye', 'Yemen'),
('yt', 'Mayotte'),
('yu', 'Yugoslavia'),
('za', 'South Africa'),
('zm', 'Zambia'),
('zw', 'Zimbabwe');

-- --------------------------------------------------------

--
-- Table structure for table `entries`
--

CREATE TABLE IF NOT EXISTS `entries` (
  `entryId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `feedId` int(10) unsigned NOT NULL,
  `entryTitle` varchar(255) NOT NULL,
  `entryContent` text NOT NULL,
  `entryAuthor` varchar(255) DEFAULT NULL,
  `entryDate` datetime NOT NULL,
  `entryUrl` varchar(255) NOT NULL,
  PRIMARY KEY (`entryId`),
  UNIQUE KEY `entryUrl` (`entryUrl`),
  KEY `feedId` (`feedId`),
  KEY `feedEntryTitle` (`entryTitle`),
  KEY `entryDate` (`entryDate`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=341096 ;

-- --------------------------------------------------------

--
-- Table structure for table `feeds`
--

CREATE TABLE IF NOT EXISTS `feeds` (
  `feedId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `feedName` varchar(255) NOT NULL,
  `feedUrl` varchar(255) NOT NULL,
  `feedLink` varchar(255) NOT NULL,
  `feedLastUpdate` datetime NOT NULL,
  `statusId` int(10) unsigned NOT NULL,
  `feedIcon` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`feedId`),
  UNIQUE KEY `feedUrl` (`feedUrl`),
  KEY `feedName` (`feedName`),
  KEY `feedLink` (`feedLink`),
  KEY `statusId` (`statusId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1634 ;

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE IF NOT EXISTS `files` (
  `fileId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fileName` varchar(255) NOT NULL,
  `fileTitle` varchar(100) NOT NULL,
  PRIMARY KEY (`fileId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE IF NOT EXISTS `groups` (
  `groupId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `groupName` char(255) DEFAULT NULL,
  `webSiteHome` char(255) NOT NULL,
  `systemGroup` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`groupId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`groupId`, `groupName`, `webSiteHome`, `systemGroup`) VALUES
(1, 'Usuario Anomino', '', 1),
(2, 'root', '', 1),
(3, 'default', '', 1);

-- --------------------------------------------------------

--
-- Table structure for table `groups_controllers`
--

CREATE TABLE IF NOT EXISTS `groups_controllers` (
  `groupId` int(10) unsigned NOT NULL DEFAULT '0',
  `controllerId` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`groupId`,`controllerId`) USING BTREE,
  KEY `functionId` (`controllerId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `groups_controllers`
--

INSERT INTO `groups_controllers` (`groupId`, `controllerId`) VALUES
(1, 1),
(2, 2),
(3, 2),
(2, 3),
(2, 4),
(2, 5),
(2, 6),
(2, 7),
(2, 8),
(2, 9),
(2, 10),
(3, 10),
(1, 11),
(2, 11),
(1, 12),
(2, 13),
(2, 14),
(2, 15),
(2, 16),
(2, 17),
(2, 18),
(2, 19),
(3, 19),
(2, 20),
(3, 20),
(2, 21),
(2, 22),
(1, 23),
(2, 23),
(3, 23);

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE IF NOT EXISTS `menu` (
  `menuId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `menuName` char(255) NOT NULL,
  `menuPosition` int(10) DEFAULT '0',
  `menuParentId` int(10) unsigned NOT NULL DEFAULT '0',
  `controllerId` int(10) unsigned DEFAULT NULL,
  `menuIcon` varchar(100) NOT NULL,
  PRIMARY KEY (`menuId`) USING BTREE,
  KEY `functionId` (`controllerId`),
  KEY `menuParentId` (`menuParentId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=24 ;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`menuId`, `menuName`, `menuPosition`, `menuParentId`, `controllerId`, `menuIcon`) VALUES
(1, 'menuAdmin', 2, 0, NULL, ''),
(2, 'Edit users', 1, 14, 3, ''),
(3, 'Edit controllers', 2, 14, 5, ''),
(4, 'Edit groups', 3, 14, 7, ''),
(5, 'Edit menu', 5, 14, 9, ''),
(6, 'Preferencias', 10, 10, NULL, 'icon-gear'),
(7, 'Edit entries', 2, 17, 15, ''),
(8, 'menuMain', 4, 0, NULL, ''),
(9, 'Edit feeds', 1, 17, 13, ''),
(10, 'menuProfile', 2, 0, NULL, ''),
(11, 'Profile', 1, 10, 10, 'icon-user'),
(12, 'Logout', 3, 10, 2, 'icon-off'),
(13, 'Login', 2, 10, 1, 'icon-signin'),
(14, 'System', 1, 6, NULL, ''),
(15, 'Signup', 1, 10, 12, 'icon-user'),
(16, 'Edit tags', 3, 17, 18, ''),
(17, 'Rss', 2, 6, NULL, ''),
(20, 'Import', 3, 6, NULL, ''),
(21, 'Import feeds', 1, 20, 19, ''),
(22, 'Import starred', 2, 20, 20, ''),
(23, 'Edit news', 4, 17, 21, '');

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE IF NOT EXISTS `news` (
  `newId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `newTitle` varchar(255) NOT NULL,
  `newContent` text NOT NULL,
  `userId` int(10) unsigned NOT NULL,
  `newDate` datetime NOT NULL,
  `newSef` varchar(255) NOT NULL,
  PRIMARY KEY (`newId`),
  KEY `userId` (`userId`),
  KEY `newSef` (`newSef`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`newId`, `newTitle`, `newContent`, `userId`, `newDate`, `newSef`) VALUES
(1, 'README - Clone Reader ', 'Clone of the old google reader. Reader of feeds, rss news.\nOpen source.\nImport subscriptions.xml and starred.json from google reader.\nRemote login.\nResponsive.\nRemote Storage.\nShare entries in social networks.\n\nSource: https://github.com/jcarle/cloneReader\n\nPowered with codeigniter, simplepie, jquery, bootstrap \n\n\n/*************************************************************************/\n\nClon de google reader. Lector de feeds, rss, noticias.\nOpen source.\nImporta subscriptions.xml y starred.json de google reader.\nLogin remoto.\nResponsive.\nRemote Storage.\nShare entries in social networks.\n\nSource: https://github.com/jcarle/cloneReader\n\n\nDesarrrollado con codeigniter, simplepie, jquery, bootstrap\n\n/*************************************************************************/\n\ndemo: http://www.clonereader.com.ar/\n', 2, '2013-11-14 14:23:00', 'readme_-_clone_reader_');

-- --------------------------------------------------------

--
-- Table structure for table `status`
--

CREATE TABLE IF NOT EXISTS `status` (
  `statusId` int(10) unsigned NOT NULL,
  `statusName` varchar(255) NOT NULL,
  PRIMARY KEY (`statusId`),
  KEY `statusName` (`statusName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `status`
--

INSERT INTO `status` (`statusId`, `statusName`) VALUES
(1, 'approved'),
(3, 'invalid format'),
(404, 'not found'),
(0, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE IF NOT EXISTS `tags` (
  `tagId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tagName` varchar(255) NOT NULL,
  PRIMARY KEY (`tagId`),
  KEY `tagName` (`tagName`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=69 ;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`tagId`, `tagName`) VALUES
(1, 'all'),
(3, 'home'),
(2, 'star');

-- --------------------------------------------------------

--
-- Table structure for table `tmp_users_entries`
--

CREATE TABLE IF NOT EXISTS `tmp_users_entries` (
  `userId` int(10) unsigned NOT NULL,
  `entryId` int(10) unsigned NOT NULL,
  `starred` int(1) unsigned NOT NULL,
  `entryRead` int(1) unsigned NOT NULL,
  PRIMARY KEY (`userId`,`entryId`),
  KEY `entryId` (`entryId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `userId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userEmail` char(255) NOT NULL,
  `userPassword` char(255) DEFAULT NULL,
  `userFirstName` char(255) DEFAULT NULL,
  `userLastName` char(255) DEFAULT NULL,
  `userBirthDate` date NOT NULL,
  `countryId` char(2) DEFAULT NULL,
  `userFilters` text NOT NULL COMMENT 'un json con los filtros aplicados',
  `facebookUserId` varchar(200) DEFAULT NULL,
  `googleUserId` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`userId`),
  UNIQUE KEY `userEmail` (`userEmail`),
  KEY `countryId` (`countryId`),
  KEY `googleUserId` (`googleUserId`),
  KEY `oauth_uid` (`facebookUserId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=27 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userId`, `userEmail`, `userPassword`, `userFirstName`, `userLastName`, `userBirthDate`, `countryId`, `userFilters`, `facebookUserId`, `googleUserId`) VALUES
(1, '1@', NULL, 'anonymous', 'anonymous', '0000-00-00', 'ar', '{"onlyUnread":true,"sortDesc":true,"id":"664","type":"feed","viewType":"detail","isMaximized":false}', NULL, NULL),
(2, 'admin@creader.com', '63a9f0ea7bb98050796b649e85481845', 'admin', 'person', '0000-00-00', 'ar', '{}', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users_entries`
--

CREATE TABLE IF NOT EXISTS `users_entries` (
  `userId` int(10) unsigned NOT NULL,
  `entryId` int(10) unsigned NOT NULL,
  `tagId` int(10) unsigned NOT NULL DEFAULT '0',
  `feedId` int(10) unsigned NOT NULL,
  `entryRead` int(1) unsigned NOT NULL,
  `entryDate` datetime NOT NULL,
  PRIMARY KEY (`userId`,`entryId`,`feedId`,`tagId`),
  KEY `entryId` (`entryId`),
  KEY `tagId` (`tagId`),
  KEY `feedId` (`feedId`),
  KEY `entryDate` (`entryDate`),
  KEY `indexUnread` (`userId`,`feedId`,`tagId`,`entryRead`),
  KEY `indexTag` (`userId`,`tagId`,`entryDate`),
  KEY `indexFeed` (`userId`,`feedId`,`entryDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users_feeds`
--

CREATE TABLE IF NOT EXISTS `users_feeds` (
  `userId` int(10) unsigned NOT NULL,
  `feedId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`userId`,`feedId`),
  KEY `feedId` (`feedId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users_feeds_tags`
--

CREATE TABLE IF NOT EXISTS `users_feeds_tags` (
  `userId` int(10) unsigned NOT NULL,
  `feedId` int(10) unsigned NOT NULL,
  `tagId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`userId`,`feedId`,`tagId`),
  KEY `feedId` (`feedId`),
  KEY `tagId` (`tagId`),
  KEY `indexUserIdFeedId` (`userId`,`feedId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users_groups`
--

CREATE TABLE IF NOT EXISTS `users_groups` (
  `userId` int(10) unsigned NOT NULL,
  `groupId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`userId`,`groupId`),
  KEY `groupId` (`groupId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users_groups`
--

INSERT INTO `users_groups` (`userId`, `groupId`) VALUES
(1, 1),
(2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `users_tags`
--

CREATE TABLE IF NOT EXISTS `users_tags` (
  `userId` int(10) unsigned NOT NULL,
  `tagId` int(10) unsigned NOT NULL,
  `expanded` int(1) NOT NULL,
  PRIMARY KEY (`userId`,`tagId`),
  KEY `expanded` (`expanded`),
  KEY `tagId` (`tagId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users_tags`
--

INSERT INTO `users_tags` (`userId`, `tagId`, `expanded`) VALUES
(1, 1, 1),
(2, 1, 1);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `entries`
--
ALTER TABLE `entries`
  ADD CONSTRAINT `entries_ibfk_1` FOREIGN KEY (`feedId`) REFERENCES `feeds` (`feedId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `feeds`
--
ALTER TABLE `feeds`
  ADD CONSTRAINT `feeds_ibfk_1` FOREIGN KEY (`statusId`) REFERENCES `status` (`statusId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `groups_controllers`
--
ALTER TABLE `groups_controllers`
  ADD CONSTRAINT `groups_controllers_ibfk_2` FOREIGN KEY (`controllerId`) REFERENCES `controllers` (`controllerId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `groups_controllers_ibfk_3` FOREIGN KEY (`groupId`) REFERENCES `groups` (`groupId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `groups_controllers_ibfk_5` FOREIGN KEY (`groupId`) REFERENCES `groups` (`groupId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `menu_ibfk_2` FOREIGN KEY (`controllerId`) REFERENCES `controllers` (`controllerId`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `news`
--
ALTER TABLE `news`
  ADD CONSTRAINT `news_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tmp_users_entries`
--
ALTER TABLE `tmp_users_entries`
  ADD CONSTRAINT `tmp_users_entries_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tmp_users_entries_ibfk_2` FOREIGN KEY (`entryId`) REFERENCES `entries` (`entryId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`countryId`) REFERENCES `countries` (`countryId`),
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`countryId`) REFERENCES `countries` (`countryId`);

--
-- Constraints for table `users_entries`
--
ALTER TABLE `users_entries`
  ADD CONSTRAINT `users_entries_ibfk_1` FOREIGN KEY (`entryId`) REFERENCES `entries` (`entryId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `users_entries_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `users_entries_ibfk_3` FOREIGN KEY (`tagId`) REFERENCES `tags` (`tagId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `users_entries_ibfk_4` FOREIGN KEY (`feedId`) REFERENCES `feeds` (`feedId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users_feeds`
--
ALTER TABLE `users_feeds`
  ADD CONSTRAINT `users_feeds_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `users_feeds_ibfk_2` FOREIGN KEY (`feedId`) REFERENCES `feeds` (`feedId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users_feeds_tags`
--
ALTER TABLE `users_feeds_tags`
  ADD CONSTRAINT `users_feeds_tags_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `users_feeds_tags_ibfk_2` FOREIGN KEY (`feedId`) REFERENCES `feeds` (`feedId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `users_feeds_tags_ibfk_3` FOREIGN KEY (`tagId`) REFERENCES `tags` (`tagId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users_groups`
--
ALTER TABLE `users_groups`
  ADD CONSTRAINT `users_groups_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `users_groups_ibfk_2` FOREIGN KEY (`groupId`) REFERENCES `groups` (`groupId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users_tags`
--
ALTER TABLE `users_tags`
  ADD CONSTRAINT `users_tags_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `users_tags_ibfk_2` FOREIGN KEY (`tagId`) REFERENCES `tags` (`tagId`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
