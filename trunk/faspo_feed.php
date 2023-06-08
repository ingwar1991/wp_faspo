<?php
/**
 * Created by ingwar1991 from 0-1-10.com
 */

require_once 'faspo_utils.php';
require_once 'faspo_settings.php';
require_once 'faspo_post.php';
require_once 'faspo_date.php';

class FasPo_Feed {
    private static $instance;
    private $curlError;

    private function __construct() {}

    public static function getInstance() {
        if ( !self::$instance ) {
            self::$instance = new FasPo_Feed();
        }

        return self::$instance;
    }

    private function read( $feedKey ) {
        $feedUrl = FasPo_Settings::getInstance()->get( 'url', $feedKey );
        if ( !$feedUrl ) {
            return false;
        }

        $content = $this->getContent( $feedUrl );
        if ( !$content ) {
            if ( !empty( $this->curlError ) ) {
                $this->saveFeedStatus( $feedKey, 0, $this->curlError );
            }

            return false;
        }

        try {
            $feedData = new SimpleXMLElement( $content );
        } catch( \Exception $e ) {
            $this->saveFeedStatus( $feedKey, 0, $e->getMessage() );

            return false;
        }

        if ( empty( $feedData->channel ) ) {
            $this->saveFeedStatus( $feedKey, 0, 'Not valid feed content' );

            return false;
        }
        unset( $content );

        $title = null;
        if ( $feedData->channel->title ) {
            $title = FasPo_Utils::getInstance()->stripHtmlTags( (string) $feedData->channel->title );
        }

        $articles = array();
        $articleUrlMd5s = array();
        $i = 0;
        foreach( $feedData->channel->item as $item ) {
            if ( ++$i > FasPo_Settings::getInstance()->get( 'limit', $feedKey ) ) {
                break;
            }

            $artUrl = (string) $item->link;
            $urlMd5 = md5( $artUrl );
            if ( !$artUrl || in_array( $urlMd5, $articleUrlMd5s ) ) {
                continue;
            }
            $articleUrlMd5s[] = $urlMd5;

            $imgUrl = is_object( $item->enclosure )
                ? $item->enclosure->attributes()->url
                : null;
            $articles[] = array(
                'title' => (string) $item->title,
                'url' => $artUrl,
                'url_md5' => $urlMd5,
                'content' => (string) $item->description,
                'summary' => (string) $item->summary,
                'image_url' => !empty( $imgUrl )
                    ? (string) $imgUrl
                    : null,
                'date' => $this->fixDate( (string) $item->pubDate ),
            );
        }
        unset(
            $feedData,
            $articleUrlMd5s
        );

        $this->updateFeedTitle( $feedKey, $title );
        $this->saveFeedStatus( $feedKey, 1, count( $articles ) . ' articles parsed from feed' );

        return $articles;
    }

    private function updateFeedTitle( $feedKey, $title ) {
        FasPo_Settings::getInstance()->update( $title, 'name', $feedKey );
    }

    private function saveFeedStatus( $feedKey, $status, $statusMsg ) {
        FasPo_Settings::getInstance()->update( $status, 'feed_processed', $feedKey );
        FasPo_Settings::getInstance()->update( $statusMsg, 'feed_processed_msg', $feedKey );
    }

    private function fixDate( $date ) {
        return FasPo_Date::getInstance()->utcDate( $date );
    }

    public function process() {
        $feeds = FasPo_Settings::getInstance()->get( 'feeds' );
        foreach( $feeds as $feedKey => $feed ) {
            $articles = $this->read( $feedKey );
            if ( !empty( $articles ) ) {
                FasPo_Post::getInstance()->saveArticles( $feedKey, $articles );
            }
        }
    }

    private function getContent( $url ) {
//        // behave like browser by default
//        $content = $this->curl( $url, true );
//
//        // if failed to get html, try to behave like script
//        return !empty( $content )
//            ? $content
//            : $this->curl( $url );

        $html = wp_remote_retrieve_body( wp_remote_get( $url ) );

        return $this->isGzEncoded( $html )
            ? gzdecode( $html )
            : $html;
    }

    private function isGzEncoded( $html ) {
        if ( strlen( $html ) < 2 ) {
            return false;
        }

        return ( ord( substr( $html, 0, 1 ) ) == 0x1f && ord( substr( $html, 1, 1 ) ) == 0x8b );
    }
}

function faspo_process_feed() {
    FasPo_Feed::getInstance()->process();
}
