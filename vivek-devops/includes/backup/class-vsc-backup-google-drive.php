<?php
/**
 * VSC Backup Google Drive Integration
 *
 * @package Vivek_DevOps
 * @version 32.0
 */

if (!defined('ABSPATH')) exit;

class VSC_Backup_Google_Drive {

    private static $instance = null;
    private $client_id;
    private $client_secret;
    private $refresh_token;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->client_id = get_option('vsc_gdrive_client_id');
        $this->client_secret = get_option('vsc_gdrive_client_secret');
        $this->refresh_token = get_option('vsc_gdrive_refresh_token');
    }

    /**
     * Upload backup to Google Drive
     */
    public function upload_backup($file_path) {
        if (!$this->is_configured()) {
            return ['success' => false, 'error' => 'Google Drive not configured'];
        }

        try {
            $access_token = $this->get_access_token();

            $file_name = basename($file_path);
            $file_size = filesize($file_path);

            // Create metadata
            $metadata = [
                'name' => $file_name,
                'mimeType' => 'application/zip'
            ];

            // Upload file
            $boundary = uniqid();
            $delimiter = '--' . $boundary;

            $post_data = '';
            $post_data .= $delimiter . "\r\n";
            $post_data .= "Content-Type: application/json; charset=UTF-8\r\n\r\n";
            $post_data .= json_encode($metadata) . "\r\n";
            $post_data .= $delimiter . "\r\n";
            $post_data .= "Content-Type: application/zip\r\n\r\n";
            $post_data .= file_get_contents($file_path) . "\r\n";
            $post_data .= $delimiter . "--\r\n";

            $response = wp_remote_post('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $access_token,
                    'Content-Type' => 'multipart/related; boundary=' . $boundary,
                    'Content-Length' => strlen($post_data)
                ],
                'body' => $post_data,
                'timeout' => 300
            ]);

            if (is_wp_error($response)) {
                throw new Exception($response->get_error_message());
            }

            $body = json_decode(wp_remote_retrieve_body($response), true);

            if (isset($body['id'])) {
                return [
                    'success' => true,
                    'file_id' => $body['id']
                ];
            } else {
                throw new Exception('Upload failed');
            }

        } catch (Exception $e) {
            error_log('Google Drive upload error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get access token from refresh token
     */
    private function get_access_token() {
        $response = wp_remote_post('https://oauth2.googleapis.com/token', [
            'body' => [
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'refresh_token' => $this->refresh_token,
                'grant_type' => 'refresh_token'
            ]
        ]);

        if (is_wp_error($response)) {
            throw new Exception('Failed to get access token');
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (!isset($body['access_token'])) {
            throw new Exception('No access token in response');
        }

        return $body['access_token'];
    }

    /**
     * Check if Google Drive is configured
     */
    public function is_configured() {
        return !empty($this->client_id) && !empty($this->client_secret) && !empty($this->refresh_token);
    }

    /**
     * Get OAuth authorization URL
     */
    public function get_auth_url() {
        $redirect_uri = admin_url('admin.php?page=vsc-backup');

        $params = [
            'client_id' => $this->client_id,
            'redirect_uri' => $redirect_uri,
            'response_type' => 'code',
            'scope' => 'https://www.googleapis.com/auth/drive.file',
            'access_type' => 'offline',
            'prompt' => 'consent'
        ];

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }

    /**
     * Exchange authorization code for tokens
     */
    public function exchange_code($code) {
        $redirect_uri = admin_url('admin.php?page=vsc-backup');

        $response = wp_remote_post('https://oauth2.googleapis.com/token', [
            'body' => [
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'code' => $code,
                'redirect_uri' => $redirect_uri,
                'grant_type' => 'authorization_code'
            ]
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['refresh_token'])) {
            update_option('vsc_gdrive_refresh_token', $body['refresh_token']);
            return true;
        }

        return false;
    }
}
