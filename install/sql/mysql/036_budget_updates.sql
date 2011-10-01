
CREATE TABLE IF NOT EXISTS `budgets` (
  `budget_id` int(10) NOT NULL AUTO_INCREMENT,
  `budget_company` int(10) NOT NULL DEFAULT '0',
  `budget_dept` int(10) NOT NULL DEFAULT '0',
  `budget_start_date` date DEFAULT NULL,
  `budget_end_date` date DEFAULT NULL,
  `budget_amount` decimal(10,2) NOT NULL,
  `budget_category` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`budget_id`),
  KEY `budget_start_date` (`budget_start_date`),
  KEY `budget_end_date` (`budget_end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `budgets`
--


-- --------------------------------------------------------

--
-- Table structure for table `budgets_assigned`
--

CREATE TABLE IF NOT EXISTS `budgets_assigned` (
  `budget_id` int(10) NOT NULL AUTO_INCREMENT,
  `budget_project` int(10) NOT NULL,
  `budget_task` int(10) NOT NULL,
  `budget_category` varchar(50) NOT NULL DEFAULT '',
  `budget_amount` decimal(10,2) NOT NULL,
  PRIMARY KEY (`budget_id`),
  KEY `budget_project` (`budget_project`),
  KEY `budget_task` (`budget_task`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;