<?php
    require_once 'faspo_utils.php';
    require_once 'faspo_settings.php';
?>

<div class="wrap">
    <h1>
        <img src="../<?php echo esc_html( FasPo_Utils::getInstance()->getPluginPath( 'attachments/faspo_logo.jpg' ) ); ?>" width="30" height="30" />
        FasPo Settings
    </h1>

    <form action="options.php" method="post" id="faspo-form" >
        <div class="faspo-form-row faspo-form-row-freq-exclude-cat" >
            <div class="form-field">
                <span class="faspo-label">Frequency</span>
                <select name="faspo_settings[frequency]" id="frequency" >
                    <?php
                        for ( $i = 1; $i <= 24; $i++ ) {
                            $selected = FasPo_Settings::getInstance()->get( 'frequency' ) == $i
                                ? 'selected'
                                : '';
                            echo '<option value="' . esc_html( $i ) . '" ' . esc_html( $selected ) . ' >' .
                                esc_html( $i ). ' hour(s)' .
                            "</option>\n";
                        }
                    ?>
                </select>
                <p>How often to check feed for updates (in hours). Recommended setting: 1 hour(s)</p>
            </div>

            <div class="form-field">
                <span class="faspo-label" >Exclude posts category</span>
                <select id="exclude_category_list" data-placeholder="Select category to exclude by" >
                    <option value=""></option>

                    <?php
                        $feeds = FasPo_Settings::getInstance()->get( 'feeds' );
                        $selectedCats = array();
                        foreach( $feeds as $feedKey => $feed ) {
                            $selectedCats = array_merge( $selectedCats, FasPo_Settings::getInstance()->get( 'categories', $feedKey ) );
                        }
                        $selectedCats = array_unique( array_filter( $selectedCats ) );

                        $cats = FasPo_Utils::getInstance()->getCategories();
                        $excludeCatId = FasPo_Settings::getInstance()->get( 'exclude_category' );
                        foreach( $selectedCats as $catId ) {
                            $selected = $catId == $excludeCatId
                                ? 'selected="selected"'
                                : '';
                            echo '<option value="' . esc_html( $catId ) . '" ' . esc_html( $selected ) . ' >' .
                                esc_html( $cats[ $catId ] ) .
                            "</option>\n";
                        }
                    ?>
                </select>

                <p>
                    <b>IMPORTANT</b>.
                    Exclude the category assigned to Press Releases if you do NOT want Press Releases posted to your home page/blog.
                    This is useful when you want post the Press Releases to a specific page on your website - separate from your other `posts`
                </p>
            </div>
        </div>

        <div class="faspo-clearfix"></div>

        <input type="hidden" name="faspo_settings[exclude_category]" id="exclude_category" value="<?php echo esc_html( FasPo_Settings::getInstance()->get( 'exclude_category' ) ); ?>" />
        <input type="hidden" name="faspo_settings[feeds]" id="feeds" />
    </form>

    <button id="feed_add_btn" class="button button-small">Add Feed</button>
    <div id="feed_add_content" style="display: none;" >
        <?php
            $feedKey = uniqid();
            $feedName = 'Feed 0';

            include 'faspo_form_tab.php';
        ?>
    </div>

    <div id="faspo-tabs" >
        <ul id="faspo-tabs-list" >
            <?php
                $feeds = FasPo_Settings::getInstance()->get( 'feeds' );
                foreach( $feeds as $feedKey => $feed ) {
                    echo '<li class="faspo-tabs-btn" ><a href="#feed-' . esc_html( $feedKey ) . '">' .
                        esc_html( FasPo_Settings::getInstance()->get( 'name', $feedKey ) ) .
                    "</a></li>\n";
                }

                if ( !count( $feeds ) ) {
                    $feedKey = uniqid();
                    echo '<li class="faspo-tabs-btn" ><a href="#feed-' . esc_html( $feedKey ) . "\">Feed 0</a></li>\n";
                }
            ?>
        </ul>

        <div id="faspo-tabs-container">
            <?php
                if ( !count( $feeds ) ) {
                    $feedName = 'Feed 0';

                    include 'faspo_form_tab.php';
                } else {
                    foreach( $feeds as $feedKey => $feed ) {
                        include 'faspo_form_tab.php';
                    }
                }
            ?>
        </div>

        <div class="faspo-clearfix"></div>
    </div>
</div>

<button id="submit-btn" class="button button-primary" >Save</button>

<?php $attachmentGetParam = FasPo_Utils::getInstance()->getWebAttachmentGetParam(); ?>

<!--<link href="../--><?php //echo FasPo_Utils::getInstance()->getPluginPath( 'attachments/jquery.chosen.min.css' ) . $attachmentGetParam; ?><!--" rel="stylesheet" type="text/css" />-->
<!--<link href="../--><?php //echo FasPo_Utils::getInstance()->getPluginPath( 'attachments/faspo_style.css' ) . $attachmentGetParam; ?><!--" rel="stylesheet" type="text/css" />-->

<!--<script src="../--><?php //echo FasPo_Utils::getInstance()->getPluginPath( 'attachments/jquery.chosen.min.js' ) . $attachmentGetParam; ?><!--" ></script>-->
<!--<script src="../--><?php //echo FasPo_Utils::getInstance()->getPluginPath( 'attachments/faspo_script.js' ) . $attachmentGetParam; ?><!--" type="text/javascript" ></script>-->
</div>
