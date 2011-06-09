<?php

require("lib/simplehtmldom.class");
error_reporting(E_ALL);

/* ############################################################################### */

function dict_getword($line) {
    $html = str_get_html($line);
    $element = $html->find('o:hw', 0);
    if($element)
        return strip_tags($element->innertext);

    return false;
}

$translate = array(
    "o:ent" => '<span class="def" style="font-family: Baskerville; ">',

    "o:hw" => '<span d:priority="2" d:dhw="1" class="hw" style="font-size: 24px; ">',
    "o:hsb" => '<span class="hsb" style="font-size: 75%; ">•',
    "o:sb" => '<span class="SB" style="display: block; margin-left: 1em; text-indent: -1em; margin-top: 1em; ">',
    "o:ps" => '<span d:ps="1" class="ps" style="font-weight: normal; ">',
    "o:syntax" => '<span class="syntax" style="font-weight: normal; ">',
    "o:sense" => '<span d:abs="1" class="sense" style="display: block; ">',
    "o:ms" => '<span class="MS" style="display: block; ">',
    
    "o:hm" => '<span class="hm" style="vertical-align: super; ">',

    "o:sc" => '<span class="sc" style="font-variant: small-caps; ">',

    "o:x" => '<span class="x" style="font-weight: 600; font-variant: small-caps; ">',

    "o:inf" => '<span class="inf" style="font-weight: 600; ">',
    "o:sn" => '<span class="sn" style="font-weight: 600; ">',
    "o:v" => '<span class="v" style="font-weight: 600; ">',
    "o:l" => '<span class="l" style="font-weight: 600; ">',
    "o:f" => '<span class="f" style="font-weight: 600; ">',

    "o:bold" => '<span class="bold" style="font-weight: 600; font-style: italic; ">',
    "o:ff" => '<span class="ff" style="font-weight: 600; font-style: italic; ">',
    "o:trans" => '<span class="trans" style="font-weight: 600; font-style: italic; ">',
    
    "o:underline" => '<span class="underline" style="text-decoration: underline; ">',

    "o:def" => '<span class="def" style="font-weight: normal; ">',

    "o:specuse" => '<span d:priority="2" class="specUse" style="display: block; text-indent: 0px; ">',
    
    "o:encblock" => '<span class="encBlock" style="display: block; margin-top: 1em; margin-bottom: 1em; text-indent: 0px; background-color: rgb(238, 238, 238); padding-top: 0.33em; padding-right: 0.33em; padding-bottom: 0.33em; padding-left: 0.33em; margin-right: 1em; ">',

    "o:ex" => '<span d:priority="2" class="ex" style="font-style: italic; ">',

    "o:date" => '<span class="date" style="font-weight: normal; ">',
    "o:lang" => '<span class="lang" style="font-weight: normal; ">'

    /*
     * XXX: the different grps that I've come across all have different translations, but they seem to be fairly reducible.
     * as a result, they are all reduced to <span class="$tag" style="font-weight: normal; "> in dict_translate_tag()
     */
//    "o:prongrp" => '',
//    "o:hwgrp" => '',
//    "o:gramgrp" => '<span d:priority="2" class="gramGrp" style="font-weight: normal; ">',
//    "o:formgrp" => '<span class="formGrp" style="font-weight: normal; ">',
//    "o:vargrp" => '<span class="varGrp" style="font-weight: normal; ">',
//    "o:infgrp" => '<span d:priority="2" class="infGrp">',
);

$subent = 0;
function dict_translate_tag(&$element) {
    global $translate;
    $tag = $element->tag;
    
    if(array_key_exists($tag, $translate))
        $trans = $translate[$tag];

    // some tags have endings that, without even seeing the rest of the tag, we can guess what the markup should look like.
    else if(substr($tag, strlen($tag) - 5) == 'block')
      $trans = '<span d:priority="2" class="'.substr($tag, 2).'" style="display: block; margin-top: 1em; text-indent: 0px; ">';
    else if(substr($tag, strlen($tag) - 3) == 'grp')
      $trans = '<span d:priority="2" class="'.substr($tag, 2).'" style="font-weight: normal; ">';
    else if(substr($tag, strlen($tag) - 5) == 'label')
      $trans = '<span class="'.substr($tag, 2).'" style="font-family: HelveticaNeue-Light; font-size: 13px; ">';
      
    // and some tags are just plain weird and require some extra logic
    else if($tag == "o:pr") {
      if($element->type != "US_IPA")
          $trans = '<span d:pr="'.$element->type.'" type="'.$element->type.'" class="pr" style="font-family: HiraMinPro-W3; display: none; ">';
      else
          $trans = '<span d:pr="'.$element->type.'" type="'.$element->type.'" class="pr" style="font-family: HiraMinPro-W3; ">';
    }
    else if($tag == "o:subent") {
      global $subent;
      $trans = '<span id="index_'.++$subent.'" class="subEnt" style="display: block; ">';
    }
    
    // but the basic case is easy.  we could probably just strip these without issue.
    else
        $trans = '<span class="'.substr($tag, 2).'">';

    $element->outertext = $trans.$element->innertext.'</span>';
    
    return;
}

function dict_dfs(&$element) {
    if(count($element->children())) {
        foreach($element->children() as $child) {
            dict_dfs($child);
        }
    }
    // don't translate tags that aren't dictionary markup
    if(substr($element->tag, 0, 2) == "o:") {
        dict_translate_tag($element);
    }
}

function dict_indent($line, $level) {
    $str = "";
    while($level-- > 0) {
        $str .= "  ";
    }
    return $str.trim($line);
}

function dict_human($str) {
    $str = str_replace(array("<", ">"), array("\n<", ">\n"), $str);
    $str = str_replace("\n\n", "\n", $str);
    $arr = explode("\n", $str);
    $level = 0;
    for($i = 0; $i < count($arr); $i++) {
        if(substr($arr[$i], 0, 2) == "</") {
            $level--;
            $arr[$i] = dict_indent($arr[$i], $level);
        } else if(substr($arr[$i], 0, 1) == "<" && substr($arr[$i], 0, 5) != "<meta" && substr($arr[$i], 0, 2) != "<!") {
            $arr[$i] = dict_indent($arr[$i], $level);
            $level++;
        } else {
            $arr[$i] = dict_indent($arr[$i], $level);
        }
    }
    return implode("\n", $arr);
}

function dict_toHTML($line) {
    $html = str_get_html($line);
    
    // lbl does different things based on the parent tag
    foreach($html->find("o:MS o:lbl") as $element)
        $element->outertext = '<span class="lbl" style="font-family: LucidaGrande; font-size: 13px; ">'.$element->innertext."</span>";
    foreach($html->find("o:ex o:lbl") as $element)
        $element->outertext = '<span class="lbl" style="font-weight: normal; ">'.$element->innertext."</span>";
    foreach($html->find("o:lbl") as $element)
        $element->outertext = '<span class="lbl" style="font-size: 14px; ">'.$element->innertext."</span>";
    
    // image tags get some extra styling
    foreach($html->find("img") as $element) {
        $element->style = 'display: block; width: 100%; margin-top: 1em; ';
        $size = getimagesize($element->src);
        $element->width = $size[0];
    }
    
    // div class="lineart" has some special effects on its contained elements:
    // div within a within div class="lineart" gains some special styling
    foreach($html->find("div.lineart div") as $element)
      $element->style = "display: block; text-align: center; font-weight: 600; font-size: 13px; ";
    // a within div class="lineart" gains some special styling
    foreach($html->find("div.lineart a") as $element)
      $element->style = "color: rgb(41, 113, 167); text-decoration: none; ";
    // div class="lineart" gains some special styling
    foreach($html->find("div.lineart") as $element)
      $element->style = "float: right; padding-top: 1em; padding-right: 0em; padding-bottom: 0.5em; padding-left: 1.5em; background-color: white; max-width: 33%; ";
    
    // force the library to reparse the DOM tree
    $html = str_get_html($html->save());
    
    // other tags are much simpler—translate them bottom-up according to the lookup table
    /*
     * XXX: I do a bottom-up search for o: tags to keep the html dom parser happy—it gets cranky when you change an
     * element and then try to delve into its children.
     */
    foreach($html->find("o:ent") as $element) {
        if(count($element->children())) {
            dict_dfs($element);
        }
        dict_translate_tag($element);
    }
    
    // force the library to reparse the DOM tree
    $html = str_get_html($html->save());
    
    foreach($html->find("span.def") as $element) {
        $first_child = $element->find("span.SB", 0);
        $first_child->style = "display: block; margin-left: 1em; text-indent: -1em; ";
    }
    
    // done!  stringify and do one last wrap.
    $str = $html->save();
    $str = '<div class="def" style="margin-top: 1em; ">'.$str.'</div>';
    return $str;
    
    // for human-readable html (but correspondingly incorrect whitespace) use:
    return dict_human($str);
}

function dict_lookup($word) {
    global $db;
    
    $sql = "SELECT rowid, * FROM words WHERE type = 'dict' AND word LIKE '".str_replace("'", "''", $word)."'";
    $res = $db->query($sql);
    
    $defs = $res->fetchAll();

    if(count($defs) == 0) {
        // no match. try pspell to find match?
        return array();
    }

    // short circuit if there's only one match
    if(count($defs) == 1)
        return $defs;
    
    // order-preserving slow sort
    $ret = array();
    $diff = 0;
    while(count($ret) != count($defs)) {
        for($i = 0; $i < count($defs); $i++)
            if(levenshtein($word, $defs[$i]['word']) == $diff)
                $ret[] = $defs[$i];

        $diff++;
    }

    unset($defs);
    return $ret;
}

// go!
{
    $words = array();
    
    if(count($_GET) > 0)
        foreach($_GET as $name => $blank)
            $words[] = $name;
    
    if(count($words) == 0)
        trigger_error("no word", E_USER_ERROR);
    
    $db = new PDO("sqlite:words.db");
    
    echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"><html><head><meta http-equiv="Content-Type" content="text/html;charset=utf-8" ><title>';
    for($i = 0; $i < count($words); $i++) {
        echo $words[$i];
        if($i < count($words) - 1)
            echo ", ";
    }
    echo '</title></head><body><div class="defs" style="margin: 3em; margin-top: 2em; "><span class="Apple-style-span" style="border-collapse: separate; color: rgb(0, 0, 0); font-family: Times; font-size: medium; font-style: normal; font-variant: normal; font-weight: normal; letter-spacing: normal; line-height: normal; orphans: 2; text-align: auto; text-indent: 0px; text-transform: none; white-space: normal; widows: 2; word-spacing: 0px; -webkit-border-horizontal-spacing: 0px; -webkit-border-vertical-spacing: 0px; -webkit-text-decorations-in-effect: none; -webkit-text-size-adjust: auto; -webkit-text-stroke-width: 0px; ">';
    
    foreach($words as $word) {
        foreach(dict_lookup($word) as $def) {
            echo "<!-- wordid: ".$def['rowid']."-->";
            echo dict_toHTML($def['data']);
        }
    }
    
    echo '</span></div></body><!-- Google Analytics stuff --><script src="http://www.google-analytics.com/urchin.js" type="text/javascript"></script><script type="text/javascript">_uacct = "UA-432885-1";urchinTracker();</script><!-- Done! --></html>';
}

?>