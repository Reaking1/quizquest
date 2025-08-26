-- Create and select database
CREATE DATABASE IF NOT EXISTS quizquest
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE quizquest;

-- Players
CREATE TABLE IF NOT EXISTS players (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Quizzes (a quiz has themed rounds)
CREATE TABLE IF NOT EXISTS quizzes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(160) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Rounds in a quiz
CREATE TABLE IF NOT EXISTS rounds (
  id INT AUTO_INCREMENT PRIMARY KEY,
  quiz_id INT NOT NULL,
  name VARCHAR(160) NOT NULL,
  theme VARCHAR(160),
  time_limit_sec INT NOT NULL DEFAULT 30,
  order_no INT NOT NULL DEFAULT 1,
  CONSTRAINT fk_round_quiz FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
  INDEX (quiz_id, order_no)
) ENGINE=InnoDB;

-- Questions (MCQ or OPEN)
CREATE TABLE IF NOT EXISTS questions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  text TEXT NOT NULL,
  type ENUM('MCQ','OPEN') NOT NULL,
  difficulty ENUM('EASY','MEDIUM','HARD') NOT NULL,
  option_a VARCHAR(255),
  option_b VARCHAR(255),
  option_c VARCHAR(255),
  option_d VARCHAR(255),
  correct_answer VARCHAR(255),   -- used for MCQ (A/B/C/D or exact text)
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Map questions to rounds (and order in round)
CREATE TABLE IF NOT EXISTS round_questions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  round_id INT NOT NULL,
  question_id INT NOT NULL,
  order_no INT NOT NULL DEFAULT 1,
  CONSTRAINT fk_rq_round    FOREIGN KEY (round_id)   REFERENCES rounds(id)    ON DELETE CASCADE,
  CONSTRAINT fk_rq_question FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
  UNIQUE KEY uq_round_question (round_id, question_id)
) ENGINE=InnoDB;

-- Player attempts at a specific question in a round
-- (round_id & question_id can be NULL for bonus/adjustments)
CREATE TABLE IF NOT EXISTS attempts (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  player_id INT NOT NULL,
  quiz_id INT NOT NULL,
  round_id INT NULL,
  question_id INT NULL,
  answer_text TEXT,
  is_correct TINYINT(1) NOT NULL DEFAULT 0,
  points INT NOT NULL DEFAULT 0,
  answered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_att_player   FOREIGN KEY (player_id)  REFERENCES players(id)  ON DELETE CASCADE,
  CONSTRAINT fk_att_quiz     FOREIGN KEY (quiz_id)    REFERENCES quizzes(id)  ON DELETE CASCADE,
  CONSTRAINT fk_att_round    FOREIGN KEY (round_id)   REFERENCES rounds(id)   ON DELETE CASCADE,
  CONSTRAINT fk_att_question FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
  INDEX (player_id, quiz_id),
  INDEX (question_id)
) ENGINE=InnoDB;

-- Aggregated scores per player per quiz
CREATE TABLE IF NOT EXISTS scores (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  player_id INT NOT NULL,
  quiz_id INT NOT NULL,
  total_points INT NOT NULL DEFAULT 0,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_player_quiz (player_id, quiz_id),
  CONSTRAINT fk_score_player FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE,
  CONSTRAINT fk_score_quiz   FOREIGN KEY (quiz_id)   REFERENCES quizzes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Helpful view for leaderboard
CREATE OR REPLACE VIEW v_leaderboard AS
SELECT s.quiz_id,
       q.title        AS quiz_title,
       s.player_id,
       p.name         AS player_name,
       s.total_points
FROM scores s
JOIN players p ON p.id = s.player_id
JOIN quizzes q ON q.id = s.quiz_id;
