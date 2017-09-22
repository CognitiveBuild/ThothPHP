<?php
final class EventController extends AbstractController {

    public static function get($method) {

        return EventController::class . ":{$method}";
    }

    public function getEvents($request, $response, $args) {

        $list = EventManager::getEvents();

        return $this->view->render($response, 'events.php', [
            'events' => $list
        ]);
    }

    public function getEvent($request, $response, $args) {

        $id = isset($args['id']) ? $args['id'] : 0;
        $today = date("Y-m-d");
        $result = [
            'id' => 0, 
            'visitdate' => $today, 
            'idcompany' => 0, 
            'isactive' => 'Y', 
            'displayas' => ''
        ];

        $companies = CompanyManager::getCompanies();

        $timelines = EventManager::getTimelinesByEventId($id);

        if($id > 0) {
            $result = EventManager::getEvent($id);
        }

        return $this->view->render($response, 'event.php', [
            'id' => $id, 
            'event' => $result, 
            'companies' => $companies, 
            'timelines' => $timelines
        ]);
    }

    public function postEvent($request, $response, $args) {

        $id = isset($args['id']) ? $args['id'] : 0;
        $post = $request->getParsedBody();

        $visitors = isset($post['idvisitor']) ? $post['idvisitor'] : array();

        $timestarts = isset($post['timeline_timestart']) ? $post['timeline_timestart'] : array();
        $timeends = isset($post['timeline_timeend']) ? $post['timeline_timeend'] : array(); 
        $activitis = isset($post['timeline_activity']) ? $post['timeline_activity'] : array();
        $isactive = isset($post['isactive']) ? $post['isactive'] : OPTION_NO;

        $isNew = TRUE;

        // Make sure there is no conflict events on same date
        if($isactive === OPTION_YES) {
            EventManager::deactivateEvent();
        }

        if($id > 0) {
            $isNew = FALSE;
            EventManager::updateEvent($id, $post['visitdate'], $post['displayas'], $post['idcompany'], $isactive);
        }
        else {
            $id = EventManager::addEvent($post['visitdate'], $post['displayas'], $post['idcompany'], $isactive);
        }

        EventManager::delteVisitorByEventId($id);
        foreach($visitors as $key => $idvisitor) {
            EventManager::addVisitorByEventId($id, $idvisitor);
        }

        EventManager::deleteTimelineByEventId($id);
        foreach($timestarts as $key => $time_start) {

            $time_start = $timestarts[$key];
            $time_end = $timeends[$key];
            $activity = $activitis[$key];

            EventManager::addTimeline($id, $time_start, $time_end, $activity);
        }

        if($isNew) {
            return $response->withStatus(200)->withHeader('Location', "/events");
        }

        return $response->withStatus(200)->withHeader('Location', "/events/{$id}");
    }

    public function getVisitors ($request, $response, $args) {

        $list = VisitorManager::getVisitors();

        return $this->view->render($response, 'visitors.php', [
            'visitors' => $list
        ]);
    }

    public function getVisitor($request, $response, $args) {

        $id = isset($args['id']) ? $args['id'] : 0;

        $result = [
            'id' => 0, 
            'firstname' => '', 
            'lastname' => '', 
            'idcompany' => 0, 
            'website' => '', 
            'linkedin' => '', 
            'facebook' => '',
            'twitter' => ''
        ];

        $visitor = VisitorManager::getVisitor($id);
        $companies = CompanyManager::getCompanies();

        return $this->view->render($response, 'visitor.php', [
            'id' => $id, 
            'visitor' => $visitor, 
            'companies' => $companies
        ]);
    }

    public function postVisitor($request, $response, $args) {

        $id = isset($args['id']) ? $args['id'] : 0;
        $post = $request->getParsedBody();
        $files = $request->getUploadedFiles();

        $avatar = NULL;
        if(isset($files['avatar']) && count($files['avatar']) === 1) {
            $size = $files['avatar'][0]->getSize();
            if($size > 0) {
                $avatar = file_get_contents($files['avatar'][0]->file);
            }
        }
        $isNew = TRUE;
        if($id > 0) {
            $isNew = FALSE;
            VisitorManager::updateVisitor($id, $post['firstname'], $post['lastname'], $post['idcompany'], $post['website'], $post['linkedin'], $post['facebook'], $post['twitter'], $post['order']);
        }
        else {
            $id = VisitorManager::addVisitor($post['firstname'], $post['lastname'], $post['idcompany'], $post['website'], $post['linkedin'], $post['facebook'], $post['twitter'], $post['order']);
        }

        if($id > 0 && $avatar !== NULL) {
            VisitorManager::updateAvatar($id, $avatar);
        }

        if($isNew) {
            return $response->withStatus(200)->withHeader('Location', "/visitors");
        }

        return $response->withStatus(200)->withHeader('Location', "/visitors/{$id}");
    }

    public function getCompanies($request, $response, $args) {

        $list = CompanyManager::getCompanies();

        return $this->view->render($response, 'companies.php', [
            'companies' => $list
        ]);
    }

    public function getCompany($request, $response, $args) {

        $id = isset($args['id']) ? $args['id'] : 0;
        $p = $request->getQueryParams();
        $l = isset($p['language']) ? $p['language'] : LANGUAGE;
        $industries = CatalogManager::getCatalog(KEY_INDUSTRY, $l);

        $result = array(
            'id' => 0, 
            'name' => '', 
            'description' => '', 
            'logo' => NULL, 
            'idindustry' => 0
        );

        if($id > 0) {
            $result = CompanyManager::getCompany($id);
        }

        return $this->view->render($response, 'company.php', [
            'id' => $id, 
            'company' => $result, 
            'industries' => $industries
        ]);
    }

    public function postCompany($request, $response, $args) {

        $id = isset($args['id']) ? $args['id'] : 0;
        $post = $request->getParsedBody();
        $files = $request->getUploadedFiles();

        $logo = NULL;
        if(isset($files['logo']) && count($files['logo']) === 1) {
            $size = $files['logo'][0]->getSize();
            if($size > 0) {
                $logo = file_get_contents($files['logo'][0]->file);
            }
        }
        $isNew = TRUE;
        if($id > 0) {
            CompanyManager::updateCompany($id, $post['name'], $post['idindustry'], $post['description']);
            $isNew = FALSE;
        }
        else {
            $id = CompanyManager::addCompany($post['name'], $post['idindustry'], $post['description']);
        }

        if($id > 0 && $logo !== NULL) {
            CompanyManager::updateLogo($id, $logo);
        }

        if($isNew) {
            return $response->withStatus(200)->withHeader('Location', "/companies");
        }

        return $response->withStatus(200)->withHeader('Location', "/companies/{$id}");
    }
}