<?php

assert_options(ASSERT_ACTIVE, true);
assert_options(ASSERT_WARNING, true);
assert_options(ASSERT_BAIL, true);

assert('isset($argc)');
assert('gc_enabled()');

require("../lib/simplehtmldom.class");
require("../lib/dictionary.class.inc");

$db = new PDO("sqlite:../words.db");

$lines = file("uniqs.txt");

$refs = array();

foreach($lines as $line) {
  $phrase = str_replace("'", "''", substr($line, 0, strpos($line, " -> ")));
  $tword = str_replace("'", "''", substr($line, strpos($line, " -> ") + 4, strpos($line, " <") - (strpos($line, " -> ") + 4)));
  $twordid = substr($line, strrpos($line, "<") + 1, strrpos($line, "> (") - (strrpos($line, "<") + 1));
  $type = str_replace("'", "''", substr($line, strrpos($line, "(") + 1, strrpos($line, ")") - (strrpos($line, "(") + 1)));
  
  assert(is_numeric($twordid));
  
  if(strcasecmp($phrase, $tword) == 0) {
    echo ",";
    continue;
  }
  
  $sql = "INSERT INTO refs VALUES('$phrase', '$type', $twordid)";
  
  $db->exec($sql);
  echo ".";  
}

?>