<?php
/**
 * CodeIgniter Migrate
 *
 * @author  Natan Felles <natanfelles@gmail.com>
 * @link    http://github.com/natanfelles/codeigniter-migrate
 */
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class Migrate
 */
class Migrate extends CI_Controller
{
    /**
     * @var array Migrations
     */
    protected $migrations;

    /**
     * @var bool Migration Status
     */
    protected $migration_enabled;

    /**
     * Migrate constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->config->load('migration');
        $this->migration_enabled = $this->config->item('migration_enabled');
        if ($this->migration_enabled) {
            $this->load->database();
            $this->load->library('migration');
            $this->migrations = $this->migration->find_migrations();
        }
        $this->load->helper('url');
    }

    /**
     * Index page
     */
    public function index()
    {
        if ($this->migration_enabled) {
            foreach ($this->migrations as $version => $filepath) {
                $fp = explode('/', $filepath);
                $data['migrations'][] = [
                    'version' => $version,
                    'file' => $fp[count($fp) - 1],
                ];
            }
            $migration_db = $this->db->get($this->config->item('migration_table'))->row_array(1);
            $data['current_version'] = $migration_db['version'];
        } else {
            $data['migration_disabled'] = true;
        }
        // You can change the assets links to other versions or to be site relative
        $data['assets'] = [
            'bootstrap_css' => 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css',
            'bootstrap_js' => 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js',
            'jquery' => 'https://code.jquery.com/jquery-2.2.4.min.js'
        ];

        $this->load->view('migrate', $data);
    }

    /**
     * Post page
     */
    public function post()
    {
        if ($this->input->is_ajax_request() && $this->migration_enabled) {
            $version = $this->input->post('version');
            if ($version) {
                // If you works with Foreign Keys look this helper:
                // https://gist.github.com/natanfelles/4024b598f3b31db47c3e139d82dec281
                // $this->load->helper('db');
                $v = $this->migration->version($version);
                if (is_numeric($v)) {
                    $response['type'] = 'success';
                    $response['header'] = 'Sucess!';
                    $response['content'] = "The current version is <strong>{$v}</strong> now.";
                } elseif ($v === true) {
                    $response['type'] = 'info';
                    $response['header'] = 'Info';
                    $response['content'] = 'Migration continues in the same version.';
                }
            }
            header('Content-Type: application/json');
            echo json_encode(isset($response) ? $response : '');
        }
    }

    /**
     * Token page
     */
    public function token()
    {
        header('Content-Type: application/json');
        echo json_encode([
            'name' => $this->security->get_csrf_token_name(),
            'value' => $this->security->get_csrf_hash()
        ]);
    }
}
