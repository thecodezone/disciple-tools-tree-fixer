<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Disciple_Tools_Tree_Fixer_Endpoints
{
    /**
     * @link https://github.com/DiscipleTools/Documentation/blob/master/theme-core/capabilities.md
     * @var string[]
     */
    public $permissions = array( 'manage_dt' );


    public function add_api_routes() {
        $namespace = 'dt-tree-fixer/v1';
        register_rest_route(
            $namespace, '/groups/fix', array(
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => array( $this, 'fix_group_tree' ),
                'permission_callback' => function ( WP_REST_Request $request ) {
                    return $this->has_permission();
                },
            )
        );

        register_rest_route(
            $namespace, '/contacts/fix', array(
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => array( $this, 'fix_contact_tree' ),
                'permission_callback' => function ( WP_REST_Request $request ) {
                    return $this->has_permission();
                },
            )
        );
    }


    public function fix_group_tree( WP_REST_Request $request ) {
        $queries = array(
            dt_queries()->tree( 'group_all' ),
        );

        $errors = array_filter($queries, function ( $error ) {
            return is_wp_error( $error );
        });

        if ( count( $errors ) === 0 ) {
            return new WP_REST_Response( array(
                'log' => array( __( 'The tree is healthy.', 'disciple-tools-tree-fixer' ) ),
                'link' => null,
                'continue' => false,
            ), 200 );
        }
        $error = $errors[0];
        if ( !is_wp_error( $error ) ) {
            return new WP_REST_Response( array(
                'log' => array( __( 'The tree is healthy.', 'disciple-tools-tree-fixer' ) ),
                'link' => null,
                'continue' => false,
            ), 200 );
        }

        $record = $error->get_error_data()['record'];
        $group = DT_Posts::get_post( 'groups', $record );
        $log = array( $error->get_error_message() );

        if ( ! $group ) {
            $log[] = __( 'The record does not exist.', 'disciple-tools-tree-fixer' );
            return array(
                'log' => $log,
                'record' => $record,
                'continue' => true,
            );
        }

        $parent_groups = array_column( $group['parent_groups'], 'ID' );
        $child_groups = array_column( $group['child_groups'], 'ID' );
        $circular_groups = array_intersect( $parent_groups, $child_groups );

        foreach ( $circular_groups as $circular_group ) {
            $log[] = sprintf( __( 'The group %s is a circular reference.', 'disciple-tools-tree-fixer' ), $circular_group );
        }

        if ( count( $circular_groups ) ) {

            $log[] = 'Removing circular references from child groups.';

            $result = DT_Posts::update_post( 'groups', $record, array(
                'child_groups' => array(
                    'values' => array_map(function ( $id ) {
                        return array(
                            'value' => $id,
                        );
                    }, array_diff( $child_groups, $circular_groups )),
                    'force_values' => true,
                ),
            ));

            if ( is_wp_error( $result ) ) {
                $log[] = $result->get_error_message();
                return array(
                    'log' => $log,
                    'record' => $record,
                    'continue' => false,
                );
            }

            $log[] = __( 'The circular references have been removed.', 'disciple-tools-tree-fixer' );

            return array(
                'log' => $log,
                'record' => $record,
                'continue' => true,
            );
        }

        $log[] = __( 'Re-saving parent and child groups to remove stale data.', 'disciple-tools-tree-fixer' );

        DT_Posts::update_post( 'groups', $record, array(
            'parent_groups' => array(
                'values' => array(),
                'force_values' => true,
            ),
            'child_groups' => array(
                'values' => array(),
                'force_values' => true,
            ),
        ));

        DT_Posts::update_post( 'contacts', $record, array(
            'parent_groups' => array(
                'values' => array_map(function ( $id ) {
                    return array(
                        'value' => $id,
                    );
                }, $parent_groups),
                'force_values' => true,
            ),
            'child_groups' => array(
                'values' => array_map(function ( $id ) {
                    return array(
                        'value' => $id,
                    );
                }, $child_groups),
                'force_values' => true,
            ),
        ));


        return array(
            'log' => $log,
            'record' => $record,
            'continue' => true,
        );
    }

    public function fix_contact_tree( WP_REST_Request $request ) {

        $error = dt_queries()->tree( 'coaching_all' );
        if ( !is_wp_error( $error ) ) {
            return new WP_REST_Response( array(
                'log' => array( __( 'The tree is healthy.', 'disciple-tools-tree-fixer' ) ),
                'link' => null,
                'continue' => false,
            ), 200 );
        }

        $record = $error->get_error_data()['record'];
        $contact = DT_Posts::get_post( 'contacts', $record );
        $log = array( $error->get_error_message() );

        if ( ! $contact ) {
            $log[] = __( 'The record does not exist.', 'disciple-tools-tree-fixer' );
            return array(
                'log' => $log,
                'record' => $record,
                'continue' => true,
            );
        }

        $coached_by = array_column( $contact['coached_by'], 'ID' );
        $coaching = array_column( $contact['coaching'], 'ID' );
        $circular_contacts = array_intersect( $coached_by, $coaching );

        foreach ( $circular_contacts as $circular_contact ) {
            $log[] = sprintf( __( 'The contact %s has a circular coaching reference.', 'disciple-tools-tree-fixer' ), $circular_contact );
        }

        if ( count( $circular_contacts ) ) {

            $log[] = 'Removing circular coaching records.';

            $result = DT_Posts::update_post( 'contacts', $record, array(
                'coaching' => array(
                    'values' => array_map(function ( $id ) {
                        return array(
                            'value' => $id,
                        );
                    }, array_diff( $coaching, $circular_contacts )),
                    'force_values' => true,
                ),
            ));

            if ( is_wp_error( $result ) ) {
                $log[] = $result->get_error_message();
                return array(
                    'log' => $log,
                    'record' => $record,
                    'continue' => false,
                );
            }

            $log[] = __( 'The circular references have been removed.', 'disciple-tools-tree-fixer' );

            return array(
                'log' => $log,
                'record' => $record,
                'continue' => true,
            );
        }

        $log[] = __( 'Re-saving coaches to remove stale data.', 'disciple-tools-tree-fixer' );

        DT_Posts::update_post( 'contacts', $record, array(
            'coached_by' => array(
                'values' => array(),
                'force_values' => true,
            ),
            'coaching' => array(
                'values' => array(),
                'force_values' => true,
            ),
        ));

        DT_Posts::update_post( 'contacts', $record, array(
            'coached_by' => array(
                'values' => array_map(function ( $id ) {
                    return array(
                        'value' => $id,
                    );
                }, $coached_by),
                'force_values' => true,
            ),
            'coaching' => array(
                'values' => array_map(function ( $id ) {
                    return array(
                        'value' => $id,
                    );
                }, $coaching),
                'force_values' => true,
            ),
        ));


        return array(
            'log' => $log,
            'record' => $record,
            'continue' => true,
        );
    }

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()
    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'add_api_routes' ) );
    }
    public function has_permission(){
        $pass = false;
        foreach ( $this->permissions as $permission ){
            if ( current_user_can( $permission ) ){
                $pass = true;
            }
        }
        return $pass;
    }
}
Disciple_Tools_Tree_Fixer_Endpoints::instance();
