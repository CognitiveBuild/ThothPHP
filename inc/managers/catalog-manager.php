<?php
final class CatalogManager {

    public static function getCatalog($key) {

        return db::query("SELECT `id`, `name` FROM `catalog` WHERE `key` = ?;", $key);
    }

    public static function addCatalog($key, $name) {

        return db::insert("INSERT INTO `catalog` (`key`, `name`) VALUES (?,?);", array($key, $name));

    }

    public static function updateCatalog($key, $id, $name) {
        return db::insert("UPDATE `catalog` SET `key` = ?, `name` = ? WHERE `id` = ?;", array($key, $name, $id));
    }

}