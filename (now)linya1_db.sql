-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 12, 2026 at 05:25 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `linya1_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `user_id` int(10) NOT NULL,
  `user_name` varchar(30) NOT NULL,
  `password` varchar(30) NOT NULL,
  `name` varchar(30) NOT NULL,
  `user_type` int(10) NOT NULL,
  `office` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`user_id`, `user_name`, `password`, `name`, `user_type`, `office`) VALUES
(1, 'admin', 'admin', 'administrator', 1, 1),
(2, 'Astaff1', 'Astaff1', 'Jason', 2, 10),
(3, 'Astaff2', 'Astaff2', 'Jameson', 2, 10),
(4, 'robert', 'robert', 'Robert', 2, 1),
(5, 'badong', '1234', 'badong', 2, 1),
(7, 'roberts', 'roberts', 'roberts', 2, 1),
(8, 'eryel', 'eryel', 'eryel', 2, 10),
(9, 'christian', '12345', 'christian', 2, NULL),
(12, 'lexter', '123', 'lexter', 2, NULL),
(13, 'abcd', '1234', 'abcd', 2, NULL),
(16, 'lexty', 'lexty', 'lexty', 2, 1),
(17, 'genggo', '1234', 'genggo', 2, NULL),
(18, 'jojo', 'jojo', 'jojo', 2, NULL),
(19, 'johndoe', 'johndoe', 'john doe', 2, 10);

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `client_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `safe_name` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `student_number` varchar(20) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL,
  `face_data` longblob DEFAULT NULL,
  `face_path` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`client_id`, `name`, `safe_name`, `phone`, `email`, `student_number`, `course`, `face_data`, `face_path`, `created_at`) VALUES
(20, 'John Lexter B. Armenta', 'john_lexter_b_armenta', '09276164084', 'johnarmenta.8v@gmail.com', NULL, NULL, 0x934e554d5059010076007b276465736372273a20273c6634272c2027666f727472616e5f6f72646572273a2046616c73652c20277368617065273a2028352c20313238292c207d20202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020200aa4cfc5bdcd87ad3ddbe4b33d9cdb3bbd5bf58bbcd73aa0bd8a31763c226c76bda135243ede9bd7bd8013873e1acab7bd178653beb51c18bef08bd53ce6ac413e3d1d0abe49e7c9bde1c857bd343d34bd37867f3d4c7a83bd2681dd3a52fd7a3d19c28dbd6631f0be096fb1bd369fe6bd8c1a21bd9ffe1abc3a2626bdf97ac43d45bc15be060545bd2b860fbd7e742a3de73916bd7ccb00bdf1121a3ea6e767bc4eb143bec6c24dbd1ef1c63be24c7e3e06ec243eec1d553d3e4f9e3d04dda7bbfe03673c7e6429be287f7c3c954d073e346aac3d4ba2f53c728ca93dac87b9bde2edae3b7a782a3d141d43be856e97bc891c043d48f678bd7a8e7cbd004776bd5607923ebf60f03dfae708be9c0bb7bdc1cd493eea6304be5ee1cdbcceb6bdbc14ef29be40344fbeff2f92be15769e3dc137db3e32aa2f3e7c9d32beff4fd13cfd7909be2af791bcf0b1fd3ded47c53d8ab33abd22d5fd3c81e3afbd9051b33c7ea2c93d8871a3bced8688bda3ef4e3e27370fbd2b72083d80a3e4bcbd413bbda1f622bd7b4b973df8b9f1bd3e03e2bbad0f873daa6084bd7401e03b1060d33dc42332be7fd1a73db352eb3cee024a3d1294143df1d2053e63351abe09daf2bdec4fa43de1ba69be747df93deed9513edbe7413d5b2a0b3e6733bb3d4b6da23db10a023d89d2a6bd43884fbe828833bc7442b03dc8ce2f3dc012603d5954553d2cc0fcbdb4f74a3d9049583d78c032bdef2b86bbdef924bde6f304bd6ef6d7bd3dd1243ec031c6bdb42a883e650c95bd1dd96bbe429d03be9f0800bd1f044e3e610534be0eb7b8bdbc260ebd0aa4983c7f45873d166ca8bd39dc72bdb2e1a03d50d1d7bddffcc1beb2cedbbd9e6bbcbda580fbbdc920a9bddd9800bebf28c83ca53b1fbe848082bd83ad24bd57bd8e3d979a07bd33ca92bd7500353e30a8d33c8fb33fbe5706923c4ea0993cdf9e853e0fa9443ee283343d446b02bcbd4717bd36600d3e174c40be436d0a3d65d1bf3da5c42e3e7a2a353d0a13eb3cc42ce0bd8acf66bc5006ed3d1fd843bec6af983b3484293d4fea02be7b7de8bc420789bd3677683e7669263ea6a50dbe808be3bd1dc41c3ebdc2e0bdd6cc37bd6113cabce75411bee5c680be0e1bacbe2ae2b63d249baa3e132a2c3e1cfd87beecab77bd923499bdc0420e397408383e0eaad83dff9b953c60e540bb548d04be340c30bd983f383e5f067dbcc99056bd329b593eca109bbc989c183dc8368fbc7c4c44bb65de85bdd3718b3d041010beb582c03cdaf5173df628ddbb801809396cc9f23d46b102bebf9a883d6d59fa3ce791be3ca046663afd43b73ca6942abe70e2cabd15adab3d915479be9f40003e722e293e4ab4493d39f3e83de54b9b3d4e10633d8e410bbcab6f45bdffff6abe238a5bbca8750b3e421b673db649e03d34c97b3c6c5addbd66d0e53c7384993daefd81bc5cfb30bd58282fbdde7444bd4ef5c2bd19befc3d60c5eebde8b3863ec51aa3bddfd959be6b1bf0bd620c13bdde71513e7d650dbe60dad0bd5e4609bdb91b163de562dd3d8e808abde3c413bd6cc9c13c23c8b6bdc6eab0be5c7ddabde2f64dbdfc23b0bd21d55dbd7561e0bd55141ebc08e22dbef4e966bdd893fbbcd2cfa33dcd5109bdedb290bdd1a1123ee4e9603bdd175bbeaee9133c5327093d36ff833ec388183ea2fbc23dd8fba9bb40f67cbd8e39b33dfe723abea0e4983dc3d0b33d3844103e0432863c95f4e83ce9afe6bd794a873c0546d03dc12221be6c1a573b75a49f3d49c58ebddaf0d3bc80c1c0bd9eda893e8b74163e280f06be0605e6bd1896063e0f97c2bd749578bd170df6bc995a28be5c874cbe6a40b4bec57c0b3da063c93eccb3c43da9db68be4b66f3bc64664dbd42c7bc3c422a383e78b6fe3dd0a8373c753ade3c834f08be3864b1bc84803c3e9aa18cbd5b1339bd4547353efd6909bc229fd53bec4a9abcc27c69bc0a911ebde461813df4c0fabd40577d3a84b2623dfc6e9ebc104c1eb96805043eca170fbe9593633d5edc123dfa02283dade4463d6367d33c27212bbe9b2fefbdce98883d191d5ebead04fd3d49ab243e4a9a753d5f27af3d3036ba3d5586ce3d7d2b16bcf093913a0a1577bec11792bb03afbf3da99f133d6b2ae83d801b4c3d4d907bbd14fdb23df75fac3d341022bd2e572a3d9bc3cabd52fe9f3cd91a39bd9f05ff3db00c60bd1c0f8f3e4509febdcce03ebe6e0c0abe4de0783ccb8d2d3e313511be58099cbdf96c82bd818985bb903f4c3d500177bd39ef8abc1381423dc0e095bdc860c6be18b9a5bd23ebbabd204212bda5fdb9bb8d1b01be7a71d73dbe3d3dbecf85abbd2eaf79bbffee973d3e828dbcd8cb9ebccab81c3ef8f4e53c73872dbe95a6233d1a289b3cb1407b3ebe8e233eabcd8e3d98d2c83cfafab3bc52075b3c529e34becc56513dc4cd023ef8711e3e309f9a3d2e1c643cc850fdbdd8ac0d3b1c95c63cc7123ebe2fbf21bded21bb3c90946fbdb85575bdbe7265bdcf27923e7a74153ece040fbed451c9bd7d781c3e6c9aadbd17ae02bde2032d3c2de10dbef7812abeb18e8abec63aef3d567bc23e993b033ec0c271beb0dace3c9c6c34beada5d4bc2014e73d4b0c963d2efce9bc2668623d3c27b0bd61f902bc2263003e08a048bd6532a5bdd21d2c3ee7edbdbc67ce8f3d2ef9c8bc790c53bd985d56bc9be15d3de05f09be4a8bb4bc20a2853d0a2f44bdd6c14ebd11ece23d2bc950be6d92a83da628823d1838783b3e8e543d07acc63dd5fbcdbd6729d1bd1e746b3d494c95be0607273e4e7c3b3eb0a02d3d018af33dd4bbb33d6988823d6731243d79946fbdb90848becaef23bddd81063eee539d3d5717913d80c5423d4a40c9bd1bdbac3d4848953de0d383bd5c12c1bc661872bd388c80bb34e2ddbd0247203e882be3bdb774843ee306d5bd62c72bbe95941bbe07fb80bc9e8c383e05b656be5935fdbd9cef0fbbf8e76bbbf175d63d802797bdc6539ebd11c2a33d47c9f1bd3f07a2befa7e96bdd05f14be3cbe8abd088683bd06e3aebc3c7b263df34049be50f275bdb54f60bdda702bbcc6bcc3bcacc510bdfd481c3e5eebe2bc64182ebe6600233cb695723d6fb56d3e3e9e013e90c45c3dfa8866bc1fe803bd2763c83da86936be3ee9c43c1a368b3d8470043e436f8e3d7f49f73c2d6411be3bc802bc9cfcf83c80f534bee166833d8f55b73cf39ecdbdeab567bddecb75bd8be1763eb495043e2524bfbda45deabdc8de0d3eca13eabde290aebd53e4d63baa38fabdebe332bee1c499be7b01353d77dca53ef82bd93d16a873bea10ab8bc1c6f44bd3837ce3bfc90243e25d9ca3d2f499c3cd288113c4cdb5cbdf09ebdbbcf30383e141f71bd3a3c71bdc571523ed288e3bc06b7a23c42ac1abd5914acbda712243c067a993d6f7505bea2461b3da6a5173d3bcfb43b163214bda6d2a63ddf941bbe7cf7253d58a49b3d4023beb90d54efbc7f269e3d37f541be18e798bdbe83b93d45795cbe03d5683e43892e3e2acd943d83e6cb3d6ed08a3da2ccb93c24e3fd3cb1a643bdc88e2fbe02dca0bc5c5d273e7edf8e3cb813a53de852ee3b, 'gs://linya-api-bucket/db_pictures/john_lexter_b_armenta/20260224_053509/encoding.npy', '2026-02-24 05:35:12'),
(22, 'Erick', 'erick', '09123455555', 'ddd@gmail.com', NULL, NULL, 0x934e554d5059010076007b276465736372273a20273c6634272c2027666f727472616e5f6f72646572273a2046616c73652c20277368617065273a2028352c20313238292c207d20202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020200a53769fbd28c4a63db789643d9e948e3d5eb2bd3c4c7acfbd9adc88bb01093fbef51c093ef414c9bd60948c3e8d4f94bd4ddd03be24c416be0eca353d376b1b3e36d424be7b49d6bd4b51fabd8b88bdbd60af0cbd07701fbd4888fdbad715ef3b5615aabcb06fb7be3b1480bd3db319be1df4dc3de57292bc0270c73c0122833cf63542bed22d97bd067d083dc08110bdc33436bd722b213ddcea773e1abed93b696df8bd152f19bd6a5bddbcbf43793e2e93493eb45f123dcbc0953c9a67a5bdeb38a93d265142be0c15a0bd1de6073ea90b133e20c6e53c27e37f3d613d9dbd4c68cabcc4317c3ddf067abe1649ac3d096d473df39c30be4edac9bdd7f3023c9885873e3f8aa73d0dbfefbddc30b7bdf3b1163e4ed901bebafb5c3cb3bd1a3c6a1a86bda1fb44be58ada0be1ed76c3cdad3d43ef2a87c3d0ca437beba71db3ccbb5dfbd7e910bbdda27f63d821d9b3d66702fbdabe8a9bdbd4bbcbcada7323c3621c93d31da8bbd99388bbb76490a3e6abc16be4bfa113dd4bea0bdc6d6e5bcad9e98bd6705953ca6ba24bd7039f6bc9221803cc008a5ba08ca013e9cb7103d85f713be593e883dcc1185ba043c433bdc16533df4d23b3c5bf584bd2c0e71bd4a23e53d950285bedbf4873ef56e393ebe6b7cbdd110003edee0303cecf0e33c5a65a7bcaff50e3cde2714be71d207bd7cd0efbb3be9c93ca26fdc3c82a247bdb5bea6bd40f1ae3dab445c3d1a45523d13b62d3ca252b8bd3e4421bcb48c43be1504113e4396d8bde4868d3eace08fbd137b11bea68d08bef2f8c03c41b31d3e65821bbe061dd2bd76bf03be60abbbbd4b4342bdf3ede7bc8797fabb94af273cbcc30abc604cb4be813889bdb9c319beb5dab73d24b5a8bcecb7af3c3702893cf5c83fbe5ce49abd3e25123d5e2800bdba634dbd1871163dafbd793ef469e23b1509f0bdd0b9f6bccc011dbd45c2793e2a1a473eaa84f13ce85b8f3c7d1eaebdba6daa3d76db43beb57fa3bd13de073efb811a3e71d9d03c38176f3d908f9bbd14ac1abd7eb8a53d328f6ebed72c9e3d17f30c3d732a3cbe7a44c9bd7c5e4dba47d5873e005db53d97a7febd1caab5bd2be21b3ee7bff6bdca0e403c039b273c2f098abd0d2944be34f79abe8ac1a73cf173d33e18007a3dfb7639beddff953c4b00f3bd90270fbd7ecfce3d4823963de4a81dbdbf14b7bde82d5dbc9864453c3afbd83da580a1bd2cbd86bbdc66103ed70a1cbea4fb1c3d1953b2bdb6fbd2bca0f98dbd6e79843cd43ef7bc1bb1b8bcca98903c00b713381bdcfc3d12d70d3d3c7819be1e1a793da4c209bc400e7f39dd9a4f3d6cf443ba1e1361bda77f5bbd7c96033e782f88bea44a8b3e5c2a383e925091bdcde5053ea4e8203c4f428e3cb8225ebcc8be7ebb3aa316be958a34bd239edf3b4401033d2069e23cdd1c50bde5e3c0bd2b57c33dd4d08d3d58fa703d9411e93c84a6afbdb5a91abcd45936be521f2d3ee7b3c2bd5935813ef85862bd2dc52dbecf99f7bd3c6d073d4b28123e6e9d31be346ed3bd90aaddbdae69e9bd152c86bd17f28fbc44e3023db36a343c06e505bdce88b8beb9cf56bddabf1bbe7050b53d78fc61bc60a6c53a3a75993c2dab5bbedba5ecbd5e4c6d3d7a323e3cf4c278bda446ec3c59bc863e7a81bb3b59c7c2bd1e9dfabc0c13b4bc3454853eac303f3e5cab3b3d3093b9bb58c1cdbda4e2d33db27543be06eb55bd8a141b3e44541e3e4e12f63cf09f883d6f39e4bdcab986bb0ed6543d15e486be38acb53dee79683d53d71dbec4d6a0bd3474ee3acc3e843e870c993d0f0bfbbd546adfbd2ab3163e97aaf8bdbd751e3d9c63613dfade98bd169258be5c919ebef7e91c3c9f46ce3e45f4b73dfbf526bec05dcb3a403aeabd467878bd71cdba3db4e3c33d2a7337bd82468abd2a777abc5b75013d672db23d23ac06bd6dd17bbd0c4b163e77cf07beb22d6e3d32549fbdf9b672bc4fd1debd84a17b3ca3afabbdba0a10bd007c5c3cb2da823cd237f23deebe7a3d3bb80fbe226f8b3de207a6bcb67e15bc3c97db3c02ae75bb9e48adbdbaa22dbd1297093e053596be54c4883e8b6c3d3ed7be61bda8f4003e06ea253c34609bbb304d68bade25f53be7c52cbeb3434dbd849247bc6c1a013ccda5963d4da915bd1c2dc8bd06dec73d92319f3d92f47c3dc0b3163d3bd7a7bd26b1f9bc716743be7fd6213e79f5a2bdb9f7843e0bb05abc847302bea1f711be2b97803def3cf13d2ee419be4748f2bdae91e1bd169fecbd11d579bdf43eccbc85a4173d33bba5bc1ebfe8bc6b86bfbe3bc0b0bdfae538beaf82f93d85b094bc1466403bb67308bcab1944be145c99bd6b845e3c50448c3bb6e725bd8f8dc5bb6281803e4573d73c18c9b3bd984a14bc924897bd4679763ef99b603ef317283c9a962d3db4efc4bdf4a0d73dafee3fbe5df704bd84341d3edab7223ec98e403d7fcea03da4198fbd6ae147bcb861bb3bfa7789be91a9c13d739f5d3dfabcdcbd496995bdc897433b33da953e32aa043e6563e1bd95aed9bd37ef233e1226efbdcbd7b23ccc13603d277ac9bd16b24ebe897186be30a47a3d8db4ca3e7c4ecc3da80741be4671813b92c838be55e878bd20978c3d6d7f523d5ee15dbda20547bdbfc023bd38a8cd3c4d48113e20e45abdad735fbd59633e3e958cfebd88b86d3da8f75ebd0779043c9443a5bd58819aba864975bd3e5491bd72f0853d3c8441bb0de3fc3db67b4e3d5d7003be5defac3df48afdbce896263cfef6273d835e51bc33f5a5bd638342bd5e4d213e56e083be284a923e72261e3e936445bd2d50ee3df22af43b8d67083d762d26bd728db1bc8405f0bd668d09bd1750b5bccbec773d2cea113dee11a5bbb763d5bdef17ae3dfe9e763d8ac48a3c34cc07bd4d90febd1c45ea3d8bbab2bd0f2c033ec1742abd866a5c3eb1e46fbd263259be9f8191bd2035d7bc50fd1f3ebac527be4f348dbd53ad06be4ef934bd081e03bd3dab84bc65667e3c17bc013d744732bc8f6fa6bed00a15bd93bc01be9efa3b3d4bae6fbddc06f03c040612bc637d63be5aac33be93970c3da2f0233c1777c0bd70ec2dbdbf88453e1d961d3d52de1dbe10f66e3dbc3e573d9fe09a3e73ab373e2cb4543dccda68bc7ed3d8bdad65f03ddf648abe522f7a3c8641583d5649133eac1f943dc6aa543ddd1817be8886b73d83c0843d861a84bec2bea63da2d7023e6c3671bd1dac0fbd0469373db5815a3eb779973d2f24e9bd49af92bde0a1103e16c911becca8cd3cbe20be3c64fb24bdaa632cbe2cc88abe15589b3dbd04c33e84970e3e783d0bbe141a33bdee071bbec0e3583d2ec3c23dc70c893d016d6cbde6b0b7bd91cd95bd40ec173c5fee993d6cef2cbb576599bd6f463b3e17b50cbd94159bbb80afa7bce236ea3b063d1fbe73b73f3d12d4e6bd08f7acbc8f95943d135574bde80a8b3d6810dd3db2f54dbe9a86003eea42f9bc5e15d83b23c88d3d7aa97b3be1096ebdfd99a93cd926853d02e575bea7eea33e28863f3e6849f7bc020ab33dc2f4603d7dff19bd2c5f643cf454abbbec3e37be08ea86bdf577983cf8b022bc3dffa53d1c3b02bc, 'gs://linya-api-bucket/db_pictures/erick/20260224_101000/encoding.npy', '2026-02-24 10:10:03'),
(23, 'Ricardo Salvador IV', 'ricardo_salvador_iv', '09915261467', 'ricksiv0107@gmail.com', '20220629-S', 'BSCS', 0x934e554d5059010076007b276465736372273a20273c6634272c2027666f727472616e5f6f72646572273a2046616c73652c20277368617065273a2028322c20313238292c207d20202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020200a257cb7bd1913073d8abecb3d3bc877bd0d0fabbd9d81843beebd75bd84450fbe9bea103eb1de35bef6db9c3e16621e3ca32edfbd9ade59bebd528d3d84620f3e053202bebadf47bef254f9bcc8b845bc93268d3d8d2e4bbd8246b0baad2bd53d9be236be9a53d5be760a1abe9aa5d5bd0c1a4f3c847378bd484a38bdec7fd23deec859be4dc3a4bcc6358cbdfc21e13d52291d3d5309ddbcfe68a93d581be03b0d7a66bed2c4a13d9a16103c31c85b3ebda9333ec05a953cf893933db5f708bc93e4ce3d3d7d35be4acac73d7024d83d5a55293e2ad019bc46ebb63de87414be825f9abc36bc553d2b6bf2bd5c9b563d0cd09a3a4a55aebd56a8edbce368c3bda4b44b3efaca0b3e351390bdfdc8eebd1d81473e22f2a1bd3f8e83bde87e363caa914bbe585714be0106a3be8d49b13d3bafca3edf95ed3d1c1488be7435ad3dc48bd1bd1550573d5afd633cd10c8c3d5a9ebabc6d59c03c92db0cbe1847ae3c71982c3ec0330a390afb143d200b7e3eefbcadbc332e5f3d468a933d344dc4bc08427cbd889a743c830903be4eb8923b606653bcd1a18dbdc25b2dbda4f6793d375c51be262b1a3edaec9c3cc04bb23cf4bdab3cba1ed83d166711be7096a2bd7252cd3d151175be6d481d3e1ac32d3ea107883de7db383e6852013edfdb0a3e878261bddea267bd97b12fbe93030cbd3255d63d2d22a2bd28b94abb24dbaa3dda63a1bdb8a3c23d83eb933dd228b1bdad35c7bd9e2263bc4b2033bd5e48a4bd9a67f43d3178e8bdd51a9a3e0ce44bbca32a39be09327abe8a5a6a3c8796183e564c21be90d00ebe00fc47bb767bca3b3c50033eb2656cbd1666123b0cb2a63d10440ebe62e4bdbe8741d0bde91af4bdbe5c4dbc535d12be34cedabd439d0b3d110e33bea91b4ebd9d8f91bd27546a3daa7cd13b6c2e83bce979da3d5cb5433b8da566be5d64163db0d1693bc0ea563e298a563e75a484bb15ef583d5942b0bdad4ed43d537d41bebaf8683d5191f73d134a043ee9f5193da6e2493d5499f8bdb17f39bd4eaf843d71cafcbd481a423c5e26bbbcac3688bdfbbb35bdaff3d8bde760413e9004b63de082d6bdba0f1abe75a3113edb5af6bd1fe9d0bd1821973d6ebf05be861d2fbebcc798bec88e683d3076c53ee650213ec57c90be2e5f113d08572dbef08a16bd05c9723d6373a33d40d4cfbbd95cd03de2dc07be1269d53c90f2273ee25e93bdfedeed3cf5f1813e449fdd3c3d88103e6dd16b3ded9cfb3bc66a5bbd4cce613d0b470ebee198eabcfc383b3c1c0fd8bd243906bde6266e3d3c9f41be1ca81f3ead3e803d4894863c973ad7bc8354fa3c3a76cbbd188756bdcb1ae23d480a83be53752b3e57654e3edab4ca3d95da1a3ebf2c043e9aa7903df4612bbd11e30dbed4df12be4ade4c3c41a5df3dbe7939bd7e0c313d6a19c93c, 'gs://linya-api-bucket/db_pictures/ricardo_salvador_iv/20260227_031426/encoding.npy', '2026-02-27 11:14:29'),
(24, 'Aeryhiel John Mojica', 'aeryhiel_john_mojica', '09931131311', 'aeryhielmojica05@gmail.com', '20220501-S', 'BSCS', 0x934e554d5059010076007b276465736372273a20273c6634272c2027666f727472616e5f6f72646572273a2046616c73652c20277368617065273a2028352c20313238292c207d20202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020200a62b9c7bd849ac83d85ded7bb1dc47abd10e635bd12c861bd2254c1bd1d361abe73182b3ebd84aabd8602713e2ab31dbd3e3e5cbe218007bec68036bdda20313e6b0149be2d5de9bdfa269f3c85ea83bc5dc07c3d8aab0c3da43a1e3deca36e3cfeb5b0bd5a8cacbedec817bef0e9cbbdab79e1bc2438683bdcc4063ca9f8003df8bb17bea62990bd0ff88a3dfacba43d0879dbbc911432bdacde443e71f420bcc82337be172278bd73d4d63db092773ea5250d3ee1806c3d2006a9ba1dfc3dbe9ba8ce3d33f316be32e7603dff680f3ea2b13b3d7020853d0e4e913c9e80dcbd9ab3543d4d552a3ed03155be891a123cb2349a3de84eaebdd55bbbbd62e891bd8bbd963e8134113eab230ebe2a8737bef03d1f3e068007bed336f0bd2c5e633d7c3106be7ffb56bea4069fbe003ff03b6c58b33e94c0bd3df4f430be1e2f853d5c3ef0baf10c1abd9943193e5cbf473e04e9c2bc107e013d868981bd9370dfbc383f4b3ea4d86bbdd3a5f0bc94c7603eda4902bd81f9fb3de70c343d5580083d956760bd0eafd13cc6023dbe8a5e4ebd89bfb23d2779c63b7c30953b7d19e33d8814cfbde56fca3d4a09213d8fae85bb56fa043d4a76a0bd3e8330bd9a0faabd09d11e3eb6914ebeb8b0663ed18bcf3d429a853d15e2233edcd4f63d311ede3dc3e5b1bc0a779bbd9e0a71be14f1ae3c1712c53d500d05bdcfd7cb3dad88a6bcaeb7c5bd3301cb3d4c02063ca40993bd9d6b73bd6a04e4bb4d8ffebd665a1ebe00b64c3eac4ad9bd284a7b3e543ea1bd3b5055be80edb7bde76792bde352393e5ea859be2fb707bef492423b34b08cbb07db5f3d35eca53a24a22e3bc43a473d8d09fbbdfc02b9be925becbda2eb04bec9da353c025f623c28fda8bc3d36b93bbf1c4dbe0aeaaabdac18fa3cc469933db919cebc772aa5bd3d1d3e3e8002a0bc9cef66bec00a10b971f8963dd121623e8881083e54d1a03d4c9229bc91af33beed540d3e486e2dbe9782973d3a83133e9821263d74618b3d230a923cd07211be3f89063d381e0f3e8a9b5cbeabecd93cb656413d964b05bdde0371bdf1b2e1bd7d70953e58de343efca9f6bdea313ebe5823333e6251f0bdb0a09cbdb4a4c23dd63609bec4e34abe43089abe13a1103c881dd63ef425af3d34683ebebc8d393df5d7ebbc8ab933bdcc6a133e82f9283e6fc19d3cd29be13b1402f5bcefd63bbdbb2a753e2a0b3bbd766b76bde7b1643e4cac32bd4947953d68bb203d27a96a3d1645b3bd9644673ce09b27bee5788ebdb078c83d4e78033d72739cbc9f39c23d523805be0b33eb3dd64e493d03764b3c875d903db16b61bc11768dbd349d9dbd7d6d433e38a56dbef56f893e5313a73d1f58a23d49341d3e2ef6e43d2eb9bc3d8c82cebc825f4abdca986cbeedd433bd926bd03dddf11abd1771ef3dbb91bebbf74bb7bd91aafe3d30af333b19ca99bdfc228bbd926801bd32b7d6bd475f25be78b5423eaa08e1bdaf13673e4e26a4bd429d7dbea9d8c2bd1584b4bdcbb04b3e36a944beca5af9bd4e39943b1c9a8cbbdb502b3ddce78f3cb22eef3cb837443d96f265bd80f2b1be37bbdabda66a03be9e8897bcee7a2fbc3439f4bc272ac1bb613afdbd602487bdfe73973c047a703d54992fbd81b98ebdab40413e343795bc51245fbea6410fbda2f24a3d5c65583eb4d3123eac24833d747034bc380443be9c58073e330423bef72de33c8c34043e98c6753c9e854c3da01cad3b48d70cbe7e6ff53bcab92f3e962744be74e8babb98a63d3d924859bd527766bd7d29c8bdacb7a63ea32d273e8c3c10be463a2ebeeecc3d3e589e0bbe40b8d9bd23763b3d62cee7bd3d332fbe99c986be90c5cb3adf09b93ebce4983d62cf2bbee2fe013dbef574bd399510bd7caee63d23a7203ea0fca1bca8cce73bc14f83bd6d70e7bd5bcc5a3ea0a082bde432eebce0c9683edcd436bd96fef93dd91f413dade5c93cb264f4bd55d8e03c04b332be147aacbd27aa943d7e0f8cbc06bd70bb89afd63da72aeabd9ec5e73d9bd8eb3c637c063d55358c3d06663ebce68061bd43a3c5bdcf8b4d3e3f4835be4a757d3eadd8af3d08b1ed3d11e5193ec975173e4657ca3d42751ebd960f3ebd7ebd72beda1095bb1281f33da0ab30bd3798c53d68830b3bc9371ebecabcad3d8a36503cb2c9b5bcec116dbdad18bfbd836e99bde3dff6bda200323ec0418cbd769e663e14bf42bdd2414dbe0a9edabd54d32dbdd4de1e3eee3220be9715cebd94f75c3b3c779a3c54a3e23da4a39b39d436bd3c4c530f3cde63abbdfd0eb9be7a2f22bee9d0c0bd999c5dbd1616a53bb8a9fdbbab0e873c8ea817be04fce8bd531d883d3c2bcc3d59e56cbdd04c45bdc2f8303e900990bcde2c48bea9f8b0bd5a4bbe3d31ce833ec7ad063e2cb7ab3dc1a0813c44b032be4c30b93d85ed18bed24ae73c806b193e6ec89e3db4a2a43de62a243db1f3bcbde038943d9c212b3e899461bef3ca40bc4872963d40f4aebd02e5b0bdc848cabd2fb8a03e11ab183e06fc13beda8324beaa71253e580b00beabfcd6bd9a95413d95e513be996a7bbe05369fbe20a1bc3af0f4b93ee188be3d508524be426e463d145630bda835d0bc4818ee3d74403d3e9fe969bca5165d3d891281bd4df934bd850b483e18679fbd644658bcd5df333ebe689cbca44a9e3d0b91db3ce775293c9cbe83bd09a1053dd54b31be202d51bd9bad8c3d2c9b8cbc79a6aa3bc446c13d066404bec07ead3d1db6333df134d5bb3f05483c3f3acfbd81888dbd1310a8bdb8c9063e56c062be9849643e3991fe3d6189633db4832e3ee6d5c53d257ece3d4261c9bcd42052bdd38b68beaa4d993c98b3be3d306146bba551983d0196f9bccc7dc7bdcc46bb3d3975ebbbbc7585bdc6f150bd0669c5bc70abc3bd191e33be4bf4353e876e97bd0de1883e4ff068bdc3b777be89c8e2bd4cc797bd2c06473e7bdb48beb7c706be00ef4e3c346aa63bd3bc143d9ec5083d952c103db271b83cf0bfc1bdff14b1be454a04be001cc7bd7010bc3a4f390b3ce0db303a9e00133d100116bee7e751bdfd91873db02ead3daebbc7bccbad75bde6c9463e6159263c8a3c64be476fecbcb4088a3d4296753e61f8013e117c533db80c76bcf43554bea0f3c93d10932ebe5104983dd7ba0c3ee5962f3dbc09413d7f1a833b2c52e4bd3617d33cc59e3d3ee07e65be72461b3c53d4a03dad2580bdf10e3bbd8ba7d6bdce3d8b3e413b1e3ea99414beb1ea24bed252253e21e6dbbd5bacd3bda1d49d3dbafdf0bdaa1d5bbeabcc9ebe2937283c5759cf3ef841cf3d64dd28be56e4253d2fe5c2bc1f693ebd96c80b3e705a293efc6c59bcbc787abb189e89bd662a51bdd244593ef44c3cbddf7f1fbd08ff763efcba4fbdba81053e1a1cda3c954f833d1ea896bd0c4ceb3c6a6523be9542a8bd6c896f3d3fa5de3c5751fa3c49aaec3de7cbcdbd4067f03d8055603d4c5da73c3348a73d5e740bbd1a4c85bc1836b3bdb5ea273ebc5663be9a366e3e54a5a93db39bcc3def29153eb5b8ff3d02b8b53d126913bdf65cb0bd6a6c77becebb5a3cab49c53db9582cbd5e01e53dc4d5b53b, 'gs://linya-api-bucket/db_pictures/aeryhiel_john_mojica/20260312_111927/encoding.npy', '2026-03-12 19:19:29');

-- --------------------------------------------------------

--
-- Table structure for table `finished_queues`
--

CREATE TABLE `finished_queues` (
  `finished_id` int(11) NOT NULL,
  `original_queue_id` int(11) DEFAULT NULL,
  `office_id` varchar(255) DEFAULT NULL,
  `window_id` int(11) DEFAULT NULL,
  `processed_by` varchar(100) DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `services` text DEFAULT NULL,
  `customer_name` varchar(200) DEFAULT NULL,
  `customer_phone` varchar(50) DEFAULT NULL,
  `customer_email` varchar(100) DEFAULT NULL,
  `student_number` varchar(20) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL,
  `process_start` datetime DEFAULT NULL,
  `process_finished` datetime DEFAULT NULL,
  `processing_duration` time DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `finished_queues`
--

INSERT INTO `finished_queues` (`finished_id`, `original_queue_id`, `office_id`, `window_id`, `processed_by`, `finished_at`, `created_at`, `status`, `services`, `customer_name`, `customer_phone`, `customer_email`, `student_number`, `course`, `process_start`, `process_finished`, `processing_duration`, `remarks`) VALUES
(1, 94, NULL, 1, 'lexty', '2025-11-16 20:35:04', '2025-11-16 20:34:59', 'finished', 'Legal Documents', 'John Lexter B. Armenta', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(2, 92, 'Registrar', 3, 'lexty', '2025-11-16 21:21:12', '2025-11-16 19:48:12', 'finished', 'Tor (PDF), form137 (PDF)', 'John Lexter B. Armenta', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(3, 95, NULL, 2, 'lexter', '2025-11-16 21:50:43', '2025-11-16 21:50:15', 'finished', 'Borrow Laptop (PDF), Thesis Defense', 'John Lexter B. Armenta', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(4, 96, NULL, 2, 'lexter', '2025-11-16 21:50:46', '2025-11-16 21:50:25', 'finished', 'Thesis Defense (PDF)', 'Camille T. Soliman', '09050795313', 'soliman.camille.bscs2022@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(5, 97, NULL, 1, 'eryel', '2025-11-16 21:51:05', '2025-11-16 21:50:35', 'finished', 'Borrow Laptop', 'Christian Espinoza', '09223394958', 'espinoza.christian.bscs2022@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(6, 98, 'CSD Office', 2, 'eryel', '2025-11-16 21:56:56', '2025-11-16 21:56:20', 'finished', 'Borrow Remote', 'Camille T. Soliman', '09050795313', 'soliman.camille.bscs2022@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(7, 99, 'CSD Office', 2, 'eryel', '2025-11-16 21:57:31', '2025-11-16 21:56:35', 'finished', 'Borrow Remote', 'John Lexter B. Armenta', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(8, 100, 'CSD Office', 1, 'christian', '2025-11-16 21:57:33', '2025-11-16 21:56:47', 'finished', 'Borrow Remote (PDF)', 'Christian Espinoza', '09223394958', 'espinoza.christian.bscs2022@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(9, 90, 'Management Informati', 1, 'Astaff1', '2025-11-18 08:16:28', '2025-11-16 06:05:00', 'finished', 'Add software', 'John', '09456654322', '', NULL, NULL, NULL, NULL, NULL, NULL),
(10, 101, 'Management Informati', 1, 'Astaff1', '2025-11-18 08:16:30', '2025-11-18 04:58:39', 'finished', 'Assist in AIms', 'Aeryhiel John', '09301939121', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(11, 102, 'Management Informati', 1, 'Astaff1', '2025-11-19 18:15:39', '2025-11-18 07:12:14', 'finished', 'Assist in AIms (PDF), Add software (PDF), Fix printer (PDF)', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(12, 103, 'Management Informati', 1, 'Astaff1', '2025-11-19 18:15:42', '2025-11-18 07:56:45', 'finished', 'Assist in AIms', 'Aeryhiel John', '09301939121', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(13, 104, 'Management Informati', 1, 'Astaff1', '2025-11-19 18:15:46', '2025-11-18 08:11:06', 'finished', 'Assist in AIms (PDF), Add software (PDF), Fix printer (PDF)', 'Ricardo Salvador', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(14, 114, 'Management Informati', 1, 'Astaff1', '2025-11-22 12:30:28', '2025-11-18 09:45:16', 'finished', 'Add software', 'Aeryhiel John', '09301939121', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(15, 115, 'Management Informati', 1, 'Astaff1', '2025-11-22 12:30:32', '2025-11-18 09:47:51', 'finished', 'Assist in AIms', 'Abc', 'abc', 'abc', NULL, NULL, NULL, NULL, NULL, NULL),
(16, 116, 'Management Informati', 1, 'Astaff1', '2025-11-22 12:30:35', '2025-11-18 09:51:46', 'finished', 'Add software (PDF)', 'Christian Espinoza', '09926870934', 'espinoza.christian.bscs2022@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(17, 117, 'Management Informati', 1, 'Astaff1', '2025-11-22 12:30:52', '2025-11-18 09:55:46', 'finished', 'Fix printer', 'Abc', 'abc', 'abc', NULL, NULL, NULL, NULL, NULL, NULL),
(18, 119, 'Management Informati', 1, 'Astaff1', '2025-11-22 12:30:55', '2025-11-18 10:08:18', 'finished', 'Assist in AIms', 'Abc', 'abc', 'abc', NULL, NULL, NULL, NULL, NULL, NULL),
(19, 120, 'Management Informati', 1, 'Astaff1', '2025-11-22 12:31:03', '2025-11-18 10:17:53', 'finished', 'Assist in AIms', 'Abc', 'abc', 'abc', NULL, NULL, NULL, NULL, NULL, NULL),
(20, 122, 'Management Informati', 1, 'Astaff1', '2025-11-22 12:31:06', '2025-11-18 10:30:52', 'finished', 'Assist in AIms', 'Abc', 'abc', 'abc', NULL, NULL, NULL, NULL, NULL, NULL),
(21, 167, 'Registrar', 1, 'robert', '2025-11-22 12:33:24', '2025-11-22 12:32:06', 'finished', 'Tor', 'Aeryhiel John Mojica', '09931131311', 'aeryhielmojica05@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(22, 171, 'MIS', 1, 'Astaff1', '2025-11-27 10:35:57', '2025-11-22 13:17:11', 'finished', 'Assist in A.I.M.S, Fix Printer, Install Software, Item Borrow (PDF), PC Reformat (PDF)', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(23, 172, 'MIS', 1, 'Astaff1', '2025-11-27 11:46:33', '2025-11-22 13:18:28', 'finished', 'Item Borrow', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(24, 173, 'MIS', 1, 'Astaff1', '2025-11-27 12:02:48', '2025-11-22 13:20:37', 'finished', 'Install Software', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(25, 174, 'MIS', 1, 'Astaff1', '2025-11-27 13:10:14', '2025-11-22 13:21:22', 'finished', 'Install Software', 'Aeryhiel John', '09555111111', 'ag@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(26, 177, 'MIS', 2, 'Astaff2', '2025-11-27 13:24:17', '2025-11-22 13:32:00', 'finished', 'Fix Printer', 'Aeryhiel John', '09555111111', 'ag@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(27, 178, 'MIS', 2, 'Astaff2', '2025-11-27 13:31:31', '2025-11-22 13:41:13', 'finished', 'Install Software', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(28, 176, 'MIS', 1, 'Astaff1', '2025-11-27 19:55:52', '2025-11-22 13:31:04', 'finished', 'Fix Printer', 'Aeryhiel John', '09555111111', 'ag@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(29, 168, 'Registrar', 1, 'robert', '2025-11-27 21:52:30', '2025-11-22 12:35:10', 'finished', 'ID Request', 'Aeryhiel John', '09301939121', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(30, 169, 'Registrar', 1, 'robert', '2025-11-27 21:52:36', '2025-11-22 12:36:13', 'finished', 'ID Request', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(31, 170, 'Registrar', 1, 'robert', '2025-11-27 21:52:37', '2025-11-22 12:41:59', 'finished', 'ID Request', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(32, 175, 'Registrar', 1, 'robert', '2025-11-27 21:52:40', '2025-11-22 13:22:41', 'finished', 'Adding and Changing', 'Aeryhiel John', '09555111111', 'ag@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(33, 179, 'Registrar', 1, 'robert', '2025-11-27 21:52:42', '2025-11-22 13:42:40', 'finished', 'Adding and Changing', 'Aeryhiel John', '09555111111', 'ag@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(34, 182, 'Registrar', 1, 'robert', '2025-11-27 21:52:47', '2025-11-22 13:48:54', 'finished', 'ID Request', 'Aeryhiel John', '09555111111', 'ag@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(35, 208, 'Registrar', 1, 'robert', '2025-11-27 21:52:50', '2025-11-27 12:09:32', 'finished', 'ID Request (PDF), Adding and Changing (PDF)', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(36, 181, 'MIS', 1, 'Astaff1', '2025-11-27 22:41:12', '2025-11-22 13:45:23', 'finished', 'Item Borrow', 'Aeryhiel John', '09555111111', 'ag@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(37, 183, 'MIS', 5, 'Astaff1', '2026-02-05 07:09:12', '2025-11-22 13:51:35', 'finished', 'Fix Printer', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(38, 184, 'MIS', 5, 'Astaff1', '2026-02-05 07:09:15', '2025-11-22 13:53:49', 'finished', 'Item Borrow', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(39, 185, 'MIS', 5, 'Astaff1', '2026-02-05 07:09:18', '2025-11-22 13:56:18', 'finished', 'Item Borrow', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(40, 186, 'MIS', 5, 'Astaff1', '2026-02-05 07:09:27', '2025-11-22 14:00:34', 'finished', 'Item Borrow', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(41, 187, 'MIS', 5, 'Astaff1', '2026-02-05 07:09:30', '2025-11-22 14:04:06', 'finished', 'Item Borrow', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(42, 188, 'MIS', 5, 'Astaff1', '2026-02-05 07:09:34', '2025-11-22 14:08:53', 'finished', 'Item Borrow', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(43, 189, 'MIS', 5, 'Astaff1', '2026-02-05 07:09:40', '2025-11-22 14:09:37', 'finished', 'Item Borrow', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(44, 190, 'MIS', 5, 'Astaff1', '2026-02-05 07:09:43', '2025-11-22 14:16:51', 'finished', 'Item Borrow', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(45, 191, 'MIS', 5, 'Astaff1', '2026-02-05 07:09:54', '2025-11-22 14:18:46', 'finished', 'Item Borrow', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(46, 192, 'MIS', 5, 'Astaff1', '2026-02-05 07:09:58', '2025-11-22 14:19:49', 'finished', 'Item Borrow', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(47, 193, 'MIS', 5, 'Astaff1', '2026-02-05 07:10:02', '2025-11-22 14:21:41', 'finished', 'Item Borrow', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(48, 194, 'MIS', 5, 'Astaff1', '2026-02-05 07:10:06', '2025-11-22 14:23:20', 'finished', 'Item Borrow', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(49, 195, 'MIS', 5, 'Astaff1', '2026-02-05 07:10:10', '2025-11-22 14:27:33', 'finished', 'Item Borrow', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(50, 196, 'MIS', 5, 'Astaff1', '2026-02-05 07:10:13', '2025-11-22 14:28:45', 'finished', 'Item Borrow', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(51, 197, 'MIS', 5, 'Astaff1', '2026-02-05 07:10:18', '2025-11-22 14:29:43', 'finished', 'Fix Printer', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(52, 198, 'MIS', 5, 'Astaff1', '2026-02-05 07:10:20', '2025-11-22 14:31:55', 'finished', 'Item Borrow', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(53, 199, 'MIS', 5, 'Astaff1', '2026-02-05 07:10:26', '2025-11-22 14:33:47', 'finished', 'Item Borrow (PDF), Install Software (PDF), Fix Printer (PDF), PC Reformat (PDF)', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(54, 200, 'MIS', 5, 'Astaff1', '2026-02-05 07:10:29', '2025-11-22 14:34:59', 'finished', 'Item Borrow', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(55, 201, 'MIS', 5, 'Astaff1', '2026-02-05 07:10:39', '2025-11-22 14:37:30', 'finished', 'Item Borrow', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(56, 202, 'MIS', 5, 'Astaff1', '2026-02-05 07:10:42', '2025-11-22 14:37:42', 'finished', 'Item Borrow', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(57, 203, 'MIS', 5, 'Astaff1', '2026-02-05 07:10:47', '2025-11-22 14:38:10', 'finished', 'Install Software, Fix Printer (PDF), Item Borrow (PDF), PC Reformat (PDF)', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(58, 204, 'MIS', 5, 'Astaff1', '2026-02-05 07:10:49', '2025-11-23 04:58:50', 'finished', 'Item Borrow', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(59, 205, 'MIS', 5, 'Astaff1', '2026-02-05 07:10:51', '2025-11-23 06:17:16', 'finished', 'Install Software (PDF)', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(60, 206, 'MIS', 5, 'Astaff1', '2026-02-05 07:10:54', '2025-11-23 06:17:48', 'finished', 'Install Software (PDF)', 'Aeryhiel John', '09301939121', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(61, 207, 'MIS', 5, 'Astaff1', '2026-02-05 07:10:56', '2025-11-26 17:23:37', 'finished', 'Item Borrow', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(62, 209, 'MIS', 5, 'Astaff1', '2026-02-05 07:11:19', '2025-12-03 09:33:01', 'finished', 'Item Borrow', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(63, 210, 'MIS', 5, 'Astaff1', '2026-02-05 07:11:22', '2025-12-03 10:09:12', 'finished', 'Fix Printer', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(64, 211, 'MIS', 5, 'Astaff1', '2026-02-05 07:11:24', '2025-12-29 12:47:27', 'finished', 'Fix Printer', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(65, 212, 'MIS', 5, 'Astaff1', '2026-02-05 07:11:27', '2026-01-19 06:02:07', 'finished', 'Assist in A.I.M.S (PDF)', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(66, 213, 'MIS', 5, 'Astaff1', '2026-02-05 07:11:29', '2026-01-19 06:04:18', 'finished', 'Fix Printer', 'Aeryhiel John', '09301939121', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(67, 214, 'MIS', 5, 'Astaff1', '2026-02-05 07:11:32', '2026-01-23 08:31:35', 'finished', 'Assist in A.I.M.S', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(68, 215, 'MIS', 5, 'Astaff1', '2026-02-05 09:07:29', '2026-02-05 06:09:15', 'finished', 'Fix Printer (PDF), Assist in A.I.M.S, Install Software', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(69, 216, 'MIS', 5, 'Astaff1', '2026-02-05 09:07:31', '2026-02-05 07:48:02', 'finished', 'Assist in A.I.M.S, Item Borrow, Fix Printer (PDF), PC Reformat (PDF)', 'alwyn', '09763683414', 'adrianoalwyn@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(70, 223, 'MIS', 5, 'Astaff1', '2026-02-10 12:48:56', '2026-02-09 05:28:59', 'finished', 'Fix Printer', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(71, 224, 'MIS', 5, 'Astaff1', '2026-02-10 12:49:00', '2026-02-09 06:36:57', 'finished', 'Fix Printer', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(72, 225, 'MIS', 5, 'Astaff1', '2026-02-10 12:49:04', '2026-02-09 12:07:08', 'finished', 'Assist in A.I.M.S', 'RICARDO SALVADOR IV', '09568718445', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(73, 226, 'MIS', 5, 'Astaff1', '2026-02-10 12:49:06', '2026-02-10 11:43:43', 'finished', 'PC Reformat', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(74, 227, 'MIS', 5, 'Astaff1', '2026-02-12 09:22:19', '2026-02-10 12:48:12', 'finished', 'Item Borrow', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(75, 228, 'MIS', 5, 'Astaff1', '2026-02-12 09:22:43', '2026-02-12 08:48:31', 'finished', 'Assist in A.I.M.S', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(76, 229, 'MIS', 5, 'Astaff1', '2026-02-12 09:25:02', '2026-02-12 09:24:08', 'finished', 'Assist in A.I.M.S', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(77, 230, 'MIS', 5, 'Astaff1', '2026-02-12 18:26:37', '2026-02-12 10:26:28', 'finished', 'PC Reformat', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(78, 231, 'MIS', 5, 'Astaff1', '2026-02-24 18:25:33', '2026-02-12 18:29:16', 'finished', 'Item Borrow', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(79, 233, 'MIS', 5, 'Astaff1', '2026-02-24 18:25:36', '2026-02-24 13:25:47', 'finished', 'Fix Printer (PDF), Install Software (PDF)', 'johnlex', '09276164084', 'johnarmenta.8v@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(80, 234, 'MIS', 5, 'Astaff1', '2026-02-24 18:25:38', '2026-02-24 13:27:37', 'finished', 'Assist in A.I.M.S', 'Aeryhiel John Mojica', '09931131311', 'aeryhielmojica05@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(81, 235, 'MIS', 5, 'Astaff1', '2026-02-24 18:25:40', '2026-02-24 13:35:34', 'finished', 'Assist in A.I.M.S (PDF)', 'John Lexter B. Armenta', '09276164084', 'johnarmenta.8v@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(82, 236, 'MIS', 5, 'Astaff1', '2026-02-24 18:25:42', '2026-02-24 13:46:55', 'finished', 'Assist in A.I.M.S', 'Earyhiel Mojica', '09123456789', 'hwdiwhdqdj@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(83, 237, 'MIS', 5, 'Astaff1', '2026-02-24 18:25:44', '2026-02-24 15:09:57', 'finished', 'Assist in A.I.M.S', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(84, 238, 'MIS', 5, 'Astaff1', '2026-02-24 18:26:41', '2026-02-24 15:12:11', 'finished', 'Assist in A.I.M.S', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(85, 239, 'MIS', 5, 'Astaff1', '2026-02-24 18:26:43', '2026-02-24 16:57:52', 'finished', 'Item Borrow', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(86, 240, 'MIS', 5, 'Astaff1', '2026-02-24 18:29:24', '2026-02-24 16:58:31', 'finished', 'Install Software (PDF), Fix Printer, Assist in A.I.M.S (PDF)', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(87, 241, 'MIS', 2, 'Astaff2', '2026-02-24 18:41:47', '2026-02-24 18:15:10', 'finished', 'PC Reformat, Assist in A.I.M.S, Item Borrow, Fix Printer, Install Software', 'Erick', '09123455555', 'ddd@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(88, 243, 'MIS', 2, 'Astaff2', '2026-02-24 19:03:57', '2026-02-24 18:42:34', 'finished', 'Item Borrow', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(89, 244, 'MIS', 5, 'Astaff1', '2026-02-27 11:34:24', '2026-02-24 19:18:53', 'finished', 'Install Software', 'Aeryhiel John Mojica', '09931131311', 'aeryhielmojica05@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(90, 246, 'MIS', 5, 'Astaff1', '2026-02-27 11:48:14', '2026-02-27 11:36:06', 'finished', 'Assist in A.I.M.S', 'John Lexter B. Armenta', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(91, 247, 'MIS', 5, 'Astaff1', '2026-03-10 22:42:52', '2026-02-27 11:48:09', 'finished', 'Assist in A.I.M.S', 'John Lexter B. Armenta', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(92, 248, 'MIS', 5, 'Astaff1', '2026-03-10 22:47:02', '2026-02-27 12:05:52', 'finished', 'Item Borrow', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(93, 249, 'MIS', 5, 'Astaff1', '2026-03-11 16:02:16', '2026-03-10 22:46:48', 'finished', 'Assist in A.I.M.S', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL),
(94, 250, 'MIS', 5, 'Astaff1', '2026-03-11 16:11:56', '2026-03-11 16:11:40', 'finished', 'Assist in A.I.M.S', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', '20220629-S', 'BSCS', NULL, NULL, NULL, NULL),
(95, 252, 'MIS', 5, 'Astaff1', '2026-03-11 10:18:25', '2026-03-11 18:18:02', 'finished', 'Assist in A.I.M.S', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', '20220629-S', 'BSCS', '2026-03-11 10:18:24', '2026-03-11 10:18:25', '00:00:01', 'gago amp'),
(96, 254, 'MIS', 5, 'Astaff1', '2026-03-11 16:22:02', '2026-03-12 00:06:39', 'finished', 'Item Borrow', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', '20220629-S', 'BSCS', '0000-00-00 00:00:00', '2026-03-11 16:22:02', '00:00:00', 'ok ka ha'),
(97, 255, 'MIS', 5, 'Astaff1', '2026-03-11 16:24:56', '2026-03-12 00:24:28', 'finished', 'Assist in A.I.M.S', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', '20220629-S', 'BSCS', '2026-03-11 16:24:54', '2026-03-11 16:24:56', '00:00:02', 'ok den'),
(98, 256, 'MIS', 5, 'Astaff1', '2026-03-12 00:51:18', '2026-03-12 00:25:47', 'finished', 'Assist in A.I.M.S', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', '20220629-S', 'BSCS', '2026-03-12 00:51:15', '2026-03-12 00:51:18', '00:00:03', 'gg'),
(99, 232, 'Registrar', 1, 'robert', '2026-03-12 01:29:26', '2026-02-12 18:56:55', 'finished', 'Adding and Changing', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com', NULL, NULL, '2026-03-12 01:29:24', '2026-03-12 01:29:26', '00:00:02', 'ok na'),
(100, 242, 'Registrar', 1, 'robert', '2026-03-12 01:29:40', '2026-02-24 18:29:59', 'finished', 'Adding and Changing (PDF), ID Request (PDF)', 'Earyhiel Mojica', '09123456789', 'mhwdiwhdqdj@gmail.com', NULL, NULL, '2026-03-12 01:29:33', '2026-03-12 01:29:40', '00:00:07', 'ok den'),
(101, 245, 'Registrar', 1, 'robert', '2026-03-12 01:29:51', '2026-02-27 11:11:51', 'finished', 'Adding and Changing', 'John Lexter B. Armenta', '09915261467', 'ricksiv0107@gmail.com', '20220629-S', 'BSCS', '2026-03-12 01:29:50', '2026-03-12 01:29:51', '00:00:01', 'ok na ok'),
(102, 258, 'MIS', 5, 'Astaff1', '2026-03-12 08:01:11', '2026-03-12 09:37:53', 'finished', 'Item Borrow', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', '20220629-S', 'BSCS', '2026-03-12 08:00:56', '2026-03-12 08:01:11', '00:00:15', 'Pwede ka'),
(103, 260, 'MIS', 5, 'Astaff1', '2026-03-12 11:01:17', '2026-03-12 19:00:57', 'finished', 'Item Borrow', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', '20220629-S', 'BSCS', '2026-03-12 11:01:16', '2026-03-12 11:01:17', '00:00:01', ''),
(104, 261, 'MIS', 5, 'Astaff1', '2026-03-12 11:02:10', '2026-03-12 19:01:44', 'finished', 'Item Borrow', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', '20220629-S', 'BSCS', '2026-03-12 11:02:08', '2026-03-12 11:02:10', '00:00:02', 'sadasd'),
(105, 266, 'MIS', 5, 'Astaff1', '2026-03-12 11:05:48', '2026-03-12 19:05:14', 'finished', 'Install Software', 'John Lexter B. Armenta', '09276164084', 'johnarmenta.8v@gmail.com', NULL, NULL, '2026-03-12 11:05:47', '2026-03-12 11:05:48', '00:00:01', 'asdasdad'),
(106, 267, 'MIS', 5, 'Astaff1', '2026-03-12 11:08:14', '2026-03-12 19:08:08', 'finished', 'ID Printing', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', '20220629-S', 'BSCS', '2026-03-12 11:08:13', '2026-03-12 11:08:14', '00:00:01', 'sadasda'),
(107, 269, 'MIS', 5, 'Astaff1', '2026-03-12 11:11:14', '2026-03-12 19:10:09', 'finished', 'Item Borrow', 'Aeryhiel John Mojica', '09931131311', 'aeryhielmojica05@gmail.com', NULL, NULL, '2026-03-12 11:11:13', '2026-03-12 11:11:14', '00:00:01', 'asdadsda'),
(108, 270, 'MIS', 5, 'Astaff1', '2026-03-12 11:11:23', '2026-03-12 19:10:49', 'finished', 'Item Borrow', 'John Lexter B. Armenta', '09276164084', 'johnarmenta.8v@gmail.com', NULL, NULL, '2026-03-12 11:11:21', '2026-03-12 11:11:23', '00:00:02', 'sasdasdads'),
(109, 272, 'MIS', 5, 'Astaff1', '2026-03-12 11:12:39', '2026-03-12 19:12:29', 'finished', 'ID Printing', 'Ricardo Salvador IV', '09301939121', '', '20220629-S', 'BSCS', '2026-03-12 11:12:38', '2026-03-12 11:12:39', '00:00:01', 'asdasd'),
(110, 273, 'MIS', 5, 'Astaff1', '2026-03-12 11:13:14', '2026-03-12 19:12:58', 'finished', 'Fix Printer', 'Ricardo Salvador IV', '09301939121', 'ricksiv0107@gmail.com', '20220629-S', 'BSCS', '2026-03-12 11:13:13', '2026-03-12 11:13:14', '00:00:01', 'asdads'),
(111, 274, 'MIS', 5, 'Astaff1', '2026-03-12 11:13:59', '2026-03-12 19:13:46', 'finished', 'Fix Printer', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', '20220629-S', 'BSCS', '2026-03-12 11:13:58', '2026-03-12 11:13:59', '00:00:01', 'asdasd'),
(112, 275, 'MIS', 5, 'Astaff1', '2026-03-12 11:14:50', '2026-03-12 19:14:21', 'finished', 'ID Printing', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', '20220629-S', 'BSCS', '2026-03-12 11:14:49', '2026-03-12 11:14:50', '00:00:01', 'asdasda'),
(113, 276, 'MIS', 5, 'Astaff1', '2026-03-12 11:15:28', '2026-03-12 19:15:14', 'finished', 'Install Software', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', '20220629-S', 'BSCS', '2026-03-12 11:15:27', '2026-03-12 11:15:28', '00:00:01', 'asdasd'),
(114, 277, 'MIS', 5, 'Astaff1', '2026-03-12 11:16:06', '2026-03-12 19:15:51', 'finished', 'Install Software', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', '20220629-S', 'BSCS', '2026-03-12 11:16:05', '2026-03-12 11:16:06', '00:00:01', 'asdad'),
(115, 280, 'MIS', 5, 'Astaff1', '2026-03-12 11:17:43', '2026-03-12 19:17:35', 'finished', 'Item Borrow', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', '20220629-S', 'BSCS', '2026-03-12 11:17:42', '2026-03-12 11:17:43', '00:00:01', 'asdasd'),
(116, 281, 'MIS', 5, 'Astaff1', '2026-03-12 11:22:40', '2026-03-12 19:18:05', 'finished', 'ID Printing', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', '20220629-S', 'BSCS', '2026-03-12 11:22:40', '2026-03-12 11:22:40', '00:00:00', 'jhbjhh');

-- --------------------------------------------------------

--
-- Table structure for table `offices`
--

CREATE TABLE `offices` (
  `office_id` int(10) NOT NULL,
  `office_description` varchar(20) NOT NULL,
  `office_code` varchar(3) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `offices`
--

INSERT INTO `offices` (`office_id`, `office_description`, `office_code`, `enabled`) VALUES
(1, 'Registrar', 'REG', 1),
(10, 'MIS', 'MIS', 1),
(24, 'Accounting', 'ACC', 1),
(25, 'CSD Office', 'CSD', 1),
(26, 'Dean\'s Office', 'DO', 1),
(27, 'VP Admin', 'VPA', 1);

-- --------------------------------------------------------

--
-- Table structure for table `office_windows`
--

CREATE TABLE `office_windows` (
  `window_id` int(11) NOT NULL,
  `office_id` int(11) DEFAULT NULL,
  `window_no` int(11) NOT NULL,
  `assigned_staff_id` int(11) DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `is_on_duty` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `office_windows`
--

INSERT INTO `office_windows` (`window_id`, `office_id`, `window_no`, `assigned_staff_id`, `enabled`, `created_at`, `is_on_duty`) VALUES
(11, 10, 2, 3, 1, '2025-11-18 07:13:56', 0),
(12, 10, 3, 8, 1, '2025-11-19 19:26:21', 0),
(13, 1, 1, 4, 1, '2025-11-22 12:33:08', 0),
(14, 10, 4, 19, 1, '2025-11-27 22:44:15', 0),
(15, 10, 5, 2, 1, '2026-01-28 10:14:00', 0);

-- --------------------------------------------------------

--
-- Table structure for table `queues`
--

CREATE TABLE `queues` (
  `queue_id` int(11) NOT NULL,
  `queue_code` varchar(32) NOT NULL,
  `office_id` int(11) NOT NULL,
  `window_id` int(10) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_phone` varchar(30) DEFAULT NULL,
  `customer_email` varchar(100) DEFAULT NULL,
  `student_number` varchar(20) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `queues`
--

INSERT INTO `queues` (`queue_id`, `queue_code`, `office_id`, `window_id`, `created_at`, `status`, `customer_name`, `customer_phone`, `customer_email`, `student_number`, `course`) VALUES
(283, 'MIS-024', 10, 15, '2026-03-13 00:14:15', 'ongoing', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `queue_counters`
--

CREATE TABLE `queue_counters` (
  `office_id` int(11) NOT NULL,
  `last_number` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `queue_counters`
--

INSERT INTO `queue_counters` (`office_id`, `last_number`) VALUES
(1, 0),
(10, 24);

-- --------------------------------------------------------

--
-- Table structure for table `queue_services`
--

CREATE TABLE `queue_services` (
  `id` int(11) NOT NULL,
  `queue_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `soft_copy` tinyint(1) NOT NULL DEFAULT 0,
  `hard_copy` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `queue_services`
--

INSERT INTO `queue_services` (`id`, `queue_id`, `service_id`, `soft_copy`, `hard_copy`) VALUES
(300, 283, 30, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(10) NOT NULL,
  `role_name` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`) VALUES
(1, 'admin'),
(2, 'staff');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `service_id` int(10) NOT NULL,
  `service_description` varchar(20) NOT NULL,
  `office` int(10) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `service_description`, `office`, `enabled`) VALUES
(16, 'Fix Printer', 10, 1),
(17, 'Install Software', 10, 1),
(18, 'Assist in A.I.M.S', 10, 1),
(19, 'ID Request', 1, 1),
(20, 'Adding and Changing', 1, 1),
(28, 'Item Borrow', 10, 1),
(29, 'PC Reformat', 10, 1),
(30, 'ID Printing', 10, 1);

-- --------------------------------------------------------

--
-- Table structure for table `voided_queues`
--

CREATE TABLE `voided_queues` (
  `finished_id` int(11) NOT NULL,
  `original_queue_id` int(11) DEFAULT NULL,
  `office_id` varchar(255) DEFAULT NULL,
  `window_id` int(11) DEFAULT NULL,
  `processed_by` varchar(100) DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `services` text DEFAULT NULL,
  `customer_name` varchar(200) DEFAULT NULL,
  `customer_phone` varchar(50) DEFAULT NULL,
  `customer_email` varchar(100) DEFAULT NULL,
  `student_number` varchar(20) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL,
  `process_start` datetime DEFAULT NULL,
  `process_finished` datetime DEFAULT NULL,
  `processing_duration` time DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `voided_queues`
--

INSERT INTO `voided_queues` (`finished_id`, `original_queue_id`, `office_id`, `window_id`, `processed_by`, `finished_at`, `created_at`, `status`, `services`, `customer_name`, `customer_phone`, `customer_email`, `student_number`, `course`, `process_start`, `process_finished`, `processing_duration`, `remarks`) VALUES
(1, 251, 'MIS', 5, 'Astaff1', '2026-03-11 10:16:52', '2026-03-11 18:16:30', 'finished', 'Assist in A.I.M.S', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', '20220629-S', 'BSCS', '2026-03-11 10:16:49', '2026-03-11 10:16:52', '00:00:03', 'tanga e'),
(2, 253, 'MIS', 5, 'Astaff1', '2026-03-11 15:08:18', '2026-03-11 21:39:53', 'finished', 'Assist in A.I.M.S', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', '20220629-S', 'BSCS', '2026-03-11 15:08:17', '2026-03-11 15:08:18', '00:00:01', 'bad'),
(3, 257, 'MIS', 5, 'Astaff1', '2026-03-12 00:52:38', '2026-03-12 08:52:21', 'finished', 'PC Reformat', 'Ricardo Salvador IV', '09301939121', 'ricksiv0107@gmail.com', '20220629-S', 'BSCS', '2026-03-12 00:52:37', '2026-03-12 00:52:38', '00:00:01', 'wla'),
(4, 262, 'MIS', 5, 'Astaff1', '2026-03-12 11:03:53', '2026-03-12 19:02:46', 'finished', 'ID Printing', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', '20220629-S', 'BSCS', '2026-03-12 11:03:52', '2026-03-12 11:03:53', '00:00:01', ''),
(5, 263, 'MIS', 5, 'Astaff1', '2026-03-12 11:04:15', '2026-03-12 19:03:09', 'finished', 'ID Printing', 'Aeryhiel John Mojica', '09931131311', 'aeryhielmojica05@gmail.com', NULL, NULL, '2026-03-12 11:04:14', '2026-03-12 11:04:15', '00:00:01', ''),
(6, 264, 'MIS', 5, 'Astaff1', '2026-03-12 11:04:21', '2026-03-12 19:03:44', 'finished', 'ID Printing', 'John Lexter B. Armenta', '09276164084', 'johnarmenta.8v@gmail.com', NULL, NULL, '2026-03-12 11:04:20', '2026-03-12 11:04:21', '00:00:01', ''),
(7, 265, 'MIS', 5, 'Astaff1', '2026-03-12 11:05:40', '2026-03-12 19:04:53', 'finished', 'ID Printing', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', '20220629-S', 'BSCS', '2026-03-12 11:05:39', '2026-03-12 11:05:40', '00:00:01', 'asdada'),
(8, 268, 'MIS', 5, 'Astaff1', '2026-03-12 11:11:08', '2026-03-12 19:09:30', 'finished', 'ID Printing', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', '20220629-S', 'BSCS', '2026-03-12 11:11:07', '2026-03-12 11:11:08', '00:00:01', 'asdasda'),
(9, 271, 'MIS', 5, 'Astaff1', '2026-03-12 11:12:11', '2026-03-12 19:12:03', 'finished', 'Install Software', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', '20220629-S', 'BSCS', '2026-03-12 11:12:10', '2026-03-12 11:12:11', '00:00:01', 'asdads'),
(10, 278, 'MIS', 5, 'Astaff1', '2026-03-12 11:16:43', '2026-03-12 19:16:29', 'finished', 'Install Software', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', '20220629-S', 'BSCS', '2026-03-12 11:16:42', '2026-03-12 11:16:43', '00:00:01', 'asdasd'),
(11, 279, 'MIS', 5, 'Astaff1', '2026-03-12 11:17:15', '2026-03-12 19:17:05', 'finished', 'Item Borrow', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com', '20220629-S', 'BSCS', '2026-03-12 11:17:14', '2026-03-12 11:17:15', '00:00:01', 'asdad'),
(12, 282, 'MIS', 5, 'Astaff1', '2026-03-12 16:13:56', '2026-03-12 19:19:52', 'finished', 'ID Printing', 'Aeryhiel John Mojica', '09931131311', 'aeryhielmojica05@gmail.com', '20220501-S', 'BSCS', '2026-03-12 16:13:55', '2026-03-12 16:13:56', '00:00:01', 'dsfasda');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `user_type_and_role_id` (`user_type`),
  ADD KEY `office_and_offices_id` (`office`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`client_id`),
  ADD UNIQUE KEY `safe_name` (`safe_name`);

--
-- Indexes for table `finished_queues`
--
ALTER TABLE `finished_queues`
  ADD PRIMARY KEY (`finished_id`);

--
-- Indexes for table `offices`
--
ALTER TABLE `offices`
  ADD PRIMARY KEY (`office_id`);

--
-- Indexes for table `office_windows`
--
ALTER TABLE `office_windows`
  ADD PRIMARY KEY (`window_id`),
  ADD UNIQUE KEY `uk_office_window` (`office_id`,`window_no`),
  ADD KEY `idx_office` (`office_id`),
  ADD KEY `idx_staff` (`assigned_staff_id`);

--
-- Indexes for table `queues`
--
ALTER TABLE `queues`
  ADD PRIMARY KEY (`queue_id`),
  ADD UNIQUE KEY `uq_queue_code` (`queue_code`),
  ADD KEY `office_id` (`office_id`),
  ADD KEY `window_id` (`window_id`);

--
-- Indexes for table `queue_counters`
--
ALTER TABLE `queue_counters`
  ADD PRIMARY KEY (`office_id`);

--
-- Indexes for table `queue_services`
--
ALTER TABLE `queue_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_qs_queue` (`queue_id`),
  ADD KEY `fk_qs_service` (`service_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`service_id`),
  ADD KEY `service_office_id` (`office`);

--
-- Indexes for table `voided_queues`
--
ALTER TABLE `voided_queues`
  ADD PRIMARY KEY (`finished_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `user_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `client_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `finished_queues`
--
ALTER TABLE `finished_queues`
  MODIFY `finished_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=117;

--
-- AUTO_INCREMENT for table `offices`
--
ALTER TABLE `offices`
  MODIFY `office_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `office_windows`
--
ALTER TABLE `office_windows`
  MODIFY `window_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `queues`
--
ALTER TABLE `queues`
  MODIFY `queue_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=284;

--
-- AUTO_INCREMENT for table `queue_services`
--
ALTER TABLE `queue_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=301;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `voided_queues`
--
ALTER TABLE `voided_queues`
  MODIFY `finished_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `office_and_offices_id` FOREIGN KEY (`office`) REFERENCES `offices` (`office_id`),
  ADD CONSTRAINT `user_type_and_role_id` FOREIGN KEY (`user_type`) REFERENCES `roles` (`role_id`);

--
-- Constraints for table `queues`
--
ALTER TABLE `queues`
  ADD CONSTRAINT `fk_queue_office` FOREIGN KEY (`office_id`) REFERENCES `offices` (`office_id`) ON DELETE CASCADE;

--
-- Constraints for table `queue_services`
--
ALTER TABLE `queue_services`
  ADD CONSTRAINT `fk_qs_queue` FOREIGN KEY (`queue_id`) REFERENCES `queues` (`queue_id`) ON DELETE CASCADE;

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `service_office_id` FOREIGN KEY (`office`) REFERENCES `offices` (`office_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
