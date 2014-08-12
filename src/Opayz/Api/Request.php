<?php
namespace Opayz\Api;

/**
 * @author Dmytro Vovk <dmitry.vovk@gmail.com>
 */
class Request {

    /** @var string */
    protected $merchantSecret;
    /** @var string */
    protected $posId = '';
    /** @var string */
    protected $orderId = '';
    /** @var float */
    protected $amount = 0.0;
    /** @var string */
    protected $currency = 'USD';
    /** @var string */
    protected $description = '';
    /** @var string */
    protected $serverUrl = '';
    /** @var string */
    protected $callbackUrl = '';
    /** @var string */
    protected $xml = '';
    /** @var string */
    protected $signature = '';
    const XML_TEMPLATE = '
        <request>
            <point_of_sale_id>%s</point_of_sale_id>
            <order_id>%s</order_id>
            <amount>%f</amount>
            <currency>%s</currency>
            <description>%s</description>
            <server_url>%s</server_url>
            <result_url>%s</result_url>
        </request>';

    /**
     * @param string $merchantSecret
     * @param string $posId
     * @param string $serverUrl
     * @param string $callBackUrl
     */
    public function __construct($merchantSecret, $posId, $serverUrl, $callBackUrl = '') {
        $this->setPosId($posId);
        $this->serverUrl = $serverUrl;
        if (empty($callBackUrl)) {
            $this->callbackUrl = $serverUrl;
        } else {
            $this->callbackUrl = $callBackUrl;
        }
    }

    /**
     * @param string $posId
     *
     * @return $this
     */
    public function setPosId($posId) {
        $this->posId = $posId;
        return $this;
    }

    /**
     * @param string $orderId
     *
     * @return $this
     */
    public function setOrderId($orderId) {
        $this->orderId = $orderId;
        return $this;
    }

    /**
     * @param float $amount
     *
     * @return $this
     */
    public function setAmount($amount) {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param string $currency
     *
     * @return $this
     */
    public function setCurrency($currency) {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    /**
     * @return bool
     */
    public function isValid() {
        return !(
            empty($this->posId) ||
            empty($this->orderId) ||
            empty($this->amount) ||
            !is_numeric($this->amount) ||
            empty($this->currency)
        );
    }

    public function getEncodedXml() {
        if ($this->isValid()) {
            $this->generateXml();
            $this->sign();
            return [$this->xml, $this->signature];
        }
        return false;
    }

    protected function generateXml() {
        $this->xml = sprintf(
            self::XML_TEMPLATE,
            $this->posId,
            $this->orderId,
            $this->amount,
            $this->currency,
            $this->description,
            $this->serverUrl,
            $this->callbackUrl
        );
    }

    protected function sign() {
        $this->signature = base64_encode(sha1($this->xml . $this->merchantSecret));
    }
}
