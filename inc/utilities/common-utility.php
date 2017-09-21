<?php
use GuzzleHttp\Psr7\Stream;

final class CommonUtility {

    const PAGER_PER_PAGE = 30;
    public static $SUPPORTED_LANGUAGES = [ 'English (United States)' => 'en-us', '中文 (简体)' => 'zh-cn' ];

    public static function getBaseUrl($path = '', $encode = FALSE) {

        $host = HOST_NAME;
        $url = "https://{$host}{$path}";

        if($encode) {
            return urlencode($url);
        }
        return $url;
    }

    public static function createStream($resource) {

        return new Stream($resource);
    }

    public static function getServerVar($key = NULL, $default = NULL) {

        if($key === NULL) {
            return $_SERVER;
        }
        if(isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }
        return $default;
    }

    public static function setEnvVar($key, $val) {

        // Apache environment variable exists, overwrite it
        if (function_exists('apache_getenv') && function_exists('apache_setenv') && apache_getenv($key)) {
            apache_setenv($key, $val);
        }

        if (function_exists('putenv')) {
            putenv("$key=$val");
        }

        $_ENV[$key] = $val;
    }

    /**
	 * Run-time resource - Get pager
	 * @param array $data
	 * @param int $page
	 * @return Pager_Common
	 */
	public function getPager($data, $page = self::PAGER_PER_PAGE, $urlvar = 'p') {

		$params = array(
		    'perPage'    => $page,
		    'urlVar'     => $urlvar,
			'curPageLinkClassName'=>'current',
		    'itemData'   => $data,
			'altFirst' => self::getTranslation('First page'),
			'altPrev' => self::getTranslation('Previous page'),
			'altNext' => self::getTranslation('Next page'),
			'altLast' => self::getTranslation('Last page'),
			'altPage' => self::getTranslation('Page'),
			'prevImg' => self::getTranslation('&lt;'),
			'nextImg' => self::getTranslation('&gt;'),

			'firstLinkTitle' => self::getTranslation('First page'),
			'nextLinkTitle' => self::getTranslation('Next page'),
			'prevLinkTitle' => self::getTranslation('Previous page'),
			'lastLinkTitle' => self::getTranslation('Last page'),
		);

		unset($data);

		$pager = &Pager::factory($params);

		return $pager;
    }

    public static function getTranslation($var = '', $args = NULL, $language = LANGUAGE) {

        $list = $_ENV[INSTANCE]['TRANSLATION'][$language];
        if(empty($var))
            return $list;
        return isset($list[$var]) ? vsprintf($list[$var], $args) : vsprintf($var, $args);
    }

    public static function loadTranslation($language) {

        $languagefile = TRANSLATION_DIR."/inc/translations/{$language}.php";

        if(!is_readable($languagefile))
            $languagefile = TRANSLATION_DIR."/inc/translations/en-us.php";

        include($languagefile);
        $_ENV[INSTANCE]['TRANSLATION'][$language] = $lang;

        $languagefile = TRANSLATION_DIR."/inc/translations/{$language}.php";

        if(!is_readable($languagefile))
            $languagefile = TRANSLATION_DIR."/inc/translations/en-us.php";
        if(!is_readable($languagefile)) return;

        include($languagefile);
        $_ENV[INSTANCE]['TRANSLATION'][$language] = array_merge($_ENV[INSTANCE]['TRANSLATION'][$language], $lang);
        unset($lang);
    }

    public static function setLanguage($language = LANGUAGE) {

        setcookie('LANGUAGE', $language, 0, '/');
    }

    public static function getAcceptedLanguage() {

		$ret = 'en-us';

		$languages = '';
        $acceptLanguage = '';

        if(isset($_COOKIE['LANGUAGE'])) {
            $language = $_COOKIE['LANGUAGE'];

            $result = array_search($language, CommonUtility::$SUPPORTED_LANGUAGES);

            if($result !== NULL) {
                $ret = CommonUtility::$SUPPORTED_LANGUAGES[$result];
            }
		}
		else {

			if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
				$acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
			else
				$acceptLanguage = getenv('HTTP_ACCEPT_LANGUAGE');

			$languages = explode(';', isset($acceptLanguage) ? $acceptLanguage : '');
			if(isset($languages[0])) {
				$language = explode(',', $languages[0]);
				if(isset($language[0]))
					$ret = $language[0];
			}
        }

        $ret = strtolower($ret);
        self::setLanguage($ret);

		return $ret;
	}
}