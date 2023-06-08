<?php $feedKey = isset( $feedKey ) ? $feedKey : 0; ?>

<div id="feed-<?php echo esc_html( $feedKey ); ?>" class="faspo-tabs-tab" >
    <?php $status = FasPo_Settings::getInstance()->get( 'feed_processed', $feedKey ); ?>
    <?php if ( $status !== null ) { ?>
        <div class="form-wrap" >
            <div class="form-field <?php echo !$status ? 'faspo-error' : ''; ?>" >
                <span class="faspo-label" >Status</span>
                <p><?php echo esc_html( FasPo_Settings::getInstance()->get( 'feed_processed_msg', $feedKey ) ); ?></p>
            </div>
        </div>
    <?php } ?>

    <div class="form-wrap">
        <div class="form-field">
            <span class="faspo-label" >Feed Url</span>
            <input type="text" name="url" class="faspo-tab-inp" value="<?php echo esc_url_raw( FasPo_Settings::getInstance()->get( 'url', $feedKey ) ); ?>" />
            <button class="button button-small feed-remove-btn">Remove Feed</button>
            <p>Feed url to import posts from</p>
        </div>

        <input name="name" type="hidden" value="<?php echo esc_html( FasPo_Settings::getInstance()->get( 'name', $feedKey ) ); ?>" />
    </div>

    <div class="faspo-form-row faspo-form-row-vis">
        <div class="form-field">
            <span class="faspo-label" >Visibility</span>
            <select name="visibility" class="faspo-tab-inp" >
                <?php
                $visOptions = array(
                        'public',
                        'private',
                        'password',
                );
                foreach( $visOptions as $vis ) {
                    $selected = FasPo_Settings::getInstance()->get( 'visibility', $feedKey ) == $vis
                        ? 'selected'
                        : '';
                    echo '<option value="' . esc_html( $vis) . '" ' . esc_html( $selected ) . ' >' .
                        esc_html( $vis ) .
                    "</option>\n";
                }
                ?>
            </select>
            <p>Recommended setting: Public</p>
        </div>

        <div class="form-field">
            <span class="faspo-label" >Password</span>
            <input type="text" name="password" class="faspo-tab-inp" value="<?php echo esc_html( FasPo_Settings::getInstance()->get( 'password', $feedKey ) ); ?>" />
            <p>Password to open post</p>
        </div>

        <div class="faspo-clearfix"></div>
    </div>

    <div class="faspo-form-row faspo-form-row-stat-lim">
        <div class="form-field">
            <span class="faspo-label" >Status</span>
            <select name="status" class="faspo-tab-inp" >
                <?php
                $statuses = array(
                        'publish',
                        'draft',
                        'private',
                );
                foreach( $statuses as $status ) {
                    $selected = FasPo_Settings::getInstance()->get( 'status', $feedKey ) == $status
                        ? 'selected'
                        : '';
                    echo '<option value="' . esc_html( $status ) . '" ' . esc_html( $selected ) . ' >' .
                        esc_html( $status ) .
                    "</option>\n";
                }
                ?>
            </select>
            <p>
                Recommended setting: Publish. Press Releases will go live at the scheduled time.
                Draft mode requires manual publishing
            </p>
        </div>

        <div class="form-field">
            <span class="faspo-label" >Limit</span>
            <input type="text" name="limit" class="faspo-tab-inp" value="<?php echo esc_html( FasPo_Settings::getInstance()->get( 'limit', $feedKey ) ); ?>" />
            <p>Number of articles to fetch backwards. Recommended setting: 10</p>
        </div>

        <div class="faspo-clearfix"></div>
    </div>

    <div class="faspo-form-row faspo-form-row-tem-aut" >
        <div class="form-field">
            <span class="faspo-label" >Author</span>
            <select name="author" class="faspo-tab-inp" >
                <?php
                $selectedAuthor = FasPo_Settings::getInstance()->get( 'author', $feedKey );

                $users = get_users();
                foreach ( $users as $user ) {
                    $selected = '';
                    if ( $selectedAuthor == $user->ID ) {
                        $selected = 'selected';
                    }

                    echo '<option value="' . esc_html( $user->ID ) . '" ' . esc_html( $selected ) . ' >' .
                        esc_html( $user->display_name ) .
                    "</option>\n";
                }
                ?>
            </select>
            <p>
                The author that you want assigned to press releases. We recommend creating a new author
                You may also edit your template to remove remove author tags from posts
            </p>
        </div>

        <div class="form-field">
            <span class="faspo-label" >Template</span>
            <select name="template" class="faspo-tab-inp" >
                <?php
                $template = FasPo_Settings::getInstance()->get( 'template', $feedKey ) . '';
                page_template_dropdown( $template, 'post' );
                ?>
            </select>
            <p>Template For Posts</p>
        </div>

        <div class="faspo-clearfix"></div>
    </div>

    <div class="faspo-form-row faspo-form-row-cat-tag" >
        <div class="form-field">
            <div>
                <span class="faspo-label" >New Category</span>
                <input type="text" name="categories_add" />
                <button class="button button-small categories_add_btn">Add Category</button>
            </div>

            <div>
                <span class="faspo-label" >Categories</span>
                <select name="categories[]" multiple="multiple" class="faspo-tab-inp" data-placeholder="Select categories" >
                    <?php
                    $cats = FasPo_Utils::getInstance()->getCategories();
                    foreach( $cats as $catId => $catName ) {
                        $selected = '';
                        if ( in_array( $catId, FasPo_Settings::getInstance()->get( 'categories', $feedKey ) ) ) {
                            $selected = 'selected';
                        }

                        echo '<option value="' . esc_html( $catId ) . '" ' . esc_html( $selected ) . ' >' .
                            esc_html( $catName ) .
                        "</option>\n";
                    }
                    ?>
                </select>
            </div>

            <p>Posts Categories. We recommend creating a new category for Press Releases</p>
        </div>

        <div class="form-field">
            <div>
                <span class="faspo-label" >New Tag</span>
                <input type="text" name="tags_add" />
                <button class="button button-small tags_add_btn">Add Tag</button>
            </div>

            <div>
                <span class="faspo-label" >Tags</span>
                <select name="tags[]" multiple="multiple" class="faspo-tab-inp" data-placeholder="Select tags" >
                    <?php
                    $tags = get_tags( array(
                        'hide_empty' => 0
                    ) );
                    foreach( $tags as $tag ) {
                        $selected = '';
                        if ( in_array( $tag->term_id, FasPo_Settings::getInstance()->get( 'tags', $feedKey ) ) ) {
                            $selected = 'selected';
                        }

                        echo '<option value="' . esc_html( $tag->term_id ) . '" ' . esc_html( $selected ) . ' >' .
                            esc_html( $tag->name ) .
                        "</option>\n";
                    }
                    ?>
                </select>
            </div>

            <p>Posts Tags</p>
        </div>

        <div class="faspo-clearfix" ></div>
    </div>

    <div class="form-wrap" >
        <div class="form-field">
            <span class="faspo-label" >Date In Future</span>
            <select name="future_date" class="faspo-tab-inp" >
                <?php
                    $futDateOptions = array(
                        'change_to_now' => 'Change to NOW (Publish right away)',
                        'keep' => 'Keep (Schedule to date)'
                    );
                    foreach( $futDateOptions as $futDateOptVal => $futDateOptName ) {
                        $selected = FasPo_Settings::getInstance()->get( 'future_date', $feedKey ) == $futDateOptVal
                            ? 'selected'
                            : '';

                        echo "<option value=\"{$futDateOptVal}\" {$selected} >{$futDateOptName}</option>\n";
                    }
                ?>
            </select>
            <p>How to process dates in future</p>
        </div>

        <div class="faspo-clearfix"></div>
    </div>

    <div class="faspo-form-row faspo-form-row-img">
        <div class="form-field">
            <span class="faspo-label" >Attach images</span>
            <input type="checkbox" name="attach_images" value="1" class="faspo-tab-inp"
              <?php echo FasPo_Settings::getInstance()->get( 'attach_images', $feedKey ) ? 'checked="checked"' : ''; ?> />
            <p>Attach Images</p>
        </div>

        <div class="form-field">
            <span class="faspo-label" >Fixed Image Width</span>
            <input type="text" name="image_width" class="faspo-tab-inp"
              value="<?php echo esc_html( FasPo_Settings::getInstance()->get( 'image_width', $feedKey ) ); ?>" />
            <p>Set fixed image width</p>
        </div>

        <div class="form-field">
            <span class="faspo-label" >Default Image Url</span>
            <input type="text" name="image_url" class="faspo-tab-inp"
              value="<?php echo esc_url_raw( FasPo_Settings::getInstance()->get( 'image_url', $feedKey ) ); ?>" />
            <p>Image Url To Use When No Image Was Provided At Feed (Leave empty for none)</p>
        </div>

        <div class="form-field">
            <img src="<?php echo esc_url_raw( FasPo_Settings::getInstance()->get( 'image_url', $feedKey ) ); ?>" class="image_url_preview" />
        </div>

        <div class="faspo-clearfix"></div>
    </div>

    <div class="faspo-clearfix" ></div>
</div>
