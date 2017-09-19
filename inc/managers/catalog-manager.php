<?php
final class CatalogManager {

    public static function getCatalog($key, $language = DEFAULT_LANGUAGE) {

        return db::query("SELECT `id`, `name`, `language` FROM `catalog` WHERE `key` = ? AND `language` = ?;", [$key, $language]);
    }

    public static function getCatalogWithAssetCount($key, $language = DEFAULT_LANGUAGE) {

        $countSQL = "(SELECT COUNT(`id`) FROM `asset` WHERE `asset`.`idindustry` = `catalog`.`id`)";
        if($key === KEY_TECHNOLOGY) {
            $countSQL = "(SELECT COUNT(`catalog_to_asset`.`idasset`) FROM `catalog_to_asset` WHERE `catalog`.`id` = `catalog_to_asset`.`idcatalog`)";        
        }
        
        $sql = "SELECT `id`, `name`, `language`, {$countSQL} AS `count` FROM `catalog` WHERE `key` = ? AND `language` = ?;";

        return db::query($sql, [$key, $language]);
    }

    public static function addCatalog($name, $key, $language) {

        return db::insert("INSERT INTO `catalog` (`name`, `key`, `language`) VALUES (?,?,?);", array($name, $key, $language));

    }

    public static function updateCatalog($key, $id, $name, $language) {
        return db::execute("UPDATE `catalog` SET `key` = ?, `name` = ?, `language` = ? WHERE `id` = ?;", array($key, $name, $language, $id));
    }

}