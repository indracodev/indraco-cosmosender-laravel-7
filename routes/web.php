<?php

use Illuminate\Support\Facades\Route;

// Public Authentication Routes
Route::get('/login', 'AuthController@showLogin')->name('login');
Route::post('/login', 'AuthController@login')->name('login.submit');
Route::post('/logout', 'AuthController@logout')->name('logout');

// Protected Application Routes
Route::middleware(['cosmosender.auth'])->group(function () {
    // Workspace / Dashboard
    Route::get('/', 'DashboardController@index')->name('dashboard');

    // Content Management (Per Brand)
    Route::get('/brands/{brand}/contents', 'ContentController@index')->name('contents.index');
    Route::post('/brands/{brand}/contents', 'ContentController@store')->name('contents.store');
    Route::put('/contents/{content}', 'ContentController@updateMeta')->name('contents.update');
    Route::get('/contents/{content}/editor', 'ContentController@editorData')->name('contents.editor');
    Route::post('/contents/{content}/body', 'ContentController@saveBody')->name('contents.saveBody');
    Route::post('/contents/{content}/assets', 'ContentController@uploadAsset')->name('contents.uploadAsset');
    Route::post('/contents/{content}/push-live', 'ContentController@pushToLive')->name('contents.pushLive');
    Route::get('/contents/{content}/preview', 'ContentController@preview')->name('contents.preview');

    // Blast Engine (Per Brand)
    Route::get('/brands/{brand}/blast', 'BlastController@index')->name('blast.index');
    Route::post('/brands/{brand}/blast/recipients', 'BlastController@loadRecipients')->name('blast.recipients');
    Route::post('/brands/{brand}/blast/send-item', 'BlastController@sendItem')->name('blast.sendItem');

    // Customer Email Sync
    Route::get('/email-sync', 'EmailSyncController@index')->name('emailsync.index');
    Route::get('/email-sync/{brand}/customers', 'EmailSyncController@customers')->name('emailsync.customers');
    Route::post('/email-sync/{brand}/config', 'EmailSyncController@saveConfig')->name('emailsync.saveConfig');
    Route::post('/email-sync/{brand}/sync', 'EmailSyncController@syncFromRemote')->name('emailsync.sync');

    // Transactional Settings: Brands & Blast Rules
    Route::get('/settings/brands', 'BrandController@index')->name('settings.brands');
    Route::post('/settings/brands', 'BrandController@store')->name('settings.brands.store');
    Route::put('/settings/brands/{brand}', 'BrandController@update')->name('settings.brands.update');

    Route::get('/settings/blast-rules', 'BlastRuleController@index')->name('settings.blastrules');
    Route::post('/settings/blast-rules', 'BlastRuleController@storeRule')->name('settings.blastrules.store');
    Route::put('/settings/blast-rules/{rule}', 'BlastRuleController@updateRule')->name('settings.blastrules.update');
    Route::post('/settings/blast-rules/{rule}/relays', 'BlastRuleController@storeRelay')->name('settings.blastrules.relays.store');
    Route::delete('/settings/blast-rules/relays/{detail}', 'BlastRuleController@removeRelay')->name('settings.blastrules.relays.remove');
    Route::post('/settings/blast-rules/{rule}/recipients', 'BlastRuleController@storeRecipient')->name('settings.blastrules.recipients.store');
    Route::delete('/settings/blast-rules/recipients/{recipient}', 'BlastRuleController@removeRecipient')->name('settings.blastrules.recipients.remove');

    // Master Settings: Servers, Services, Hosts, Relays
    Route::get('/settings/servers', 'MasterSettingController@servers')->name('settings.servers');
    Route::post('/settings/servers', 'MasterSettingController@storeServer')->name('settings.servers.store');
    Route::put('/settings/servers/{server}', 'MasterSettingController@updateServer')->name('settings.servers.update');

    Route::get('/settings/services', 'MasterSettingController@services')->name('settings.services');
    Route::post('/settings/services', 'MasterSettingController@storeService')->name('settings.services.store');
    Route::put('/settings/services/{service}', 'MasterSettingController@updateService')->name('settings.services.update');

    Route::get('/settings/hosts', 'MasterSettingController@hosts')->name('settings.hosts');
    Route::post('/settings/hosts', 'MasterSettingController@storeHost')->name('settings.hosts.store');
    Route::put('/settings/hosts/{host}', 'MasterSettingController@updateHost')->name('settings.hosts.update');

    Route::get('/settings/relays', 'MasterSettingController@relays')->name('settings.relays');
    Route::post('/settings/relays', 'MasterSettingController@storeRelay')->name('settings.relays.store');
    Route::put('/settings/relays/{relay}', 'MasterSettingController@updateRelay')->name('settings.relays.update');
});
