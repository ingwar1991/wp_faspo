<?php
/**
 * Created by ingwar1991 from 0-1-10.com
 */

require_once 'faspo_utils.php';
require_once 'faspo_logs.php';

class FasPo_Image {
    private static $instance;

    private function __construct() {}

    public static function getInstance() {
        if ( !self::$instance ) {
            self::$instance = new FasPo_Image();
        }

        return self::$instance;
    }

    public function enabled( $feedKey ) {
        return !empty( FasPo_Settings::getInstance()->get( 'attach_images', $feedKey ) );
    }

    public function create( $imageUrl, $feedKey = 0 ) {
        $this->adjustImgWidth( $imageUrl, $feedKey );

        $imagePath = FasPo_Utils::getInstance()->getImagePath( md5( $imageUrl ) . '.png' );
        var_dump( 'Image path: ' . $imagePath );
        if ( !file_exists( $imagePath ) ) {
            $data = file_get_contents( $imageUrl );
            if ( !$data ) {
                return 0;
            }

            $img = imagecreatefromstring( $data );
            var_dump( "Image: ", $img );
            imagealphablending( $img, false );
            imagesavealpha( $img, true );
            if ( !imagepng( $img, $imagePath ) ) {
                return 0;
            }
        }

        $filetype = wp_check_filetype( $imagePath, null );
        $attachment = array(
            'post_mime_type' => $filetype['type'],
            'post_title' => sanitize_file_name( $imagePath ),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        var_dump( 'Image Attachment: ' . print_r( $attachment, true ) );

        $attachId = wp_insert_attachment( $attachment, $imagePath );
        if ( is_wp_error( $attachId ) || $attachId < 1 ) {
            var_dump( 'Image Error: ' . $attachId->get_error_messages() );
            die;
            return 0;
        }
        var_dump( 'Image Attachment ID: ' . $attachId );

        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        $attachData = wp_generate_attachment_metadata( $attachId, $imagePath );
        wp_update_attachment_metadata( $attachId, $attachData );

        return $attachId;
    }

    private function adjustImgWidth( &$imgUrl, $feedKey ) {
        $pattern = '/(\/resize\=\w\:){1}(\d+)\//';

        $matches = [];
        $res = preg_match_all( $pattern, $imgUrl, $matches );
        if ( !$res ) {
            return;
        }

        $imgWidth = !empty( $matches )
            ? end( $matches )[0]
            : 0;
        if ( !$imgWidth ) {
            return;
        }

        $requiredWidth = FasPo_Settings::getInstance()->get( 'image_width', $feedKey );
        if ( $imgWidth != $requiredWidth ) {
            $imgUrl = preg_replace( $pattern, '${1}' . $requiredWidth . '/', $imgUrl );
        }
    }

    public function defaultImgId() {
        $imgUrl = FasPo_Settings::getInstance()->get( 'image_url' );
        if ( !$imgUrl ) {
            return 0;
        }

        return $this->create( $imgUrl );
    }

    public function createDir() {
        $imgDirPath = FasPo_Utils::getInstance()->getImagePath();
        if ( file_exists( $imgDirPath ) ) {
            return true;
        }

        return mkdir( $imgDirPath );
    }
}
