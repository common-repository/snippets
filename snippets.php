<?php
/*
Plugin Name: Snippets
Plugin URI: http://www.businessxpand.com
Description: Allows you to display defined fields within your themes or content defined by the user
Author: Business Xpand
Version: 0.9.4
Author URI: http://www.businessxpand.com
*/
//error_reporting(E_ALL);
//ini_set('display_errors', '1');

/**
 * Snippets Class
 *
 * @copyright 2009 Business Xpand
 * @license GPL v2.0
 * @author Steven Raynham
 * @version 0.9.4
 * @link http://www.businessxpand.com/
 * @since File available since Release 0.9
 */
class Snippets
{
    var $availableSnippetTypes;

    /**
     * Construct the plugin
     *
     * @author Steven Raynham
     * @since 0.9.2
     *
     * @param void
     * @return null
     */
    function Snippets()
    {
        register_deactivation_hook( __FILE__, array( &$this, 'deactivate' ) );
        if ( !get_option( 'snippets_plugin' ) ) add_option( 'snippets_plugin', true );
        if ( is_admin() ) add_action( 'admin_menu', array( &$this, 'adminMenu' ) );
        add_filter( 'the_content', array( &$this, 'content' ) );
        $this->availableSnippetTypes = array( 'text' => '<input type="text" name="snippet_name[%s]" value="%s"/>',
                                              'textarea' => '<textarea name="snippet_name[%s]">%s</textarea>',
                                              'checkbox' => '<input type="checkbox" name="snippet_name[%s]" value="1"%s/>');
    }

    /**
     * Remove the snippets plugin reference, but leaves the fields intact, mainly for themes with the snippets functions.php
     *
     * @author Steven Raynham
     * @since 0.9
     *
     * @param void
     * @return null
     */
    function deactivate()
    {
        if ( get_option( 'snippets_plugin' ) ) delete_option( 'snippets_plugin' );
    }

   /**
     * Initiate admin menu
     *
     * @author Steven Raynham
     * @since 0.9
     *
     * @param void
     * @return null
     */
    function adminMenu()
    {
        add_options_page( 'Snippets ' . __( 'Setup' ), 'Snippets ' . __( 'Setup' ), 'level_7', basename(__FILE__), array( &$this,'optionsPage' ) );
        add_theme_page( 'Snippets', 'Snippets', 'edit_themes', basename(__FILE__), array( &$this, 'themePage' ) );
    }

    /**
     * Options page
     *
     * @author Steven Raynham
     * @since 0.9.4
     *
     * @param void
     * @return null
     */
    function optionsPage()
    {
        $message = '';
        if ( !( $snippetOptions = get_option( 'snippets' ) ) ) $snippetOptions[''] = '';
        if ( !( $snippetTypes = get_option( 'snippets_type' ) ) ) $snippetTypes[''] = $this->availableSnippetTypes['text'];
        if ( isset( $_POST['action'] ) && isset( $_POST['snippets-form'] ) ) {
            check_admin_referer( 'snippets-nonce', 'snippets-nonce' );
            switch ( $_POST['action'] ) {
                case 'save':
                    if ( isset( $_POST['doaction_delete'] ) ) {
                        if ( !empty( $_POST['snippet_name'][key( $_POST['doaction_delete'] )] ) ) $message .= '<p>Snippet ' . key( $_POST['doaction_delete'] ) . ' deleted.</p>';
                        unset( $_POST['snippet_name'][key( $_POST['doaction_delete'] )] );
                        unset( $_POST['snippet_type'][key( $_POST['doaction_delete'] )] );
                        if ( count( $_POST['snippet_name'] ) == 0 ) {
                            delete_option( 'snippets' );
                            unset($snippetOptions);
                            $snippetOptions[''] = '';
                        }
                    }
                    if ( count( $_POST['snippet_name'] ) > 0 ) {
                        unset($snippetOptions);
                        foreach ( $_POST['snippet_name'] as $name => $value ) {
                            if ( !empty( $value ) ) {
                                $snippetOptions[$value] = $_POST['snippet_value'][$name];
                                $snippetTypes[$value] = $_POST['snippet_type'][$name];
                            }
                        }
                        if ( get_option( 'snippets' ) ) update_option( 'snippets', $snippetOptions ); else add_option( 'snippets', $snippetOptions );
                        if ( get_option( 'snippets_type' ) ) update_option( 'snippets_type', $snippetTypes ); else add_option( 'snippets_type', $snippetTypes );
                        if ( isset( $_POST['doaction_save'] ) ) $message .= '<p>Snippet fields updated.</p>';
                    }
                    if ( isset( $_POST['doaction_add'] ) ) {
                        $snippetOptions[''] = '';
                        $message .= '<p>Snippet field added.</p>';
                    }
                    break;
            }
        }
?><div class='wrap'>
    <h2>Snippets <?php _e( 'Setup' ); ?></h2>
    <?php if ( !empty( $message ) ) { ?><div id="message" class="updated fade"><p><strong><?php _e( $message ); ?></strong></p></div><?php } ?>
    <h3><?php _e( 'Instructions' ); ?></h3>
    <ul>
        <li><?php _e( 'The values you enter below appear in the Snippets menu under the appearance menu.' ); ?></li>
        <li><?php _e( 'You can add the snippet value to your theme using the following code:<pre>' . htmlentities( '<?php snippets_value( \'fieldname\' ); ?>' ) . '</pre>where fieldname is the exact name of the field you enter below.' ); ?></li>
        <li><?php _e( 'An alternative is that you can use the snippet value in your posts and pages using the following markup:<pre>[snippets:fieldname]</pre>again where fieldname is the exact name of the field you enter below.' ); ?></li>
        <li><?php _e( 'Included in the plugin directory is a functions.php file which allows you to distribute themes with the snippets menu option built in, please read the comments in the source code for more information on how to use it.' ); ?></li>
    </ul>
    <hr/>
    <div>
        <form method="post" action="">
            <?php wp_nonce_field( 'snippets-nonce', 'snippets-nonce', true, true ); ?>
            <input type="hidden" name="action" value="save"/>
            <input type="hidden" name="snippets-form" value="true"/>
            <table class="form-table">
                <thead>
                    <tr>
                        <th scope="col"><strong><?php _e( 'Field name' ); ?></strong></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $snippetOptions as $name => $value ) { ?>
                    <?php //$cleanSnippetName = str_replace( ' ', '_', trim( $name ) ); ?>
                    <tr valign="top">
                        <td>
                            <input type="text" name="snippet_name[<?php echo stripslashes( $name ); ?>]" value="<?php echo stripslashes( $name ); ?>" onkeydown="if (event.keyCode==13) submitChanges();"/>
                            <?php if ( isset( $snippetType[$name] ) ) $snippetType = $snippetType[$name]; else $snippetType = $this->availableSnippetTypes['text']; ?>
                            <select name="snippet_type[<?php echo stripslashes( $name ); ?>]">
                                <?php foreach ( $this->availableSnippetTypes as $availableSnippetType => $dummy ) { ?>
                                <option value="<?php echo stripslashes( $availableSnippetType ); ?>"<?php echo stripslashes( ( ( $snippetTypes[$name] == $availableSnippetType ) ? ' selected="selected"' : '' ) ); ?>><?php echo stripslashes( $availableSnippetType ); ?></a>
                                <?php } ?>
                            </select>
                            <input type="hidden" name="snippet_value[<?php echo stripslashes( $name ); ?>]" value="<?php echo stripslashes( $value ); ?>"/>
                            <?php if ( !empty( $name ) ) { ?><input class="button-secondary action" type="submit" value="<?php _e( 'Delete' ); ?>" name="doaction_delete[<?php echo stripslashes( $name ); ?>]" onclick="return confirmDelete('<?php echo stripslashes( $name ); ?>');"/><?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
                <tfoot><tr><td><input class="button-secondary action" type="submit" value="<?php _e( 'Add new' ); ?>" name="doaction_add"/></td></tr></tfoot>
            </table>
            <p class="submit"><input class="button-primary" type="submit" id="submit_changes" name="doaction_save" value="<?php _e( 'Save changes' ); ?>"/></p>
        </form>
        <script type="text/javascript">
        /* <![CDATA[ */
            function confirmDelete(term) {
                if (confirm("Are you sure you want to delete '" + term + "'?")) {
                    return true;
                } else {
                    return false;
                }
            }
            function submitChanges() {
                var btnSubmitChanges = document.getElementById('submit_changes');
                btnSubmitChanges.click();
            }
            function stopReturnKey(evt) {
                var evt = (evt) ? evt : ((event) ? event : null);
                var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);
                if ((evt.keyCode == 13) && (node.type=="text"))  {return false;}
            }
            document.onkeypress = stopReturnKey;
        /* ]]> */
        </script>
    </div>
</div><?php
    }

    /**
     * Themes page
     *
     * @author Steven Raynham
     * @since 0.9.4
     *
     * @param void
     * @return null
     */
    function themePage()
    {
        $message = '';
        if ( $snippetOptions = get_option('snippets') ) {
            $snippetTypes = get_option('snippets_type');
        } else {
            $snippetOptions = array();
            $snippetTypes = array();
        }
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
        }
?>
<div class='wrap'>
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
                        <?php if ( isset( $snippetTypes[$name] ) ) $snippetType = $this->availableSnippetTypes[$snippetTypes[$name]]; else $snippetType = $this->availableSnippetTypes['text']; ?>
                        <td>
                            <?php
                                if ( $snippetTypes[$name] == 'checkbox' ) {
                                    echo sprintf( stripslashes( $snippetType ), stripslashes( $name ), ( ( stripslashes( $value ) == 1 ) ? ' checked="checked"': '' ) );
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
    function content( $content )
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
}
$snippets = new Snippets;

/**
 * Output snippet value
 *
 * @author Steven Raynham
 * @since 0.9.4
 *
 * @param string $fieldName
 * @return mixed
 */
if ( !function_exists( 'snippets_value' ) ) {
    function snippets_value( $fieldName , $echo = true)
    {
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