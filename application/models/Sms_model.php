<?php

class Sms_model extends CI_Model {

    const SKEBBY_CLASSIC = 'classic';
    const SKEBBY_PLUS = 'plus';
    const SKEBBY_BASIC = 'basic';
    const INTL_PREFIX_DEFAULT = 39;

    /**
     * Sender data
     * 
     * @var array
     */
    private $sender = [];

    /**
     * Array contenente i parametri di connessione ai vari servizi
     * 
     * @var array
     */
    private $services = [];

    /**
     * Class constructor
     */
    public function __construct() {
        parent::__construct();

        // Setup with empty sender
        $this->setSender();
    }

    /**
     * Imposta dati sender
     * 
     * @param string|null $name
     * @param string|null $number
     */
    public function setSender($name = null, $number = null) {
        $this->sender = compact('name', 'number');
    }

    /**
     * Aggiungi i parametri di connessione per skebby
     * 
     * @param string $username
     * @param string $password
     * @param bool $test
     * @return \Sms_model
     */
    public function skebbyConnect($username, $password, $test = false) {
        require_once FCPATH . '/class/skebby_sms.php';

        $this->setServiceParams('skebby', [
            'username' => $username,
            'password' => $password,
            'testMode' => (bool) $test
        ]);

        return $this;
    }

    /**
     * Invia sms usando skebby. Modalità strict: bloccante, altrimenti non
     * bloccante.
     *   - modalità non strict desiderabile per invii multipli o quando si vuole
     *     lasciare che il sistema configuri automaticamente l'invio in caso di
     *     errori non bloccanti
     *   - modalità strict lancia un'eccezione al minimo problema (un numero non
     *     corretto, ecc...). In genere si usa in operazioni mission critical,
     *     quando è indispensabile che il messaggio venga recapitato
     * 
     * @param array|string $numbers
     * @param string $text
     * @param array $data
     * @param bool $strict
     * @param string $type
     * @param string|null $senderName
     * @param string|null $senderNum
     * 
     * @return int
     * 
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function skebbySend($numbers, $text, array $data = [], $strict = false, $type = self::SKEBBY_CLASSIC, $senderName = null, $senderNum = null) {

        // Recupera parametri skebby dal collettore servizi
        $params = $this->getServiceParams('skebby');

        // Normalizzazione numeri:
        // ---
        // I numeri verranno ciclati e a ciascuno verrà anteposto il prefisso
        // internazionale senza +.
        // Nel caso in cui il prefisso non sia passato assieme al numero, verrà
        // impostato il prefisso di default
        if (!is_array($numbers)) {
            $numbers = [$numbers];
        }

        foreach ($numbers as $k => &$number) {
            if ($strict) {
                $number = $this->normalizePhoneNumber($number);
            } else {
                try {
                    $number = $this->normalizePhoneNumber($number);
                } catch (Exception $ex) {
                    unset($numbers[$k]);
                }
            }
        }
        
        if (!$numbers) {
            // Nessun numero? 0 sms inviati
            return 0;
        }
        
        
        // Determina il tipo di sms da inviare [strettamente legato al servizio
        // skebby]
        switch ($type) {
            case self::SKEBBY_BASIC:
                $type = $params['testMode']? SMS_TYPE_TEST_BASIC: SMS_TYPE_BASIC;
                break;
            
            case self::SKEBBY_PLUS:
                $type = $params['testMode']? SMS_TYPE_TEST_CLASSIC_PLUS: SMS_TYPE_CLASSIC_PLUS;
                break;
            
            default:
                if (!$strict OR $type === self::SKEBBY_CLASSIC) {
                    // Di default imposto il tipo classic se siamo in modalità
                    // NON STRICT oppure se effetticamente abbiamo richiesto
                    // l'invio di un sms classic
                    $type = $params['testMode']? SMS_TYPE_TEST_CLASSIC: SMS_TYPE_CLASSIC;
                } else {
                    // In tutti gli altri casi lancio un'eccezione per bloccare
                    // l'invio
                    throw new InvalidArgumentException(sprintf(
                        "Tipo sms %s non valido. Tipi accettati: %s, %s, %s",
                        $type,
                        'Sms_model::SKEBBY_CLASSIC (:' . self::SKEBBY_CLASSIC . ')',
                        'Sms_model::SKEBBY_BASIC (:' . self::SKEBBY_BASIC . ')',
                        'Sms_model::SKEBBY_PLUS (:' . self::SKEBBY_PLUS . ')'
                    ));
                }
        }

        // A questo punto prova l'invio dell'sms: se lo stato non è `success`
        // allora lancio una runtime exception, altrimenti ritorno il numero di
        // sms inviati
        $result = skebbyGatewaySendSMS(
            $params['username'],
            $params['password'],
            $numbers,
            $this->replacePlaceholders($text, $data),
            $type,
            $senderNum? : $this->sender['number'],
            $senderName? : $this->sender['name']
        );

        if ($result['status'] != 'success') {
            throw new RuntimeException($result['message'], $result['code']);
        }
        
        return count($numbers);
    }

    // ==========================
    // Internals
    // ==========================

    /**
     * Aggiungi i parametri di connessione ad un servizio
     * 
     * @param string $name
     * @param array $params
     */
    private function setServiceParams($name, array $params) {
        $this->services[$name] = $params;
    }

    /**
     * Ottieni i parametri di connessione ad un servizio
     * 
     * @param string $name
     * @return array
     * @throws RuntimeException
     */
    private function getServiceParams($name) {
        if (empty($this->services[$name])) {
            throw new RuntimeException(sprintf("Il servizio %s non è stato impostato", $name));
        }

        return $this->services[$name];
    }

    /**
     * Normalizza il numero di telefono
     *  - rimuove ogni carattere estraneo
     *  - rimuove il +{prefix}
     * 
     * @param string $cell
     * @return string
     */
    private function normalizePhoneNumber($cell) {
        
        $_orig = $cell;
        $number = str_replace([' ', '-', '/'], "", $cell);

        if ($number[0] == '+') {
            // Il numero ha il prefisso
            $_number = ltrim($number, '+');
            $prefix = substr($_number, 0, 2);
            $number = substr($_number, 2);
        } else {
            $prefix = self::INTL_PREFIX_DEFAULT;
        }
        
        if (strlen($number) < 6 OR ! is_numeric($number) OR ! is_numeric($prefix)) {
            throw new InvalidArgumentException(sprintf("Il numero %s non è corretto", $_orig));
        }

        return $prefix . $number;
    }
    
    /**
     * Sostituisci i placeholder nel testo nella forma {key}
     * 
     * @param string $string
     * @param array $data
     */
    private function replacePlaceholders($string, array $data) {
        
        if (!$data) {
            return $string;
        }
        
        return str_replace(
            array_map(function($key) { return '{'.$key.'}'; }, array_keys($data)),
            array_values($data),
            $string
        );
    }

}
