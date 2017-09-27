<?php 

final class UserManager {

    public static function getUsers() {

        return db::query("SELECT * FROM `user`;");
    }

    public static function getUserById($id) {

        return db::queryFirst("SELECT * FROM `user` WHERE `id` = ?", [$id]);
    }

    public static function getUserByLogin($login) {

        return db::queryFirst("SELECT * FROM `user` WHERE `login` = ?;", [$login]);
    }

    public static function generatePasscode($encryptedPasscode, $newToken, $time, $login) {

        return db::update("UPDATE `user` SET `passcode` = ?, `token` = ?, `activetime` = ? WHERE `login` = ?", [$encryptedPasscode, $newToken, $time, $login]);
    }

    public static function updateToken($newToken, $time, $login) {

        return db::update("UPDATE `user` SET `token` = ?, `activetime` = ? WHERE `login` = ?", [$newToken, $time, $login]);
    }

    public static function updateSettings($language, $id) {

        return db::execute("UPDATE `user` SET `language` = ? WHERE `login` = ?", [$language, $id]);
    }

    public static function addUser($display, $login, $idrole, $passcode, $language) {

        $time = time();
        $newToken = SessionManager::generateToken($login, $passcode, $time);
        $encryptedPasscode = SessionManager::generateToken($login, $passcode);

        return db::insert("INSERT INTO `user` (`idrole`, `login`, `display`, `passcode`, `token`, `activetime`, `language`) VALUES (?,?,?,?,?,?,?);", 
            [$idrole, $login, $display, $encryptedPasscode, $newToken, $time, $language]
        );
    }

    public static function updateUser($id, $display, $login, $idrole, $passcode, $language) {

        $len = strlen($passcode);

        if($len >= 6) {
            $time = time();
            $newToken = SessionManager::generateToken($login, $passcode, $time);
            $encryptedPasscode = SessionManager::generateToken($login, $passcode);
            return db::execute("UPDATE `user` SET `display` = ?, `login` = ?, `idrole` = ?, `passcode` = ?, `language` = ? WHERE `id` = ?", [$display, $login, $idrole, $passcode, $language, $id]);
        }
        return db::execute("UPDATE `user` SET `display` = ?, `login` = ?, `idrole` = ?, `language` = ? WHERE `id` = ?", [$display, $login, $idrole, $language, $id]);
    }

    public static function getRoles() {

        return db::query("SELECT * FROM `role`");
    }

    public static function getRoleById($id) {

        return db::queryFirst("SELECT * FROM `role` WHERE `id` = ?", [$id]);
    }

    public static function addRole($name, $description) {

        return db::insert("INSERT INTO `role` (`name`, `description`) VALUES (?,?);", [$name, $description]);
    }
    public static function updateRole($id, $name, $description) {

        return db::execute("UPDATE `role` SET `name` = ?, `description` = ? WHERE `id` = ?;", [ $name, $description, $id ]);
    }

    public static function getACLs($idrole) {

        return db::query("SELECT `acl`.*, (SELECT COUNT(`rta`.`idacl`) FROM `role_to_acl` `rta` WHERE `rta`.`idacl` = `acl`.`id` AND `rta`.`idrole` = ?) AS `count` FROM `acl`;", 
            [$idrole]
        );
    }

    public static function getACLsByRoleId($idrole) {

        return db::query("SELECT `acl`.`key` FROM `acl` LEFT JOIN `role_to_acl` `rta` ON `rta`.`idacl` = `acl`.`id` WHERE `rta`.`idrole` = ?", 
            [$idrole]
        );
    }

    public static function removeRoleACL($idrole) {

        return db::execute("DELETE FROM `role_to_acl` WHERE `idrole` = ?", [$idrole]);
    }

    public static function addRoleACL($idrole, $idacl) {

        return db::insert("INSERT INTO `role_to_acl` (`idrole`, `idacl`) VALUES (?,?);", [$idrole, $idacl]);
    }
}