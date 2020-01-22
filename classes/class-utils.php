<?php

/**
 * Class to contain Util functions.
 *
 * @link        http://livetime.nu
 * @since       1.0.0
 * @author      Alexander Karlsson <alexander@livetime.nu>
 * @package     BankID
 */

namespace BankID;

use GuzzleHttp\Exception\GuzzleException;

class Utils
{
    const HINT = 'collect';

    const ERROR = 'error';

    const INTERNAL_ERROR = 'internalError';

    const ALREADY_IN_PROGRESS = 'alreadyInProgress';

    const INVALID_PARAMETERS = 'invalidParameters';

    const UNAUTHORIZED = 'unauthorized';

    const NOTFOUND = 'notfound';

    const REQUEST_TIMEOUT = 'requestTimeout';

    const UNSUPPORTED_MEDIA_TYPE = 'unsupportedMediaType';

    const MAINTENANCE = 'Maintenance';

    const EXPIRED_TRANSACTION = 'expiredTransaction';

    const CERTIFICATE_ERR = 'certificateErr';

    const USER_CANCEL = 'userCancel';

    const CANCELLED = 'cancelled';

    const START_FAILED = 'startFailed';

    const OUTSTANDING_TRANSACTION = 'outstandingTransaction';

    const NO_CLIENT = 'noClient';

    const STARTED = 'started';

    const USER_SIGN = 'userSign';

    const COMPLETE = 'complete';

    const _PENDING_UNKNOWN = '_pending_unknown';

    const _FAILED_UNKNOWN = '_failed_unknown';

    private const errors = array(
        self::INVALID_PARAMETERS => array(
            'reason' => 'Invalid parameter. Invalid use of method',
            'action' => "RP must not try the same request again. This is an internal error within RP's system and must not be communicated to the user as a BankID-error.",
        ),
        self::ALREADY_IN_PROGRESS => array(
            'reason' => 'An order for this user is already in progress. The order is aborted. No order is created.',
            'action' => 'RP must inform the user that a login or signing operation is already initiated for this user. Message RFA3 should be used.',
            'message'=> Messages::RFA_3
        ),
        self::UNAUTHORIZED => array(
            'reason' => 'RP does not have access to the service.',
            'action' => 'RP must not try the same request again. This is an internal error within RP\'s system and must not be communicated to the user as a BankID error.'
        ),
        self::NOTFOUND => array(
          'reason' => 'An erroneously URL path was used.',
          'action' => 'RP must not try the same request again. This is an internal error within RP\'s system and must not be communicated to the user as a BankID error.',
        ),
        self::REQUEST_TIMEOUT => array(
          'reason' => 'It took too long time to transmit the request.',
          'action' => 'RP must not automatically try again. This error may occur if the processing at RP or the communication is too slow. RP must inform the user. Message RFA5.',
          'message' => Messages::RFA_5
        ),
        self::UNSUPPORTED_MEDIA_TYPE => array(
          'reason' => 'Adding a "charset" parameter after \'application/json\' is not allowed since the MIME type "application/json" has neither optional nor required parameters. The type is missing or erroneously.',
          'action' => 'RP must not try the same request again. This is an internal error within RP\'s system and must not be communicated to the user as a BankID error'
        ),
        self::INTERNAL_ERROR => array(
            'reason' => 'Internal technical error in the BankID system.',
            'action' => 'RP must not automatically try again. RP must inform the user that a technical error has occurred. Message RFA5 should be used.',
            'message'=> Messages::RFA_5
        ),
        self::MAINTENANCE => array(
            'reason' => 'The service is temporarily out of service.',
            'action' => 'RP may try again without informing the user. If this error is returned repeatedly, RP must inform the user. Message RFA5.',
            'message' => Messages::RFA_5
        )
    );

    private const hintCodes = array(
        self::EXPIRED_TRANSACTION => array(
            'reason' => 'The order has expired. The BankID security app/program did not start, the user did not finalize the signing or the RP called collect too late.',
            'action' => 'RP must inform the user. Message RFA8.',
            'message'=> Messages::RFA_8
        ),
        self::CERTIFICATE_ERR => array(
            'reason' => 'This error is returned if: 1) The user has entered wrong security code too many times. The BankID cannot be used. 2) The users BankID is revoked. 3) The users BankID is invalid.',
            'action' => 'RP must inform the user. Message RFA16.',
            'message'=> Messages::RFA_16
        ),
        self::USER_CANCEL => array(
            'reason' => 'The user decided to cancel the order.',
            'action' => 'RP must inform the user. Message RFA6.',
            'message'=> Messages::RFA_6
        ),
        self::CANCELLED => array(
            'reason' => "The order was cancelled. The system received a new order for the user.",
            'action' => "RP must inform the user. Message RFA3.",
            'message'=> Messages::RFA_3
        ),
        self::START_FAILED => array(
            'reason' => 'The user did not provide her ID, or the RP requires autoStartToken to be used, but the client did not start within a certain time limit. The reason may be: 1) RP did not use autoStartToken when starting BankID security program/app. RP must correct this in their implementation. 2) The client software was not installed or other problem with the user’s computer.',
            'action' => 'The RP must inform the user. Message RFA17.',
            'message' => Messages::RFA_17
        ),
        self::OUTSTANDING_TRANSACTION => array(
            'reason' => 'The order is pending. The client has not yet received the order. The hintCode will later change to noClient, started or userSign.',
            'action' => 'If RP tried to start the client automatically, the RP should inform the user that the app is starting. Message RFA13 should be used. If RP did not try to start the client automatically, the RP should inform the user that she needs to start the app. Message RFA1 should be used.',
            'message' => array(Messages::RFA_13, Messages::RFA_1)
        ),
        self::NO_CLIENT => array(
            'reason' => 'The order is pending. The client has not yet received the order.',
            'action' => 'If RP tried to start the client automatically: This status indicates that the start failed or the users BankID was not available in the started client. RP should inform the user. Message RFA1 should be used. If RP did not try to start the client automatically: This status indicates that the user not yet has started her client. RP should inform the user. Message RFA1 should be used.',
            'message'=> Messages::RFA_1
        ),
        self::STARTED => array(
            'reason' => 'The order is pending. A client has been started with the autostarttoken but a usable ID has not yet been found in the started client. When the client starts there may be a short delay until all ID:s are registered. The user may not have any usable ID:s at all, or has not yet inserted their smart card.',
            'action' => 'If RP does not require the autoStartToken to be used and the user provided her personal number the RP should inform the user of possible solutions. Message RFA14 should be used. If RP require the autoStartToken to be used or the user did not provide her personal number the RP should inform the user of possible solutions. Message RFA15 should be used. Note: started is not an error, RP should keep on polling using collect.',
            'message' => array(Messages::RFA_14_A, Messages::RFA_14_B, Messages::RFA_15_A, Messages::RFA_15_B)
        ),
        self::USER_SIGN => array(
            'reason' => 'The order is pending. The client has received the order.',
            'action' => 'The RP should inform the user. Message RFA9 should be used.',
            'message'=> Messages::RFA_9
        ),
        self::_PENDING_UNKNOWN => array(
          'reason' => 'The order is pending. RP does not recognize the hintcode.',
          'action' => 'Use fallback message.',
          'message' => Messages::RFA_21
        ),
        self::_FAILED_UNKNOWN => array(
          'reason' => 'The order failed. RP does not recognize the hintcode.',
          'action' => 'Use fallback message.',
          'message' => Messages::RFA_22
        )
    );

    public static function message_ids_for($type, $identifier) {
        $id = null;
        $description = self::find_description($type, $identifier);
        if(!is_null($description) && array_key_exists('message', $description)) {
            $id = is_array($description['message']) ? $description['message'] : array($description['message']);
        }
        return $id;
    }

    /**
     * Normalize the given input to UTF-8
     *
     * @since       1.0.0
     * @param       string      The data to normalize
     * @return      string      The data converted to UTF-8
     */
    public static function normalize_text( $input )
    {
        return iconv( mb_detect_encoding( $input, mb_detect_order(), true ), "UTF-8", $input );
    }

    /**
     * Get the path and make sure file exists for the specificed certificate
     * name or certficate absolute path.
     *
     * @since       1.0.1
     * @param       string      $name       The certificate name.
     * @return      string                  Full certificate path.
     */
    public static function get_certificate( $name )
    {
        $cert_path = dirname( __DIR__ ) . DIRECTORY_SEPARATOR . 'certs' . DIRECTORY_SEPARATOR . $name;

        if ( file_exists( $cert_path ) )
            return $cert_path;
        else if(file_exists( $name ))
            return $name;

        return null;
    }

    public static function get_known_error(GuzzleException $fault) {
        $fault = self::convert_fault($fault);
        return $fault && self::is_known_error_identifier($fault->errorCode) ? $fault->errorCode : self::find_description(self::HINT, SELF);
    }

  public static function convert_fault(GuzzleException $fault) {
    if ($fault->getResponse()) {
      return json_decode($fault->getResponse()->getBody());
    }
    return NULL;
  }

    private static function is_known_error_identifier($identifier) {
        return array_key_exists($identifier, self::errors) || array_key_exists($identifier, self::hintCodes);
    }

    private static function find_description($type, $identifier) {
        switch ($type) {
            case self::HINT:
                $definitions = self::hintCodes;
                break;
            case self::ERROR:
                $definitions = self::errors;
                break;
             default:
                return null;
                break;
        }
        return isset($definitions[$identifier]) ? $definitions[$identifier] : null;
    }
}
