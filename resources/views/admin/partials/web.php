<?php

use Illuminate\Support\Facades\Route;

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


require __DIR__.'/auth.php';


Route::get('/', 'HomeController@index');


Route::get('home', ['as' => 'home','uses' =>'HomeController@index']);
Route::post('home', ['as' => 'home.store','uses' =>'HomeController@store']);
Route::get('search', ['as' => 'search','uses' =>'HomeController@search']);
Route::post('search', ['as' => 'ticket.search','uses' =>'HomeController@ticketSearch']);
Route::get('ticket/{id}', ['as' => 'home.view','uses' =>'HomeController@view']);
Route::post('ticket/{id}', ['as' => 'home.reply','uses' =>'HomeController@reply']);
Route::get('faq', ['as' => 'faq','uses' =>'HomeController@faq']);
Route::get('knowledge', ['as' => 'knowledge','uses' =>'HomeController@knowledge']);
Route::get('knowledgedesc', ['as' => 'knowledgedesc','uses' =>'HomeController@knowledgeDescription']);

Route::name('admin.')->prefix('admin')->middleware(['auth','XSS'])->group(function() {
    Route::get('dashboard', 'DashboardController')->name('dashboard');


    Route::resource('users', 'UserController', [
        'names' => [
            'index' => 'users'
        ]
    ]);
    Route::get('lang/clear',['as' => 'lang.clear','uses' =>'LanguageController@clear']);

    Route::get('lang/create',['as' => 'lang.create','uses' =>'LanguageController@create']);
    Route::post('lang/create',['as' => 'lang.store','uses' =>'LanguageController@store']);
    Route::get('lang/{lang}',['as' => 'lang.index','uses' =>'LanguageController@index']);
    Route::post('lang/{lang}',['as' => 'lang.store.data','uses' =>'LanguageController@storeData']);
    Route::get('lang/change/{lang}',['as' => 'lang.update','uses' =>'LanguageController@update']);
    Route::delete('lang/{lang}',['as' => 'lang.destroy','uses' =>'LanguageController@destroyLang']);

    Route::get('category/create',['as' => 'category.create','uses' =>'CategoryController@create']);
    Route::post('category',['as' => 'category.store','uses' =>'CategoryController@store']);
    Route::get('category',['as' => 'category','uses' =>'CategoryController@index']);
    Route::get('category/{id}/edit',['as' => 'category.edit','uses' =>'CategoryController@edit']);
    Route::delete('category/{id}/destroy',['as' => 'category.destroy','uses' =>'CategoryController@destroy']);
    Route::put('category/{id}/update',['as' => 'category.update','uses' =>'CategoryController@update']);

    // Message Route
    Route::get('chat',['as' => 'chats','uses' =>'MessageController@index']);
    Route::get('message/{id}',['as' => 'message','uses' => 'MessageController@getMessage']);
    Route::delete('delete-user-message/{id}',['as' => 'delete.user.message','uses' => 'MessageController@deleteUserMessage']);
    Route::post('message', 'MessageController@sendMessage');
    // End Message Route

    Route::post('/custom-fields',['as' => 'custom-fields.store','uses' =>'SettingsController@storeCustomFields']);

});


Route::any('users-reset-password/{id}', 'UserController@userPassword')->name('user.reset');
Route::post('users-reset-password/{id}', 'UserController@userPasswordReset')->name('user.password.update');


Route::name('admin.')->prefix('admin')->middleware(['auth','XSS'])->group(function() {

    Route::get('ticket/create',['as' => 'tickets.create','uses' =>'TicketController@create']);
    Route::post('ticket',['as' => 'tickets.store','uses' =>'TicketController@store']);
    Route::get('ticket/{status?}',['as' => 'tickets.index','uses' =>'TicketController@index']);
    Route::get('ticket/{id}/edit',['as' => 'tickets.edit','uses' =>'TicketController@editTicket']);
    Route::delete('ticket/{id}/destroy',['as' => 'tickets.destroy','uses' =>'TicketController@destroy']);
    Route::delete('ticket-attachment/{tid}/destroy/{id}',['as' => 'tickets.attachment.destroy','uses' =>'TicketController@attachmentDestroy']);
    Route::put('ticket/{id}/update',['as' => 'tickets.update','uses' =>'TicketController@updateTicket']);


    Route::get('faq/create',['as' => 'faq.create','uses' =>'FaqController@create']);
    Route::post('faq',['as' => 'faq.store','uses' =>'FaqController@store']);
    Route::get('faq',['as' => 'faq','uses' =>'FaqController@index']);
    Route::get('faq/{id}/edit',['as' => 'faq.edit','uses' =>'FaqController@edit']);
    Route::delete('faq/{id}/destroy',['as' => 'faq.destroy','uses' =>'FaqController@destroy']);
    Route::put('faq/{id}/update',['as' => 'faq.update','uses' =>'FaqController@update']);


    Route::post('ticket/{id}/conversion',['as' => 'conversion.store','uses' =>'ConversionController@store']);
    Route::post('ticket/{id}/note',['as' => 'note.store','uses' =>'TicketController@storeNote']);



    Route::get('knowledge',['as' => 'knowledge','uses' =>'KnowledgeController@index']);
    Route::get('knowledge/create',['as' => 'knowledge.create','uses' =>'KnowledgeController@create']);
    Route::post('knowledge',['as' => 'knowledge.store','uses' =>'KnowledgeController@store']);
    Route::get('knowledge/{id}/edit',['as' => 'knowledge.edit','uses' =>'KnowledgeController@edit']);
    Route::delete('knowledge/{id}/destroy',['as' => 'knowledge.destroy','uses' =>'KnowledgeController@destroy']);
    Route::put('knowledge/{id}/update',['as' => 'knowledge.update','uses' =>'KnowledgeController@update']);


    Route::get('knowledgecategory',['as' => 'knowledgecategory','uses' =>'KnowledgebaseCategoryController@index']);
    Route::get('knowledgecategory/create',['as' => 'knowledgecategory.create','uses' =>'KnowledgebaseCategoryController@create']);
    Route::post('knowledgecategory',['as' => 'knowledgecategory.store','uses' =>'KnowledgebaseCategoryController@store']);
    Route::get('knowledgecategory/{id}/edit',['as' => 'knowledgecategory.edit','uses' =>'KnowledgebaseCategoryController@edit']);
    Route::delete('knowledgecategory/{id}/destroy',['as' => 'knowledgecategory.destroy','uses' =>'KnowledgebaseCategoryController@destroy']);
    Route::put('knowledgecategory/{id}/update',['as' => 'knowledgecategory.update','uses' =>'KnowledgebaseCategoryController@update']);



    Route::get('/settings',['as' => 'settings.index','uses' =>'SettingsController@index'])->middleware(['auth','XSS']);
    Route::post('/settings',['as' => 'settings.store','uses' =>'SettingsController@store'])->middleware(['auth','XSS']);
    Route::post('/email-settings',['as' => 'email.settings.store','uses' =>'SettingsController@emailSettingStore'])->middleware(['auth','XSS']);
    Route::post('/payment-settings',['as' => 'payment.settings.store','uses' =>'SettingsController@paymentSettingStore'])->middleware(['auth','XSS']);
    Route::post('/pusher-settings',['as' => 'pusher.settings.store','uses' =>'SettingsController@pusherSettingStore'])->middleware(['auth','XSS']);
    Route::post('/recaptcha-settings',['as' => 'recaptcha.settings.store','uses' =>'SettingsController@recaptchaSettingStore'])->middleware(['auth','XSS']);
    Route::post('/test',['as' => 'test.email','uses' =>'SettingsController@testEmail'])->middleware(['auth','XSS']);
    Route::post('/test/send',['as' => 'test.email.send','uses' =>'SettingsController@testEmailSend'])->middleware(['auth','XSS']);




});

Route::get('get_message',['as' => 'get_message','uses' => 'MessageController@getFloatingMessage']);
Route::post('message_form',['as' => 'chat_form.store','uses' =>'MessageController@store']);
Route::post('floating_message', 'MessageController@sendFloatingMessage');


//Export
Route::get('export/tickets', 'TicketController@export')->name('tickets.export');


