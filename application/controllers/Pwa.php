<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * PWA Controller
 * Handles Progressive Web App functionality
 */
class Pwa extends CI_Controller {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Show PWA install instructions page
     */
    public function install() {
        $this->load->view('pwa/install');
    }

    /**
     * API: Get offline queue status
     */
    public function get_offline_queue() {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'message' => 'Offline queue endpoint ready'
            ]));
    }

    /**
     * API: Submit survey response (handles both online and offline)
     */
    public function submit() {
        // Check if request is POST
        if ($this->input->method() !== 'post') {
            $this->output
                ->set_status_header(405)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Method not allowed'
                ]));
            return;
        }

        // Get posted data
        $data = $this->input->post(NULL, TRUE);
        
        // Validate required fields
        if (empty($data['survey_id']) || empty($data['answers'])) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Missing required fields: survey_id or answers'
                ]));
            return;
        }

        // Load survey model
        $this->load->model('survey_model');

        // Try to save to database
        try {
            $response_id = $this->survey_model->save_response($data);
            
            if ($response_id) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'success' => true,
                        'message' => 'Survey response saved successfully',
                        'response_id' => $response_id
                    ]));
            } else {
                throw new Exception('Failed to save response');
            }
        } catch (Exception $e) {
            // If database save fails, accept offline submission
            log_message('error', 'Survey save failed: ' . $e->getMessage());
            
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => true,
                    'message' => 'Response queued for offline sync',
                    'offline' => true
                ]));
        }
    }

    /**
     * API: Sync offline data
     */
    public function sync() {
        // Check authentication
        $token = $this->input->get_request_header('Authorization');
        if (!$token) {
            $this->output
                ->set_status_header(401)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Unauthorized'
                ]));
            return;
        }

        // Get posted data
        $data = $this->input->post(NULL, TRUE);
        
        if (empty($data['responses'])) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'No responses to sync'
                ]));
            return;
        }

        $this->load->model('survey_model');
        $synced_count = 0;
        $failed_count = 0;

        foreach ($data['responses'] as $response) {
            try {
                $this->survey_model->save_response($response);
                $synced_count++;
            } catch (Exception $e) {
                log_message('error', 'Sync failed for response: ' . $e->getMessage());
                $failed_count++;
            }
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'message' => 'Sync completed',
                'synced' => $synced_count,
                'failed' => $failed_count
            ]));
    }

    /**
     * Service Worker scope endpoint
     */
    public function sw_scope() {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'scope' => '/',
                'sw_path' => '/assets/pwa/sw.js',
                'manifest_path' => '/assets/pwa/manifest.json'
            ]));
    }
}
