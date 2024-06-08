<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\EncryptController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\OffersController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DentistPanelController;
use App\Http\Controllers\ProfileController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/home', [LoginController::class, 'home'])->name('home')->middleware('auth');
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('home');
    } else {
        return view('welcome');
    }
})->name('welcome');
// Route::get('/wasmTest', function () {
//     return view('wasmTest');
// });
Route::get('/profile/show', [ProfileController::class, 'index'])->name('profile')->middleware('auth');
Route::get('/login', [LoginController::class, 'index'])->name('login')->middleware('guest');
Route::get('/register', [LoginController::class, 'register'])->name('register')->middleware('guest');
Route::get('/email_verification/{email}/{token}', [LoginController::class, 'emailVerification'])->name('email_verification')->middleware('guest');
Route::get('/login/encrypt', [LoginController::class, 'encrypt'])->name('login.encrypt')->middleware('auth');
Route::get('/login/decrypt', [LoginController::class, 'decrypt'])->name('login.decrypt')->middleware('auth');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');
Route::get('/changepassword', [LoginController::class, 'changePasswordForm'])->name('login.changepasswordForm')->middleware('auth');
Route::get('/chat', [ChatController::class, 'index'])->name('chat')->middleware('auth');
Route::get('/chat/start/{user_id}', [ChatController::class, 'startNewChat'])->name('chat.start')->middleware('auth');
Route::get('/chat/with/{chatroom_id}', [ChatController::class, 'chatWith'])->name('chat.with')->middleware('auth');
Route::get('/chat/downloadfile/{message_id}', [ChatController::class, 'downloadFile'])->name('chat.downloadfile')->middleware('auth');
Route::get('/offers/edit', [OffersController::class, 'edit'])->name('offers.edit')->middleware('dentist');
Route::get('/offers/show/{id}', [OffersController::class, 'show'])->name('offers.show');
Route::get('/offers/search', [OffersController::class, 'search'])->name('offers.search');
Route::get('/appointment/search/{id}', [AppointmentController::class, 'searchAppointmentForm'])->name('appointment.searchAppointmentForm')->middleware('auth');
Route::get('/appointment/questionnaire/{id}', [AppointmentController::class, 'showQuestionnaireForm'])->name('appointment.new.questionnaire')->middleware('auth');
Route::get('/appointment/calendar/today', [AppointmentController::class, 'showCalendarToday'])->name('appointment.calendar')->middleware('auth');
Route::get('/appointment/calendar/at/{date}', [AppointmentController::class, 'showCalendar'])->name('appointment.calendar.at')->middleware('auth');
Route::get('/appointment/calendar/day/{date}', [AppointmentController::class, 'showDay'])->name('appointment.calendar.day')->middleware('auth');
Route::get('/appointment/calendar/event/{id}', [AppointmentController::class, 'showEvent'])->name('appointment.calendar.event')->middleware('auth');
Route::get('/appointment/move/{id}', [AppointmentController::class, 'move'])->name('appointment.move')->middleware('auth');
Route::post('/appointment/move/search', [AppointmentController::class, 'searchMove'])->name('appointment.move.search')->middleware('auth');
Route::post('/appointment/move/save', [AppointmentController::class, 'saveMove'])->name('appointment.move.save')->middleware('auth');
Route::post('/appointment/confirm', [AppointmentController::class, 'confirm'])->name('appointment.confirm')->middleware('auth');
Route::post('/appointment/cancel', [AppointmentController::class, 'cancel'])->name('appointment.cancel')->middleware('auth');
Route::post('/login/updatepassword', [LoginController::class, 'updatePassword'])->name('login.updatepassword')->middleware('auth');
Route::Post('/appointment/search/{id}', [AppointmentController::class, 'searchAppointment'])->name('appointment.search');
Route::Post('/appointment/saveAppointmentFromPatient', [AppointmentController::class, 'saveAppointmentFromPatient'])->name('appointment.saveAppointmentFromPatient')->middleware('auth');
Route::post('/offers/found', [OffersController::class, 'found'])->name('offers.found');
Route::post('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit')->middleware('auth');
Route::post('/login', [LoginController::class, 'login'])->name('login')->middleware('guest');
Route::post('/register', [LoginController::class, 'store'])->name('register')->middleware('guest');
Route::post('/login/purgeKeys', [LoginController::class, 'purgeKeys'])->name('login.purgeKeys')->middleware('auth');
Route::post('/chat/createChatRoom', [ChatController::class, 'createChatRoom'])->name('chat.createChatRoom')->middleware('auth');
Route::post('/offers/update/photo', [OffersController::class, 'updatePhoto'])->name('offers.update.photo')->middleware('dentist');
Route::post('/offers/update/description', [OffersController::class, 'updateDescription'])->name('offers.update.description')->middleware('dentist');
Route::post('/offers/update/address', [OffersController::class, 'updateAddress'])->name('offers.update.address')->middleware('dentist');
Route::post('/offers/add/service', [OffersController::class, 'addService'])->name('offers.add.service')->middleware('dentist');
Route::post('/offers/update/service', [OffersController::class, 'updateService'])->name('offers.update.service')->middleware('dentist');
Route::post('/offers/delete/service', [OffersController::class, 'deleteService'])->name('offers.delete.service')->middleware('dentist');
Route::post('/offers/activate', [OffersController::class, 'activate'])->name('offers.activate')->middleware('dentist');
Route::post('/offers/update/duaration', [OffersController::class, 'updateDuration'])->name('offers.update.duration')->middleware('dentist');
Route::post('/offers/update/workinghours', [OffersController::class, 'updateWorkingHours'])->name('offers.update.workinghours')->middleware('dentist');
Route::post('/api/post/encrypt/store', [LoginController::class, 'storeKeys'])->name('api.encrypt.store')->middleware('auth');
Route::get('/api/get/signedEmail', [LoginController::class, 'getSignedEmail'])->name('api.get.signedemail')->middleware('auth');
Route::get('/api/get/email', [LoginController::class, 'getEmail'])->name('api.get.email')->middleware('auth');
Route::post('/api/post/getPublicKeyAny', [LoginController::class, 'getPublicKeyAny'])->name('api.post.getPublicKeyAny')->middleware('auth');
Route::post('/api/post/getChatMessages', [ChatController::class, 'getChatMessages'])->name('api.post.getChatMessages')->middleware('auth');
Route::post('/api/post/getChatrooms', [ChatController::class, 'getChatrooms'])->name('api.post.getChatrooms')->middleware('auth');
Route::post('/api/post/sendMessage', [ChatController::class, 'sendMessage'])->name('api.post.sendMessage')->middleware('auth');
Route::post('/api/post/getNearestOffers', [OffersController::class, 'getNearestOffers'])->name('api.post.getNearestOffers');
Route::post('/api/post/sendFile', [ChatController::class, 'sendFile'])->name('api.post.sendFile')->middleware('auth');
Route::post('/api/post/setSeenToAllBefore', [ChatController::class, 'setSeenToAllBefore'])->name('api.post.setSeenToAllBefore')->middleware('auth');
