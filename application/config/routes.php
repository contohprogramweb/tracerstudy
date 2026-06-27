<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Route Configuration for HMVC (Modular Extensions)
|--------------------------------------------------------------------------
|
| This file lets you remap URI requests to specific controllers and methods.
| HMVC routes are organized by module.
|
*/

// Default route
$route['default_controller'] = 'auth';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

/*
|--------------------------------------------------------------------------
| Module Routes - Auth
|--------------------------------------------------------------------------
*/
$route['auth'] = 'auth/auth';
$route['auth/login'] = 'auth/auth/login';
$route['auth/logout'] = 'auth/auth/logout';
$route['auth/register'] = 'auth/auth/register';
$route['auth/forgot-password'] = 'auth/auth/forgot_password';
$route['auth/reset-password'] = 'auth/auth/reset_password';
$route['auth/profile'] = 'auth/auth/profile';
$route['auth/change-password'] = 'auth/auth/change_password';

/*
|--------------------------------------------------------------------------
| Module Routes - Alumni
|--------------------------------------------------------------------------
*/
$route['alumni'] = 'alumni/alumni';
$route['alumni/dashboard'] = 'alumni/alumni/dashboard';
$route['alumni/profile'] = 'alumni/alumni/profile';
$route['alumni/pendidikan'] = 'alumni/alumni/pendidikan';
$route['alumni/pekerjaan'] = 'alumni/alumni/pekerjaan';
$route['alumni/prestasi'] = 'alumni/alumni/prestasi';
$route['alumni/sertifikasi'] = 'alumni/alumni/sertifikasi';

/*
|--------------------------------------------------------------------------
| Module Routes - Survey Builder
|--------------------------------------------------------------------------
*/
$route['survey_builder'] = 'survey_builder/survey_builder';
$route['survey_builder/create'] = 'survey_builder/survey_builder/create';
$route['survey_builder/store'] = 'survey_builder/survey_builder/store';
$route['survey_builder/edit/(:num)'] = 'survey_builder/survey_builder/edit/$1';
$route['survey_builder/update/(:num)'] = 'survey_builder/survey_builder/update/$1';
$route['survey_builder/delete/(:num)'] = 'survey_builder/survey_builder/delete/$1';
$route['survey_builder/preview/(:num)'] = 'survey_builder/survey_builder/preview/$1';
$route['survey_builder/publish/(:num)'] = 'survey_builder/survey_builder/publish/$1';
$route['survey_builder/questions/(:num)'] = 'survey_builder/survey_question/index/$1';
$route['survey_builder/logic/(:num)'] = 'survey_builder/survey_logic/index/$1';

/*
|--------------------------------------------------------------------------
| Module Routes - Notification
|--------------------------------------------------------------------------
*/
$route['notification'] = 'notification/notification';
$route['notification/settings'] = 'notification/notification/settings';
$route['notification/test'] = 'notification/notification/test';
$route['notification/mark-read/(:num)'] = 'notification/notification/mark_read/$1';

/*
|--------------------------------------------------------------------------
| Module Routes - Sync
|--------------------------------------------------------------------------
*/
$route['sync'] = 'sync/sync';
$route['sync/pddikti/(:any)/(:any)'] = 'sync/sync/pddikti/$1/$2';
$route['sync/status/(:any)'] = 'sync/sync/status/$1';
$route['sync/cli/(:any)'] = 'sync/sync/cli/$1';

/*
|--------------------------------------------------------------------------
| Module Routes - Survey
|--------------------------------------------------------------------------
*/
$route['survey'] = 'survey_builder/survey';
$route['survey/list'] = 'survey_builder/survey/index';
$route['survey/fill/(:num)'] = 'survey_builder/survey/fill/$1';
$route['survey/submit/(:num)'] = 'survey_builder/survey/submit/$1';
$route['survey/results/(:num)'] = 'survey_builder/survey/results/$1';

/*
|--------------------------------------------------------------------------
| Module Routes - IKU (Indikator Kinerja Utama)
|--------------------------------------------------------------------------
*/
$route['iku'] = 'iku/iku';
$route['iku/dashboard'] = 'iku/iku/dashboard';
$route['iku/calculate'] = 'iku/iku/calculate';
$route['iku/calculateAll'] = 'iku/iku/calculateAll';
$route['iku/indicator/(:num)'] = 'iku/iku/indicator/$1';
$route['iku/report'] = 'iku/iku/report';
$route['iku/export'] = 'iku/iku/export';
$route['iku/verifikasi'] = 'iku/iku/verifikasi';
$route['iku/triwulan'] = 'iku/iku/triwulan';

/*
|--------------------------------------------------------------------------
| Module Routes - Kurikulum
|--------------------------------------------------------------------------
*/
$route['kurikulum'] = 'kurikulum/kurikulum';
$route['kurikulum/mata-kuliah'] = 'kurikulum/kurikulum/mata_kuliah';
$route['kurikulum/cpmk'] = 'kurikulum/cpl/index';
$route['kurikulum/evaluasi'] = 'kurikulum/kurikulum/evaluasi';
$route['kurikulum/review'] = 'kurikulum/kurikulum/review';
$route['kurikulum/gap-analysis'] = 'kurikulum/gap_analysis/index';

/*
|--------------------------------------------------------------------------
| Module Routes - Stakeholder
|--------------------------------------------------------------------------
*/
$route['stakeholder'] = 'stakeholder/stakeholder';
$route['stakeholder/register'] = 'stakeholder/stakeholder/register';
$route['stakeholder/dashboard'] = 'stakeholder/stakeholder/dashboard';
$route['stakeholder/survey/(:num)'] = 'stakeholder/stakeholder/survey/$1';
$route['stakeholder/submit/(:num)'] = 'stakeholder/stakeholder/submit/$1';
$route['stakeholder/gap-analysis/(:num)'] = 'stakeholder/stakeholder/gapAnalysis/$1';
$route['stakeholder/verify/(:any)'] = 'stakeholder/stakeholder/verify/$1';

/*
|--------------------------------------------------------------------------
| Module Routes - Laporan
|--------------------------------------------------------------------------
*/
$route['laporan'] = 'laporan/laporan';
$route['laporan/dashboard'] = 'laporan/laporan/dashboard';
$route['laporan/status-kerja'] = 'laporan/laporan/statusKerja';
$route['laporan/gaji'] = 'laporan/laporan/gaji';
$route['laporan/masa-tunggu'] = 'laporan/laporan/masaTunggu';
$route['laporan/kompetensi'] = 'laporan/laporan/kompetensi';
$route['laporan/iku'] = 'laporan/laporan/iku';
$route['laporan/kurikulum'] = 'laporan/laporan/kurikulum';
$route['laporan/banpt'] = 'laporan/laporan/banpt';
$route['laporan/ppepp'] = 'laporan/laporan/ppepp';
$route['laporan/export/(:any)'] = 'laporan/laporan/export/$1';
$route['laporan/download/(:any)'] = 'laporan/laporan/download/$1';
$route['laporan/print/(:any)'] = 'laporan/laporan/print/$1';

/*
|--------------------------------------------------------------------------
| Module Routes - Admin
|--------------------------------------------------------------------------
*/
$route['admin'] = 'admin/audit/index';
$route['admin/dashboard'] = 'admin/audit/index';
$route['admin/audit'] = 'admin/audit/index';

/*
|--------------------------------------------------------------------------
| API Routes (Optional)
|--------------------------------------------------------------------------
*/
$route['api/(:any)'] = '$1/api';
