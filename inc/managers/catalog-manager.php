<?php
final class CatalogManager {

    public static function getCatalog($key) {

        return db::query("SELECT `id`, `name` FROM `catalog` WHERE `key` = ?;", $key);
    }

    public static function addCatalog($name, $key) {

        return db::insert("INSERT INTO `catalog` (`name`, `key`) VALUES (?,?);", array($name, $key));

    }

    public static function updateCatalog($key, $id, $name) {
        return db::execute("UPDATE `catalog` SET `key` = ?, `name` = ? WHERE `id` = ?;", array($key, $name, $id));
    }

}