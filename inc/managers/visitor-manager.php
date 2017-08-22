<?php

final class VisitorManager {

    public static function getVisitors() {

        return db::query("SELECT `visitor`.`*`, `company`.`name` AS `companyname` FROM `visitor` 
        LEFT JOIN `company` ON `company`.`id` = `visitor`.`idcompany` ORDER BY `visitor`.`order` ASC;");
    }

    public static function getVisitorsForEvent() {

        return db::query("SELECT `visitor`.`id`, `visitor`.`firstname`, `visitor`.`lastname`, `visitor`.`idcompany`, `visitor`.`website`, `visitor`.`linkedin`, `visitor`.`facebook`, `visitor`.`twitter`, `visitor`.`order`, `company`.`name` AS `company` 
        FROM `visitor` 
        LEFT JOIN `company` ON `company`.`id` = `visitor`.`idcompany`
        ORDER BY `company`.`name` ASC, `visitor`.`order` ASC;");
    }

    public static function getVisitor($id) {

        return db::queryFirst("SELECT * FROM `visitor` WHERE `id` = ?", $id);
    }

    public static function addVisitor($firstname, $lastname, $idcompany, $website, $linkedin, $facebook, $twitter, $order = 0) {

        return db::insert("INSERT INTO `visitor` (`firstname`, `lastname`, `idcompany`, `website`, `linkedin`, `facebook`, `twitter`, `order`) VALUES (?,?,?,?,?,?,?,?);", 
        array($firstname, $lastname, $idcompany, $website, $linkedin, $facebook, $twitter, $order));
    }

    public static function updateVisitor($id, $firstname, $lastname, $idcompany, $website, $linkedin, $facebook, $twitter, $order = 0) {

        return db::execute("UPDATE `visitor` SET `firstname` = ?, `lastname` = ?, `idcompany` = ?, `website` = ?, `linkedin` = ?, `facebook` = ?, `twitter` = ?, `order` = ? WHERE `id` = ?;", 
            array($firstname, $lastname, $idcompany, $website, $linkedin, $facebook, $twitter, $order, $id)
        );
    }

    public static function updateAvatar($id, $avatar) {

        return db::execute("UPDATE `visitor` SET `avatar` = ? WHERE `id` = ?;", array($avatar, $id));
    }

    public static function getVisitorsOfToday() {

        return db::query("SELECT `visitor`.`id`, `visitor`.`idcompany`, `visitor`.`firstname`, `visitor`.`lastname`, `visitor`.`facebook`, `visitor`.`website`, `visitor`.`linkedin`, `visitor`.`twitter`, `company`.`name` AS `company` 
        FROM `event`
        LEFT JOIN `event_to_visitor` ON `event_to_visitor`.`idevent` = `event`.`id` 
        LEFT JOIN `visitor` ON `visitor`.`id` = `event_to_visitor`.`idvisitor`
        LEFT JOIN `company` ON `company`.`id` = `visitor`.`idcompany`
        WHERE `event`.`visitdate` = current_date() 
        AND `event_to_visitor`.`idvisitor` = `visitor`.`id`
        AND `event`.`isactive` = ? ORDER BY `visitor`.`order` ASC;", OPTION_YES);
    }

}