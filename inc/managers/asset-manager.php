<?php

class AssetManager {

    public static function getTable($name) {
        return db::query("SELECT * FROM `{$name}`;");
    }
    public static function getAssets() {

        return db::query('SELECT `*` FROM `asset`;');
    }

    public static function getAssetsByCatalogName($key, $name) {

        $result = array();
        if($key === KEY_INDUSTRY) {
            $result = db::query('SELECT `asset`.`*` FROM `asset` 
                JOIN `catalog` ON `catalog`.`id` = `asset`.`idindustry`
                WHERE `catalog`.`key` = ? AND `catalog`.`name` LIKE ?;', 
                array($key, $name)
            );
        }
        else {
            $result =  db::query('SELECT `asset`.`*` FROM `asset` 
                JOIN `catalog_to_asset` ON `catalog_to_asset`.`idasset` = `asset`.`id` 
                JOIN `catalog` ON `catalog`.`id` = `catalog_to_asset`.`idcatalog` 
                WHERE `catalog`.`key` = ? AND `catalog`.`name` LIKE ?;', 
                array($key, $name)
            );
        }

        // todo: to avoid addtional query, maybe store ids in master table
        $result = self::fetchAttachments($result);

        return $result;
    }

    private static function fetchAttachments($result) {
        if(count($result) > 0) {
            foreach($result as $key => $val) {
                $query = self::getFileIDList($val['id']);
                foreach($query as $q => $v) {
                    $query[$q]['url'] = "/api/v1/assets/attachment/{$v['id']}";
                }
                $result[$key]['attachments'] = $query;
            }
        }
        return $result;
    }


    public static function getAssetsByCatalogId($key, $id) {

        $result = array();
        if($key === KEY_INDUSTRY) {
            $result = db::query('SELECT `asset`.`*` FROM `asset` WHERE `asset`.`idindustry` = ?', array($id));
        }
        else {
            $result =  db::query('SELECT `asset`.`*` FROM `asset` 
                JOIN `catalog_to_asset` ON `catalog_to_asset`.`idasset` = `asset`.`id` 
                JOIN `catalog` ON `catalog`.`id` = `catalog_to_asset`.`idcatalog` 
                WHERE `catalog`.`key` = ? AND `catalog`.`id` = ?;', 
                array($key, $id)
            );
        }

        // todo: to avoid addtional query, maybe store ids in master table
        $result = self::fetchAttachments($result);

        return $result;
    }

    public static function addAsset($name, $idindustry, $description, $logourl, $videourl, $linkurl) {

        return db::insert(
            "INSERT INTO `asset` (`name`, `idindustry`, `description`, `logourl`, `videourl`, `linkurl`) VALUES (?,?,?,?,?,?);", 
            array($name, $idindustry, $description, $logourl, $videourl, $linkurl)
        );
    }

    public static function updateAsset($id, $name, $idindustry, $description, $logourl, $videourl, $linkurl) {

        return db::update(
            "UPDATE `asset` SET `name` = ?, `idindustry` = ?, `description` = ?, `logourl` = ?, `videourl` = ?, `linkurl` = ? WHERE `id` = ?;", 
            array($name, $idindustry, $description, $logourl, $videourl, $linkurl, $id)
        );
    }

    public static function deleteAsset($id) {

        return db::execute('DELETE FROM `asset` WHERE `id` = ?;', $id);
    }

    public static function getCatalog($key) {

        return db::query("SELECT `id`, `name` FROM `catalog` WHERE `key` = ?;", $key);
    }

    public static function deleteCatalog($id) {

        return db::execute("DELETE FROM `catalog_to_asset` WHERE `idasset` = ?;", $id);
    }

    public static function addCatalog($key, $idasset, $idcatalog) {

        return db::insert(
            "INSERT INTO `catalog_to_asset` (`key`, `idasset`, `idcatalog`) VALUES (?,?,?);", 
            array($key, $idasset, $idcatalog)
        );
    }

    public static function getFileIDList($id) {

        return db::query("SELECT `id` FROM `asset_to_file` WHERE `idasset` = ?", $id);
    }

    public static function readFile($id) {

        return db::queryFirst("SELECT `*` FROM `asset_to_file` WHERE `id` = ?", $id);
    }

    public static function addFiles($id, $files) {

        foreach($files as $file) {
            $size = $file->getSize();
            if($size == 0) continue;

            $binary = file_get_contents($file->file);
            $name = $file->getClientFilename();
            $type = $file->getClientMediaType();

            db::insert(
                "INSERT INTO `asset_to_file` (`idasset`, `binary`, `name`, `size`, `type`) VALUES (?,?,?,?,?);", 
                array($id, $binary, $name, $size, $type)
            );

        }
    }


    public static function deleteFile($id) {

        return db::execute("DELETE FROM `asset_to_file` WHERE `id` = ?", $id);
    }

}