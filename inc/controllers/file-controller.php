<?php
final class FileController extends AbstractController {

  public static function get($method) {

      return FileController::class . ":{$method}";
  }

  public function getFiles($request, $response, $args) {

      $p = $request->getQueryParams();

      $pn = isset($p['p']) ? $p['p'] : '1';
      
      $list = FileManager::getFiles();

      $pager = CommonUtility::getPager($list, 'files');

      return $this->view->render($response, 'files.php', [
          'files' => $pager->getPageData(), 
          'pager' => $pager->links, 
          'pn' => $pn
      ]);
  }

  public function getAsset($request, $response, $args) {

    $id = isset($args['id']) ? $args['id'] : 0;

    $result = [
        'id' => 0, 
        'idasset' => '0', 
        'name' => '', 
        'size' => '',
        'type' => ''
    ];

    if($id > 0) {
        $result = db::queryFirst('SELECT `*` FROM `asset_to_file` WHERE `id` = ?;', $id);
    }

    return $this->view->render($response, 'file.php', [
        'id' => $id, 
        'asset' => $result
    ]);
  }

}