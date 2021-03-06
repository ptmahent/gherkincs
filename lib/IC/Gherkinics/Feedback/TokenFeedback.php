<?php
/**
 * @copyright 2013 Instaclick Inc.
 */
namespace IC\Gherkinics\Feedback;

use IC\Gherkinics\Model\Token;

/**
 * Token Feedback Backet
 *
 * @author Juti Noppornpitak <jnopporn@shiroyuki.com>
 */
final class TokenFeedback
{
    /**
     * @var \IC\Gherkincs\Model\Token the current token
     */
    private $token;

    /**
     * @var array
     */
    private $messageList = array();

    /**
     * Constructor
     *
     * @param \IC\Gherkinics\Model\Token $token
     */
    public function __construct(Token $token = null)
    {
        $this->token = $token;
    }

    /**
     * Retrieve token
     *
     * @return \IC\Gherkinics\Model\Token
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Add the message
     *
     * @param string $message
     */
    public function add($message)
    {
        $this->messageList[] = $message;
    }

    /**
     * Retrieve all messages
     *
     * @return array
     */
    public function all()
    {
        return $this->messageList;
    }

    /**
     * Retrieve the number of messages
     *
     * @return integer
     */
    public function count()
    {
        return count($this->messageList);
    }
}
