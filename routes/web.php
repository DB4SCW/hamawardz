<?php

use App\Http\Controllers\AutoimportController;
use App\Http\Controllers\AwardController;
use App\Http\Controllers\CallsignController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HameventController;
use App\Http\Controllers\LogcheckController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

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

//handle link from main homepage
Route::get('/frommainwebsite', function() { return redirect('/'); });

//Logcheck Handling - Open for all
Route::get('/', [LogcheckController::class, 'home'])->name('home');
Route::post('/logcheck', [LogcheckController::class, 'chooseindex'])->name('chooselogcheck');
Route::get('/logcheck/{event:slug}', [LogcheckController::class, 'index'])->name('landingpage_eventcheck');
Route::post('/logcheck/{event:slug}', [LogcheckController::class, 'select'])->name('select_logcheck');
Route::get('/logcheck/{event:slug}/{callsign}', [LogcheckController::class, 'check'])->name('singlelogcheck');

//Award printing
Route::post('/awards/{award:slug}/pdf', [AwardController::class, 'print'])->name('printaward');

//handle Guest-middleware redirect
Route::get('/home', function() { return redirect('/'); });

//Only guests can login
Route::middleware('guest')->group(function () {
    Route::get('/login', [SessionController::class, 'index'])->name('loginpage');
    Route::post('/login', [SessionController::class, 'login'])->name('login');
});

//Routes for logged in users
Route::middleware('auth')->group(function () {
    
    //Session handling
    Route::get('/logout', [SessionController::class, 'logout'])->name('logout');

    //Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('participant_dashboard');
    
    //Upload handling
    Route::get('/upload', [UploadController::class, 'index'])->name('showuploadform');
    Route::post('/upload', [UploadController::class, 'upload'])->name('upload');

    //Upload delete
    Route::post('/upload/delete', [UploadController::class, 'delete'])->name('deleteupload');

    //Show contacts for upload
    Route::get('/uploads/{upload}/showcontacts', [ContactController::class, 'show'])->name('showcontactsforupload');
    Route::get('/uploads/{upload}/showerrors', [ContactController::class, 'showerrors'])->name('showerrorsforupload');

    //Password change
    Route::get('/profile', [ProfileController::class, 'index'])->name('showprofile');
    Route::post('/profile', [ProfileController::class, 'update'])->name('changepassword');

    //Event-Administration
    Route::get('/events', [HameventController::class, 'index'])->name('listevents');
    Route::get('/events/create', [HameventController::class, 'showcreate'])->name('showcreateevent');
    Route::post('/event/create', [HameventController::class, 'create'])->name('createevent');
    Route::get('/event/{event:slug}', [HameventController::class, 'showedit'])->name('showeditevent');
    Route::post('/event/{event:slug}', [HameventController::class, 'edit'])->name('editevent');
    Route::get('/event/{event:slug}/delete', [HameventController::class, 'destroy'])->name('deleteevent');
    Route::get('/event/{event:slug}/export', [ContactController::class, 'exportcontacts'])->name('exporteventcontacts');

    //Event-Manager
    Route::get('/event/{event:slug}/manager/{managerid}/remove', [HameventController::class, 'removemanager'])->name('removeeventmanager');
    Route::post('/event/{event:slug}/addmanager', [HameventController::class, 'addmanager'])->name('addeventmanager');
    
    //Event-Callsigns
    Route::get('/event/{event:slug}/callsign/{callsign:call}/remove', [HameventController::class, 'removeeventparticipant'])->name('removeeventparticipant');
    Route::post('/event/{event:slug}/addcallsign', [HameventController::class, 'addeventparticipant'])->name('addeventparticipant');

    //Award handling
    Route::post('/event/{event:slug}/addaward', [AwardController::class, 'showcreate'])->name('showcreateawardpost');
    Route::get('/event/{event:slug}/addaward', [AwardController::class, 'showcreate'])->name('showcreateawardget');
    Route::get('/awards/{award:slug}/remove', [AwardController::class, 'destroy'])->name('deleteaward');
    Route::post('/event/{event:slug}/createaward', [AwardController::class, 'create'])->name('createaward');
    Route::get('/awards/{award:slug}/edit', [AwardController::class, 'showedit'])->name('showeditaward');
    Route::get('/awards/{award:slug}/exampleaward', [AwardController::class, 'printexample'])->name('printexampleaward');
    Route::post('/awards/{award:slug}/edit', [AwardController::class, 'edit'])->name('editaward');
    Route::post('/awards/{award:slug}/uploadbackground', [AwardController::class, 'uploadbackground'])->name('uploadawardbackground');

    //Callsigns
    Route::get('/callsigns', [CallsignController::class, 'index'])->name('showcallsigns');
    Route::post('/callsigns/create', [CallsignController::class, 'create'])->name('createcallsign');
    Route::get('/callsign/{callsign:call}', [CallsignController::class, 'show'])->name('showeditcallsign');
    Route::post('/callsign/{callsign:call}', [CallsignController::class, 'edit'])->name('editcallsign');
    Route::get('/callsign/{callsign:call}/delete', [CallsignController::class, 'destroy'])->name('deletecallsign');   
    
    Route::get('/callsign/{callsign:call}/user/{uploaderid}/delete', [CallsignController::class, 'removeuploader'])->name('removeuploader');
    Route::post('/callsign/{callsign:call}/adduploader', [CallsignController::class, 'adduploader'])->name('adduploader');

    //Users
    Route::get('/users', [UserController::class, 'index'])->name('listusers');
    Route::post('/users/create', [UserController::class, 'create'])->name('createuser');
    Route::get('/user/{user:id}/toggle', [UserController::class, 'toggle'])->name('toggleusers');
    Route::get('/user/{user:id}', [UserController::class, 'showedit'])->name('showedituser');
    Route::post('/user/{user:id}', [UserController::class, 'edit'])->name('edituser');

    //Autoimport
    Route::get('/executeautoimport', [AutoimportController::class, 'trigger'])->name('autoimport');
    Route::get('/autoimports', [AutoimportController::class, 'index'])->name('listautoimports');
    Route::get('/autoimports/create', [AutoimportController::class, 'showcreate'])->name('showcreateautoimport');
    Route::post('/autoimports/create', [AutoimportController::class, 'create'])->name('createautoimport');
    Route::get('/autoimport/{autoimport:id}/edit', [AutoimportController::class, 'showedit'])->name('showeditautoimport');
    Route::post('/autoimport/{autoimport:id}/edit', [AutoimportController::class, 'edit'])->name('editautoimport');
    Route::get('/autoimport/{autoimport:id}/delete', [AutoimportController::class, 'destroy'])->name('deleteautoimport');
    Route::get('/autoimport/{autoimport:id}/toggle', [AutoimportController::class, 'toggle'])->name('toggleautoimport');

    //FixDXCCs
    Route::get("/fixdxccs", [ContactController::class, 'fixmissingdxccs'])->name('fixdxccs');

});

//this is a must
Route::get('/418', function() { abort(418); });

//Maintenance page
Route::get('/503', function() { abort(503); });

Route::get('/update', function() { return view('eastereggs.update'); });