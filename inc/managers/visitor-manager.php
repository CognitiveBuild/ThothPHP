<?php

final class VisitorManager {

    public static function getVisitors() {

        return db::query("SELECT * FROM `visitor`;");
    }

    public static function getVisitorsByCompanyId($idcompany) {
        return db::query("SELECT * FROM `visitor` WHERE `idcompany` = ?;", $idcompany);
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

}