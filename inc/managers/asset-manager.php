<?php

class AssetManager {

    private static $asset_columns = '`asset`.`id`, `asset`.`name`, `asset`.`description`, `asset`.`logoUrl`, `asset`.`linkUrl`, `asset`.`videoUrl`, `asset`.`attachments`';
    public static function getTable($name) {
        return db::query("SELECT * FROM `{$name}`;");
    }
    public static function getAssets() {

        $asset_columns = self::$asset_columns;
        return db::query("SELECT {$asset_columns} FROM `asset`;");
    }

    public static function getAssetsByCatalogName($key, $name) {

        $result = array();
        $asset_columns = self::$asset_columns;
        if($key === KEY_INDUSTRY) {
            $result = db::query("SELECT {$asset_columns} FROM `asset` 
                JOIN `catalog` ON `catalog`.`id` = `asset`.`idindustry`
                WHERE `catalog`.`key` = ? AND `catalog`.`name` LIKE ?;", 
                array($key, $name)
            );
        }
        else {
            $result =  db::query("SELECT {$asset_columns} FROM `asset` 
                JOIN `catalog_to_asset` ON `catalog_to_asset`.`idasset` = `asset`.`id` 
                JOIN `catalog` ON `catalog`.`id` = `catalog_to_asset`.`idcatalog` 
                WHERE `catalog`.`key` = ? AND `catalog`.`name` LIKE ?;", 
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

                $list = array();
                $query = json_decode($val['attachments'], TRUE);

                if(is_array($query)) {
                    foreach($query as $k => $v) {
                        //array_push($list, "/api/v1/assets/attachment/{$v['id']}");
                        array_push($list, $v['id']);
                    }
                }
                $result[$key]['attachments'] = $list;
            }
        }
        return $result;
    }


    public static function getAssetsByCatalogId($key, $id) {

        $result = array();
        $asset_columns = self::$asset_columns;
        if($key === KEY_INDUSTRY) {
            $result = db::query("SELECT {$asset_columns} FROM `asset` WHERE `asset`.`idindustry` = ?", array($id));
        }
        else {
            $result =  db::query("SELECT {$asset_columns} FROM `asset` 
                JOIN `catalog_to_asset` ON `catalog_to_asset`.`idasset` = `asset`.`id` 
                JOIN `catalog` ON `catalog`.`id` = `catalog_to_asset`.`idcatalog` 
                WHERE `catalog`.`key` = ? AND `catalog`.`id` = ?;", 
                array($key, $id)
            );
        }

        // todo: to avoid addtional query, maybe store ids in master table
        $result = self::fetchAttachments($result);

        return $result;
    }

    public static function getAssetsByCompanyId($id) {

        $result = array();
        $asset_columns = self::$asset_columns;

        $result = db::query("SELECT {$asset_columns} FROM `asset` WHERE `videourl` != '' ORDER BY RAND() LIMIT 10 ;"); //, array($id)

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

    public static function deleteCatalogToAsset($id) {

        return db::execute("DELETE FROM `catalog_to_asset` WHERE `idasset` = ?;", $id);
    }

    public static function addCatalogToAsset($key, $idasset, $idcatalog) {

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

    public static function updateFileIds($id) {
        $result = self::getFileIDList($id);
        $data = json_encode($result);
        return db::execute("UPDATE `asset` SET `attachments` = ? WHERE `id` = ?", array($data, $id));
    }


    public static function deleteFile($id) {

        return db::execute("DELETE FROM `asset_to_file` WHERE `id` = ?", $id);
    }

}