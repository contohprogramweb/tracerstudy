<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller: Stakeholder
 * Modul Survey Stakeholder/Pengguna Lulusan
 * 
 * Business Rules:
 * - BR-SUR-006: Stakeholder survey wajib linked ke alumni atau prodi
 * - BR-SUR-007: Rating kompetensi di-average 60:40 (stakeholder:alumni)
 */
class Stakeholder extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Stakeholder_model');
        $this->load->model('Stakeholder_survey_model');
        $this->load->library('form_validation');
        $this->load->library('email');
        
        // Check authentication
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
    }

    /**
     * Registrasi DUDI/Employer baru
     * 
     * @return void
     */
    public function register()
    {
        $data['page_title'] = 'Registrasi Stakeholder/DUDI';
        $data['industries'] = $this->config->item('industries'); // Load from config
        
        if ($this->input->post()) {
            $this->form_validation->set_rules([
                'company_name' => 'required|min_length[3]',
                'company_type' => 'required',
                'industry' => 'required',
                'address' => 'required',
                'contact_person' => 'required',
                'email' => 'required|valid_email',
                'phone' => 'required'
            ]);

            if ($this->form_validation->run() == TRUE) {
                $stakeholder_data = [
                    'company_name' => $this->input->post('company_name'),
                    'company_type' => $this->input->post('company_type'),
                    'industry' => $this->input->post('industry'),
                    'address' => $this->input->post('address'),
                    'city' => $this->input->post('city'),
                    'province' => $this->input->post('province'),
                    'contact_person' => $this->input->post('contact_person'),
                    'position' => $this->input->post('position'),
                    'email' => $this->input->post('email'),
                    'phone' => $this->input->post('phone'),
                    'website' => $this->input->post('website'),
                    'status' => 'pending', // pending, active, inactive
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => $this->session->userdata('user_id')
                ];

                $stakeholder_id = $this->Stakeholder_model->insert($stakeholder_data);

                if ($stakeholder_id) {
                    // Send verification email
                    $this->_sendVerificationEmail($stakeholder_id, $stakeholder_data['email']);
                    
                    $this->session->set_flashdata('success', 'Registrasi berhasil. Silakan cek email untuk verifikasi.');
                    redirect('stakeholder/dashboard');
                } else {
                    $this->session->set_flashdata('error', 'Registrasi gagal. Silakan coba lagi.');
                }
            }
        }

        $this->load->view('stakeholder/register', $data);
    }

    /**
     * Dashboard stakeholder
     * Menampilkan statistik dan aktivitas stakeholder
     * 
     * @return void
     */
    public function dashboard()
    {
        $user_id = $this->session->userdata('user_id');
        $stakeholder = $this->Stakeholder_model->getByUserId($user_id);

        if (!$stakeholder) {
            redirect('stakeholder/register');
        }

        $data['stakeholder'] = $stakeholder;
        $data['page_title'] = 'Dashboard Stakeholder';
        
        // Statistik
        $data['stats'] = [
            'total_surveys' => $this->Stakeholder_survey_model->countByStakeholder($stakeholder['id']),
            'pending_surveys' => $this->Stakeholder_survey_model->countByStakeholder($stakeholder['id'], 'pending'),
            'completed_surveys' => $this->Stakeholder_survey_model->countByStakeholder($stakeholder['id'], 'completed'),
            'total_alumni_assessed' => $this->Stakeholder_survey_model->countUniqueAlumni($stakeholder['id'])
        ];

        // Recent surveys
        $data['recent_surveys'] = $this->Stakeholder_survey_model->getRecentByStakeholder($stakeholder['id'], 5);

        // Pending invitations
        $data['pending_invitations'] = $this->Stakeholder_survey_model->getPendingInvitations($stakeholder['id']);

        $this->load->view('stakeholder/dashboard', $data);
    }

    /**
     * Form survey untuk menilai alumni tertentu
     * 
     * @param int $alumni_id
     * @return void
     */
    public function survey($alumni_id)
    {
        $user_id = $this->session->userdata('user_id');
        $stakeholder = $this->Stakeholder_model->getByUserId($user_id);

        if (!$stakeholder) {
            show_error('Anda harus registrasi sebagai stakeholder terlebih dahulu', 403);
        }

        // Cek apakah sudah ada survey yang belum selesai
        $existing = $this->Stakeholder_survey_model->getByAlumniAndStakeholder($alumni_id, $stakeholder['id'], 'pending');
        
        if ($existing) {
            redirect('stakeholder_survey/edit/' . $existing['id']);
        }

        // Get alumni data
        $this->load->model('Alumni_model');
        $data['alumni'] = $this->Alumni_model->getById($alumni_id);

        if (!$data['alumni']) {
            show_404('Alumni tidak ditemukan');
        }

        // Check BR-SUR-006: Must be linked to alumni or prodi
        $data['alumni_id'] = $alumni_id;
        $data['prodi_id'] = $data['alumni']['prodi_id'];

        // Get CPL for this prodi
        $this->load->model('CPL_model');
        $data['cpl_list'] = $this->CPL_model->getByProdi($data['prodi_id']);

        // Get existing survey if any
        $data['survey'] = null;
        $invitation = $this->Stakeholder_survey_model->getInvitation($alumni_id, $stakeholder['id']);
        if ($invitation) {
            $data['survey'] = $invitation;
        }

        $data['page_title'] = 'Penilaian Kompetensi Alumni';
        $data['rating_options'] = [
            1 => 'Sangat Kurang',
            2 => 'Kurang',
            3 => 'Cukup',
            4 => 'Baik',
            5 => 'Sangat Baik'
        ];

        $data['kesesuaian_options'] = [
            'sangat_sesuai' => 'Sangat Sesuai',
            'sesuai' => 'Sesuai',
            'kurang' => 'Kurang Sesuai',
            'tidak_sesuai' => 'Tidak Sesuai'
        ];

        $this->load->view('stakeholder/survey', $data);
    }

    /**
     * Submit penilaian stakeholder
     * 
     * @param int $alumni_id
     * @return void
     */
    public function submit($alumni_id)
    {
        $this->load->library('form_validation');
        
        $user_id = $this->session->userdata('user_id');
        $stakeholder = $this->Stakeholder_model->getByUserId($user_id);

        if (!$stakeholder) {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Stakeholder tidak terdaftar']));
            return;
        }

        // Validation
        $this->form_validation->set_rules([
            'work_period' => 'required',
            'work_position' => 'required'
        ]);

        if ($this->form_validation->run() == FALSE) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false, 
                    'message' => 'Validasi gagal',
                    'errors' => validation_errors()
                ]));
            return;
        }

        // Get CPL ratings from POST
        $cpl_ratings = $this->input->post('cpl_ratings');
        $cpl_kesesuaian = $this->input->post('cpl_kesesuaian');
        
        if (empty($cpl_ratings)) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false, 
                    'message' => 'Penilaian CPL wajib diisi'
                ]));
            return;
        }

        // Prepare survey data
        $survey_data = [
            'alumni_id' => $alumni_id,
            'stakeholder_id' => $stakeholder['id'],
            'prodi_id' => $this->input->post('prodi_id'),
            'work_period' => $this->input->post('work_period'),
            'work_position' => $this->input->post('work_position'),
            'company_feedback' => $this->input->post('company_feedback'),
            'recommended_competencies' => $this->input->post('recommended_competencies'),
            'curriculum_suggestions' => $this->input->post('curriculum_suggestions'),
            'status' => 'completed',
            'submitted_at' => date('Y-m-d H:i:s'),
            'submitted_by' => $user_id
        ];

        // Save survey header
        $survey_id = $this->Stakeholder_survey_model->create($survey_data);

        if ($survey_id) {
            // Save CPL ratings
            $ratings_data = [];
            foreach ($cpl_ratings as $cpl_id => $rating) {
                $ratings_data[] = [
                    'survey_id' => $survey_id,
                    'cpl_id' => $cpl_id,
                    'rating' => $rating,
                    'kesesuaian' => isset($cpl_kesesuaian[$cpl_id]) ? $cpl_kesesuaian[$cpl_id] : null
                ];
            }

            $this->Stakeholder_survey_model->saveCplRatings($ratings_data);

            // Calculate average rating (BR-SUR-007 will be applied in aggregation)
            $average_rating = array_sum($cpl_ratings) / count($cpl_ratings);
            $this->Stakeholder_survey_model->update($survey_id, ['average_rating' => $average_rating]);

            // Send notification to alumni and prodi
            $this->_sendSubmissionNotification($survey_id);

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => true, 
                    'message' => 'Penilaian berhasil disimpan',
                    'survey_id' => $survey_id
                ]));
        } else {
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false, 
                    'message' => 'Gagal menyimpan penilaian'
                ]));
        }
    }

    /**
     * View Gap Analysis CPL per Program Studi
     * Membandingkan ekspektasi stakeholder dengan capaian alumni
     * 
     * @param int $prodi_id
     * @return void
     */
    public function gapAnalysis($prodi_id)
    {
        $data['page_title'] = 'Gap Analysis CPL';
        $data['prodi_id'] = $prodi_id;

        // Get prodi info
        $this->load->model('Prodi_model');
        $data['prodi'] = $this->Prodi_model->getById($prodi_id);

        if (!$data['prodi']) {
            show_404('Program Studi tidak ditemukan');
        }

        // Get CPL list
        $this->load->model('CPL_model');
        $data['cpl_list'] = $this->CPL_model->getByProdi($prodi_id);

        // Get stakeholder survey averages
        $data['stakeholder_averages'] = $this->Stakeholder_survey_model->getAverageRatingByCpl($prodi_id);

        // Get alumni self-assessment averages (BR-SUR-007: 60:40 ratio)
        $this->load->model('Alumni_survey_model');
        $data['alumni_averages'] = $this->Alumni_survey_model->getAverageRatingByCpl($prodi_id);

        // Calculate combined average (60% stakeholder : 40% alumni) - BR-SUR-007
        $data['combined_averages'] = [];
        foreach ($data['cpl_list'] as $cpl) {
            $stakeholder_avg = isset($data['stakeholder_averages'][$cpl['id']]) ? $data['stakeholder_averages'][$cpl['id']] : 0;
            $alumni_avg = isset($data['alumni_averages'][$cpl['id']]) ? $data['alumni_averages'][$cpl['id']] : 0;
            
            // BR-SUR-007: 60:40 ratio
            $combined = ($stakeholder_avg * 0.6) + ($alumni_avg * 0.4);
            
            $data['combined_averages'][$cpl['id']] = [
                'cpl_id' => $cpl['id'],
                'cpl_code' => $cpl['code'],
                'cpl_description' => $cpl['description'],
                'stakeholder_avg' => round($stakeholder_avg, 2),
                'alumni_avg' => round($alumni_avg, 2),
                'combined_avg' => round($combined, 2),
                'gap' => round($stakeholder_avg - $alumni_avg, 2)
            ];
        }

        // Get recommendations summary
        $data['recommendations'] = $this->Stakeholder_survey_model->getRecommendationsSummary($prodi_id);

        $this->load->view('stakeholder/gap_analysis', $data);
    }

    /**
     * Send verification email to new stakeholder
     * 
     * @param int $stakeholder_id
     * @param string $email
     * @return void
     */
    private function _sendVerificationEmail($stakeholder_id, $email)
    {
        $verification_token = md5(uniqid(rand(), true));
        
        // Save token
        $this->Stakeholder_model->update($stakeholder_id, [
            'verification_token' => $verification_token
        ]);

        $verification_link = site_url('stakeholder/verify/' . $verification_token);

        $this->email->from('noreply@university.edu', 'University Alumni System');
        $this->email->to($email);
        $this->email->subject('Verifikasi Akun Stakeholder');
        
        $message = "Terima kasih telah mendaftar sebagai stakeholder.\n\n";
        $message .= "Silakan klik link berikut untuk verifikasi akun Anda:\n";
        $message .= $verification_link . "\n\n";
        $message .= "Link ini akan kadaluarsa dalam 24 jam.";

        $this->email->message($message);
        $this->email->send();
    }

    /**
     * Send notification when survey is submitted
     * 
     * @param int $survey_id
     * @return void
     */
    private function _sendSubmissionNotification($survey_id)
    {
        $survey = $this->Stakeholder_survey_model->getById($survey_id);
        
        // Notify alumni
        $this->load->model('Alumni_model');
        $alumni = $this->Alumni_model->getById($survey['alumni_id']);
        
        if ($alumni && $alumni['email']) {
            $this->email->from('noreply@university.edu', 'University Alumni System');
            $this->email->to($alumni['email']);
            $this->email->subject('Penilaian Kompetensi dari Stakeholder');
            
            $message = "Halo {$alumni['name']},\n\n";
            $message .= "Seorang stakeholder telah memberikan penilaian terhadap kompetensi Anda.\n\n";
            $message .= "Silakan login ke sistem untuk melihat detail penilaian.";

            $this->email->message($message);
            $this->email->send();
        }

        // Notify prodi
        $this->load->model('Prodi_model');
        $prodi = $this->Prodi_model->getById($survey['prodi_id']);
        
        if ($prodi && $prodi['email']) {
            $this->email->to($prodi['email']);
            $this->email->subject('Penilaian Kompetensi Alumni oleh Stakeholder');
            
            $message = "Halo Kaprodi {$prodi['name']},\n\n";
            $message .= "Telah ada penilaian kompetensi alumni oleh stakeholder.\n\n";
            $message .= "Silakan cek dashboard untuk melihat detail dan gap analysis.";

            $this->email->message($message);
            $this->email->send();
        }
    }

    /**
     * Send invitation email to stakeholder for survey
     * 
     * @param int $alumni_id
     * @param int $stakeholder_id
     * @return void
     */
    public function sendInvitation($alumni_id, $stakeholder_id)
    {
        $this->load->model('Alumni_model');
        $alumni = $this->Alumni_model->getById($alumni_id);
        
        $stakeholder = $this->Stakeholder_model->getById($stakeholder_id);

        if (!$alumni || !$stakeholder) {
            return false;
        }

        // Create invitation record
        $invitation_data = [
            'alumni_id' => $alumni_id,
            'stakeholder_id' => $stakeholder_id,
            'prodi_id' => $alumni['prodi_id'],
            'status' => 'sent',
            'sent_at' => date('Y-m-d H:i:s')
        ];

        $invitation_id = $this->Stakeholder_survey_model->createInvitation($invitation_data);

        // Generate unique survey link
        $survey_token = md5(uniqid(rand(), true));
        $this->Stakeholder_survey_model->updateInvitation($invitation_id, [
            'survey_token' => $survey_token
        ]);

        $survey_link = site_url('stakeholder/survey/' . $alumni_id . '?token=' . $survey_token);

        // Send email
        $this->email->from('noreply@university.edu', 'University Alumni System');
        $this->email->to($stakeholder['email']);
        $this->email->subject('Undangan Penilaian Kompetensi Alumni');
        
        $message = "Yth. {$stakeholder['contact_person']},\n\n";
        $message .= "Kami mengundang Bapak/Ibu untuk memberikan penilaian terhadap kompetensi alumni kami:\n\n";
        $message .= "Nama: {$alumni['name']}\n";
        $message .= "Program Studi: {$alumni['prodi_name']}\n";
        $message .= "Angkatan: {$alumni['graduation_year']}\n\n";
        $message .= "Penilaian Bapak/Ibu sangat berharga bagi peningkatan kualitas pendidikan kami.\n\n";
        $message .= "Silakan klik link berikut untuk mengisi survey:\n";
        $message .= $survey_link . "\n\n";
        $message .= "Terima kasih atas partisipasi Bapak/Ibu.";

        $this->email->message($message);
        
        if ($this->email->send()) {
            $this->Stakeholder_survey_model->updateInvitation($invitation_id, ['status' => 'delivered']);
            return true;
        }

        return false;
    }

    /**
     * Verify stakeholder email
     * 
     * @param string $token
     * @return void
     */
    public function verify($token)
    {
        $stakeholder = $this->Stakeholder_model->getByToken($token);

        if ($stakeholder) {
            $this->Stakeholder_model->update($stakeholder['id'], [
                'status' => 'active',
                'verified_at' => date('Y-m-d H:i:s'),
                'verification_token' => null
            ]);

            $this->session->set_flashdata('success', 'Email berhasil diverifikasi. Akun Anda telah aktif.');
            redirect('stakeholder/dashboard');
        } else {
            $this->session->set_flashdata('error', 'Token verifikasi tidak valid atau sudah kadaluarsa.');
            redirect('stakeholder/register');
        }
    }
}
