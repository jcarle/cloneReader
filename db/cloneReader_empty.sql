-- phpMyAdmin SQL Dump
-- version 4.0.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 17, 2015 at 10:29 AM
-- Server version: 5.5.38-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `cloneReader`
--
#CREATE DATABASE IF NOT EXISTS `cloneReader` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
#USE `cloneReader`;

DELIMITER $$
--
-- Functions
--
CREATE FUNCTION `countUnread`(_userId INT, _feedId INT, _tagId INT, _maxCount INT) RETURNS int(11)
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

CREATE FUNCTION `searchReplace`(`search` TEXT) RETURNS text CHARSET utf8
RETURN REPLACE(REPLACE(REPLACE(search , '+', 'plus'), '-', 'minus'), '&', 'ampersand')$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `coins`
--

CREATE TABLE IF NOT EXISTS `coins` (
  `currencyId` int(10) unsigned NOT NULL,
  `currencyName` varchar(3) NOT NULL,
  `currencyDesc` varchar(255) NOT NULL,
  PRIMARY KEY (`currencyId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `coins`
--

INSERT INTO `coins` (`currencyId`, `currencyName`, `currencyDesc`) VALUES
(1, 'AR$', 'Pesos Argentinos'),
(2, 'U$S', 'Dolares'),
(3, '€', 'Euros');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `controllers`
--

INSERT INTO `controllers` (`controllerId`, `controllerName`, `controllerUrl`, `controllerActive`) VALUES
(1, 'login', 'login', 1),
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
(19, 'import/feeds', 'import/feeds', 1),
(20, 'import/starred', 'import/starred', 1),
(21, 'news/listing', 'news', 1),
(22, 'news/edit', 'news/edit', 1),
(23, 'rss/index', 'rss', 1),
(24, 'langs/change/es', 'langs/change/es', 1),
(25, 'langs/change/en', 'langs/change/en', 1),
(26, 'langs/change/pt-br', 'langs/change/pt-br', 1),
(27, 'feedbacks/listing', 'feedbacks/listing', 1),
(28, 'feedbacks/edit', 'feedbacks/edit', 1),
(29, 'feedbacks/addFeedback', 'feedback', 1),
(30, 'profile/forgotPassword', 'forgotPassword', 1),
(31, 'help/keyboardShortcut', 'help/keyboardShortcut', 1),
(32, 'testing/listing', 'testing', 1),
(33, 'testing/edit', 'testing/edit', 1),
(34, 'users/logs', 'users/logs', 1),
(35, 'langs/change/zh-cn', 'langs/change/zh-cn', 1),
(36, 'about', 'about', 1),
(37, 'tasks/listing', 'tasks/listing', 1),
(38, 'tools/feeds', 'tools/feeds', 1),
(39, 'tools/tags', 'tools/tags', 1),
(40, 'process', 'process', 1);

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
-- Table structure for table `entities_files`
--

CREATE TABLE IF NOT EXISTS `entities_files` (
  `entityTypeId` int(10) unsigned NOT NULL,
  `entityId` int(10) unsigned NOT NULL,
  `fileId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`entityTypeId`,`entityId`,`fileId`),
  KEY `fileId` (`fileId`),
  KEY `entityTypeId` (`entityTypeId`),
  KEY `entityId` (`entityId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `entities_search`
--

CREATE TABLE IF NOT EXISTS `entities_search` (
  `entityTypeId` int(10) unsigned NOT NULL,
  `entityId` varchar(100) NOT NULL,
  `entityNameSearch` text NOT NULL,
  `entityFullSearch` text NOT NULL,
  `entityName` varchar(255) NOT NULL,
  `entityTree` text NOT NULL,
  `entityReverseTree` text NOT NULL,
  PRIMARY KEY (`entityTypeId`,`entityId`),
  KEY `entityTypeId` (`entityTypeId`),
  FULLTEXT KEY `entityFullSearch` (`entityFullSearch`),
  FULLTEXT KEY `entityNameSearch` (`entityNameSearch`),
  FULLTEXT KEY `entityNameSearch_2` (`entityNameSearch`,`entityFullSearch`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `entities_type`
--

CREATE TABLE IF NOT EXISTS `entities_type` (
  `entityTypeId` int(10) unsigned NOT NULL,
  `entityTypeName` varchar(255) NOT NULL,
  PRIMARY KEY (`entityTypeId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `entities_type`
--

INSERT INTO `entities_type` (`entityTypeId`, `entityTypeName`) VALUES
(1, 'testing'),
(5, 'feeds'),
(6, 'tags'),
(7, 'users'),
(8, 'entries');

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
  `lastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`entryId`),
  UNIQUE KEY `indexFeedIdEntryUrl` (`feedId`,`entryUrl`),
  KEY `feedId` (`feedId`),
  KEY `entryDate` (`entryDate`),
  KEY `indexFeedIdEntryDate` (`feedId`,`entryDate`),
  KEY `entryUrl` (`entryUrl`),
  KEY `lastUpdate` (`lastUpdate`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `entries_tags`
--

CREATE TABLE IF NOT EXISTS `entries_tags` (
  `entryId` int(10) unsigned NOT NULL,
  `tagId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`entryId`,`tagId`),
  KEY `tagId` (`tagId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `feedbacks`
--

CREATE TABLE IF NOT EXISTS `feedbacks` (
  `feedbackId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `feedbackUserName` varchar(255) NOT NULL,
  `feedbackUserEmail` varchar(255) NOT NULL,
  `feedbackTitle` varchar(255) NOT NULL,
  `feedbackDesc` text NOT NULL,
  `feedbackDate` datetime NOT NULL,
  `userId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`feedbackId`),
  KEY `commentEmail` (`feedbackUserEmail`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `feeds`
--

CREATE TABLE IF NOT EXISTS `feeds` (
  `feedId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `feedName` varchar(255) NOT NULL,
  `feedDescription` varchar(255) DEFAULT NULL,
  `feedUrl` varchar(255) NOT NULL,
  `feedLink` varchar(255) NOT NULL,
  `feedLastScan` datetime NOT NULL,
  `feedLastEntryDate` datetime NOT NULL,
  `statusId` int(10) unsigned NOT NULL,
  `feedIcon` varchar(255) DEFAULT NULL,
  `langId` varchar(10) DEFAULT NULL,
  `countryId` char(2) DEFAULT NULL,
  `feedSuggest` int(1) unsigned NOT NULL DEFAULT '1',
  `fixLocale` int(1) unsigned NOT NULL DEFAULT '0',
  `feedMaxRetries` int(10) unsigned NOT NULL DEFAULT '0',
  `feedCountUsers` int(10) NOT NULL,
  `feedCountEntries` int(10) NOT NULL,
  `lastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`feedId`),
  UNIQUE KEY `feedUrl` (`feedUrl`),
  KEY `feedName` (`feedName`),
  KEY `feedLink` (`feedLink`),
  KEY `statusId` (`statusId`),
  KEY `langId` (`langId`),
  KEY `countryId` (`countryId`),
  KEY `feedSuggest` (`feedSuggest`),
  KEY `fixLocale` (`fixLocale`),
  KEY `feedMaxRetries` (`feedMaxRetries`),
  KEY `feedCountUsers` (`feedCountUsers`),
  KEY `feedCountEntries` (`feedCountEntries`),
  KEY `lastUpdate` (`lastUpdate`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Triggers `feeds`
--
DROP TRIGGER IF EXISTS `changeFeedName`;
DELIMITER //
CREATE TRIGGER `changeFeedName` BEFORE UPDATE ON `feeds`
 FOR EACH ROW IF (NEW.feedName <> OLD.feedName)  THEN
	SET NEW.lastUpdate = NOW();
END IF
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `feeds_tags`
--

CREATE TABLE IF NOT EXISTS `feeds_tags` (
  `feedId` int(10) unsigned NOT NULL,
  `tagId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`feedId`,`tagId`),
  KEY `feedId` (`feedId`),
  KEY `tagId` (`tagId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE IF NOT EXISTS `files` (
  `fileId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fileName` varchar(255) NOT NULL,
  `fileTitle` varchar(100) NOT NULL,
  PRIMARY KEY (`fileId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE IF NOT EXISTS `groups` (
  `groupId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `groupName` char(255) DEFAULT NULL,
  `groupHomePage` char(255) NOT NULL,
  `systemGroup` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`groupId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`groupId`, `groupName`, `groupHomePage`, `systemGroup`) VALUES
(1, 'anonymous', '', 1),
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
(3, 23),
(1, 24),
(2, 24),
(3, 24),
(1, 25),
(2, 25),
(3, 25),
(1, 26),
(2, 26),
(3, 26),
(2, 27),
(2, 28),
(1, 29),
(2, 29),
(3, 29),
(1, 30),
(1, 31),
(2, 31),
(3, 31),
(2, 32),
(2, 33),
(2, 34),
(1, 35),
(2, 35),
(3, 35),
(1, 36),
(2, 36),
(3, 36),
(2, 37),
(1, 38),
(2, 38),
(3, 38),
(1, 39),
(2, 39),
(3, 39),
(2, 40);

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE IF NOT EXISTS `languages` (
  `langId` varchar(10) NOT NULL,
  `langName` varchar(255) NOT NULL,
  PRIMARY KEY (`langId`),
  KEY `langName` (`langName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `languages`
--

INSERT INTO `languages` (`langId`, `langName`) VALUES
('af', 'Afrikaans'),
('sq', 'Albanian'),
('ar-dz', 'Arabic (Algeria)'),
('ar-bh', 'Arabic (Bahrain)'),
('ar-eg', 'Arabic (Egypt)'),
('ar-iq', 'Arabic (Iraq)'),
('ar-jo', 'Arabic (Jordan)'),
('ar-kw', 'Arabic (Kuwait)'),
('ar-lb', 'Arabic (Lebanon)'),
('ar-ly', 'Arabic (Libya)'),
('ar-ma', 'Arabic (Morocco)'),
('ar-om', 'Arabic (Oman)'),
('ar-qa', 'Arabic (Qatar)'),
('ar-sa', 'Arabic (Saudi Arabia)'),
('ar-sy', 'Arabic (Syria)'),
('ar-tn', 'Arabic (Tunisia)'),
('ar-ae', 'Arabic (U.A.E.)'),
('ar-ye', 'Arabic (Yemen)'),
('eu', 'Basque'),
('be', 'Belarusian'),
('bg', 'Bulgarian'),
('ca', 'Catalan'),
('zh-hk', 'Chinese (Hong Kong)'),
('zh-cn', 'Chinese (PRC)'),
('zh-sg', 'Chinese (Singapore)'),
('zh-tw', 'Chinese (Taiwan)'),
('hr', 'Croatian'),
('cs', 'Czech'),
('da', 'Danish'),
('nl-be', 'Dutch (Belgium)'),
('nl', 'Dutch (Standard)'),
('en', 'English'),
('en-au', 'English (Australia)'),
('en-bz', 'English (Belize)'),
('en-ca', 'English (Canada)'),
('en-ie', 'English (Ireland)'),
('en-jm', 'English (Jamaica)'),
('en-nz', 'English (New Zealand)'),
('en-za', 'English (South Africa)'),
('en-tt', 'English (Trinidad)'),
('en-gb', 'English (United Kingdom)'),
('en-us', 'English (United States)'),
('et', 'Estonian'),
('fo', 'Faeroese'),
('fa', 'Farsi'),
('fi', 'Finnish'),
('fr-be', 'French (Belgium)'),
('fr-ca', 'French (Canada)'),
('fr-lu', 'French (Luxembourg)'),
('fr', 'French (Standard)'),
('fr-ch', 'French (Switzerland)'),
('gd', 'Gaelic (Scotland)'),
('de-at', 'German (Austria)'),
('de-li', 'German (Liechtenstein)'),
('de-lu', 'German (Luxembourg)'),
('de', 'German (Standard)'),
('de-ch', 'German (Switzerland)'),
('el', 'Greek'),
('he', 'Hebrew'),
('hi', 'Hindi'),
('hu', 'Hungarian'),
('is', 'Icelandic'),
('id', 'Indonesian'),
('ga', 'Irish'),
('it', 'Italian (Standard)'),
('it-ch', 'Italian (Switzerland)'),
('ja', 'Japanese'),
('ko', 'Korean'),
('ku', 'Kurdish'),
('lv', 'Latvian'),
('lt', 'Lithuanian'),
('mk', 'Macedonian (FYROM)'),
('ml', 'Malayalam'),
('ms', 'Malaysian'),
('mt', 'Maltese'),
('no', 'Norwegian'),
('nb', 'Norwegian (Bokmål)'),
('nn', 'Norwegian (Nynorsk)'),
('pl', 'Polish'),
('pt-br', 'Portuguese (Brazil)'),
('pt', 'Portuguese (Portugal)'),
('pa', 'Punjabi'),
('rm', 'Rhaeto-Romanic'),
('ro', 'Romanian'),
('ro-md', 'Romanian (Republic of Moldova)'),
('ru', 'Russian'),
('ru-md', 'Russian (Republic of Moldova)'),
('sr', 'Serbian'),
('sk', 'Slovak'),
('sl', 'Slovenian'),
('sb', 'Sorbian'),
('es-ar', 'Spanish (Argentina)'),
('es-bo', 'Spanish (Bolivia)'),
('es-cl', 'Spanish (Chile)'),
('es-co', 'Spanish (Colombia)'),
('es-cr', 'Spanish (Costa Rica)'),
('es-do', 'Spanish (Dominican Republic)'),
('es-ec', 'Spanish (Ecuador)'),
('es-sv', 'Spanish (El Salvador)'),
('es-gt', 'Spanish (Guatemala)'),
('es-hn', 'Spanish (Honduras)'),
('es-mx', 'Spanish (Mexico)'),
('es-ni', 'Spanish (Nicaragua)'),
('es-pa', 'Spanish (Panama)'),
('es-py', 'Spanish (Paraguay)'),
('es-pe', 'Spanish (Peru)'),
('es-pr', 'Spanish (Puerto Rico)'),
('es', 'Spanish (Spain)'),
('es-uy', 'Spanish (Uruguay)'),
('es-ve', 'Spanish (Venezuela)'),
('sv', 'Swedish'),
('sv-fi', 'Swedish (Finland)'),
('th', 'Thai'),
('ts', 'Tsonga'),
('tn', 'Tswana'),
('tr', 'Turkish'),
('uk', 'Ukrainian'),
('ur', 'Urdu'),
('ve', 'Venda'),
('vi', 'Vietnamese'),
('xh', 'Xhosa'),
('ji', 'Yiddish'),
('zu', 'Zulu');

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
  `menuClassName` varchar(50) NOT NULL,
  `menuTranslate` int(11) NOT NULL,
  `menuDividerBefore` int(1) NOT NULL,
  `menuDividerAfter` int(1) NOT NULL,
  PRIMARY KEY (`menuId`) USING BTREE,
  KEY `functionId` (`controllerId`),
  KEY `menuParentId` (`menuParentId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`menuId`, `menuName`, `menuPosition`, `menuParentId`, `controllerId`, `menuIcon`, `menuClassName`, `menuTranslate`, `menuDividerBefore`, `menuDividerAfter`) VALUES
(1, 'menuAdmin', 2, 0, NULL, '', '', 1, 0, 0),
(2, 'Edit users', 1, 14, 3, '', '', 1, 0, 0),
(3, 'Edit controllers', 2, 14, 5, '', '', 1, 0, 0),
(4, 'Edit groups', 3, 14, 7, '', '', 1, 0, 0),
(5, 'Edit menu', 5, 14, 9, '', '', 1, 0, 0),
(6, 'Settings', 10, 10, NULL, 'fa fa-gear fa-bars', 'menuItemSettings', 1, 0, 0),
(7, 'Edit entries', 2, 17, 15, '', '', 1, 0, 0),
(8, 'menuMain', 4, 0, NULL, '', '', 1, 0, 0),
(9, 'Edit feeds', 1, 17, 13, '', '', 1, 0, 0),
(10, 'menuProfile', 2, 0, NULL, '', '', 1, 0, 0),
(11, 'Profile', 2, 10, 10, 'fa fa-user', '', 1, 0, 0),
(12, 'Logout', 999, 6, 2, 'fa fa-power-off', '', 1, 1, 0),
(13, 'Login', 2, 10, 1, 'fa fa-sign-in', '', 1, 0, 0),
(14, 'System', 1, 6, NULL, '', '', 1, 0, 1),
(15, 'Signup', 1, 10, 12, 'fa fa-user', '', 1, 0, 0),
(16, 'Edit tags', 3, 17, 18, '', '', 1, 0, 0),
(17, 'Rss', 2, 6, NULL, '', '', 1, 0, 0),
(20, 'Import', 4, 6, NULL, '', '', 1, 0, 0),
(21, 'Import feeds', 1, 20, 19, '', '', 1, 0, 0),
(22, 'Import starred', 2, 20, 20, '', '', 1, 0, 0),
(23, 'Edit news', 4, 17, 21, '', '', 1, 0, 0),
(24, 'Language', 9, 10, NULL, 'fa fa-flag-o', '', 1, 0, 0),
(25, 'English', 1, 24, 25, 'lang-en', '', 0, 0, 0),
(26, 'Español', 2, 24, 24, 'lang-es', '', 0, 0, 0),
(27, 'Português', 3, 24, 26, 'lang-pt-br', '', 0, 0, 0),
(28, 'Feedback', 3, 10, 29, 'fa fa-comment', '', 1, 0, 0),
(29, 'Edit feedbacks', 5, 17, 27, '', '', 1, 0, 0),
(30, 'Edit testing', 200, 14, 32, '', '', 1, 0, 0),
(31, '中国', 4, 24, 35, 'lang-zh-cn', '', 0, 0, 0),
(32, 'About', 10, 6, 36, 'menuItemAbout', '', 1, 1, 0),
(33, 'Edit tasks', 9, 14, 37, '', '', 1, 0, 0),
(34, 'Tools', 3, 6, NULL, '', '', 1, 0, 0),
(35, 'Admin feeds', 0, 34, 38, '', '', 1, 0, 0),
(36, 'Admin tags', 2, 34, 39, '', '', 1, 0, 0),
(37, 'Process', 10, 14, 40, '', '', 1, 0, 0);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `process`
--

CREATE TABLE IF NOT EXISTS `process` (
  `processName` varchar(255) NOT NULL,
  `lastUpdate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`processName`),
  KEY `lastUpdate` (`lastUpdate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `process`
--

INSERT INTO `process` (`processName`, `lastUpdate`) VALUES
('saveEntriesSearch', '2015-06-16 16:15:56'),
('saveFeedsSearch', '2015-06-16 16:15:56'),
('saveTagsSearch', '2015-06-16 16:15:56'),
('saveUsersSearch', '2015-06-16 16:15:56');

-- --------------------------------------------------------

--
-- Table structure for table `shared_by_email`
--

CREATE TABLE IF NOT EXISTS `shared_by_email` (
  `shareByEmailId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL,
  `entryId` int(10) unsigned NOT NULL,
  `userFriendId` int(10) unsigned NOT NULL,
  `shareByEmailDate` datetime NOT NULL,
  `shareByEmailComment` text NOT NULL,
  PRIMARY KEY (`shareByEmailId`),
  KEY `userId` (`userId`),
  KEY `entryId` (`entryId`),
  KEY `userFriendId` (`userFriendId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `states`
--

CREATE TABLE IF NOT EXISTS `states` (
  `stateId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `stateName` varchar(255) NOT NULL,
  `countryId` char(2) NOT NULL,
  PRIMARY KEY (`stateId`),
  KEY `countryId` (`countryId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `states`
--

INSERT INTO `states` (`stateId`, `stateName`, `countryId`) VALUES
(1, 'Kabol', 'af'),
(2, 'Qandahar', 'af'),
(3, 'Herat', 'af'),
(4, 'Balkh', 'af'),
(5, 'Noord-Holland', 'nl'),
(6, 'Zuid-Holland', 'nl'),
(7, 'Utrecht', 'nl'),
(8, 'Noord-Brabant', 'nl'),
(9, 'Groningen', 'nl'),
(10, 'Gelderland', 'nl'),
(11, 'Overijssel', 'nl'),
(12, 'Flevoland', 'nl'),
(13, 'Limburg', 'nl'),
(14, 'Drenthe', 'nl'),
(15, 'Curaçao', 'an'),
(16, 'Tirana', 'al'),
(17, 'Alger', 'dz'),
(18, 'Oran', 'dz'),
(19, 'Constantine', 'dz'),
(20, 'Annaba', 'dz'),
(21, 'Batna', 'dz'),
(22, 'Sétif', 'dz'),
(23, 'Sidi Bel Abbès', 'dz'),
(24, 'Skikda', 'dz'),
(25, 'Biskra', 'dz'),
(26, 'Blida', 'dz'),
(27, 'Béjaïa', 'dz'),
(28, 'Mostaganem', 'dz'),
(29, 'Tébessa', 'dz'),
(30, 'Tlemcen', 'dz'),
(31, 'Béchar', 'dz'),
(32, 'Tiaret', 'dz'),
(33, 'Chlef', 'dz'),
(34, 'Ghardaïa', 'dz'),
(35, 'Tutuila', 'as'),
(36, 'Andorra la Vella', 'ad'),
(37, 'Luanda', 'ao'),
(38, 'Huambo', 'ao'),
(39, 'Benguela', 'ao'),
(40, 'Namibe', 'ao'),
(41, '–', 'ai'),
(42, 'St John', 'ag'),
(43, 'Dubai', 'ae'),
(44, 'Abu Dhabi', 'ae'),
(45, 'Sharja', 'ae'),
(46, 'Ajman', 'ae'),
(47, 'Distrito Federal', 'ar'),
(48, 'Buenos Aires', 'ar'),
(49, 'Córdoba', 'ar'),
(50, 'Santa Fé', 'ar'),
(51, 'Tucumán', 'ar'),
(52, 'Salta', 'ar'),
(53, 'Corrientes', 'ar'),
(54, 'Chaco', 'ar'),
(55, 'Entre Rios', 'ar'),
(56, 'Mendoza', 'ar'),
(57, 'Misiones', 'ar'),
(58, 'Santiago del Estero', 'ar'),
(59, 'Jujuy', 'ar'),
(60, 'Neuquén', 'ar'),
(61, 'Formosa', 'ar'),
(62, 'La Rioja', 'ar'),
(63, 'Catamarca', 'ar'),
(64, 'Chubut', 'ar'),
(65, 'San Juan', 'ar'),
(66, 'San Luis', 'ar'),
(67, 'Yerevan', 'am'),
(68, 'Širak', 'am'),
(69, 'Lori', 'am'),
(70, '–', 'aw'),
(71, 'New South Wales', 'au'),
(72, 'Victoria', 'au'),
(73, 'Queensland', 'au'),
(74, 'West Australia', 'au'),
(75, 'South Australia', 'au'),
(76, 'Capital Region', 'au'),
(77, 'Tasmania', 'au'),
(78, 'Baki', 'az'),
(79, 'Gäncä', 'az'),
(80, 'Sumqayit', 'az'),
(81, 'Mingäçevir', 'az'),
(82, 'New Providence', 'bs'),
(83, 'al-Manama', 'bh'),
(84, 'Dhaka', 'bd'),
(85, 'Chittagong', 'bd'),
(86, 'Khulna', 'bd'),
(87, 'Rajshahi', 'bd'),
(88, 'Barisal', 'bd'),
(89, 'Sylhet', 'bd'),
(90, 'St Michael', 'bb'),
(91, 'Antwerpen', 'be'),
(92, 'East Flanderi', 'be'),
(93, 'Hainaut', 'be'),
(94, 'Liège', 'be'),
(95, 'Bryssel', 'be'),
(96, 'West Flanderi', 'be'),
(97, 'Namur', 'be'),
(98, 'Belize City', 'bz'),
(99, 'Cayo', 'bz'),
(100, 'Atlantique', 'bj'),
(101, 'Ouémé', 'bj'),
(102, 'Atacora', 'bj'),
(103, 'Borgou', 'bj'),
(104, 'Saint George´s', 'bm'),
(105, 'Hamilton', 'bm'),
(106, 'Thimphu', 'bt'),
(107, 'Santa Cruz', 'bo'),
(108, 'La Paz', 'bo'),
(109, 'Cochabamba', 'bo'),
(110, 'Oruro', 'bo'),
(111, 'Chuquisaca', 'bo'),
(112, 'Potosí', 'bo'),
(113, 'Tarija', 'bo'),
(114, 'Federaatio', 'ba'),
(115, 'Republika Srpska', 'ba'),
(116, 'Gaborone', 'bw'),
(117, 'Francistown', 'bw'),
(118, 'São Paulo', 'br'),
(119, 'Rio de Janeiro', 'br'),
(120, 'Bahia', 'br'),
(121, 'Minas Gerais', 'br'),
(122, 'Ceará', 'br'),
(123, 'Distrito Federal', 'br'),
(124, 'Paraná', 'br'),
(125, 'Pernambuco', 'br'),
(126, 'Rio Grande do Sul', 'br'),
(127, 'Amazonas', 'br'),
(128, 'Pará', 'br'),
(129, 'Goiás', 'br'),
(130, 'Maranhão', 'br'),
(131, 'Alagoas', 'br'),
(132, 'Piauí', 'br'),
(133, 'Rio Grande do Norte', 'br'),
(134, 'Mato Grosso do Sul', 'br'),
(135, 'Paraíba', 'br'),
(136, 'Mato Grosso', 'br'),
(137, 'Sergipe', 'br'),
(138, 'Santa Catarina', 'br'),
(139, 'Espírito Santo', 'br'),
(140, 'Rondônia', 'br'),
(141, 'Acre', 'br'),
(142, 'Amapá', 'br'),
(143, 'Roraima', 'br'),
(144, 'Tocantins', 'br'),
(145, 'England', 'gb'),
(146, 'Scotland', 'gb'),
(147, 'Wales', 'gb'),
(148, 'North Ireland', 'gb'),
(149, 'Jersey', 'gb'),
(150, '–', 'gb'),
(151, 'Tortola', 'vg'),
(152, 'Brunei and Muara', 'bn'),
(153, 'Grad Sofija', 'bg'),
(154, 'Plovdiv', 'bg'),
(155, 'Varna', 'bg'),
(156, 'Burgas', 'bg'),
(157, 'Ruse', 'bg'),
(158, 'Haskovo', 'bg'),
(159, 'Lovec', 'bg'),
(160, 'Kadiogo', 'bf'),
(161, 'Houet', 'bf'),
(162, 'Boulkiemdé', 'bf'),
(163, 'Bujumbura', 'bi'),
(164, 'Grand Cayman', 'ky'),
(165, 'Santiago', 'cl'),
(166, 'Valparaíso', 'cl'),
(167, 'Bíobío', 'cl'),
(168, 'Antofagasta', 'cl'),
(169, 'La Araucanía', 'cl'),
(170, 'O´Higgins', 'cl'),
(171, 'Tarapacá', 'cl'),
(172, 'Maule', 'cl'),
(173, 'Los Lagos', 'cl'),
(174, 'Coquimbo', 'cl'),
(175, 'Magallanes', 'cl'),
(176, 'Atacama', 'cl'),
(177, 'Rarotonga', 'ck'),
(178, 'San José', 'cr'),
(179, 'Djibouti', 'dj'),
(180, 'St George', 'dm'),
(181, 'Distrito Nacional', 'do'),
(182, 'Santiago', 'do'),
(183, 'La Romana', 'do'),
(184, 'San Pedro de Macorís', 'do'),
(185, 'Duarte', 'do'),
(186, 'Puerto Plata', 'do'),
(187, 'Guayas', 'ec'),
(188, 'Pichincha', 'ec'),
(189, 'Azuay', 'ec'),
(190, 'El Oro', 'ec'),
(191, 'Manabí', 'ec'),
(192, 'Tungurahua', 'ec'),
(193, 'Imbabura', 'ec'),
(194, 'Los Ríos', 'ec'),
(195, 'Loja', 'ec'),
(196, 'Chimborazo', 'ec'),
(197, 'Esmeraldas', 'ec'),
(198, 'Kairo', 'eg'),
(199, 'Aleksandria', 'eg'),
(200, 'Giza', 'eg'),
(201, 'al-Qalyubiya', 'eg'),
(202, 'Port Said', 'eg'),
(203, 'Suez', 'eg'),
(204, 'al-Gharbiya', 'eg'),
(205, 'al-Daqahliya', 'eg'),
(206, 'Luxor', 'eg'),
(207, 'Asyut', 'eg'),
(208, 'al-Sharqiya', 'eg'),
(209, 'al-Faiyum', 'eg'),
(210, 'Ismailia', 'eg'),
(211, 'al-Buhayra', 'eg'),
(212, 'Assuan', 'eg'),
(213, 'al-Minya', 'eg'),
(214, 'Bani Suwayf', 'eg'),
(215, 'Qina', 'eg'),
(216, 'Sawhaj', 'eg'),
(217, 'al-Minufiya', 'eg'),
(218, 'Kafr al-Shaykh', 'eg'),
(219, 'Shamal Sina', 'eg'),
(220, 'San Salvador', 'sv'),
(221, 'Santa Ana', 'sv'),
(222, 'San Miguel', 'sv'),
(223, 'La Libertad', 'sv'),
(224, 'Maekel', 'er'),
(225, 'Madrid', 'es'),
(226, 'Katalonia', 'es'),
(227, 'Valencia', 'es'),
(228, 'Andalusia', 'es'),
(229, 'Aragonia', 'es'),
(230, 'Baskimaa', 'es'),
(231, 'Canary Islands', 'es'),
(232, 'Murcia', 'es'),
(233, 'Balears', 'es'),
(234, 'Castilla and León', 'es'),
(235, 'Galicia', 'es'),
(236, 'Asturia', 'es'),
(237, 'Cantabria', 'es'),
(238, 'Navarra', 'es'),
(239, 'Kastilia-La Mancha', 'es'),
(240, 'Extremadura', 'es'),
(241, 'La Rioja', 'es'),
(242, 'Western Cape', 'za'),
(243, 'Gauteng', 'za'),
(244, 'Eastern Cape', 'za'),
(245, 'KwaZulu-Natal', 'za'),
(246, 'Free State', 'za'),
(247, 'North West', 'za'),
(248, 'Northern Cape', 'za'),
(249, 'Mpumalanga', 'za'),
(250, 'Addis Abeba', 'et'),
(251, 'Dire Dawa', 'et'),
(252, 'Oromia', 'et'),
(253, 'Amhara', 'et'),
(254, 'Tigray', 'et'),
(255, 'East Falkland', 'fk'),
(256, 'Central', 'fj'),
(257, 'National Capital Reg', 'ph'),
(258, 'Southern Mindanao', 'ph'),
(259, 'Central Visayas', 'ph'),
(260, 'Western Mindanao', 'ph'),
(261, 'Southern Tagalog', 'ph'),
(262, 'Northern Mindanao', 'ph'),
(263, 'Western Visayas', 'ph'),
(264, 'Central Luzon', 'ph'),
(265, 'Central Mindanao', 'ph'),
(266, 'Caraga', 'ph'),
(267, 'CAR', 'ph'),
(268, 'Eastern Visayas', 'ph'),
(269, 'Bicol', 'ph'),
(270, 'Ilocos', 'ph'),
(271, 'Cagayan Valley', 'ph'),
(272, 'ARMM', 'ph'),
(273, 'Streymoyar', 'fo'),
(274, 'Estuaire', 'ga'),
(275, 'Kombo St Mary', 'gm'),
(276, 'Banjul', 'gm'),
(277, 'Tbilisi', 'ge'),
(278, 'Imereti', 'ge'),
(279, 'Kvemo Kartli', 'ge'),
(280, 'Adzaria [Atšara]', 'ge'),
(281, 'Abhasia [Aphazeti]', 'ge'),
(282, 'Greater Accra', 'gh'),
(283, 'Ashanti', 'gh'),
(284, 'Northern', 'gh'),
(285, 'Western', 'gh'),
(286, '–', 'gi'),
(287, 'St George', 'gd'),
(288, 'Kitaa', 'gl'),
(289, 'Grande-Terre', 'gp'),
(290, 'Basse-Terre', 'gp'),
(291, '–', 'gu'),
(292, 'Guatemala', 'gt'),
(293, 'Quetzaltenango', 'gt'),
(294, 'Conakry', 'gn'),
(295, 'Bissau', 'gw'),
(296, 'Georgetown', 'gy'),
(297, 'Ouest', 'ht'),
(298, 'Nord', 'ht'),
(299, 'Distrito Central', 'hn'),
(300, 'Cortés', 'hn'),
(301, 'Atlántida', 'hn'),
(302, 'Kowloon and New Kowl', 'hk'),
(303, 'Hongkong', 'hk'),
(304, 'Länsimaa', 'sj'),
(305, 'Jakarta Raya', 'id'),
(306, 'East Java', 'id'),
(307, 'West Java', 'id'),
(308, 'Sumatera Utara', 'id'),
(309, 'Sumatera Selatan', 'id'),
(310, 'Central Java', 'id'),
(311, 'Sulawesi Selatan', 'id'),
(312, 'Lampung', 'id'),
(313, 'Sumatera Barat', 'id'),
(314, 'Kalimantan Selatan', 'id'),
(315, 'Riau', 'id'),
(316, 'Bali', 'id'),
(317, 'Yogyakarta', 'id'),
(318, 'Kalimantan Barat', 'id'),
(319, 'Kalimantan Timur', 'id'),
(320, 'Jambi', 'id'),
(321, 'Sulawesi Utara', 'id'),
(322, 'Nusa Tenggara Barat', 'id'),
(323, 'Molukit', 'id'),
(324, 'Bengkulu', 'id'),
(325, 'Aceh', 'id'),
(326, 'Sulawesi Tengah', 'id'),
(327, 'Nusa Tenggara Timur', 'id'),
(328, 'Kalimantan Tengah', 'id'),
(329, 'Sulawesi Tenggara', 'id'),
(330, 'West Irian', 'id'),
(331, 'Maharashtra', 'in'),
(332, 'Delhi', 'in'),
(333, 'West Bengali', 'in'),
(334, 'Tamil Nadu', 'in'),
(335, 'Andhra Pradesh', 'in'),
(336, 'Gujarat', 'in'),
(337, 'Karnataka', 'in'),
(338, 'Uttar Pradesh', 'in'),
(339, 'Rajasthan', 'in'),
(340, 'Madhya Pradesh', 'in'),
(341, 'Punjab', 'in'),
(342, 'Bihar', 'in'),
(343, 'Jammu and Kashmir', 'in'),
(344, 'Haryana', 'in'),
(345, 'Jharkhand', 'in'),
(346, 'Assam', 'in'),
(347, 'Kerala', 'in'),
(348, 'Chandigarh', 'in'),
(349, 'Chhatisgarh', 'in'),
(350, 'Orissa', 'in'),
(351, 'Uttaranchal', 'in'),
(352, 'Pondicherry', 'in'),
(353, 'Manipur', 'in'),
(354, 'Tripura', 'in'),
(355, 'Mizoram', 'in'),
(356, 'Meghalaya', 'in'),
(357, 'Baghdad', 'iq'),
(358, 'Ninawa', 'iq'),
(359, 'Irbil', 'iq'),
(360, 'al-Tamim', 'iq'),
(361, 'Basra', 'iq'),
(362, 'al-Sulaymaniya', 'iq'),
(363, 'al-Najaf', 'iq'),
(364, 'Karbala', 'iq'),
(365, 'Babil', 'iq'),
(366, 'DhiQar', 'iq'),
(367, 'Maysan', 'iq'),
(368, 'al-Qadisiya', 'iq'),
(369, 'al-Anbar', 'iq'),
(370, 'Wasit', 'iq'),
(371, 'Diyala', 'iq'),
(372, 'Teheran', 'ir'),
(373, 'Khorasan', 'ir'),
(374, 'Esfahan', 'ir'),
(375, 'East Azerbaidzan', 'ir'),
(376, 'Fars', 'ir'),
(377, 'Khuzestan', 'ir'),
(378, 'Qom', 'ir'),
(379, 'Kermanshah', 'ir'),
(380, 'West Azerbaidzan', 'ir'),
(381, 'Sistan va Baluchesta', 'ir'),
(382, 'Gilan', 'ir'),
(383, 'Hamadan', 'ir'),
(384, 'Kerman', 'ir'),
(385, 'Markazi', 'ir'),
(386, 'Ardebil', 'ir'),
(387, 'Yazd', 'ir'),
(388, 'Qazvin', 'ir'),
(389, 'Zanjan', 'ir'),
(390, 'Kordestan', 'ir'),
(391, 'Hormozgan', 'ir'),
(392, 'Lorestan', 'ir'),
(393, 'Mazandaran', 'ir'),
(394, 'Golestan', 'ir'),
(395, 'Bushehr', 'ir'),
(396, 'Ilam', 'ir'),
(397, 'Semnan', 'ir'),
(398, 'Chaharmahal va Bakht', 'ir'),
(399, 'Leinster', 'ie'),
(400, 'Munster', 'ie'),
(401, 'Höfuðborgarsvæði', 'is'),
(402, 'Jerusalem', 'il'),
(403, 'Tel Aviv', 'il'),
(404, 'Haifa', 'il'),
(405, 'Ha Merkaz', 'il'),
(406, 'Ha Darom', 'il'),
(407, 'Latium', 'it'),
(408, 'Lombardia', 'it'),
(409, 'Campania', 'it'),
(410, 'Piemonte', 'it'),
(411, 'Sisilia', 'it'),
(412, 'Liguria', 'it'),
(413, 'Emilia-Romagna', 'it'),
(414, 'Toscana', 'it'),
(415, 'Apulia', 'it'),
(416, 'Veneto', 'it'),
(417, 'Friuli-Venezia Giuli', 'it'),
(418, 'Calabria', 'it'),
(419, 'Sardinia', 'it'),
(420, 'Umbria', 'it'),
(421, 'Abruzzit', 'it'),
(422, 'Trentino-Alto Adige', 'it'),
(423, 'Marche', 'it'),
(424, 'Dili', 'tp'),
(425, 'Wien', 'at'),
(426, 'Steiermark', 'at'),
(427, 'North Austria', 'at'),
(428, 'Salzburg', 'at'),
(429, 'Tiroli', 'at'),
(430, 'Kärnten', 'at'),
(431, 'St. Catherine', 'jm'),
(432, 'St. Andrew', 'jm'),
(433, 'Tokyo-to', 'jp'),
(434, 'Kanagawa', 'jp'),
(435, 'Osaka', 'jp'),
(436, 'Aichi', 'jp'),
(437, 'Hokkaido', 'jp'),
(438, 'Kyoto', 'jp'),
(439, 'Hyogo', 'jp'),
(440, 'Fukuoka', 'jp'),
(441, 'Hiroshima', 'jp'),
(442, 'Miyagi', 'jp'),
(443, 'Chiba', 'jp'),
(444, 'Kumamoto', 'jp'),
(445, 'Okayama', 'jp'),
(446, 'Shizuoka', 'jp'),
(447, 'Kagoshima', 'jp'),
(448, 'Niigata', 'jp'),
(449, 'Saitama', 'jp'),
(450, 'Ehime', 'jp'),
(451, 'Ishikawa', 'jp'),
(452, 'Tochigi', 'jp'),
(453, 'Oita', 'jp'),
(454, 'Nagasaki', 'jp'),
(455, 'Gifu', 'jp'),
(456, 'Wakayama', 'jp'),
(457, 'Nara', 'jp'),
(458, 'Fukushima', 'jp'),
(459, 'Nagano', 'jp'),
(460, 'Kagawa', 'jp'),
(461, 'Toyama', 'jp'),
(462, 'Kochi', 'jp'),
(463, 'Akita', 'jp'),
(464, 'Miyazaki', 'jp'),
(465, 'Okinawa', 'jp'),
(466, 'Aomori', 'jp'),
(467, 'Mie', 'jp'),
(468, 'Iwate', 'jp'),
(469, 'Gumma', 'jp'),
(470, 'Shiga', 'jp'),
(471, 'Tokushima', 'jp'),
(472, 'Yamaguchi', 'jp'),
(473, 'Yamagata', 'jp'),
(474, 'Fukui', 'jp'),
(475, 'Ibaragi', 'jp'),
(476, 'Yamanashi', 'jp'),
(477, 'Saga', 'jp'),
(478, 'Shimane', 'jp'),
(479, 'Tottori', 'jp'),
(480, 'Sanaa', 'ye'),
(481, 'Aden', 'ye'),
(482, 'Taizz', 'ye'),
(483, 'Hodeida', 'ye'),
(484, 'Hadramawt', 'ye'),
(485, 'Ibb', 'ye'),
(486, 'Amman', 'jo'),
(487, 'al-Zarqa', 'jo'),
(488, 'Irbid', 'jo'),
(489, '–', 'cx'),
(490, 'Central Serbia', 'yu'),
(491, 'Vojvodina', 'yu'),
(492, 'Kosovo and Metohija', 'yu'),
(493, 'Montenegro', 'yu'),
(494, 'Phnom Penh', 'kh'),
(495, 'Battambang', 'kh'),
(496, 'Siem Reap', 'kh'),
(497, 'Littoral', 'cm'),
(498, 'Centre', 'cm'),
(499, 'Nord', 'cm'),
(500, 'Extrême-Nord', 'cm'),
(501, 'Nord-Ouest', 'cm'),
(502, 'Ouest', 'cm'),
(503, 'Québec', 'ca'),
(504, 'Alberta', 'ca'),
(505, 'Ontario', 'ca'),
(506, 'Manitoba', 'ca'),
(507, 'British Colombia', 'ca'),
(508, 'Saskatchewan', 'ca'),
(509, 'Nova Scotia', 'ca'),
(510, 'Newfoundland', 'ca'),
(511, 'São Tiago', 'cv'),
(512, 'Almaty Qalasy', 'kz'),
(513, 'Qaraghandy', 'kz'),
(514, 'South Kazakstan', 'kz'),
(515, 'Taraz', 'kz'),
(516, 'Astana', 'kz'),
(517, 'East Kazakstan', 'kz'),
(518, 'Pavlodar', 'kz'),
(519, 'Aqtöbe', 'kz'),
(520, 'Qostanay', 'kz'),
(521, 'North Kazakstan', 'kz'),
(522, 'West Kazakstan', 'kz'),
(523, 'Qyzylorda', 'kz'),
(524, 'Mangghystau', 'kz'),
(525, 'Atyrau', 'kz'),
(526, 'Almaty', 'kz'),
(527, 'Nairobi', 'ke'),
(528, 'Coast', 'ke'),
(529, 'Nyanza', 'ke'),
(530, 'Rift Valley', 'ke'),
(531, 'Eastern', 'ke'),
(532, 'Central', 'ke'),
(533, 'Bangui', 'cf'),
(534, 'Shanghai', 'cn'),
(535, 'Peking', 'cn'),
(536, 'Chongqing', 'cn'),
(537, 'Tianjin', 'cn'),
(538, 'Hubei', 'cn'),
(539, 'Heilongjiang', 'cn'),
(540, 'Liaoning', 'cn'),
(541, 'Guangdong', 'cn'),
(542, 'Sichuan', 'cn'),
(543, 'Jiangsu', 'cn'),
(544, 'Jilin', 'cn'),
(545, 'Shaanxi', 'cn'),
(546, 'Shandong', 'cn'),
(547, 'Zhejiang', 'cn'),
(548, 'Henan', 'cn'),
(549, 'Hebei', 'cn'),
(550, 'Shanxi', 'cn'),
(551, 'Yunnan', 'cn'),
(552, 'Hunan', 'cn'),
(553, 'Jiangxi', 'cn'),
(554, 'Fujian', 'cn'),
(555, 'Gansu', 'cn'),
(556, 'Guizhou', 'cn'),
(557, 'Anhui', 'cn'),
(558, 'Xinxiang', 'cn'),
(559, 'Guangxi', 'cn'),
(560, 'Inner Mongolia', 'cn'),
(561, 'Qinghai', 'cn'),
(562, 'Ningxia', 'cn'),
(563, 'Hainan', 'cn'),
(564, 'Tibet', 'cn'),
(565, 'Bishkek shaary', 'kg'),
(566, 'Osh', 'kg'),
(567, 'South Tarawa', 'ki'),
(568, 'Santafé de Bogotá', 'co'),
(569, 'Valle', 'co'),
(570, 'Antioquia', 'co'),
(571, 'Atlántico', 'co'),
(572, 'Bolívar', 'co'),
(573, 'Norte de Santander', 'co'),
(574, 'Santander', 'co'),
(575, 'Tolima', 'co'),
(576, 'Risaralda', 'co'),
(577, 'Magdalena', 'co'),
(578, 'Caldas', 'co'),
(579, 'Nariño', 'co'),
(580, 'Huila', 'co'),
(581, 'Quindío', 'co'),
(582, 'Meta', 'co'),
(583, 'Cundinamarca', 'co'),
(584, 'Cesar', 'co'),
(585, 'Córdoba', 'co'),
(586, 'Sucre', 'co'),
(587, 'Cauca', 'co'),
(588, 'Boyacá', 'co'),
(589, 'Caquetá', 'co'),
(590, 'La Guajira', 'co'),
(591, 'Njazidja', 'km'),
(592, 'Brazzaville', 'cg'),
(593, 'Kouilou', 'cg'),
(594, 'Kinshasa', 'cd'),
(595, 'Shaba', 'cd'),
(596, 'East Kasai', 'cd'),
(597, 'Haute-Zaïre', 'cd'),
(598, 'West Kasai', 'cd'),
(599, 'South Kivu', 'cd'),
(600, 'Bandundu', 'cd'),
(601, 'Bas-Zaïre', 'cd'),
(602, 'Equateur', 'cd'),
(603, 'North Kivu', 'cd'),
(604, 'Home Island', 'cc'),
(605, 'West Island', 'cc'),
(606, 'Pyongyang-si', 'kp'),
(607, 'Hamgyong N', 'kp'),
(608, 'Hamgyong P', 'kp'),
(609, 'Nampo-si', 'kp'),
(610, 'Pyongan P', 'kp'),
(611, 'Kangwon', 'kp'),
(612, 'Pyongan N', 'kp'),
(613, 'Hwanghae P', 'kp'),
(614, 'Hwanghae N', 'kp'),
(615, 'Chagang', 'kp'),
(616, 'Yanggang', 'kp'),
(617, 'Kaesong-si', 'kp'),
(618, 'Seoul', 'kr'),
(619, 'Pusan', 'kr'),
(620, 'Inchon', 'kr'),
(621, 'Taegu', 'kr'),
(622, 'Taejon', 'kr'),
(623, 'Kwangju', 'kr'),
(624, 'Kyongsangnam', 'kr'),
(625, 'Kyonggi', 'kr'),
(626, 'Chollabuk', 'kr'),
(627, 'Chungchongbuk', 'kr'),
(628, 'Kyongsangbuk', 'kr'),
(629, 'Chungchongnam', 'kr'),
(630, 'Cheju', 'kr'),
(631, 'Chollanam', 'kr'),
(632, 'Kang-won', 'kr'),
(633, 'Attika', 'gr'),
(634, 'Central Macedonia', 'gr'),
(635, 'West Greece', 'gr'),
(636, 'Crete', 'gr'),
(637, 'Thessalia', 'gr'),
(638, 'Grad Zagreb', 'hr'),
(639, 'Split-Dalmatia', 'hr'),
(640, 'Primorje-Gorski Kota', 'hr'),
(641, 'Osijek-Baranja', 'hr'),
(642, 'La Habana', 'cu'),
(643, 'Santiago de Cuba', 'cu'),
(644, 'Camagüey', 'cu'),
(645, 'Holguín', 'cu'),
(646, 'Villa Clara', 'cu'),
(647, 'Guantánamo', 'cu'),
(648, 'Pinar del Río', 'cu'),
(649, 'Granma', 'cu'),
(650, 'Cienfuegos', 'cu'),
(651, 'Las Tunas', 'cu'),
(652, 'Matanzas', 'cu'),
(653, 'Sancti-Spíritus', 'cu'),
(654, 'Ciego de Ávila', 'cu'),
(655, 'Hawalli', 'kw'),
(656, 'al-Asima', 'kw'),
(657, 'Nicosia', 'cy'),
(658, 'Limassol', 'cy'),
(659, 'Viangchan', 'la'),
(660, 'Savannakhet', 'la'),
(661, 'Riika', 'lv'),
(662, 'Daugavpils', 'lv'),
(663, 'Liepaja', 'lv'),
(664, 'Maseru', 'ls'),
(665, 'Beirut', 'lb'),
(666, 'al-Shamal', 'lb'),
(667, 'Montserrado', 'lr'),
(668, 'Tripoli', 'ly'),
(669, 'Bengasi', 'ly'),
(670, 'Misrata', 'ly'),
(671, 'al-Zawiya', 'ly'),
(672, 'Schaan', 'li'),
(673, 'Vaduz', 'li'),
(674, 'Vilna', 'lt'),
(675, 'Kaunas', 'lt'),
(676, 'Klaipeda', 'lt'),
(677, 'Šiauliai', 'lt'),
(678, 'Panevezys', 'lt'),
(679, 'Luxembourg', 'lu'),
(680, 'El-Aaiún', 'eh'),
(681, 'Macau', 'mo'),
(682, 'Antananarivo', 'mg'),
(683, 'Toamasina', 'mg'),
(684, 'Mahajanga', 'mg'),
(685, 'Fianarantsoa', 'mg'),
(686, 'Skopje', 'mk'),
(687, 'Blantyre', 'mw'),
(688, 'Lilongwe', 'mw'),
(689, 'Maale', 'mv'),
(690, 'Wilayah Persekutuan', 'my'),
(691, 'Perak', 'my'),
(692, 'Johor', 'my'),
(693, 'Selangor', 'my'),
(694, 'Terengganu', 'my'),
(695, 'Pulau Pinang', 'my'),
(696, 'Kelantan', 'my'),
(697, 'Pahang', 'my'),
(698, 'Negeri Sembilan', 'my'),
(699, 'Sarawak', 'my'),
(700, 'Sabah', 'my'),
(701, 'Kedah', 'my'),
(702, 'Bamako', 'ml'),
(703, 'Outer Harbour', 'mt'),
(704, 'Inner Harbour', 'mt'),
(705, 'Casablanca', 'ma'),
(706, 'Rabat-Salé-Zammour-Z', 'ma'),
(707, 'Marrakech-Tensift-Al', 'ma'),
(708, 'Fès-Boulemane', 'ma'),
(709, 'Tanger-Tétouan', 'ma'),
(710, 'Meknès-Tafilalet', 'ma'),
(711, 'Oriental', 'ma'),
(712, 'Gharb-Chrarda-Béni H', 'ma'),
(713, 'Doukkala-Abda', 'ma'),
(714, 'Souss Massa-Draâ', 'ma'),
(715, 'Chaouia-Ouardigha', 'ma'),
(716, 'Tadla-Azilal', 'ma'),
(717, 'Taza-Al Hoceima-Taou', 'ma'),
(718, 'Majuro', 'mh'),
(719, 'Fort-de-France', 'mq'),
(720, 'Nouakchott', 'mr'),
(721, 'Dakhlet Nouâdhibou', 'mr'),
(722, 'Port-Louis', 'mu'),
(723, 'Plaines Wilhelms', 'mu'),
(724, 'Mamoutzou', 'yt'),
(725, 'Distrito Federal', 'mx'),
(726, 'Jalisco', 'mx'),
(727, 'México', 'mx'),
(728, 'Puebla', 'mx'),
(729, 'Chihuahua', 'mx'),
(730, 'Baja California', 'mx'),
(731, 'Guanajuato', 'mx'),
(732, 'Nuevo León', 'mx'),
(733, 'Sinaloa', 'mx'),
(734, 'Guerrero', 'mx'),
(735, 'Yucatán', 'mx'),
(736, 'San Luis Potosí', 'mx'),
(737, 'Aguascalientes', 'mx'),
(738, 'Querétaro de Arteaga', 'mx'),
(739, 'Michoacán de Ocampo', 'mx'),
(740, 'Sonora', 'mx'),
(741, 'Coahuila de Zaragoza', 'mx'),
(742, 'Tabasco', 'mx'),
(743, 'Durango', 'mx'),
(744, 'Veracruz', 'mx'),
(745, 'Chiapas', 'mx'),
(746, 'Tamaulipas', 'mx'),
(747, 'Quintana Roo', 'mx'),
(748, 'Morelos', 'mx'),
(749, 'Nayarit', 'mx'),
(750, 'Oaxaca', 'mx'),
(751, 'Hidalgo', 'mx'),
(752, 'Campeche', 'mx'),
(753, 'Baja California Sur', 'mx'),
(754, 'Zacatecas', 'mx'),
(755, 'Querétaro', 'mx'),
(756, 'Veracruz-Llave', 'mx'),
(757, 'Colima', 'mx'),
(758, 'Chuuk', 'fm'),
(759, 'Pohnpei', 'fm'),
(760, 'Chisinau', 'md'),
(761, 'Dnjestria', 'md'),
(762, 'Balti', 'md'),
(763, 'Bender (Tîghina)', 'md'),
(764, '–', 'mc'),
(765, 'Ulaanbaatar', 'mn'),
(766, 'Plymouth', 'ms'),
(767, 'Maputo', 'mz'),
(768, 'Sofala', 'mz'),
(769, 'Nampula', 'mz'),
(770, 'Manica', 'mz'),
(771, 'Zambézia', 'mz'),
(772, 'Tete', 'mz'),
(773, 'Gaza', 'mz'),
(774, 'Inhambane', 'mz'),
(775, 'Rangoon [Yangon]', 'mm'),
(776, 'Mandalay', 'mm'),
(777, 'Mon', 'mm'),
(778, 'Pegu [Bago]', 'mm'),
(779, 'Irrawaddy [Ayeyarwad', 'mm'),
(780, 'Sagaing', 'mm'),
(781, 'Rakhine', 'mm'),
(782, 'Shan', 'mm'),
(783, 'Tenasserim [Tanintha', 'mm'),
(784, 'Magwe [Magway]', 'mm'),
(785, 'Khomas', 'na'),
(786, '–', 'nr'),
(787, 'Central', 'np'),
(788, 'Eastern', 'np'),
(789, 'Western', 'np'),
(790, 'Managua', 'ni'),
(791, 'León', 'ni'),
(792, 'Chinandega', 'ni'),
(793, 'Masaya', 'ni'),
(794, 'Niamey', 'ne'),
(795, 'Zinder', 'ne'),
(796, 'Maradi', 'ne'),
(797, 'Lagos', 'ng'),
(798, 'Oyo & Osun', 'ng'),
(799, 'Kano & Jigawa', 'ng'),
(800, 'Kwara & Kogi', 'ng'),
(801, 'Ogun', 'ng'),
(802, 'Rivers & Bayelsa', 'ng'),
(803, 'Kaduna', 'ng'),
(804, 'Anambra & Enugu & Eb', 'ng'),
(805, 'Ondo & Ekiti', 'ng'),
(806, 'Federal Capital Dist', 'ng'),
(807, 'Borno & Yobe', 'ng'),
(808, 'Imo & Abia', 'ng'),
(809, 'Edo & Delta', 'ng'),
(810, 'Katsina', 'ng'),
(811, 'Plateau & Nassarawa', 'ng'),
(812, 'Sokoto & Kebbi & Zam', 'ng'),
(813, 'Cross River', 'ng'),
(814, 'Bauchi & Gombe', 'ng'),
(815, 'Niger', 'ng'),
(816, 'Benue', 'ng'),
(817, '–', 'nu'),
(818, '–', 'nf'),
(819, 'Oslo', 'no'),
(820, 'Hordaland', 'no'),
(821, 'Sør-Trøndelag', 'no'),
(822, 'Rogaland', 'no'),
(823, 'Akershus', 'no'),
(824, 'Abidjan', 'ci'),
(825, 'Bouaké', 'ci'),
(826, 'Yamoussoukro', 'ci'),
(827, 'Daloa', 'ci'),
(828, 'Korhogo', 'ci'),
(829, 'Masqat', 'om'),
(830, 'Zufar', 'om'),
(831, 'al-Batina', 'om'),
(832, 'Sindh', 'pk'),
(833, 'Punjab', 'pk'),
(834, 'Nothwest Border Prov', 'pk'),
(835, 'Baluchistan', 'pk'),
(836, 'Islamabad', 'pk'),
(837, 'Sind', 'pk'),
(838, 'Koror', 'pw'),
(839, 'Panamá', 'pa'),
(840, 'San Miguelito', 'pa'),
(841, 'National Capital Dis', 'pg'),
(842, 'Asunción', 'py'),
(843, 'Alto Paraná', 'py'),
(844, 'Central', 'py'),
(845, 'Lima', 'pe'),
(846, 'Arequipa', 'pe'),
(847, 'La Libertad', 'pe'),
(848, 'Lambayeque', 'pe'),
(849, 'Callao', 'pe'),
(850, 'Loreto', 'pe'),
(851, 'Ancash', 'pe'),
(852, 'Junín', 'pe'),
(853, 'Piura', 'pe'),
(854, 'Cusco', 'pe'),
(855, 'Ucayali', 'pe'),
(856, 'Tacna', 'pe'),
(857, 'Ica', 'pe'),
(858, 'Puno', 'pe'),
(859, 'Huanuco', 'pe'),
(860, 'Ayacucho', 'pe'),
(861, 'Cajamarca', 'pe'),
(862, '–', 'pn'),
(863, 'Saipan', 'mp'),
(864, 'Lisboa', 'pt'),
(865, 'Porto', 'pt'),
(866, 'Coímbra', 'pt'),
(867, 'Braga', 'pt'),
(868, 'San Juan', 'pr'),
(869, 'Bayamón', 'pr'),
(870, 'Ponce', 'pr'),
(871, 'Carolina', 'pr'),
(872, 'Caguas', 'pr'),
(873, 'Arecibo', 'pr'),
(874, 'Guaynabo', 'pr'),
(875, 'Mayagüez', 'pr'),
(876, 'Toa Baja', 'pr'),
(877, 'Mazowieckie', 'pl'),
(878, 'Lodzkie', 'pl'),
(879, 'Malopolskie', 'pl'),
(880, 'Dolnoslaskie', 'pl'),
(881, 'Wielkopolskie', 'pl'),
(882, 'Pomorskie', 'pl'),
(883, 'Zachodnio-Pomorskie', 'pl'),
(884, 'Kujawsko-Pomorskie', 'pl'),
(885, 'Lubelskie', 'pl'),
(886, 'Slaskie', 'pl'),
(887, 'Podlaskie', 'pl'),
(888, 'Swietokrzyskie', 'pl'),
(889, 'Warminsko-Mazurskie', 'pl'),
(890, 'Podkarpackie', 'pl'),
(891, 'Opolskie', 'pl'),
(892, 'Lubuskie', 'pl'),
(893, 'Bioko', 'gq'),
(894, 'Doha', 'qa'),
(895, 'Île-de-France', 'fr'),
(896, 'Provence-Alpes-Côte', 'fr'),
(897, 'Rhône-Alpes', 'fr'),
(898, 'Midi-Pyrénées', 'fr'),
(899, 'Pays de la Loire', 'fr'),
(900, 'Alsace', 'fr'),
(901, 'Languedoc-Roussillon', 'fr'),
(902, 'Aquitaine', 'fr'),
(903, 'Haute-Normandie', 'fr'),
(904, 'Champagne-Ardenne', 'fr'),
(905, 'Nord-Pas-de-Calais', 'fr'),
(906, 'Bretagne', 'fr'),
(907, 'Bourgogne', 'fr'),
(908, 'Auvergne', 'fr'),
(909, 'Picardie', 'fr'),
(910, 'Limousin', 'fr'),
(911, 'Centre', 'fr'),
(912, 'Lorraine', 'fr'),
(913, 'Franche-Comté', 'fr'),
(914, 'Basse-Normandie', 'fr'),
(915, 'Cayenne', 'gf'),
(916, 'Tahiti', 'pf'),
(917, 'Saint-Denis', 're'),
(918, 'Bukarest', 'ro'),
(919, 'Iasi', 'ro'),
(920, 'Constanta', 'ro'),
(921, 'Cluj', 'ro'),
(922, 'Galati', 'ro'),
(923, 'Timis', 'ro'),
(924, 'Brasov', 'ro'),
(925, 'Dolj', 'ro'),
(926, 'Prahova', 'ro'),
(927, 'Braila', 'ro'),
(928, 'Bihor', 'ro'),
(929, 'Bacau', 'ro'),
(930, 'Arges', 'ro'),
(931, 'Arad', 'ro'),
(932, 'Sibiu', 'ro'),
(933, 'Mures', 'ro'),
(934, 'Maramures', 'ro'),
(935, 'Buzau', 'ro'),
(936, 'Satu Mare', 'ro'),
(937, 'Botosani', 'ro'),
(938, 'Neamt', 'ro'),
(939, 'Vâlcea', 'ro'),
(940, 'Suceava', 'ro'),
(941, 'Mehedinti', 'ro'),
(942, 'Dâmbovita', 'ro'),
(943, 'Vrancea', 'ro'),
(944, 'Gorj', 'ro'),
(945, 'Tulcea', 'ro'),
(946, 'Caras-Severin', 'ro'),
(947, 'Kigali', 'rw'),
(948, 'Lisboa', 'se'),
(949, 'West Götanmaan län', 'se'),
(950, 'Skåne län', 'se'),
(951, 'Uppsala län', 'se'),
(952, 'East Götanmaan län', 'se'),
(953, 'Västmanlands län', 'se'),
(954, 'Örebros län', 'se'),
(955, 'Jönköpings län', 'se'),
(956, 'Västerbottens län', 'se'),
(957, 'Västernorrlands län', 'se'),
(958, 'Gävleborgs län', 'se'),
(959, 'Saint Helena', 'sh'),
(960, 'St George Basseterre', 'kn'),
(961, 'Castries', 'lc'),
(962, 'St George', 'vc'),
(963, 'Saint-Pierre', 'pm'),
(964, 'Berliini', 'de'),
(965, 'Hamburg', 'de'),
(966, 'Baijeri', 'de'),
(967, 'Nordrhein-Westfalen', 'de'),
(968, 'Hessen', 'de'),
(969, 'Baden-Württemberg', 'de'),
(970, 'Bremen', 'de'),
(971, 'Niedersachsen', 'de'),
(972, 'Saksi', 'de'),
(973, 'Anhalt Sachsen', 'de'),
(974, 'Schleswig-Holstein', 'de'),
(975, 'Mecklenburg-Vorpomme', 'de'),
(976, 'Thüringen', 'de'),
(977, 'Saarland', 'de'),
(978, 'Rheinland-Pfalz', 'de'),
(979, 'Brandenburg', 'de'),
(980, 'Honiara', 'sb'),
(981, 'Lusaka', 'zm'),
(982, 'Copperbelt', 'zm'),
(983, 'Central', 'zm'),
(984, 'Upolu', 'ws'),
(985, 'Serravalle/Dogano', 'sm'),
(986, 'San Marino', 'sm'),
(987, 'Aqua Grande', 'st'),
(988, 'Riyadh', 'sa'),
(989, 'Mekka', 'sa'),
(990, 'Medina', 'sa'),
(991, 'al-Sharqiya', 'sa'),
(992, 'Tabuk', 'sa'),
(993, 'al-Qasim', 'sa'),
(994, 'Asir', 'sa'),
(995, 'Hail', 'sa'),
(996, 'Riad', 'sa'),
(997, 'al-Khudud al-Samaliy', 'sa'),
(998, 'Qasim', 'sa'),
(999, 'Najran', 'sa'),
(1000, 'Cap-Vert', 'sn'),
(1001, 'Thiès', 'sn'),
(1002, 'Kaolack', 'sn'),
(1003, 'Ziguinchor', 'sn'),
(1004, 'Saint-Louis', 'sn'),
(1005, 'Diourbel', 'sn'),
(1006, 'Mahé', 'sc'),
(1007, 'Western', 'sl'),
(1008, '–', 'sg'),
(1009, 'Bratislava', 'sk'),
(1010, 'Východné Slovensko', 'sk'),
(1011, 'Osrednjeslovenska', 'si'),
(1012, 'Podravska', 'si'),
(1013, 'Banaadir', 'so'),
(1014, 'Woqooyi Galbeed', 'so'),
(1015, 'Jubbada Hoose', 'so'),
(1016, 'Western', 'lk'),
(1017, 'Northern', 'lk'),
(1018, 'Central', 'lk'),
(1019, 'Khartum', 'sd'),
(1020, 'al-Bahr al-Ahmar', 'sd'),
(1021, 'Kassala', 'sd'),
(1022, 'Kurdufan al-Shamaliy', 'sd'),
(1023, 'Darfur al-Janubiya', 'sd'),
(1024, 'al-Jazira', 'sd'),
(1025, 'al-Qadarif', 'sd'),
(1026, 'al-Bahr al-Abyad', 'sd'),
(1027, 'Darfur al-Shamaliya', 'sd'),
(1028, 'Bahr al-Jabal', 'sd'),
(1029, 'Newmaa', 'fi'),
(1030, 'Pirkanmaa', 'fi'),
(1031, 'Varsinais-Suomi', 'fi'),
(1032, 'Pohjois-Pohjanmaa', 'fi'),
(1033, 'Päijät-Häme', 'fi'),
(1034, 'Paramaribo', 'sr'),
(1035, 'Hhohho', 'sz'),
(1036, 'Zürich', 'ch'),
(1037, 'Geneve', 'ch'),
(1038, 'Basel-Stadt', 'ch'),
(1039, 'Bern', 'ch'),
(1040, 'Vaud', 'ch'),
(1041, 'Damascus', 'sy'),
(1042, 'Aleppo', 'sy'),
(1043, 'Hims', 'sy'),
(1044, 'Hama', 'sy'),
(1045, 'Latakia', 'sy'),
(1046, 'al-Hasaka', 'sy'),
(1047, 'Dayr al-Zawr', 'sy'),
(1048, 'Damaskos', 'sy'),
(1049, 'al-Raqqa', 'sy'),
(1050, 'Idlib', 'sy'),
(1051, 'Karotegin', 'tj'),
(1052, 'Khujand', 'tj'),
(1053, 'Taipei', 'tw'),
(1054, 'Kaohsiung', 'tw'),
(1055, 'Taichung', 'tw'),
(1056, 'Tainan', 'tw'),
(1057, 'Keelung', 'tw'),
(1058, 'Hsinchu', 'tw'),
(1059, 'Taoyuan', 'tw'),
(1060, 'Chiayi', 'tw'),
(1061, 'Changhwa', 'tw'),
(1062, 'Pingtung', 'tw'),
(1063, '', 'tw'),
(1064, 'Taitung', 'tw'),
(1065, 'Hualien', 'tw'),
(1066, 'Nantou', 'tw'),
(1067, 'Yünlin', 'tw'),
(1068, 'Ilan', 'tw'),
(1069, 'Miaoli', 'tw'),
(1070, 'Dar es Salaam', 'tz'),
(1071, 'Dodoma', 'tz'),
(1072, 'Mwanza', 'tz'),
(1073, 'Zanzibar West', 'tz'),
(1074, 'Tanga', 'tz'),
(1075, 'Mbeya', 'tz'),
(1076, 'Morogoro', 'tz'),
(1077, 'Arusha', 'tz'),
(1078, 'Kilimanjaro', 'tz'),
(1079, 'Tabora', 'tz'),
(1080, 'København', 'dk'),
(1081, 'Århus', 'dk'),
(1082, 'Fyn', 'dk'),
(1083, 'Nordjylland', 'dk'),
(1084, 'Frederiksberg', 'dk'),
(1085, 'Bangkok', 'th'),
(1086, 'Nonthaburi', 'th'),
(1087, 'Nakhon Ratchasima', 'th'),
(1088, 'Chiang Mai', 'th'),
(1089, 'Udon Thani', 'th'),
(1090, 'Songkhla', 'th'),
(1091, 'Khon Kaen', 'th'),
(1092, 'Nakhon Sawan', 'th'),
(1093, 'Ubon Ratchathani', 'th'),
(1094, 'Nakhon Pathom', 'th'),
(1095, 'Maritime', 'tg'),
(1096, 'Fakaofo', 'tk'),
(1097, 'Tongatapu', 'to'),
(1098, 'Caroni', 'tt'),
(1099, 'Port-of-Spain', 'tt'),
(1100, 'Chari-Baguirmi', 'td'),
(1101, 'Logone Occidental', 'td'),
(1102, 'Hlavní mesto Praha', 'cz'),
(1103, 'Jizní Morava', 'cz'),
(1104, 'Severní Morava', 'cz'),
(1105, 'Zapadní Cechy', 'cz'),
(1106, 'Severní Cechy', 'cz'),
(1107, 'Jizní Cechy', 'cz'),
(1108, 'Východní Cechy', 'cz'),
(1109, 'Tunis', 'tn'),
(1110, 'Sfax', 'tn'),
(1111, 'Ariana', 'tn'),
(1112, 'Sousse', 'tn'),
(1113, 'Kairouan', 'tn'),
(1114, 'Biserta', 'tn'),
(1115, 'Gabès', 'tn'),
(1116, 'Istanbul', 'tr'),
(1117, 'Ankara', 'tr'),
(1118, 'Izmir', 'tr'),
(1119, 'Adana', 'tr'),
(1120, 'Bursa', 'tr'),
(1121, 'Gaziantep', 'tr'),
(1122, 'Konya', 'tr'),
(1123, 'Içel', 'tr'),
(1124, 'Antalya', 'tr'),
(1125, 'Diyarbakir', 'tr'),
(1126, 'Kayseri', 'tr'),
(1127, 'Eskisehir', 'tr'),
(1128, 'Sanliurfa', 'tr'),
(1129, 'Samsun', 'tr'),
(1130, 'Malatya', 'tr'),
(1131, 'Kocaeli', 'tr'),
(1132, 'Denizli', 'tr'),
(1133, 'Sivas', 'tr'),
(1134, 'Erzurum', 'tr'),
(1135, 'Kahramanmaras', 'tr'),
(1136, 'Elâzig', 'tr'),
(1137, 'Van', 'tr'),
(1138, 'Manisa', 'tr'),
(1139, 'Batman', 'tr'),
(1140, 'Balikesir', 'tr'),
(1141, 'Sakarya', 'tr'),
(1142, 'Hatay', 'tr'),
(1143, 'Osmaniye', 'tr'),
(1144, 'Çorum', 'tr'),
(1145, 'Kütahya', 'tr'),
(1146, 'Kirikkale', 'tr'),
(1147, 'Adiyaman', 'tr'),
(1148, 'Trabzon', 'tr'),
(1149, 'Ordu', 'tr'),
(1150, 'Aydin', 'tr'),
(1151, 'Usak', 'tr'),
(1152, 'Edirne', 'tr'),
(1153, 'Tekirdag', 'tr'),
(1154, 'Isparta', 'tr'),
(1155, 'Karabük', 'tr'),
(1156, 'Kilis', 'tr'),
(1157, 'Mardin', 'tr'),
(1158, 'Zonguldak', 'tr'),
(1159, 'Siirt', 'tr'),
(1160, 'Karaman', 'tr'),
(1161, 'Afyon', 'tr'),
(1162, 'Aksaray', 'tr'),
(1163, 'Erzincan', 'tr'),
(1164, 'Tokat', 'tr'),
(1165, 'Kars', 'tr'),
(1166, 'Ahal', 'tm'),
(1167, 'Lebap', 'tm'),
(1168, 'Dashhowuz', 'tm'),
(1169, 'Mary', 'tm'),
(1170, 'Grand Turk', 'tc'),
(1171, 'Funafuti', 'tv'),
(1172, 'Central', 'ug'),
(1173, 'Kiova', 'ua'),
(1174, 'Harkova', 'ua'),
(1175, 'Dnipropetrovsk', 'ua'),
(1176, 'Donetsk', 'ua'),
(1177, 'Odesa', 'ua'),
(1178, 'Zaporizzja', 'ua'),
(1179, 'Lviv', 'ua'),
(1180, 'Mykolajiv', 'ua'),
(1181, 'Lugansk', 'ua'),
(1182, 'Vinnytsja', 'ua'),
(1183, 'Herson', 'ua'),
(1184, 'Krim', 'ua'),
(1185, 'Pultava', 'ua'),
(1186, 'Tšernigiv', 'ua'),
(1187, 'Tšerkasy', 'ua'),
(1188, 'Zytomyr', 'ua'),
(1189, 'Sumy', 'ua'),
(1190, 'Kirovograd', 'ua'),
(1191, 'Hmelnytskyi', 'ua'),
(1192, 'Tšernivtsi', 'ua'),
(1193, 'Rivne', 'ua'),
(1194, 'Ivano-Frankivsk', 'ua'),
(1195, 'Ternopil', 'ua'),
(1196, 'Volynia', 'ua'),
(1197, 'Taka-Karpatia', 'ua'),
(1198, 'Budapest', 'hu'),
(1199, 'Hajdú-Bihar', 'hu'),
(1200, 'Borsod-Abaúj-Zemplén', 'hu'),
(1201, 'Csongrád', 'hu'),
(1202, 'Baranya', 'hu'),
(1203, 'Györ-Moson-Sopron', 'hu'),
(1204, 'Szabolcs-Szatmár-Ber', 'hu'),
(1205, 'Bács-Kiskun', 'hu'),
(1206, 'Fejér', 'hu'),
(1207, 'Montevideo', 'uy'),
(1208, '–', 'nc'),
(1209, 'Auckland', 'nz'),
(1210, 'Canterbury', 'nz'),
(1211, 'Wellington', 'nz'),
(1212, 'Dunedin', 'nz'),
(1213, 'Hamilton', 'nz'),
(1214, 'Toskent Shahri', 'uz'),
(1215, 'Namangan', 'uz'),
(1216, 'Samarkand', 'uz'),
(1217, 'Andijon', 'uz'),
(1218, 'Buhoro', 'uz'),
(1219, 'Qashqadaryo', 'uz'),
(1220, 'Karakalpakistan', 'uz'),
(1221, 'Fargona', 'uz'),
(1222, 'Toskent', 'uz'),
(1223, 'Khorazm', 'uz'),
(1224, 'Cizah', 'uz'),
(1225, 'Navoi', 'uz'),
(1226, 'Surkhondaryo', 'uz'),
(1227, 'Horad Minsk', 'by'),
(1228, 'Gomel', 'by'),
(1229, 'Mogiljov', 'by'),
(1230, 'Vitebsk', 'by'),
(1231, 'Grodno', 'by'),
(1232, 'Brest', 'by'),
(1233, 'Minsk', 'by'),
(1234, 'Wallis', 'wf'),
(1235, 'Shefa', 'vu'),
(1236, '–', 'va'),
(1237, 'Distrito Federal', 've'),
(1238, 'Zulia', 've'),
(1239, 'Lara', 've'),
(1240, 'Carabobo', 've'),
(1241, 'Bolívar', 've'),
(1242, 'Miranda', 've'),
(1243, 'Aragua', 've'),
(1244, 'Anzoátegui', 've'),
(1245, 'Monagas', 've'),
(1246, 'Táchira', 've'),
(1247, 'Sucre', 've'),
(1248, 'Mérida', 've'),
(1249, 'Barinas', 've'),
(1250, 'Falcón', 've'),
(1251, 'Portuguesa', 've'),
(1252, '', 've'),
(1253, 'Trujillo', 've'),
(1254, 'Guárico', 've'),
(1255, 'Apure', 've'),
(1256, 'Yaracuy', 've'),
(1257, 'Moscow (City)', 'ru'),
(1258, 'Pietari', 'ru'),
(1259, 'Novosibirsk', 'ru'),
(1260, 'Nizni Novgorod', 'ru'),
(1261, 'Sverdlovsk', 'ru'),
(1262, 'Samara', 'ru'),
(1263, 'Omsk', 'ru'),
(1264, 'Tatarstan', 'ru'),
(1265, 'Baškortostan', 'ru'),
(1266, 'Tšeljabinsk', 'ru'),
(1267, 'Rostov-na-Donu', 'ru'),
(1268, 'Perm', 'ru'),
(1269, 'Volgograd', 'ru'),
(1270, 'Voronez', 'ru'),
(1271, 'Krasnojarsk', 'ru'),
(1272, 'Saratov', 'ru'),
(1273, 'Uljanovsk', 'ru'),
(1274, 'Udmurtia', 'ru'),
(1275, 'Krasnodar', 'ru'),
(1276, 'Jaroslavl', 'ru'),
(1277, 'Habarovsk', 'ru'),
(1278, 'Primorje', 'ru'),
(1279, 'Irkutsk', 'ru'),
(1280, 'Altai', 'ru'),
(1281, 'Kemerovo', 'ru'),
(1282, 'Penza', 'ru'),
(1283, 'Rjazan', 'ru'),
(1284, 'Orenburg', 'ru'),
(1285, 'Lipetsk', 'ru'),
(1286, 'Tula', 'ru'),
(1287, 'Tjumen', 'ru'),
(1288, 'Astrahan', 'ru'),
(1289, 'Tomsk', 'ru'),
(1290, 'Kirov', 'ru'),
(1291, 'Ivanovo', 'ru'),
(1292, 'Tšuvassia', 'ru'),
(1293, 'Brjansk', 'ru'),
(1294, 'Tver', 'ru'),
(1295, 'Kursk', 'ru'),
(1296, 'Kaliningrad', 'ru'),
(1297, 'Murmansk', 'ru'),
(1298, 'Burjatia', 'ru'),
(1299, 'Kurgan', 'ru'),
(1300, 'Arkangeli', 'ru'),
(1301, 'Smolensk', 'ru'),
(1302, 'Orjol', 'ru'),
(1303, 'Stavropol', 'ru'),
(1304, 'Belgorod', 'ru'),
(1305, 'Kaluga', 'ru'),
(1306, 'Vladimir', 'ru'),
(1307, 'Dagestan', 'ru'),
(1308, 'Vologda', 'ru'),
(1309, 'Mordva', 'ru'),
(1310, 'Tambov', 'ru'),
(1311, 'North Ossetia-Alania', 'ru'),
(1312, 'Tšita', 'ru'),
(1313, 'Novgorod', 'ru'),
(1314, 'Kostroma', 'ru'),
(1315, 'Karjala', 'ru'),
(1316, 'Hanti-Mansia', 'ru'),
(1317, 'Marinmaa', 'ru'),
(1318, 'Kabardi-Balkaria', 'ru'),
(1319, 'Komi', 'ru'),
(1320, 'Amur', 'ru'),
(1321, 'Pihkova', 'ru'),
(1322, 'Saha (Jakutia)', 'ru'),
(1323, 'Moskova', 'ru'),
(1324, 'Kamtšatka', 'ru'),
(1325, 'Tšetšenia', 'ru'),
(1326, 'Sahalin', 'ru'),
(1327, 'Hakassia', 'ru'),
(1328, 'Adygea', 'ru'),
(1329, 'Karatšai-Tšerkessia', 'ru'),
(1330, 'Magadan', 'ru'),
(1331, 'Kalmykia', 'ru'),
(1332, 'Tyva', 'ru'),
(1333, 'Yamalin Nenetsia', 'ru'),
(1334, 'Ho Chi Minh City', 'vn'),
(1335, 'Hanoi', 'vn'),
(1336, 'Haiphong', 'vn'),
(1337, 'Quang Nam-Da Nang', 'vn'),
(1338, 'Dong Nai', 'vn'),
(1339, 'Khanh Hoa', 'vn'),
(1340, 'Thua Thien-Hue', 'vn'),
(1341, 'Can Tho', 'vn'),
(1342, 'Quang Binh', 'vn'),
(1343, 'Nam Ha', 'vn'),
(1344, 'Binh Dinh', 'vn'),
(1345, 'Ba Ria-Vung Tau', 'vn'),
(1346, 'Kien Giang', 'vn'),
(1347, 'An Giang', 'vn'),
(1348, 'Bac Thai', 'vn'),
(1349, 'Quang Ninh', 'vn'),
(1350, 'Binh Thuan', 'vn'),
(1351, 'Nghe An', 'vn'),
(1352, 'Tien Giang', 'vn'),
(1353, 'Lam Dong', 'vn'),
(1354, 'Dac Lac', 'vn'),
(1355, 'Harjumaa', 'ee'),
(1356, 'Tartumaa', 'ee'),
(1357, 'New York', 'us'),
(1358, 'California', 'us'),
(1359, 'Illinois', 'us'),
(1360, 'Texas', 'us'),
(1361, 'Pennsylvania', 'us'),
(1362, 'Arizona', 'us'),
(1363, 'Michigan', 'us'),
(1364, 'Indiana', 'us'),
(1365, 'Florida', 'us'),
(1366, 'Ohio', 'us'),
(1367, 'Maryland', 'us'),
(1368, 'Tennessee', 'us'),
(1369, 'Wisconsin', 'us'),
(1370, 'Massachusetts', 'us'),
(1371, 'District of Columbia', 'us'),
(1372, 'Washington', 'us'),
(1373, 'Colorado', 'us'),
(1374, 'North Carolina', 'us'),
(1375, 'Oregon', 'us'),
(1376, 'Oklahoma', 'us'),
(1377, 'Louisiana', 'us'),
(1378, 'Nevada', 'us'),
(1379, 'New Mexico', 'us'),
(1380, 'Missouri', 'us'),
(1381, 'Virginia', 'us'),
(1382, 'Georgia', 'us'),
(1383, 'Nebraska', 'us'),
(1384, 'Minnesota', 'us'),
(1385, 'Hawaii', 'us'),
(1386, 'Kansas', 'us'),
(1387, 'New Jersey', 'us'),
(1388, 'Kentucky', 'us'),
(1389, 'Alaska', 'us'),
(1390, 'Alabama', 'us'),
(1391, 'Iowa', 'us'),
(1392, 'Idaho', 'us'),
(1393, 'Mississippi', 'us'),
(1394, 'Arkansas', 'us'),
(1395, 'Utah', 'us'),
(1396, 'Rhode Island', 'us'),
(1397, 'Connecticut', 'us'),
(1398, 'South Dakota', 'us'),
(1399, 'South Carolina', 'us'),
(1400, 'New Hampshire', 'us'),
(1401, 'Montana', 'us'),
(1402, 'St Thomas', 'vi'),
(1403, 'Harare', 'zw'),
(1404, 'Bulawayo', 'zw'),
(1405, 'Manicaland', 'zw'),
(1406, 'Midlands', 'zw'),
(1407, 'Gaza', 'ps'),
(1408, 'Khan Yunis', 'ps'),
(1409, 'Hebron', 'ps'),
(1410, 'North Gaza', 'ps'),
(1411, 'Nablus', 'ps'),
(1412, 'Rafah', 'ps');

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
  `countFeeds` int(10) unsigned NOT NULL,
  `countEntries` int(10) unsigned NOT NULL,
  `countUsers` int(10) unsigned NOT NULL,
  `countTotal` int(10) unsigned NOT NULL,
  `lastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`tagId`),
  UNIQUE KEY `tagName` (`tagName`),
  KEY `countFeeds` (`countFeeds`),
  KEY `countEntries` (`countEntries`),
  KEY `countUsers` (`countUsers`),
  KEY `countTotal` (`countTotal`),
  KEY `lastUpdate` (`lastUpdate`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`tagId`, `tagName`, `countFeeds`, `countEntries`, `countUsers`, `countTotal`, `lastUpdate`) VALUES
(1, '@tag-all', 0, 0, 0, 0, '2015-06-15 20:31:24'),
(2, '@tag-star', 0, 0, 0, 0, '2015-06-15 20:31:24'),
(3, '@tag-home', 0, 0, 0, 0, '2015-06-15 20:31:24'),
(4, '@tag-browse', 0, 0, 0, 0, '2015-06-15 20:31:24');

-- --------------------------------------------------------

--
-- Table structure for table `tasks_email`
--

CREATE TABLE IF NOT EXISTS `tasks_email` (
  `taskId` int(10) NOT NULL AUTO_INCREMENT,
  `taskMethod` varchar(255) DEFAULT NULL,
  `taskParams` text,
  `taskRunning` int(1) NOT NULL,
  `taskRetries` tinyint(4) NOT NULL,
  `langId` varchar(10) NOT NULL,
  `taskSchedule` datetime NOT NULL,
  PRIMARY KEY (`taskId`),
  KEY `langId` (`langId`),
  KEY `taskSchedule` (`taskSchedule`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tasks_status`
--

CREATE TABLE IF NOT EXISTS `tasks_status` (
  `statusTaskId` int(10) unsigned NOT NULL,
  `statusTaskName` varchar(255) NOT NULL,
  PRIMARY KEY (`statusTaskId`),
  KEY `statusTaskName` (`statusTaskName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tasks_status`
--

INSERT INTO `tasks_status` (`statusTaskId`, `statusTaskName`) VALUES
(2, 'cancel'),
(0, 'pending'),
(1, 'running');

-- --------------------------------------------------------

--
-- Table structure for table `testing`
--

CREATE TABLE IF NOT EXISTS `testing` (
  `testId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `testName` varchar(255) NOT NULL,
  `testDesc` text NOT NULL,
  `testDate` datetime NOT NULL,
  `countryId` char(2) DEFAULT NULL,
  `stateId` int(10) unsigned DEFAULT NULL,
  `testRating` int(1) unsigned NOT NULL,
  `testPictureFileId` int(10) unsigned DEFAULT NULL,
  `testDocFileId` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`testId`),
  KEY `countryId` (`countryId`),
  KEY `stateId` (`stateId`),
  KEY `fileIdTestPicture` (`testPictureFileId`),
  KEY `fileIdTestDoc` (`testDocFileId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `testing_childs`
--

CREATE TABLE IF NOT EXISTS `testing_childs` (
  `testChildId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `testId` int(10) unsigned NOT NULL,
  `currencyId` int(11) NOT NULL,
  `testChildPrice` decimal(10,2) NOT NULL,
  `testChildExchange` decimal(10,2) NOT NULL,
  `testChildDate` datetime NOT NULL,
  `testChildName` varchar(255) NOT NULL,
  `countryId` char(2) NOT NULL,
  PRIMARY KEY (`testChildId`),
  KEY `countryId` (`countryId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `testing_childs_users`
--

CREATE TABLE IF NOT EXISTS `testing_childs_users` (
  `testChildId` int(10) unsigned NOT NULL,
  `userId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`testChildId`,`userId`),
  KEY `testChildId` (`testChildId`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tmp_users_entries`
--

CREATE TABLE IF NOT EXISTS `tmp_users_entries` (
  `userId` int(10) unsigned NOT NULL,
  `entryId` int(10) unsigned NOT NULL,
  `entryStarred` int(1) unsigned NOT NULL,
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
  `userEmail` char(255) DEFAULT NULL,
  `userPassword` char(255) DEFAULT NULL,
  `userFirstName` char(255) DEFAULT NULL,
  `userLastName` char(255) DEFAULT NULL,
  `userBirthDate` date DEFAULT NULL,
  `userDateAdd` datetime NOT NULL,
  `userLastAccess` datetime DEFAULT NULL,
  `countryId` char(2) DEFAULT NULL,
  `langId` varchar(10) NOT NULL DEFAULT 'en',
  `userFilters` text NOT NULL COMMENT 'un json con los filtros aplicados',
  `facebookUserId` varchar(200) DEFAULT NULL,
  `googleUserId` varchar(200) DEFAULT NULL,
  `resetPasswordKey` varchar(20) DEFAULT NULL,
  `resetPasswordDate` datetime DEFAULT NULL,
  `confirmEmailKey` varchar(20) DEFAULT NULL,
  `confirmEmailDate` datetime DEFAULT NULL,
  `confirmEmailValue` varchar(255) DEFAULT NULL,
  `verifiedUserEmail` int(1) NOT NULL DEFAULT '0',
  `lastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`userId`),
  UNIQUE KEY `userEmail` (`userEmail`),
  UNIQUE KEY `resetPasswordKey` (`resetPasswordKey`),
  UNIQUE KEY `changeEmailKey` (`confirmEmailKey`),
  UNIQUE KEY `changeEmailValue` (`confirmEmailValue`),
  KEY `countryId` (`countryId`),
  KEY `googleUserId` (`googleUserId`),
  KEY `oauth_uid` (`facebookUserId`),
  KEY `langId` (`langId`),
  KEY `userDateAdd` (`userDateAdd`),
  KEY `resetPasswordDate` (`resetPasswordDate`),
  KEY `changeEmailDate` (`confirmEmailDate`),
  KEY `lastUpdate` (`lastUpdate`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userId`, `userEmail`, `userPassword`, `userFirstName`, `userLastName`, `userBirthDate`, `userDateAdd`, `userLastAccess`, `countryId`, `langId`, `userFilters`, `facebookUserId`, `googleUserId`, `resetPasswordKey`, `resetPasswordDate`, `confirmEmailKey`, `confirmEmailDate`, `confirmEmailValue`, `verifiedUserEmail`, `lastUpdate`) VALUES
(1, NULL, NULL, 'anonymous', 'anonymous', NULL, '2013-10-01 00:11:12', '2013-10-01 00:11:15', 'ar', 'en', '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2015-06-16 15:03:44'),
(2, 'admin@creader.com', '63a9f0ea7bb98050796b649e85481845', 'admin', 'person', '0000-00-00', '0000-00-00 00:00:00', '2014-05-15 19:26:13', 'ar', 'en', '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2015-06-16 15:03:44');

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
  `entryStarred` int(1) unsigned NOT NULL,
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
-- Table structure for table `users_friends`
--

CREATE TABLE IF NOT EXISTS `users_friends` (
  `userFrieldId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL,
  `userFriendEmail` varchar(255) NOT NULL,
  `userFriendName` varchar(255) NOT NULL,
  PRIMARY KEY (`userFrieldId`),
  UNIQUE KEY `indexUserIdUserFriendEmail` (`userId`,`userFriendEmail`),
  KEY `userId` (`userId`),
  KEY `userFriendEmail` (`userFriendEmail`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

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

-- --------------------------------------------------------

--
-- Table structure for table `usertracking`
--

CREATE TABLE IF NOT EXISTS `usertracking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(100) NOT NULL,
  `user_identifier` varchar(255) NOT NULL,
  `request_uri` text NOT NULL,
  `timestamp` varchar(20) NOT NULL,
  `client_ip` varchar(50) NOT NULL,
  `client_user_agent` text NOT NULL,
  `referer_page` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `entities_files`
--
ALTER TABLE `entities_files`
  ADD CONSTRAINT `entities_files_ibfk_2` FOREIGN KEY (`fileId`) REFERENCES `files` (`fileId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `entities_files_ibfk_3` FOREIGN KEY (`entityTypeId`) REFERENCES `entities_type` (`entityTypeId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `entries`
--
ALTER TABLE `entries`
  ADD CONSTRAINT `entries_ibfk_1` FOREIGN KEY (`feedId`) REFERENCES `feeds` (`feedId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `entries_tags`
--
ALTER TABLE `entries_tags`
  ADD CONSTRAINT `entries_tags_ibfk_1` FOREIGN KEY (`entryId`) REFERENCES `entries` (`entryId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `entries_tags_ibfk_2` FOREIGN KEY (`tagId`) REFERENCES `tags` (`tagId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD CONSTRAINT `feedbacks_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `feeds`
--
ALTER TABLE `feeds`
  ADD CONSTRAINT `feeds_ibfk_1` FOREIGN KEY (`statusId`) REFERENCES `status` (`statusId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `feeds_tags`
--
ALTER TABLE `feeds_tags`
  ADD CONSTRAINT `feeds_tags_ibfk_1` FOREIGN KEY (`feedId`) REFERENCES `feeds` (`feedId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `feeds_tags_ibfk_2` FOREIGN KEY (`tagId`) REFERENCES `tags` (`tagId`) ON DELETE CASCADE ON UPDATE CASCADE;

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
-- Constraints for table `shared_by_email`
--
ALTER TABLE `shared_by_email`
  ADD CONSTRAINT `shared_by_email_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `shared_by_email_ibfk_2` FOREIGN KEY (`entryId`) REFERENCES `entries` (`entryId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `shared_by_email_ibfk_3` FOREIGN KEY (`userFriendId`) REFERENCES `users_friends` (`userFrieldId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `states`
--
ALTER TABLE `states`
  ADD CONSTRAINT `states_ibfk_1` FOREIGN KEY (`countryId`) REFERENCES `countries` (`countryId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tasks_email`
--
ALTER TABLE `tasks_email`
  ADD CONSTRAINT `tasks_email_ibfk_1` FOREIGN KEY (`langId`) REFERENCES `languages` (`langId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `testing`
--
ALTER TABLE `testing`
  ADD CONSTRAINT `testing_ibfk_1` FOREIGN KEY (`testPictureFileId`) REFERENCES `files` (`fileId`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `testing_ibfk_2` FOREIGN KEY (`testDocFileId`) REFERENCES `files` (`fileId`) ON DELETE SET NULL ON UPDATE SET NULL;

--
-- Constraints for table `tmp_users_entries`
--
ALTER TABLE `tmp_users_entries`
  ADD CONSTRAINT `tmp_users_entries_ibfk_3` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tmp_users_entries_ibfk_4` FOREIGN KEY (`entryId`) REFERENCES `entries` (`entryId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`countryId`) REFERENCES `countries` (`countryId`),
  ADD CONSTRAINT `users_ibfk_3` FOREIGN KEY (`langId`) REFERENCES `languages` (`langId`);

--
-- Constraints for table `users_entries`
--
ALTER TABLE `users_entries`
  ADD CONSTRAINT `users_entries_ibfk_5` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `users_entries_ibfk_6` FOREIGN KEY (`entryId`) REFERENCES `entries` (`entryId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `users_entries_ibfk_7` FOREIGN KEY (`tagId`) REFERENCES `tags` (`tagId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `users_entries_ibfk_8` FOREIGN KEY (`feedId`) REFERENCES `feeds` (`feedId`) ON DELETE CASCADE ON UPDATE CASCADE;

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
-- Constraints for table `users_friends`
--
ALTER TABLE `users_friends`
  ADD CONSTRAINT `users_friends_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE ON UPDATE CASCADE;

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
