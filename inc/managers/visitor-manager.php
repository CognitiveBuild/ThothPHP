<?php

final class VisitorManager {

    public static function getVisitors() {

        return db::query("SELECT * FROM `visitor`;");
    }

    public static function getVisitorsByCompanyId($idcompany) {

        return db::query("SELECT `id`, `firstname`, `lastname`, `idcompany`, `linkedin`, `facebook`, `twitter` FROM `visitor` WHERE `idcompany` = ?;", $idcompany);
    }

    public static function getVisitor($id) {

        return db::queryFirst("SELECT * FROM `visitor` WHERE `id` = ?", $id);
    }

    public static function addVisitor($firstname, $lastname, $idcompany, $linkedin, $facebook, $twitter) {

        return db::insert("INSERT INTO `visitor` (`firstname`, `lastname`, `idcompany`, `linkedin`, `facebook`, `twitter`) VALUES (?,?,?,?,?,?);", 
        array($firstname, $lastname, $idcompany, $linkedin, $facebook, $twitter));
    }

    public static function updateVisitor($id, $firstname, $lastname, $idcompany, $linkedin, $facebook, $twitter) {

        return db::execute("UPDATE `visitor` SET `firstname` = ?, `lastname` = ?, `idcompany` = ?, `linkedin` = ?, `facebook` = ?, `twitter` = ? WHERE `id` = ?;", 
            array($firstname, $lastname, $idcompany, $linkedin, $facebook, $twitter, $id)
        );
    }

    public static function updateAvatar($id, $avatar) {

        return db::execute("UPDATE `visitor` SET `avatar` = ? WHERE `id` = ?;", array($avatar, $id));
    }

    public static function getVisitorsOfToday() {

        return db::query("SELECT `visitor`.`id`, `visitor`.`firstname`, `visitor`.`lastname`, `visitor`.`facebook`, `visitor`.`linkedin`, `visitor`.`twitter`, `company`.`name` AS `company` FROM `visitor`
        LEFT JOIN `company` ON `company`.`id` = `visitor`.`idcompany`
        LEFT JOIN `event` ON `event`.`idcompany` = `company`.`id`
        WHERE `event`.`visitdate` = current_date() AND `event`.`isactive` = ?;", OPTION_YES);
    }

}