<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cpl_model extends CI_Model {

    public function get_by_prodi($prodi_id) {
        $this->db->where('prodi_id', $prodi_id);
        $this->db->order_by('kode_cpl', 'ASC');
        return $this->db->get('cpl')->result();
    }

    public function get_detail($id) {
        return $this->db->get_where('cpl', ['id' => $id])->row();
    }

    public function save($data) {
        return $this->db->insert('cpl', $data);
    }

    public function update($id, $data) {
        return $this->db->update('cpl', $data, ['id' => $id]);
    }

    public function delete($id) {
        return $this->db->delete('cpl', ['id' => $id]);
    }

    public function calculate_gap($prodi_id, $tahun) {
        $cpls = $this->get_by_prodi($prodi_id);
        $results = [];

        foreach ($cpls as $cpl) {
            $alumni_avg = $this->_get_alumni_rating($cpl->id, $tahun);
            $stakeholder_avg = $this->_get_stakeholder_rating($cpl->id, $tahun);

            // KUR-003: Penilaian kesesuaian CPL 60:40 (stakeholder:alumni)
            $realization = (0.6 * $stakeholder_avg) + (0.4 * $alumni_avg);

            $target = $cpl->target_industri ?? 4.0;
            $gap = $target - $realization;

            $results[] = [
                'cpl_id' => $cpl->id,
                'kode_cpl' => $cpl->kode_cpl,
                'deskripsi' => $cpl->deskripsi,
                'aspect' => $cpl->aspect,
                'alumni_score' => round($alumni_avg, 2),
                'stakeholder_score' => round($stakeholder_avg, 2),
                'combined_score' => round($realization, 2),
                'target' => $target,
                'gap' => round($gap, 2),
                'status' => $gap > 0.5 ? 'Perlu Perbaikan Mendesak' : ($gap > 0 ? 'Perlu Perbaikan' : 'Sesuai')
            ];
        }

        return $results;
    }

    private function _get_alumni_rating($cpl_id, $tahun) {
        $this->db->select('AVG(tsd.rating) as avg_rating');
        $this->db->from('tracer_survey_details tsd');
        $this->db->join('tracer_surveys ts', 'ts.id = tsd.survey_id');
        $this->db->join('alumni a', 'a.id = ts.alumni_id');
        $this->db->where('tsd.cpl_id', $cpl_id);
        $this->db->where('YEAR(a.graduation_date)', $tahun);
        $query = $this->db->get();
        $row = $query->row();
        return $row->avg_rating ?? 0;
    }

    private function _get_stakeholder_rating($cpl_id, $tahun) {
        $this->db->select('AVG(ssd.rating) as avg_rating');
        $this->db->from('stakeholder_survey_details ssd');
        $this->db->join('stakeholder_surveys ss', 'ss.id = ssd.survey_id');
        $this->db->join('alumni a', 'a.id = ss.alumni_id');
        $this->db->where('ssd.cpl_id', $cpl_id);
        $this->db->where('YEAR(a.graduation_date)', $tahun);
        $query = $this->db->get();
        $row = $query->row();
        return $row->avg_rating ?? 0;
    }

    public function generate_recommendations($gap_data) {
        $recommendations = [];
        foreach ($gap_data as $item) {
            if ($item['gap'] > 0.5) {
                $rec = "Revitalisasi materi pada CPL {$item['kode_cpl']} ({$item['aspect']}). Gap signifikan terdeteksi (".number_format($item['gap'], 2)."). Disarankan menambahkan studi kasus industri atau praktikum lanjutan.";
                $recommendations[] = ['priority' => 'HIGH', 'cpl' => $item['kode_cpl'], 'text' => $rec];
            } elseif ($item['gap'] > 0) {
                $rec = "Penyesuaian minor pada CPL {$item['kode_cpl']}. Pertimbangkan update referensi buku ajar atau metode pembelajaran.";
                $recommendations[] = ['priority' => 'MEDIUM', 'cpl' => $item['kode_cpl'], 'text' => $rec];
            } else {
                $rec = "CPL {$item['kode_cpl']} sudah sesuai target. Pertahankan kualitas dan lakukan monitoring berkala.";
                $recommendations[] = ['priority' => 'LOW', 'cpl' => $item['kode_cpl'], 'text' => $rec];
            }
        }
        return $recommendations;
    }

    public function get_mapping($cpl_id) {
        return $this->db->where('cpl_id', $cpl_id)->get('cpl_mapping')->result();
    }

    public function save_mapping($data) {
        return $this->db->insert('cpl_mapping', $data);
    }

    public function delete_mapping($id) {
        return $this->db->delete('cpl_mapping', ['id' => $id]);
    }
}
