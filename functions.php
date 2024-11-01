<?php
/**
 * Snippets Function File
 *
 * LICENSE: The GNU General Public License (GPL)
 *
 * @copyright  2009 Business Xpand
 * @license    GPL v2.0
 * @version    0.9.4
 * @link       http://www.businessxpand.com
 * @since      File available since Release 0.9
*/

/**
 * Update your snippet fields here
 *
 * If you are unfamiliar with PHP code or WordPress theme creation please visit
 * the following sites for more information:
 * PHP: http://www.php.net
 * Theme development: http://codex.wordpress.org/Theme_Development
 *
 * If you decide to use this in your theme, we would really appreciate a thank you email and perhaps a link to your theme,
 * send to team@businessxpand.com
 *
 * INSTRUCTIONS:
 *   $snippetFields
 *   The array key ( value before the => ) is the name of the field to be referenced by the snippets_value function.
 *   The array value ( value after the => ) is the starting value of the field, you can leave this blank ( '' ).
 *
 *   $snippetTypes
 *   The array key ( value before the => ) is the name of the field to be referenced by the snippets_value function.
 *   The array value ( value after the => ) is the type of field to display to your user, you have a choice of text, textarea or checkbox.
 *
 *   You can add the snippet value to your theme using the following code:
 *     <?php snippets_value( 'fieldname' ); ?>
 *       where fieldname is the exact name of the field you enter below.
 *
 *   An alternative is that you can use the snippet value in your posts and pages using the following markup:
 *     [snippets:fieldname]
 *       again where fieldname is the exact name of the field you enter below.
 */
$snippetFields = array( 'Test Field 1' => 'test_field_1',
                        'Test Field 2' => 'test_field_2',
                        'Test Field 3' => 'test_field_3' );
$snippetTypes = array( 'Test Field 1' => 'text',
                       'Test Field 2' => 'textarea',
                       'Test Field 3' => 'checkbox' );

/**
 * Standard WordPress widget sidebar
 */
if ( function_exists( 'register_sidebar' ) ) {
    register_sidebar( array( 'before_widget' => '<li id="%1$s" class="widget %2$s">',
                             'after_widget' => '</li>',
                             'before_title' => '<h2 class="widgettitle">',
                             'after_title' => '</h2>' ) );
}


/** DO NOT CHANGE ANYTHING BELOW THIS LINE, UNLESS YOU KNOW WHAT YOU'RE DOING AND AGREE AND COMPLY TO THE TERMS OF THE GPL v2.0 LICENSE */
$availableSnippetTypes = array( 'text' => '<input type="text" name="snippet_name[%s]" value="%s"/>',
                                'textarea' => '<textarea name="snippet_name[%s]">%s</textarea>',
                                'checkbox' => '<input type="checkbox" name="snippet_name[%s]" value="1"%s/>');
/**
 * Output snippet field value
 *
 * @param string $fieldName
 * @return mixed
 */
if ( !function_exists( 'snippets_value' ) ) {
    function snippets_value( $fieldName , $echo = true) {
        $return = false;
        if ( isset( $fieldName ) ) {
            $snippetOptions = get_option( 'snippets' );
            if ( isset( $snippetOptions[$fieldName] ) ) {
                $return = stripslashes( $snippetOptions[$fieldName] );
            }
        }
        if ( $echo ) echo $return; else return $return;
    }
}

if ( !get_option( 'snippets_plugin' ) ) {
    $message = '';

    /**
     * Processing for the snippet theme admin page
     *
     * @author Steven Raynham
     * @since 0.9.4
     *
     * @param null
     * @return void
     */
    function snippetsAddThemePage() {
        global $snippetFields, $snippetTypes, $message;
        if ( !( $snippetOptions = get_option('snippets') ) ) {
            add_option( 'snippets', $snippetFields );
            $snippetOptions = $snippetFields;
        }
        if ( !get_option('snippets_type') ) {
            add_option( 'snippets_type', $snippetTypes );
        }
        if ( isset( $_GET['page'] ) && ( $_GET['page'] == basename(__FILE__) ) ) {
            if ( isset( $_POST['action'] ) && isset( $_POST['snippets-form'] ) ) {
                check_admin_referer( 'snippets-nonce', 'snippets-nonce' );
                switch ( $_POST['action'] ) {
                    case 'save':
                        if ( count( $_POST['snippet_name'] ) > 0 ) {
                            foreach ( $snippetOptions as $name => $value ) {
                                if ( ( $snippetTypes[$name] == 'checkbox' ) && ( !isset( $_POST['snippet_name'][$name] ) ) ) {
                                    $_POST['snippet_name'][$name] = '';
                                }
                            }
                            foreach ( $_POST['snippet_name'] as $name => $value ) {
                                $snippetOptions[$name] = $value;
                            }
                            update_option( 'snippets', $snippetOptions );
                            $message .= '<p>Snippet values updated.</p>';
                        }
                        break;
                }
                wp_redirect("themes.php?page=functions.php&saved=true");
                die;
            }
        }
        add_theme_page('Snippets', 'Snippets', 'edit_themes', basename(__FILE__), 'snippetsThemePage');
    }
    add_action('admin_menu', 'snippetsAddThemePage');

    /**
     * Output the snippet theme admin page
     *
     * @author Steven Raynham
     * @since 0.9.2
     *
     * @param null
     * @return void
     */
    function snippetsThemePage() {
        global $message, $availableSnippetTypes;
        $snippetOptions = get_option('snippets');
        $snippetTypes = get_option('snippets_type');
?><div class='wrap'>
    <h2>Snippets</h2>
    <?php if ( !empty( $message ) ) { ?><div id="message" class="updated fade"><p><strong><?php _e( $message ); ?></strong></p></div><?php } ?>
    <h3><?php _e( 'Instructions' ); ?></h3>
    <ul>
        <li><?php _e( 'You can add the snippet value to your theme using the following code:<pre>' . htmlentities( '<?php snippets_value( \'fieldname\' ); ?>' ) . '</pre>where fieldname is the exact name of the field you enter below.' ); ?></li>
        <li><?php _e( 'An alternative is that you can use the snippet value in your posts and pages using the following markup:<pre>[snippets:fieldname]</pre>again where fieldname is the exact name of the field you enter below.' ); ?></li>
    </ul>
    <hr/>
    <div>
        <form method="post" action="">
            <?php wp_nonce_field( 'snippets-nonce', 'snippets-nonce', true, true ); ?>
            <input type="hidden" name="action" value="save"/>
            <input type="hidden" name="snippets-form" value="true"/>
            <table class="form-table">
                <tbody>
                    <?php foreach ( $snippetOptions as $name => $value ) { ?>
                    <tr valign="top">
                        <th scope="row"><label><?php echo stripslashes( $name ); ?></label></th>
                        <?php if ( isset( $snippetTypes[$name] ) ) $snippetType = $availableSnippetTypes[$snippetTypes[$name]]; else $snippetType = $availableSnippetTypes['text']; ?>
                        <td>
                            <?php
                                if ( $snippetTypes[$name] == 'checkbox' ) {
                                    echo sprintf( stripslashes( $snippetType ), stripslashes( $name ), ( ( $value == 1 ) ? ' checked="checked"': '' ) );
                                } else {
                                    echo sprintf( stripslashes( $snippetType ), stripslashes( $name ), stripslashes( $value ) );
                                }
                            ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            <p class="submit"><input class="button-primary" type="submit" name="submit" value="Save changes"/></p>
        </form>
    </div>
</div><?php
    }

    /**
     * Content filter
     *
     * @author Steven Raynham
     * @since 0.9
     *
     * @param string $content
     * @return string
     */
    function snippetsContent( $content )
    {
        $pattern = '/\[snippets:(.+)\]/i';
        preg_match_all( $pattern, $content, $matches, PREG_SET_ORDER );
        if ( count( $matches ) > 0 ) {
            foreach ( $matches as $match ) {
                if ( trim( $match[1] ) != '') {
                    $search[] = $match[0];
                    $replace[] = snippets_value( $match[1], false );
                }
            }
        }
        if ( isset( $search ) && isset( $replace ) ) $content = str_replace($search, $replace, $content);
        return $content;
    }
    add_filter( 'the_content', 'snippetsContent' );
}