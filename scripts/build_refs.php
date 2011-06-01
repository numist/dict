<?php

assert_options(ASSERT_ACTIVE, true);
assert_options(ASSERT_WARNING, true);
assert_options(ASSERT_BAIL, true);

assert('isset($argc)');
assert('gc_enabled()');

require("../lib/simplehtmldom.class");

$db = new PDO("sqlite:../words.db");

// will not overwrite an existing refs table. 
$sql = "CREATE TABLE refs(term TEXT, type TEXT, wordid INTEGER)";
if ($db->exec($sql) === false) {
    $err = $db->errorInfo();
    trigger_error("failed: ".$err[0].": ".$err[2]." (".$err[1].")", E_USER_ERROR);
}

function add_ref($element, $row, $type) {
  global $db;
  
  $term = str_replace("'", "''", $element->innertext);
  $type = str_replace("'", "''", $type);
  
  $sql = "INSERT INTO refs VALUES('$term', '$type', ${row['rowid']})";
  echo $sql." -- ${row['word']}\n";
  if($db->exec($sql) === false) {
      $err = $db->errorInfo();
      trigger_error("failed: ".$err[0].": ".$err[2]." (".$err[1].")", E_USER_ERROR);
  }
}

$sql = "SELECT rowid, * FROM words";
$res = $db->query($sql);
assert('is_object($res)');

while($row = $res->fetch(PDO::FETCH_ASSOC)) {
  $html = str_get_html($row['data']);

  // o:l filter
  foreach($html->find("o:l") as $element) {
    foreach($element->find("o:v") as $crap) {
      $crap->outertext = " ";
    }
    foreach($element->find("o:vargrp") as $crap) {
      $crap->outertext = " ";
    }
    foreach($element->find("o:hsb") as $crap) {
      $crap->outertext = "";
    }
    foreach($element->find("o:in") as $crap) {
      $crap->outertext = trim($crap->innertext);
    }
    foreach($element->find("o:r") as $crap) {
      $crap->outertext = trim($crap->innertext)." ";
    }
    foreach($element->find("o:italic") as $crap) {
      $crap->outertext = $crap->innertext;
    }
  
    $element->innertext = trim(str_replace("  ", " ", $element->innertext));
  
    if(strpos($element->innertext, "<") !== false) {
      fprintf(STDERR, $element->innertext." -> ".$row['word']." <".$row['rowid']."> (skipped-l)\n");
      continue;
    }
    
    add_ref($element, $row, "l");
    $element->clear();
  }

  // o:v filter NOT WITHIN o:l
  foreach($html->find("o:v") as $element) {
    $bloo = $element;
    while($bloo->parent()) {
      $bloo = $bloo->parent();
      if($bloo->tag == "o:l") {
        continue 2;
      }
    }
    foreach($element->find("o:hsb") as $crap) {
      $crap->outertext = "";
    }
    foreach($element->find("o:bold") as $crap) {
      $crap->outertext = $crap->innertext;
    }
    foreach($element->find("o:su") as $crap) {
      $crap->outertext = trim($crap->innertext);
    }
    foreach($element->find("o:italic") as $crap) {
      $crap->outertext = trim($crap->innertext);
    }
    foreach($element->find("o:dn") as $crap) {
      $crap->outertext = trim($crap->innertext);
    }
    foreach($element->find("o:r") as $crap) {
      $crap->outertext = trim($crap->innertext);
    }
    
    $element->innertext = trim(str_replace("  ", " ", $element->innertext));
    
    if(strpos($element->innertext, "<") !== false) {
      fprintf(STDERR, $element->innertext." -> ".$row['word']." <".$row['rowid']."> (skipped-v)\n");
      continue;
    }
    
    if(strpos($element->innertext, "-") === 0) {
      // argh suffix
      $wordend = strrpos($row['word'], $element->innertext[1].$element->innertext[2]);
      if($wordend === false) {
        $wordend = strrpos($row['word'], $element->innertext[1]);
        if($wordend === false) {
          $wordend = strlen($row['word']);
        }
      }
      $element->innertext = substr($row['word'], 0, $wordend).substr($element->innertext, 1);
    }
    
    add_ref($element, $row, "v");
    $element->clear();
  }
  
  // o:inf filter
  foreach($html->find("o:inf") as $element) {
    foreach($element->find("o:hsb") as $crap) {
      $crap->outertext = "";
    }
  
    if(strpos($element->innertext, "<") !== false) {
      fprintf(STDERR, $element->innertext." -> ".$row['word']." <".$row['rowid']."> (skipped-inf)\n");
      continue;
    }
    
    $element->innertext = trim(str_replace("  ", " ", $element->innertext));
    
    if(strpos($element->innertext, "-") === 0) {
      // argh suffix
      $wordend = strrpos($row['word'], $element->innertext[1].$element->innertext[2]);
      if($wordend === false) {
        $wordend = strrpos($row['word'], $element->innertext[1]);
        if($wordend === false) {
          $wordend = strlen($row['word']);
        }
      }
      $element->innertext = substr($row['word'], 0, $wordend).substr($element->innertext, 1);
    }
    
    add_ref($element, $row, "inf");
    $element->clear();
  }

  // o:f filter
  foreach($html->find("o:f") as $element) {
    foreach($element->find("o:vargrp") as $crap) {
      $crap->outertext = " ";
    }
    foreach($element->find("o:hsb") as $crap) {
      $crap->outertext = "";
    }
    foreach($element->find("o:italic") as $crap) {
      $crap->outertext = $crap->innertext;
    }
    foreach($element->find("o:su") as $crap) {
      $crap->outertext = trim($crap->innertext);
    }
    
    $element->innertext = trim(str_replace("  ", " ", $element->innertext));
    
    if(strpos($element->innertext, "<") !== false) {
      fprintf(STDERR, $element->innertext." -> ".$row['word']." <".$row['rowid']."> (skipped-f)\n");
      continue;
    }
    
    add_ref($element, $row, "f");
    $element->clear();
  }
  
  $html->clear();
}

/*
redirects:
    tags:
        f (forms like mustaches -> mustache)
        l (phrases like as long as)
            inner v: replace with " " and collapse "  " -> " " later.
        inf (check for incomplete plurals!)
        v (variations like moustache -> mustache)
                make sure it's not within l
        
    universal joint also universal coupling
    derivatives (see: pig)
    phrases? (see: as)
 */

?>