<?php

//define("DEBUG", true);

if(defined("DEBUG")) {
  assert_options(ASSERT_ACTIVE,   true);
  assert_options(ASSERT_WARNING,  true);
  assert_options(ASSERT_BAIL,     true);
  error_reporting(E_ALL | E_STRICT);
} else {
  assert_options(ASSERT_ACTIVE, false);
  error_reporting(E_ALL);
  ini_set("display_errors", "0");
}

require("lib/functions.inc");
require("lib/dictionary.class");

$words = array();

if(count($_GET) > 0) {
  $query = $_SERVER['QUERY_STRING'];
  $params = [];
  foreach (explode('&', $query) as $pair) {
    list($key, $unused) = explode('=', $pair);
    $words[] = urldecode($key);
  }
}

if(count($words) == 0) {
  trigger_error("no word", E_USER_ERROR);
}
    
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"><html><head><meta http-equiv="Content-Type" content="text/html;charset=utf-8" ><title>';
for($i = 0; $i < count($words); $i++) {
  echo $words[$i];
  if($i < count($words) - 1) {
    echo ", ";
  }
}
echo '</title></head><body><div class="defs" style="margin: 3em; margin-top: 2em; "><span class="Apple-style-span" style="border-collapse: separate; color: rgb(0, 0, 0); font-family: Times; font-size: medium; font-style: normal; font-variant: normal; font-weight: normal; letter-spacing: normal; line-height: normal; orphans: 2; text-align: auto; text-indent: 0px; text-transform: none; white-space: normal; widows: 2; word-spacing: 0px; -webkit-border-horizontal-spacing: 0px; -webkit-border-vertical-spacing: 0px; -webkit-text-decorations-in-effect: none; -webkit-text-size-adjust: auto; -webkit-text-stroke-width: 0px; ">';
    
foreach($words as $word) {
  $dict = new Dictionary($word);
  
  foreach($dict->getdefs() as $def) {
    echo "<!-- wordid: ".$def['rowid']."-->";
    echo Dictionary::toHTML($def['data']);
  }
}
    
echo '</span></div></body><!-- Google Analytics stuff --><script src="http://www.google-analytics.com/urchin.js" type="text/javascript"></script><script type="text/javascript">_uacct = "UA-432885-1";urchinTracker();</script><!-- Done! --></html>';

?>