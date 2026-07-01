<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Default Route
|--------------------------------------------------------------------------
| default_controller: module/controller (HMVC format)
| CI akan panggil index() pada Home controller di module public saat URI kosong untuk landing page
*/
$route['default_controller'] = 'public/home';
$route['404_override']        = '';
$route['translate_uri_dashes'] = FALSE;

/*
|--------------------------------------------------------------------------
| Home/Landing Page Routes (Public Module)
|--------------------------------------------------------------------------
*/
$route['']              = 'public/home/index';
$route['home']          = 'public/home/index';
$route['about']         = 'public/home/about';
$route['contact']       = 'public/home/contact';

/*
|--------------------------------------------------------------------------
| Auth Module Routes
|--------------------------------------------------------------------------
*/
$route['login']                         = 'auth/auth/login';
$route['logout']                        = 'auth/auth/logout';

$route['auth']                          = 'auth/auth/login';
$route['auth/login']                    = 'auth/auth/login';
$route['auth/logout']                   = 'auth/auth/logout';
$route['auth/register']                 = 'auth/auth/register';
$route['auth/forgot-password']          = 'auth/auth/forgotPassword';
$route['auth/reset-password/(:any)']    = 'auth/auth/resetPassword/$1';
$route['auth/verify-email/(:any)']      = 'auth/auth/verifyEmail/$1';
$route['auth/check-session']            = 'auth/auth/checkSession';
$route['auth/profile']                  = 'auth/auth/profile';
$route['auth/change-password']          = 'auth/auth/changePassword';

/*
|--------------------------------------------------------------------------
| Prodi Module Routes
|--------------------------------------------------------------------------
*/
$route['prodi']                         = 'prodi/dashboard/index';
$route['prodi/dashboard']               = 'prodi/dashboard/index';
$route['prodi/alumni']                  = 'prodi/alumni/index';
$route['prodi/alumni/(:any)']           = 'prodi/alumni/$1';
$route['prodi/survey']                  = 'prodi/survey/index';
$route['prodi/laporan']                 = 'prodi/laporan/index';
$route['prodi/laporan/(:any)']          = 'prodi/laporan/$1';
$route['prodi/profile']                 = 'prodi/profile/index';
$route['prodi/profile/change-password'] = 'prodi/profile/changePassword';

/*
|--------------------------------------------------------------------------
| Admin Module Routes
|--------------------------------------------------------------------------
*/
$route['admin']                         = 'admin/dashboard/index';
$route['admin/dashboard']               = 'admin/dashboard/index';
$route['admin/audit']                   = 'admin/audit/index';
$route['admin/audit/(:any)']            = 'admin/audit/$1';

/*
|--------------------------------------------------------------------------
| Alumni Module Routes
|--------------------------------------------------------------------------
*/
$route['alumni']                        = 'alumni/alumni/index';
$route['alumni/(:any)']                 = 'alumni/alumni/$1';

/*
|--------------------------------------------------------------------------
| IKU Module Routes
|--------------------------------------------------------------------------
*/
$route['iku']                           = 'iku/iku/index';
$route['iku/(:any)']                    = 'iku/iku/$1';
$route['iku-export/(:any)']             = 'iku/export/$1';

/*
|--------------------------------------------------------------------------
| Kurikulum Module Routes
|--------------------------------------------------------------------------
*/
$route['kurikulum']                     = 'kurikulum/kurikulum/index';
$route['kurikulum/(:any)']              = 'kurikulum/kurikulum/$1';
$route['cpl/(:any)']                    = 'kurikulum/cpl/$1';
$route['gap-analysis/(:any)']           = 'kurikulum/gap_analysis/$1';

/*
|--------------------------------------------------------------------------
| Laporan Module Routes
|--------------------------------------------------------------------------
*/
$route['laporan']                       = 'laporan/laporan/index';
$route['laporan/(:any)']                = 'laporan/laporan/$1';

/*
|--------------------------------------------------------------------------
| Stakeholder Module Routes
|--------------------------------------------------------------------------
*/
$route['stakeholder']                   = 'stakeholder/stakeholder/index';
$route['stakeholder/survey/(:any)']     = 'stakeholder/stakeholder_survey/$1';
$route['stakeholder/(:any)']            = 'stakeholder/stakeholder/$1';

/*
|--------------------------------------------------------------------------
| Survey Builder Module Routes
|--------------------------------------------------------------------------
*/
$route['survey']                        = 'survey_builder/survey/index';
$route['survey/builder/(:any)']         = 'survey_builder/survey_builder/$1';
$route['survey/logic/(:any)']           = 'survey_builder/survey_logic/$1';
$route['survey/question/(:any)']        = 'survey_builder/survey_question/$1';
$route['survey/(:any)']                 = 'survey_builder/survey/$1';

/*
|--------------------------------------------------------------------------
| Notification Module Routes
|--------------------------------------------------------------------------
*/
$route['notification']                  = 'notification/notification/index';
$route['notification/(:any)']           = 'notification/notification/$1';

/*
|--------------------------------------------------------------------------
| Sync Module Routes
|--------------------------------------------------------------------------
*/
$route['sync']                          = 'sync/sync/index';
$route['sync/(:any)']                   = 'sync/sync/$1';
