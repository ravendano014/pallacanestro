/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 100414
 Source Host           : localhost:3306
 Source Schema         : indes_goldslide

 Target Server Type    : MySQL
 Target Server Version : 100414
 File Encoding         : 65001

 Date: 22/08/2025 22:03:39
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for basketball_courts
-- ----------------------------
DROP TABLE IF EXISTS `basketball_courts`;
CREATE TABLE `basketball_courts`  (
  `court_id` int(11) NOT NULL AUTO_INCREMENT,
  `court_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `capacity` int(11) NULL DEFAULT NULL,
  `other_details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  PRIMARY KEY (`court_id`) USING BTREE,
  UNIQUE INDEX `uq_court_name`(`court_name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of basketball_courts
-- ----------------------------
INSERT INTO `basketball_courts` VALUES (1, 'CANCHA EQUIPO A', NULL, NULL, NULL);
INSERT INTO `basketball_courts` VALUES (2, 'CANCHA EQUIPO B', NULL, NULL, NULL);
INSERT INTO `basketball_courts` VALUES (3, 'CANCHA EQUIPO C', NULL, NULL, NULL);
INSERT INTO `basketball_courts` VALUES (4, 'CANCHA EQUIPO D', NULL, NULL, NULL);
INSERT INTO `basketball_courts` VALUES (5, 'CANCHA EQUIPO E', NULL, NULL, NULL);
INSERT INTO `basketball_courts` VALUES (6, 'CANCHA EQUIPO F', NULL, NULL, NULL);
INSERT INTO `basketball_courts` VALUES (7, 'CANCHA EQUIPO G', NULL, NULL, NULL);
INSERT INTO `basketball_courts` VALUES (8, 'CANCHA CSJ', NULL, NULL, NULL);

-- ----------------------------
-- Table structure for games
-- ----------------------------
DROP TABLE IF EXISTS `games`;
CREATE TABLE `games`  (
  `game_code` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `game_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `game_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `other_details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  PRIMARY KEY (`game_code`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of games
-- ----------------------------
INSERT INTO `games` VALUES ('BASK01', 'Basketball', NULL, NULL);

-- ----------------------------
-- Table structure for leagues
-- ----------------------------
DROP TABLE IF EXISTS `leagues`;
CREATE TABLE `leagues`  (
  `league_id` int(11) NOT NULL AUTO_INCREMENT,
  `league_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `league_details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  PRIMARY KEY (`league_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of leagues
-- ----------------------------
INSERT INTO `leagues` VALUES (1, 'DEPARTAMENTO', NULL);

-- ----------------------------
-- Table structure for leagues_games
-- ----------------------------
DROP TABLE IF EXISTS `leagues_games`;
CREATE TABLE `leagues_games`  (
  `league_id` int(11) NOT NULL,
  `game_code` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`league_id`, `game_code`) USING BTREE,
  INDEX `fk_lg_game`(`game_code`) USING BTREE,
  CONSTRAINT `fk_lg_game` FOREIGN KEY (`game_code`) REFERENCES `games` (`game_code`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_lg_league` FOREIGN KEY (`league_id`) REFERENCES `leagues` (`league_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of leagues_games
-- ----------------------------

-- ----------------------------
-- Table structure for matches
-- ----------------------------
DROP TABLE IF EXISTS `matches`;
CREATE TABLE `matches`  (
  `match_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `game_code` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `player_1_id` int(11) NOT NULL,
  `player_2_id` int(11) NOT NULL,
  `court_id` int(11) NOT NULL,
  `match_date` datetime(0) NOT NULL,
  `result` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `other_details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  PRIMARY KEY (`match_id`) USING BTREE,
  INDEX `fk_matches_p1`(`player_1_id`) USING BTREE,
  INDEX `fk_matches_p2`(`player_2_id`) USING BTREE,
  INDEX `idx_matches_game_date`(`game_code`, `match_date`) USING BTREE,
  INDEX `idx_matches_court_date`(`court_id`, `match_date`) USING BTREE,
  CONSTRAINT `fk_matches_court` FOREIGN KEY (`court_id`) REFERENCES `basketball_courts` (`court_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_matches_game` FOREIGN KEY (`game_code`) REFERENCES `games` (`game_code`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_matches_p1` FOREIGN KEY (`player_1_id`) REFERENCES `players` (`player_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_matches_p2` FOREIGN KEY (`player_2_id`) REFERENCES `players` (`player_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of matches
-- ----------------------------

-- ----------------------------
-- Table structure for players
-- ----------------------------
DROP TABLE IF EXISTS `players`;
CREATE TABLE `players`  (
  `player_id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `last_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `gender` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `other_details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  PRIMARY KEY (`player_id`) USING BTREE,
  UNIQUE INDEX `uq_players_name`(`first_name`, `last_name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of players
-- ----------------------------
INSERT INTO `players` VALUES (1, 'System', 'User', NULL, NULL, NULL);

-- ----------------------------
-- Table structure for players_game_ranking
-- ----------------------------
DROP TABLE IF EXISTS `players_game_ranking`;
CREATE TABLE `players_game_ranking`  (
  `player_id` int(11) NOT NULL,
  `game_code` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ranking` int(11) NOT NULL,
  PRIMARY KEY (`player_id`, `game_code`) USING BTREE,
  INDEX `fk_pgr_game`(`game_code`) USING BTREE,
  CONSTRAINT `fk_pgr_game` FOREIGN KEY (`game_code`) REFERENCES `games` (`game_code`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_pgr_player` FOREIGN KEY (`player_id`) REFERENCES `players` (`player_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of players_game_ranking
-- ----------------------------

-- ----------------------------
-- Table structure for team_matches
-- ----------------------------
DROP TABLE IF EXISTS `team_matches`;
CREATE TABLE `team_matches`  (
  `match_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tournament_id` int(11) NOT NULL,
  `jornada` int(11) NOT NULL,
  `juego` int(11) NOT NULL,
  `phase` enum('IDA','VUELTA') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `start_datetime` datetime(0) NULL DEFAULT NULL,
  `home_team_id` int(11) NULL DEFAULT NULL,
  `away_team_id` int(11) NULL DEFAULT NULL,
  `home_score` int(11) NULL DEFAULT NULL,
  `away_score` int(11) NULL DEFAULT NULL,
  `is_bye` tinyint(1) NOT NULL DEFAULT 0,
  `bye_team_id` int(11) NULL DEFAULT NULL,
  `court_id` int(11) NOT NULL,
  `status` enum('SCHEDULED','PLAYED','WALKOVER','POSTPONED','CANCELLED') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'SCHEDULED',
  `walkover_winner` enum('HOME','AWAY') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  PRIMARY KEY (`match_id`) USING BTREE,
  UNIQUE INDEX `uq_tmatch`(`tournament_id`, `jornada`, `juego`) USING BTREE,
  INDEX `idx_tmatch_round`(`tournament_id`, `jornada`, `juego`) USING BTREE,
  INDEX `idx_tmatch_date`(`tournament_id`, `start_datetime`) USING BTREE,
  INDEX `fk_tm_home`(`home_team_id`) USING BTREE,
  INDEX `fk_tm_away`(`away_team_id`) USING BTREE,
  INDEX `fk_tm_bye`(`bye_team_id`) USING BTREE,
  INDEX `fk_tm_court`(`court_id`) USING BTREE,
  CONSTRAINT `fk_tm_away` FOREIGN KEY (`away_team_id`) REFERENCES `teams` (`team_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_tm_bye` FOREIGN KEY (`bye_team_id`) REFERENCES `teams` (`team_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_tm_court` FOREIGN KEY (`court_id`) REFERENCES `basketball_courts` (`court_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_tm_home` FOREIGN KEY (`home_team_id`) REFERENCES `teams` (`team_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_tm_tournament` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`tournament_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 57 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of team_matches
-- ----------------------------
INSERT INTO `team_matches` VALUES (1, 1, 1, 1, 'IDA', NULL, 1, 5, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (2, 1, 1, 2, 'IDA', NULL, 6, 4, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (3, 1, 1, 3, 'IDA', NULL, 7, 3, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (4, 1, 1, 0, 'IDA', NULL, NULL, NULL, NULL, NULL, 1, 2, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (5, 1, 2, 4, 'IDA', NULL, 2, 7, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (6, 1, 2, 5, 'IDA', NULL, 3, 6, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (7, 1, 2, 6, 'IDA', NULL, 4, 5, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (8, 1, 2, 0, 'IDA', NULL, NULL, NULL, NULL, NULL, 1, 1, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (9, 1, 3, 7, 'IDA', NULL, 1, 4, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (10, 1, 3, 8, 'IDA', NULL, 5, 3, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (11, 1, 3, 9, 'IDA', NULL, 6, 2, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (12, 1, 3, 0, 'IDA', NULL, NULL, NULL, NULL, NULL, 1, 7, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (13, 1, 4, 10, 'IDA', NULL, 7, 1, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (14, 1, 4, 11, 'IDA', NULL, 2, 5, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (15, 1, 4, 12, 'IDA', NULL, 3, 4, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (16, 1, 4, 0, 'IDA', NULL, NULL, NULL, NULL, NULL, 1, 6, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (17, 1, 5, 13, 'IDA', NULL, 1, 3, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (18, 1, 5, 14, 'IDA', NULL, 4, 2, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (19, 1, 5, 15, 'IDA', NULL, 6, 7, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (20, 1, 5, 0, 'IDA', NULL, NULL, NULL, NULL, NULL, 1, 5, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (21, 1, 6, 16, 'IDA', NULL, 6, 1, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (22, 1, 6, 17, 'IDA', NULL, 7, 5, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (23, 1, 6, 18, 'IDA', NULL, 2, 3, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (24, 1, 6, 0, 'IDA', NULL, NULL, NULL, NULL, NULL, 1, 4, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (25, 1, 7, 19, 'IDA', NULL, 1, 2, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (26, 1, 7, 20, 'IDA', NULL, 4, 7, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (27, 1, 7, 21, 'IDA', NULL, 5, 6, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (28, 1, 7, 0, 'IDA', NULL, NULL, NULL, NULL, NULL, 1, 3, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (29, 1, 8, 22, 'VUELTA', NULL, 5, 1, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (30, 1, 8, 23, 'VUELTA', NULL, 4, 6, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (31, 1, 8, 24, 'VUELTA', NULL, 3, 7, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (32, 1, 8, 0, 'VUELTA', NULL, NULL, NULL, NULL, NULL, 1, 2, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (33, 1, 9, 25, 'VUELTA', NULL, 7, 2, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (34, 1, 9, 26, 'VUELTA', NULL, 6, 3, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (35, 1, 9, 27, 'VUELTA', NULL, 5, 4, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (36, 1, 9, 0, 'VUELTA', NULL, NULL, NULL, NULL, NULL, 1, 1, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (37, 1, 10, 28, 'VUELTA', NULL, 4, 1, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (38, 1, 10, 29, 'VUELTA', NULL, 3, 5, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (39, 1, 10, 30, 'VUELTA', NULL, 2, 6, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (40, 1, 10, 0, 'VUELTA', NULL, NULL, NULL, NULL, NULL, 1, 7, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (41, 1, 11, 31, 'VUELTA', NULL, 1, 7, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (42, 1, 11, 32, 'VUELTA', NULL, 5, 2, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (43, 1, 11, 33, 'VUELTA', NULL, 4, 3, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (44, 1, 11, 0, 'VUELTA', NULL, NULL, NULL, NULL, NULL, 1, 6, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (45, 1, 12, 34, 'VUELTA', NULL, 3, 1, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (46, 1, 12, 35, 'VUELTA', NULL, 2, 4, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (47, 1, 12, 36, 'VUELTA', NULL, 7, 6, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (48, 1, 12, 0, 'VUELTA', NULL, NULL, NULL, NULL, NULL, 1, 5, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (49, 1, 13, 37, 'VUELTA', NULL, 1, 6, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (50, 1, 13, 38, 'VUELTA', NULL, 5, 7, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (51, 1, 13, 39, 'VUELTA', NULL, 3, 2, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (52, 1, 13, 0, 'VUELTA', NULL, NULL, NULL, NULL, NULL, 1, 4, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (53, 1, 14, 40, 'VUELTA', NULL, 2, 1, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (54, 1, 14, 41, 'VUELTA', NULL, 7, 4, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (55, 1, 14, 42, 'VUELTA', NULL, 6, 5, NULL, NULL, 0, NULL, 8, 'SCHEDULED', NULL, NULL);
INSERT INTO `team_matches` VALUES (56, 1, 14, 0, 'VUELTA', NULL, NULL, NULL, NULL, NULL, 1, 3, 8, 'SCHEDULED', NULL, NULL);

-- ----------------------------
-- Table structure for team_players
-- ----------------------------
DROP TABLE IF EXISTS `team_players`;
CREATE TABLE `team_players`  (
  `team_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `date_from` date NOT NULL,
  `date_to` date NULL DEFAULT NULL,
  `other_details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  PRIMARY KEY (`team_id`, `player_id`, `date_from`) USING BTREE,
  INDEX `idx_tp_player`(`player_id`) USING BTREE,
  CONSTRAINT `fk_tp_player` FOREIGN KEY (`player_id`) REFERENCES `players` (`player_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tp_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`team_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of team_players
-- ----------------------------

-- ----------------------------
-- Table structure for team_tournament_standings
-- ----------------------------
DROP TABLE IF EXISTS `team_tournament_standings`;
CREATE TABLE `team_tournament_standings`  (
  `tournament_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `games_won` int(11) NOT NULL DEFAULT 0,
  `games_drawn` int(11) NOT NULL DEFAULT 0,
  `games_lost` int(11) NOT NULL DEFAULT 0,
  `won_by_default` int(11) NOT NULL DEFAULT 0,
  `lost_by_default` int(11) NOT NULL DEFAULT 0,
  `points` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`tournament_id`, `team_id`) USING BTREE,
  INDEX `fk_tts_team`(`team_id`) USING BTREE,
  CONSTRAINT `fk_tts_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`team_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tts_tournament` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`tournament_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of team_tournament_standings
-- ----------------------------

-- ----------------------------
-- Table structure for teams
-- ----------------------------
DROP TABLE IF EXISTS `teams`;
CREATE TABLE `teams`  (
  `team_id` int(11) NOT NULL AUTO_INCREMENT,
  `created_by_player_id` int(11) NOT NULL,
  `team_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date_created` date NOT NULL,
  `date_disbanded` date NULL DEFAULT NULL,
  `other_details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  PRIMARY KEY (`team_id`) USING BTREE,
  UNIQUE INDEX `uq_team_name`(`team_name`) USING BTREE,
  INDEX `fk_teams_creator`(`created_by_player_id`) USING BTREE,
  CONSTRAINT `fk_teams_creator` FOREIGN KEY (`created_by_player_id`) REFERENCES `players` (`player_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of teams
-- ----------------------------
INSERT INTO `teams` VALUES (1, 1, 'COCODRILOS', '2025-08-21', NULL, NULL);
INSERT INTO `teams` VALUES (2, 1, 'TALENTO HUMANO', '2025-08-21', NULL, NULL);
INSERT INTO `teams` VALUES (3, 1, 'DDTI', '2025-08-21', NULL, NULL);
INSERT INTO `teams` VALUES (4, 1, 'MVP', '2025-08-21', NULL, NULL);
INSERT INTO `teams` VALUES (5, 1, 'CIVIL Y MERCANTIL', '2025-08-21', NULL, NULL);
INSERT INTO `teams` VALUES (6, 1, 'RECICLADOS', '2025-08-21', NULL, NULL);
INSERT INTO `teams` VALUES (7, 1, 'SANTA ANA', '2025-08-21', NULL, NULL);

-- ----------------------------
-- Table structure for tournament_teams
-- ----------------------------
DROP TABLE IF EXISTS `tournament_teams`;
CREATE TABLE `tournament_teams`  (
  `tournament_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `home_court_id` int(11) NULL DEFAULT NULL,
  `seed_number` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`tournament_id`, `team_id`) USING BTREE,
  INDEX `fk_tt_team`(`team_id`) USING BTREE,
  INDEX `fk_tt_court`(`home_court_id`) USING BTREE,
  CONSTRAINT `fk_tt_court` FOREIGN KEY (`home_court_id`) REFERENCES `basketball_courts` (`court_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_tt_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`team_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_tt_tournament` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`tournament_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tournament_teams
-- ----------------------------
INSERT INTO `tournament_teams` VALUES (1, 1, 1, 1);
INSERT INTO `tournament_teams` VALUES (1, 2, 2, 2);
INSERT INTO `tournament_teams` VALUES (1, 3, 3, 3);
INSERT INTO `tournament_teams` VALUES (1, 4, 4, 4);
INSERT INTO `tournament_teams` VALUES (1, 5, 5, 5);
INSERT INTO `tournament_teams` VALUES (1, 6, 6, 6);
INSERT INTO `tournament_teams` VALUES (1, 7, 7, 7);

-- ----------------------------
-- Table structure for tournaments
-- ----------------------------
DROP TABLE IF EXISTS `tournaments`;
CREATE TABLE `tournaments`  (
  `tournament_id` int(11) NOT NULL AUTO_INCREMENT,
  `league_id` int(11) NOT NULL,
  `game_code` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `season_label` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `start_date` date NULL DEFAULT NULL,
  `end_date` date NULL DEFAULT NULL,
  `stage` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `gender` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `sport` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `category` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `win_points` int(11) NOT NULL DEFAULT 2,
  `draw_points` int(11) NOT NULL DEFAULT 0,
  `loss_points` int(11) NOT NULL DEFAULT 1,
  `wo_win_points` int(11) NOT NULL DEFAULT 2,
  `wo_loss_points` int(11) NOT NULL DEFAULT 0,
  `other_details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  PRIMARY KEY (`tournament_id`) USING BTREE,
  UNIQUE INDEX `uq_tournament`(`league_id`, `game_code`, `name`) USING BTREE,
  INDEX `fk_tournaments_game`(`game_code`) USING BTREE,
  CONSTRAINT `fk_tournaments_game` FOREIGN KEY (`game_code`) REFERENCES `games` (`game_code`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_tournaments_league` FOREIGN KEY (`league_id`) REFERENCES `leagues` (`league_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tournaments
-- ----------------------------
INSERT INTO `tournaments` VALUES (1, 1, 'BASK01', 'Torneo Apertura 2026', '2026', NULL, NULL, 'Clasificación de Grupo', 'Masculino', 'Basketball', 'General', 2, 0, 1, 2, 0, NULL);

-- ----------------------------
-- View structure for v_tournament_schedule_phase_headers
-- ----------------------------
DROP VIEW IF EXISTS `v_tournament_schedule_phase_headers`;
CREATE ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW `v_tournament_schedule_phase_headers` AS SELECT
  p.tournament_id,
  p.phase,
  1 AS is_header,
  CASE WHEN p.phase='IDA' THEN 'IDA' ELSE '2da. VUELTA' END AS header_text,
  NULL,NULL,NULL,NULL,NULL,NULL,NULL,
  (CASE WHEN p.phase='IDA' THEN 1 ELSE 2 END)*100000 AS sort_key
FROM (SELECT DISTINCT tournament_id, phase FROM Team_Matches WHERE phase IS NOT NULL) p ;

-- ----------------------------
-- Records of tournaments
-- ----------------------------
INSERT INTO `tournaments` VALUES (1, 'IDA', 1, 'IDA', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 100000);
INSERT INTO `tournaments` VALUES (1, 'VUELTA', 1, '2da. VUELTA', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 200000);

-- ----------------------------
-- View structure for v_tournament_schedule_phase_rows
-- ----------------------------
DROP VIEW IF EXISTS `v_tournament_schedule_phase_rows`;
CREATE ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW `v_tournament_schedule_phase_rows` AS SELECT
  tm.tournament_id,
  tm.phase,
  0 AS is_header,
  NULL AS header_text,
  tm.jornada AS JORNADA,
  tm.juego   AS JUEGO,
  DATE(tm.start_datetime)                 AS FECHA,
  DATE_FORMAT(tm.start_datetime,'%H:%i')  AS HORA,
  CASE WHEN tm.is_bye=1 THEN 'DESCANSA' ELSE th.team_name END AS LOCAL,
  CASE
    WHEN tm.is_bye=1 THEN '-'
    WHEN tm.home_score IS NULL OR tm.away_score IS NULL THEN '-'
    ELSE CONCAT(tm.home_score,' - ',tm.away_score)
  END AS RESULTADO,
  CASE WHEN tm.is_bye=1 THEN ta.team_name ELSE ta.team_name END AS VISITA,
  bc.court_name AS CANCHA,
  (CASE WHEN tm.phase='IDA' THEN 1 ELSE 2 END)*100000 + tm.jornada*100 + tm.juego AS sort_key
FROM Team_Matches tm
LEFT JOIN Teams th ON tm.home_team_id = th.team_id
LEFT JOIN Teams ta ON (tm.is_bye=1 AND tm.bye_team_id=ta.team_id)
                  OR (tm.is_bye=0 AND tm.away_team_id=ta.team_id)
JOIN Basketball_Courts bc ON bc.court_id = tm.court_id ;

-- ----------------------------
-- Records of tournaments
-- ----------------------------
INSERT INTO `tournaments` VALUES (1, 'IDA', 0, NULL, 1, 1, NULL, NULL, 'COCODRILOS', '-', 'CIVIL Y MERCANTIL', 'CANCHA CSJ', 100101);
INSERT INTO `tournaments` VALUES (1, 'IDA', 0, NULL, 1, 2, NULL, NULL, 'RECICLADOS', '-', 'MVP', 'CANCHA CSJ', 100102);
INSERT INTO `tournaments` VALUES (1, 'IDA', 0, NULL, 1, 3, NULL, NULL, 'SANTA ANA', '-', 'DDTI', 'CANCHA CSJ', 100103);
INSERT INTO `tournaments` VALUES (1, 'IDA', 0, NULL, 1, 0, NULL, NULL, 'DESCANSA', '-', 'TALENTO HUMANO', 'CANCHA CSJ', 100100);
INSERT INTO `tournaments` VALUES (1, 'IDA', 0, NULL, 2, 4, NULL, NULL, 'TALENTO HUMANO', '-', 'SANTA ANA', 'CANCHA CSJ', 100204);
INSERT INTO `tournaments` VALUES (1, 'IDA', 0, NULL, 2, 5, NULL, NULL, 'DDTI', '-', 'RECICLADOS', 'CANCHA CSJ', 100205);
INSERT INTO `tournaments` VALUES (1, 'IDA', 0, NULL, 2, 6, NULL, NULL, 'MVP', '-', 'CIVIL Y MERCANTIL', 'CANCHA CSJ', 100206);
INSERT INTO `tournaments` VALUES (1, 'IDA', 0, NULL, 2, 0, NULL, NULL, 'DESCANSA', '-', 'COCODRILOS', 'CANCHA CSJ', 100200);
INSERT INTO `tournaments` VALUES (1, 'IDA', 0, NULL, 3, 7, NULL, NULL, 'COCODRILOS', '-', 'MVP', 'CANCHA CSJ', 100307);
INSERT INTO `tournaments` VALUES (1, 'IDA', 0, NULL, 3, 8, NULL, NULL, 'CIVIL Y MERCANTIL', '-', 'DDTI', 'CANCHA CSJ', 100308);
INSERT INTO `tournaments` VALUES (1, 'IDA', 0, NULL, 3, 9, NULL, NULL, 'RECICLADOS', '-', 'TALENTO HUMANO', 'CANCHA CSJ', 100309);
INSERT INTO `tournaments` VALUES (1, 'IDA', 0, NULL, 3, 0, NULL, NULL, 'DESCANSA', '-', 'SANTA ANA', 'CANCHA CSJ', 100300);
INSERT INTO `tournaments` VALUES (1, 'IDA', 0, NULL, 4, 10, NULL, NULL, 'SANTA ANA', '-', 'COCODRILOS', 'CANCHA CSJ', 100410);
INSERT INTO `tournaments` VALUES (1, 'IDA', 0, NULL, 4, 11, NULL, NULL, 'TALENTO HUMANO', '-', 'CIVIL Y MERCANTIL', 'CANCHA CSJ', 100411);
INSERT INTO `tournaments` VALUES (1, 'IDA', 0, NULL, 4, 12, NULL, NULL, 'DDTI', '-', 'MVP', 'CANCHA CSJ', 100412);
INSERT INTO `tournaments` VALUES (1, 'IDA', 0, NULL, 4, 0, NULL, NULL, 'DESCANSA', '-', 'RECICLADOS', 'CANCHA CSJ', 100400);
INSERT INTO `tournaments` VALUES (1, 'IDA', 0, NULL, 5, 13, NULL, NULL, 'COCODRILOS', '-', 'DDTI', 'CANCHA CSJ', 100513);
INSERT INTO `tournaments` VALUES (1, 'IDA', 0, NULL, 5, 14, NULL, NULL, 'MVP', '-', 'TALENTO HUMANO', 'CANCHA CSJ', 100514);
INSERT INTO `tournaments` VALUES (1, 'IDA', 0, NULL, 5, 15, NULL, NULL, 'RECICLADOS', '-', 'SANTA ANA', 'CANCHA CSJ', 100515);
INSERT INTO `tournaments` VALUES (1, 'IDA', 0, NULL, 5, 0, NULL, NULL, 'DESCANSA', '-', 'CIVIL Y MERCANTIL', 'CANCHA CSJ', 100500);
INSERT INTO `tournaments` VALUES (1, 'IDA', 0, NULL, 6, 16, NULL, NULL, 'RECICLADOS', '-', 'COCODRILOS', 'CANCHA CSJ', 100616);
INSERT INTO `tournaments` VALUES (1, 'IDA', 0, NULL, 6, 17, NULL, NULL, 'SANTA ANA', '-', 'CIVIL Y MERCANTIL', 'CANCHA CSJ', 100617);
INSERT INTO `tournaments` VALUES (1, 'IDA', 0, NULL, 6, 18, NULL, NULL, 'TALENTO HUMANO', '-', 'DDTI', 'CANCHA CSJ', 100618);
INSERT INTO `tournaments` VALUES (1, 'IDA', 0, NULL, 6, 0, NULL, NULL, 'DESCANSA', '-', 'MVP', 'CANCHA CSJ', 100600);
INSERT INTO `tournaments` VALUES (1, 'IDA', 0, NULL, 7, 19, NULL, NULL, 'COCODRILOS', '-', 'TALENTO HUMANO', 'CANCHA CSJ', 100719);
INSERT INTO `tournaments` VALUES (1, 'IDA', 0, NULL, 7, 20, NULL, NULL, 'MVP', '-', 'SANTA ANA', 'CANCHA CSJ', 100720);
INSERT INTO `tournaments` VALUES (1, 'IDA', 0, NULL, 7, 21, NULL, NULL, 'CIVIL Y MERCANTIL', '-', 'RECICLADOS', 'CANCHA CSJ', 100721);
INSERT INTO `tournaments` VALUES (1, 'IDA', 0, NULL, 7, 0, NULL, NULL, 'DESCANSA', '-', 'DDTI', 'CANCHA CSJ', 100700);
INSERT INTO `tournaments` VALUES (1, 'VUELTA', 0, NULL, 8, 22, NULL, NULL, 'CIVIL Y MERCANTIL', '-', 'COCODRILOS', 'CANCHA CSJ', 200822);
INSERT INTO `tournaments` VALUES (1, 'VUELTA', 0, NULL, 8, 23, NULL, NULL, 'MVP', '-', 'RECICLADOS', 'CANCHA CSJ', 200823);
INSERT INTO `tournaments` VALUES (1, 'VUELTA', 0, NULL, 8, 24, NULL, NULL, 'DDTI', '-', 'SANTA ANA', 'CANCHA CSJ', 200824);
INSERT INTO `tournaments` VALUES (1, 'VUELTA', 0, NULL, 8, 0, NULL, NULL, 'DESCANSA', '-', 'TALENTO HUMANO', 'CANCHA CSJ', 200800);
INSERT INTO `tournaments` VALUES (1, 'VUELTA', 0, NULL, 9, 25, NULL, NULL, 'SANTA ANA', '-', 'TALENTO HUMANO', 'CANCHA CSJ', 200925);
INSERT INTO `tournaments` VALUES (1, 'VUELTA', 0, NULL, 9, 26, NULL, NULL, 'RECICLADOS', '-', 'DDTI', 'CANCHA CSJ', 200926);
INSERT INTO `tournaments` VALUES (1, 'VUELTA', 0, NULL, 9, 27, NULL, NULL, 'CIVIL Y MERCANTIL', '-', 'MVP', 'CANCHA CSJ', 200927);
INSERT INTO `tournaments` VALUES (1, 'VUELTA', 0, NULL, 9, 0, NULL, NULL, 'DESCANSA', '-', 'COCODRILOS', 'CANCHA CSJ', 200900);
INSERT INTO `tournaments` VALUES (1, 'VUELTA', 0, NULL, 10, 28, NULL, NULL, 'MVP', '-', 'COCODRILOS', 'CANCHA CSJ', 201028);
INSERT INTO `tournaments` VALUES (1, 'VUELTA', 0, NULL, 10, 29, NULL, NULL, 'DDTI', '-', 'CIVIL Y MERCANTIL', 'CANCHA CSJ', 201029);
INSERT INTO `tournaments` VALUES (1, 'VUELTA', 0, NULL, 10, 30, NULL, NULL, 'TALENTO HUMANO', '-', 'RECICLADOS', 'CANCHA CSJ', 201030);
INSERT INTO `tournaments` VALUES (1, 'VUELTA', 0, NULL, 10, 0, NULL, NULL, 'DESCANSA', '-', 'SANTA ANA', 'CANCHA CSJ', 201000);
INSERT INTO `tournaments` VALUES (1, 'VUELTA', 0, NULL, 11, 31, NULL, NULL, 'COCODRILOS', '-', 'SANTA ANA', 'CANCHA CSJ', 201131);
INSERT INTO `tournaments` VALUES (1, 'VUELTA', 0, NULL, 11, 32, NULL, NULL, 'CIVIL Y MERCANTIL', '-', 'TALENTO HUMANO', 'CANCHA CSJ', 201132);
INSERT INTO `tournaments` VALUES (1, 'VUELTA', 0, NULL, 11, 33, NULL, NULL, 'MVP', '-', 'DDTI', 'CANCHA CSJ', 201133);
INSERT INTO `tournaments` VALUES (1, 'VUELTA', 0, NULL, 11, 0, NULL, NULL, 'DESCANSA', '-', 'RECICLADOS', 'CANCHA CSJ', 201100);
INSERT INTO `tournaments` VALUES (1, 'VUELTA', 0, NULL, 12, 34, NULL, NULL, 'DDTI', '-', 'COCODRILOS', 'CANCHA CSJ', 201234);
INSERT INTO `tournaments` VALUES (1, 'VUELTA', 0, NULL, 12, 35, NULL, NULL, 'TALENTO HUMANO', '-', 'MVP', 'CANCHA CSJ', 201235);
INSERT INTO `tournaments` VALUES (1, 'VUELTA', 0, NULL, 12, 36, NULL, NULL, 'SANTA ANA', '-', 'RECICLADOS', 'CANCHA CSJ', 201236);
INSERT INTO `tournaments` VALUES (1, 'VUELTA', 0, NULL, 12, 0, NULL, NULL, 'DESCANSA', '-', 'CIVIL Y MERCANTIL', 'CANCHA CSJ', 201200);
INSERT INTO `tournaments` VALUES (1, 'VUELTA', 0, NULL, 13, 37, NULL, NULL, 'COCODRILOS', '-', 'RECICLADOS', 'CANCHA CSJ', 201337);
INSERT INTO `tournaments` VALUES (1, 'VUELTA', 0, NULL, 13, 38, NULL, NULL, 'CIVIL Y MERCANTIL', '-', 'SANTA ANA', 'CANCHA CSJ', 201338);
INSERT INTO `tournaments` VALUES (1, 'VUELTA', 0, NULL, 13, 39, NULL, NULL, 'DDTI', '-', 'TALENTO HUMANO', 'CANCHA CSJ', 201339);
INSERT INTO `tournaments` VALUES (1, 'VUELTA', 0, NULL, 13, 0, NULL, NULL, 'DESCANSA', '-', 'MVP', 'CANCHA CSJ', 201300);
INSERT INTO `tournaments` VALUES (1, 'VUELTA', 0, NULL, 14, 40, NULL, NULL, 'TALENTO HUMANO', '-', 'COCODRILOS', 'CANCHA CSJ', 201440);
INSERT INTO `tournaments` VALUES (1, 'VUELTA', 0, NULL, 14, 41, NULL, NULL, 'SANTA ANA', '-', 'MVP', 'CANCHA CSJ', 201441);
INSERT INTO `tournaments` VALUES (1, 'VUELTA', 0, NULL, 14, 42, NULL, NULL, 'RECICLADOS', '-', 'CIVIL Y MERCANTIL', 'CANCHA CSJ', 201442);
INSERT INTO `tournaments` VALUES (1, 'VUELTA', 0, NULL, 14, 0, NULL, NULL, 'DESCANSA', '-', 'DDTI', 'CANCHA CSJ', 201400);

SET FOREIGN_KEY_CHECKS = 1;
