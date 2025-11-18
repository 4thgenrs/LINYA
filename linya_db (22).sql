-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 18, 2025 at 02:40 AM
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
-- Database: `linya_db`
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
  `office` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`user_id`, `user_name`, `password`, `name`, `user_type`, `office`) VALUES
(1, 'admin', 'admin', 'administrator', 1, 1),
(2, 'Astaff1', 'Astaff1', 'Jason', 2, 2),
(3, 'Astaff2', 'Astaff2', 'Jameson', 2, 2),
(4, 'robert', 'robert', 'Robert', 2, 1),
(5, 'badong', '1234', 'badong', 2, 2),
(7, 'roberts', 'roberts', 'roberts', 2, 8),
(8, 'eryel', 'eryel', 'eryel', 2, 8),
(9, 'christian', '12345', 'christian', 2, 8),
(12, 'lexter', '123', 'lexter', 2, 2),
(13, 'cams', 'cams', 'cams', 2, 8),
(14, 'espi', 'espi', 'espi', 2, 8),
(15, 'mojica', 'mojica', 'mojica', 2, 8),
(16, 'ricardo', '12345', 'ricardo', 2, 1),
(17, 'salvador', 'salvador', 'salvadors', 2, 1);

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
(2, 'Christian Espinoza', 'christian_espinoza', '09926870934', 'espinoza.christian.bscs2022@gmail.com', 0x934e554d5059010076007b276465736372273a20273c6634272c2027666f727472616e5f6f72646572273a2046616c73652c20277368617065273a2028352c20313238292c207d20202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020200a4bb808be8d14963deef6a53d91d451bd861c89bde1e808becf184bbcf4812bbee767f03da9ff97bdc90b9b3e53e584bd07d022be58a633bedfb762bc0f89433ece532ebe941706be5af511bdf41885bb6f51013d898babbd97cbf93c07929b3d9c9511bdb839a6bed1d6ddbd2433f6bd30c1c9bbab70a6bca76873bc1d57a1bb3e8346beed84afbd24205bbcae497a3dca330dbca1640bbde917473e83138cbd03655abe8ee35d3bb466963c1de24f3eaee5153ed6c4953d4c01e93ca0389fbdf428fb3dc5e94cbefa639b3de025ab3d728efb3df6220a3ce5914ebc6d240bbec52c11bc6238f03d66282ebe7042b23caa38473dcae8aebd80988fbd255badbd357c923e1619223e99960dbed6a0febd9828163e70e6ecbd764081bdfc8f433d052525be0d3133be2b03aebe6e74b23d77fec13e98fc973db69a4ebe428f09bd0999c3bd736a933dbbce943d3a61c73dee9bb2bc227fc13ce58cf3bd15f347bd7f85293e2a69c7bd59c26abde724573e69ca21bdfdd1df3d00c122bdb032a5ba24d93c3c51e1243d3746e5bdc1f0cf3cea98bc3dc2088e3c67ce4b3b4195a73d8a6a1abe187e393dd4c990bbb4a8153da84b083c98d7e63cce11c6bd77deecbd96eddd3d34854cbe7b0a893e9a034c3e6c4c2e3d5f27ef3d7bdebc3dfefc5f3df086a6ba385938bd29c21dbedeb2bfbb9018113e0006cabb74aeb03dc018043d8b45cebd22b2393d3273c23d3c6f53bda4b901bea8d2eabd6631c23cef1a10bebd1a1d3eda0ca7bd168d963e3c64b3bd2d6942bec3affabdd2582dbdc1e9473e74c1a9bd4195d8bd58f4b2bcd138d1bc58315a3d721b0dbd3909c2bc62ecc33d20e0eebb6d5396bea5a4cfbdeffdc1bd2249413d8a66fabc4cb7c7bb53da033eaba615be00a1adbdc446bcbc34c8543d5ded65bc23d5f7bcf993333e97ff31bd0f3675be2bd294bd0c5bed3cbcb9613eaf810a3e21a5b63c56680d3d1b0808bd1723693dcf1979bec04c673afb6fb43d88700b3e2f4c8abc382d473cec92fabdf563c3bc76bd9c3d7bea59becbc4b53c6a6d903dfc2ee3bdb902ddbd01ecc4bd1470933e8cb81c3e2de403beab7d8cbd6ea3603e8af813beb60d8cbd72d3823d302b0ebed64a1abe3a1897befa33293dbc5ad03e51b2dc3d38793bbe75efb13c87e3aebda56a963d03f5903d57016f3dc52606bd5c5e4c3d091ecebd4a19d1bc3f2b083ea40abfbdd6fe3fbc87266c3ea864fdbb4242a63d956080bcd522db3bc0e9423d5928c1bcf7b517be8bddf1bb9cf7bc3d6ef317bc6aa92a3d467d993ddbdf1cbebf60aa3dee7a833cfbf8073d1d2c493c58e4323cbff3b7bd395772bd1c9f1a3e8db728be6f4b3d3e06a2f73d4ea2283c825fc23d7398be3d1589553d8cb6d0bb2a681bbe4fa7ffbd05590ebdba60153ec114a3bda60f873d3a1ddd3ce32601be68291c3dd0a1db3d22109cbdfbe0d8bd2411cdbd424bfb3bdf3a07bef8d7ff3d57b3b8bd7abd953e6ffaefbdd4046abe52c71ebe926a46bcabbb493e5d6cbcbdebf40fbe76a80fbde2ac9fbc1343dc3cdb928ebd2126e4bc4981a43d1a3f22bc9c9dafbe40e502bec4b6c0bd0436823c3af227bda701cbbc7799c13d744550beeabe98bd171469bda4bd743d804b223b242ab0bcccf5363e047f67bdeafe67be52ae59bdc4a1c53c645f3d3e108b2c3e33e38e3d446b223cf54fe7bc5353b23d7f387bbe7f9e963d780de03d1880173e2dca7e3c3e3d85bc7d0302beb9ddecbc5d3b9e3dbe9842bee9a3ff3c923f1a3df32904bee720adbd4a4499bdd8f0953e6ebb2d3e9922e5bd1d74b9bdae445e3ed22c0bbef6173abd7696163db06b24be368003bef60ea9be663ebd3de327c23ef6de7b3d14bb4abe90de233cd5ace8bd9e14663de4e3783daefd023d963932bdf05d7cbb6c0cfabd3850b7bc7e713a3e58b3eebdf7c839bc60a56b3ee8362fbde197cf3d5b1eebbba9e8073dce9f3a3df6a654bc8318e7bd17eb783c1b8d013ec88712bd91600a3c22b49a3d661325be7a97723d0abd313cb8eb3f3de6c4ae3cdc5b723c5348e7bd08ebd6bd7d2e193e6bd327be0b4b7c3e61a20a3ef7c2b1bc09d8d53d6c84d63d0b6dcc3d5fa54abc1f1909be109cf0bd331212bd3959383e74eb083b0e99823d7548873d29f7c8bd21f6623d5f105d3d767a5abd6fa346bd957be1bdec3b55bbe5fa3cbe0119e83de02db0bd5fbe9d3e1480e9bd11374bbed47d4bbe7003a33ae43b453e2b8015bea9cb1ebe742723bdbe6010bd4806923df78d04be60b2e73c6c98db3d0171ecbdba7aa4be8f71b5bd8e9901be5888f5bcde5967bdcb9359bd88559c3dc76d3bbe68d6adbd457f81bb9e5aa43d8cc9eabb90ba2dbc2a9e4e3e989c44bd91ba64be1a3606bc245c15bbe9a65a3eec3d273eb5946a3debfac43c0bd288bdacc2d53d153248be6aa0d33d5292b83d833f0c3e6d3dde3ca47324bb5f7e13be1077f5bcd8ff0b3e5d3649be6189103d5aa6073d728624be92e9a8bdd6ddd9bd8af3833e79c2453ee8fc11beaea9c6bd4b602a3ed064a4bd4bc587bd7b142e3d8d412dbe4d143cbe8ecfaabe502a843da814c63e62e4a63dfd3d80be0fd12ebd866544bdb053723d7634013e7188fb3d1825473b711e913cf0d48dbd448904bc1028f33dee28b1bd7f5fbfbd864e4d3e01089cbda7cefa3d5842f3bbb72fbb3b07c6023c5bca7f3d7805efbd7474a83c7c51883d51fb1f3c9924bf3b40c5423de761e2bdae09703d456fb83c8817553d383768bcb9c3e83b8fc4b6bdf4c09dbde49bc43dab8184bebe1c863e1e664a3e22ba063ddc3cfe3d0f85a03d578f023d6427453d6079a1bd89fc3bbee5ea8c3cb14b3f3eb42c58bbf5fadb3dc80a8c3c86d708be42a5863c5114963dfff72dbc8856b1bd141c2cbef0ef1a3dc49cd6bd7da7de3d4044bbbde218963e985983bda76c17be3dd8f5bd7a7315bc6ed51e3e77fa2dbeae5599bd9600d9bc929be5bc3635bb3d7d7879bdc959113c7b1c9f3d6c7965bddc4ababe54f1c1bd802aecbda7d6133d048645bcc0169e39b956553dab1c5ebe1ef464bde6804cbd5e59a13d7dff64bb4a468b3c8a5c0c3e8a369bbced4964bec9ce79bd7ac4383cde5d553eea841a3ed6ff843d845b343dfe7c7ebddcab273d6ecb33be7eda1a3d0a558b3d51571a3e7aacbd3c396182bcb972e2bdc678bebce9eebe3d492648be96b3113c7ce0ac3c85e180bd566d73bd76fa40bd1c089e3e020a023eb4a0ffbd3bbc85bdd8ce573e7d1b28befe2394bd2afe153d006fe1bd67cb37be5b009cbe1e90563d881dcc3ea367673d947133bef44607bcc0c707bec41b44bb639db33dd51ecc3d5061b2bcf183833dad41c7bd871ec73c4c42223efe11cabd5c2ebabb25ce483e2253dabc49a08d3dee36d4bc9fc034bc6854cfbb264c783d0240e9bd7ba0d2bc27b5913dd64003bdf97aa3bb2310763dddcf3ebeb352b23d94680a3d61244ebca9b5c23c8ffa373c8b8ebdbd4af0cebd5c33d63d501d4fbe04327f3ebf144c3e5de0c43cc7921c3ee194293d002f423d0e5ed13bdf66e6bd0d4e35be1618e23c15c3f83d7c5656bdfd5a4c3d44a4383c, 'C:\\xampp\\htdocs\\faceapp_php\\db_pictures\\christian_espinoza\\encoding.npy', '2025-11-15 10:00:03'),
(3, 'Aeryhiel John', 'aeryhiel_john', '09663811477', 'aeryhielmojica000@gmail.com', 0x934e554d5059010076007b276465736372273a20273c6634272c2027666f727472616e5f6f72646572273a2046616c73652c20277368617065273a2028352c20313238292c207d20202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020202020200ab20efbbdee8ea33d8a0c0e3d1865b8bc54d559bc8b875ebd469b12bd08810ebe52be2a3ede3002be8a80903ef6c127bdbecf50bedea7fbbd040f1cbd89952b3ed4a22dbe00d6edbdf241e2bc48fe353b520f9f3d1a31093ab4ff073cd74d493dcae288bdeae2adbe4849ccbd242b96bd99785b3cdd4a46bcc696eabc271220bdf7815bbea0dcbdbd6e55bb3cfce9463d79669ebdc1432cbd25683a3e4a5a43bd90c450be3e191fbd79d7cb3d4391823ee9e2fd3d85f5ce3d1910dbbc826e34bedcf29f3d0aaf30beefec0b3d2546da3d39548b3dadae933dad68033d5f4d04beeeac4b3de41edc3d6dfa47be6550223d59483c3d4e4ed7bd33921fbd576090bd98e1873e01958d3d1bbce5bdf96d1dbe8474f63d986036bea0d684bda345a33d562c82bd47827ebebcb7aabefce39f3cb7fbb33eb4a3173e809626be3991853dee1f5e3c7c0f24bd44e0373e77533c3ebb9900bc62f64b3db650c0bda5124dbc8964423e1c9864bd8c5f6bbca4bd653e62cce6bc3c701c3db6e7d03c4bb90c3de963c7bd31014c3ddba333beeb5225bd5670aa3d9408eb3cf8ce123dc789a13d2864ffbde9d7b33d6b91613c3c29b3bc129a393d07332fbd0141abbd0865dfbd355eb63d865f63be84805d3ec43c0d3efa7f9b3dbd8d293e4e44e33d6c30a43d241b4fbcb0fc15bd58d155be4221b1bb4aebbf3d5ac5f4bce6161c3e45e0e2bc30e5a0bd5873893d2108ff3cf52da9bd6f54b1bddc21fcbc7f8121bd34e623be718e4a3e36c7f6bdea339b3e0fd1b0bdf9244dbe7b98cfbd1b3082bdef77213eab5421bea545c5bd2c19d0bbd282823c19165c3d519de0bc7ab0353b3bc25c3ddea42fbda722b7be8376b6bd0437f2bd450bb1bc14e6dabc7aff84bc2cfe2e3d002527beb42c3cbda881ffba0df9143d48e48abd167d7dbdc646333e744987bdf32871be31e1a9bd8efa333d34de503e1a13283eceee2c3dc55e91bce9d507be23bbff3d1dab1dbeaa66873b733bfe3d35cd843d35cd1c3c4c0ef43cb0edacbd87ed893c025b023e9a593abeda250abcd6c9b53d3e0bb5bd3f329fbde2c6fdbda5db833e153f133e7579cebd75e034beabb7323e46ac32be6137d7bd595b993dba0b17be6dc362beaebc8fbe90f70cbd8611a63ecb2a143e72ad26be36b90dbb79fae0bca296df3cd239203eb011dd3d80a2183bbdd2d43c2c50d2bdb4ce8fbd9eb3593ea2ff94bd503718bdabfa603e948588bd4014f63c0a1562bbd366a9bc493e31bdd863503dcf4a41be20e13bbd56e5863d47447a3cb890333c7000e83dffce1dbef399b23ddc0553bc44c3473df62d543dabcf69bded189fbd31268ebd15fe293eda402bbe452f483e4749b53dbb7fe73df24cdf3df634c13dedc5623d63ee3abd6d13babd6ab880be4ca049bcff53bd3d28050ebdeccdd13d777010bd8863f3bdcbf1803dab99963c630b41bd8b1b43bd608851bdf388aabdfa7534be4db1083eb1fabebd7afd923e290069bdc39333be422b27be49e8f3bc207c3c3e6d5a4abe45b5d3bdf0bbc7bc5a1e30bc0ed35c3d2f3b3ebd2923fe3ca4da313d0e8296bd0d33b7beec1094bd595e8bbda387013dbcbc63bb0cc70fbdf3033dbc572f71be244291bd90ffc83c37edd93df8b633bd8c9b64bd42b73a3e40e228bd4aa34dbe342cd4bc24a5473d90fc603e6dad283e539fd03df8ce8dbbaa56febd9eced43de67b38be1ad9343d6343d83d1c60613d84bdb23cb800d2bbf6061dbe96a99c3c120d613ddc9d20be10f0d2bc8c70583d5a05a6bd9d1588bd94f3c7bdd004973ef3a7d23d3d8408be33b907be00dd033ec11bfebdb82e12bdfc645b3d6a85e7bd852250beebc3b3be3b37ba3c3f7fc83ec772df3d84a459be0e641ebb91340bbde0d0943ce9be2a3e934c173e32144b3cfa59703d945eddbd9bbdf1bc3f1b3c3e38e5a5bd0b856ebddf185d3eb541a0bc380c953d12d9503cb74d283dbf6079bde14bdb3c53ec22be2d4836bd289df33d5db0303d1d76213dda1fdb3da81efcbd808dc73dd977b83cdaa8853cbb347b3dadc0b2bcb4eac9bd54f601be4658d03dae0363be3cb0563e2324293e4010913d7c3b273e9f1ef63dc632be3d58ffeabcb9fe1cbcf76332be62800abd6f176f3da008213b3ebc0a3ec099aa396b9208be5ad5803d88ba453df5887fbd5a1467ba6f38e1bd624215bd2c9105bee241163e6ceac8bdf35e983e45ce37bde26927be859b17bee671a0bc23440c3e948e60be6ab6adbd5ec065bda490a93b5c3b933d021b22bdb0f2543d99d6463d28b7aabdbf1cc2be6e70ecbdba5391bde432733ca562e13ba2a19ebc137dd5bc39e284be8c46d7bd76f4c6bbb806843d57888cbd237306bd6e541e3ebc308dbc979133beb6e12dbdfb3a913db4c26a3e0aaa2d3e098ec73d24f1693bd18aeabdce288d3dd39f35be642d7e3b5b26ca3d7a769a3d448f293dbe22303da9bdc1bdfd13153dba94f03d276e3bbe2e2d233c2e11f63cc9d0cebd69fe9cbded1596bdb1ef873e28ceec3dc3eeeabd2df420beda89163e202b11be4f6b59bde2589a3d802099bd7e2054befa70abbee88e85bbd57aae3eabaada3da1291fbebe84f53c676092bd06aa55bc46a5083eb7ecf03dfcc0dabc54eea33dad76f8bdd88c743b2b73213e78bb43bd58fababc824e4b3e071e16bdae8c603d14fa9a3c9d3507ba9ea1cebd605f563dcb333dbecec1fbbc69c6b53d42b9f53c6fd1463c9cf48d3d640be8bd5edf8f3d9eaab13c647dc1bc76aaf63c47bb52bd04a2b9bd57a7d0bde2bd863da00878be3ee74d3e0cdc2c3ec471453d7353173ec701c93d289d2d3db98e22bd2868b6bc85944bbe161aacbb2c37843d3ce250bce4b8033e20861ebdacecfcbde0cce23cdcd48d3ca078c2bc9b8918bd58835bbd8d1584bd03d735bec359fc3d7d85e0bd6ab58c3e162689bddc792dbef6e015bea53f83bdce76433e5fc33ebe36fe07bedd3111bd08f430bb921cc83c72870cbc9230113d2eb20d3de12e8bbd1736bbbe66dad1bd6834a4bda0c2bf3bb01db03c7a8f11bdca99febbd2ff4bbe863a77bde47e823dce766a3d04050bbd123082bd0e45503e78b33fbd35a467be47d305bd1bd7ff3d7118793ea9b9033e7da8ad3db8c305bd292e13be56d4e73d77f407be015c933d8d29d73d2d40ba3d1acdc93cce26e03c08afcebd3473383dcbc5ee3d8b3444be95ff923c84e1993d2daeb7bd627a2bbdd386ccbd0895853e740ed73dfda0f1bd80e42fbeded7153e70c800bedf9f4bbd92816c3dce85dabdec7b6abe153ba5be480de8bb3d0cc93eaaa0063ea1cc28be48481f3dc63b81bbc47127bdd58b2c3e8f99293e6cceef3b9ff4f23c82d3c4bd9f3fe9bc94c34b3eed1520bd8ee477bda346483e62ca55bda00feb3c9f320d3cec49f23ccf8ebbbd9c29883d40e421be202520bd9650943daf9c303d1c5c5a3d6908aa3d3119dabda8bcb53dcd51b23cde09b33ccc1f7b3d808ab1bc262fa2bd7059c0bd5191a53dcd8665be10c9603e999dc23d715ee13d8357223ed8b2dc3dc44b973d9e2749bc587320bd104c64be28bff8bc767c9f3d8748f7bc24ed3a3efbf9b63c, 'C:\\xampp\\htdocs\\LINYA\\faceapp_php\\db_pictures\\aeryhiel_john\\encoding.npy', '2025-11-17 00:44:13');

-- --------------------------------------------------------

--
-- Table structure for table `finished_queues`
--

CREATE TABLE `finished_queues` (
  `finished_id` int(11) NOT NULL,
  `original_queue_id` int(11) DEFAULT NULL,
  `office_id` int(11) NOT NULL,
  `window_id` int(11) DEFAULT 0,
  `processed_by` varchar(100) DEFAULT NULL,
  `finished_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `status` varchar(20) DEFAULT 'finished',
  `services` text DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_phone` varchar(30) DEFAULT NULL,
  `customer_email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `finished_queues`
--

INSERT INTO `finished_queues` (`finished_id`, `original_queue_id`, `office_id`, `window_id`, `processed_by`, `finished_at`, `created_at`, `status`, `services`, `customer_name`, `customer_phone`, `customer_email`) VALUES
(8, 27, 2, 4, 'badong', '2025-11-03 22:13:17', '2025-10-30 11:06:04', 'finished', 'Order of Payment', NULL, NULL, NULL),
(9, 28, 2, 4, 'badong', '2025-11-03 22:13:21', '2025-10-30 11:06:04', 'finished', 'Order of Payment', NULL, NULL, NULL),
(10, 29, 2, 4, 'badong', '2025-11-03 22:13:50', '2025-10-30 11:08:26', 'finished', 'Order of Payment', NULL, NULL, NULL),
(11, 32, 2, 4, 'badong', '2025-11-03 22:13:52', '2025-10-30 11:13:45', 'finished', 'T.O.R', NULL, NULL, NULL),
(12, 33, 2, 4, 'badong', '2025-11-03 22:13:55', '2025-10-30 11:13:52', 'finished', 'Order of Payment', NULL, NULL, NULL),
(13, 36, 2, 4, 'badong', '2025-11-03 22:13:57', '2025-11-01 04:35:33', 'finished', 'Order of Payment, 1v1 with Sir Aldrin, Payment Failure, T.O.R', NULL, NULL, NULL),
(14, 37, 2, 4, 'badong', '2025-11-03 22:17:15', '2025-11-01 06:22:42', 'finished', 'Payment Failure, Order of Payment', NULL, NULL, NULL),
(15, 57, 2, 4, 'badong', '2025-11-03 22:29:37', '2025-11-03 22:29:01', 'finished', '1v1 with Sir Aldrin (PDF), T.O.R, Payment Failure', 'Jeremy', '09663811477', 'jeremy@email.com'),
(16, 58, 2, 4, 'badong', '2025-11-03 22:29:39', '2025-11-03 22:29:17', 'finished', 'Payment Failure (PDF)', 'jobert', '09123455555', 'salvador.ricardo.bscs2022@gmail.com'),
(17, 60, 2, 4, 'badong', '2025-11-03 23:30:12', '2025-11-03 22:50:04', 'finished', '1v1 with Sir Aldrin, Order of Payment, Payment Failure, T.O.R', 'Ricardo', '09915261467', 'ricksiv0107@gmail.com'),
(18, 61, 2, 4, 'badong', '2025-11-03 23:30:16', '2025-11-03 23:28:52', 'finished', 'Order of Payment', 'Ricardo', '09915261467', 'ricksiv0107@gmail.com'),
(19, 62, 2, 4, 'badong', '2025-11-05 09:07:31', '2025-11-05 08:51:39', 'finished', 'Order of Payment', 'Ricardo', '09915261467', 'ricksiv0107@gmail.com'),
(20, 63, 2, 1, 'Astaff1', '2025-11-05 09:07:34', '2025-11-05 08:52:23', 'finished', 'Payment Failure (PDF), 1v1 with Sir Aldrin (PDF), Order of Payment (PDF), T.O.R (PDF)', 'Ricardo', '09915261467', 'ricksiv0107@gmail.com'),
(21, 64, 2, 4, 'badong', '2025-11-05 13:02:42', '2025-11-05 08:52:58', 'finished', '1v1 with Sir Aldrin', 'Ricardo', '09915261467', 'ricksiv0107@gmail.com'),
(22, 66, 2, 4, 'badong', '2025-11-05 13:09:50', '2025-11-05 13:02:15', 'finished', 'Order of Payment (PDF)', 'Camille Soliman', '09050795313', 'soliman.camille.bscs2022@gmail.com'),
(23, 67, 2, 4, 'badong', '2025-11-05 13:11:23', '2025-11-05 13:10:32', 'finished', 'Order of Payment (PDF)', 'Lawrence', '09474492247', 'bitancurjohnlawrence.bsit@gmail.com'),
(24, 68, 2, 4, 'badong', '2025-11-05 13:12:33', '2025-11-05 13:12:13', 'finished', 'Payment Failure (PDF)', 'John Lexter Balladares', '09276164084', 'armenta.johnlexter.bscs2022@gmail.com'),
(25, 69, 2, 4, 'badong', '2025-11-05 19:57:11', '2025-11-05 19:56:38', 'finished', 'Order of Payment (PDF)', 'Camille Soliman', '09050795313', 'soliman.camille.bscs2022@gmail.com'),
(26, 70, 2, 4, 'badong', '2025-11-05 21:00:08', '2025-11-05 19:57:39', 'finished', 'T.O.R', 'Jojo B.', '09123455555', 'salvador.ricardo.bscs2022@gmail.com'),
(27, 71, 2, 4, 'badong', '2025-11-05 21:06:01', '2025-11-05 21:00:01', 'finished', 'Payment Failure (PDF), Order of Payment (PDF), T.O.R', 'John', '09915261467', 'ricksiv0107@gmail.com'),
(28, 65, 2, 1, 'Astaff1', '2025-11-06 06:26:34', '2025-11-05 09:05:56', 'finished', 'T.O.R (PDF), 1v1 with Sir Aldrin (PDF), Payment Failure (PDF), Order of Payment (PDF)', 'Ricardo', '09915261467', 'ricksiv0107@gmail.com'),
(29, 73, 2, 1, 'Astaff1', '2025-11-06 06:27:44', '2025-11-06 06:26:48', 'finished', 'Order of Payment', 'Camille Soliman', '09050795313', 'soliman.camille.bscs2022@gmail.com'),
(30, 74, 2, 1, 'Astaff1', '2025-11-06 06:27:46', '2025-11-06 06:27:14', 'finished', 'Order of Payment (PDF)', 'Jeremy', '09663811477', 'jeremy@email.com'),
(31, 75, 2, 1, 'Astaff1', '2025-11-06 06:27:49', '2025-11-06 06:27:28', 'finished', '1v1 with Sir Aldrin (PDF), Order of Payment, Payment Failure', 'jobert', '09123455555', 'salvador.ricardo.bscs2022@gmail.com'),
(32, 76, 2, 1, 'Astaff1', '2025-11-06 06:28:38', '2025-11-06 06:28:06', 'finished', 'Payment Failure (PDF)', 'John', '09915261467', 'ricksiv0107@gmail.com'),
(33, 72, 2, 4, 'badong', '2025-11-06 06:51:57', '2025-11-05 21:06:22', 'finished', 'Order of Payment (PDF)', 'John', '09915261467', 'ricksiv0107@gmail.com'),
(34, 78, 2, 4, 'badong', '2025-11-06 06:52:40', '2025-11-06 06:44:31', 'finished', 'T.O.R', 'Jeremy', '09663811477', 'jeremy@email.com'),
(35, 79, 2, 4, 'badong', '2025-11-06 07:33:28', '2025-11-06 06:44:46', 'finished', 'T.O.R', 'John Lexter Balladares', '09276164084', 'armenta.johnlexter.bscs2022@gmail.com'),
(36, 82, 2, 4, 'badong', '2025-11-13 21:57:59', '2025-11-13 20:05:07', 'finished', 'Order of Payment (PDF)', 'Christian Espinoza', '09223394958', 'espinoza.christian.bscs2022@gmail.com'),
(37, 83, 2, 4, 'badong', '2025-11-13 22:36:03', '2025-11-13 22:15:30', 'finished', '1v1 with Sir Aldrin, Payment Failure, Order of Payment (PDF)', 'Ricardo', '09915261467', 'ricksiv0107@gmail.com'),
(38, 84, 2, 4, 'badong', '2025-11-13 22:37:40', '2025-11-13 22:15:50', 'finished', 'Payment Failure (PDF), T.O.R', 'jobert', '09123455555', 'salvador.ricardo.bscs2022@gmail.com'),
(39, 85, 2, 3, 'badong', '2025-11-14 21:24:47', '2025-11-13 22:37:34', 'finished', 'Order of Payment', 'Camille T. Soliman', '09050795313', 'soliman.camille.bscs2022@gmail.com'),
(40, 86, 2, 3, 'badong', '2025-11-14 21:24:53', '2025-11-14 08:08:23', 'finished', 'Order of Payment, Payment Failure', 'Mylene Bayobay', '09942774966', 'bayobaymylene.bsit@gmail.com'),
(41, 87, 2, 3, 'badong', '2025-11-14 21:24:58', '2025-11-14 08:08:34', 'finished', 'T.O.R (PDF)', 'Mylene Bayobay', '09942774966', 'bayobaymylene.bsit@gmail.com'),
(42, 88, 2, 3, 'badong', '2025-11-14 21:25:00', '2025-11-14 08:08:51', 'finished', '1v1 with Sir Aldrin, Payment Failure (PDF)', 'Mylene Bayobay', '09942774966', 'bayobaymylene.bsit@gmail.com'),
(43, 89, 2, 3, 'badong', '2025-11-14 21:25:03', '2025-11-14 20:55:15', 'finished', 'Order of Payment, 1v1 with Sir Aldrin, Payment Failure', 'Aeryhiel John', '09301939121', 'ricksiv0107@gmail.com'),
(44, 90, 2, 3, 'badong', '2025-11-14 21:26:54', '2025-11-14 20:56:04', 'finished', '1v1 with Sir Aldrin (PDF), Order of Payment (PDF)', 'Ricardo', '09301939121', 'ricksiv0107@gmail.com'),
(45, 91, 2, 13, 'badong', '2025-11-15 01:23:17', '2025-11-14 21:26:47', 'finished', 'Order of Payment', 'Ricardo', '09301939121', 'ricksiv0107@gmail.com'),
(46, 93, 2, 13, 'badong', '2025-11-15 01:23:19', '2025-11-15 01:07:15', 'finished', 'Order of Payment', 'John Lexter B. Armenta', '09915261467', 'ricksiv0107@gmail.com'),
(47, 95, 2, 13, 'badong', '2025-11-15 01:25:02', '2025-11-15 01:24:55', 'finished', '1v1 with Sir Aldrin (PDF), Payment Failure (PDF)', 'avc', '09222212132', 'ricksiv0107@gmail.com'),
(48, 94, 8, 7, 'eryel', '2025-11-15 01:44:40', '2025-11-15 01:13:18', 'finished', 'Consultation', 'Camille T. Soliman', '09050795313', 'soliman.camille.bscs2022@gmail.com');

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
(2, 'Accounting', 'ACC', 1),
(8, 'MIS', 'MIS', 1),
(9, 'College Of Law', 'COL', 1),
(10, 'Dean\'s Office', 'DO', 0);

-- --------------------------------------------------------

--
-- Table structure for table `office_windows`
--

CREATE TABLE `office_windows` (
  `window_id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL,
  `window_no` int(11) NOT NULL,
  `assigned_staff_id` int(11) DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `office_windows`
--

INSERT INTO `office_windows` (`window_id`, `office_id`, `window_no`, `assigned_staff_id`, `enabled`, `created_at`) VALUES
(1, 2, 1, 2, 1, '2025-10-30 03:30:48'),
(2, 2, 2, 3, 1, '2025-10-30 03:32:07'),
(7, 8, 1, 8, 1, '2025-11-14 00:33:53'),
(9, 8, 2, 13, 1, '2025-11-15 08:01:40'),
(10, 8, 3, 9, 1, '2025-11-15 08:01:48'),
(11, 8, 4, 15, 1, '2025-11-15 08:01:59'),
(12, 2, 4, 12, 1, '2025-11-15 08:06:46'),
(13, 2, 3, 5, 1, '2025-11-15 08:09:16'),
(14, 1, 1, 17, 1, '2025-11-15 15:03:45'),
(15, 1, 2, 4, 1, '2025-11-15 15:04:33');

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
(92, 'MIS-001', 8, 11, '2025-11-15 00:59:02', 'ongoing', 'Ricardo', '09301939121', 'ricksiv0107@gmail.com'),
(96, 'MIS-003', 8, 9, '2025-11-15 03:01:27', 'ongoing', 'Christian Espinoza', '09926870934', 'espinoza.christian.bscs2022@gmail.com'),
(97, 'ACC-013', 2, 13, '2025-11-15 08:36:16', 'ongoing', 'John Lexter B. Armenta', '09915261467', 'ricksiv0107@gmail.com'),
(98, 'ACC-014', 2, 1, '2025-11-15 08:41:24', 'ongoing', 'John Lexter B. Armenta', '09915261467', 'ricksiv0107@gmail.com'),
(99, 'ACC-015', 2, 2, '2025-11-15 08:41:36', 'ongoing', 'Camille T. Soliman', '09050795313', 'soliman.camille.bscs2022@gmail.com'),
(100, 'ACC-016', 2, 12, '2025-11-15 08:41:55', 'ongoing', 'Ricardo', '09301939121', 'ricksiv0107@gmail.com'),
(101, 'MIS-004', 8, 7, '2025-11-15 08:44:11', 'ongoing', 'Ricardo', '09301939121', 'ricksiv0107@gmail.com'),
(102, 'MIS-005', 8, 10, '2025-11-15 08:44:21', 'ongoing', 'Christian Espinoza', '09223394958', 'espinoza.christian.bscs2022@gmail.com'),
(103, 'MIS-006', 8, 0, '2025-11-15 08:44:34', 'pending', 'Aeryhiel John', '09301939121', 'ricksiv0107@gmail.com'),
(104, 'REG-001', 1, 0, '2025-11-15 08:49:16', 'pending', 'Aeryhiel John', '09301939121', 'ricksiv0107@gmail.com');

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
(1, 1),
(2, 16),
(8, 6);

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
(64, 92, 15, 0, 1),
(69, 96, 15, 1, 1),
(70, 97, 1, 0, 1),
(71, 97, 17, 1, 1),
(72, 97, 12, 0, 1),
(73, 98, 11, 0, 1),
(74, 99, 10, 0, 1),
(75, 100, 1, 0, 1),
(76, 101, 16, 0, 1),
(77, 102, 15, 0, 1),
(78, 103, 16, 0, 1),
(79, 104, 3, 0, 1);

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
(1, 'T.O.R', 2, 1),
(2, 'Diploma', 1, 0),
(3, 'Good Moral', 1, 1),
(4, 'Adding and Changing', 1, 1),
(10, 'Order of Payment', 2, 1),
(11, '1v1 with Sir Aldrin', 2, 1),
(12, 'Payment Failure', 2, 1),
(14, 'Memorandum of Agreem', 9, 1),
(15, 'Consultation', 8, 1),
(16, 'Creation of Faculty ', 8, 1),
(17, 'Order of Payment', 2, 1);

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
  ADD KEY `idx_safe_name` (`safe_name`);

--
-- Indexes for table `finished_queues`
--
ALTER TABLE `finished_queues`
  ADD PRIMARY KEY (`finished_id`),
  ADD KEY `office_id` (`office_id`),
  ADD KEY `original_queue_id` (`original_queue_id`);

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
  MODIFY `user_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `client_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `finished_queues`
--
ALTER TABLE `finished_queues`
  MODIFY `finished_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `offices`
--
ALTER TABLE `offices`
  MODIFY `office_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `office_windows`
--
ALTER TABLE `office_windows`
  MODIFY `window_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `queues`
--
ALTER TABLE `queues`
  MODIFY `queue_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT for table `queue_services`
--
ALTER TABLE `queue_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

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
  ADD CONSTRAINT `fk_qs_queue` FOREIGN KEY (`queue_id`) REFERENCES `queues` (`queue_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_qs_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`);

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `service_office_id` FOREIGN KEY (`office`) REFERENCES `offices` (`office_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
