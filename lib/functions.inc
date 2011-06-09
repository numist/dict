<?php

// Indent the string $line $level times, using $filler as the whitespace (defaults to two spaces).
function indent($line, $level, $filler="  ") {
  $str = "";
  while($level-- > 0) {
    $str .= $filler;
  }
  return $str.trim($line);
}

// Prettify XML-like data.
// XXX: loses an indent level on self-closing tags due to not being syntax-aware.
function format_human($str) {
  $str = str_replace(array("<", ">"), array("\n<", ">\n"), $str);
  $str = str_replace("\n\n", "\n", $str);
  $arr = explode("\n", $str);
  $level = 0;
  for($i = 0; $i < count($arr); $i++) {
    if(substr($arr[$i], 0, 2) == "</") {
      $level--;
      $arr[$i] = indent($arr[$i], $level);
    }
    else if(substr($arr[$i], 0, 1) == "<" && substr($arr[$i], 0, 5) != "<meta" && substr($arr[$i], 0, 2) != "<!") {
      $arr[$i] = indent($arr[$i], $level);
      $level++;
    }
    else {
      $arr[$i] = indent($arr[$i], $level);
    }
  }
  return implode("\n", $arr);
}

?>