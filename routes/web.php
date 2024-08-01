<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\FaqKnwlController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ConversionController;
use App\Http\Controllers\KnowledgeController;
use App\Http\Controllers\KnowledgebaseCategoryController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\EmailTemplateLangController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\PlanRequestController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaypalController;
use App\Http\Controllers\StripePaymentController;
use App\Http\Controllers\PaystackPaymentController;
use App\Http\Controllers\PaymentWallController;
use App\Http\Controllers\FlutterwavePaymentController;
use App\Http\Controllers\RazorpayPaymentController;
use App\Http\Controllers\PaytmPaymentController;
use App\Http\Controllers\MercadoPaymentController;
use App\Http\Controllers\MolliePaymentController;
use App\Http\Controllers\SkrillPaymentController;
use App\Http\Controllers\CoingatePaymentController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\OperatinghoursController;
use App\Http\Controllers\ToyyibpayController;
use App\Http\Controllers\PriorityController;
use App\Http\Controllers\SLAPoliciyController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\PayfastController;
use App\Http\Controllers\BanktransferController;
use App\Http\Controllers\NotificationTemplatesController;
use App\Http\Controllers\AiTemplateController;
use App\Http\Controllers\IyziPayController;
use App\Http\Controllers\SspayController;
use App\Http\Controllers\PaytabController;
use App\Http\Controllers\BenefitPaymentController;
use App\Http\Controllers\CashfreeController;
use App\Http\Controllers\AamarpayController;
use App\Http\Controllers\CinetpayPaymentController;
use App\Http\Controllers\CustomDomainRequestController;
use App\Http\Controllers\FedapayPaymentController;
use App\Http\Controllers\PaytrController;
use App\Http\Controllers\YooKassaController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\NepalstePaymentController;
use App\Http\Controllers\PaiementProController;
use App\Http\Controllers\PayHerePaymentController;
use App\Http\Controllers\ReferralProgramController;
use App\Http\Controllers\XenditPaymentController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

require __DIR__ . '/auth.php';


Route::get('/', [HomeController::class, 'index'])->middleware('XSS');


Route::resource('users', UserController::class)->middleware(['auth', 'XSS']);

Route::any('/cookie-consent', [SettingsController::class,'CookieConsent'])->name('cookie-consent');
Route::controller(FaqKnwlController::class)->middleware(['DomainRequest', 'XSS'])->group(function () {

    Route::get('{slug}/tickets', 'ticket_slug')->name('{slug}/tickets');
    Route::get('{slug}/faqs', 'faqs')->name('{slug}/faqs');
    Route::get('{slug}/searches', 'searches')->name('{slug}/searches');
    Route::post('{slug}/ticketsearches', 'ticketsSearches')->name('{slug}/tickets.searches');
    Route::get('{slug}/knowledges', 'knowledges')->name('{slug}/knowledges');
    Route::get('{slug}/knowledgedesc', 'knowledgeDescription')->name('{slug}/knowledgedesc');
});



Route::controller(HomeController::class)->middleware('XSS')->group(function () {

    Route::get('home', 'index')->name('home');
    Route::post('home', 'store')->name('home.store');
    // Route::get('search/{lang?}', 'search')->name('search');
    // Route::post('search', 'ticketSearch')->name('ticket.search');
    Route::get('tickets/{id}', 'view')->name('home.view');
    Route::post('ticket/{id}', 'reply')->name('home.reply');
    Route::get('faq', 'faq')->name('faq');
    Route::get('knowledge', 'knowledge')->name('knowledge');
    Route::get('knowledgedesc', 'knowledgeDescription')->name('knowledgedesc');
});

Route::get('search/{lang?}', [HomeController::class, 'search'])->name('search');
Route::post('ticketsearch', [HomeController::class, 'ticketSearch'])->name('ticket.search');


Route::group(['middleware' => ['verified']], function () {

    // impersonating
    Route::get('login-with-company/exit', [UserController::class, 'ExitCompany'])->name('exit.company');
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware(['auth', 'XSS']);

    Route::get('lang/clear', [LanguageController::class, 'clear'])->name('lang.clear');
    Route::get('lang/create', [LanguageController::class, 'create'])->name('lang.create');
    Route::post('lang/create', [LanguageController::class, 'store'])->name('lang.store');
    Route::get('lang/{lang}', [LanguageController::class, 'manageLanguage'])->name('lang.index')->middleware(['XSS']);
    Route::post('lang/{lang}', [LanguageController::class, 'storeData'])->name('lang.store.data');
    Route::get('lang/change/{lang}', [LanguageController::class, 'update'])->name('lang.update')->middleware(['XSS']);
    Route::delete('lang/{lang}', [LanguageController::class, 'destroyLang'])->name('lang.destroy');
    Route::post('disable-language',[LanguageController::class,'disableLang'])->name('disablelanguage')->middleware(['auth','XSS']);

    Route::get('category/create', [CategoryController::class, 'create'])->name('category.create')->middleware(['XSS']);
    Route::post('category', [CategoryController::class, 'store'])->name('category.store')->middleware(['XSS']);
    Route::get('category', [CategoryController::class, 'index'])->name('category')->middleware(['XSS']);
    Route::get('category/{id}/edit', [CategoryController::class, 'edit'])->name('category.edit')->middleware(['XSS']);
    Route::delete('category/{id}/destroy', [CategoryController::class, 'destroy'])->name('category.destroy')->middleware(['XSS']);
    Route::put('category/{id}/update', [CategoryController::class, 'update'])->name('category.update')->middleware(['XSS']);


    Route::controller(MessageController::class)->middleware('auth', 'XSS')->group(function () {

        // Message Route
        Route::get('chat', [MessageController::class, 'index'])->name('chats');
        Route::get('message/{id}', [MessageController::class, 'getMessage'])->name('message');
        Route::delete('delete-user-message/{id}', [MessageController::class, 'deleteUserMessage'])->name('delete.user.message');
        Route::post('message', [MessageController::class, 'sendMessage']);

    });

    Route::post('/custom-fields', [SettingsController::class, 'storeCustomFields'])->name('custom-fields.store')->middleware(['XSS']);

    Route::any('users-reset-password/{id}', [UserController::class, 'userPassword'])->name('user.reset')->middleware(['XSS']);

    Route::post('users-reset-password/{id}', [UserController::class, 'userPasswordReset'])->name('user.password.update')->middleware(['XSS']);

    Route::get('user-login/{id}', [UserController::class, 'LoginManage'])->name('users.login');


    // Route::post('users',[UserController::class,'store'])->name('users.store')->middleware(['auth','XSS']);

    Route::get('ticket/create', [TicketController::class, 'create'])->name('tickets.create')->middleware(['auth', 'XSS']);

    Route::post('ticket', [TicketController::class, 'store'])->name('tickets.store')->middleware(['auth', 'XSS']);

    Route::get('ticket', [TicketController::class, 'index'])->name('tickets.index')->middleware(['auth', 'XSS']);

    Route::get('ticket/{id}/edit', [TicketController::class, 'editTicket'])->name('tickets.edit')->middleware(['auth', 'XSS']);

    Route::delete('ticket/{id}/destroy', [TicketController::class, 'destroy'])->name('tickets.destroy')->middleware(['auth', 'XSS']);

    Route::delete('ticket-attachment/{tid}/destroy/{id}', [TicketController::class, 'attachmentDestroy'])->name('tickets.attachment.destroy')->middleware(['auth', 'XSS']);

    Route::put('ticket/{id}/update', [TicketController::class, 'updateTicket'])->name('tickets.update')->middleware(['auth', 'XSS']);

    Route::get('group',[GroupController::class,'index'])->name('group')->middleware(['auth', 'XSS']);

    Route::get('group/create', [GroupController::class, 'create'])->name('groups.create')->middleware(['auth', 'XSS']);

    Route::post('group', [GroupController::class, 'store'])->name('groups.store')->middleware(['auth', 'XSS']);

    Route::get('group/{id}/edit', [GroupController::class, 'edit'])->name('groups.edit')->middleware(['auth', 'XSS']);

    Route::put('group/{id}/update', [GroupController::class, 'update'])->name('groups.update')->middleware(['auth', 'XSS']);

    Route::delete('group/{id}/destroy', [GroupController::class, 'destroy'])->name('groups.destroy')->middleware(['auth', 'XSS']);


    Route::resource('operating_hours', OperatinghoursController::class)->middleware('auth','XSS');

    Route::resource('priority', PriorityController::class)->middleware('auth','XSS');

    Route::resource('policiy', SLAPoliciyController::class)->middleware('auth','XSS');


    Route::get('faq/create', [FaqController::class, 'create'])->name('faq.create')->middleware(['XSS']);

    Route::post('faq', [FaqController::class, 'store'])->name('faq.store')->middleware(['XSS']);

    Route::get('faq', [FaqController::class, 'index'])->name('faq')->middleware(['XSS']);

    Route::get('faq/{id}/edit', [FaqController::class, 'edit'])->name('faq.edit')->middleware(['XSS']);

    Route::delete('faq/{id}/destroy', [FaqController::class, 'destroy'])->name('faq.destroy')->middleware(['XSS']);

    Route::put('faq/{id}/update', [FaqController::class, 'update'])->name('faq.update')->middleware(['XSS']);

    Route::post('ticket/{id}/conversion', [ConversionController::class, 'store'])->name('conversion.store')->middleware(['XSS']);

    Route::post('ticket/{id}/note', [TicketController::class, 'storeNote'])->name('note.store')->middleware(['auth', 'XSS']);

    Route::get('knowledge', [KnowledgeController::class, 'index'])->name('knowledge')->middleware(['XSS', 'auth']);

    Route::get('knowledge/create', [KnowledgeController::class, 'create'])->name('knowledge.create')->middleware(['XSS', 'auth']);

    Route::post('knowledge', [KnowledgeController::class, 'store'])->name('knowledge.store')->middleware(['XSS', 'auth']);

    Route::get('knowledge/{id}/edit', [KnowledgeController::class, 'edit'])->name('knowledge.edit')->middleware(['XSS', 'auth']);

    Route::delete('knowledge/{id}/destroy', [KnowledgeController::class, 'destroy'])->name('knowledge.destroy')->middleware(['XSS', 'auth']);

    Route::put('knowledge/{id}/update', [KnowledgeController::class, 'update'])->name('knowledge.update')->middleware(['XSS', 'auth']);

    Route::get('knowledgecategory', [KnowledgebaseCategoryController::class, 'index'])->name('knowledgecategory')->middleware(['XSS']);

    Route::get('knowledgecategory/create', [KnowledgebaseCategoryController::class, 'create'])->name('knowledgecategory.create')->middleware(['XSS']);

    Route::post('knowledgecategory', [KnowledgebaseCategoryController::class, 'store'])->name('knowledgecategory.store')->middleware(['XSS', 'auth']);

    Route::get('knowledgecategory/{id}/edit', [KnowledgebaseCategoryController::class, 'edit'])->name('knowledgecategory.edit')->middleware(['XSS']);

    Route::delete('knowledgecategory/{id}/destroy', [KnowledgebaseCategoryController::class, 'destroy'])->name('knowledgecategory.destroy')->middleware(['XSS']);

    Route::put('knowledgecategory/{id}/update', [KnowledgebaseCategoryController::class, 'update'])->name('knowledgecategory.update')->middleware(['XSS']);

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index')->middleware(['auth', 'XSS']);

    Route::post('/settings_store', [SettingsController::class, 'saveBusinessSettings'])->name('settings.store')->middleware(['auth', 'XSS']);

    Route::post('/email-settings', [SettingsController::class, 'emailSettingStore'])->name('email.settings.store')->middleware(['auth', 'XSS']);

    Route::post('/payment-settings', [SettingsController::class, 'paymentSettingStore'])->name('payment.settings.store')->middleware(['auth', 'XSS']);

    Route::post('/pusher-settings', [SettingsController::class, 'pusherSettingStore'])->name('pusher.settings.store')->middleware(['auth', 'XSS']);

    Route::post('/recaptcha-settings', [SettingsController::class, 'recaptchaSettingStore'])->name('recaptcha.settings.store')->middleware(['auth', 'XSS']);

    Route::post('company-settings', [SettingsController::class, 'saveCompanySettings'])->name('company.settings');

    Route::post('domain-settings',[SettingsController::class,'savedomainSettings'])->name('domain.settings');

    Route::post('/test', [SettingsController::class, 'testEmail'])->name('test.email')->middleware(['auth', 'XSS']);

    Route::post('/test/send', [SettingsController::class, 'testEmailSend'])->name('test.email.send')->middleware(['auth', 'XSS']);

    Route::post('payment-settings', [SettingsController::class, 'savePaymentSettings'])->name('payment.settings')->middleware(['XSS']);

    Route::post('owner-payment-setting', [SettingsController::class, 'saveOwnerPaymentSettings'])->name('owner.payment.setting')->middleware(['XSS']);

    Route::post('storage-settings', [SettingsController::class, 'storageSettingStore'])->name('storage.setting.store')->middleware(['auth', 'XSS']);

    Route::post('setting/seo', [SettingsController::class, 'saveSEOSettings'])->name('seo.settings');

    Route::post('cookie-setting', [SettingsController::class, 'saveCookieSettings'])->name('cookie.setting');

    Route::post('chatgptkey-setting', [SettingsController::class, 'chatgptkey'])->name('settings.chatgptkey');

    Route::get('generate/{template_name}',[AiTemplateController::class,'create'])->name('generate');

    Route::post('generate/keywords/{id}',[AiTemplateController::class,'getKeywords'])->name('generate.keywords');

    Route::post('generate/response',[AiTemplateController::class,'AiGenerate'])->name('generate.response');

    Route::get('grammar/{template}',[AiTemplateController::class,'grammar'])->name('grammar');

    Route::post('grammar/response',[AiTemplateController::class,'grammarProcess'])->name('grammar.response');

    //==================================== Slack ====================================//
    Route::any('setting/slack', [SettingsController::class, 'slack'])->name('slack.setting');

    //==================================== Telegram ====================================//
    Route::any('setting/telegram', [SettingsController::class, 'telegram'])->name('telegram.setting');

    Route::resource('webhook', WebhookController::class);
    //====================================  Notification ====================================//
    Route::resource('notification-templates', NotificationTemplatesController::class)->middleware(['auth','XSS',]);
    Route::get('notification-templates/{id?}/{lang?}/', [NotificationTemplatesController::class, 'index'])->name('notification-templates.index')->middleware(['auth', 'XSS']);

    //Export
    Route::get('export/ticketss', [TicketController::class, 'export'])->name('tickets.export')->middleware(['auth', 'XSS']);

    // Email Templates
    Route::get('email_template_lang/{id}/{lang?}', [EmailTemplateController::class, 'manageEmailLang'])->name('manage.email.language')->middleware(['auth', 'XSS']);

    Route::post('email_template_store/{pid}', [EmailTemplateController::class, 'storeEmailLang'])->name('store.email.language')->middleware(['auth', 'XSS']);

    Route::post('email_template_status', [EmailTemplateController::class, 'updateStatus'])->name('status.email.language')->middleware(['auth', 'XSS']);


    Route::resource('email_template', EmailTemplateController::class)->middleware(['auth', 'XSS']);

    Route::resource('email_template_lang', EmailTemplateLangController::class)->middleware(['auth', 'XSS']);


    //------------------------------------------------------------------------------------------------------------------------

    Route::resource('plan', PlanController::class)->middleware(['auth', 'XSS']);
    Route::get('plan/plan-trial/{id}', [PlanController::class, 'PlanTrial'])->name('plan.trial')->middleware(['auth', 'XSS']);


    Route::get('user/{id}/plan', [UserController::class, 'upgradePlan'])->name('plan.upgrade')->middleware(['auth', 'XSS']);

    Route::get('user/{id}/plan/{pid}', [UserController::class, 'activePlan'])->name('plan.active')->middleware(['auth', 'XSS']);

    Route::post('plan-unable', [PlanController::class, 'PlanUnable'])->name('plan.unable');

    //---------------------------------------  Coupons --------------------------------------------- //

    Route::get('/apply-coupon', [CouponController::class, 'applyCoupon'])->name('apply.coupon')->middleware(['auth', 'XSS']);

    Route::resource('coupon', CouponController::class)->middleware(['auth', 'XSS']);


    //=================================Plan Request Module ====================================//

    Route::get('plan_request', [PlanRequestController::class, 'index'])->name('plan_request.index')->middleware(['auth', 'XSS']);

    Route::get('request_frequency/{id}', [PlanRequestController::class, 'requestView'])->name('request.view')->middleware(['auth', 'XSS']);

    Route::any('request_send/{id}', [PlanRequestController::class, 'userRequest'])->name('send.request')->middleware(['auth', 'XSS']);

    Route::get('request_response/{id}/{response}', [PlanRequestController::class, 'acceptRequest'])->name('response.request')->middleware(['auth', 'XSS']);

    Route::get('request_cancel/{id}', [PlanRequestController::class, 'cancelRequest'])->name('request.cancel')->middleware(['auth', 'XSS']);


    //=================================Customdomain Request Module ====================================//
    Route::get('custom_domain_request', [CustomDomainRequestController::class, 'index'])->name('custom_domain_request.index')->middleware(['auth', 'XSS']);
    Route::delete('custom_domain_request/{id}/destroy', [CustomDomainRequestController::class, 'destroy'])->name('custom_domain_request.destroy')->middleware(['XSS']);
    Route::get('custom_domain_request/{id}/{response}', [CustomDomainRequestController::class, 'acceptRequest'])->name('custom_domain_request.request')->middleware(['auth', 'XSS']);
    Route::get('custom_domain_request_cancel/{id}', [CustomDomainRequestController::class, 'cancelRequest'])->name('custom_domain_request.cancel')->middleware(['auth', 'XSS']);


    Route::resource('payments', PaymentController::class)->middleware(['auth', 'XSS']);

    Route::get('/Plan/Payment/{code}', [PlanController::class, 'getpaymentgatway'])->name('plan.payment')->middleware(['auth', 'XSS']);

    Route::get('{id}/plan-get-payment-status', [PaypalController::class, 'planGetPaymentStatus'])->name('plan.get.payment.status')->middleware(['auth', 'XSS']);


    Route::group(['middleware' => ['auth', 'XSS']], function () {

        Route::get('/orders', [StripePaymentController::class, 'index'])->name('order.index')->middleware(['auth', 'XSS']);
        Route::get('/refund/{id}/{user_id}', [StripePaymentController::class, 'refund'])->name('order.refund');
        Route::get('/stripe/{code}', [StripePaymentController::class, 'stripe'])->name('stripe');

        Route::post('/stripe', [StripePaymentController::class, 'stripePost'])->name('stripe.post')->middleware(['auth', 'XSS']);

        Route::get('/stripe-payment-status', [StripePaymentController::class, 'planGetStripePaymentStatus'])->name('stripe.payment.status')->middleware(['auth', 'XSS']);

        // Route::get('/orders', [StripePaymentController::class, 'destroy'])->name('order.destroy')->middleware(['auth', 'XSS']);

        Route::post('order/{id}/changeaction', [BanktransferController::class, 'changeStatus'])->name('order.changestatus');
        Route::get('order/{id}/action', [BanktransferController::class, 'action'])->name('order.action');
        Route::delete('/bank_transfer/{order}/', [BankTransferController::class, 'destroy'])->name('bank_transfer.destroy')->middleware(['auth','XSS']);



    });

    Route::middleware(['XSS'])->group(function () {

        Route::post('/invoice-pay-with-paystack', [PaystackPaymentController::class, 'invoicePayWithPaystack'])->name('invoice.pay.with.paystack');
        Route::get('/invoice/paystack/{pay_id}/{invoice_id}', [PaystackPaymentController::class, 'getInvociePaymentStatus'])->name('invoice.paystack');

        Route::get('/plan/error/{flag}', [PaymentWallController::class, 'planerror'])->name('error.plan.show');

        Route::get('/invoice/error/{flag}/{invoice_id}', [PaymentWallController::class, 'invoiceerror'])->name('error.invoice.show');
        Route::post('/invoicepayment', [PaymentWallController::class, 'invoicepay'])->name('paymentwall.invoice');
        Route::post('/invoice-pay-with-paymentwall/{invoice}', [PaymentWallController::class, 'invoicePayWithPaymentWall'])->name('invoice-pay-with-paymentwall');
    });


    Route::post('plan-pay-with-paypal', [PaypalController::class, 'planPayWithPaypal'])->name('plan.pay.with.paypal')->middleware(['auth', 'XSS',]);


    //================================= Plan Payment Gateways  ====================================//

    Route::post('/plan-pay-with-paystack', [PaystackPaymentController::class, 'planPayWithPaystack'])->name('plan.pay.with.paystack')->middleware('XSS', 'auth');
    Route::get('/plan/paystack/{pay_id}/{plan_id}', [PaystackPaymentController::class, 'getPaymentStatus'])->name('plan.paystack')->middleware('XSS');

    Route::post('/plan-pay-with-flaterwave', [FlutterwavePaymentController::class, 'planPayWithFlutterwave'])->middleware('XSS', 'auth')->name('plan.pay.with.flaterwave');
    Route::get('/plan/flaterwave/{txref}/{plan_id}', [FlutterwavePaymentController::class, 'getPaymentStatus'])->middleware('XSS')->name('plan.flaterwave');

    Route::post('/plan-pay-with-razorpay', [RazorpayPaymentController::class, 'planPayWithRazorpay'])->middleware('XSS', 'auth')->name('plan.pay.with.razorpay');
    Route::get('/plan/razorpay/{txref}/{plan_id}', [RazorpayPaymentController::class, 'getPaymentStatus'])->middleware('XSS')->name('plan.razorpay');

    Route::post('/plan-pay-with-paytm', [PaytmPaymentController::class, 'planPayWithPaytm'])->middleware('XSS', 'auth')->name('plan.pay.with.paytm');
    Route::post('/plan/paytm/{plan_id}', [PaytmPaymentController::class, 'getPaymentStatus'])->middleware('XSS')->name('plan.paytm');

    Route::post('/plan-pay-with-mercado', [MercadoPaymentController::class, 'planPayWithMercado'])->middleware('XSS', 'auth')->name('plan.pay.with.mercado');
    Route::get('/plan/mercado/{plan}', [MercadoPaymentController::class, 'getPaymentStatus'])->middleware('XSS')->name('plan.mercado');


    Route::post('/plan-pay-with-mollie', [MolliePaymentController::class, 'planPayWithMollie'])->middleware('XSS', 'auth')->name('plan.pay.with.mollie');
    Route::get('/plan/mollie/{plan}', [MolliePaymentController::class, 'getPaymentStatus'])->middleware('XSS')->name('plan.mollie');

    Route::post('/plan-pay-with-skrill', [SkrillPaymentController::class, 'planPayWithSkrill'])->middleware('XSS', 'auth')->name('plan.pay.with.skrill');
    Route::get('/plan/skrill/{plan_id}', [SkrillPaymentController::class, 'getPaymentStatus'])->middleware('XSS')->name('plan.skrill');

    Route::post('/plan-pay-with-coingate', [CoingatePaymentController::class, 'planPayWithCoingate'])->middleware('XSS', 'auth')->name('plan.pay.with.coingate');
    Route::get('/plan/coingate/{plan}', [CoingatePaymentController::class, 'getPaymentStatus'])->middleware('XSS')->name('plan.coingate');

    Route::post('/planpayment', [PaymentWallController::class, 'planpay'])->middleware('XSS', 'auth')->name('paymentwall');
    Route::post('/paymentwall-payment/{plan}', [PaymentWallController::class, 'planPayWithPaymentWall'])->middleware('XSS', 'auth')->name('paymentwall.payment');

    Route::post('/plan-pay-with-toyyibpay', [ToyyibpayController::class, 'charge'])->name('plan.pay.with.toyyibpay')->middleware(['auth', 'XSS']);
    Route::get('/plan-get-payment-status/{id}/{amount}/{couponCode?}', [ToyyibpayController::class, 'status'])->name('plan.status');

    Route::post('payfast-plan', [PayfastController::class, 'index'])->name('payfast.payment')->middleware(['auth']);
    Route::get('payfast-plan/{success}', [PayfastController::class, 'success'])->name('payfast.payment.success')->middleware(['auth']);

    Route::any('plan-pay-with-banktransfer', [BanktransferController::class, 'planPayWithBanktransfer'])->name('plan.pay.with.banktransfer')->middleware(['auth', 'XSS',]);

    Route::post('iyzipay/prepare', [IyziPayController::class, 'initiatePayment'])->name('iyzipay.payment.init');
    Route::post('iyzipay/callback/plan/{id}/{amount}/{coupan_code?}', [IyzipayController::class, 'iyzipayCallback'])->name('iyzipay.payment.callback');

    Route::post('sspay-prepare-plan', [SspayController::class, 'SspayPaymentPrepare'])->middleware(['auth'])->name('sspay.prepare.plan');
    Route::get('sspay-payment-plan/{plan_id}/{amount}/{couponCode}', [SspayController::class, 'SspayPlanGetPayment'])->middleware(['auth'])->name('plan.sspay.callback');

    Route::post('plan-pay-with-paytab', [PaytabController::class, 'planPayWithpaytab'])->middleware(['auth'])->name('plan.pay.with.paytab');
    Route::any('plan-paytab-success/', [PaytabController::class, 'PaytabGetPayment'])->middleware(['auth'])->name('plan.paytab.success');

    Route::any('/payment/initiate', [BenefitPaymentController::class, 'initiatePayment'])->name('benefit.initiate');
    Route::any('call_back', [BenefitPaymentController::class, 'call_back'])->name('benefit.call_back');

    Route::post('cashfree/payments/store', [CashfreeController::class, 'cashfreePaymentStore'])->name('cashfree.payment');
    Route::any('cashfree/payments/success', [CashfreeController::class, 'cashfreePaymentSuccess'])->name('cashfreePayment.success');

    Route::post('/aamarpay/payment', [AamarpayController::class, 'pay'])->name('pay.aamarpay.payment');
    Route::any('/aamarpay/success/{data}', [AamarpayController::class, 'aamarpaysuccess'])->name('pay.aamarpay.success');

    Route::post('/paytr/payment', [PaytrController::class, 'PlanpayWithPaytr'])->name('pay.paytr.payment');
    Route::any('/paytr/success', [PaytrController::class, 'paytrsuccess'])->name('pay.paytr.success');

    Route::get('/plan/yookassa/payment', [YooKassaController::class,'planPayWithYooKassa'])->name('plan.pay.with.yookassa');
    Route::get('/plan/yookassa/{plan}', [YooKassaController::class,'planGetYooKassaStatus'])->name('plan.get.yookassa.status');

    Route::any('/midtrans', [MidtransController::class, 'planPayWithMidtrans'])->name('plan.get.midtrans');
    Route::any('/midtrans/callback', [MidtransController::class, 'planGetMidtransStatus'])->name('plan.get.midtrans.status');

    Route::any('/xendit/payment', [XenditPaymentController::class, 'planPayWithXendit'])->name('plan.xendit.payment');
    Route::any('/xendit/payment/status', [XenditPaymentController::class, 'planGetXenditStatus'])->name('plan.xendit.status');


    //Paiement Pro
    Route::any('/paiementpro', [PaiementProController::class, 'planPayWithPaiementPro'])->name('plan.get.paiementpro');
    Route::any('/paiement/payment/status/{plan_id}', [PaiementProController::class, 'planGetPaiementProStatus'])->name('plan.get.paiementpro.status');

    //Nepalste
    Route::any('/nepalste/payment', [NepalstePaymentController::class, 'planPayWithNepalste'])->name('plan.pay.with.nepalste');
    Route::any('nepalste/status/', [NepalstePaymentController::class, 'planGetNepalsteStatus'])->name('nepalste.status');
    Route::any('nepalste/cancel/', [NepalstePaymentController::class, 'planGetNepalsteCancel'])->name('nepalste.cancel');


     //Fedapay
     Route::any('/fedapay/payment', [FedapayPaymentController::class, 'planPaywithFedapay'])->name('plan.pay.with.fedapay');
     Route::any('fedapay-payment-status/{plan_id}', [FedapayPaymentController::class, 'planGetFedapayStatus'])->name('plan.get.fedapay.status');


      //PayHere
      Route::any('/payhere/payment', [PayHerePaymentController::class, 'planPayWithPayHere'])->name('plan.pay.with.payhere');
      Route::any('payhere-payment-status/{plan_id}/{amount}', [PayHerePaymentController::class, 'planGetPayHereStatus'])->name('plan.get.payhere.status');




    //Cinetpay
    Route::any('/cinetpay/payment', [CinetpayPaymentController::class, 'planPayWithCinetpay'])->name('plan.cinetpay.payment');
    Route::any('/cinetpay-payment-status/{plan_id}', [CinetpayPaymentController::class, 'planGetCinetpayStatus'])->name('plan.cinetpay.status');

     //====================================  User Log ====================================//
     Route::get('profile', [UserController::class, 'profile'])->name('profile')->middleware(['auth', 'XSS']);
     Route::post('/profile/{id}', [UserController::class, 'editprofile'])->name('update.profile')->middleware(['auth', 'XSS']);
    // Route::post('users-reset-password/{id}', [UserController::class, 'userPasswordReset'])->name('user.password.update')->middleware(['XSS']);

    Route::get('/user-log', [UserController::class, 'userlog'])->name('userlog')->middleware(['auth', 'XSS']);
    Route::delete('/user-log-delete/{id}', [UserController::class, 'userlogDestroy'])->name('userlog.destroy')->middleware('auth','XSS');
    Route::get('/view-user-log/{id}', [UserController::class, 'userlogview'])->name('userlog.display')->middleware('XSS', 'auth');
    Route::get('admin-info/{id}', [UserController::class, 'UserInfo'])->name('user.info');
    Route::post('user-unable', [UserController::class, 'UserUnable'])->name('user.unable');
    Route::get('users/{id}/login-with-company', [UserController::class, 'LoginWithCompany'])->name('login.with.company');



});



Route::get('get_message', [MessageController::class, 'getFloatingMessage'])->name('get_message')->middleware(['XSS']);

Route::post('message_form', [MessageController::class, 'store'])->name('chat_form.store')->middleware(['XSS']);

Route::post('floating_message', [MessageController::class, 'sendFloatingMessage'])->name('floating_message')->middleware(['XSS']);

Route::get('/config-cache', function () {
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('optimize:clear');
    return redirect()->back()->with('success', 'Clear Cache successfully.');
});


// Reffral Code
Route::get('referral-program/company', [ReferralProgramController::class, 'companyIndex'])->name('referral-program.company')->middleware(['auth', 'XSS']);
Route::resource('referral-programs', ReferralProgramController::class);
Route::get('request-amount-sent/{id}', [ReferralProgramController::class, 'requestedAmountSent'])->name('request.amount.sent')->middleware(['auth', 'XSS']);
Route::get('request-amount-cancel/{id}', [ReferralProgramController::class, 'requestCancel'])->name('request.amount.cancel')->middleware(['auth', 'XSS']);;
Route::post('request-amount-store/{id}', [ReferralProgramController::class, 'requestedAmountStore'])->name('request.amount.store')->middleware(['auth', 'XSS']);
Route::get('request-amount/{id}/{status}', [ReferralProgramController::class, 'requestedAmount'])->name('amount.request')->middleware(['auth', 'XSS']);


