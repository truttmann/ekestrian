<?php

/**
* function whhich clean data and return it explode by space
* @param  string $data
* @param  string $codeLangue code de la langue (fr, en, ...)
* @return mixed
*/
function nettoyageData($data, $codeLangue)
{
    static $stopwords = null;

    if (is_null($stopwords)) {
        $stopwords = include __DIR__ . '/../../../../../config/stopwords.php';
    }

    $olddata = $data;
    $data = trim($data);

    if(!empty($data)) {
        // unspecialize
        $data = html_entity_decode(preg_replace('#&(.)(grave|acute|circ|cedil|uml|lig|uro);#', '\1', htmlentities($data, ENT_NOQUOTES, 'UTF-8')));
        // remplacement de tous les caracteres non alphanum en espace
        $data = trim(preg_replace('/[^a-zA-Z0-9]+/', ' ', $data));
        // lower case
        $data = strtolower($data);
    }

    $temp = explode(' ', $data);
    $mottraite = array();
    // suppression des tous les mot ayant une seule lettre
    for ($i=0; $i < count($temp); $i++) { 
        $temp[$i] = trim($temp[$i]);
        if(strlen($temp[$i]) == 1) {
            /* on ne fait rien */
        } elseif(array_key_exists($codeLangue, $stopwords) && in_array(strtolower($temp[$i]), $stopwords[$codeLangue])) {
            /* on ne fait rien */
        } else {
            if(strlen($temp[$i]) > 3) {
                $let = substr($temp[$i], -1);
                if(/*$let == "s" ||*/ $let == "x") {
                    $temp[$i] = substr($temp[$i], 0, -1);
                }
            }
            if(! in_array($temp[$i], $mottraite)) {
                $mottraite[] = $temp[$i];
            }
        }
    }
    // On renvoi null si aucun résultat
    if (empty($mottraite[0])) {
        return null;
    }
    return $mottraite;
}

function vksprintf($str, $args)
{
    if (is_object($args)) {
        $args = get_object_vars($args);
    }
    $map = array_flip(array_keys($args));
    $new_str = preg_replace_callback('/(^|[^%])%([a-zA-Z0-9_\.-]+)\$/',
        function($m) use ($map) { return $m[1].'%'.($map[$m[2]] + 1).'$'; },
        $str);
    return vsprintf($new_str, $args);
}


/**
 * Tranforme un tableau en une phrase séparée par $separate et $last pour le dernier élément
 * du tableau
 * @param $array
 * @param string $separate [optional]
 * @param string $last [optional]
 * @return string
 */
function transformArrayToSentence($array, $separate = ', ', $last = ' et ')
{
    $str = '';
    if (count($array)) {
        foreach ($array as $key => $row) {
            if ($key < (count($array) - 1) && $key != 0) {
                $str .= $separate;
            } else if ($key != 0) {
                $str .= $last;
            }
            $str .= $row;
        }
    }
    return $str;
}