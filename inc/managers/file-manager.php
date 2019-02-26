<?php
final class FileManager {

  public static function getFiles() {

    return db::query("SELECT `id`, `name`, ROUND(`size`/1024) AS size, `type`, `idasset` FROM `asset_to_file` ORDER BY `id` DESC;");
  }

}