<?php

final class EventManager {

    public static function getEvents() {

        return db::query("SELECT * FROM `event` ORDER BY `visitdate` DESC;");
    }

    public static function getEvent($id) {

        return db::queryFirst("SELECT * FROM `event` WHERE `id` = ?", $id);
    }

    public static function addEvent($visitdate, $displayas, $idcompany, $isactive) {

        return db::insert("INSERT INTO `event` (`visitdate`, `displayas`, `idcompany`, `isactive`) VALUES (?,?,?,?);", 
        array($visitdate, $displayas, $idcompany, $isactive));
    }

    public static function updateEvent($id, $visitdate, $displayas, $idcompany, $isactive) {

        return db::execute("UPDATE `event` SET `visitdate` = ?, `displayas` = ?, `idcompany` = ?, `isactive` = ? WHERE `id` = ?;", 
            array($visitdate, $displayas, $idcompany, $isactive, $id)
        );
    }

    public static function getVisitorsByEventId($id) {

        return db::query("SELECT * FROM `event_to_visitor` WHERE `idevent` = ?;", $id);
    }

    public static function addVisitorByEventId($id, $idvisitor) {

        return db::insert("INSERT INTO `event_to_visitor` (`idevent`, `idvisitor`) VALUES (?,?);", array($id, $idvisitor));
    }

    public static function delteVisitorByEventId($id) {

        return db::insert("DELETE FROM `event_to_visitor` WHERE `idevent` = ?;", array($id));
    }

    public static function getTimelinesByEventId($id) {

        return db::query("SELECT * FROM `event_timeline` WHERE `idevent` = ?;", $id);
    }

}