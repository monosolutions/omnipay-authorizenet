<?php

namespace Omnipay\AuthorizeNet\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;

/**
 * Authorize.Net DPM Complete Authorize Response
 * This is the result of handling the callback.
 * The result will always be a HTML redirect snippet. This gets
 * returned to the gateway, displayed in the user's browser, and a GET
 * redirect is performed using JavaScript and meta refresh (belt and braces).
 * We may want to return to the success page, the failed page or the retry
 * page (so the user can correct the form).
 */
class DPMCompleteAuthorizeResponse extends SIMCompleteAuthorizeResponse implements RedirectResponseInterface
{
    const RESPONSE_CODE_APPROVED    = '1';
    const RESPONSE_CODE_DECLINED    = '2';
    const RESPONSE_CODE_ERROR       = '3';
    const RESPONSE_CODE_REVIEW      = '4';

    public function isSuccessful()
    {
        return isset($this->data['x_response_code']) && static::RESPONSE_CODE_APPROVED === $this->data['x_response_code'];
    }

    /**
     * If there is an error in the form, then the user should be able to go back
     * to the form and give it another shot.
     */
    public function isError()
    {
        return isset($this->data['x_response_code']) && static::RESPONSE_CODE_ERROR === $this->data['x_response_code'];
    }

    public function getMessage()
    {
        return parent::getReasonCode() . '|' . parent::getMessage();
    }

    /**
     * We are in the callback, and we MUST return a HTML fragment to do a redirect.
     * All headers we may return are discarded by the gateway, so we cannot use
     * the "Location:" header.
     */
    public function isRedirect()
    {
        return true;
    }

    /**
    * We default here to POST because the default redirect mechanism
    * in Omnipay Common only generates a HTML snippet for POST and not
    * GET.
    * TODO: We could fix that here so both GET and POST can be supported.
    * Our fix should also include the "form data" with the URL.
    */
    public function getRedirectMethod()
    {
        return 'POST';
    }

    /**
     * We probably do not require any redirect data, if the incomplete transaction
     * is still in the user's session and we can inspect the results from the saved
     * transaction in the database. We cannot send the result through the redirect
     * unless it is hashed in some way so the authorisation result cannot be faked.
     */
    public function getRedirectData()
    {
        return array();
    }

    /**
     * The cancel URL is never handled here - that is a direct link from the gateway.
     * The best approach is to have just one redirect URL, and once there, check the
     * result of the authorisation in the database (assuming it has been saved in the
     * callback) and take action from there.
     */
    public function getRedirectUrl()
    {
    }
}
