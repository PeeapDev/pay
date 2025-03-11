<?php

namespace App\Providers\Admin;


class ExtensionProvider {

    public $extension;

    public function __construct($extensions = null)
    {
        $this->extension = $extensions;
    }


    public function set($extensions) {
        return $this->extension = $extensions;
    }
    
    public function getData() {
        return $this->extension;
    }

    public static function get() {
        return app(ExtensionProvider::class)->getData();
    }
}