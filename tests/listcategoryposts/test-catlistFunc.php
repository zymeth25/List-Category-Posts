<?php

class Tests_ListCategoryPosts_CatlistFunc extends WP_UnitTestCase {

    public function test_display_posts_tags_tag() {
        $test_posts = self::factory()->post->create_many(3, array(
            'tags_input' => array('Tag1', 'Tag2', 'Tag3')
        ));
        $lcp = new ListCategoryPosts();

        // Make sure there is no html between the tags' string 
        // and the </li> closing the post's entry when tag is not set
        $this->assertContains('Tag1, Tag2, Tag3</li><li', $lcp->catlist_func(array(
            'posts_tags' => 'yes',
            'posts_tags_tag' => ''
        )));

        // Test when post_tags_tag is set
        $this->assertContains('<div>Tag1, Tag2, Tag3</div>', $lcp->catlist_func(array(
            'posts_tags' => 'yes',
            'posts_tags_tag' => 'div'
        )));
    }

    public function test_display_posts_tags_class() {
        $test_posts = self::factory()->post->create(array(
            'tags_input' => array('Tag1', 'Tag2', 'Tag3')
        ));
        $lcp = new ListCategoryPosts();

        $this->assertNotContains('<p class=', $lcp->catlist_func(array(
            'posts_tags' => 'yes',
            'posts_tags_tag' => 'p'
        )));

        $this->assertContains('<p class="test">Tag1, Tag2, Tag3</p>',
                              $lcp->catlist_func(array(
                                  'posts_tags' => 'yes',
                                  'posts_tags_tag' => 'p',
                                  'posts_tags_class' => 'test'
                                  )
                              ));
    }
}