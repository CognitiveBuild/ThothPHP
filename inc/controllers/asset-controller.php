<?php
final class AssetController extends AbstractController {

    public static function get($method) {

        return AssetController::class . ":{$method}";
    }

    public function getAssets($request, $response, $args) {

        $p = $request->getQueryParams();
        $language = isset($p['language']) ? $p['language'] : LANGUAGE;
        $list = AssetManager::getAssets($language);

        return $this->view->render($response, 'assets.php', [
            'assets' => $list, 
            'language' => $language
        ]);
    }

    public function getAsset($request, $response, $args) {

        $id = isset($args['id']) ? $args['id'] : 0;
        $p = $request->getQueryParams();
        $l = isset($p['language']) ? $p['language'] : LANGUAGE;

        $result = [
            'id' => 0, 
            'name' => '', 
            'description' => '', 
            'idindustry' => 0, 
            'logourl' => '',
            'linkurl' => '',
            'videourl' => '', 
            'language' => $l
        ];

        $industries = CatalogManager::getCatalog(KEY_INDUSTRY, $l);
        $technologies = CatalogManager::getCatalog(KEY_TECHNOLOGY, $l);
        $technologies_applied = array();
        $attachments = array();

        if($id > 0) {
            $result = db::queryFirst('SELECT `*` FROM `asset` WHERE `id` = ? ORDER BY `id` DESC;', $id);
            $technologies_applied = db::query('SELECT `idcatalog` FROM `catalog_to_asset` WHERE `key` = "'.KEY_TECHNOLOGY.'" AND `idasset` = ?;', $id);
            $attachments = db::query('SELECT `*` FROM `asset_to_file` WHERE `idasset` = ?;', $id);
        }

        return $this->view->render($response, 'asset.php', [
            'id' => $id, 
            'asset' => $result, 
            'industries' => $industries, 
            'technologies' => $technologies, 
            'technologies_applied' => $technologies_applied, 
            'attachments' => $attachments, 
            'language' => $l
        ]);
    }

    public function postAsset($request, $response, $args) {

        $id = isset($args['id']) ? $args['id'] : 0;

        $post = $request->getParsedBody();
        $files = $request->getUploadedFiles();

        $images = $files['binary'];
        $technologies = $post['technology'];

        if($id > 0) {
            // update asset
            $result = AssetManager::updateAsset($id, $post['name'], $post['idindustry'], $post['description'], $post['logourl'], $post['videourl'], $post['linkurl'], $post['language']);

            AssetManager::deleteCatalogToAsset($id);
            foreach($technologies as $idcatalog) {
                AssetManager::addCatalogToAsset(KEY_TECHNOLOGY, $id, $idcatalog);
            }

            AssetManager::addFiles($id, $images);
            AssetManager::updateFileIds($id);

            return $response->withStatus(200)->withHeader('Location', "/assets/{$id}");
        }
        // insert asset
        $id = AssetManager::addAsset($post['name'], $post['idindustry'], $post['description'], $post['logourl'], $post['videourl'], $post['linkurl'], $post['language']);

        if($id > 0) {
            AssetManager::deleteCatalogToAsset($id);
            foreach($technologies as $idcatalog) {
                AssetManager::addCatalogToAsset(KEY_TECHNOLOGY, $id, $idcatalog);
            }

            AssetManager::addFiles($id, $images);
            AssetManager::updateFileIds($id);
        }

        return $response->withStatus(200)->withHeader('Location', "/assets");
    }

    public function getCatalog($request, $response, $args) {

        $name = isset($args['name']) ? $args['name'] : KEY_INDUSTRY;
        $p = $request->getQueryParams();
        $language = isset($p['language']) ? $p['language'] : LANGUAGE;

        $result = CatalogManager::getCatalogWithAssetCount($name, $language);

        return $this->view->render($response, 'catalog.php', [
            'catalogs' => $result, 
            'type' => $name, 
            'language' => $language
        ]);
    }
}