<?php

class Doc_Control_i18n {
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'doc-control-system',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
} 