<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Core\Response;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Response\DTO;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Class Response
 *
 * @package Bitrix24\SDK\Core\Response
 */
class Response
{
    /**
     * @var ResponseInterface
     */
    protected $httpResponse;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var  DTO\ResponseData|null
     */
    protected $responseData;

    /**
     * Response constructor.
     *
     * @param ResponseInterface $httpResponse
     * @param LoggerInterface   $logger
     */
    public function __construct(ResponseInterface $httpResponse, LoggerInterface $logger)
    {
        $this->httpResponse = $httpResponse;
        $this->logger = $logger;
    }

    /**
     * @return ResponseInterface
     */
    public function getHttpResponse(): ResponseInterface
    {
        return $this->httpResponse;
    }

    /**
     * @return DTO\ResponseData
     * @throws BaseException
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function getResponseData(): DTO\ResponseData
    {
        $this->logger->debug('getResponseData.start');

        if ($this->responseData === null) {
            try {
                $responseResult = $this->httpResponse->toArray(true);

                $this->handleApiLevelErrors($responseResult);

                $resultDto = new DTO\Result($responseResult['result']);
                $time = DTO\Time::initFromResponse($responseResult['time']);
                $this->responseData = new DTO\ResponseData(
                    $resultDto,
                    $time
                );
            } catch (\Throwable $e) {
                $this->logger->error(
                    $e->getMessage(),
                    [
                        'response' => $this->httpResponse->getContent(false),
                    ]
                );
                throw new BaseException(sprintf('api request error: %s', $e->getMessage()), $e->getCode(), $e);
            }
        }
        $this->logger->debug('getResponseData.finish');

        return $this->responseData;
    }


    /**
     * @param array $apiResponse
     */
    private function handleApiLevelErrors(array $apiResponse): void
    {
        $this->logger->debug('handleApiLevelErrors.start');

        if (array_key_exists('error', $apiResponse)) {
            $errorMsg = sprintf(
                '%s - %s ',
                $apiResponse['error'],
                (array_key_exists('error_description', $apiResponse) ? $apiResponse['error_description'] : ''),
            );

//            switch (strtoupper(trim($apiResponse['error']))) {
//                case 'EXPIRED_TOKEN':
//                    throw new Bitrix24TokenIsExpiredException($errorMsg);
//                case 'WRONG_CLIENT':
//                case 'ERROR_OAUTH':
//                    $this->log->error($errorMsg, $this->getErrorContext());
//                    throw new Bitrix24WrongClientException($errorMsg);
//                case 'ERROR_METHOD_NOT_FOUND':
//                    $this->log->error($errorMsg, $this->getErrorContext());
//                    throw new Bitrix24MethodNotFoundException($errorMsg);
//                case 'INVALID_TOKEN':
//                case 'INVALID_GRANT':
//                    $this->log->error($errorMsg, $this->getErrorContext());
//                    throw new Bitrix24TokenIsInvalidException($errorMsg);

//                case 'PAYMENT_REQUIRED':
//                    $this->log->error($errorMsg, $this->getErrorContext());
//                    throw new Bitrix24PaymentRequiredException($errorMsg);
//                case 'NO_AUTH_FOUND':
//                    $this->log->error($errorMsg, $this->getErrorContext());
//                    throw new Bitrix24PortalRenamedException($errorMsg);
//                case 'INSUFFICIENT_SCOPE':
//                    $this->log->error($errorMsg, $this->getErrorContext());
//                    throw new Bitrix24InsufficientScope($errorMsg);
//                default:
//                    $this->log->error($errorMsg, $this->getErrorContext());
//                    throw new Bitrix24ApiException($errorMsg);
        }
        $this->logger->debug('handleApiLevelErrors.finish');
    }
}