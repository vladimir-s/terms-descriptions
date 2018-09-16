<?php
/**
 * This script contains functions that processes AJAX requests
 */
add_action( 'wp_ajax_td_add_term', 'td_add_term' );
add_action( 'wp_ajax_td_delete_term', 'td_delete_term' );
add_action( 'wp_ajax_td_delete_terms', 'td_delete_terms' );
add_action( 'wp_ajax_td_get_term', 'td_get_term' );
add_action( 'wp_ajax_td_update_term', 'td_update_term' );
add_action( 'wp_ajax_td_update_permalink', 'td_update_permalink' );

/**
 * Adding term.
 * Term data should sent in $_POST array.
 * $_POST[ 'td_term' ]
 * $_POST[ 'td_link' ]
 * $_POST[ 'td_content_type' ]
 * $_POST[ 'td_post_id' ]
 * 
 * @return JSON new term in JSON format
 */
function td_add_term() {
	header( 'Content-type: application/json' );

    //Checking user capabilities
	if ( !current_user_can( 'manage_options' ) ) {
		die();
	}

	$res = array(
		'status' => 'ERR',
	);
    
    if ( !check_admin_referer( 'td_add_term' ) ) {
        die( 'Security check error' );
    }

    //checking term data
    if ( !isset( $_POST[ 'td_term' ] ) || empty( $_POST[ 'td_term' ] ) ) {
        $res[ 'message' ] = __( 'Enter the term, please', 'terms-descriptions' );
    }
    elseif ( !isset( $_POST[ 'td_link' ] ) || empty( $_POST[ 'td_link' ] ) ) {
        $res[ 'message' ] = __( 'Enter the link, please', 'terms-descriptions' );
    }
    elseif ( !isset( $_POST[ 'td_content_type' ] ) || empty( $_POST[ 'td_content_type' ] ) ) {
        $res[ 'message' ] = __( 'Content type is not set', 'terms-descriptions' );
    }
	else {
        list($term_data, $res) = td_prepare_term_data($res);
        //saving term
        global $wpdb;
        $wpdb->insert( $wpdb->prefix . 'td_terms', $term_data, array( '%d', '%s', '%s', '%s', '%s' ) );
        if ( !is_int( $wpdb->insert_id ) || ( int )$wpdb->insert_id <= 0 ) {
            $res[ 'message' ] = __( 'Term save error', 'terms-descriptions' );
            echo json_encode( $res );
            die();
        }
		$res[ 'status' ] = 'OK';
		$res[ 'message' ] = __( 'The term was added', 'terms-descriptions' );
        $term_data[ 't_id' ] = $wpdb->insert_id;
        $res[ 'term_data' ] = $term_data;
        $res[ 'term_data' ][ 't_post_title' ] = stripcslashes( $res[ 'term_data' ][ 't_post_title' ] );
        $res[ 'term_data' ][ 't_term' ] = stripcslashes( $res[ 'term_data' ][ 't_term' ] );
	}

	echo json_encode( $res );
	die();
}

function td_prepare_term_data($res) {
    switch ($_POST['td_content_type']) {
        case 'ext_link' :
            $term_link = $_POST['td_link'];
            if (!preg_match('/^\w{3,5}\:\/\//i', $term_link)) {
                $term_link = 'http://' . $term_link;
            }
            $link_title = $term_link;
            $trimmedTitle = trim($_POST['td_title']);
            if ( isset( $_POST[ 'td_title' ] ) && !empty($trimmedTitle) ) {
                $link_title = $trimmedTitle;
            }
            $term_data = array('t_post_id'    => 0,
                               't_post_title' => $link_title,
                               't_post_url'   => $term_link,
                               't_post_type'  => $_POST['td_content_type'],
                               't_term'       => $_POST['td_term'],);
            break;
        case 'post_id' :
            if (!is_int($_POST['td_link']) || ( int )$_POST['td_link'] <= 0) {
                $res['message'] = __('Incorrect post ID', 'terms-descriptions');
            }
            $term_link = get_permalink(( int )$_POST['td_link']);
            if (false === $term_link) {
                $res['message'] = __('Link creation error', 'terms-descriptions');
                echo json_encode($res);
                die();
            }
            $term_data = array('t_post_id'    => $_POST['td_link'],
                               't_post_title' => get_the_title(( int )$_POST['td_link']),
                               't_post_url'   => $term_link,
                               't_post_type'  => $_POST['td_content_type'],
                               't_term'       => $_POST['td_term'],);
            break;
        default :
            if (!isset($_POST['td_post_id']) || empty($_POST['td_post_id']) || !is_int($_POST['td_post_id']) || 0 >= ( int )$_POST['td_post_id']) {
                $res['message'] = __('Post ID is not set', 'terms-descriptions');
            }
            $term_link = get_permalink(( int )$_POST['td_post_id']);
            if (false === $term_link) {
                $res['message'] = __('Link creation error', 'terms-descriptions');
                echo json_encode($res);
                die();
            }
            $term_data = array('t_post_id'    => $_POST['td_post_id'],
                               't_post_title' => $_POST['td_link'],
                               't_post_url'   => $term_link,
                               't_post_type'  => $_POST['td_content_type'],
                               't_term'       => $_POST['td_term'],);
            break;
    }
    return array($term_data, $res);
}

/**
 * Delete term.
 * $_POST[ 'term_id' ] - id of the term that should be deleted
 * 
 * @global type $wpdb wordpress database class
 * 
 * @return JSON id of the deleted term
 */
function td_delete_term() {
	header( 'Content-type: application/json' );

    //Checking user capabilities
	if ( !current_user_can( 'manage_options' ) ) {
		die();
	}

	$res = array(
		'status' => 'ERR',
	);
    
    if ( !wp_verify_nonce( $_POST[ '_wpnonce' ], 'td_delete_term' ) ) {
        die( 'Security check error' );
    }
    
    //checking term id
    if ( isset( $_POST[ 'term_id' ] ) && ( int )$_POST[ 'term_id' ] > 0 ) {
        global $wpdb;
        //deleteng term
        $affected_rows = $wpdb->query( 'DELETE FROM ' . $wpdb->prefix . 'td_terms WHERE t_id=' . ( int )$_POST[ 'term_id' ] );
        switch ( $affected_rows ) {
            case false :
                $res[ 'message' ] = __( 'Unknown term ID', 'terms-descriptions' );
                break;
            case 0 :
                $res[ 'message' ] = __( 'Term delete error', 'terms-descriptions' );
                break;
            default :
                $res[ 'status' ] = 'OK';
                $res[ 'message' ] = __( 'The term was deleted', 'terms-descriptions' );
                $res[ 't_id' ] = $_POST[ 'term_id' ];
                break;
        }
    }
    else {
        $res[ 'message' ] = __( 'Unknown term', 'terms-descriptions' );
    }
    
	echo json_encode( $res );
	die();
}

/**
 * Delete several terms.
 * $_POST[ 'terms_ids' ] - ids of the terms that should be deleted
 *
 * @global type $wpdb wordpress database class
 *
 * @return JSON ids of the deleted term
 */
function td_delete_terms() {
    header( 'Content-type: application/json' );

    //Checking user capabilities
    if ( !current_user_can( 'manage_options' ) ) {
        die();
    }

    $res = array(
        'status' => 'ERR',
    );

    if ( !wp_verify_nonce( $_POST[ '_wpnonce' ], 'td_delete_term' ) ) {
        die( 'Security check error' );
    }

    //checking term id
    if ( isset( $_POST[ 'terms_ids' ] ) ) {
        $ids = array();
        foreach ($_POST[ 'terms_ids' ] as $id ) {
            if ( (int)$id > 0 ) {
                $ids[] = (int)$id;
            }
        }

        global $wpdb;
        //deleteng term
        $affected_rows = $wpdb->query( 'DELETE FROM ' . $wpdb->prefix . 'td_terms WHERE t_id IN (' . implode( ',', $ids ) . ')' );
        switch ( $affected_rows ) {
            case false :
                $res[ 'message' ] = __( 'Unknown terms IDs', 'terms-descriptions' );
                break;
            case 0 :
                $res[ 'message' ] = __( 'Terms delete error', 'terms-descriptions' );
                break;
            default :
                $res[ 'status' ] = 'OK';
                $res[ 'message' ] = __( 'The terms were deleted', 'terms-descriptions' );
                $res[ 't_ids' ] = $_POST[ 'terms_ids' ];
                break;
        }
    }
    else {
        $res[ 'message' ] = __( 'Unknown terms', 'terms-descriptions' );
    }

    echo json_encode( $res );
    die();
}

/**
 * Searches for the term.
 *
 * $_POST[ 'term_id' ] - id of the term that should be found.
 * 
 * @global type $wpdb
 * 
 * @return JSON term data in JSON format
 */
function td_get_term() {
	header( 'Content-type: application/json' );

    //Checking user capabilities
	if ( !current_user_can( 'manage_options' ) ) {
		die();
	}

	$res = array(
		'status' => 'ERR',
	);
    
    if ( isset( $_POST[ 'term_id' ] ) && ( int )$_POST[ 'term_id' ] > 0 ) {
        global $wpdb;
        $term = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix
                . 'td_terms WHERE t_id=' . ( int )$_POST[ 'term_id' ] );
        if ( null !== $term ) {
            $res[ 'status' ] = 'OK';
            $res[ 'term' ] = $term;
            $res[ 'term' ]->t_post_title = stripcslashes( $res[ 'term' ]->t_post_title );
            $res[ 'term' ]->t_term = stripcslashes( $res[ 'term' ]->t_term );
        }
        else {
            $res[ 'message' ] = __( 'Unknown term', 'terms-descriptions' );
        }
    }
    else {
        $res[ 'message' ] = __( 'Unknown term', 'terms-descriptions' );
    }
    
	echo json_encode( $res );
	die();
}

/**
 * Update of the term.
 * $_POST[ 'td_term' ]
 * $_POST[ 'td_link' ]
 * $_POST[ 'td_content_type' ]
 * $_POST[ 'td_post_id' ]
 * 
 * @global type $wpdb 
 * 
 * @return JSON updated term data
 */
function td_update_term() {
    header( 'Content-type: application/json' );

    //Checking user capabilities
	if ( !current_user_can( 'manage_options' ) ) {
		die();
	}

	$res = array(
		'status' => 'ERR',
	);
    
    if ( !check_admin_referer( 'td_add_term' ) ) {
        die( 'Security check error' );
    }

    //checkig term data
    if ( !isset( $_POST[ 'td_term' ] ) || empty( $_POST[ 'td_term' ] ) ) {
        $res[ 'message' ] = __( 'Enter the term, please', 'terms-descriptions' );
    }
    elseif ( !isset( $_POST[ 'td_link' ] ) || empty( $_POST[ 'td_link' ] ) ) {
        $res[ 'message' ] = __( 'Enter the link, please', 'terms-descriptions' );
    }
    elseif ( !isset( $_POST[ 'td_content_type' ] ) || empty( $_POST[ 'td_content_type' ] ) ) {
        $res[ 'message' ] = __( 'Content type is not set', 'terms-descriptions' );
    }
    elseif ( !isset( $_POST[ 'td_term_id' ] ) || 0 >= ( int )$_POST[ 'td_term_id' ] ) {
        $res[ 'message' ] = __( 'Unknown term id', 'terms-descriptions' );
    }
	else {
        list($term_data, $res) = td_prepare_term_data($res);
		//updating term
        global $wpdb;
        $affected_rows = $wpdb->update( $wpdb->prefix . 'td_terms', $term_data, array( 't_id' => $_POST[ 'td_term_id' ] )
                , array( '%d', '%s', '%s', '%s', '%s' ), array( '%d' ) );
        if ( !is_int( $affected_rows ) || ( int )$affected_rows < 0 ) {
            $res[ 'message' ] = __( 'Term update error', 'terms-descriptions' );
            echo json_encode( $res );
            die();
        }
		$res[ 'status' ] = 'OK';
		$res[ 'message' ] = __( 'The term was updated', 'terms-descriptions' );
        $term_data[ 't_id' ] = $_POST[ 'td_term_id' ];
        $res[ 'term_data' ] = $term_data;
        $res[ 'term_data' ][ 't_post_title' ] = stripcslashes( $res[ 'term_data' ][ 't_post_title' ] );
        $res[ 'term_data' ][ 't_term' ] = stripcslashes( $res[ 'term_data' ][ 't_term' ] );
	}

	echo json_encode( $res );
	die();
}

/**
 * Term permalink update.
 * $_POST[ 'td_term_id' ] - id of the term
 *
 * @global type $wpdb
 * 
 * @return JSON term data with updated permalink
 */
function td_update_permalink() {
    header( 'Content-type: application/json' );

    //Checking user capabilities
	if ( !current_user_can( 'manage_options' ) ) {
		die();
	}

	$res = array(
		'status' => 'ERR',
	);
    
    if ( !wp_verify_nonce( $_POST[ '_wpnonce' ], 'td_update_permalink' ) ) {
        die( 'Security check error' );
    }

    //checking term id
    if ( !isset( $_POST[ 'td_term_id' ] ) || 0 >= ( int )$_POST[ 'td_term_id' ] ) {
        $res[ 'message' ] = __( 'Unknown term id', 'terms-descriptions' );
    }
	else {
        global $wpdb;
		//searching for the term
        $term = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix
                . 'td_terms WHERE t_id=%d', $_POST[ 'td_term_id' ] ), ARRAY_A );

        if ( null === $term ) {
            $res[ 'message' ] = __( 'Unknown term id', 'terms-descriptions' );
        }
        else {
            //updating term permalink (ignoring terms with absolute links)
            if ( $term[ 't_post_id' ] != 0 ) {
                $term[ 't_post_url' ] = get_permalink( $term[ 't_post_id' ] );
                $affected_rows = $wpdb->update( $wpdb->prefix . 'td_terms', $term, array( 't_id' => $_POST[ 'td_term_id' ] )
                        , array( '%d', '%d', '%s', '%s', '%s', '%s' ), array( '%d' ) );
                if ( !is_int( $affected_rows ) || ( int )$affected_rows < 0 ) {
                    $res[ 'message' ] = __( 'Permalink update error', 'terms-descriptions' );
                    echo json_encode( $res );
                    die();
                }
            }
            
            $res[ 'status' ] = 'OK';
            $res[ 'message' ] = __( 'The term permalink was updated', 'terms-descriptions' );
            $res[ 'term_data' ] = $term;
            $res[ 'term_data' ][ 't_post_title' ] = stripcslashes( $res[ 'term_data' ][ 't_post_title' ] );
            $res[ 'term_data' ][ 't_term' ] = stripcslashes( $res[ 'term_data' ][ 't_term' ] );
        }
	}

	echo json_encode( $res );
	die();
}