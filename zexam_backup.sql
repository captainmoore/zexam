-- MySQL dump 10.13  Distrib 5.7.13, for Win64 (x86_64)
--
-- Host: localhost    Database: z-exam
-- ------------------------------------------------------
-- Server version	5.7.13-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `exam_candidates`
--

DROP TABLE IF EXISTS `exam_candidates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_candidates` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sn` int(8) unsigned NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL,
  `sex` varchar(10) NOT NULL DEFAULT '男',
  `unit` varchar(11) DEFAULT '',
  `phone` varchar(30) DEFAULT '',
  `remark` varchar(255) DEFAULT '',
  `birth_time` int(11) DEFAULT '0',
  `photo` varchar(255) NOT NULL DEFAULT '0',
  `create_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_candidates`
--

LOCK TABLES `exam_candidates` WRITE;
/*!40000 ALTER TABLE `exam_candidates` DISABLE KEYS */;
INSERT INTO `exam_candidates` VALUES (1,20190001,'张三','男','保障大队','189123456789','',0,'0',1547018683,1546567334,-1),(2,20190002,'李四','女','保障大队','133123456789','',0,'0',1547018964,1547018964,1),(3,20190003,'王五','男','直属队','13023235689','学术水平高',0,'0',1547021804,1547021804,1),(4,20190004,'张三丰','男','组织部','031188796366','组织部干事',0,'0',1548139242,1548139242,1),(5,0,'关云长','男','','','',0,'0',1548211758,1548211758,1),(6,0,'刘玄德','男','','','',0,'0',1548212011,1548212011,1),(7,0,'张翼德','男','西蜀','99999999','辅助',0,'0',1548212164,1548212164,1),(8,20190010,'孙尚香','女','东吴','','adc稳健11222333嗯嗯111111',0,'0',1548295689,1548295689,1);
/*!40000 ALTER TABLE `exam_candidates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exam_options`
--

DROP TABLE IF EXISTS `exam_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_options` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `question_id` int(10) unsigned DEFAULT NULL,
  `content` varchar(255) NOT NULL,
  `sequence` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_options`
--

LOCK TABLES `exam_options` WRITE;
/*!40000 ALTER TABLE `exam_options` DISABLE KEYS */;
/*!40000 ALTER TABLE `exam_options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exam_paper`
--

DROP TABLE IF EXISTS `exam_paper`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_paper` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_paper`
--

LOCK TABLES `exam_paper` WRITE;
/*!40000 ALTER TABLE `exam_paper` DISABLE KEYS */;
/*!40000 ALTER TABLE `exam_paper` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exam_question_types`
--

DROP TABLE IF EXISTS `exam_question_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_question_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_question_types`
--

LOCK TABLES `exam_question_types` WRITE;
/*!40000 ALTER TABLE `exam_question_types` DISABLE KEYS */;
INSERT INTO `exam_question_types` VALUES (1,'单选'),(2,'多选'),(3,'问答');
/*!40000 ALTER TABLE `exam_question_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exam_questions`
--

DROP TABLE IF EXISTS `exam_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_questions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `paper_id` varchar(255) DEFAULT '',
  `type` int(10) NOT NULL,
  `title` varchar(255) NOT NULL,
  `answer_ids` varchar(255) DEFAULT NULL,
  `option_ids` varchar(255) DEFAULT NULL,
  `time` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_questions`
--

LOCK TABLES `exam_questions` WRITE;
/*!40000 ALTER TABLE `exam_questions` DISABLE KEYS */;
/*!40000 ALTER TABLE `exam_questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exam_score`
--

DROP TABLE IF EXISTS `exam_score`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_score` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `test_id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `candidate_name` varchar(255) NOT NULL,
  `score` float(5,2) NOT NULL,
  `remark` varchar(255) NOT NULL DEFAULT '',
  `list_order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_score`
--

LOCK TABLES `exam_score` WRITE;
/*!40000 ALTER TABLE `exam_score` DISABLE KEYS */;
INSERT INTO `exam_score` VALUES (14,4,4,'张三丰',22.00,'',0),(4,4,2,'李四',56.90,'',0),(6,3,5,'关云长',63.00,'',0),(7,2,6,'刘玄德',55.00,'',0),(8,1,7,'张翼德',85.00,'',0),(9,4,6,'刘玄德',67.00,'',0),(10,5,6,'刘玄德',98.00,'',0),(11,5,4,'张三丰',100.00,'',0),(12,5,3,'王五',65.00,'',0),(13,4,9,'孙尚香',82.86,'',0);
/*!40000 ALTER TABLE `exam_score` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exam_test`
--

DROP TABLE IF EXISTS `exam_test`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_test` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `start_time` int(11) NOT NULL,
  `end_time` int(11) NOT NULL,
  `create_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  `score_num` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_test`
--

LOCK TABLES `exam_test` WRITE;
/*!40000 ALTER TABLE `exam_test` DISABLE KEYS */;
INSERT INTO `exam_test` VALUES (4,'第二次考试','这是今年组织的第二次考试',1546567334,1546567334,1547537001,1547537001,4),(5,'第五次考试','333我问问一定要好好学习天天向上',1548150840,1549446840,1548240723,1548292010,3);
/*!40000 ALTER TABLE `exam_test` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-01-25  9:36:38
