<?php

require_once('config.inc.php');

class db extends mysqli {

    public function __construct() {
        parent::__construct(DB_HOST, DB_USER, DB_PASS);
        if ($this->connect_errno) {
            $this->fail('could not connect to database server');
        }
        $this->query("SET NAMES 'utf8' COLLATE 'utf8_unicode_ci'");
        if (!$this->select_db(DB_BASE)) {
            $this->create_db();
        }
        if (false) { // TODO: check if tables exist
            create_tables();
        }
    }

    public function query($query) {
        if (!($result = parent::query($query))) {
            if (DEBUG) {
                $this->fail($this->error);
            } else {
                $this->fail('invalid SQL query syntax');
            }
        }
        return $result;
    }

    public function protect($str) {
        return $this->escape_string(htmlspecialchars($str));
    }

    private function create_db() {
        $this->query("CREATE DATABASE `".DB_BASE."` CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci'");
        $this->select_db(DB_BASE);
    }
    
    private function create_tables() {
        /*
        This table holds the user data of all the students who have access.
        id:   unique user id used to identify user during their session
        name: user name, e.g. 'jdoe'
        pwd:  sha256-hashed password
        priv: privilege level
                0 - no rights whatsoever (useful for suspending accounts)
                1 - view all data except for teacher names, default (students)
                2 - view all data (teachers)
                3 - view all data, and modify entries (Mrs. Lange I)
                4 - view all data, modify entries, and add new users (root)
         */
        $this->query("CREATE TABLE `user` (
            `id`   INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(20)  NOT NULL,
            `pwd`  CHAR(64)     NOT NULL,
            `priv` INT UNSIGNED NOT NULL DEFAULT 1
        )");

        /*
        This table holds the timetable changes (including the good stuff such as cancelled classes...)
        id:       unique entry id used to identify an entry during modification
        time:     timestamp of the day and time the class would normally start (e.g. Friday, July 13th)
        teacher:  name of the absent teacher (e.g. Mr. Doe)
        subject:  name and type of the course or subject (e.g. Ma-LK)
        duration: duration of this class in minutes (e.g. 75) (TODO: old or new duration?)
        class:    name of the class (e.g. '9.3')
        oldroom:  room the class was supposed to take place in originally (e.g. H2-3)
        sub:      name of the substitute teacher (e.g. 'Fr. Musterfrau')
        change:   what class takes place [where] instead (e.g. 'Geschichte H0-2' or 'Ausfall')
        */
        $this->query("CREATE TABLE `entry` (
            `id`       INT UNSIGNED      NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `time`     TIMESTAMP         NULL     DEFAULT NULL,
            `teacher`  VARCHAR(30)       NULL     DEFAULT NULL,
            `subject`  VARCHAR(20)       NULL     DEFAULT NULL,
            `duration` SHORTINT UNSIGNED NULL     DEFAULT NULL,
            `class`    VARCHAR(3)        NULL     DEFAULT NULL,
            `oldroom`  VARCHAR(5)        NULL     DEFAULT NULL,
            `sub`      VARCHAR(30)       NULL     DEFAULT NULL,
            `change`   VARCHAR(50)       NULL     DEFAULT NULL
        )");
        $this->query("INSERT INTO `user` VALUES (NULL, 'admin', '".ADMIN_PWD."', '4')");
    }

    private function fail($msg) {
        die('ERROR: '.$msg);
    }
}
?>