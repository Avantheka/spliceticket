   @php
       $setting = Utility::settings();
       if (\Auth::user()->type == 'Admin') {
           $logo = $setting['company_logo'];

           if ($setting['cust_darklayout'] == 'on') {
               $logo = $setting['company_logo_light'];
           }
       } else {
           $logo = Utility::get_superadmin_logo();

       }
       $logos = \App\Models\Utility::get_file('uploads/logo/');
       $emailTemplate = App\Models\EmailTemplate::first();
   @endphp

   @if (isset($setting['cust_theme_bg']) && $setting['cust_theme_bg'] == 'on')
       <nav class="dash-sidebar light-sidebar transprent-bg">
       @else
           <nav class="dash-sidebar light-sidebar">
   @endif

   <div class="navbar-wrapper">
       <div class="m-header main-logo">
           <a href="{{ route('home') }}" class="b-brand">
               <!-- ========   change your logo hear   ============ -->
               <img src="{{ $logos . (isset($logo) && !empty($logo) ? $logo .'?'.time() : 'logo-dark.png'.'?'.time()) }}"
                   alt="{{ config('app.name', 'TicketGo SaaS') }}" class="logo logo-lg">
           </a>
       </div>
       <div class="navbar-content">
           <ul class="dash-navbar">
               <li class="dash-item {{ request()->is('*dashboard*') ? ' active' : '' }}">
                   <a href="{{ route('home') }}" class="dash-link "><span class="dash-micon"><i
                               class="ti ti-home"></i></span><span class="dash-mtext">{{ __('Dashboard') }}</span></a>
               </li>

               @if (\Auth::user()->type == 'Super Admin' || \Auth::user()->type == 'Admin')
                   <li class="dash-item {{ request()->is('*users*') ? ' active' : '' }}">
                       <a href="{{ route('users.index') }}" class="dash-link"><span class="dash-micon"><i
                                   class="ti ti-users"></i></span><span
                               class="dash-mtext">{{ __('Users') }}</span></a>
                   </li>
               @endif


               @can('manage-tickets')
                   <li class="dash-item {{ request()->is('*ticket*') ? ' active' : '' }}">
                       <a href="{{ route('tickets.index') }}" class="dash-link"><span class="dash-micon"><i
                                   class="ti ti-ticket"></i></span><span class="dash-mtext">{{ __('Tickets') }}</span></a>
                   </li>
               @endcan


               @if(\Auth::user()->type == "Admin")
               @can('manage-faq')
                   @if (Utility::getSettingValByName('FAQ') == 'on')
                       <li class="dash-item {{ request()->is('*faq*') ? ' active' : '' }}">
                           <a href="{{ route('faq')}}" class="dash-link"><span class="dash-micon"><i
                                       class="ti ti-question-mark"></i></span><span
                                   class="dash-mtext">{{ __('FAQ') }}</span></a>
                       </li>
                   @endif
               @endcan
               @endif

               @if (\Auth::user()->type != 'Super Admin')
                   @if (Utility::settingsById(1)['CHAT_MODULE'] == 'yes')
                       <li class="dash-item {{ request()->is('*Messenger*') ? ' active' : '' }}">
                           <a href="{{ route('chats') }}" class="dash-link"><span class="dash-micon"><i
                                       class="ti ti-brand-hipchat"></i></span><span
                                   class="dash-mtext">{{ __('Messenger') }}</span></a>
                       </li>
                   @endif
               @endif

               @if(\Auth::user()->type == "Admin")
               @can('manage-knowledge')
                   @if (Utility::getSettingValByName('Knowlwdge_Base') == 'on')
                       <li class="dash-item {{ request()->is('*knowledge*') ? ' active' : '' }}">
                           <a href="{{ route('knowledge') }}" class="dash-link"><span class="dash-micon"><i
                                       class="ti ti-school"></i></span><span
                                   class="dash-mtext">{{ __('Knowledge Base') }}</span></a>
                       </li>
                   @endif
               @endcan
               @endif




               @if (\Auth::user()->type == 'Super Admin' || \Auth::user()->type == 'Admin')
                   <li
                       class="dash-item {{ Request::segment(1) == 'plan' || Request::route()->getName() == 'plan.payment' ? 'active' : '' }}">
                       <a class="dash-link" href="{{ route('plan.index') }}">
                           <span class="dash-micon"><i class="ti ti-trophy"></i></span><span
                               class="dash-mtext">{{ __('Plan') }}</span>
                       </a>
                   </li>
               @endif

               @if (\Auth::user()->type == 'Super Admin')
                   <li
                       class="dash-item  {{ \Request::route()->getName() == 'plan_request' || \Request::route()->getName() == 'plan_request.show' || \Request::route()->getName() == 'plan_request.edit' ? ' active' : '' }}">
                       <a href="{{ route('plan_request.index') }}" class="dash-link">
                           <span class="dash-micon"><i class="ti ti-brand-telegram"></i></span><span
                               class="dash-mtext">{{ __('Plan Request') }}</span>
                       </a>
                   </li>
                    <li class="dash-item   {{ Request::segment(1) == 'referral-program' ? 'active' : '' }}">
                        <a href="{{ route('referral-programs.index') }}" class="dash-link">
                            <span class="dash-micon"><i class="ti ti-trophy"></i></span><span
                                class="dash-mtext">{{ __('Referral Program') }}</span>
                        </a>
                    </li>

                   <li
                   class="dash-item  {{ \Request::route()->getName() == 'custom_domain_request' || \Request::route()->getName() == 'custom_domain_request.show' || \Request::route()->getName() == 'custom_domain_request.edit' ? ' active' : '' }}">
                   <a href="{{ route('custom_domain_request.index') }}" class="dash-link">
                       <span class="dash-micon"><i class="ti ti-browser"></i></span><span
                           class="dash-mtext">{{ __('Domain Request') }}</span>
                   </a>
               </li>
               @endif

               @if(\Auth::user()->type == 'Admin')
                <li class="dash-item {{ (\Request::route()->getName()=='referral-program') ? ' active' : '' }}">
                    <a href="{{route('referral-program.company')}}" class="dash-link"><span class="dash-micon"><i class="ti ti-trophy"></i></span><span class="dash-mtext">{{__('Referral Program')}}</span></a>
                </li>
               @endif

               @if (\Auth::user()->type == 'Super Admin')
                   <li class="dash-item {{ Request::segment(1) == 'coupons' ? 'active' : '' }}">
                       <a class="dash-link" href="{{ route('coupon.index') }}">
                           <span class="dash-micon"><i class="ti ti-gift"></i></span><span
                               class="dash-mtext">{{ __('Coupons') }}</span>
                       </a>
                   </li>
               @endif

               @if (\Auth::user()->type == 'Super Admin' || \Auth::user()->type == 'Admin')
                   <li class="dash-item {{ \Request::route()->getName() == 'order' ? ' active' : '' }}">
                       <a href="{{ route('order.index') }}" class="dash-link">
                           <span class="dash-micon"><i class="ti ti-shopping-cart-plus"></i></span><span
                               class="dash-mtext">{{ __('Order') }}</span>
                       </a>
                   </li>
               @endif

               @if (\Auth::user()->type == 'Super Admin')
                   <li class="dash-item {{ request()->is('*email*') ? ' active' : '' }}">
                       <a href="{{ route('manage.email.language', [$emailTemplate->id, \Auth::user()->lang]) }}"
                           class="dash-link"><span class="dash-micon"><i class="ti ti-template"></i></span><span
                               class="dash-mtext">{{ __('Email Template') }}</span></a>
                   </li>
               @endif
               @if (\Auth::user()->type == 'Admin')
               <li class="dash-item">
                           <a href="{{ route('category') }}" class="dash-link"><span class="dash-micon"><i
                            class="ti ti-layout-2"></i></span><span
                        class="dash-mtext">{{ __('Setup') }}</span></a>


               </li>
               @endif

               @if (\Auth::user()->type == 'Admin')
               <li class="dash-item {{ (\Request::route()->getName()=='notification-templates') ? ' active' : '' }}">
                <a href="{{route('notification-templates.index')}}" class="dash-link"><span class="dash-micon"><i class="ti ti-notification"></i></span><span class="dash-mtext">{{__('Notification')}}</span></a>
                </li>
              @endif


              @if (\Auth::user()->type == 'Super Admin')
                @include('landingpage::menu.landingpage')
              @endif

               @if (\Auth::user()->type == 'Super Admin' || \Auth::user()->type == 'Admin')
                   <li class="dash-item {{ request()->is('*setting*') ? ' active' : '' }}">
                       <a href="{{ route('settings.index') }}" class="dash-link"><span class="dash-micon"><i
                                   class="ti ti-settings"></i></span><span
                               class="dash-mtext">{{ __('Settings') }}</span></a>
                   </li>
               @endif

           </ul>
       </div>
   </div>
   </nav>
