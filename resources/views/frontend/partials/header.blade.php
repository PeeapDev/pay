@php
    $lang = selectedLang();
    $about_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::ABOUT_SECTION);
    $about = App\Models\Admin\SiteSections::getData($about_slug)->first();
    $system_default    = $default_language_code;
@endphp
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Header
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<header class="header-section position-sticky">
    <div class="header">
        <div class="header-bottom-area">
            <div class="container custom-container">
                <div class="header-menu-content">
                    <nav class="navbar navbar-expand-xl p-0">
                        <a class="site-logo site-title" href="{{ setRoute('index') }}">
                            <img src="{{ get_logo($basic_settings) }}"  data-white_img="{{ get_logo($basic_settings,'white') }}"
                            data-dark_img="{{ get_logo($basic_settings,'dark') }}"
                                alt="site-logo">
                        </a>
                        <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse"
                            data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                            aria-expanded="false" aria-label="Toggle navigation">
                            <span class="fas fa-bars"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarSupportedContent">
                            <ul class="navbar-nav main-menu me-auto">
                                @if(page_access('personal'))
                                    <li class="nav-item dropdown"><a href="javascript:void(0);" class="has-sub">{{ __("Personal") }}
                                            <i class="fas fa-angle-down"></i></a>
                                        <div class="sub-menu">
                                            <div class="sub-menu-title">
                                                <a href="{{ setRoute('user.login') }}" class="menu-name">
                                                    <h3 class="title">{{ __("User") }} <i
                                                            class="fas fa-long-arrow-alt-right ms-1"></i> </h3>
                                                </a>
                                            </div>
                                            <div class="sub-menu-wrapper">
                                                <div class="row mb-20">
                                                    @foreach ($personal ?? [] as  $item)
                                                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-10">
                                                            <a href="{{ setRoute('header.page',encrypt($item->id)) }}">
                                                                <div class="sub-menu-item">
                                                                    <div class="icon">
                                                                        <i class="{{ __($item->icon->language->$lang->icon ?? $item->icon->language->$system_default->icon) }}"></i>
                                                                    </div>
                                                                    <div class="menu-item-name">
                                                                        <h4 class="title">{{ __($item->title->language->$lang->title ?? $item->title->language->$system_default->title) }}</h4>
                                                                        <p>{{ __($item->sub_title->language->$lang->sub_title ?? $item->sub_title->language->$system_default->sub_title) }}</p>
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endif
                                @if(page_access('business'))
                                    <li class="nav-item dropdown"><a href="javascript:void(0);" class="has-sub">{{ __("Business") }}
                                            <i class="fas fa-angle-down"></i></a>
                                        <div class="sub-menu">
                                            @if(page_access('merchant'))
                                                <div class="sub-menu-title">
                                                    <a href="{{ setRoute('merchant') }}" class="menu-name">
                                                        <h3 class="title">{{ __("Merchant") }} <i class="fas fa-long-arrow-alt-right ms-1"></i> </h3>
                                                    </a>
                                                </div>
                                            @endif
                                            <div class="sub-menu-wrapper">
                                                <div class="row mb-10-none">
                                                    @foreach ($business ?? [] as  $item)
                                                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-10">
                                                            <a href="{{ setRoute('header.page',encrypt($item->id)) }}">
                                                                <div class="sub-menu-item">
                                                                    <div class="icon">
                                                                        <i class="{{ __($item->icon->language->$lang->icon ?? $item->icon->language->$system_default->icon) }}"></i>
                                                                    </div>
                                                                    <div class="menu-item-name">
                                                                        <h4 class="title">{{ __($item->title->language->$lang->title ?? $item->title->language->$system_default->title) }}</h4>
                                                                        <p>{{ __($item->sub_title->language->$lang->sub_title ?? $item->sub_title->language->$system_default->sub_title) }}</p>
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endif
                                @if(page_access('enterprice'))
                                    <li class="nav-item dropdown"><a href="javascript:void(0);" class="has-sub">{{ __("Enterprise") }}
                                            <i class="fas fa-angle-down"></i></a>
                                        <div class="sub-menu">
                                            @if(page_access('agent'))
                                                <div class="sub-menu-title">
                                                    <a href="{{setRoute('agent') }}" class="menu-name">
                                                        <h3 class="title">{{ __("Agent") }} <i class="fas fa-long-arrow-alt-right ms-1"></i> </h3>
                                                    </a>
                                                </div>
                                            @endif
                                            <div class="sub-menu-wrapper">
                                                <div class="row mb-10-none">
                                                    @foreach ($enter_price ?? [] as  $item)
                                                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-10">
                                                            <a href="{{ setRoute('header.page',encrypt($item->id)) }}">
                                                                <div class="sub-menu-item">
                                                                    <div class="icon">
                                                                        <i class="{{ __($item->icon->language->$lang->icon ?? $item->icon->language->$system_default->icon) }}"></i>
                                                                    </div>
                                                                    <div class="menu-item-name">
                                                                        <h4 class="title">{{ __($item->title->language->$lang->title ?? $item->title->language->$system_default->title) }}</h4>
                                                                        <p>{{ __($item->sub_title->language->$lang->sub_title ?? $item->sub_title->language->$system_default->sub_title) }}</p>
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endif
                                @if(page_access('company'))
                                    <li class="nav-item dropdown"><a href="javascript:void(0);" class="has-sub">{{ __("Company") }}
                                            <i class="fas fa-angle-down"></i></a>
                                        <div class="sub-menu">
                                            <div class="sub-menu-wrapper">
                                                <div class="row mb-10-none">
                                                    @foreach ($company ?? [] as  $item)
                                                            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 mb-10">
                                                                <a href="{{ setRoute( $item->slug ?? '') }}">
                                                                    <div class="sub-menu-item">
                                                                        <div class="icon">
                                                                            <i class="{{ __($item->icon->language->$lang->icon ?? $item->icon->language->$system_default->icon) }}"></i>
                                                                        </div>
                                                                        <div class="menu-item-name">
                                                                            <h4 class="title">{{ __($item->title->language->$lang->title ?? $item->title->language->$system_default->title) }}</h4>
                                                                            <p>{{ __($item->sub_title->language->$lang->sub_title ?? $item->sub_title->language->$system_default->sub_title) }}</p>
                                                                        </div>
                                                                    </div>
                                                                </a>
                                                            </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endif

                                @if(page_access('developer'))
                                    <li class="nav-item dropdown">
                                        <a href="{{ setRoute("developer.index") }}" class="has-sub {{ menuActive('developer.index') }}">{{ __("Developer") }}</a>
                                    </li>
                                @endif

                            </ul>
                            <div class="navbar-right">
                                <ul>
                                    @if(page_access('contact'))
                                        <li class="nav-item">
                                            <a href="{{ setRoute('contact') }}" class="help-btn {{ menuActive('contact') }}">{{ __("Help") }}</a>
                                        </li>
                                    @endif
                                </ul>
                                <div class="lang-select">
                                    @php
                                    $session_lan = session('local')??get_default_language_code();
                                    @endphp
                                    <select class="form--control langSel nice-select">
                                        @foreach($__languages as $item)
                                        <option value="{{$item->code}}" @if( $session_lan == $item->code) selected  @endif>{{ __($item->name) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="header-action">
                                    @if(auth('web')->check())
                                        <a href="{{ setRoute('user.dashboard') }}" class="btn--base btn-auth">{{ __("Dashboard") }}</a>
                                     @elseif(auth('agent')->check())
                                        <a href="{{ setRoute('agent.dashboard') }}" class="btn--base btn-auth">{{ __("Dashboard") }}</a>
                                    @elseif(auth('merchant')->check())
                                        <a href="{{ setRoute('merchant.dashboard') }}" class="btn--base btn-auth">{{ __("Dashboard") }}</a>
                                    @else
                                        <a href="{{ setRoute('user.login') }}" class="btn--base btn">{{ __("Log In") }}</a>
                                        <a href="{{ setRoute('user.register') }}" class="btn--base btn-auth">{{ __("sign Up") }}</a>
                                    @endif
                                </div>


                            </div>

                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</header>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End Header
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
