<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kurikulum_model extends CI_Model {
    
    public function get_by_prodi($prodi_id, $tahun = null) {
        $this->db->where('prodi_id', $prodi_id);
        if ($tahun) {
            $this->db->where('tahun_kurikulum', $tahun);
        }
        $this->db->order_by('semester', 'ASC');
        return $this->db->get('kurikulum_mata_kuliah')->result();
    }

    public function get_detail($id) {
        return $this->db->get_where('kurikulum_mata_kuliah', ['id' => $id])->row();
    }

    public function save($data) {
        return $this->db->insert('kurikulum_mata_kuliah', $data);
    }

    public function update($id, $data) {
        return $this->db->update('kurikulum_mata_kuliah', $data, ['id' => $id]);
    }

    public function delete($id) {
        return $this->db->delete('kurikulum_mata_kuliah', ['id' => $id]);
    }

    public function get_for_compare($prodi_id, $tahun1, $tahun2) {
        $this->db->where('prodi_id', $prodi_id);
        $this->db->where_in('tahun_kurikulum', [$tahun1, $tahun2]);
        $this->db->order_by('semester', 'ASC');
        return $this->db->get('kurikulum_mata_kuliah')->result();
    }

    public function get_sks_total($prodi_id, $tahun) {
        $this->db->select_sum('sks');
        $this->db->where('prodi_id', $prodi_id);
        $this->db->where('tahun_kurikulum', $tahun);
        $query = $this->db->get('kurikulum_mata_kuliah');
        $row = $query->row();
        return $row->sks ?? 0;
    }

    public function get_all_prodi() {
        return $this->db->get('study_programs')->result();
    }
}
