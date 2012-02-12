<?php

class nyaml {
    private $lines;

    public function file($fileName) {
        //Parse file
        return $this->string(file_get_contents($fileName)); 
    }

    public function string($nyaml) {
        //Parse string

        //Preprocessing
        $nyaml = preg_replace("/(\n|^)\s*\#.*?\n/", "\n", $nyaml); //Cleaning from one-line comments
        $nyaml = str_replace("\r", "", $nyaml); //Fix new lines
        $nyaml = str_replace("\n\n", "\n", $nyaml); //Cleaning clear lines 
        $nyaml = trim($nyaml,"\n");

        $this->lines = explode("\n", $nyaml);
        //Parsing
        return $this->parse(0);
     }

    private function parse($level) {
        $return=array();
        $linelevel = $level;

        while (count($this->lines)>0) {

            $line = array_shift($this->lines);
            $linelevel = $this->countLevel($line);

            if ($linelevel<$level) { //If next line from root node stop parsing
                array_unshift($this->lines, $line);
                return $return;
            }
            if (substr($line,-1,1)==":") { //If new node
                $key = substr($line, 0, strlen($line)-1);
                $value = $this->parse($linelevel+1);
                $return[trim($key)] = $value;
            } elseif (strpos($line,":")!==false) { //If key-value pair
                list($key, $value) = explode(":", $line, 2);
                $return[trim($key)] = $this->parseValue($value);
            } else { //If just value
                $return[] = trim($line);
            }
        }
        return $return;
    }

    private function parseValue($value) {
        $value = trim($value);
        switch (substr($value,0,1)) {
            case "\"":
                return substr($value,1,(strlen($value)-2));
            break;
            case "'":
                return substr($value,1,(strlen($value)-2));
            break;
            case "[":
                $result = $this->explode(",", substr($value,1,(strlen($value)-2)));
                $result = array_map(array($this, "parseValue"), $result);
                return $result;
            break;
            default:
                return $value;
        }
    }
    
    private function explode($letter, $string) {
        //smart exploding string
        $quotes = array("\"","'","[","]");
        $quote = '';
        $result = array();
        $token = "";
        for ($i = 0; $i<strlen($string); $i++) {
            $symbol = substr($string,$i,1);
            if ((in_array($symbol, $quotes)) && ($quote == '')) {
                if ($symbol == '[') {
                    $symbol = ']';
                }
                $quote = $symbol;
            } elseif ((in_array($symbol, $quotes)) && ($quote == $symbol)) {
                if ($quote == ']') {
                    $token = "[$token]";
                }
                $quote = '';
            } elseif (($symbol == $letter) && ($quote=='')) {
                $result[] = $token;
                $token = '';
            } else {
                $token .= $symbol;
            }
        }
        if ($token!='') {
            $result[] = $token; 
        }
        return $result;
    }

    private function countLevel($string) {
        //Counting level by indenting
        return (strlen($string) - strlen(ltrim($string,"\t ")));
    }
}
