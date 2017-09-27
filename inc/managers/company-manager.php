<?php
final class CompanyManager {

    public static function getCompanies() {

        return db::query("SELECT * FROM `company`");
    }

    public static function getCompany($id) {

        return db::queryFirst("SELECT * FROM `company` WHERE `id` = ?", $id);
    }

    public static function addCompany($name, $idindustry, $description) {

        return db::insert("INSERT INTO `company` (`name`, `idindustry`, `description`) VALUES (?,?,?);", [$name, $idindustry, $description]);
    }

    public static function updateCompany($id, $name, $idindustry, $description) {

        return db::execute("UPDATE `company` SET `name` = ?, `idindustry` = ?, `description` = ? WHERE `id` = ?;", [$name, $idindustry, $description, $id]);
    }

    public static function updateLogo($id, $logo) {

        return db::execute("UPDATE `company` SET `logo` = ? WHERE `id` = ?;", [$logo, $id]);
    }

}