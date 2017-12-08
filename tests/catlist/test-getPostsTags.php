<?php

class Tests_Catlist_GetPostsTags extends WP_UnitTestCase {
    
    protected static $test_post;

    public static function wpSetUpBeforeClass($factory) {
        self::$test_post = $factory->post->create_and_get(array(
            'tags_input' => array('Tag1', 'Tag2', 'Tag3'),
        ));
    }

    public function test_should_display_posts_tags() {
        // Parameter not defined
        $catlist = new Catlist(array());
        $this->assertSame(null, $catlist->get_posts_tags(self::$test_post));
        
        // Default value
        $catlist = new Catlist(array('posts_tags' => 'no'));
        $this->assertSame(null, $catlist->get_posts_tags(self::$test_post));
        
        // Random string
        $catlist = new Catlist(array('posts_tags' => 'asdfhgjkl'));
        $this->assertSame(null, $catlist->get_posts_tags(self::$test_post));
        
        // Display if set to 'yes'
        $catlist = new Catlist(array('posts_tags' => 'yes'));
        $this->assertSame('Tag1Tag2Tag3', $catlist->get_posts_tags(self::$test_post));

        // Post without any tags
        $no_tags_post = self::factory()->post->create_and_get();
        $this->assertSame(null, $catlist->get_posts_tags($no_tags_post));
    }

    public function test_should_display_prefix(){
        $catlist = new Catlist(array(
            'posts_tags' => 'yes',
            'posts_tags_prefix' => ''
        ));
        $this->assertSame('Tag1Tag2Tag3', $catlist->get_posts_tags(self::$test_post));

        $catlist = new Catlist(array(
            'posts_tags' => 'yes',
            'posts_tags_prefix' => 'Prefix '
        ));
        $this->assertSame('Prefix Tag1Tag2Tag3', $catlist->get_posts_tags(self::$test_post));
    }

    public function test_should_display_taglink() {
        $catlist = new Catlist(array(
            'posts_tags' => 'yes',
            'taglink' => ''
        ));
        $this->assertSame('Tag1Tag2Tag3', $catlist->get_posts_tags(self::$test_post));

        $catlist = new Catlist(array(
            'posts_tags' => 'yes',
            'taglink' => 'yes'
        ));
        $returned_string = $catlist->get_posts_tags(self::$test_post);

        $this->assertContains('<a href="', $returned_string);
        $this->assertContains('">Tag1</a><a href="', $returned_string);
        $this->assertContains('">Tag2</a><a href="', $returned_string);
        $this->assertContains('">Tag3</a>', $returned_string);
    }

    public function test_display_glue () {
        $catlist = new Catlist(array(
            'posts_tags' => 'yes',
            'posts_tags_glue' => ''
        ));
        $this->assertSame('Tag1Tag2Tag3', $catlist->get_posts_tags(self::$test_post));

        $catlist = new Catlist(array(
            'posts_tags' => 'yes',
            'posts_tags_glue' => ' GLUE '
        ));
        $this->assertSame('Tag1 GLUE Tag2 GLUE Tag3',
                          $catlist->get_posts_tags(self::$test_post));
    }

    public function test_display_inner_tag() {
        $catlist = new Catlist(array(
            'posts_tags' => 'yes',
            'posts_tags_inner' => ''
        ));
        $this->assertSame('Tag1Tag2Tag3', $catlist->get_posts_tags(self::$test_post));

        $catlist = new Catlist(array(
            'posts_tags' => 'yes',
            'posts_tags_inner' => 'p'
        ));
        $this->assertSame('<p class="tag-tag1">Tag1</p><p class="tag-tag2">Tag2</p>' .
                          '<p class="tag-tag3">Tag3</p>',
                          $catlist->get_posts_tags(self::$test_post));
    }
}