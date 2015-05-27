# ************************************************************
# Sequel Pro SQL dump
# Version 3408
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: 127.0.0.1 (MySQL 5.1.41)
# Database: biostor
# Generation Time: 2011-12-09 15:43:38 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table ann mag nat hist
# ------------------------------------------------------------



# Dump of table bhl_item
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bhl_item`;

CREATE TABLE `bhl_item` (
  `ItemID` int(11) DEFAULT NULL,
  `TitleID` int(11) DEFAULT NULL,
  `BarCode` varchar(50) DEFAULT NULL,
  `MARCItemID` varchar(50) DEFAULT NULL,
  `CallNumber` varchar(100) DEFAULT NULL,
  `VolumeInfo` varchar(100) DEFAULT NULL,
  `ItemURL` varchar(100) DEFAULT NULL,
  `LocalID` varchar(100) DEFAULT NULL,
  `Year` varchar(20) DEFAULT NULL,
  `InstitutionName` varchar(255) DEFAULT NULL,
  `ZQuery` varchar(200) DEFAULT NULL,
  KEY `ItemID` (`ItemID`),
  KEY `TitleID` (`TitleID`),
  KEY `VolumeInfo` (`VolumeInfo`),
  KEY `InstitutionName` (`InstitutionName`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table bhl_page
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bhl_page`;

CREATE TABLE `bhl_page` (
  `PageID` int(11) DEFAULT NULL,
  `ItemID` int(11) DEFAULT NULL,
  `Year` varchar(20) DEFAULT NULL,
  `Volume` varchar(20) DEFAULT NULL,
  `Issue` varchar(20) DEFAULT NULL,
  `PagePrefix` varchar(20) DEFAULT NULL,
  `PageNumber` varchar(20) DEFAULT NULL,
  `PageTypeName` varchar(40) DEFAULT NULL,
  KEY `PageID` (`PageID`),
  KEY `ItemID` (`ItemID`),
  KEY `PageTypeName` (`PageTypeName`),
  KEY `PagePrefix` (`PagePrefix`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table bhl_page_name
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bhl_page_name`;

CREATE TABLE `bhl_page_name` (
  `NameBankID` int(11) unsigned NOT NULL,
  `NameConfirmed` varchar(255) DEFAULT NULL,
  `PageID` int(11) DEFAULT NULL,
  `CreationDate` datetime DEFAULT NULL,
  KEY `NameConfirmed` (`NameConfirmed`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
/*!50100 PARTITION BY KEY (NameConfirmed)
PARTITIONS 100 */;



# Dump of table bhl_title
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bhl_title`;

CREATE TABLE `bhl_title` (
  `TitleID` int(11) NOT NULL DEFAULT '0',
  `MARCBibID` varchar(50) DEFAULT NULL,
  `MARCLeader` varchar(24) DEFAULT NULL,
  `FullTitle` text,
  `ShortTitle` varchar(255) DEFAULT NULL,
  `PublicationDetails` varchar(255) DEFAULT NULL,
  `CallNumber` varchar(100) DEFAULT NULL,
  `StartYear` int(11) DEFAULT NULL,
  `EndYear` int(11) DEFAULT NULL,
  `LanguageCode` varchar(10) DEFAULT NULL,
  `TL2Author` varchar(100) DEFAULT NULL,
  `TitleURL` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`TitleID`),
  FULLTEXT KEY `ShortTitle_2` (`ShortTitle`) /*!50100 WITH PARSER `bi_gram` */ 
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table bhl_title_identifier
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bhl_title_identifier`;

CREATE TABLE `bhl_title_identifier` (
  `TitleID` int(11) DEFAULT NULL,
  `IdentifierName` char(40) DEFAULT NULL,
  `IdentifierValue` char(125) DEFAULT NULL,
  KEY `TitleID` (`TitleID`),
  KEY `IdentifierName` (`IdentifierName`),
  KEY `IdentifierValue` (`IdentifierValue`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table col_tree
# ------------------------------------------------------------

DROP TABLE IF EXISTS `col_tree`;

CREATE TABLE `col_tree` (
  `record_id` int(11) NOT NULL DEFAULT '0',
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `left_id` int(11) NOT NULL DEFAULT '0',
  `right_id` int(11) NOT NULL DEFAULT '0',
  `weight` int(11) NOT NULL DEFAULT '1',
  `path` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`record_id`),
  UNIQUE KEY `left_id` (`left_id`),
  UNIQUE KEY `right_id` (`right_id`),
  UNIQUE KEY `path` (`path`),
  KEY `parent_id` (`parent_id`),
  KEY `weight` (`weight`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table feed
# ------------------------------------------------------------



# Dump of table fieldiana
# ------------------------------------------------------------



# Dump of table ion_rss
# ------------------------------------------------------------



# Dump of table issn
# ------------------------------------------------------------



# Dump of table jstor
# ------------------------------------------------------------



# Dump of table page
# ------------------------------------------------------------

DROP TABLE IF EXISTS `page`;

CREATE TABLE `page` (
  `PageID` int(11) NOT NULL,
  `ItemID` int(11) NOT NULL,
  `FileNamePrefix` varchar(50) DEFAULT NULL,
  `SequenceOrder` int(11) DEFAULT NULL,
  `PageDescription` varchar(255) DEFAULT NULL,
  `Illustration` tinyint(4) DEFAULT '0',
  `Note` varchar(255) DEFAULT NULL,
  `FileSize_Temp` int(11) DEFAULT NULL,
  `FileExtension` varchar(5) DEFAULT NULL,
  `CreationDate` datetime DEFAULT NULL,
  `LastModifiedDate` datetime DEFAULT NULL,
  `CreationUserID` int(11) DEFAULT '1',
  `LastModifiedUserID` int(11) DEFAULT '1',
  `Active` tinyint(4) DEFAULT '1',
  `Year` varchar(20) DEFAULT NULL,
  `Series` varchar(20) DEFAULT NULL,
  `Volume` varchar(20) DEFAULT NULL,
  `Issue` varchar(20) DEFAULT NULL,
  `ExternalURL` text,
  `IssuePrefix` varchar(20) DEFAULT NULL,
  `LastPageNameLookupDate` datetime DEFAULT NULL,
  `PaginationUserID` int(11) DEFAULT NULL,
  `PaginationDate` datetime DEFAULT NULL,
  `AltExternalURL` text,
  PRIMARY KEY (`PageID`),
  KEY `ItemID` (`ItemID`),
  KEY `SequenceOrder` (`SequenceOrder`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table rdmp_author
# ------------------------------------------------------------

DROP TABLE IF EXISTS `rdmp_author`;

CREATE TABLE `rdmp_author` (
  `author_id` int(11) NOT NULL AUTO_INCREMENT,
  `author_cluster_id` int(11) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `forename` varchar(255) DEFAULT NULL,
  `suffix` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`author_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table rdmp_author_reference_joiner
# ------------------------------------------------------------

DROP TABLE IF EXISTS `rdmp_author_reference_joiner`;

CREATE TABLE `rdmp_author_reference_joiner` (
  `author_id` int(11) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `author_order` int(11) DEFAULT NULL,
  KEY `author_id` (`author_id`),
  KEY `reference_id` (`reference_id`),
  CONSTRAINT `rdmp_author_reference_joiner_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `rdmp_author` (`author_id`) ON DELETE CASCADE,
  CONSTRAINT `rdmp_author_reference_joiner_ibfk_2` FOREIGN KEY (`reference_id`) REFERENCES `rdmp_reference` (`reference_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table rdmp_citation_string
# ------------------------------------------------------------

DROP TABLE IF EXISTS `rdmp_citation_string`;

CREATE TABLE `rdmp_citation_string` (
  `citation_string_id` int(11) NOT NULL AUTO_INCREMENT,
  `citation_cluster_id` int(11) NOT NULL,
  `citation_string` text NOT NULL,
  `citation_object` text,
  PRIMARY KEY (`citation_string_id`),
  KEY `citation_string` (`citation_string`(255)),
  KEY `citation_cluster_id` (`citation_cluster_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table rdmp_documentcloud
# ------------------------------------------------------------

DROP TABLE IF EXISTS `rdmp_documentcloud`;

CREATE TABLE `rdmp_documentcloud` (
  `reference_id` int(11) unsigned NOT NULL,
  `page` int(11) DEFAULT NULL,
  `ocr_text` text,
  KEY `reference_id` (`reference_id`),
  KEY `page` (`page`),
  FULLTEXT KEY `ocr_text` (`ocr_text`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table rdmp_issn_title_joiner
# ------------------------------------------------------------

DROP TABLE IF EXISTS `rdmp_issn_title_joiner`;

CREATE TABLE `rdmp_issn_title_joiner` (
  `issn` char(9) NOT NULL,
  `TitleID` int(11) NOT NULL,
  KEY `issn` (`issn`),
  KEY `TitleID` (`TitleID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table rdmp_locality
# ------------------------------------------------------------

DROP TABLE IF EXISTS `rdmp_locality`;

CREATE TABLE `rdmp_locality` (
  `locality_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `loc` point NOT NULL,
  `woeid` int(11) NOT NULL DEFAULT '0',
  `latitude` double NOT NULL,
  `longitude` double NOT NULL,
  PRIMARY KEY (`locality_id`),
  SPATIAL KEY `loc` (`loc`),
  KEY `woeid` (`woeid`),
  KEY `latitude` (`latitude`,`longitude`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table rdmp_locality_page_joiner
# ------------------------------------------------------------

DROP TABLE IF EXISTS `rdmp_locality_page_joiner`;

CREATE TABLE `rdmp_locality_page_joiner` (
  `PageID` int(11) NOT NULL,
  `locality_id` int(11) NOT NULL,
  KEY `locality_id` (`locality_id`),
  KEY `PageID` (`PageID`),
  KEY `PageID_2` (`PageID`,`locality_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table rdmp_log
# ------------------------------------------------------------

DROP TABLE IF EXISTS `rdmp_log`;

CREATE TABLE `rdmp_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference_id` int(11) DEFAULT NULL,
  `ip` int(11) unsigned DEFAULT NULL,
  `useragent` varchar(255) DEFAULT NULL,
  `accessed` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `doctype` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table rdmp_namestring
# ------------------------------------------------------------

DROP TABLE IF EXISTS `rdmp_namestring`;

CREATE TABLE `rdmp_namestring` (
  `namestring_id` int(11) NOT NULL AUTO_INCREMENT,
  `namestring` varchar(255) DEFAULT NULL,
  `NameBankID` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`namestring_id`),
  UNIQUE KEY `name` (`namestring`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table rdmp_oclc_title_joiner
# ------------------------------------------------------------

DROP TABLE IF EXISTS `rdmp_oclc_title_joiner`;

CREATE TABLE `rdmp_oclc_title_joiner` (
  `oclc` int(11) NOT NULL,
  `TitleID` int(11) NOT NULL,
  KEY `oclc` (`oclc`),
  KEY `TitleID` (`TitleID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table rdmp_reference
# ------------------------------------------------------------

DROP TABLE IF EXISTS `rdmp_reference`;

CREATE TABLE `rdmp_reference` (
  `reference_id` int(11) NOT NULL AUTO_INCREMENT,
  `reference_cluster_id` int(11) NOT NULL DEFAULT '0',
  `genre` enum('article','book','chapter') NOT NULL DEFAULT 'article',
  `title` text,
  `secondary_title` varchar(255) DEFAULT NULL,
  `volume` varchar(16) DEFAULT NULL,
  `series` varchar(16) DEFAULT NULL,
  `issue` varchar(16) DEFAULT NULL,
  `spage` varchar(16) DEFAULT NULL,
  `epage` varchar(16) DEFAULT NULL,
  `year` char(4) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `issn` char(9) DEFAULT NULL,
  `isbn` char(10) DEFAULT NULL,
  `isbn13` char(13) DEFAULT NULL,
  `oclc` int(11) DEFAULT NULL,
  `doi` varchar(255) DEFAULT NULL,
  `pmid` int(11) DEFAULT NULL,
  `sici` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `hdl` varchar(255) DEFAULT NULL,
  `pdf` varchar(255) DEFAULT NULL,
  `lsid` varchar(255) DEFAULT NULL,
  `PageID` int(11) NOT NULL DEFAULT '0',
  `abstract` text,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`reference_id`),
  KEY `secondary_title` (`secondary_title`),
  KEY `volume` (`volume`),
  KEY `series` (`series`),
  KEY `issue` (`issue`),
  KEY `spage` (`spage`),
  KEY `year` (`year`),
  KEY `date` (`date`),
  KEY `issn` (`issn`),
  KEY `PageID` (`PageID`),
  KEY `lsid` (`lsid`),
  KEY `doi` (`doi`),
  KEY `oclc` (`oclc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table rdmp_reference_citation_string_joiner
# ------------------------------------------------------------

DROP TABLE IF EXISTS `rdmp_reference_citation_string_joiner`;

CREATE TABLE `rdmp_reference_citation_string_joiner` (
  `reference_id` int(11) DEFAULT NULL,
  `citation_string_id` int(11) DEFAULT NULL,
  KEY `reference_id` (`reference_id`),
  KEY `citation_string_id` (`citation_string_id`),
  CONSTRAINT `rdmp_reference_citation_string_joiner_ibfk_1` FOREIGN KEY (`reference_id`) REFERENCES `rdmp_reference` (`reference_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table rdmp_reference_cites
# ------------------------------------------------------------

DROP TABLE IF EXISTS `rdmp_reference_cites`;

CREATE TABLE `rdmp_reference_cites` (
  `reference_id` int(11) NOT NULL,
  `citation_string_id` int(11) NOT NULL,
  `citation_order` int(11) NOT NULL DEFAULT '0',
  KEY `reference_id` (`reference_id`),
  KEY `citation_string_id` (`citation_string_id`),
  KEY `citation_order` (`citation_order`),
  CONSTRAINT `rdmp_reference_cites_ibfk_1` FOREIGN KEY (`reference_id`) REFERENCES `rdmp_reference` (`reference_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table rdmp_reference_page_joiner
# ------------------------------------------------------------

DROP TABLE IF EXISTS `rdmp_reference_page_joiner`;

CREATE TABLE `rdmp_reference_page_joiner` (
  `reference_id` int(11) NOT NULL,
  `PageID` int(11) NOT NULL,
  `page_order` int(11) NOT NULL,
  KEY `reference_id` (`reference_id`),
  KEY `PageID` (`PageID`),
  CONSTRAINT `rdmp_reference_page_joiner_ibfk_1` FOREIGN KEY (`reference_id`) REFERENCES `rdmp_reference` (`reference_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table rdmp_reference_taxon_name_joiner
# ------------------------------------------------------------

DROP TABLE IF EXISTS `rdmp_reference_taxon_name_joiner`;

CREATE TABLE `rdmp_reference_taxon_name_joiner` (
  `reference_id` int(11) DEFAULT NULL,
  `taxon_name_id` int(11) DEFAULT NULL,
  UNIQUE KEY `reference_id_2` (`reference_id`,`taxon_name_id`),
  KEY `reference_id` (`reference_id`),
  KEY `taxon_name_id` (`taxon_name_id`),
  CONSTRAINT `rdmp_reference_taxon_name_joiner_ibfk_1` FOREIGN KEY (`reference_id`) REFERENCES `rdmp_reference` (`reference_id`) ON DELETE CASCADE,
  CONSTRAINT `rdmp_reference_taxon_name_joiner_ibfk_2` FOREIGN KEY (`taxon_name_id`) REFERENCES `rdmp_taxon_name` (`taxon_name_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table rdmp_reference_version
# ------------------------------------------------------------

DROP TABLE IF EXISTS `rdmp_reference_version`;

CREATE TABLE `rdmp_reference_version` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference_id` int(11) DEFAULT NULL,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` int(10) unsigned DEFAULT NULL,
  `oauth` varchar(255) DEFAULT NULL,
  `json` tinytext,
  PRIMARY KEY (`id`),
  KEY `reference_id` (`reference_id`),
  CONSTRAINT `rdmp_reference_version_ibfk_1` FOREIGN KEY (`reference_id`) REFERENCES `rdmp_reference` (`reference_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table rdmp_secondary_author_reference_joiner
# ------------------------------------------------------------

DROP TABLE IF EXISTS `rdmp_secondary_author_reference_joiner`;

CREATE TABLE `rdmp_secondary_author_reference_joiner` (
  `author_id` int(11) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `author_order` int(11) DEFAULT NULL,
  KEY `author_id` (`author_id`),
  KEY `reference_id` (`reference_id`),
  CONSTRAINT `rdmp_secondary_author_reference_joiner_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `rdmp_author` (`author_id`) ON DELETE CASCADE,
  CONSTRAINT `rdmp_secondary_author_reference_joiner_ibfk_2` FOREIGN KEY (`reference_id`) REFERENCES `rdmp_reference` (`reference_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table rdmp_taxon_name
# ------------------------------------------------------------

DROP TABLE IF EXISTS `rdmp_taxon_name`;

CREATE TABLE `rdmp_taxon_name` (
  `taxon_name_id` int(11) NOT NULL AUTO_INCREMENT,
  `namestring_id` int(11) NOT NULL,
  `local_id` int(11) DEFAULT '0',
  `global_id` varchar(255) NOT NULL,
  `versionInfo` char(32) DEFAULT NULL,
  `nameComplete` varchar(255) NOT NULL,
  `uninomial` varchar(255) DEFAULT NULL,
  `genusPart` varchar(255) DEFAULT NULL,
  `specificEpithet` varchar(255) DEFAULT NULL,
  `infraspecificEpithet` varchar(255) DEFAULT NULL,
  `rank` varchar(255) DEFAULT NULL,
  `rankString` varchar(64) DEFAULT NULL,
  `nomenclaturalCode` varchar(255) DEFAULT NULL,
  `authorship` varchar(255) DEFAULT NULL,
  `year` char(4) DEFAULT NULL,
  `publishedIn` text,
  `publishedInCitation` varchar(255) DEFAULT NULL,
  `microreference` char(16) DEFAULT NULL,
  `publication` text,
  PRIMARY KEY (`taxon_name_id`),
  UNIQUE KEY `global_id` (`global_id`),
  KEY `namestring_id` (`namestring_id`),
  KEY `nameComplete` (`nameComplete`),
  KEY `local_id` (`local_id`),
  CONSTRAINT `rdmp_taxon_name_ibfk_1` FOREIGN KEY (`namestring_id`) REFERENCES `rdmp_namestring` (`namestring_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table rdmp_text
# ------------------------------------------------------------

DROP TABLE IF EXISTS `rdmp_text`;

CREATE TABLE `rdmp_text` (
  `PageID` int(11) NOT NULL,
  `ocr_text` text NOT NULL,
  PRIMARY KEY (`PageID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table rdmp_text_index
# ------------------------------------------------------------

DROP TABLE IF EXISTS `rdmp_text_index`;

CREATE TABLE `rdmp_text_index` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `object_type` enum('citation','author','title') NOT NULL DEFAULT 'title',
  `object_id` int(11) DEFAULT NULL,
  `object_uri` varchar(255) DEFAULT NULL,
  `object_text` text NOT NULL,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `object_text` (`object_text`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table rdmp_tree_index
# ------------------------------------------------------------

DROP TABLE IF EXISTS `rdmp_tree_index`;

CREATE TABLE `rdmp_tree_index` (
  `reference_id` int(11) NOT NULL DEFAULT '0',
  `path` varchar(255) DEFAULT NULL,
  KEY `path` (`path`),
  KEY `reference_id` (`reference_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table rdmp_user
# ------------------------------------------------------------

DROP TABLE IF EXISTS `rdmp_user`;

CREATE TABLE `rdmp_user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `openid` varchar(255) DEFAULT NULL,
  `username` varchar(32) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table references
# ------------------------------------------------------------



# Dump of table scientific_name_references
# ------------------------------------------------------------



# Dump of table scientific_names
# ------------------------------------------------------------



# Dump of table taxa
# ------------------------------------------------------------





# Replace placeholder table for ann mag nat hist with correct view syntax
# ------------------------------------------------------------

DROP TABLE `ann mag nat hist`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `ann mag nat hist`
AS select
   `rdmp_reference`.`reference_id` AS `reference_id`,
   `rdmp_reference`.`reference_cluster_id` AS `reference_cluster_id`,
   `rdmp_reference`.`genre` AS `genre`,
   `rdmp_reference`.`title` AS `title`,
   `rdmp_reference`.`secondary_title` AS `secondary_title`,
   `rdmp_reference`.`volume` AS `volume`,
   `rdmp_reference`.`series` AS `series`,
   `rdmp_reference`.`issue` AS `issue`,
   `rdmp_reference`.`spage` AS `spage`,
   `rdmp_reference`.`epage` AS `epage`,
   `rdmp_reference`.`year` AS `year`,
   `rdmp_reference`.`date` AS `date`,
   `rdmp_reference`.`issn` AS `issn`,
   `rdmp_reference`.`isbn` AS `isbn`,
   `rdmp_reference`.`isbn13` AS `isbn13`,
   `rdmp_reference`.`oclc` AS `oclc`,
   `rdmp_reference`.`doi` AS `doi`,
   `rdmp_reference`.`pmid` AS `pmid`,
   `rdmp_reference`.`sici` AS `sici`,
   `rdmp_reference`.`url` AS `url`,
   `rdmp_reference`.`hdl` AS `hdl`,
   `rdmp_reference`.`pdf` AS `pdf`,
   `rdmp_reference`.`lsid` AS `lsid`,
   `rdmp_reference`.`PageID` AS `PageID`,
   `rdmp_reference`.`abstract` AS `abstract`,
   `rdmp_reference`.`created` AS `created`,
   `rdmp_reference`.`updated` AS `updated`
from `rdmp_reference`
where (`rdmp_reference`.`issn` = '0374-5481');

/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
