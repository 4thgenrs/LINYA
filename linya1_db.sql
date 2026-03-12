-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 26, 2026 at 05:09 PM
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
  `face_data` longblob DEFAULT NULL,
  `face_path` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`client_id`, `name`, `safe_name`, `phone`, `email`, `face_data`, `face_path`, `created_at`) VALUES
(19, 'Aeryhiel John Mojica', 'aeryhiel_john_mojica', '09931131311', 'aeryhielmojica05@gmail.com', 0x934e554d5059010076007b276465736372273a20273c6634272c2027666f727472616e5f6f72646572273a2046616c73652c20277368617065273a2028352c20313238292c207d20202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020200a2efa0ebe3c54cd3d06c31d3d4e2a95bd8c7514bd445a04bd31c2cbbd2d2c37be4caa1d3e40f1b1bdaca0893e9b1d54bdc3f94dbe311fe4bd03e773bd154d3f3eb13d62beb34a0fbe48a6d03a03889e3c36f2253df4f95d3b563cbf3c4b419f3c88069cbd3969adbeca7301be2eafebbdd6f146bc0a4704bc58eda1bc24420abdc0e538bed1bf0bbdfa29033d868b7e3d5be025bd2538b6bd2287203e66785abce21859bef5d9e8bbb58d9f3d83e1693e6c0f043ecdd99c3dfbc685bcf2cd27beee0dc53d464d38be4100643df8d4ed3d0a053f3da1362f3d702ef23a545314be2e71a73c60d2133e974248be348b313d746f463d418c38bd945396bd0a2bdebdaf73953e94872c3e2bd420bed8e220bed8aa0f3e99b3d2bdc8d2a3bdef76aa3db6240ebe5c8776bebbb086beb9f3e53c4aecd73ecafeae3dd74131be1f72a53ca6b41ebdbbce03bd6d8ac73d0fd4323ed3ad58bc2ad4013d6d1580bd42756abd8bbd703e118060bd53651cbdc78e743ee63f5abda708953d524a2cbbc44a743c568c08bd86e78c3c33dc1abe483ab7bd605f523dc7ad54bc4892e6bc2134c23d886010be02a2bc3d9d3b873d60a8363aabce793d7b208abcffed2bbd71cbb8bdf2b31c3e77b573be10987c3eb7c2203ec9c00b3e58ab1b3e9900c73db70ba83df64b32bc18a409bdd19e54be836d1ebc8eb1c43d542ce5bc2988c83d1e0818bba713f4bdfaeb543d39553a3d444696bd948bf4bdf573d8bcf60edbbdfa3021be04ea2a3eb000b2bdbbbc973ec12973bc05913dbebe5adbbdb9b46dbd2e15333ee0624abeac0003be000cd938be2d9fbc10cf143d0e39e33b11f7693c0d2a183c9dc4a2bd78bfc9be12e6bcbd4fa005becb0769bc91acad3ade4cd03be477ab3c5d8f32be046b5dbd9388bc3c96c97e3d836091bd0c9589bd11f53d3eb30c2ebcbe375cbef0e223bd1d651b3dd3497b3e68a10d3e38bc1f3d4c96a63cff450bbeb495e03d8d7f17becaac543d7205fe3da791ad3d3e43d03bfee08b3c217bd4bdf8d9453cfcbc193ea96f4abe17dacb3c081a853d343ea3bd2e5596bdd8f706be4a329a3ecabd133edfaef3bd009e25beac74223ede0bc7bd045af1bd0be4053d374e31bef32265be5a60a2bed3ed313c9488c23e534e923de7b950bea015d9ba885eaabc8c83b3bc4928043efb5c213ee0aed83a0639ac3c3df698bd8cd46bbd8012623e59369fbdad9b2ebd28636c3e603a1dbde3fc803d3074923c34f1eaba91f84dbd289b303d5be934be230668bd5d0b403d760c4f3cb5372f3c61a9c43ddecffebd9fb8a13daf7e083d4266ce3c2da04f3dab4152bd8417bebd57b40ebefebe363ef01538be230c603ea6eb133ea7fa123e04a3213ea288f53d4a802a3d07ad13bde50619bd0e2e7abe3c2fe23c3455bc3d912d0bbd097e923da4f46d3cd126d2bdc2b0893db5a5183d405a7dbd1074b4bdd65975bc481bdfbdd09401be7c77123eff7ae7bdd5f68e3e310c7abdb88246bed5c7ccbd31ca30bdc39c223e77e062be4280bfbdac6bd23b839f2b3cb652923dfb8eec3c6000933c3b457e3c5869c7bd5b76b9be35b087bdb7a5d8bd2fc0333d996e06bd524a70bd66f33d3cead460be70d98bbdacd2f63c4826e53db33c41bc62396cbd5e69323e2429b23c024151be768dfebcee0d93bce68b7d3e05840f3e54f1b63de99c14bdea3af4bd48b0bb3d928d58be369d903d68e1d73da0d4503d31f4363c1a00d5bc36db08be5950fb3cc2a2d53d746b33be428b8b3a114e873df080d0bdf62d01bd1c0d9fbd97f89d3e71cedb3d7437e3bda33c02be0285f53d15afc2bd4796c0bd7d334f3d1b6fd7bd83e125bee14aa2be30badabb57abe03e8b39c93de7d17cbec0b325ba5b62a4bd595e92bc0fc54c3e9a22193edea3023d4ae1043d722fc8bdb0d020bd6d7c663e30ac9bbd4abd86bd18b76e3ed958c9bc4e3bd13d0ee61a3d1ab317bc855c39bdd0a69a39a6760bbe9a82b8bd9004be3d8ad9b43c1ac09ebcd75f1e3ec91b34be0a52e33d7bc4233d9e677cbbddeeeb3d0fdc2abd5a6c90bdc95cefbd6d62193e158f78be0c6a4a3e7f3f2a3eca64b93def30313e15badf3dae75613dd439b3bc0a0c02bd222879bebfba1ebd551dbc3db0a78fbab8b6e33df2bec2bb827105be18acba3dde500ebcb40225bdcc1c56bd3184c6bd65de63bd2a9d10be37a32e3ef312b3bdec65753ed6172cbd0f8d1fbee3d70cbe885eacbc54fd073e142752be4df7f6bd72d98cbc174e05bd1151c73d5872a3bb813c2d3df68a6c3c406eb5bdae86c1bec4b2fabd7e9b9fbd8cabf83c40332ebdeed2f73ceacdc2bc74cb68bebc39d4bde5cd233c46a5c73d54ca50bd871a74bdc7c52f3e4e2de0bc4e0841beab0ca0bd45ce833d9258813effa2093e8e035a3d6dae5f3cfc8902be6b5bdc3d5f6602be2c3db03beb8bee3df2c2373d39c0663dd6aa07bc2d4456bd326eaf3ce6bd413e1ace48be4e78a93bf8c48e3d5d85b9bd3bf3b8bd5e58a1bd87c4913ef81aef3df6eddebde1b833be33121a3e8cf221be34aed3bd1132323db862aebdc77456beddf999be5eddef3b7c83b63edd7f623dc4811ebe682ff03ccc2a0ebd6fafa9bc7f6f133e2ab0283eb464c1bb2be5bb3dbc4dd0bd91459bbc6c8d6d3e04c468bd100400bd99123c3e0c2ba6bc24429b3de4dd373dc9557c3cc675a3bd8c4b3f3da6de39beb41032bd908f983d5eb1acbb2a2eb3bcc539ac3d32bab3bd0d67f43d72c3483df4c985bb749b923cadbe8cbd83be80bd0ce105be590dec3d04544bbe1651583ed20c263ed1047f3d8d2f3f3e8463af3d3bbfb73dc46288bc9eadb7bc669159be94ba803be5ce853d90a9c6393890a43d98412fbda86bd1bdf7cccc3d2ec03f3deb2ebabdfa4cb9bdf6cfaabc3d329abd061a20bebc9dfe3d48ced8bd068aa03e40dd81bdc2ec6cbe0c2fdfbd01a323bdd5294b3ed94b7dbec2d029be208cfa3c89ea9c3c3cd3b23d6ca820baef84d03cdbb72a3d7cbbe8bd6f36bebe28e9f6bd90aaa6bdfcb3703bc4e380bc26cf69bc3d4aebb9bb4e51be4467e4bc0486be3c0d69203d7586ecbc3a92a3bd538e323ed4ad7b3c962079be90bc5b3b8691653d88196e3e58caec3deaef733d6ce18cbb4cf218be09e8383d831b2dbe4eab873d5ed80f3e97d98a3d86eb0d3dc95e36bd1d7206be7802cbbbcb300f3e406839be47802a3d6b689b3d8640a8bdb4a7f8bca88a13beb2cf803ee8c3e33de3851ebeaf3e2fbe088dd43decf7a7bdb286f7bdc4a6b03d137b25beb2bb61be957e9dbe8754d03ce4fccd3e4367b53df4cf37becb1f4fbc41aafbbc17669fbc553de93d4695313ed0635c3c4e41663c8a24abbd826ddb3b51ec543e5a9d9dbd08ca89bd5c9e803e227681bd5173003ee54423bcf464613d654815bdeff7793dde2e05bed65f14bd84a7f13ca20a1a3ccad3b1bce9edf03d2ec9e2bd4b02b03d48dd343d8d95133c3bf3133d9a150d3aae23aebde59dd6bd5036003eb89472be0a43723e96434d3e2cb5f83de43afc3d2b36053ec37f413ddce09ebc219996bd027979be07d80e3c235bfb3dcb472dbd6ca9fa3dc0d024bc, 'gs://linya-api-bucket/db_pictures/aeryhiel_john_mojica/20260224_032603/encoding.npy', '2026-02-24 03:26:05'),
(20, 'John Lexter B. Armenta', 'john_lexter_b_armenta', '09276164084', 'johnarmenta.8v@gmail.com', 0x934e554d5059010076007b276465736372273a20273c6634272c2027666f727472616e5f6f72646572273a2046616c73652c20277368617065273a2028352c20313238292c207d20202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020200aa4cfc5bdcd87ad3ddbe4b33d9cdb3bbd5bf58bbcd73aa0bd8a31763c226c76bda135243ede9bd7bd8013873e1acab7bd178653beb51c18bef08bd53ce6ac413e3d1d0abe49e7c9bde1c857bd343d34bd37867f3d4c7a83bd2681dd3a52fd7a3d19c28dbd6631f0be096fb1bd369fe6bd8c1a21bd9ffe1abc3a2626bdf97ac43d45bc15be060545bd2b860fbd7e742a3de73916bd7ccb00bdf1121a3ea6e767bc4eb143bec6c24dbd1ef1c63be24c7e3e06ec243eec1d553d3e4f9e3d04dda7bbfe03673c7e6429be287f7c3c954d073e346aac3d4ba2f53c728ca93dac87b9bde2edae3b7a782a3d141d43be856e97bc891c043d48f678bd7a8e7cbd004776bd5607923ebf60f03dfae708be9c0bb7bdc1cd493eea6304be5ee1cdbcceb6bdbc14ef29be40344fbeff2f92be15769e3dc137db3e32aa2f3e7c9d32beff4fd13cfd7909be2af791bcf0b1fd3ded47c53d8ab33abd22d5fd3c81e3afbd9051b33c7ea2c93d8871a3bced8688bda3ef4e3e27370fbd2b72083d80a3e4bcbd413bbda1f622bd7b4b973df8b9f1bd3e03e2bbad0f873daa6084bd7401e03b1060d33dc42332be7fd1a73db352eb3cee024a3d1294143df1d2053e63351abe09daf2bdec4fa43de1ba69be747df93deed9513edbe7413d5b2a0b3e6733bb3d4b6da23db10a023d89d2a6bd43884fbe828833bc7442b03dc8ce2f3dc012603d5954553d2cc0fcbdb4f74a3d9049583d78c032bdef2b86bbdef924bde6f304bd6ef6d7bd3dd1243ec031c6bdb42a883e650c95bd1dd96bbe429d03be9f0800bd1f044e3e610534be0eb7b8bdbc260ebd0aa4983c7f45873d166ca8bd39dc72bdb2e1a03d50d1d7bddffcc1beb2cedbbd9e6bbcbda580fbbdc920a9bddd9800bebf28c83ca53b1fbe848082bd83ad24bd57bd8e3d979a07bd33ca92bd7500353e30a8d33c8fb33fbe5706923c4ea0993cdf9e853e0fa9443ee283343d446b02bcbd4717bd36600d3e174c40be436d0a3d65d1bf3da5c42e3e7a2a353d0a13eb3cc42ce0bd8acf66bc5006ed3d1fd843bec6af983b3484293d4fea02be7b7de8bc420789bd3677683e7669263ea6a50dbe808be3bd1dc41c3ebdc2e0bdd6cc37bd6113cabce75411bee5c680be0e1bacbe2ae2b63d249baa3e132a2c3e1cfd87beecab77bd923499bdc0420e397408383e0eaad83dff9b953c60e540bb548d04be340c30bd983f383e5f067dbcc99056bd329b593eca109bbc989c183dc8368fbc7c4c44bb65de85bdd3718b3d041010beb582c03cdaf5173df628ddbb801809396cc9f23d46b102bebf9a883d6d59fa3ce791be3ca046663afd43b73ca6942abe70e2cabd15adab3d915479be9f40003e722e293e4ab4493d39f3e83de54b9b3d4e10633d8e410bbcab6f45bdffff6abe238a5bbca8750b3e421b673db649e03d34c97b3c6c5addbd66d0e53c7384993daefd81bc5cfb30bd58282fbdde7444bd4ef5c2bd19befc3d60c5eebde8b3863ec51aa3bddfd959be6b1bf0bd620c13bdde71513e7d650dbe60dad0bd5e4609bdb91b163de562dd3d8e808abde3c413bd6cc9c13c23c8b6bdc6eab0be5c7ddabde2f64dbdfc23b0bd21d55dbd7561e0bd55141ebc08e22dbef4e966bdd893fbbcd2cfa33dcd5109bdedb290bdd1a1123ee4e9603bdd175bbeaee9133c5327093d36ff833ec388183ea2fbc23dd8fba9bb40f67cbd8e39b33dfe723abea0e4983dc3d0b33d3844103e0432863c95f4e83ce9afe6bd794a873c0546d03dc12221be6c1a573b75a49f3d49c58ebddaf0d3bc80c1c0bd9eda893e8b74163e280f06be0605e6bd1896063e0f97c2bd749578bd170df6bc995a28be5c874cbe6a40b4bec57c0b3da063c93eccb3c43da9db68be4b66f3bc64664dbd42c7bc3c422a383e78b6fe3dd0a8373c753ade3c834f08be3864b1bc84803c3e9aa18cbd5b1339bd4547353efd6909bc229fd53bec4a9abcc27c69bc0a911ebde461813df4c0fabd40577d3a84b2623dfc6e9ebc104c1eb96805043eca170fbe9593633d5edc123dfa02283dade4463d6367d33c27212bbe9b2fefbdce98883d191d5ebead04fd3d49ab243e4a9a753d5f27af3d3036ba3d5586ce3d7d2b16bcf093913a0a1577bec11792bb03afbf3da99f133d6b2ae83d801b4c3d4d907bbd14fdb23df75fac3d341022bd2e572a3d9bc3cabd52fe9f3cd91a39bd9f05ff3db00c60bd1c0f8f3e4509febdcce03ebe6e0c0abe4de0783ccb8d2d3e313511be58099cbdf96c82bd818985bb903f4c3d500177bd39ef8abc1381423dc0e095bdc860c6be18b9a5bd23ebbabd204212bda5fdb9bb8d1b01be7a71d73dbe3d3dbecf85abbd2eaf79bbffee973d3e828dbcd8cb9ebccab81c3ef8f4e53c73872dbe95a6233d1a289b3cb1407b3ebe8e233eabcd8e3d98d2c83cfafab3bc52075b3c529e34becc56513dc4cd023ef8711e3e309f9a3d2e1c643cc850fdbdd8ac0d3b1c95c63cc7123ebe2fbf21bded21bb3c90946fbdb85575bdbe7265bdcf27923e7a74153ece040fbed451c9bd7d781c3e6c9aadbd17ae02bde2032d3c2de10dbef7812abeb18e8abec63aef3d567bc23e993b033ec0c271beb0dace3c9c6c34beada5d4bc2014e73d4b0c963d2efce9bc2668623d3c27b0bd61f902bc2263003e08a048bd6532a5bdd21d2c3ee7edbdbc67ce8f3d2ef9c8bc790c53bd985d56bc9be15d3de05f09be4a8bb4bc20a2853d0a2f44bdd6c14ebd11ece23d2bc950be6d92a83da628823d1838783b3e8e543d07acc63dd5fbcdbd6729d1bd1e746b3d494c95be0607273e4e7c3b3eb0a02d3d018af33dd4bbb33d6988823d6731243d79946fbdb90848becaef23bddd81063eee539d3d5717913d80c5423d4a40c9bd1bdbac3d4848953de0d383bd5c12c1bc661872bd388c80bb34e2ddbd0247203e882be3bdb774843ee306d5bd62c72bbe95941bbe07fb80bc9e8c383e05b656be5935fdbd9cef0fbbf8e76bbbf175d63d802797bdc6539ebd11c2a33d47c9f1bd3f07a2befa7e96bdd05f14be3cbe8abd088683bd06e3aebc3c7b263df34049be50f275bdb54f60bdda702bbcc6bcc3bcacc510bdfd481c3e5eebe2bc64182ebe6600233cb695723d6fb56d3e3e9e013e90c45c3dfa8866bc1fe803bd2763c83da86936be3ee9c43c1a368b3d8470043e436f8e3d7f49f73c2d6411be3bc802bc9cfcf83c80f534bee166833d8f55b73cf39ecdbdeab567bddecb75bd8be1763eb495043e2524bfbda45deabdc8de0d3eca13eabde290aebd53e4d63baa38fabdebe332bee1c499be7b01353d77dca53ef82bd93d16a873bea10ab8bc1c6f44bd3837ce3bfc90243e25d9ca3d2f499c3cd288113c4cdb5cbdf09ebdbbcf30383e141f71bd3a3c71bdc571523ed288e3bc06b7a23c42ac1abd5914acbda712243c067a993d6f7505bea2461b3da6a5173d3bcfb43b163214bda6d2a63ddf941bbe7cf7253d58a49b3d4023beb90d54efbc7f269e3d37f541be18e798bdbe83b93d45795cbe03d5683e43892e3e2acd943d83e6cb3d6ed08a3da2ccb93c24e3fd3cb1a643bdc88e2fbe02dca0bc5c5d273e7edf8e3cb813a53de852ee3b, 'gs://linya-api-bucket/db_pictures/john_lexter_b_armenta/20260224_053509/encoding.npy', '2026-02-24 05:35:12'),
(21, 'Ricardo Salvador IV', 'ricardo_salvador_iv', '09915261467', 'ricksiv0107@gmail.com', 0x934e554d5059010076007b276465736372273a20273c6634272c2027666f727472616e5f6f72646572273a2046616c73652c20277368617065273a2028342c20313238292c207d20202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020200abc9aa3bd1447783d7cc6aa3d40ad9b386c176ebdfd5d2cbd537b30bdefff69bdc41adf3df1c294bd51199d3e14fa273bccd916be507030be9c416f3d2db62c3e2525b9bdac4118be6cb4813bd4bcb83c2271d23ddea279bd619afebcc0dabf3d511a3abec4c8c2be52bd03bec683abbdf894d73a6444cebdd435d5bd206a103ed49322be73bf4ebd780fdfba34a7d73d484f083de6b5103d73a3db3d1a27863d8dbe74be948afe3cc6fbda3c41ce843e10314b3ead32713dc182a03dfdd33bbdb4fc803dec9b28be6c3de23d364aa33dde0c603edac5523cae156d3d1a0c11be6c96d9bcd63a4c3d28913cbe63d5383dd309763c5e86abbd94e75cbdc48d92bd9bcc6f3ed9e6de3d15b9d0bdd01edbbd10c1603efb489bbd0eecb2bdc2f1813d4cef0ebeb39630be9019a5bee87e8a3d546ac63e7a9ac43d3d829abe2d25933d0d859dbd7066ccbc54d85d3d1727a13df61519bd26dd3a3d20bf04bea81399bb8b28463eb89487bae8bfc53cd048483e6204cfbb08a1be3d9408923d8de9903cc2b63dbdbbff703c99cc24be0225b5bdfda311bc24978abd9cfe55bddabeb13d750d53be742a273ef315573d7fdb9b3ce9b3e8bbefed8d3d61af0dbee94665bdebc9cc3d8b7284bea16c103e93e6163e2e72843de199d23df7a9f43d028e9f3d81d876bcc54cf0bdf4bc50bea8cf043afa1f173eb454f3bd57ad493da2ee4c3dd90492bd1a24043d00bdfd3da6c6a83a2f8681bd764640bd4e140cbd2907b8bd1cb5ce3d607bfabd063d953e225628bd9d381cbe5f9b32be3cd2a83bf64b163e6e1cd6bde20f1fbe14a9e1bbb63c1b3db68faf3d4be575bd479d273ce9ab193d88fe39be8c34abbe79008dbdd3daedbd5e60d83bc0cb79bc4d8cc3bd87cb923d13c751bec8fc44bdab0c2abd5ae6943db2e6183de053103c92a4193e1c350c3dd1eb90be016f263de14877bdcf81793ec3882b3e45fe3a3d1336683de868cfbd4e71883dec1548bec7d7183ed1a0bd3d832be93d5cfe48bcc4d12fbb31c004bee0d9c4bccf355b3d81a022beaca1aa3cbae9823df2801dbda4721fbdf5f2e3bdfe28693eeec80f3eab02a8bd19d519be7a8e383e2c79e3bdeb31cebd3fbc6e3d6f1f21bed2d0fcbd19f6bebe0889503dcf18c83e5a4ebf3da79176be83f6f13cdef4dcbde0e2213d68cd0a3ef06ce73de2288b3c7e396e3d1c4f86bdd85474bb6f523d3e2bee7abdcb7482bdd647343ed2a514bcf1dd5c3d4da5f83ca0c4ebbc943c853bf9ba7f3d7fd910be10cfddba6461073ddd964a3c8083e4ba6f16f43dd9384dbe0009e63de278be3c7899863cc2a99e3d0b14093d328d01be641aa6bd47f0c23d10646ebec1124b3ec5e61b3e5591e63d9901bc3d18b0103e5002503dc09139bb8bcc0dbd63b877be83e6183ca259123e0eb270bdc97dac3dbe877c3d859c86bd7fd7de3da7d5733d8b445a3c3f4ccbbd7079083d35e993bd504ea7bd80cbf53d1616eabd32c3a63ebbba953cca0718bedff049be764934bcbf682f3e8c1434be685329bedac85f3d1a49913c8c94cb3d3e92243ab79a9b3c096fc13c504b10be74aea8bec830dfbd4c29c3bd9c7e133d7f167fbd0f44f4bd253a8b3d422d25be08b5f6bc18a19dbbf6d2813de225353d40b33a3cf9d7fe3d320e083d7bb256be84d0833df4eb80bbc2af953e4169283e1c901c3db61dca3cf8c69cbd172a233d6291eabd0d37de3d62b2043e7f3c173edaeea3bb261acbbb01e10bbe23f780bd7a2aaf3d236705bea248103c77af683c18b678bc66dc4fbc7c7de9bdec7d533e1689e83d945c82bddce064bef977163e812deabd77a311bef0986d3df06436beb1a31abeffca9abe14e52e3d9c84ab3e3c59b03d41db6abefd98823dda4044bcaaae36bd0829983d9793fa3d14beb8bc7b5aa73d429eeabd64587fbc20874c3eb49d65bd89b4d8bc77e5583ec15b5fbc24da333d3dd2973deff699bc16fca73c44f9a33ddbde2bbe685741bdc453c3bc22d4b8bd888b59bdbda0d83d317f44be401eb13d600e713c21b2c03c44a24fbc61cbc63c187605be247762bdb5f6133e416b73be8de73c3e4ebd363ef7cdff3dfa0df63d79c50c3ed1d3ad3d8a478dbc7cf688bd186755bed0af19bc0423de3d9cc4d9bd3d24793d8d3f643d7a8fd8bd76544a3ddafd0c3d841428bd6195dabdb8f530bd8c90a5bd91dad4bdf463ae3d83baecbde5697a3ef51c0fbdcc0530bec48252be7acfb6bcba7b0d3e04e5f6bd1ff803bee06607bc126946bc3aadf43d40ee24bdf87b023da67f513dc24912be47e2d8bef5519fbde44ef7bdba68d43bd495a2bd65dadbbddd3bd63d65e438be78e092bb26146ebdb9f6e53dc6b5b03c8a690a3d0087ed3df070053d6d646abe546c98bcb049eabb95a16b3e17e0493e425bd33c9743893d2e7124bdf46b8e3dfe6e54be26dd933d6aa8ef3d9d0c0f3eda24123cf3ff013daa68ecbd193e52bd60628c3d47db32be3d6a8a3c1558dcbc029280bd045ababde5ebcebd0ece823e9f37013eaa5d05be2f8995bdf95e333ecdb608beeae456bd2410fb3c6fb500bea2de46bed74d93be18a5673df306c33e454f0a3e2cdf70be5655243dd78e02be348a64bd2251543d0ed6773df4d51ebdda5ede3dedd5bebd47d6213d8ff52d3e1bb1a1bd4bded23c7406723e903b2bbbeefadf3d76fced3c1424783ceede39bdc435a33c799b06be238b1dbdb0cffe3cd06008be5e45debc1ab5183d9e084dbe76c0133e2d5c383dff1cfdbb10aae53b25a0bdbb14e1edbd74bb9bbd8f84013ec81070bea685333ea8b0543e9b1ae53d468b463e059bf13d8e732e3d789442bba734fbbde32320bea9872ebcb7d4053e11ec84bd56b6413c07b81b3d, 'gs://linya-api-bucket/db_pictures/ricardo_salvador_iv/20260224_070927/encoding.npy', '2026-02-24 07:09:30'),
(22, 'Erick', 'erick', '09123455555', 'ddd@gmail.com', 0x934e554d5059010076007b276465736372273a20273c6634272c2027666f727472616e5f6f72646572273a2046616c73652c20277368617065273a2028352c20313238292c207d20202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020200a53769fbd28c4a63db789643d9e948e3d5eb2bd3c4c7acfbd9adc88bb01093fbef51c093ef414c9bd60948c3e8d4f94bd4ddd03be24c416be0eca353d376b1b3e36d424be7b49d6bd4b51fabd8b88bdbd60af0cbd07701fbd4888fdbad715ef3b5615aabcb06fb7be3b1480bd3db319be1df4dc3de57292bc0270c73c0122833cf63542bed22d97bd067d083dc08110bdc33436bd722b213ddcea773e1abed93b696df8bd152f19bd6a5bddbcbf43793e2e93493eb45f123dcbc0953c9a67a5bdeb38a93d265142be0c15a0bd1de6073ea90b133e20c6e53c27e37f3d613d9dbd4c68cabcc4317c3ddf067abe1649ac3d096d473df39c30be4edac9bdd7f3023c9885873e3f8aa73d0dbfefbddc30b7bdf3b1163e4ed901bebafb5c3cb3bd1a3c6a1a86bda1fb44be58ada0be1ed76c3cdad3d43ef2a87c3d0ca437beba71db3ccbb5dfbd7e910bbdda27f63d821d9b3d66702fbdabe8a9bdbd4bbcbcada7323c3621c93d31da8bbd99388bbb76490a3e6abc16be4bfa113dd4bea0bdc6d6e5bcad9e98bd6705953ca6ba24bd7039f6bc9221803cc008a5ba08ca013e9cb7103d85f713be593e883dcc1185ba043c433bdc16533df4d23b3c5bf584bd2c0e71bd4a23e53d950285bedbf4873ef56e393ebe6b7cbdd110003edee0303cecf0e33c5a65a7bcaff50e3cde2714be71d207bd7cd0efbb3be9c93ca26fdc3c82a247bdb5bea6bd40f1ae3dab445c3d1a45523d13b62d3ca252b8bd3e4421bcb48c43be1504113e4396d8bde4868d3eace08fbd137b11bea68d08bef2f8c03c41b31d3e65821bbe061dd2bd76bf03be60abbbbd4b4342bdf3ede7bc8797fabb94af273cbcc30abc604cb4be813889bdb9c319beb5dab73d24b5a8bcecb7af3c3702893cf5c83fbe5ce49abd3e25123d5e2800bdba634dbd1871163dafbd793ef469e23b1509f0bdd0b9f6bccc011dbd45c2793e2a1a473eaa84f13ce85b8f3c7d1eaebdba6daa3d76db43beb57fa3bd13de073efb811a3e71d9d03c38176f3d908f9bbd14ac1abd7eb8a53d328f6ebed72c9e3d17f30c3d732a3cbe7a44c9bd7c5e4dba47d5873e005db53d97a7febd1caab5bd2be21b3ee7bff6bdca0e403c039b273c2f098abd0d2944be34f79abe8ac1a73cf173d33e18007a3dfb7639beddff953c4b00f3bd90270fbd7ecfce3d4823963de4a81dbdbf14b7bde82d5dbc9864453c3afbd83da580a1bd2cbd86bbdc66103ed70a1cbea4fb1c3d1953b2bdb6fbd2bca0f98dbd6e79843cd43ef7bc1bb1b8bcca98903c00b713381bdcfc3d12d70d3d3c7819be1e1a793da4c209bc400e7f39dd9a4f3d6cf443ba1e1361bda77f5bbd7c96033e782f88bea44a8b3e5c2a383e925091bdcde5053ea4e8203c4f428e3cb8225ebcc8be7ebb3aa316be958a34bd239edf3b4401033d2069e23cdd1c50bde5e3c0bd2b57c33dd4d08d3d58fa703d9411e93c84a6afbdb5a91abcd45936be521f2d3ee7b3c2bd5935813ef85862bd2dc52dbecf99f7bd3c6d073d4b28123e6e9d31be346ed3bd90aaddbdae69e9bd152c86bd17f28fbc44e3023db36a343c06e505bdce88b8beb9cf56bddabf1bbe7050b53d78fc61bc60a6c53a3a75993c2dab5bbedba5ecbd5e4c6d3d7a323e3cf4c278bda446ec3c59bc863e7a81bb3b59c7c2bd1e9dfabc0c13b4bc3454853eac303f3e5cab3b3d3093b9bb58c1cdbda4e2d33db27543be06eb55bd8a141b3e44541e3e4e12f63cf09f883d6f39e4bdcab986bb0ed6543d15e486be38acb53dee79683d53d71dbec4d6a0bd3474ee3acc3e843e870c993d0f0bfbbd546adfbd2ab3163e97aaf8bdbd751e3d9c63613dfade98bd169258be5c919ebef7e91c3c9f46ce3e45f4b73dfbf526bec05dcb3a403aeabd467878bd71cdba3db4e3c33d2a7337bd82468abd2a777abc5b75013d672db23d23ac06bd6dd17bbd0c4b163e77cf07beb22d6e3d32549fbdf9b672bc4fd1debd84a17b3ca3afabbdba0a10bd007c5c3cb2da823cd237f23deebe7a3d3bb80fbe226f8b3de207a6bcb67e15bc3c97db3c02ae75bb9e48adbdbaa22dbd1297093e053596be54c4883e8b6c3d3ed7be61bda8f4003e06ea253c34609bbb304d68bade25f53be7c52cbeb3434dbd849247bc6c1a013ccda5963d4da915bd1c2dc8bd06dec73d92319f3d92f47c3dc0b3163d3bd7a7bd26b1f9bc716743be7fd6213e79f5a2bdb9f7843e0bb05abc847302bea1f711be2b97803def3cf13d2ee419be4748f2bdae91e1bd169fecbd11d579bdf43eccbc85a4173d33bba5bc1ebfe8bc6b86bfbe3bc0b0bdfae538beaf82f93d85b094bc1466403bb67308bcab1944be145c99bd6b845e3c50448c3bb6e725bd8f8dc5bb6281803e4573d73c18c9b3bd984a14bc924897bd4679763ef99b603ef317283c9a962d3db4efc4bdf4a0d73dafee3fbe5df704bd84341d3edab7223ec98e403d7fcea03da4198fbd6ae147bcb861bb3bfa7789be91a9c13d739f5d3dfabcdcbd496995bdc897433b33da953e32aa043e6563e1bd95aed9bd37ef233e1226efbdcbd7b23ccc13603d277ac9bd16b24ebe897186be30a47a3d8db4ca3e7c4ecc3da80741be4671813b92c838be55e878bd20978c3d6d7f523d5ee15dbda20547bdbfc023bd38a8cd3c4d48113e20e45abdad735fbd59633e3e958cfebd88b86d3da8f75ebd0779043c9443a5bd58819aba864975bd3e5491bd72f0853d3c8441bb0de3fc3db67b4e3d5d7003be5defac3df48afdbce896263cfef6273d835e51bc33f5a5bd638342bd5e4d213e56e083be284a923e72261e3e936445bd2d50ee3df22af43b8d67083d762d26bd728db1bc8405f0bd668d09bd1750b5bccbec773d2cea113dee11a5bbb763d5bdef17ae3dfe9e763d8ac48a3c34cc07bd4d90febd1c45ea3d8bbab2bd0f2c033ec1742abd866a5c3eb1e46fbd263259be9f8191bd2035d7bc50fd1f3ebac527be4f348dbd53ad06be4ef934bd081e03bd3dab84bc65667e3c17bc013d744732bc8f6fa6bed00a15bd93bc01be9efa3b3d4bae6fbddc06f03c040612bc637d63be5aac33be93970c3da2f0233c1777c0bd70ec2dbdbf88453e1d961d3d52de1dbe10f66e3dbc3e573d9fe09a3e73ab373e2cb4543dccda68bc7ed3d8bdad65f03ddf648abe522f7a3c8641583d5649133eac1f943dc6aa543ddd1817be8886b73d83c0843d861a84bec2bea63da2d7023e6c3671bd1dac0fbd0469373db5815a3eb779973d2f24e9bd49af92bde0a1103e16c911becca8cd3cbe20be3c64fb24bdaa632cbe2cc88abe15589b3dbd04c33e84970e3e783d0bbe141a33bdee071bbec0e3583d2ec3c23dc70c893d016d6cbde6b0b7bd91cd95bd40ec173c5fee993d6cef2cbb576599bd6f463b3e17b50cbd94159bbb80afa7bce236ea3b063d1fbe73b73f3d12d4e6bd08f7acbc8f95943d135574bde80a8b3d6810dd3db2f54dbe9a86003eea42f9bc5e15d83b23c88d3d7aa97b3be1096ebdfd99a93cd926853d02e575bea7eea33e28863f3e6849f7bc020ab33dc2f4603d7dff19bd2c5f643cf454abbbec3e37be08ea86bdf577983cf8b022bc3dffa53d1c3b02bc, 'gs://linya-api-bucket/db_pictures/erick/20260224_101000/encoding.npy', '2026-02-24 10:10:03');

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
  `customer_email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `finished_queues`
--

INSERT INTO `finished_queues` (`finished_id`, `original_queue_id`, `office_id`, `window_id`, `processed_by`, `finished_at`, `created_at`, `status`, `services`, `customer_name`, `customer_phone`, `customer_email`) VALUES
(1, 94, NULL, 1, 'lexty', '2025-11-16 20:35:04', '2025-11-16 20:34:59', 'finished', 'Legal Documents', 'John Lexter B. Armenta', '09915261467', 'ricksiv0107@gmail.com'),
(2, 92, 'Registrar', 3, 'lexty', '2025-11-16 21:21:12', '2025-11-16 19:48:12', 'finished', 'Tor (PDF), form137 (PDF)', 'John Lexter B. Armenta', '09915261467', 'ricksiv0107@gmail.com'),
(3, 95, NULL, 2, 'lexter', '2025-11-16 21:50:43', '2025-11-16 21:50:15', 'finished', 'Borrow Laptop (PDF), Thesis Defense', 'John Lexter B. Armenta', '09915261467', 'ricksiv0107@gmail.com'),
(4, 96, NULL, 2, 'lexter', '2025-11-16 21:50:46', '2025-11-16 21:50:25', 'finished', 'Thesis Defense (PDF)', 'Camille T. Soliman', '09050795313', 'soliman.camille.bscs2022@gmail.com'),
(5, 97, NULL, 1, 'eryel', '2025-11-16 21:51:05', '2025-11-16 21:50:35', 'finished', 'Borrow Laptop', 'Christian Espinoza', '09223394958', 'espinoza.christian.bscs2022@gmail.com'),
(6, 98, 'CSD Office', 2, 'eryel', '2025-11-16 21:56:56', '2025-11-16 21:56:20', 'finished', 'Borrow Remote', 'Camille T. Soliman', '09050795313', 'soliman.camille.bscs2022@gmail.com'),
(7, 99, 'CSD Office', 2, 'eryel', '2025-11-16 21:57:31', '2025-11-16 21:56:35', 'finished', 'Borrow Remote', 'John Lexter B. Armenta', '09915261467', 'ricksiv0107@gmail.com'),
(8, 100, 'CSD Office', 1, 'christian', '2025-11-16 21:57:33', '2025-11-16 21:56:47', 'finished', 'Borrow Remote (PDF)', 'Christian Espinoza', '09223394958', 'espinoza.christian.bscs2022@gmail.com'),
(9, 90, 'Management Informati', 1, 'Astaff1', '2025-11-18 08:16:28', '2025-11-16 06:05:00', 'finished', 'Add software', 'John', '09456654322', ''),
(10, 101, 'Management Informati', 1, 'Astaff1', '2025-11-18 08:16:30', '2025-11-18 04:58:39', 'finished', 'Assist in AIms', 'Aeryhiel John', '09301939121', 'ricksiv0107@gmail.com'),
(11, 102, 'Management Informati', 1, 'Astaff1', '2025-11-19 18:15:39', '2025-11-18 07:12:14', 'finished', 'Assist in AIms (PDF), Add software (PDF), Fix printer (PDF)', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com'),
(12, 103, 'Management Informati', 1, 'Astaff1', '2025-11-19 18:15:42', '2025-11-18 07:56:45', 'finished', 'Assist in AIms', 'Aeryhiel John', '09301939121', 'ricksiv0107@gmail.com'),
(13, 104, 'Management Informati', 1, 'Astaff1', '2025-11-19 18:15:46', '2025-11-18 08:11:06', 'finished', 'Assist in AIms (PDF), Add software (PDF), Fix printer (PDF)', 'Ricardo Salvador', '09915261467', 'ricksiv0107@gmail.com'),
(14, 114, 'Management Informati', 1, 'Astaff1', '2025-11-22 12:30:28', '2025-11-18 09:45:16', 'finished', 'Add software', 'Aeryhiel John', '09301939121', 'ricksiv0107@gmail.com'),
(15, 115, 'Management Informati', 1, 'Astaff1', '2025-11-22 12:30:32', '2025-11-18 09:47:51', 'finished', 'Assist in AIms', 'Abc', 'abc', 'abc'),
(16, 116, 'Management Informati', 1, 'Astaff1', '2025-11-22 12:30:35', '2025-11-18 09:51:46', 'finished', 'Add software (PDF)', 'Christian Espinoza', '09926870934', 'espinoza.christian.bscs2022@gmail.com'),
(17, 117, 'Management Informati', 1, 'Astaff1', '2025-11-22 12:30:52', '2025-11-18 09:55:46', 'finished', 'Fix printer', 'Abc', 'abc', 'abc'),
(18, 119, 'Management Informati', 1, 'Astaff1', '2025-11-22 12:30:55', '2025-11-18 10:08:18', 'finished', 'Assist in AIms', 'Abc', 'abc', 'abc'),
(19, 120, 'Management Informati', 1, 'Astaff1', '2025-11-22 12:31:03', '2025-11-18 10:17:53', 'finished', 'Assist in AIms', 'Abc', 'abc', 'abc'),
(20, 122, 'Management Informati', 1, 'Astaff1', '2025-11-22 12:31:06', '2025-11-18 10:30:52', 'finished', 'Assist in AIms', 'Abc', 'abc', 'abc'),
(21, 167, 'Registrar', 1, 'robert', '2025-11-22 12:33:24', '2025-11-22 12:32:06', 'finished', 'Tor', 'Aeryhiel John Mojica', '09931131311', 'aeryhielmojica05@gmail.com'),
(22, 171, 'MIS', 1, 'Astaff1', '2025-11-27 10:35:57', '2025-11-22 13:17:11', 'finished', 'Assist in A.I.M.S, Fix Printer, Install Software, Item Borrow (PDF), PC Reformat (PDF)', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com'),
(23, 172, 'MIS', 1, 'Astaff1', '2025-11-27 11:46:33', '2025-11-22 13:18:28', 'finished', 'Item Borrow', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com'),
(24, 173, 'MIS', 1, 'Astaff1', '2025-11-27 12:02:48', '2025-11-22 13:20:37', 'finished', 'Install Software', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com'),
(25, 174, 'MIS', 1, 'Astaff1', '2025-11-27 13:10:14', '2025-11-22 13:21:22', 'finished', 'Install Software', 'Aeryhiel John', '09555111111', 'ag@gmail.com'),
(26, 177, 'MIS', 2, 'Astaff2', '2025-11-27 13:24:17', '2025-11-22 13:32:00', 'finished', 'Fix Printer', 'Aeryhiel John', '09555111111', 'ag@gmail.com'),
(27, 178, 'MIS', 2, 'Astaff2', '2025-11-27 13:31:31', '2025-11-22 13:41:13', 'finished', 'Install Software', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com'),
(28, 176, 'MIS', 1, 'Astaff1', '2025-11-27 19:55:52', '2025-11-22 13:31:04', 'finished', 'Fix Printer', 'Aeryhiel John', '09555111111', 'ag@gmail.com'),
(29, 168, 'Registrar', 1, 'robert', '2025-11-27 21:52:30', '2025-11-22 12:35:10', 'finished', 'ID Request', 'Aeryhiel John', '09301939121', 'ricksiv0107@gmail.com'),
(30, 169, 'Registrar', 1, 'robert', '2025-11-27 21:52:36', '2025-11-22 12:36:13', 'finished', 'ID Request', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com'),
(31, 170, 'Registrar', 1, 'robert', '2025-11-27 21:52:37', '2025-11-22 12:41:59', 'finished', 'ID Request', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com'),
(32, 175, 'Registrar', 1, 'robert', '2025-11-27 21:52:40', '2025-11-22 13:22:41', 'finished', 'Adding and Changing', 'Aeryhiel John', '09555111111', 'ag@gmail.com'),
(33, 179, 'Registrar', 1, 'robert', '2025-11-27 21:52:42', '2025-11-22 13:42:40', 'finished', 'Adding and Changing', 'Aeryhiel John', '09555111111', 'ag@gmail.com'),
(34, 182, 'Registrar', 1, 'robert', '2025-11-27 21:52:47', '2025-11-22 13:48:54', 'finished', 'ID Request', 'Aeryhiel John', '09555111111', 'ag@gmail.com'),
(35, 208, 'Registrar', 1, 'robert', '2025-11-27 21:52:50', '2025-11-27 12:09:32', 'finished', 'ID Request (PDF), Adding and Changing (PDF)', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com'),
(36, 181, 'MIS', 1, 'Astaff1', '2025-11-27 22:41:12', '2025-11-22 13:45:23', 'finished', 'Item Borrow', 'Aeryhiel John', '09555111111', 'ag@gmail.com'),
(37, 183, 'MIS', 5, 'Astaff1', '2026-02-05 07:09:12', '2025-11-22 13:51:35', 'finished', 'Fix Printer', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com'),
(38, 184, 'MIS', 5, 'Astaff1', '2026-02-05 07:09:15', '2025-11-22 13:53:49', 'finished', 'Item Borrow', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com'),
(39, 185, 'MIS', 5, 'Astaff1', '2026-02-05 07:09:18', '2025-11-22 13:56:18', 'finished', 'Item Borrow', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com'),
(40, 186, 'MIS', 5, 'Astaff1', '2026-02-05 07:09:27', '2025-11-22 14:00:34', 'finished', 'Item Borrow', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com'),
(41, 187, 'MIS', 5, 'Astaff1', '2026-02-05 07:09:30', '2025-11-22 14:04:06', 'finished', 'Item Borrow', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com'),
(42, 188, 'MIS', 5, 'Astaff1', '2026-02-05 07:09:34', '2025-11-22 14:08:53', 'finished', 'Item Borrow', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com'),
(43, 189, 'MIS', 5, 'Astaff1', '2026-02-05 07:09:40', '2025-11-22 14:09:37', 'finished', 'Item Borrow', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com'),
(44, 190, 'MIS', 5, 'Astaff1', '2026-02-05 07:09:43', '2025-11-22 14:16:51', 'finished', 'Item Borrow', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com'),
(45, 191, 'MIS', 5, 'Astaff1', '2026-02-05 07:09:54', '2025-11-22 14:18:46', 'finished', 'Item Borrow', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com'),
(46, 192, 'MIS', 5, 'Astaff1', '2026-02-05 07:09:58', '2025-11-22 14:19:49', 'finished', 'Item Borrow', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com'),
(47, 193, 'MIS', 5, 'Astaff1', '2026-02-05 07:10:02', '2025-11-22 14:21:41', 'finished', 'Item Borrow', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com'),
(48, 194, 'MIS', 5, 'Astaff1', '2026-02-05 07:10:06', '2025-11-22 14:23:20', 'finished', 'Item Borrow', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com'),
(49, 195, 'MIS', 5, 'Astaff1', '2026-02-05 07:10:10', '2025-11-22 14:27:33', 'finished', 'Item Borrow', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com'),
(50, 196, 'MIS', 5, 'Astaff1', '2026-02-05 07:10:13', '2025-11-22 14:28:45', 'finished', 'Item Borrow', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com'),
(51, 197, 'MIS', 5, 'Astaff1', '2026-02-05 07:10:18', '2025-11-22 14:29:43', 'finished', 'Fix Printer', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com'),
(52, 198, 'MIS', 5, 'Astaff1', '2026-02-05 07:10:20', '2025-11-22 14:31:55', 'finished', 'Item Borrow', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com'),
(53, 199, 'MIS', 5, 'Astaff1', '2026-02-05 07:10:26', '2025-11-22 14:33:47', 'finished', 'Item Borrow (PDF), Install Software (PDF), Fix Printer (PDF), PC Reformat (PDF)', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com'),
(54, 200, 'MIS', 5, 'Astaff1', '2026-02-05 07:10:29', '2025-11-22 14:34:59', 'finished', 'Item Borrow', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com'),
(55, 201, 'MIS', 5, 'Astaff1', '2026-02-05 07:10:39', '2025-11-22 14:37:30', 'finished', 'Item Borrow', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com'),
(56, 202, 'MIS', 5, 'Astaff1', '2026-02-05 07:10:42', '2025-11-22 14:37:42', 'finished', 'Item Borrow', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com'),
(57, 203, 'MIS', 5, 'Astaff1', '2026-02-05 07:10:47', '2025-11-22 14:38:10', 'finished', 'Install Software, Fix Printer (PDF), Item Borrow (PDF), PC Reformat (PDF)', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com'),
(58, 204, 'MIS', 5, 'Astaff1', '2026-02-05 07:10:49', '2025-11-23 04:58:50', 'finished', 'Item Borrow', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com'),
(59, 205, 'MIS', 5, 'Astaff1', '2026-02-05 07:10:51', '2025-11-23 06:17:16', 'finished', 'Install Software (PDF)', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com'),
(60, 206, 'MIS', 5, 'Astaff1', '2026-02-05 07:10:54', '2025-11-23 06:17:48', 'finished', 'Install Software (PDF)', 'Aeryhiel John', '09301939121', 'ricksiv0107@gmail.com'),
(61, 207, 'MIS', 5, 'Astaff1', '2026-02-05 07:10:56', '2025-11-26 17:23:37', 'finished', 'Item Borrow', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com'),
(62, 209, 'MIS', 5, 'Astaff1', '2026-02-05 07:11:19', '2025-12-03 09:33:01', 'finished', 'Item Borrow', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com'),
(63, 210, 'MIS', 5, 'Astaff1', '2026-02-05 07:11:22', '2025-12-03 10:09:12', 'finished', 'Fix Printer', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com'),
(64, 211, 'MIS', 5, 'Astaff1', '2026-02-05 07:11:24', '2025-12-29 12:47:27', 'finished', 'Fix Printer', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com'),
(65, 212, 'MIS', 5, 'Astaff1', '2026-02-05 07:11:27', '2026-01-19 06:02:07', 'finished', 'Assist in A.I.M.S (PDF)', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com'),
(66, 213, 'MIS', 5, 'Astaff1', '2026-02-05 07:11:29', '2026-01-19 06:04:18', 'finished', 'Fix Printer', 'Aeryhiel John', '09301939121', 'ricksiv0107@gmail.com'),
(67, 214, 'MIS', 5, 'Astaff1', '2026-02-05 07:11:32', '2026-01-23 08:31:35', 'finished', 'Assist in A.I.M.S', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com'),
(68, 215, 'MIS', 5, 'Astaff1', '2026-02-05 09:07:29', '2026-02-05 06:09:15', 'finished', 'Fix Printer (PDF), Assist in A.I.M.S, Install Software', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com'),
(69, 216, 'MIS', 5, 'Astaff1', '2026-02-05 09:07:31', '2026-02-05 07:48:02', 'finished', 'Assist in A.I.M.S, Item Borrow, Fix Printer (PDF), PC Reformat (PDF)', 'alwyn', '09763683414', 'adrianoalwyn@gmail.com'),
(70, 223, 'MIS', 5, 'Astaff1', '2026-02-10 12:48:56', '2026-02-09 05:28:59', 'finished', 'Fix Printer', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com'),
(71, 224, 'MIS', 5, 'Astaff1', '2026-02-10 12:49:00', '2026-02-09 06:36:57', 'finished', 'Fix Printer', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com'),
(72, 225, 'MIS', 5, 'Astaff1', '2026-02-10 12:49:04', '2026-02-09 12:07:08', 'finished', 'Assist in A.I.M.S', 'RICARDO SALVADOR IV', '09568718445', 'ricksiv0107@gmail.com'),
(73, 226, 'MIS', 5, 'Astaff1', '2026-02-10 12:49:06', '2026-02-10 11:43:43', 'finished', 'PC Reformat', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com'),
(74, 227, 'MIS', 5, 'Astaff1', '2026-02-12 09:22:19', '2026-02-10 12:48:12', 'finished', 'Item Borrow', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com'),
(75, 228, 'MIS', 5, 'Astaff1', '2026-02-12 09:22:43', '2026-02-12 08:48:31', 'finished', 'Assist in A.I.M.S', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com'),
(76, 229, 'MIS', 5, 'Astaff1', '2026-02-12 09:25:02', '2026-02-12 09:24:08', 'finished', 'Assist in A.I.M.S', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com'),
(77, 230, 'MIS', 5, 'Astaff1', '2026-02-12 18:26:37', '2026-02-12 10:26:28', 'finished', 'PC Reformat', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com'),
(78, 231, 'MIS', 5, 'Astaff1', '2026-02-24 18:25:33', '2026-02-12 18:29:16', 'finished', 'Item Borrow', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com'),
(79, 233, 'MIS', 5, 'Astaff1', '2026-02-24 18:25:36', '2026-02-24 13:25:47', 'finished', 'Fix Printer (PDF), Install Software (PDF)', 'johnlex', '09276164084', 'johnarmenta.8v@gmail.com'),
(80, 234, 'MIS', 5, 'Astaff1', '2026-02-24 18:25:38', '2026-02-24 13:27:37', 'finished', 'Assist in A.I.M.S', 'Aeryhiel John Mojica', '09931131311', 'aeryhielmojica05@gmail.com'),
(81, 235, 'MIS', 5, 'Astaff1', '2026-02-24 18:25:40', '2026-02-24 13:35:34', 'finished', 'Assist in A.I.M.S (PDF)', 'John Lexter B. Armenta', '09276164084', 'johnarmenta.8v@gmail.com'),
(82, 236, 'MIS', 5, 'Astaff1', '2026-02-24 18:25:42', '2026-02-24 13:46:55', 'finished', 'Assist in A.I.M.S', 'Earyhiel Mojica', '09123456789', 'hwdiwhdqdj@gmail.com'),
(83, 237, 'MIS', 5, 'Astaff1', '2026-02-24 18:25:44', '2026-02-24 15:09:57', 'finished', 'Assist in A.I.M.S', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com'),
(84, 238, 'MIS', 5, 'Astaff1', '2026-02-24 18:26:41', '2026-02-24 15:12:11', 'finished', 'Assist in A.I.M.S', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com'),
(85, 239, 'MIS', 5, 'Astaff1', '2026-02-24 18:26:43', '2026-02-24 16:57:52', 'finished', 'Item Borrow', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com'),
(86, 240, 'MIS', 5, 'Astaff1', '2026-02-24 18:29:24', '2026-02-24 16:58:31', 'finished', 'Install Software (PDF), Fix Printer, Assist in A.I.M.S (PDF)', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com'),
(87, 241, 'MIS', 2, 'Astaff2', '2026-02-24 18:41:47', '2026-02-24 18:15:10', 'finished', 'PC Reformat, Assist in A.I.M.S, Item Borrow, Fix Printer, Install Software', 'Erick', '09123455555', 'ddd@gmail.com'),
(88, 243, 'MIS', 2, 'Astaff2', '2026-02-24 19:03:57', '2026-02-24 18:42:34', 'finished', 'Item Borrow', 'Ricardo Salvador IV', '09915261467', 'ricksiv0107@gmail.com');

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
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `office_windows`
--

INSERT INTO `office_windows` (`window_id`, `office_id`, `window_no`, `assigned_staff_id`, `enabled`, `created_at`) VALUES
(11, 10, 2, 3, 1, '2025-11-18 07:13:56'),
(12, 10, 3, 8, 1, '2025-11-19 19:26:21'),
(13, 1, 1, 4, 1, '2025-11-22 12:33:08'),
(14, 10, 4, 19, 1, '2025-11-27 22:44:15'),
(15, 10, 5, 2, 1, '2026-01-28 10:14:00');

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
  `customer_email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `queues`
--

INSERT INTO `queues` (`queue_id`, `queue_code`, `office_id`, `window_id`, `created_at`, `status`, `customer_name`, `customer_phone`, `customer_email`) VALUES
(232, 'REG-001', 1, 0, '2026-02-12 18:56:55', 'pending', 'Aeryhiel John Mojica', '09555111111', 'ag@gmail.com'),
(242, 'REG-002', 1, 0, '2026-02-24 18:29:59', 'pending', 'Earyhiel Mojica', '09123456789', 'mhwdiwhdqdj@gmail.com'),
(244, 'MIS-020', 10, 0, '2026-02-24 19:18:53', 'pending', 'Aeryhiel John Mojica', '09931131311', 'aeryhielmojica05@gmail.com');

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
(1, 2),
(10, 20);

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
(239, 232, 20, 0, 1),
(256, 242, 20, 1, 1),
(257, 242, 19, 1, 1),
(259, 244, 17, 0, 1);

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
(29, 'PC Reformat', 10, 1);

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
  MODIFY `client_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `finished_queues`
--
ALTER TABLE `finished_queues`
  MODIFY `finished_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

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
  MODIFY `queue_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=245;

--
-- AUTO_INCREMENT for table `queue_services`
--
ALTER TABLE `queue_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=260;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

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
