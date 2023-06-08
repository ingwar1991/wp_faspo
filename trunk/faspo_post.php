<?php
/**
 * Created by ingwar1991 from 0-1-10.com
 */

require_once 'faspo_settings.php';
require_once 'faspo_utils.php';
require_once 'faspo_image.php';
require_once 'faspo_logs.php';
require_once 'faspo_date.php';

class FasPo_Post {
    private static $instance;

    private function __construct() {}

    public static function getInstance() {
        if ( !self::$instance ) {
            self::$instance = new FasPo_Post();
        }

        return self::$instance;
    }

    public function saveArticles( $feedKey, $articles ) {
        if ( !FasPo_Settings::getInstance()->get( 'url', $feedKey ) ) {
            return false;
        }
        $existingUrlMd5s = $this->getLastTenPostUrlMd5s( $feedKey );

        $postIds = array();
        foreach( $articles as $article ) {
            if ( in_array( $article['url_md5'], $existingUrlMd5s ) ) {
                continue;
            }

            $postStatus = FasPo_Settings::getInstance()->get( 'status', $feedKey );
            $postStatus = $postStatus
                ? $postStatus
                : 'draft';

            $postData = array(
                'post_title' => $article['title'],
                'post_content' => $this->fixContent( $feedKey, $article ),
                'post_status' => $postStatus,
            );
            $this->addDates( $postData, $article['date'] );

            if ( !empty( $article['summary'] ) ) {
                $postData['post_excerpt'] = $article['summary'];
            }
            if ( FasPo_Settings::getInstance()->get( 'author', $feedKey ) ) {
                $postData['post_author'] = FasPo_Settings::getInstance()->get( 'author', $feedKey );
            }
            if ( FasPo_Settings::getInstance()->get( 'status', $feedKey ) == 'password' && FasPo_Settings::getInstance()->get( 'password', $feedKey ) ) {
                $postData['post_password'] = FasPo_Settings::getInstance()->get( 'password', $feedKey );
            }

            $postId = wp_insert_post( $postData );
            if ( !is_wp_error( $postId ) && $postId > 0 ) {
                $postIds[] = $postId;

                $this->setCategories( $feedKey, $postId );
                $this->setTags( $feedKey, $postId );
                $this->setTemplate( $feedKey, $postId );
                $this->setImage( $feedKey, $postId, $article );
            }
        }

        $savedPosts = count( $postIds );
        $date = FasPo_Date::getInstance()->wpDate( 'now', FasPo_Date::getInstance()->getWpFormat() ) .
            ' (' . FasPo_Date::getInstance()->utcDate( 'now', FasPo_Date::getInstance()->getWpFormat() ) . ' UTC)';

        FasPo_Settings::getInstance()->update(
            $savedPosts > 0
                ? $savedPosts . ' new articles added at ' . $date
                : 'No new articles found at ' . $date,
            'feed_processed_msg',
            $feedKey
        );

        return $postIds;
    }

    private function getLastTenPostUrlMd5s( $feedKey ) {
        $query = new WP_Query( [
            's' => FasPo_Utils::getInstance()->getPostContentKeyword( $feedKey ),
            'post_type' => 'post',
            'post_status' => 'any',
            'posts_per_page' => 10
        ] );

        $urlMd5s = [];
        if ( $query->have_posts() ) {
            foreach( $query->posts as $post ) {
                $content = $post->post_content;
                $content = substr( $content, strpos( $content, FasPo_Utils::getInstance()->getPostContentKeyword( $feedKey ) ) + strlen( $this->getContentKeyword( $feedKey ) ) );
                $urlMd5s[] = trim( substr( $content, 0, strpos( $content, '-->' ) ) );
            }
        }

        return $urlMd5s;
    }

    private function getContentKeyword( $feedKey ) {
        return md5( FasPo_Settings::getInstance()->get( 'url', $feedKey ) ) . '_feed_article';
    }

    private function fixContent( $feedKey, $article ) {
        $content = '<!-- ' . FasPo_Utils::getInstance()->getPostContentKeyword( $feedKey ) . ' ' . $article['url_md5'] .  ' -->';
        $content .= "\r\n" . $article['content'];

        return $content;
    }

    private function addDates( &$postData, $utcDate ) {
        if ( FasPo_Date::getInstance()->compareUtcDates( $utcDate ) > 0 ) {
            if ( FasPo_Settings::getInstance()->get( 'future_date' ) != 'keep' ) {
                $utcDate = FasPo_Date::getInstance()->utcDate();
            }
        }
        $wpDate = FasPo_Date::getInstance()->wpDate( $utcDate );

        $postData['post_date'] = $wpDate;
        $postData['post_date_gmt'] = $utcDate;
    }

    private function setCategories( $feedKey, $postId ) {
        $categories = FasPo_Settings::getInstance()->get( 'categories', $feedKey );
        if ( !empty( $categories ) && is_array( $categories ) ) {
            wp_set_object_terms( $postId, $categories, 'category' );
        }
    }

    private function setTags( $feedKey, $postId ) {
        $tags = FasPo_Settings::getInstance()->get( 'tags', $feedKey );
        if ( !empty( $tags ) && is_array( $tags ) ) {
            wp_set_post_tags( $postId, $tags );
        }
    }

    private function setTemplate( $feedKey, $postId ) {
        if ( FasPo_Settings::getInstance()->get( 'template', $feedKey ) ) {
            update_post_meta( $postId, '_wp_page_template', FasPo_Settings::getInstance()->get( 'template', $feedKey ) );
        }
    }

    private function setImage( $feedKey, $postId, $article ) {
        if ( FasPo_Image::getInstance()->enabled( $feedKey ) ) {
            $attachId = FasPo_Image::getInstance()->defaultImgId();
            if ( !empty( $article['image_url'] ) ) {
                $attachId = FasPo_Image::getInstance()->create( $article['image_url'], $feedKey );
            }

            if ( $attachId ) {
                set_post_thumbnail( $postId, $attachId );
            }
        }
    }
}
