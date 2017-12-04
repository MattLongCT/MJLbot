<?php

/*
 * Current tests that are failing.
 */

// backward compatibility
if (!class_exists('\PHPUnit\Framework\TestCase') &&
    class_exists('\PHPUnit_Framework_TestCase')) {
    class_alias('\PHPUnit_Framework_TestCase', 'PHPUnit\Framework\TestCase');
}
$SLOW_MODE = TRUE;
 

class WikipediaBotTest extends PHPUnit\Framework\TestCase {

  protected function setUp() {
  }

  protected function tearDown() {
  }
  
  public function testReadExpandWrite() {
    $api = new WikipediaBot();
    $page = new TestPage();
    $page->get_text_from('User:Blocked Testing Account/readtest');
    $this->assertEquals('This page tests bots', $page->parsed_text());
    
    $page = new TestPage();
    $writeTestPage = 'User:Blocked Testing Account/writetest';
    $page->get_text_from($writeTestPage);
    $trialCitation = '{{Cite journal | title Bot Testing | ' .
      'doi_broken_date=1986-01-01 | doi = 10.1038/nature09068}}';
    $page->overwrite_text($trialCitation);
    $this->assertFalse(@$page->write("Testing bot write function")); // False because Travis IPs are blocked from editing
    
    $page->get_text_from($writeTestPage);
    $this->assertEquals($trialCitation, $page->parsed_text());
    $page->expand_text();
    $this->assertTrue(strpos($page->edit_summary(), 'journal, ') > 3);
    $this->assertTrue(strpos($page->edit_summary(), ' Removed ') > 3);
    $page->write($api);
    
    $page->get_text_from($writeTestPage);
    print $page->parsed_text();
    $this->assertTrue(strpos($page->parsed_text(), 'Nature') > 5);
  }
  
  public function testCategoryMembers() {
    $api = new WikipediaBot();
    $this->assertTrue(count($api->category_members('GA-Class cricket articles')) > 10);
    $this->assertEquals(0, count($api->category_members('A category we expect to be empty')));
  }
  
  public function testWhatTranscludes() {
    $this->assertTrue(count($api->what_transcludes('Graphical timeline')) > 10);
  }
    
  public function testGetPrefixIndex() {
    $api = new WikipediaBot();
    $namespace = $api->get_namespace('Template:Cite journal');
    $this->assertEquals($api->namespace_id('Template'), $namespace);
    $results = $api->get_prefix_index('Cite jo', $namespace); // too many results if we just use 'Cite'
    $this->assertTrue(array_search('Template:Cite journal', $results) !== FALSE);
    $results = $api->get_prefix_index("If we retrieve anything here, it's an error", $namespace);
    $this->assertTrue(empty($results));
  }
  
  public function testRedirects() {
    $api = new WikipediaBot();
    $this->assertEquals(-1, $api->is_redirect('NoSuchPage:ThereCan-tBe'));
    $this->assertEquals( 0, $api->is_redirect('User:Citation_bot'));
    $this->assertEquals( 1, $api->is_redirect('WP:UCB'));
  }  
  
  public function testNamespaces() {
    $api = new WikipediaBot();
    $vars = array(
          'format' => 'json',
          'action' => 'query',
          'meta'   => 'siteinfo',
          'siprop'  => 'namespaces',
      );
    $namespaces = $api->fetch($vars, 'POST');
    
    foreach ($namespaces->query->namespaces as $ns) {
      $ns_name = isset($ns->canonical)? $ns->canonical : '';
      $ns_id = (string) $ns->id;
      $this->assertEquals($ns_id, $api->namespace_id($ns_name));
      $this->assertEquals($ns_name, $api->namespace_name($ns_id));
    }
    
    /*
    // If the above assertions are throwing an error, you can generate an updated 
    // version of constants/namespace.php by running the below and pasting the content:
    echo "\n\n! Namespaces are out of date. Please update constants/namespace.php with the below:\n\n";
    echo "<?php\nconst NAMESPACES = Array(";
    foreach ($namespaces->query->namespaces as $ns) {
      $ns_name = isset($ns->canonical)? $ns->canonical : '';
      echo ("\n  " . (string) $ns->id . " => '" . $ns_name . "',");
    }
    echo ");\n\nconst NAMESPACE_ID = Array(";
    foreach ($namespaces->query->namespaces as $ns) {
      $ns_name = isset($ns->canonical)? $ns->canonical : '';
      echo ("\n  '" . strtolower($ns_name) . "' => " . (string) $ns->id . ",");
    }
    echo "\n);\n?" . ">\n";
    exit(0);
    */
  }
    
  public function testGetLastRevision() {
    $api = new WikipediaBot();
    $this->assertTrue(is_int(1 * $api->get_last_revision('User talk:Citation bot')));
  }
   
}

