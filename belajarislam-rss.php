<?php
/**
 * @package belajarislam_rss
 * @version 1.0
 */
/*
Plugin Name: Belajar Islam RSS Feed
Plugin URI:  http://andrey.web.id/plugin
Description: Plugin for fetching rss feed from belajarislam.com
Author:      Andrey Ferriyan
Version:     1.0
Author URI:  http://andrey.web.id/about-andrey-ferriyan
License:     GPL2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

class AN_Belajarislam_Rss extends WP_Widget {

    /*
     * Class constructor
     */
    function __construct()
    {
        parent::__construct(
            'AN_Belajarislam_Rss', // Base ID
            __('Belajar Islam RSS', 'an_belajarislam_widget'), // Name
            array( 'description' => __( 'belajarislam.com RSS Widget', 'an_belajarislam_widget' ), ) // Args
        );

    }


    // for widget
    function form($instance)
    {
        if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ];
        }
        else {
            $title = __( 'New title', 'an_belajarislam_widget' );
        }
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <?php
    }

    // for widget
    function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        return $instance;

    }

    // for widget
    function widget($args, $instance)
    {
        global $wpdb;
        $maxrow = $wpdb->get_row( 'SELECT id,maxlist FROM wp_belajarislam_rss' );
        include_once( ABSPATH . WPINC . '/feed.php' );

        echo $args['before_widget'];
        include_once( ABSPATH . WPINC . '/feed.php' );
        $rss = fetch_feed( 'http://belajarislam.com/feed/' );

        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
        }


        if ( is_wp_error( $rss ) ){

                echo $rss->get_error_message();

        } else {

                // Figure out how many total items there are, but limit it to 5.
                $maxitems = $rss->get_item_quantity( $maxrow->maxlist );

                // Build an array of all the items, starting with element 0 (first element).
                $rss_items = $rss->get_items( 0, $maxitems );
        }

        if ( $maxitems == 0 )
            {

                echo __( 'No items', 'an_belajarislam_widget' );

            }
        else
            {

                ?>
                <?php foreach ( $rss_items as $item ) : ?>
                    <li>
                        <a href="<?php echo esc_url( $item->get_permalink() ); ?>"
                           title="<?php printf( __( 'Posted %s', 'my-text-domain' ), $item->get_date('j F Y | g:i a') ); ?>">
                            <?php echo esc_html( $item->get_title() ); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php
            }

        echo $args['after_widget'];

    }

}

/*
 * Register activation
 */
function an_belajarislam_registerall()
{
    add_action( 'admin_menu', 'an_belajarislam_add_menu' );
    register_activation_hook( __FILE__, 'an_belajarislam_rss_install' );
    register_activation_hook( __FILE__, 'an_belajarislam_rss_install_data' );
    register_deactivation_hook( __FILE__, 'an_belajarislam_rss_uninstall' );
}

/*
     * Adding menu for plugin
     */
function an_belajarislam_add_menu()
{

    add_menu_page( 'Belajar Islam RSS', 'Belajar Islam', 'manage_options', 'belajarislam-rss-dashboard', 'an_page_file_path'
        , plugins_url('images/belajarislam-logo.png', __FILE__ ), '1.0'
    );
    add_submenu_page( 'belajarislam-rss-dashboard', 'Belajar Islam' . ' Dashboard', ' Dashboard', 'manage_options', 'belajarislam-rss-dashboard', 'an_page_file_path' );

}

/*
 * Actions performs on install plugin
 */
function an_belajarislam_rss_install()
{

    global $wpdb;

    $table_name = $wpdb->prefix . "belajarislam_rss";
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
          id mediumint(9) NOT NULL AUTO_INCREMENT,
          maxlist INTEGER NOT NULL,
          UNIQUE KEY id (id)
        ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

}

/*
 * Actions performs on uninstall
 */
function an_belajarislam_rss_uninstall()
{

    delete_option('an_belajarislam_rss_data');

}

function an_belajarislam_rss_install_data()
{
    global $wpdb;

    $table_name = $wpdb->prefix . "belajarislam_rss";

    $wpdb->insert( $table_name, array(
        'id' => 1,
        'manylists' => 1
    ) );
}

/*
     * File path
     */
function an_page_file_path()
{

    $screen = get_current_screen();
    if ( strpos( $screen->base, 'belajarislam-rss-dashboard' ) !== false ) {
        include( dirname(__FILE__) . '/belajarislam-rss-dashboard.php' );
    } else {
        include( dirname(__FILE__) . '/belajarislam-rss-dashboard.php' );
    }

}


function an_belajarislam_rss_form($id,$maxList)
{
    global $wpdb;
    $table_name = $wpdb->prefix . "belajarislam_rss";
    //$maxrow = $wpdb->get_row( $wpdb->prepare( "SELECT id,maxlist FROM %s", $table_name ) );
    $maxrow = $wpdb->get_row( 'SELECT id,maxlist FROM wp_belajarislam_rss' );
    //echo $maxrow->id;

    echo '
        <form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
        <input type="hidden" name="id" value="' . (isset( $_POST['id'] ) ? $id : $maxrow->id ) . '" />
            <div>
                <label for="maxList">Maximum List</label>
            </div>
            <div>
                <input type="text" name="maxList" value="' . (isset( $_POST['maxList'] ) ? $maxList : $maxrow->maxlist ) . '"/>
            </div>
            <div>
                <input type="submit" name="submit" value="Save" />
            </div>
        </form>
      ';

}

function an_belajarislam_rss_form_complete()
{

    global $wpdb;
    $rssdata = array(
        'id'      => $_POST['id'],
        'maxlist' => $_POST['maxList']
    );

    $wpdb->replace(
        $wpdb->prefix . "belajarislam_rss",
        $rssdata
    );
    //echo "berhasil"; //for debugging purpose
    //echo $wpdb->insert_id;

}

function an_belajarislam_rss_form_handler()
{

    if ( isset($_POST['submit'] ) ) {
        an_belajarislam_rss_form_complete();
        //echo "berhasil"; // for debugging purpose
    }

}


function AN_Belajarislam_Widget()
{
    register_widget( 'AN_Belajarislam_Rss' );
}

add_action( 'widgets_init', 'AN_Belajarislam_Widget' );
new AN_Belajarislam_Rss();
an_belajarislam_registerall();


