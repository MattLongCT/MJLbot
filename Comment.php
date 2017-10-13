<?php
/*
 * Extends Item. Contains constants and text-parsing functions for wikitext comments.
 */

require_once('Item.php');

class Comment extends Item {
  const PLACEHOLDER_TEXT = '# # # CITATION_BOT_PLACEHOLDER_COMMENT %s # # #';
  const REGEXP = '~<!--.*?-->~us';
  const TREAT_IDENTICAL_SEPARATELY = FALSE;

  public function parse_text($text) {
    $this->rawtext = $text;
  }

  public function parsed_text() {
    return $this->rawtext;
  }
}

class Nowiki extends Item {
  const PLACEHOLDER_TEXT = '# # # CITATION_BOT_PLACEHOLDER_NOWIKI %s # # #';  // Have space in nowiki so that it does not through some crazy bug match itself recursively
  const REGEXP = '~<nowiki>.*?</nowiki>~us'; 
  const TREAT_IDENTICAL_SEPARATELY = FALSE;
  
  public function parse_text($text) {
    $this->rawtext = $text;
  }
  
  public function parsed_text() {
    return $this->rawtext;
  }
}
