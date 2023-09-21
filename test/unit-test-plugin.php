<?php

class PluginTest extends TestCase
{
    public function test_plugin_installed()
    {
        activate_plugin( 'disciple-tools-tree-fixer/disciple-tools-tree-fixer.php' );

        $this->assertContains(
	        'disciple-tools-tree-fixer/disciple-tools-tree-fixer.php',
            get_option( 'active_plugins' )
        );
    }
}
