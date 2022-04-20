<?php
namespace BankID;
use \Exception;

/**
 * Class providing BankID Recommended User Messages.
 * If you want to provide your own custom ones subclass or use
 * register_custom_message(...)
 *
 * @version     2.1.3 Updated to match BankID Relying Party Guidelines version 2.1.3
 * @link        https://www.bankid.com/bankid-i-dina-tjanster/rp-info
 * @since       1.1.0
 * @author      Karl Berggren <kalle@jjabba.com>
 * @package     BankID
 */


class Messages {
    // message types keys
    const DEFAULT_MESSAGE = "default";
    const CUSTOM_MESSAGE  = "custom";

    const RFA_1 = 'RFA1';

    const RFA_2 = 'RFA2';

    const RFA_3 = 'RFA3';

    const RFA_4 = 'RFA4';

    const RFA_5 = 'RFA5';

    const RFA_6 = 'RFA6';

    const RFA_8 = 'RFA8';

    const RFA_9 = 'RFA9';

    const RFA_13 = 'RFA13';

    const RFA_14_A = 'RFA14(A)';

    const RFA_14_B = 'RFA14(B)';

    const RFA_15_A = 'RFA15(A)';

    const RFA_15_B = 'RFA15(B)';

    const RFA_16 = 'RFA16';

    const RFA_17_A = 'RFA17(A)';

    const RFA_17_B = 'RFA17(B)';

    const RFA_18 = 'RFA18';

    const RFA_19 = 'RFA19';

    const RFA_20 = 'RFA20';

    const RFA_21 = 'RFA21';

    const RFA_22 = 'RFA22';

    private static $inited = false;

    private static $identifiers;

    private static $messages;

    private function __construct() {}

    private static function init() {
        if( self::$inited ) {
            return;
        }

        self::$identifiers = array();
        self::$messages    = array();

        // build the message directory
        self::register_identifier(self::RFA_1, array(Utils::OUTSTANDING_TRANSACTION, Utils::NO_CLIENT), array(
            'SV' => 'Starta BankID-appen',
            'EN' => 'Start your BankID app.'
        ));
        self::register_identifier(self::RFA_2, array(/* The BankID app is not installed in the mobile device. */), array(
            'SV' => 'Du har inte BankID-appen installerad. Kontakta din internetbank.',
            'EN' => 'The BankID app is not installed. Please contact your internet bank.'
        ));
        self::register_identifier(self::RFA_3, array(Utils::CANCELLED), array(
            'SV' => 'Åtgärden avbruten. Försök igen.',
            'EN' => 'Action cancelled. Please try again.'
        ));
        self::register_identifier(self::RFA_4, array(Utils::ALREADY_IN_PROGRESS), array(
          'SV' => 'En identifiering eller underskrift för det här personnumret är redan påbörjad. Försök igen.',
          'EN' => 'An identification or signing for this personal number is already started. Please try again.'
        ));
        self::register_identifier(self::RFA_5, array(Utils::INTERNAL_ERROR), array(
          'SV' => "Intern tekniskt fel. Försök igen.",
          'EN' => "Internal error. Please try again."
        ));
        self::register_identifier(self::RFA_6, array(Utils::USER_CANCEL), array(
            'SV' => "Åtgärden avbruten.",
            'EN' => "Action cancelled."
        ));
        self::register_identifier(self::RFA_8, array(Utils::EXPIRED_TRANSACTION), array(
            'SV' => 'BankID-appen svarar inte. Kontrollera att den är startad och att du har internetanslutning. Om du inte har något giltigt BankID kan du hämta ett hos din Bank. Försök sedan igen.',
            'EN' => 'The BankID app is not responding. Please check that the program is started and that you have internet access. If you don’t have a valid BankID you can get one from your bank. Try again.'
        ));
        self::register_identifier(self::RFA_9, array(Utils::USER_SIGN), array(
            'SV' => 'Skriv in din säkerhetskod i BankIDappen och välj Legitimera eller Skriv under.',
            'EN' => 'Enter your security code in the BankID app and select Identify or Sign.'
        ));
        self::register_identifier(self::RFA_13, array(Utils::OUTSTANDING_TRANSACTION), array(
            'SV' => 'Försöker starta BankID-appen.',
            'EN' => 'Trying to start your BankID app.'
        ));
        self::register_identifier(self::RFA_14_A, array(Utils::STARTED /*The RP provided the ID number in the web service call (without using AutoStartTokenRequired). The user accesses the service using a personal computer.*/), array(
            'SV' => 'Söker efter BankID, det kan ta en liten stund... Om det har gått några sekunder och inget BankID har hittats har du sannolikt inget BankID som går att använda för den aktuella identifieringen/underskriften i den här datorn. Om du har ett BankID- kort, sätt in det i kortläsaren. Om du inte har något BankID kan du hämta ett hos din internetbank. Om du har ett BankID på en annan enhet kan du starta din BankID-app där.',
            'EN' => 'Searching for BankID:s, it may take a little while... If a few seconds have passed and still no BankID has been found, you probably don’t have a BankID which can be used for this identification/signing on this computer. If you have a BankID card, please insert it into your card reader. If you don’t have a BankID you can order one from your internet bank. If you have a BankID on another device you can start the BankID app on that device.'
        ));
        self::register_identifier(self::RFA_14_B, array(Utils::STARTED /*The RP provided the ID number in the web service call (without using AutoStartTokenRequired). The user accesses the service using a mobile device.*/), array(
            'SV' => 'Söker efter BankID, det kan ta en liten stund... Om det har gått några sekunder och inget BankID har hittats har du sannolikt inget BankID som går att använda för den aktuella identifieringen/underskriften i den här enheten. Om du inte har något BankID kan du hämta ett hos din internetbank. Om du har ett BankID på en annan enhet kan du starta din BankID-app där.',
            'EN' => 'Searching for BankID:s, it may take a little while... If a few seconds have passed and still no BankID has been found, you probably don’t have a BankID which can be used for this identification/signing on this device. If you don’t have a BankID you can order one from your internet bank. If you have a BankID on another device you can start the BankID app on that device.'
        ));
        self::register_identifier(self::RFA_15_A, array(Utils::STARTED /*The RP did not provide the ID number in the web service call. The user accesses the service using a personal computer.*/), array(
            'SV' => 'Söker efter BankID, det kan ta en liten stund... Om det har gått några sekunder och inget BankID har hittats har du sannolikt inget BankID som går att använda för den aktuella identifieringen/underskriften i den här datorn. Om du har ett BankID- kort, sätt in det i kortläsaren. Om du inte har något BankID kan du hämta ett hos din internetbank.',
            'EN' => 'Searching for BankID:s, it may take a little while... If a few seconds have passed and still no BankID has been found, you probably don’t have a BankID which can be used for this identification/signing on this computer. If you have a BankID card, please insert it into your card reader. If you don’t have a BankID you can order one from your internet bank.'
        ));
        self::register_identifier(self::RFA_15_B, array(Utils::STARTED /*The RP did not provide the ID number in the web service call. The user accesses the service using a mobile device. */), array(
            'SV' => 'Söker efter BankID, det kan ta en liten stund... Om det har gått några sekunder och inget BankID har hittats har du sannolikt inget BankID som går att använda för den aktuella identifieringen/underskriften i den här enheten. Om du inte har något BankID kan du hämta ett hos din internetbank.',
            'EN' => 'Söker efter BankID, det kan ta en liten stund... Om det har gått några sekunder och inget BankID har hittats har du sannolikt inget BankID som går att använda för den aktuella identifieringen/underskriften i den här enheten. Om du inte har något BankID kan du hämta ett hos din internetbank.',
        ));
        self::register_identifier(self::RFA_16, array(Utils::CERTIFICATE_ERR), array(
            'SV' => 'Det BankID du försöker använda är för gammalt eller spärrat. Använd ett annat BankID eller hämta ett nytt hos din internetbank.',
            'EN' => 'The BankID you are trying to use is revoked or too old. Please use another BankID or order a new one from your internet bank.'
        ));
        self::register_identifier(self::RFA_17_A, array(Utils::START_FAILED), array(
            'SV' => 'BankID-appen verkar inte finnas i din dator eller telefon. Installera den och hämta ett BankID hos din internetbank. Installera appen från din appbutik eller https://install.bankid.com.',
            'EN' => 'The BankID app couldn’t be found on your computer or mobile device. Please install it and order a BankID from your internet bank. Install the app from your app store or https://install.bankid.com.',
        ));
        self::register_identifier(self::RFA_17_B, array(Utils::START_FAILED), array(
            'SV' => 'Misslyckades att läsa av QR koden. Starta BankID-appen och läs av QR koden. Kontrollera att BankID-appen är uppdaterad. Om du inte har BankID-appen måste du installera den och hämta ett BankID hos din internetbank. Installera appen från din appbutik eller https://install.bankid.com.',
            'EN' => 'Failed to scan the QR code. Start the BankID app and scan the QR code. Check that the BankID app is up to date. If you don\'t have the BankID app, you need to install it and order a BankID from your internet bank. Install the app from your app store or https://install.bankid.com.'
        ));
        self::register_identifier(self::RFA_18, array( /* The name of link or button used to start the BankID App */ ), array(
            'SV' => 'Starta BankID-appen',
            'EN' => 'Start the BankID app'
        ));
        self::register_identifier(self::RFA_19, array( /*The user access the service using a browser on a personal computer.*/ ), array(
            'SV' => 'Vill du identifiera dig eller skriva under med BankID på den här datorn eller med ett Mobilt BankID?',
            'EN' => 'Would you like to identify yourself or sign with a BankID on this computer or with a Mobile BankID?'
        ));
        self::register_identifier(self::RFA_20, array( /*The user access the service using a browser on a mobile device.*/ ), array(
            'SV' => 'Vill du identifiera dig eller skriva under med ett BankID på den här enheten eller med ett BankID på en annan enhet?',
            'EN' => 'Would you like to identify yourself or sign with a BankID on this device or with a BankID on another device?'
        ));
        self::register_identifier(self::RFA_21, array(), array(
          'SV' => 'Identifiering eller underskrift pågår.',
          'EN' => 'Identification or signing in progress.'
        ));
        self::register_identifier(self::RFA_22, array(), array(
          'SV' => 'Okänt fel. Försök igen.',
          'EN' => 'Unknown error. Please try again.'
        ));
        self::$inited = true;
    }

    public static function get_user_message( $identifier, $language_code = "en") {
        self::init();
        self::throw_if(!self::valid_identifier($identifier), "Invalid identifier: " . $identifier);
        self::throw_if(!self::valid_ISO_639_1($language_code), "Invalid languale code: " . $language_code . " Use only ISO 639-1 codes");
        $language_code = strtolower($language_code);
        $messages = isset(self::$messages[$language_code][$identifier]) ? self::$messages[$language_code][$identifier] : self::$messages['en'][$identifier];

        return isset($messages[self::CUSTOM_MESSAGE]) ? $messages[self::CUSTOM_MESSAGE] : $messages[self::DEFAULT_MESSAGE];
    }

    public static function register_custom_message($identifier, $language_code, $message) {
        self::init();
        return self::register_message( self::CUSTOM_MESSAGE, $identifier, $language_code, $message );
    }

    private static function register_message($type, $identifier, $language_code, $message) {
        $success = false;
        $language_code = strtolower($language_code);

        switch($type){
            case self::DEFAULT_MESSAGE:
            case self::CUSTOM_MESSAGE:
                if(self::valid_identifier($identifier) && self::valid_ISO_639_1($language_code)){
                    self::$messages[$language_code][$identifier][$type] = $message;
                    $success = true;
                }
                break;
            default:
                throw new Exception("Trying to register unkown message type (should not happen)", 1);
        }

        return $success;
    }

    private static function register_identifier($identifier, $mappings, $default_messages = array()) {
        self::throw_if(array_key_exists($identifier, self::$identifiers), "identifier " . $identifier . " is already registered!");
        self::$identifiers[$identifier] = $mappings;
        foreach ($default_messages as $language_code => $message) {
            self::register_message(self::DEFAULT_MESSAGE, $identifier, $language_code, $message);
        }
    }

    private static function valid_identifier($identifier) {
        return array_key_exists($identifier, self::$identifiers);
    }

    private static function valid_ISO_639_1($language_code) {
        return preg_match('/^[a-z]{2}$/i', $language_code) === 1;
    }

    private static function throw_if($result, $error_message) {
        if($result) {
            throw new Exception($error_message, 1);
        }
        return $result;
    }
}
