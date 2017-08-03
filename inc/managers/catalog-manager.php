<?php
final class CatalogManager {

    public static function getCatalog($key) {

        return db::query("SELECT `id`, `name` FROM `catalog` WHERE `key` = ?;", $key);
    }

    public static function getCatalogWithAssetCount($key) {

        $countSQL = "(SELECT COUNT(`id`) FROM `asset` WHERE `asset`.`idindustry` = `catalog`.`id`)";
        if($key === KEY_TECHNOLOGY) {
            $countSQL = "(SELECT COUNT(`catalog_to_asset`.`idasset`) FROM `catalog_to_asset` WHERE `catalog`.`id` = `catalog_to_asset`.`idcatalog`)";        
        }
        
        $sql = "SELECT `id`, `name`, {$countSQL} AS `count` FROM `catalog` WHERE `key` = ?;";

        return db::query($sql, $key);
    }

    public static function addCatalog($name, $key) {

        return db::insert("INSERT INTO `catalog` (`name`, `key`) VALUES (?,?);", array($name, $key));

    }

    public static function updateCatalog($key, $id, $name) {
        return db::execute("UPDATE `catalog` SET `key` = ?, `name` = ? WHERE `id` = ?;", array($key, $name, $id));
    }

}