<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Home Controller - Landing Page untuk Masyarakat Umum
 * 
 * Halaman publik yang menampilkan informasi tentang sistem Tracer Study
 * tanpa memerlukan login.
 */
class Home extends MX_Controller {

    public function __construct()
    {
        parent::__construct();
        // Tidak perlu auth check karena ini halaman publik
    }

    /**
     * Halaman utama landing page
     */
    public function index()
    {
        $data['title'] = 'Tracer Study - Sistem Pelacakan Alumni';
        $data['page'] = 'home/landing';
        
        $this->load->view('home/landing', $data);
    }

    /**
     * Informasi tentang sistem
     */
    public function about()
    {
        $data['title'] = 'Tentang Tracer Study';
        $data['page'] = 'home/about';
        
        $this->load->view('home/about', $data);
    }

    /**
     * Informasi kontak
     */
    public function contact()
    {
        $data['title'] = 'Kontak Kami';
        $data['page'] = 'home/contact';
        
        $this->load->view('home/contact', $data);
    }
}
