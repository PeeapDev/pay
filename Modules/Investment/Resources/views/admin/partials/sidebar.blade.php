@if (isActive('Investment') && (Common::has_permission(auth('admin')->user()->id, 'view_investment_plan') || Common::has_permission(auth('admin')->user()->id, 'view_investment') || Common::has_permission(auth('admin')->user()->id, 'view_investment_setting')))
    <!-- Investment -->
    <li <?= (isset($menu) && $menu == 'investments') ? ' class="active child"' : 'child'?> >
        <a href="#">
            <svg id="e1WnpxAjyv41" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="16" class="svgicon" stroke="currentColor" viewBox="0 -20 512 512" shape-rendering="geometricPrecision" text-rendering="geometricPrecision">
                <path id="e1WnpxAjyv42" stroke-width="2" d="M452,0L60,0C26.914062,0,0,26.914062,0,60L0,412C0,445.085938,26.914062,472,60,472L452,472C485.085938,472,512,445.085938,512,412L512,60C512,26.914062,485.085938,0,452,0ZM60,40L452,40C463.027344,40,472,48.972656,472,60L472,120L40,120L40,60C40,48.972656,48.972656,40,60,40ZM452,432L60,432C48.972656,432,40,423.027344,40,412L40,160L472,160L472,412C472,423.027344,463.027344,432,452,432ZM70,80C70,68.953125,78.953125,60,90,60C101.046875,60,110,68.953125,110,80C110,91.046875,101.046875,100,90,100C78.953125,100,70,91.046875,70,80ZM140,80C140,68.953125,148.953125,60,160,60C171.046875,60,180,68.953125,180,80C180,91.046875,171.046875,100,160,100C148.953125,100,140,91.046875,140,80ZM346.640625,185.859375L416.785156,256L346.640625,326.140625L318.359375,297.859375L340.214844,276L235,276L235,236L340.214844,236L318.359375,214.140625ZM171.785156,316L275,316L275,356L171.785156,356L193.640625,377.859375L165.359375,406.140625L95.214844,336L165.359375,265.859375L193.640625,294.140625ZM171.785156,316"></path>
            </svg>
            <span>{{ __('Investment') }}</span>
            <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
        </a>
        <ul class="treeview-menu">
            @if (Common::has_permission(auth('admin')->user()->id, 'view_investment_plan'))
                <!-- Investment Plan -->
                <li <?= isset($sub_menu) && $sub_menu == 'investment_plans' ? ' class="active child"' : 'child'?> >
                    <a href="{{ route('investment_plans.list') }}"><i class="fa fa-exchange"></i><span>{{ __('Manage Plans') }}</span></a>
                </li>
            @endif
            @if (Common::has_permission(auth('admin')->user()->id, 'view_investment'))
                <!-- investments -->
                <li <?= isset($sub_menu) && $sub_menu == 'invests' ? ' class="active child"' : 'child'?> >
                    <a href="{{ route('investment.list') }}"><i class="fa fa-arrow-down"></i><span>{{ __('Investments') }}</span></a>
                </li>
            @endif
            @if (Common::has_permission(auth('admin')->user()->id, 'view_investment_setting'))
                <!-- Investment Settings -->
                <li <?= isset($sub_menu) && $sub_menu == 'investment_settings' ? ' class="active child"' : 'child'?> >
                    <a href="{{ route('investment_setting.add') }}"><i class="fa fa-cogs"></i><span>{{ __('Settings') }}</span></a>
                </li>
            @endif
        </ul>
    </li>
@endif