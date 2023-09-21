<?php
/**
 * Disciple Tools Tree Fixer Menu
 */

use Kucrut\Vite;

/**
 * Class Disciple_Tools_Tree_Fixer_Menu
 */
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Tree_Fixer_Menu
 */
class Disciple_Tools_Tree_Fixer_Menu {

    public $token = 'Disciple_Tools_Tree_Fixer';

    private static $_instance = null;

    /**
     * Disciple_Tools_Tree_Fixer_Menu Instance1
     *
     * Ensures only one instance of Disciple_Tools_Tree_Fixer_Menu is loaded or can be loaded.
     *
     * @since 0.1.0
     * @static
     * @return Disciple_Tools_Tree_Fixer_Menu instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()


    /**
     * Constructor function.
     * @access  public
     * @since   0.1.0
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    } // End __construct()

    public function enqueue_scripts() {
        Vite\enqueue_asset(
            __DIR__ . '/../dist',
            'src/main.js',
            array(
                'handle' => 'tree-fixer-scripts',
                'dependencies' => array( 'wp-components' ), // Optional script dependencies. Defaults to empty array.
                'css-dependencies' => array( 'wp-components' ), // Optional style dependencies. Defaults to empty array.
            )
        );
    }


    /**
     * Loads the subnav page
     * @since 0.1
     */
    public function register_menu() {
        add_submenu_page( 'dt_extensions', 'Tree Fixer', 'Tree Fixer', 'manage_dt', $this->token, array( $this, 'content' ) );
    }

    /**
     * Menu stub. Replaced when Disciple Tools Theme fully loads.
     */
    public function extensions_menu() {}

    /**
     * Builds page contents
     * @since 0.1
     */
    public function content() {

        $group_labels = get_post_type_labels( get_post_type_object( 'groups' ) );
        $contact_labels = get_post_type_labels( get_post_type_object( 'contacts' ) );


        if ( !current_user_can( 'manage_dt' ) ) { // manage dt is a permission that is specific to Disciple Tools and allows admins, strategists and dispatchers into the wp-admin
            wp_die( 'You do not have sufficient permissions to access this page.' );
        }

        if ( isset( $_GET['tab'] ) ) {
            $tab = sanitize_key( wp_unslash( $_GET['tab'] ) );
        } else {
            $tab = 'groups';
        }

        $link = 'admin.php?page='.$this->token.'&tab=';

        ?>
        <div class="wrap">
            <h2>Tree Fixer</h2>
            <h2 class="nav-tab-wrapper">
                <a href="<?php echo esc_attr( $link ) . 'groups' ?>"
                   class="nav-tab <?php echo esc_html( ( $tab == 'groups' || !isset( $tab ) ) ? 'nav-tab-active' : '' ); ?>"><?php echo esc_html( $group_labels->name ); ?></a>
                <a href="<?php echo esc_attr( $link ) . 'contacts' ?>"
                   class="nav-tab <?php echo esc_html( ( $tab == 'contacts' || !isset( $tab ) ) ? 'nav-tab-active' : '' ); ?>"><?php echo esc_html( $contact_labels->name ); ?></a>
            </h2>

            <?php
            switch ( $tab ) {
                case 'groups':
                    $object = new Disciple_Tools_Tree_Fixer_Tab_Groups();
                    $object->content();
                    break;
                case 'contacts':
                    $object = new Disciple_Tools_Tree_Fixer_Tab_Contacts();
                    $object->content();
                    break;
                default:
                    break;
            }
            ?>

        </div><!-- End wrap -->

        <?php
    }
}
Disciple_Tools_Tree_Fixer_Menu::instance();

/**
 * Class Disciple_Tools_Tree_Fixer_Tab_General
 */
class Disciple_Tools_Tree_Fixer_Tab_Groups {
    public function content() {
        ?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <!-- Main Column -->


                        <?php $this->main_column() ?>

                        <!-- End Main Column -->
                    </div><!-- end post-body-content -->
                    <div id="postbox-container-1" class="postbox-container">
                        <!-- Right Column -->

                        <?php $this->right_column() ?>

                        <!-- End Right Column -->
                    </div><!-- postbox-container 1 -->
                    <div id="postbox-container-2" class="postbox-container">
                    </div><!-- postbox-container 2 -->
                </div><!-- post-body meta box container -->
            </div><!--poststuff end -->
        </div><!-- wrap end -->
        <?php
    }

    public function main_column() {
        ?>
        <!-- Box -->
        <table class="widefat striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Fix broken tree records', 'disciple-tools-tree-fixer' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <tree-fixer
                            endpoint="<?php echo esc_url( rest_url( 'dt-tree-fixer/v1/groups/fix' ) ); ?>"
                            translations="<?php echo esc_attr( json_encode(array(
                                'warning' => __( 'Are you sure you want to automatically fix broken tree records? This cannot be undone.', 'disciple-tools-tree-fixer' ),
                                'instructions' => __( 'Click the button below to automatically fix broken tree records.', 'disciple-tools-tree-fixer' ),
                                'startLabel' => __( 'Start', 'disciple-tools-tree-fixer' ),
                                'stopLabel' => __( 'Stop', 'disciple-tools-tree-fixer' ),
                                'logCountText' => __( 'records fixed.', 'disciple-tools-tree-fixer' ),
                                'logHeading' => __( 'Log', 'disciple-tools-tree-fixer' ),
                                'fetchError' => __( 'There was an unexpected error.', 'disciple-tools-tree-fixer' ),
                            )) ); ?>"
                        ></tree-fixer>
                    </td>
                </tr>
            </tbody>
        </table>
        <br>
        <!-- End Box -->
        <?php
    }

    public function right_column() {
        ?>
        <!-- Box -->

        <?php
    }
}


/**
 * Class Disciple_Tools_Tree_Fixer_Tab_General
 */
class Disciple_Tools_Tree_Fixer_Tab_Contacts {
    public function content() {
        ?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <!-- Main Column -->


                        <?php $this->main_column() ?>

                        <!-- End Main Column -->
                    </div><!-- end post-body-content -->
                    <div id="postbox-container-1" class="postbox-container">
                        <!-- Right Column -->

                        <?php $this->right_column() ?>

                        <!-- End Right Column -->
                    </div><!-- postbox-container 1 -->
                    <div id="postbox-container-2" class="postbox-container">
                    </div><!-- postbox-container 2 -->
                </div><!-- post-body meta box container -->
            </div><!--poststuff end -->
        </div><!-- wrap end -->
        <?php
    }

    public function main_column() {
        ?>
        <!-- Box -->
        <table class="widefat striped">
            <thead>
            <tr>
                <th><?php esc_html_e( 'Fix broken tree records', 'disciple-tools-tree-fixer' ); ?></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    <tree-fixer
                            endpoint="<?php echo esc_url( rest_url( 'dt-tree-fixer/v1/contacts/fix' ) ); ?>"
                            translations="<?php echo esc_attr( json_encode(array(
                                'warning' => __( 'Are you sure you want to automatically fix broken tree records? This cannot be undone.', 'disciple-tools-tree-fixer' ),
                                'instructions' => __( 'Click the button below to automatically fix broken tree records.', 'disciple-tools-tree-fixer' ),
                                'startLabel' => __( 'Start', 'disciple-tools-tree-fixer' ),
                                'stopLabel' => __( 'Stop', 'disciple-tools-tree-fixer' ),
                                'logCountText' => __( 'records fixed.', 'disciple-tools-tree-fixer' ),
                                'logHeading' => __( 'Log', 'disciple-tools-tree-fixer' ),
                                'fetchError' => __( 'There was an unexpected error.', 'disciple-tools-tree-fixer' ),
                            )) ); ?>"
                    ></tree-fixer>
                </td>
            </tr>
            </tbody>
        </table>
        <br>
        <!-- End Box -->
        <?php
    }

    public function right_column() {
        ?>
        <!-- Box -->

        <?php
    }
}