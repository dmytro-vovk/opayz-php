<?php
namespace Opayz\Api;

/**
 * @author Dmytro Vovk <dmitry.vovk@gmail.com>
 */
class Response {

    /** @var string */
    protected $rawResponse = '';
    /** @var array */
    protected $values = [];
    /** @var bool */
    protected $valid = false;

    /**
     * @param string $rawResponse
     * @param string $merchantSecret
     * @param string $signature
     */
    public function __construct($rawResponse, $merchantSecret, $signature) {
        $this->rawResponse = $rawResponse;
        $this->parse($merchantSecret, $signature);
    }

    /**
     * @return bool
     */
    public function isValid() {
        return $this->valid;
    }

    protected function parse($merchantSecret, $signature) {
        $xmlString = base64_decode($this->rawResponse, true);
        if ($signature !== base64_encode(sha1($xmlString . $merchantSecret))) {
            return;
        }
        if (empty($xmlString)) {
            return;
        }
        $xmlUseErrors = libxml_use_internal_errors(true);
        try {
            $xml = new \SimpleXMLElement($xmlString);
            $this->values = (array) $xml;
        } catch (\Exception $e) {
            libxml_clear_errors();
            libxml_use_internal_errors($xmlUseErrors);
            return;
        }
        libxml_use_internal_errors($xmlUseErrors);
        $this->valid = true;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name) {
        if (array_key_exists($name, $this->values)) {
            return $this->values[$name];
        }
        return null;
    }
}
