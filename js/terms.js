( function( $ ) {
    //term row template 
    var term_template = _.template(
        '<tr id="term_<%=t_id%>">'
        + '<th scope="row" class="check-column">'
        + '<label class="screen-reader-text" for="cb-select-<%=t_id%>">' + td_messages.select + '</label>'
        + '<input id="cb-select-<%=t_id%>" type="checkbox" name="td_term[]" value="<%=t_id%>">'
        + '<div class="locked-indicator"></div>'
        + '</th>'
        + '<td>'
        + '<strong><%=t_term%></strong>'
        + '<div class="row-actions">'
        + '<span class="edit"><a href="?action=td_edit_term&amp;term_id=<%=t_id%>">' + td_messages.edit + '</a> | </span>'
        + '<span class="trash"><a href="?action=td_delete_term&amp;term_id=<%=t_id%>&amp;_wpnonce=' + td_messages.nonce + '">' + td_messages.remove + '</a></span>'
        + '</div>'
        + '</td>'
        + '<td><a href="<%=t_post_url%>" target="_blank"><%=t_post_title%></a></td>'
        + '<tr>' );
    
    //term id field template
    var term_id_field_template = _.template(
        '<input type="hidden" name="td_term_id" id="td_term_id" value="<%=t_id%>">'
    );
    
    //parsing terms_ids
    var terms_ids = eval( td_messages.terms_ids.replace(/\&quot\;/gi, '"') );
    
    //new or edit term buttons
    var add_term_button = $( '<input type="submit" name="td_add_term" id="td_add_term" class="button-primary" value="' + td_messages.add_term + '">' );
    var edit_term_button = $( '<input type="submit" name="td_edit_term" id="td_edit_term" class="button-primary" value="' + td_messages.edit_term + '">' );
    var cancel_edit_term_button = $( '<input type="button" name="td_cancel_edit_term" id="td_cancel_edit_term" class="button-secondary" value="' + td_messages.cancel_edit_term + '">' );
    //messages
    var term_add_msg = $( '<div id="td_message" class="updated settings-error"><p><strong>'
            + td_messages.term_add + '</strong></p></div>' );
    var term_update_msg = $( '<div id="td_message" class="updated settings-error"><p><strong>'
            + td_messages.term_update + '</strong></p></div>' );
    
    //new term submit form handler
    $( '#td_add_term_form' ).submit( function() {
        //removing messages
        $( '.td_error' ).remove();
        $( '#td_message' ).remove();
        
        //preparing form data for ajax request
        var values = {};
        $.each( $( this ).serializeArray(), function( i, field ) {
            values[ field.name ] = field.value;
        });
        //checking form data
        if ( values.td_term === '' ) {
            $( '#td_term' ).parent().append( $('<span class="td_error">' + td_messages.enter_term + '</span>') );
            return false;
        }
        if ( values.td_link === '' ) {
            $( '#td_link' ).parent().append( $('<span class="td_error">' + td_messages.enter_link + '</span>') );
            return false;
        }
        
        if ( $( '#td_add_term' ).length === 1 ) {
            values.action = 'td_add_term';
        }
        
        if ( $( '#td_edit_term' ).length === 1 ) {
            values.action = 'td_update_term';
        }

        $('#td_add_term').attr('disabled', true);
        $('#save_term_spinner').css('visibility', 'visible').css('display', 'inline-block');

        //sending AJAX request
        $.post( td_messages.url_save, values, function( response ) {
            if ( response.status === 'OK' ) {
                //adding new row in a table
                if ( values.action === 'td_add_term' ) {
                    term_add_msg.insertAfter( 'div.wrap h2' );
                    $( term_template( response.term_data ) ).fadeIn( 'slow' ).prependTo( 'table.wp-list-table tbody' );
                    //adding term id to global terms array
                    terms_ids.push( response.term_data.t_id.toString() );
                }
                //updating the term row in a table
                if ( values.action === 'td_update_term' ) {
                    term_update_msg.insertAfter( 'div.wrap h2' );
                    var row_cells = $( term_template( response.term_data ) ).children();
                    $( '#term_' + response.term_data.t_id ).html( '' ).append( row_cells )
                        .animate( {'background-color' : 'lightYellow'}, 100, function() {
                            $( this ).animate( {'background-color' : 'transparent'}, 100 );
                        } );
                }
                //clearing form
                $( '#td_term' ).val( '' );
                $( '#td_link' ).val( '' );
                $( '#td_title' ).val( '' );
                $( '#td_post_id' ).val( '' );
                $( '#td_cancel_edit_term' ).trigger( 'click' );
            }
            else {
                alert( response.message );
            }

            $('#td_add_term').attr('disabled', false);
            $('#save_term_spinner').css('visibility', 'hidden').css('display', 'none');
        } );
        
        return false;
    } );

    //content type select
    var content_type = $( '#td_content_type' );
    var link_field = $( '#td_link' );
    var title_field = $( '#td_title' );

    function get_posts() {
        var posts = [];
        var post_type = content_type.val();
        var search_exp = new RegExp(link_field.val(), 'i');

        $.each(td_posts, function(i, post) {
            if (post.post_type === post_type && search_exp.test(post.post_title)) {
                posts.push({
                    label: post.post_title,
                    value: post.post_title,
                    ID: post.ID
                });
            }
        });

        return posts;
    }

    link_field.autocomplete({
        source: function (request, response) {
            response(get_posts());
        },
        select: function (event, ui) {
            $('#td_post_id').val(ui.item.ID);
        },
        minLength: 0
    });

    link_field.dblclick(function() {
        link_field.autocomplete('search', '');
    });

    //updating autocomplete settings if content type was changed
    content_type.change( function() {
        $( '#td_link' ).val( '' );
        $( '#td_post_id' ).val( '' );

        if (content_type.val() !== 'ext_link' && content_type.val() !== 'post_id') {
            $('#td_title').attr('disabled', 'disabled').addClass('hidden');
            link_field.attr('placeholder', td_messages.dbl_click_to_open_list);
        }
        else {
            if (content_type.val() === 'ext_link') {
                $('#td_title').removeAttr('disabled').removeClass('hidden');
                link_field.attr('placeholder', 'http://site-name.com');
                title_field.attr('placeholder', td_messages.ext_link_title);
            }
            else {
                $('#td_title').attr('disabled', 'disabled').addClass('hidden');
                link_field.attr('placeholder', td_messages.post_id);
            }
        }

        $( '#td_link').autocomplete('option', 'source', function (request, response) {
            response(get_posts());
        });
    } );
    
    $( '#td_content_type' ).trigger( 'change' );
    
    //remove term handler
    $( '.wrap' ).on( 'click', 'span.trash a', function() {
        $( '#td_message' ).remove();
        var cur_term = $( this );
        var params = $( this ).attr( 'href' ).slice( 1 );
        if ( confirm( td_messages.confirm_delete ) ) {
            //sending AJAX request
            $.post( td_messages.url_save, params, function( response ) {
                if ( response.status === 'OK' ) {
                    cur_term.parent().parent().parent().parent().animate( {'background-color' : '#ffabab'}, 'fast'
                        , function() {$( this ).fadeOut( 'slow', function() {
                            $( this ).remove();
                        } )} );
                    //removing term id from global terms array
                    var term_index = terms_ids.indexOf( response.t_id );
                    if( term_index != -1 ) {
                        terms_ids.splice(term_index, 1);
                    }
                }
                else {
                    alert( response.message );
                }
            } );
        }
        return false;
    } );
    
    //edit term handler
    $( '.wrap' ).on( 'click', 'span.edit a', function() {
        $( '#td_message' ).remove();
        //reading term id
        var term_id = $( this ).attr( 'href' ).match( /term\_id\=\d+/ );
        if ( null !== term_id ) {
            //scrolling page to the top
            $( 'html, body' ).animate( {scrollTop: 0}, 'fast' );
            //getting selected term data from server
            $.post( td_messages.url_save, 'action=td_get_term&' + term_id, function( response ) {
                if ( response.status === 'OK' ) {
                    //filling form with data
                    $( '#td_term' ).val( response.term.t_term );
                    $( '#td_content_type' ).val( response.term.t_post_type );
                    $( '#td_content_type' ).trigger( 'change' );
                    if ( response.term.t_post_type === 'post_id' ) {
                        $( '#td_link' ).val( response.term.t_post_id );
                    }
                    else if ( response.term.t_post_type === 'ext_link' ) {
                        $( '#td_link' ).val( response.term.t_post_url );
                        $( '#td_title' ).val( response.term.t_post_title );
                    }
                    else {
                        $( '#td_link' ).val( response.term.t_post_title );
                    }
                    $( '#td_post_id' ).val( response.term.t_post_id );
                    var term_id_field = $( '#td_term_id' );
                    //inserting term_id field
                    if ( term_id_field.length === 0 ) {
                        $( term_id_field_template( response.term ) ).insertAfter( $( '#td_post_id' ) );
                    }
                    else {
                        term_id_field.val( response.term.t_id );
                    }
                    //replacing add button with edit button
                    $( '#td_add_term' ).remove();
                    $( '#td_add_term_form p.submit' ).append( edit_term_button ).append( cancel_edit_term_button );
                }
                else {
                    alert( response.message );
                }
            } );
        }
        return false;
    } );
    
    //cancel edit handler
    $( '.wrap' ).on( 'click', '#td_cancel_edit_term', function() {
        //replacing buttons and clearing the form
        edit_term_button.remove();
        cancel_edit_term_button.remove();
        $( '#td_term_id' ).remove();
        $( '#td_term' ).val( '' );
        $( '#td_link' ).val( '' );
        $( '#td_post_id' ).val( '' );
        $( '#td_add_term_form p.submit' ).append( add_term_button );
        return false;
    } );
    
    //indicates if permalinks updates should be stopped
    var stop_updating = false;
    
    //update permalinks dialog
    $( '#td_update_permalinks' ).submit( function() {
        //removing error messages if any
        $( '#td_update_permalinks_dialog' ).find( 'div.error' ).remove();
        
        //counting terms
        var total_terms = terms_ids.length;
        var terms_updated = 0;
        var updated_terms_percent = Math.round( terms_updated / total_terms * 100 );
        var progress_field = $( '#td_update_progress' );
        
        progress_field.html( '0' );
        
        if ( terms_ids.length > 0 ) {
            stop_updating = false;
            //showing update dialog
            $( '#td_update_permalinks_dialog' ).dialog( {
                title: td_messages.updating_permalinks,
                close: function() {
                    stop_updating = true;
                },
                dialogClass: 'update-permalinks-dialog',
                modal: true
            } );
            //updating terms permalinks
            $( terms_ids ).each( function( i, term ) {
                if ( true === stop_updating ) {
                    return false;
                }
                $.post( td_messages.url_save, {action : 'td_update_permalink'
                        , td_term_id : term, _wpnonce: td_messages.nonce_update_permalink}
                        , function( response ) {
                    if ( response.status === 'OK' ) {
                        //counting current update percentage
                        terms_updated++;
                        updated_terms_percent = Math.round( terms_updated / total_terms * 100 );
                        progress_field.html( updated_terms_percent );
                        //updating term link at the current page
                        $( 'tr#term_' + response.term_data.t_id + ' > td > a' ).attr( 'href', response.term_data.t_post_url );
                        if ( updated_terms_percent === 100 ) {
                            $( '#td_update_permalinks_dialog' ).dialog( 'option', 'title', td_messages.done );
                        }
                    }
                    else {
                        //stopping updates
                        stop_updating = true;
                        if ( $( 'div#td_update_permalinks_dialog div.error' ).length === 0 ) {
                            $( '#td_update_permalinks_dialog' ).append( '<div class="error">'
                                + response.message + '</div>' );
                        }
                    }
                } );
                return true;
            } );
        }
        
        return false;
    } );

    //remove selected terms
    var removeSelectedBtn = $( '#td_remove_selected_btn' );

    function setRemoveSelectedBtn() {
        if ($('.wp-list-table input:checkbox:checked').length > 0) {
            removeSelectedBtn.attr('disabled', false);
        }
        else {
            removeSelectedBtn.attr('disabled', true);
        }
    }

    $( '.wp-list-table tbody' ).on( 'change', 'input:checkbox', function() {
        setRemoveSelectedBtn();
    });

    $( '.cb-select-all').change( function() {
        $( '.wp-list-table input:checkbox').prop( 'checked', $( this).prop( 'checked' ) );
        setRemoveSelectedBtn();
    } );

    removeSelectedBtn.on( 'click', function() {
        var terms = $( '.wp-list-table tbody input:checkbox:checked' );
        if ( terms.length > 0 ) {
            var termsIds = [];
            $.each( terms, function( i, term ) {
                termsIds.push( $( term ).val() );
            } );

            if ( confirm( td_messages.confirm_delete ) ) {
                //sending AJAX request
                var params = {
                    'action': 'td_delete_terms',
                    'terms_ids': termsIds,
                    '_wpnonce': td_messages.nonce
                };
                $.post( td_messages.url_save, params, function( response ) {
                    if ( response.status === 'OK' ) {
                        $.each( response.t_ids, function(i, id) {
                            $( '#term_' + id ).animate( { 'background-color' : '#ffabab' }, 'fast'
                                , function() { $( this ).fadeOut( 'slow', function() {
                                    $( this ).remove();
                                } ) } );

                            //removing terms ids from global terms array
                            var term_index = terms_ids.indexOf( id );
                            if( term_index != -1 ) {
                                terms_ids.splice(term_index, 1);
                            }
                        } );
                    }
                    else {
                        alert( response.message );
                    }
                } );
            }
        }
        return false;
    } );
} )( jQuery );