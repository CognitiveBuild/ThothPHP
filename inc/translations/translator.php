<?php
final class Translator {

    public function translate($var = '', $args = NULL, $language = LANGUAGE) {

        return translate($var, $args, $language);
    }
}