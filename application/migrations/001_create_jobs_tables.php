<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration: Create Jobs and Failed Jobs Tables
 */
class Migration_Create_jobs_tables extends CI_Migration {

    public function up()
    {
        // Jobs table
        $this->dbforge->add_field([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ],
            'queue' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => FALSE
            ],
            'payload' => [
                'type' => 'TEXT',
                'null' => FALSE
            ],
            'attempts' => [
                'type' => 'TINYINT',
                'constraint' => 3,
                'unsigned' => TRUE,
                'default' => 0
            ],
            'reserved_at' => [
                'type' => 'TIMESTAMP',
                'null' => TRUE
            ],
            'available_at' => [
                'type' => 'TIMESTAMP',
                'null' => FALSE
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => FALSE
            ]
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key(['queue', 'available_at']);
        $this->dbforge->create_table('jobs', TRUE);

        // Failed jobs table (Dead Letter Queue)
        $this->dbforge->add_field([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ],
            'job_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => TRUE,
                'null' => FALSE
            ],
            'queue' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => FALSE
            ],
            'payload' => [
                'type' => 'TEXT',
                'null' => FALSE
            ],
            'exception' => [
                'type' => 'TEXT',
                'null' => FALSE
            ],
            'failed_at' => [
                'type' => 'TIMESTAMP',
                'null' => FALSE
            ]
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('job_id');
        $this->dbforge->create_table('failed_jobs', TRUE);
    }

    public function down()
    {
        $this->dbforge->drop_table('failed_jobs');
        $this->dbforge->drop_table('jobs');
    }
}
