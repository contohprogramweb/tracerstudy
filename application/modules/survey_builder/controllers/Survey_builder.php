<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Survey Builder Controller
 * Handles survey management: create, edit, publish, duplicate, preview
 */
class Survey_builder extends MY_Controller {

    private $core_questions = [
        ['question_text' => 'Apakah Anda puas dengan kualitas pembelajaran di program studi Anda?', 'type' => 'rating', 'is_core' => 1],
        ['question_text' => 'Seberapa baik dosen dalam menjelaskan materi kuliah?', 'type' => 'rating', 'is_core' => 1],
        ['question_text' => 'Apakah fasilitas laboratorium memadai untuk mendukung pembelajaran?', 'type' => 'multiple_choice', 'options' => 'Sangat Memadai|Memadai|Cukup|Kurang|Sangat Kurang', 'is_core' => 1],
        ['question_text' => 'Bagaimana ketersediaan literatur di perpustakaan?', 'type' => 'multiple_choice', 'options' => 'Sangat Lengkap|Lengkap|Cukup|Kurang|Sangat Kurang', 'is_core' => 1],
        ['question_text' => 'Apakah kurikulum sesuai dengan kebutuhan industri saat ini?', 'type' => 'rating', 'is_core' => 1],
        ['question_text' => 'Seberapa efektif metode evaluasi pembelajaran yang diterapkan?', 'type' => 'rating', 'is_core' => 1],
        ['question_text' => 'Apakah Anda mendapatkan bimbingan yang memadai dari dosen pembimbing?', 'type' => 'rating', 'is_core' => 1],
        ['question_text' => 'Bagaimana kualitas layanan administrasi akademik?', 'type' => 'rating', 'is_core' => 1],
        ['question_text' => 'Apakah terdapat kesempatan magang atau praktik kerja lapangan?', 'type' => 'multiple_choice', 'options' => 'Ya, sangat banyak|Ya, cukup|Tidak ada', 'is_core' => 1],
        ['question_text' => 'Seberapa besar kontribusi kegiatan organisasi terhadap pengembangan soft skill Anda?', 'type' => 'rating', 'is_core' => 1],
        ['question_text' => 'Apakah lingkungan kampus mendukung proses belajar?', 'type' => 'rating', 'is_core' => 1],
        ['question_text' => 'Bagaimana kualitas koneksi internet di kampus?', 'type' => 'multiple_choice', 'options' => 'Sangat Baik|Baik|Cukup|Kurang|Sangat Kurang', 'is_core' => 1],
        ['question_text' => 'Apakah Anda merasa siap bekerja setelah lulus?', 'type' => 'rating', 'is_core' => 1],
        ['question_text' => 'Seberapa relevan tugas akhir/skripsi dengan bidang minat Anda?', 'type' => 'rating', 'is_core' => 1],
        ['question_text' => 'Apakah informasi akademik disampaikan dengan jelas dan tepat waktu?', 'type' => 'rating', 'is_core' => 1],
        ['question_text' => 'Bagaimana penilaian Anda terhadap etika dan profesionalisme dosen?', 'type' => 'rating', 'is_core' => 1],
        ['question_text' => 'Apakah terdapat dukungan karir dari kampus?', 'type' => 'multiple_choice', 'options' => 'Sangat Baik|Baik|Cukup|Kurang|Tidak Ada', 'is_core' => 1],
        ['question_text' => 'Seberapa puas Anda dengan keseluruhan pengalaman kuliah?', 'type' => 'rating', 'is_core' => 1],
        ['question_text' => 'Apakah Anda akan merekomendasikan program studi ini kepada calon mahasiswa?', 'type' => 'multiple_choice', 'options' => 'Sangat Merekomendasikan|Merekomendasikan|Ragu-ragu|Tidak Merekomendasikan', 'is_core' => 1],
        ['question_text' => 'Saran perbaikan untuk program studi:', 'type' => 'long_answer', 'is_core' => 1]
    ];

    public function __construct() {
        parent::__construct();
        $this->load->model('survey_model');
        $this->load->helper(['form', 'url']);
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
    }

    public function index() {
        $data['surveys'] = $this->survey_model->get_all_surveys();
        $this->load->view('survey_builder/index', $data);
    }

    public function create() {
        $this->load->view('survey_builder/create', ['core_questions' => $this->core_questions]);
    }

    public function store() {
        $this->form_validation->set_rules('title', 'Judul Survey', 'required|trim|max_length[255]');
        $this->form_validation->set_rules('description', 'Deskripsi', 'trim');
        $this->form_validation->set_rules('add_core', 'Tambah Pertanyaan Inti', 'trim');

        if ($this->form_validation->run() == FALSE) {
            $this->load->view('survey_builder/create', ['core_questions' => $this->core_questions]);
        } else {
            $data = [
                'title' => $this->input->post('title'),
                'description' => $this->input->post('description'),
                'status' => 'draft',
                'created_by' => $this->session->userdata('user_id'),
                'created_at' => date('Y-m-d H:i:s')
            ];

            $survey_id = $this->survey_model->insert($data);

            // Add core questions if requested
            if ($this->input->post('add_core') === '1') {
                foreach ($this->core_questions as $index => $q) {
                    $question_data = [
                        'survey_id' => $survey_id,
                        'question_text' => $q['question_text'],
                        'type' => $q['type'],
                        'options' => isset($q['options']) ? $q['options'] : null,
                        'is_required' => 1,
                        'is_core' => 1,
                        'order' => $index + 1
                    ];
                    $this->survey_model->insert_question($question_data);
                }
            }

            $this->session->set_flashdata('success', 'Survey berhasil dibuat!');
            redirect('survey_builder/edit/' . $survey_id);
        }
    }

    public function edit($id) {
        $data['survey'] = $this->survey_model->get_by_id($id);
        
        if (!$data['survey']) {
            show_404();
        }

        if ($data['survey']->status === 'published') {
            $this->session->set_flashdata('error', 'Survey yang sudah dipublikasikan tidak dapat diubah strukturnya.');
            redirect('survey_builder/preview/' . $id);
        }

        $data['questions'] = $this->survey_model->get_questions($id);
        $this->load->view('survey_builder/edit', $data);
    }

    public function update($id) {
        $survey = $this->survey_model->get_by_id($id);
        
        if (!$survey || $survey->status === 'published') {
            $this->output->set_status_header(403);
            echo json_encode(['success' => false, 'message' => 'Survey tidak ditemukan atau sudah dipublikasikan.']);
            return;
        }

        $this->form_validation->set_rules('title', 'Judul Survey', 'required|trim|max_length[255]');
        $this->form_validation->set_rules('description', 'Deskripsi', 'trim');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('survey_builder/edit/' . $id);
        } else {
            $data = [
                'title' => $this->input->post('title'),
                'description' => $this->input->post('description'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->survey_model->update($id, $data);
            $this->session->set_flashdata('success', 'Survey berhasil diperbarui!');
            redirect('survey_builder/edit/' . $id);
        }
    }

    public function delete($id) {
        $survey = $this->survey_model->get_by_id($id);
        
        if (!$survey) {
            show_404();
        }

        if ($survey->status === 'published') {
            $this->session->set_flashdata('error', 'Survey yang sudah dipublikasikan tidak dapat dihapus.');
            redirect('survey_builder/index');
        }

        $this->survey_model->delete($id);
        $this->session->set_flashdata('success', 'Survey berhasil dihapus!');
        redirect('survey_builder/index');
    }

    public function publish($id) {
        $survey = $this->survey_model->get_by_id($id);
        
        if (!$survey || $survey->status === 'published') {
            $this->output->set_status_header(403);
            echo json_encode(['success' => false, 'message' => 'Survey tidak valid untuk dipublikasikan.']);
            return;
        }

        // BR-SUR-002: Min 20 pertanyaan inti untuk publish
        $core_count = $this->survey_model->count_core_questions($id);
        
        if ($core_count < 20) {
            $this->output->set_status_header(400);
            echo json_encode([
                'success' => false, 
                'message' => "Jumlah pertanyaan inti harus minimal 20. Saat ini: {$core_count}"
            ]);
            return;
        }

        $data = [
            'status' => 'published',
            'published_at' => date('Y-m-d H:i:s'),
            'published_by' => $this->session->userdata('user_id')
        ];

        $this->survey_model->update($id, $data);
        echo json_encode(['success' => true, 'message' => 'Survey berhasil dipublikasikan!']);
    }

    public function duplicate($id) {
        $original = $this->survey_model->get_by_id($id);
        
        if (!$original) {
            show_404();
        }

        $new_data = [
            'title' => $original->title . ' (Copy)',
            'description' => $original->description,
            'status' => 'draft',
            'created_by' => $this->session->userdata('user_id'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $new_id = $this->survey_model->insert($new_data);

        // Copy questions
        $questions = $this->survey_model->get_questions($id);
        foreach ($questions as $q) {
            $question_data = [
                'survey_id' => $new_id,
                'question_text' => $q->question_text,
                'type' => $q->type,
                'options' => $q->options,
                'is_required' => $q->is_required,
                'is_core' => $q->is_core,
                'order' => $q->order
            ];
            $this->survey_model->insert_question($question_data);
        }

        // Copy logic jumps
        $logics = $this->survey_model->get_logics($id);
        foreach ($logics as $logic) {
            $logic_data = [
                'survey_id' => $new_id,
                'question_id' => $this->_map_old_to_new_id($id, $new_id, $logic->question_id),
                'condition_value' => $logic->condition_value,
                'target_question_id' => $this->_map_old_to_new_id($id, $new_id, $logic->target_question_id)
            ];
            $this->survey_model->insert_logic($logic_data);
        }

        $this->session->set_flashdata('success', 'Survey berhasil diduplikasi!');
        redirect('survey_builder/edit/' . $new_id);
    }

    private function _map_old_to_new_id($old_survey_id, $new_survey_id, $old_question_id) {
        // Simplified mapping - in real app would need proper ID mapping
        $questions = $this->survey_model->get_questions($old_survey_id);
        $new_questions = $this->survey_model->get_questions($new_survey_id);
        
        $index = 0;
        foreach ($questions as $q) {
            if ($q->id == $old_question_id) {
                return $new_questions[$index]->id ?? null;
            }
            $index++;
        }
        return null;
    }

    public function preview($id) {
        $data['survey'] = $this->survey_model->get_by_id($id);
        
        if (!$data['survey']) {
            show_404();
        }

        $data['questions'] = $this->survey_model->get_questions_with_logic($id);
        $this->load->view('survey_builder/preview', $data);
    }
}
