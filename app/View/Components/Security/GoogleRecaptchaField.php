<?php

namespace App\View\Components\Security;

use Illuminate\View\Component;
use App\Constants\ExtensionConst;
use App\Providers\Admin\ExtensionProvider;

class GoogleRecaptchaField extends Component
{

    public string $site_key;

    public $extension;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        $extension = ExtensionProvider::get()->where('slug', ExtensionConst::GOOGLE_RECAPTCHA_SLUG)->first();
        $site_key = $extension->shortcode->site_key->value ?? "";

        $this->site_key     = $site_key;
        $this->extension    = $extension;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {   
        return view('components.security.google-recaptcha-field');
    }
}
