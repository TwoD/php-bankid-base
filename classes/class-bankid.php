<?php

/**
 * Class to handle BankID Integration
 *
 * @link        http://livetime.nu
 * @since       1.0.0
 * @author      Alexander Karlsson <alexander@livetime.nu>
 * @package     BankID
 */

namespace BankID;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\StreamHandler;
use GuzzleHttp\HandlerStack;
use \Exception as Exception;

class BankID
{

  /**
   * Guzzle client to make API requests to the BankID central server.
   *
   * @since         1.1.0
   * @var \GuzzleHttp\ClientInterface
   */
    protected $client;

    /**
     * Certificates to verify against BankID.
     *
     * @since       1.0.0
     * @var         string          $certs      File path to certificate files.
     */
    protected $_certs;

    /**
     * All the API related URLs.
     *
     * @since       1.0.0
     * @var         string          $api_url        URL to the API.
     * @var         string          $verify_cert    Path to the local CA file to verify the central BankID Server.
     */
    private $_settings;

    /**
     * CN to match.
     *
     * @since       1.0.0
     * @var         string          $_cn_match      The CN to match when making requests.
     */
    protected $_cn_match;

    /**
     * During the initialization of our class we will set the correct urls and ceritifications depending on if
     * it's run as a test or not.
     *
     * @since       1.0.0
     * @param       options     $options       associative array with your setup option.
     */
    public function __construct(Array $user_settings = array(), $client = NULL)
    {

        if ($client) {
          $this->client = $client;
        }

        $fixed_settings = array(
             'test_server' => array(
                 'api_url'   => 'https://appapi2.test.bankid.com/rp/v5.1/',
                 'cert_path' => __DIR__ . '/../certs/appapi2.test.bankid.com.pem',
                 'peer_name' => 'appapi2.test.bankid.com',
             ),
             'production_server' => array(
                 'api_url'   => 'https://appapi2.bankid.com/rp/v5.1/',
                 'cert_path' => __DIR__ . '/../certs/appapi2.bankid.com.pem',
                 'peer_name' => 'appapi2.bankid.com',
             )
        );

        $default_settings = array(
            "production"  => false,
            "certificate" => ''
        );

        // check options for unknown entries
        $invalid_settings = array_diff_key($user_settings, $default_settings);
        if (count($invalid_settings) > 0) {
            throw new Exception("invalid settings: " . implode(array_keys(', ',$invalid_settings)), 1);
        }

        // merge the final settings together
        $this->_settings = array_merge($fixed_settings, array_replace($default_settings, array_intersect_key($user_settings, $default_settings)));
    }

    private function &getClient() {
        if($this->client === null){
            // open an encrypted connection to the BankID server
            $server = $this->_settings['production'] ? $this->_settings['production_server'] : $this->_settings['test_server'];
            $certificate_path = $this->_settings['certificate'];

            // make sure test or production RP certificate exists
            if(!file_exists($certificate_path)){
                throw new Exception("Unable to load your certificate file! " . $certificate_path, 2);
            }

            // make sure the bankid server certificate for the selected server exists
            if(!file_exists($server['cert_path'])){
                throw new Exception("Unable to find bankid certificate: " . $server['cert_path'], 3);
            }

            $stream_handler = new StreamHandler();
            $stack = HandlerStack::create($stream_handler);
            $context_options = array(
                'base_uri' => $server['api_url'],
                'ssl' => array(
                    'verify_depth'          => 5,
                    'peer_name'             => $server['peer_name'],
                    'disable_compression'   => true,
                    'SNI_enabled'           => true,
                    'ciphers'               => 'ALL!EXPORT!EXPORT40!EXPORT56!aNULL!LOW!RC4'
                ),
                'handler' => $stack,
                'cert' => $certificate_path,
                'verify' => $server['cert_path'],
            );
            $this->client = new Client($context_options);
        }

        return $this->client;
    }

    /**
     * Start an authentication process for a user against BankID.
     *
     * @since       1.0.0
     * @param       string      $ssn                   The SSN of the person you want to authenticate, format YYYYMMDDXXXX
     * @param       string      $kwargs                Keyword argument array to allow any number of the additional BankID settings.
     * @param       string      $visible_data          Optional data that the user will be shown when authenticating.
     * @param       string      $hidden_data           Optional data that will be held at BankIDs servers. Example use: Verify that the data signed is correct and hasn't been tampered with.
     * @param       string      $visible_data_format   Can be set to 'simpleMarkdownV1' to allow simple formatting of the visible data.
     *
     * @return      string                             Valid API response or null
     */
    public function authenticate( $ssn, $kwargs = array(), $visible_data = NULL, $hidden_data = NULL , $visible_data_format = NULL)
    {
        $error = null;
        try {
            $rest = $this->getClient();
            $kwargs['personalNumber'] = $ssn;
            if ($visible_data) {
              $kwargs['userVisibleData'] = Utils::normalize_text(base64_encode($visible_data));
            }
            if ($visible_data_format) {
              $kwargs['userVisibleDataFormat'] = $visible_data_format;
            }
            if ($hidden_data) {
              $kwargs['userNonVisibleData'] = Utils::normalize_text(base64_encode($hidden_data));
            }
            $kwargs += ['endUserIp' => $_SERVER['REMOTE_ADDR']];
            $out = json_decode($rest->post( 'auth', ['json' => $kwargs] )->getBody()->getContents());
        } catch ( GuzzleException $fault ) {
            $out = null;
            $error = Utils::get_known_error($fault) ?: Utils::INVALID_PARAMETERS;
        }

        return array($out, $error);
    }

    /**
     * Start a signing process for a user against BankID.
     *
     * @since       1.0.0
     * @param       string      $ssn                   The SSN of the person you want to sign the data.
     * @param       string      $visible_data          The data that the user will be prompted to sign.
     * @param       string      $hidden_data           The data that will be held at BankIDs servers. Example use: Verify that the data signed is correct and hasn't been tampered with.
     * @param       array       $kwargs                Keyword argument array to allow any of the additional BankID settings.
     * @param       string      $visible_data_format   Can be set to 'simpleMarkdownV1' to allow simple formatting of the visible data.
     *
     * @return                                         Valid API response or null
     */
    public function sign( $ssn, $visible_data, $hidden_data = '', $kwargs = array(), $visible_data_format = NULL )
    {
        $error = null;
        try {
            $rest = $this->getClient();
            $kwargs['personalNumber'] = $ssn;
            $kwargs['userVisibleData'] = Utils::normalize_text( base64_encode( $visible_data ) );
            if ($visible_data_format) {
              $kwargs['userVisibleDataFormat'] = $visible_data_format;
            }
            $kwargs['userNonVisibleData'] = Utils::normalize_text( base64_encode( $hidden_data ) );
            $kwargs += ['endUserIp' => $_SERVER['REMOTE_ADDR']];
            $out = json_decode($rest->post('sign', ['json' => $kwargs] )->getBody()->getContents());
        } catch ( GuzzleException $fault ) {
            $out = null;
            $error = Utils::get_known_error($fault) ?: Utils::INVALID_PARAMETERS;
        }

        return array($out, $error);
    }

    /**
     * Collect a response from an ongoing order.
     *
     * @since       1.0.0
     * @param       string      $order_ref      The order reference to collect from.
     * @return                                  Valid BankID response or null
     */
    public function collect( $order_ref )
    {
        $error = null;
        try {
            $rest = $this->getClient();
            $out = json_decode($rest->post('collect', ['json' => ['orderRef' => $order_ref]] )->getBody()->getContents());
        } catch ( GuzzleException $fault ) {
            $out = null;
            $error = Utils::get_known_error($fault) ?: Utils::INVALID_PARAMETERS;
        }

        return array($out, $error);
    }

    /**
     * Cancel a response from an ongoing order.
     *
     * @since       1.0.0
     * @param       string      $order_ref      The order reference to cancel.
     * @return                                  Valid BankID response or null
     */
    public function cancel( $order_ref )
    {
        $error = null;
        try {
            $rest = $this->getClient();
            $out = json_decode($rest->post('cancel', ['json' => ['orderRef' => $order_ref]] )->getBody()->getContents());
        } catch ( GuzzleException $fault ) {
            $out = null;
            $error = Utils::get_known_error($fault) ?: Utils::INVALID_PARAMETERS;
        }

        return array($out, $error);
    }

}
