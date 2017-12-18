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
class Migrate extends CI_Controller {


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
		$this->load->helper('url');
		$this->config->load('migration');
		$this->migration_enabled = $this->config->item('migration_enabled');
		if ($this->migration_enabled && uri_string() != 'migrate/token')
		{
			$this->load->database($this->input->get('dbgroup') ? : '');
			$this->load->library('migration');
			$this->migrations = $this->migration->find_migrations();
		}
	}


	/**
	 * Index page
	 */
	public function index()
	{
		if ($this->migration_enabled)
		{
			foreach ($this->migrations as $version => $filepath)
			{
				$fp = explode(DIRECTORY_SEPARATOR, $filepath);
				$data['migrations'][] = [
					'version' => $version,
					'file'    => $fp[count($fp) - 1],
				];
			}
			$migration_db = $this->db->get($this->config->item('migration_table'))
			                         ->row_array(1);
			$data['current_version'] = $migration_db['version'];
		}
		else
		{
			$data['migration_disabled'] = TRUE;
		}
		// You can change the assets links to other versions or to be site relative
		/*$data['assets'] = [
			'bootstrap_css' => 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css',
			'bootstrap_js'  => 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js',
			'jquery'        => 'https://code.jquery.com/jquery-2.2.4.min.js',
		];*/

		$data['assets'] = [
			'bootstrap_css' => base_url('assets/css/bootstrap.min.css'),
			'bootstrap_js'  => base_url('assets/js/bootstrap.min.js'),
			'jquery'        => base_url('assets/js/jquery-2.2.4.min.js'),
		];

		$dbconfig = $this->get_dbconfig();

		$data['dbgroups']      = $dbconfig['dbgroups'];
		$data['active_group']  = $this->input->get('dbgroup') ? : $dbconfig['active_group'];

		$this->load->view('migrate', $data);
	}


	/**
	 * Post page
	 */
	public function post()
	{
		if ($this->input->is_ajax_request() && $this->migration_enabled)
		{
			// If you works with Foreign Keys look this helper:
			// https://gist.github.com/natanfelles/4024b598f3b31db47c3e139d82dec281
			$this->load->helper('db');
			$version = $this->input->post('version');
			if ($version == 0)
			{
				$this->migration->version(0);
				$response = [
					'type'    => 'success',
					'header'  => 'Sucess!',
					'content' => "Migrations has ben reseted.",
				];
			}
			elseif (array_key_exists($version, $this->migrations))
			{
				$v = $this->migration->version($version);
				if (is_numeric($v))
				{
					$response = [
						'type'    => 'success',
						'header'  => 'Sucess!',
						'content' => "The current version is <strong>{$v}</strong> now.",
					];
				}
				elseif ($v === TRUE)
				{
					$response = [
						'type'    => 'info',
						'header'  => 'Info',
						'content' => 'Migration continues in the same version.',
					];
				}
				elseif ($v === FALSE)
				{
					$response = [
						'type'    => 'danger',
						'header'  => 'Error!',
						'content' => 'Migration failed.',
					];
				}
			}
			else
			{
				$response = [
					'type'    => 'warning',
					'header'  => 'Warning!',
					'content' => 'The migration version <strong>' . htmlentities($version) . '</strong> does not exists.',
				];
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
			'name'  => $this->security->get_csrf_token_name(),
			'value' => $this->security->get_csrf_hash(),
		]);
	}

	/**
	 * Get Database Config file info
	 *
	 * @return array
	 */
	protected function get_dbconfig()
	{
		// Is the config file in the environment folder?
		if ( ! file_exists($file_path = APPPATH.'config/'.ENVIRONMENT.'/database.php')
			&& ! file_exists($file_path = APPPATH.'config/database.php'))
		{
			show_error('The configuration file database.php does not exist.');
		}

		include($file_path);

		return [
			'dbgroups'      => array_keys($db),
			'active_group'  => $active_group,
		];
	}
}
