( function( $ ) {
    $( 'document' ).ready( function() {
        $( '#submit-btn' ).click( function() {
            var form = $( '#faspo-form' ),
                tabListBtns = $( '#faspo-tabs-list li' ),
                feedsData = [],
                excludeCat = form.find( '#exclude_category_list' ).val().length
                    ? form.find( '#exclude_category_list' ).val()
                    : '',
                hasErrors = false;

            tabListBtns.removeClass( 'faspo-error' );
            $( '.form-field' ).removeClass( 'faspo-error' );

            form.find( '#exclude_category' ).val( excludeCat );

            tabListBtns.each( function() {
                var tabHasError = false,
                    tabBtn = $( this ),
                    tabId = $( this ).find( 'a' ).attr( 'href' ),
                    tab = $( tabId ),
                    urlInp = tab.find( 'input[name="url"]' ),
                    visibilityInp = tab.find( 'select[name="visibility"]' ),
                    passwordInp = tab.find( 'input[name="password"]' );

                if ( visibilityInp.val() == 'password' && !passwordInp.val() ) {
                    visibilityInp.parents( '.form-field' ).addClass( 'faspo-error' );
                    passwordInp.parents( '.form-field' ).addClass( 'faspo-error' );
                    tabHasError = true;
                }

                if ( tabHasError ) {
                    tabBtn.addClass( 'faspo-error' );
                    hasErrors = true;
                } else {
                    feedsData[ feedsData.length ] = collectTabData( tab );
                }
            } );

            if ( !feedsData.length ) {
                tabListBtns.addClass( 'faspo-error' );
                hasErrors = true;
            }

            if ( hasErrors ) {
                return false;
            }
            form.find( '#feeds' ).val( JSON.stringify( feedsData ) );

            form.submit();
        } );

        $( '#frequency' ).chosen();
        $( '#exclude_category_list' ).chosen( {
            allow_single_deselect: true
        } );

        $( '#faspo-tabs-list' ).on( 'click', 'li', function() {
            var tab = $( this ),
                tabLink = $( this ).find( 'a' ),
                tabId = tabLink.attr( 'href' );

            tab.parent().find( 'li' ).removeClass( 'active' );
            $( '.faspo-tabs-tab' ).removeClass( 'active' );

            tab.addClass( 'active' );
            $( tabId ).addClass( 'active' );

            enableTab( $( tabId ) );
            tabLink.blur();

            return false;
        } );
        $( '#faspo-tabs-list li:first' ).click();

        function uniqid( prefix ) {
            prefix = prefix != undefined
                ? prefix
                : '';

            var sec = Date.now() * 1000 + Math.random() * 1000,
                id = sec.toString( 16 ).replace( /\./g, '' ).padEnd( 14, '0' );

            return prefix + id + Math.trunc( Math.random() * 100000000 );
        }

        $( '#feed_add_btn' ).click( function() {
            var tabsList = $( '#faspo-tabs-list' ),
                tabsCount = tabsList.find( 'li' ).length,
                tabsContainer = $( '#faspo-tabs-container' ),
                newTabContent = $( $( '#feed_add_content' ).html() ),
                newTabId = 'feed-' + uniqid();

            tabsList.append(
                $( '<li class="faspo-tabs-btn" />' ).append(
                    $( '<a />' ).attr( 'href', '#' + newTabId )
                        .html( 'Feed ' + tabsCount )
                )
            );

            newTabContent.attr( 'id', newTabId );

            $.when( tabsContainer.append( newTabContent ) ).done( function() {
                tabsList.find( 'li:last' ).click();
            } );
        } );

        function collectTabData( tab ) {
            var tabData = {};
            tab.find( '.faspo-tab-inp' ).each( function() {
                var name = $( this ).attr( 'name' );
                if ( name.slice( -2 ) == '[]' ) {
                    name = name.slice( 0, name.length - 2 );
                }

                tabData[ name ] = $( this ).val();
                if ( $( this ).attr( 'type' ) == 'checkbox' ) {
                    tabData[ name ] = $( this ).is( ':checked' )
                        ? 1
                        : 0;
                }
            } );

            return tabData;
        }

        function enableTab( tab ) {
            tab.find( '.feed-remove-btn' ).click( function() {
                var tab = $( this ).parents( '.faspo-tabs-tab' ),
                    tabId = tab.attr( 'id' ),
                    tabList = $( '#faspo-tabs-list' ),
                    tabListBtn = tabList.find( 'a[href="#' + tabId + '"]' ).parent();

                tab.remove();
                tabListBtn.remove();

                tabList.find( 'li:first' ).click();

                return false;
            } );

            tab.find(
                'select[name="visibility"],select[name="status"],select[name="author"],' + 
                'select[name="template"],select[name="future_date"]'
            ).chosen();

            tab.find( 'select[name="categories[]"]' ).chosen( {
                no_results_text: 'No such category, please create new',
                allow_single_deselect: true,
            } ).css( {
                minWidth: '100%',
                width: 'auto'
            } );

            tab.find( 'select[name="tags[]"]' ).chosen( {
                no_results_text: 'No such tag, please create new',
                allow_single_deselect: true,
            } ).css( {
                minWidth: '100%',
                width: 'auto'
            } );

            tab.find( 'select[name="visibility"]' ).change( function() {
                var inp = tab.find( 'input[name="password"]' ),
                    inpContainer = inp.parent();

                if ( $( this ).val() == 'password' ) {
                    inpContainer.show();
                }
                else {
                    inp.val( '' );
                    inpContainer.hide();
                }
            } ).change();

            tab.find( '.categories_add_btn, .tags_add_btn' ).click( function() {
                var btn = $( this ),
                    type = btn.hasClass( 'categories_add_btn' )
                        ? 'categories'
                        : 'tags',
                    inp = tab.find( 'input[name="' + type + '_add"]' ),
                    select = tab.find( 'select[name="' + type + '[]"]' );
                if ( !select.length || !inp.length ) {
                    return false;
                }

                var newVal = inp.val();
                if ( !newVal ) {
                    return false;
                }
                newVal.trim();

                var existingVals = [];
                select.find( 'option' ).each( function( key, val ) {
                    existingVals[existingVals.length] = $( val ).html().trim();
                } );

                var optKey = existingVals.indexOf( newVal );
                if ( optKey > -1 ) {
                    $( select.find( 'option' )[optKey] ).attr( 'selected', true );
                } else {
                    var optVal = 'new_' + newVal;
                    if ( !select.find( 'option[value="' + optVal + '"]' ).length ) {
                        var option = $( '<option />' );
                        option.val( optVal ).html( newVal );

                        select.prepend( option );
                    }

                    select.find( 'option[value="' + optVal + '"]' ).attr( 'selected', true );
                }

                select.change();
                select.trigger( 'chosen:updated' );

                inp.val( '' );

                return false;
            } );

            tab.find( 'select[name="categories[]"]' ).change( function() {
                var excludeCatInp = $( '#exclude_category_list' ),
                    excludeCatSelected = excludeCatInp.val(),
                    selectedCats = {};
                excludeCatInp.find( 'option:not([value=""])' ).remove();

                $( 'select[name="categories[]"]' ).each( function() {
                    $( this ).find( 'option:selected' ).each( function() {
                        selectedCats[ $( this ).val() ] = $( this ).html().trim();
                    } );
                } );

                for( var catId in selectedCats ) {
                    var catName = selectedCats[ catId ],
                        option = $( '<option />' );
                    option.attr( 'value', catId );
                    option.html( catName );

                    excludeCatInp.append( option );
                }

                for( var key in excludeCatSelected ) {
                    if ( excludeCatInp.find( 'option[value="' + excludeCatSelected[ key ] + '"]' ).length ) {
                        excludeCatInp.find( 'option[value="' + excludeCatSelected[ key ] + '"]' ).attr( 'selected', true );
                    }
                }

                excludeCatInp.change();
                excludeCatInp.trigger( 'chosen:updated' );
            } ).change();

            tab.find( 'input[name="attach_images"]' ).change( function() {
                var urlInp = tab.find( 'input[name="image_url"]' ),
                    urlInpContainer = urlInp.parent(),
                    widthInpContainer = tab.find( 'input[name="image_width"]' ).parent();

                if ( $( this ).prop( 'checked' ) ) {
                    urlInpContainer.show();
                    widthInpContainer.show();
                }
                else {
                    urlInp.val( '' ).change();
                    urlInpContainer.hide();

                    widthInpContainer.hide();
                }
            } ).change();

            tab.find( 'input[name="image_url"]' ).change( function() {
                var imgObj = $( this ).parents( '.faspo-form-row-img' ).find( 'img.image_url_preview' ),
                    imgContainer = imgObj.parent(),
                    imgUrlContainer = $( this ).parents( '.faspo-form-row-img' ).find( 'input[name="image_url"]' ).parent();
                imgObj.hide();
                imgUrlContainer.removeClass( 'faspo-image-active' );

                imgObj.attr( 'src', $( this ).val() );
                if ( $( this ).val() ) {
                    imgObj.show();
                    imgUrlContainer.addClass( 'faspo-image-active' );
                }
            } ).change();
        }
    } );
} )( jQuery );
