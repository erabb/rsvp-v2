-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema rsvp
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema rsvp
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `rsvp` DEFAULT CHARACTER SET utf8 ;
USE `rsvp` ;

-- -----------------------------------------------------
-- Table `rsvp`.`user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `rsvp`.`user` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `bride` VARCHAR(100) NOT NULL,
  `groom` VARCHAR(100) NULL,
  `email` VARCHAR(100) NOT NULL,
  `email2` VARCHAR(100) NULL,
  `address` VARCHAR(255) NULL,
  `address2` VARCHAR(255) NULL,
  `city` VARCHAR(150) NULL,
  `state` VARCHAR(45) NULL,
  `zip` VARCHAR(45) NULL,
  `phone` VARCHAR(45) NULL,
  `password` VARCHAR(100) NULL,
  `modified` DATETIME NOT NULL,
  `created` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `email_UNIQUE` (`email` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `rsvp`.`event`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `rsvp`.`event` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `url` VARCHAR(45) NULL,
  `venue` VARCHAR(45) NULL,
  `address` VARCHAR(45) NULL,
  `city` VARCHAR(45) NULL,
  `state` VARCHAR(45) NULL,
  `zip` VARCHAR(45) NULL,
  `event_date` DATETIME NULL,
  `pic` VARCHAR(45) NULL,
  `notes` LONGTEXT NULL,
  `event_type` VARCHAR(45) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_event_user_idx` (`user_id` ASC),
  CONSTRAINT `fk_event_user`
    FOREIGN KEY (`user_id`)
    REFERENCES `rsvp`.`user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `rsvp`.`question`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `rsvp`.`question` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id` INT UNSIGNED NOT NULL,
  `question` VARCHAR(45) NULL,
  `values` VARCHAR(45) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_question_event1_idx` (`event_id` ASC),
  CONSTRAINT `fk_question_event1`
    FOREIGN KEY (`event_id`)
    REFERENCES `rsvp`.`event` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `rsvp`.`guest`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `rsvp`.`guest` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id` INT UNSIGNED NOT NULL,
  `first_name` VARCHAR(45) NULL,
  `last_name` VARCHAR(45) NULL,
  `isComing` TINYINT(1) NULL,
  `allowedGuest` TINYINT(1) NULL,
  `email` VARCHAR(150) NULL,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `note` LONGTEXT NULL,
  `code` VARCHAR(45) NULL,
  `isPlusGuest` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `fk_guest_event1_idx` (`event_id` ASC),
  CONSTRAINT `fk_guest_event1`
    FOREIGN KEY (`event_id`)
    REFERENCES `rsvp`.`event` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `rsvp`.`answer`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `rsvp`.`answer` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `question_id` INT UNSIGNED NOT NULL,
  `guest_id` INT UNSIGNED NOT NULL,
  `value` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_answer_question1_idx` (`question_id` ASC),
  INDEX `fk_answer_guest1_idx` (`guest_id` ASC),
  CONSTRAINT `fk_answer_question1`
    FOREIGN KEY (`question_id`)
    REFERENCES `rsvp`.`question` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_answer_guest1`
    FOREIGN KEY (`guest_id`)
    REFERENCES `rsvp`.`guest` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `rsvp`.`guest_to_guestplus`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `rsvp`.`guest_to_guestplus` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `guest_id` INT UNSIGNED NOT NULL,
  `guestplus_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_guest_to_guestplus_guest1_idx` (`guest_id` ASC),
  CONSTRAINT `fk_guest_to_guestplus_guest1`
    FOREIGN KEY (`guest_id`)
    REFERENCES `rsvp`.`guest` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
