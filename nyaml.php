<?php

namespace NXP;

class nyaml
{
    /**
     * Состояния
     */
    const PARSE_KEY = 0;
    const PARSE_VALUE = 1;
    const PARSE_INDENT = 2;
    const PARSE_KEY_IN_QUOTE = 3;
    const PARSE_KEY_IN_DQUOTE = 4;
    const PARSE_VALUE_IN_QUOTE = 5;
    const PARSE_VALUE_IN_DQUOTE = 6;

    /**
     * Термы
     */
    const T_SPACE = 0;
    const T_NEWLINE = 1;
    const T_CHAR = 2;
    const T_QUOTE = 3;
    const T_DQUOTE = 4;

    private $state = 0, $result = array();
    /**
     * Термы
     * @var array
     */
    private $terms = array(
        self::T_SPACE => '/[\s|\t]/',
        self::T_NEWLINE => '/[\n]/',
        self::T_CHAR => '/[a-zA-Zа-яА-Я0-9]/',
        self::T_QUOTE => '/[\']/',
        self::T_DQUOTE => '/[\"]/'
    );

    /**
     * Таблица переходов состояния
     * @var array
     */
    private $actions = array(
        array(self::PARSE_KEY,             self::T_CHAR,      self::PARSE_KEY),
        array(self::PARSE_KEY,             self::T_SPACE,     self::PARSE_VALUE),
        array(self::PARSE_KEY,             self::T_NEWLINE,   self::PARSE_VALUE),
        array(self::PARSE_VALUE,           self::T_NEWLINE,   self::PARSE_KEY),
        array(self::PARSE_VALUE,           self::T_SPACE,     self::PARSE_VALUE),
        array(self::PARSE_VALUE,           self::T_CHAR,      self::PARSE_VALUE),
        array(self::PARSE_KEY,             self::T_QUOTE,     self::PARSE_KEY_IN_QUOTE),
        array(self::PARSE_KEY,             self::T_DQUOTE,    self::PARSE_KEY_IN_DQUOTE),
        array(self::PARSE_VALUE,           self::T_QUOTE,     self::PARSE_VALUE_IN_QUOTE),
        array(self::PARSE_VALUE,           self::T_DQUOTE,    self::PARSE_VALUE_IN_DQUOTE),
        array(self::PARSE_KEY_IN_QUOTE,    self::T_QUOTE,     self::PARSE_KEY),
        array(self::PARSE_KEY_IN_DQUOTE,   self::T_DQUOTE,    self::PARSE_KEY),
        array(self::PARSE_VALUE_IN_QUOTE,  self::T_QUOTE,     self::PARSE_VALUE),
        array(self::PARSE_VALUE_IN_DQUOTE, self::T_DQUOTE,    self::PARSE_VALUE),
        array(self::PARSE_KEY_IN_QUOTE,    self::T_CHAR,      self::PARSE_KEY_IN_QUOTE),
        array(self::PARSE_KEY_IN_DQUOTE,   self::T_CHAR,      self::PARSE_KEY_IN_DQUOTE),
        array(self::PARSE_VALUE_IN_QUOTE,  self::T_CHAR,      self::PARSE_VALUE_IN_QUOTE),
        array(self::PARSE_VALUE_IN_DQUOTE, self::T_CHAR,      self::PARSE_VALUE_IN_DQUOTE),
        array(self::PARSE_KEY_IN_QUOTE,    self::T_SPACE,     self::PARSE_KEY_IN_QUOTE),
        array(self::PARSE_KEY_IN_DQUOTE,   self::T_SPACE,     self::PARSE_KEY_IN_DQUOTE),
        array(self::PARSE_VALUE_IN_QUOTE,  self::T_SPACE,     self::PARSE_VALUE_IN_QUOTE),
        array(self::PARSE_VALUE_IN_DQUOTE, self::T_SPACE,     self::PARSE_VALUE_IN_DQUOTE),
        array(self::PARSE_KEY_IN_QUOTE,    self::T_NEWLINE,   self::PARSE_KEY_IN_QUOTE),
        array(self::PARSE_KEY_IN_DQUOTE,   self::T_NEWLINE,   self::PARSE_KEY_IN_DQUOTE),
        array(self::PARSE_VALUE_IN_QUOTE,  self::T_NEWLINE,   self::PARSE_VALUE_IN_QUOTE),
        array(self::PARSE_VALUE_IN_DQUOTE, self::T_NEWLINE,   self::PARSE_VALUE_IN_DQUOTE),
    );

    public function file($fileName)
    {
        //Parse file
        return $this->string(file_get_contents($fileName));
    }

    public function string($nyaml)
    {
        $result = array();
        $key = '';
        $value = '';
        for ($i = 0; $i < strlen($nyaml); $i++) {

            $char = substr($nyaml, $i, 1);
            $oldState = $this->state;
            $newState = $this->getState($char);
            $this->state = $newState;
            if (($newState == self::PARSE_KEY) && ($oldState == self::PARSE_VALUE)) {
                $result[$key] = $value;
                $key = '';
                $value = '';

            }
            if (($newState == self::PARSE_KEY) && ($oldState == self::PARSE_KEY) && ($oldState != self::PARSE_VALUE_IN_QUOTE) && ($oldState != self::PARSE_VALUE_IN_DQUOTE)) {
                $key.=$char;
            }
            if ( ($newState == self::PARSE_VALUE)  && ($oldState != self::PARSE_VALUE_IN_QUOTE) && ($oldState != self::PARSE_VALUE_IN_DQUOTE) ) {
                $value.=$char;
            }

            if (($newState == self::PARSE_KEY_IN_QUOTE) && ($oldState != self::PARSE_KEY)) {
                $key.=$char;
            }
            if (($newState == self::PARSE_KEY_IN_DQUOTE) && ($oldState != self::PARSE_KEY)) {
                $key.=$char;
            }

            if (($newState == self::PARSE_VALUE_IN_QUOTE) && ($oldState != self::PARSE_VALUE)) {
                $value.=$char;
            }
            if (($newState == self::PARSE_VALUE_IN_DQUOTE) && ($oldState != self::PARSE_VALUE)) {
                $value.=$char;
            }
        }

        $result[$key] = $value;
        return $result;
    }

    public function getState($char)
    {
        $currentTerm = null;
        foreach ($this->terms as $key => $term) {
            if (preg_match($term, $char)) {
                $currentTerm = $key;
            }
        }
        if ($currentTerm === null) {
            throw new Exception('Parse error');
        }
        foreach ($this->actions as $action) {
            if (( $action[0] == $this->state ) && ( $action[1] == $currentTerm )) {

                return $action[2];
            }
        }
    }

}
